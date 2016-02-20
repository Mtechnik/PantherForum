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

if ($panther_config['o_delete_full'] == '1')
	message($lang_common['Bad request']);

// Load the admin_deleted.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_deleted.php';

if (isset($_POST['post_id']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/deleted.php');
	$post_id = intval(key($_POST['post_id']));
	$action = isset($_POST['action']) && is_array($_POST['action']) ? intval($_POST['action'][$post_id]) : '1';
	$data = array(
		':id'	=>	$post_id,
	);

	$ps = $db->run('SELECT t.first_post_id, p.topic_id, p.message, t.subject, t.forum_id FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON p.topic_id=t.id WHERE p.id=:id AND p.deleted=1', $data);
	if (!$ps->rowCount())
		message($lang_common['Bad request']);
	else
		$post = $ps->fetch();

	$is_topic_post = ($post_id == $post['first_post_id']) ? true : false;
	if ($action == '1')
	{
		if ($is_topic_post)
		{
			$update = array(
				'deleted'	=>	0,
			);

			$data = array(
				':id'	=>	$post['topic_id'],
			);

			$db->update('topics', $update, 'id=:id', $data);
			if (!defined('PANTHER_CJK_HANGUL_REGEX'))
				require PANTHER_ROOT.'include/search_idx.php';

			update_search_index('post', $post['topic_id'], $post['message'], $post['subject']);

			$db->update('posts', $update, 'topic_id=:id AND deleted=1 AND approved=1', $data);
			$ps = $db->select('posts', 'message, id', $data, 'id=:id');
			foreach ($ps as $cur_post)
				update_search_index('post', $cur_post['id'], $cur_post['message']);

			update_forum($post['forum_id']);
			redirect(panther_link($panther_url['admin_deleted']), $lang_admin_deleted['Topic approved redirect']);
		}
		else
		{
			$topic_data = array(
				':id'	=>	$post['topic_id'],
			);

			$ps = $db->select('topics', 1, $topic_data, 'id=:id AND deleted=0 AND approved=1');	// Check there's a valid topic to go back to
			if (!$ps->rowCount())
				message($lang_admin_deleted['topic has been deleted']);

			$update = array(
				'deleted'	=>	0,
			);
			
			$post_data = array(
				':id'	=>	$post_id,
			);
			
			$db->update('posts', $update, 'id=:id', $post_data);
			if (!defined('PANTHER_CJK_HANGUL_REGEX'))
				require PANTHER_ROOT.'include/search_idx.php';

			update_search_index('post', $post_id, $post['message']);
			
			$ps = $db->select('posts', 'id, poster, posted', $topic_data, 'topic_id=:id AND approved=1 AND deleted=0', 'id DESC LIMIT 1');
			list($last_id, $poster, $posted) = $ps->fetch(PDO::FETCH_NUM);
			
			$ps = $db->select('topics', 'num_replies', $topic_data, 'id=:id');
			$num_replies = $ps->fetchColumn();
			
			$update = array(
				'num_replies'	=>	$num_replies+1,
				'last_post'		=>	$posted,
				'last_post_id'	=>	$last_id,
				'last_poster'	=>	$poster,
			);

			$db->update('topics', $update, 'id=:id', $topic_data);
			update_search_index('post', $post_id, $post['message']);
				
			update_forum($post['forum_id']);
			redirect(panther_link($panther_url['admin_deleted']), $lang_admin_deleted['Post approved redirect']);			
		}
	}
	else
	{
		if ($is_topic_post)
		{
			permanently_delete_topic($post['topic_id']);
			redirect(panther_link($panther_url['admin_deleted']), $lang_admin_deleted['Topic deleted redirect']);
		}
		else
		{
			permanently_delete_post($post_id);
			redirect(panther_link($panther_url['admin_deleted']), $lang_admin_deleted['Post deleted redirect']);
		}
	}
}

$ps = $db->run('SELECT t.id AS topic_id, t.forum_id, p.poster, p.poster_id, p.posted, p.message, p.id AS pid, p.hide_smilies, t.subject, f.forum_name FROM '.$db->prefix.'posts AS p LEFT JOIN '.$db->prefix.'topics AS t ON p.topic_id=t.id LEFT JOIN '.$db->prefix.'forums AS f ON t.forum_id=f.id WHERE p.deleted=1 OR t.deleted=1 ORDER BY p.posted DESC');

require PANTHER_ROOT.'include/parser.php';
$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Deleted']);
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

generate_admin_menu('deleted');

$posts = array();
foreach ($ps as $cur_post)
	$posts[] = array(
		'id' => $cur_post['pid'],
		'posted' => format_time($cur_post['posted']),
		'message' => $parser->parse_message($cur_post['message'], $cur_post['hide_smilies']),
		'poster' => ($cur_post['poster'] != '') ? array('href' => panther_link($panther_url['profile'], array($cur_post['poster_id'], url_friendly($cur_post['poster']))), 'poster' => $cur_post['poster']) : '',
		'forum' => ($cur_post['forum_name'] != '') ? array('href' => panther_link($panther_url['forum'], array($cur_post['forum_id'], url_friendly($cur_post['forum_name']))), 'forum_name' => $cur_post['forum_name']) : '',
		'topic' => ($cur_post['subject'] != '') ? array('href' => panther_link($panther_url['topic'], array($cur_post['topic_id'], url_friendly($cur_post['subject']))), 'subject' => $cur_post['subject']) : '',
		'post' => ($cur_post['pid'] != '') ? array('href' => panther_link($panther_url['post'], array($cur_post['pid'])), 'post' => sprintf($lang_admin_deleted['Post ID'], $cur_post['pid'])) : '',
	);

$tpl = load_template('admin_deleted.tpl');
echo $tpl->render(
	array(
		'lang_admin_common' => $lang_admin_common,
		'lang_admin_deleted' => $lang_admin_deleted,
		'lang_common' => $lang_common,
		'form_action' => panther_link($panther_url['admin_deleted']),
		'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/deleted.php'),
		'posts' => $posts,
	)
);

require PANTHER_ROOT.'footer.php';