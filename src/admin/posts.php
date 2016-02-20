<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

if (!defined('PANTHER'))
{
	define('PANTHER_ROOT', __DIR__.'/../');
	require PANTHER_ROOT.'include/common.php';
}

require PANTHER_ROOT.'include/common_admin.php';

if (($panther_user['is_admmod'] && $panther_user['g_mod_cp'] == '0' && !$panther_user['is_admin']) || !$panther_user['is_admmod'])
	message($lang_common['No permission'], false, '403 Forbidden');

check_authentication();

// Load the admin_reports.php language file
require PANTHER_ROOT.'lang/English/admin_posts.php';

require PANTHER_ROOT.'include/parser.php';
require PANTHER_ROOT.'include/search_idx.php';
if (isset($_POST['post_id']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/posts.php');
	$post_id = intval(key($_POST['post_id']));
	$action = isset($_POST['action']) && is_array($_POST['action']) ? intval($_POST['action'][$post_id]) : '1';
	$data = array(
		':id'	=>	$post_id
	);

	$ps = $db->run('SELECT p.posted, p.message, p.poster, p.poster_id, p.topic_id, p.poster_email, u.email, p.poster_ip, t.forum_id, t.subject, t.first_post_id, f.forum_name, u.num_posts, g.g_promote_next_group, g.g_promote_min_posts FROM '.$db->prefix.'posts AS p LEFT JOIN '.$db->prefix.'topics AS t ON p.topic_id=t.id LEFT JOIN '.$db->prefix.'forums AS f ON t.forum_id=f.id LEFT JOIN '.$db->prefix.'users AS u ON p.poster_id=u.id LEFT JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id WHERE p.id=:id', $data);
	$post = $ps->fetch();

	$is_topic_post = ($post_id == $post['first_post_id']) ? true : false;
	if ($action == '1')
	{
		$update = array(
			'approved'	=>	1,
		);
		
		$db->update('posts', $update, 'id=:id', $data);
		if ($is_topic_post)
		{
			$update = array(
				'approved'	=>	1,
			);

			$data = array(
				':id'	=>	$post['topic_id'],
			);
			$db->update('topics', $update, 'id=:id', $data);

			update_search_index('post', $post_id, $post['message'], $post['subject']);
			
			require PANTHER_ROOT.'include/email.php';
			$mailer->handle_forum_subscriptions($post, $post['poster'], $post['topic_id']);
		}
		else
		{
			// Just to be safe in case there has been another reply made since...
			$data = array(
				':id'	=>	$post['topic_id']
			);

			$ps = $db->select('posts', 'id, poster, posted', $data, 'topic_id=:id AND approved=1 AND deleted=0', 'id DESC LIMIT 1');
			list($last_id, $poster, $posted) = $ps->fetch(PDO::FETCH_NUM);
			
			$data = array(
				':last_post'	=>	$posted,
				':last_post_id'	=>	$last_id,
				':poster'		=>	$poster,
				':id'			=>	$post['topic_id'],
			);
	
			$db->run('UPDATE '.$db->prefix.'topics SET num_replies=num_replies+1, last_post=:last_post, last_post_id=:last_post_id, last_poster=:poster WHERE id=:id', $data);
			update_search_index('post', $post_id, $post['message'], $post['subject']);

			require PANTHER_ROOT.'include/email.php';
			$mailer->handle_topic_subscriptions($post['topic_id'], $post, $post['poster'], $post_id, $posted);
		}

		if ($panther_forums[$post['forum_id']]['increment_posts'] == '1')
		{
			$data = array(
				':id'	=>	$post['poster_id'],
			);

			$db->run('UPDATE '.$db->prefix.'users SET num_posts=num_posts+1 WHERE id=:id', $data);
		
			// Promote this user to a new group if enabled
			if ($post['g_promote_next_group'] != 0 && $post['num_posts'] >= $post['g_promote_min_posts'])
			{
				$update = array(
					'group_id'	=>	$post['g_promote_next_group'],
				);
				
				$data = array(
					':id'	=>	$post['poster_id'],
				);
				
				$db->update('users', $update, 'id=:id', $data);
			}
		}

		update_forum($post['forum_id']);
		redirect(panther_link($panther_url['admin_posts']), $lang_admin_posts['Post approved redirect']);
	}
	else
	{
		if (($panther_user['g_mod_sfs_report'] == '1' || $panther_user['is_admin']) && $action == '3' && $panther_config['o_sfs_api'] != '')
		{
			//If the user wasn't a guest we need to get the email from the users table
			$email = ($post['poster_email'] == '' && $post['poster_id'] != 1) ? $post['email'] : $post['poster_email'];

			//Reporting now made fun =)
			if (!stopforumspam_report($panther_config['o_sfs_api'], $post['poster_ip'], $email, $post['poster'], $post['message']))
				message($lang_common['Unable to add spam data']);
		}

		if ($is_topic_post)
		{
			delete_topic($post['topic_id']);
			update_forum($post['forum_id']);		
		}
		else
		{
			delete_post($post_id, $post['topic_id']);
			update_forum($post['forum_id']);
		}

		redirect(panther_link($panther_url['admin_posts']), $lang_admin_posts['Post deleted redirect']);
	}
}

$data = array(
	':gid'	=>	$panther_user['g_id'],
);

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Posts']);
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

generate_admin_menu('posts');

$posts = array();
$ps = $db->run('SELECT t.id AS topic_id, t.forum_id, p.poster, p.poster_id, p.posted, p.message, p.id AS pid, p.hide_smilies, t.subject, f.forum_name, f.moderators FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON p.topic_id = t.id LEFT JOIN '.$db->prefix.'forums AS f ON t.forum_id = f.id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND p.approved=0 AND p.deleted=0 ORDER BY p.posted DESC', $data);
foreach ($ps as $cur_post)
{
	// Check if we can moderate this forum (if not, we shouldn't be approving posts for it)
	$moderators = ($cur_post['moderators'] != '') ? unserialize($cur_post['moderators']) : array();
	if (!in_array($panther_user['username'], $moderators) && !$panther_user['is_admin'] && $panther_user['g_global_moderator'] != 1)
		continue;

	$data = array(
		':id'	=>	$cur_post['pid'],
	);

	$attachments = array();
	$ps1 = $db->select('attachments', 'id, filename, post_id, size, downloads', $data, 'post_id=:id');
	foreach ($ps1 as $cur_attach)
		$attachments[] = array('icon' => attach_icon(attach_get_extension($cur_attach['filename'])), 'link' => panther_link($panther_url['attachment'], array($cur_attach['id'])), 'name' => $cur_attach['filename'], 'size' => sprintf($lang_topic['Attachment size'], file_size($cur_attach['size'])), 'downloads' => sprintf($lang_topic['Attachment downloads'], forum_number_format($cur_attach['downloads'])));

	$posts[] = array(
		'posted' => format_time($cur_post['posted']),
		'poster' => ($cur_post['poster'] != '') ? array('href' => panther_link($panther_url['profile'], array($cur_post['poster_id'], url_friendly($cur_post['poster']))), 'poster' => $cur_post['poster']) : '',
		'message' => $parser->parse_message($cur_post['message'], $cur_post['hide_smilies']),
		'id' => $cur_post['pid'],
		'attachments' => $attachments,
		'forum' => ($cur_post['forum_name'] != '') ? array('href' => panther_link($panther_url['forum'], array($cur_post['forum_id'], url_friendly($cur_post['forum_name']))), 'forum_name' => $cur_post['forum_name']) : '',
		'topic' => ($cur_post['subject'] != '') ? array('href' => panther_link($panther_url['topic'], array($cur_post['topic_id'], url_friendly($cur_post['subject']))), 'subject' => $cur_post['subject']) : '',
		'post' => ($cur_post['pid'] != '') ? array('href' => panther_link($panther_url['post'], array($cur_post['pid'])), 'post' => sprintf($lang_admin_posts['Post ID'], $cur_post['pid'])) : '',
	);
}

$tpl = load_template('admin_posts.tpl');
echo $tpl->render(
	array(
		'lang_admin_common' => $lang_admin_common,
		'lang_admin_posts' => $lang_admin_posts,
		'lang_common' => $lang_common,
		'form_action' => panther_link($panther_url['admin_posts']),
		'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/posts.php'),
		'posts' => $posts,
	)
);

require PANTHER_ROOT.'footer.php';