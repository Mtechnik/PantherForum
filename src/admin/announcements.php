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

$action = isset($_GET['action']) ? $_GET['action'] : null;
$id = isset($_GET['id']) ? intval($_GET['id']) : '0';
$page = (!isset($_GET['p']) || $_GET['p'] <= '1') ? '1' : intval($_GET['p']);

if (($panther_user['is_admmod'] && $panther_user['g_mod_cp'] == '0' && !$panther_user['is_admin']) || !$panther_user['is_admmod'])
	message($lang_common['No permission'], false, '403 Forbidden');

check_authentication();

// Load the admin_announcements.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_announcements.php';

if (isset($_POST['form_sent']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/announcements.php');
	$action = isset($_POST['action']) ? panther_trim($_POST['action']) : '';

	if ($action == 'add' || $action == 'edit')
	{
		$forums = isset($_POST['forums']) && is_array($_POST['forums']) ? array_map('intval', $_POST['forums']) : array();

		if (empty($forums))
			message($lang_common['Bad request']);

		$announcement = isset($_POST['message']) ? panther_trim($_POST['message']) : '';
		$title = isset($_POST['title']) ? panther_trim($_POST['title']) : '';
		$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

		if (strlen($title) > 50)
			message($lang_admin_announcements['title too long']);

		if (strlen($announcement) < 1 || strlen($title) < 1)
			message($lang_common['Bad request']);

		$insert = array(
			'message'	=>	$announcement,
			'forum_id'	=>	implode(',', $forums),
			'user_id'	=>	$panther_user['id'],
			'subject'		=>	$title,
		);

		if ($action == 'add')
		{
			$db->insert('announcements', $insert);
			$id = $db->lastInsertId($db->prefix.'announcements');

			$redirect_msg = $lang_admin_announcements['added redirect'];
		}
		else
		{
			if ($id < 1)
				message($lang_common['Bad request']);

			$data = array(
				':id'	=>	$id,
			);
			
			$db->update('announcements', $insert, 'id=:id', $data);
			$redirect_msg = $lang_admin_announcements['edit redirect'];
		}

		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';

		generate_announcements_cache();
		redirect(panther_link($panther_url['announcement_fid'], array($id, $forums[0], url_friendly($title))), $redirect_msg);
	}
	else if ($action == 'delete')
	{
		$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		if ($id < 1)
			message($lang_common['Bad request']);
		
		$data = array(
			':id'	=>	$id,
		);
		
		$db->delete('announcements', 'id=:id', $data);
		
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';
		
		redirect(panther_link($panther_url['admin_announcements']), $lang_admin_announcements['delete redirect']);
	}
	else
		message($lang_common['Bad request']);
}

$ps = $db->select('announcements', 'COUNT(id)');
$total = $ps->fetchColumn();
$num_pages = ceil($total/$panther_config['o_disp_topics_default']);
if ($page > $num_pages) $page = 1;
$start_from = intval($panther_config['o_disp_topics_default'])*($page-1);

$data = array(
	':start' => $start_from,
	':limit' => $panther_config['o_disp_topics_default'],
);

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Announcements']);
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

generate_admin_menu('announcements');

if ($action == 'add' || $action == 'edit' && $id > 0)
{
	if ($action == 'edit')
	{
		$data = array(
			':id'	=>	$id,
		);

		$ps = $db->select('announcements', 'message, forum_id, subject', $data, 'id=:id');
		if (!$ps->rowCount())
			message($lang_common['Bad request']);

		$cur_announcement = $ps->fetch();
	}
	else
	{
		$cur_announcement = array(
			'forum_id'	=>	0,
			'subject'		=>	'',
			'message'	=>	'',
		);
	}

	$id_list = explode(',', $cur_announcement['forum_id']);

	// Display all the categories and forums
	$categories = $forums = array();
	$ps = $db->run('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id WHERE f.redirect_url IS NULL ORDER BY c.disp_position, c.id, f.disp_position');
	foreach ($ps as $cur_forum)
	{
		if (!isset($categories[$cur_forum['cid']]))
			$categories[$cur_forum['cid']] = array(
				'cat_name' => $cur_forum['cat_name'],
				'id' => $cur_forum['cid'],
			);
				
		$forums[] = array(
			'id' => $cur_forum['fid'],
			'forum_name' => $cur_forum['forum_name'],
			'category_id' => $cur_forum['cid'],
			'selected' => ((in_array($cur_forum['fid'], $id_list)) ? true : false),
		);
	}

	$tpl = load_template('edit_announcement.tpl');
	echo $tpl->render(
		array(
			'lang_admin_announcements' => $lang_admin_announcements,
			'lang_common' => $lang_common,
			'form_action' => panther_link($panther_url['admin_announcements']),
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/announcements.php'),
			'id' => $id,
			'action' => $action,
			'cur_announce' => $cur_announcement,
			'help_link' => panther_link($panther_url['help'], array('bbcode')),
			'categories' => $categories,
			'forums' => $forums,
		)
	);
}
elseif ($action == 'delete' && $id > 0)
{
	$tpl = load_template('delete_announcement.tpl');
	echo $tpl->render(
		array(
			'lang_admin_announcements' => $lang_admin_announcements,
			'lang_common' => $lang_common,
			'lang_admin_common' => $lang_admin_common,
			'form_action' => panther_link($panther_url['delete_announcement'], array($id)),
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/announcements.php'),
			'id' => $id,
		)
	);
}
else
{
	$announcements = array();
	$ps = $db->run('SELECT a.subject, a.forum_id, a.user_id, u.username, u.group_id, a.id FROM '.$db->prefix.'announcements AS a INNER JOIN '.$db->prefix.'users AS u ON a.user_id=u.id ORDER BY a.id DESC LIMIT :start, :limit', $data);
	foreach ($ps as $announcement)
	{
		$forum_names = array();
		$ids = explode(',', $announcement['forum_id']);
		foreach ($ids as $id)
		{
			$data = array(
				':id'	=>	$id,
			);

			$ps1 = $db->select('forums', 'forum_name', $data, 'id=:id');
			$forum_names[] = $ps1->fetchColumn();
		}

		$announcements[] = array(
			'edit_link' => panther_link($panther_url['edit_announcement'], array($announcement['id'])),
			'delete_link' => panther_link($panther_url['delete_announcement'], array($announcement['id'])),
			'subject' => $announcement['subject'],
			'poster' => colourize_group($announcement['username'], $announcement['group_id'], $announcement['user_id']),
		);
	}

	$tpl = load_template('admin_announcements.tpl');
	echo $tpl->render(
		array(
			'lang_admin_common' => $lang_admin_common,
			'lang_admin_announcements' => $lang_admin_announcements,
			'lang_common' => $lang_common,
			'pagination' => paginate($num_pages, $page, $panther_url['admin_announcements']),
			'add_link' => panther_link($panther_url['add_announcement']),
			'announcements' => $announcements,
		)
	);
}

require PANTHER_ROOT.'footer.php';