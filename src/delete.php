<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

if (!defined('PANTHER'))
{
	define('PANTHER_ROOT', __DIR__.'/');
	require PANTHER_ROOT.'include/common.php';
}

if ($panther_user['g_read_board'] == '0')
	message($lang_common['No view'], false, '403 Forbidden');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id < 1)
	message($lang_common['Bad request'], false, '404 Not Found');

if ($panther_user['is_bot'])
	message($lang_common['No permission']);

// Fetch some info about the post, the topic and the forum
$data = array(
	':gid'	=>	$panther_user['g_id'],
	':id'	=>	$id,
);

$ps = $db->run('SELECT f.id AS fid, f.forum_name, f.moderators, f.password, f.redirect_url, fp.post_replies, fp.post_topics, t.id AS tid, t.subject, t.archived, t.first_post_id, t.closed, p.posted, p.poster, p.poster_id, p.poster_ip, p.message, p.hide_smilies, p.poster_email FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND p.id=:id AND p.approved=1 AND p.deleted=0', $data);
if (!$ps->rowCount())
	message($lang_common['Bad request'], false, '404 Not Found');

$cur_post = $ps->fetch();

if ($panther_config['o_censoring'] == '1')
	$cur_post['subject'] = censor_words($cur_post['subject']);

// Sort out who the moderators are and if we are currently a moderator (or an admin)
$mods_array = ($cur_post['moderators'] != '') ? unserialize($cur_post['moderators']) : array();
$is_admmod = ($panther_user['is_admin'] || ($panther_user['g_moderator'] == '1' && $panther_user['g_global_moderator'] ||  array_key_exists($panther_user['username'], $mods_array))) ? true : false;

$is_topic_post = ($id == $cur_post['first_post_id']) ? true : false;

// Do we have permission to edit this post?
if (($panther_user['g_delete_posts'] == '0' || ($panther_user['g_delete_topics'] == '0' && $is_topic_post) || $cur_post['poster_id'] != $panther_user['id'] || $cur_post['closed'] == '1' || $panther_user['g_deledit_interval'] != 0 && (time() - $cur_post['posted']) > $panther_user['g_deledit_interval']) && !$is_admmod)
	message($lang_common['No permission'], false, '403 Forbidden');

if ($is_admmod && (!$panther_user['is_admin'] && (in_array($cur_post['poster_id'], get_admin_ids()) && $panther_user['g_mod_edit_admin_posts'] == '0')))
	message($lang_common['No permission'], false, '403 Forbidden');

// Load the delete.php language file
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/delete.php';

if ($cur_post['password'] != '')
	check_forum_login_cookie($cur_post['fid'], $cur_post['password']);

check_posting_ban();

if ($cur_post['archived'] == '1')
	message($lang_delete['Topic archived']);

if (isset($_POST['delete']))
{
	// Make sure they got here from the site
	confirm_referrer('delete.php');

	require PANTHER_ROOT.'include/search_idx.php';
	if ($panther_user['is_admmod'] && ($panther_user['g_mod_sfs_report'] == '1' || $panther_user['is_admin']))
	{
		$sfs_report = isset($_POST['sfs_report']) ? intval($_POST['sfs_report']) : '0';
		if ($sfs_report == '1' && $panther_config['o_sfs_api'] != '')
		{
			//If the user wasn't a guest we need to get the email from the users table
			if ($cur_post['poster_email'] == '' && $cur_post['poster_id'] != 1)
			{
				$data = array(
					':id'	=>	$cur_post['poster_id'],
				);

				$ps = $db->select('users', 'email', $data, 'id=:id');
				$email= $ps->fetchColumn();
			}
			else
				$email = $cur_post['poster_email'];

			//Reporting now made fun =)
			if (!stopforumspam_report($panther_config['o_sfs_api'], $cur_post['poster_ip'], $email, $cur_post['poster'], $cur_post['message']))
				message($lang_common['Unable to add spam data']);
		}
	}

	if ($is_topic_post)
	{
		// Delete the topic and all of its posts
		delete_topic($cur_post['tid']);
		update_forum($cur_post['fid']);
		
		($hook = get_extensions('delete_after_delete')) ? eval($hook) : null;
		redirect(panther_link($panther_url['forum'], array($cur_post['fid'], url_friendly($cur_post['forum_name']))), $lang_delete['Topic del redirect']);
	}
	else
	{
		// Delete just this one post
		delete_post($id, $cur_post['tid']);
		update_forum($cur_post['fid']);

		// Redirect towards the previous post
		$data = array(
			':tid'	=>	$cur_post['tid'],
			':id'	=>	$id,
		);

		$ps = $db->select('posts', 'id', $data, 'topic_id=:tid AND id < :id', 'id DESC LIMIT 1');
		$post_id = $ps->fetchColumn();
		
		($hook = get_extensions('delete_after_delete')) ? eval($hook) : null;
		redirect(panther_link($panther_url['post'], array($post_id)), $lang_delete['Post del redirect']);
	}
}

require PANTHER_ROOT.'include/parser.php';
$page_title = array($panther_config['o_board_title'], $lang_delete['Delete post']);
define ('PANTHER_ACTIVE_PAGE', 'index');
require PANTHER_ROOT.'header.php';

$tpl = load_template('delete.tpl');
echo $tpl->render(
	array(
		'lang_common' => $lang_common,
		'index_link' => panther_link($panther_url['index']),
		'forum_link' => panther_link($panther_url['forum'], array($cur_post['fid'], url_friendly($cur_post['forum_name']))),
		'post_link' => panther_link($panther_url['post'], array($id)),
		'cur_post' => $cur_post,
		'lang_delete' => $lang_delete,
		'form_action' => panther_link($panther_url['delete'], array($id)),
		'csrf_token' => generate_csrf_token(),
		'is_topic_post' => $is_topic_post,
		'posted' => format_time($cur_post['posted']),
		'is_admmod' => $is_admmod,
		'panther_config' => $panther_config,
		'message' => $parser->parse_message($cur_post['message'], $cur_post['hide_smilies']),
	)
);

require PANTHER_ROOT.'footer.php';