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

if (!$panther_user['is_admin'])
	message($lang_common['No permission'], false, '403 Forbidden');

if ($panther_user['id'] != '2')
{
	if(!is_null($admins[$panther_user['id']]['admin_moderate']))
	{
		if ($admins[$panther_user['id']]['admin_moderate'] == '0')
			message($lang_common['No permission'], false, '403 Forbidden');
	}
}

check_authentication();

// Load the admin_moderate.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_moderate.php';

if (isset($_POST['form_sent']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/moderate.php');
	if ($action == 'add')
	{
		$message = isset($_POST['message']) ? panther_trim($_POST['message']) : null;
		$title = isset($_POST['title']) ? panther_trim($_POST['title']) : null;
		$add_start = isset($_POST['add_start']) ? utf8_ltrim($_POST['add_start']) : null;
		$add_end = isset($_POST['add_end']) ? utf8_rtrim($_POST['add_end']) : null;
		$increment = isset($_POST['increment']) ? intval($_POST['increment']) : '0';
		$send_email = isset($_POST['send_email']) ? intval($_POST['send_email']) : '0';
		
		if (strlen($title) > 50)
			message($lang_admin_moderate['title too long']);
			
		if (strlen($add_start) > 50 || strlen($add_end) > 50)
			message($lang_admin_moderate['addition too long']);

		if (strlen($title) < 1)
			message($lang_common['Bad request']);
	
		$close = isset($_POST['close']) ? intval($_POST['close']) : '2';
		$stick = isset($_POST['stick']) ? intval($_POST['stick']) : '2';
		$archive = isset($_POST['archive']) ? intval($_POST['archive']) : '2';
		$move = isset($_POST['forum']) ? intval($_POST['forum']) : '0';
		$leave_redirect = isset($_POST['redirect']) ? intval($_POST['redirect']) : '0';
	
		$insert = array(
			'title'	=>	$title,
			'close'	=>	$close,
			'stick'	=>	$stick,
			'archive' => $archive,
			'move'	=>	$move,
			'leave_redirect' => $leave_redirect,
			'reply_message'	=>	$message,
			'add_start'	=>	$add_start,
			'add_end'	=>	$add_end,
			'send_email' =>	$send_email,
			'increment_posts' => $increment,
		);

		$db->insert('multi_moderation', $insert);
		redirect(panther_link($panther_url['admin_moderate']), $lang_admin_moderate['added redirect']);
	}
	elseif ($action == 'edit' && $id > '0')
	{
		$message = isset($_POST['message']) ? panther_trim($_POST['message']) : null;
		$title = isset($_POST['title']) ? panther_trim($_POST['title']) : null;
		$add_start = isset($_POST['add_start']) ? utf8_ltrim($_POST['add_start']) : null;
		$add_end = isset($_POST['add_end']) ? utf8_rtrim($_POST['add_end']) : null;

		if (strlen($title) > 50)
			message($lang_admin_moderate['title too long']);
			
		if (strlen($add_start) > 50 || strlen($add_end) > 50)
			message($lang_admin_moderate['addition too long']);
	
		if (strlen($title) < 1)
			message($lang_common['Bad request']);
	
		$close = isset($_POST['close']) ? intval($_POST['close']) : '2';
		$stick = isset($_POST['stick']) ? intval($_POST['stick']) : '2';
		$archive = isset($_POST['archive']) ? intval($_POST['archive']) : '2';
		$move = isset($_POST['forum']) ? intval($_POST['forum']) : '0';
		$leave_redirect = isset($_POST['redirect']) ? intval($_POST['redirect']) : '0';
		$reply = isset($_POST['reply']) ? intval($_POST['reply']) : '0';
		$increment = isset($_POST['increment']) ? intval($_POST['increment']) : '0';
		$send_email = isset($_POST['send_email']) ? intval($_POST['send_email']) : '0';
		
		$update = array(
			'title'	=>	$title,
			'close'	=>	$close,
			'stick'	=>	$stick,
			'archive' => $archive,
			'move'	=>	$move,
			'leave_redirect'	=>	$leave_redirect,
			'add_reply'	=>	$reply,
			'reply_message'	=>	$message,
			'add_start'	=>	$add_start,
			'add_end'	=>	$add_end,
			'send_email'	=>	$send_email,
			'increment_posts'	=>	$increment,
		);
		
		$data = array(
			':id'	=>	$id,
		);

		$db->update('multi_moderation', $update, 'id=:id', $data);
		redirect(panther_link($panther_url['admin_moderate']), $lang_admin_moderate['edit redirect']);
	}
	elseif ($action == 'delete' && $id > '0')
	{
		$data = array(
			':id'	=>	$id,
		);

		$rows = $db->delete('multi_moderation', 'id=:id', $data);
		if (!$rows)
			message($lang_common['Bad request']); // If there are no rows returned we've either attempted to URL hack or something is wrong with the database (which will be displayed)

		redirect(panther_link($panther_url['admin_moderate']), $lang_admin_moderate['delete redirect']);
	}
}

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Moderate']);
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

generate_admin_menu('moderate'); 

if ($action == 'add' || $action == 'edit')
{
	
	if ($action == 'edit')
	{
		$data = array(
			':id'	=>	$id,
		);

		$ps = $db->select('multi_moderation', 'close, stick, archive, move, leave_redirect, reply_message, title, add_start, add_end, send_email, increment_posts', $data, 'id=:id');
		$cur_action = $ps->fetch();
	}
	else
	{
		$cur_action = array(
			'close' => 2,
			'stick' => 2,
			'archive' => 2,
			'move' => 0,
			'leave_redirect' => 0,
			'reply_message' => '',
			'title' => '',
			'add_start' => '',
			'add_end' => '',
			'send_email' => 1,
			'increment_posts' => 1,
		);
	}

	// Display all the categories and forums
	$categories = $forums = array();
	$ps = $db->run('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id = f.cat_id WHERE f.redirect_url IS NULL ORDER BY c.disp_position, c.id, f.disp_position');
	foreach ($ps as $cur_forum)
	{
		if (!isset($categories[$cur_forum['cid']]))
			$categories[$cur_forum['cid']] = array(
				'name' => $cur_forum['cat_name'],
				'id' => $cur_forum['cid'],
			);

		$forums[] = array(
			'id' => $cur_forum['fid'],
			'name' => $cur_forum['forum_name'],
			'category_id' => $cur_forum['cid'],
		);
	}

	$tpl = load_template('edit_action.tpl');
	echo $tpl->render(
		array(
			'lang_admin_moderate' => $lang_admin_moderate,
			'lang_admin_common' => $lang_admin_common,
			'lang_common' => $lang_common,
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/moderate.php'),
			'form_action' => ($action == 'add') ? panther_link($panther_url['admin_moderate_add']) : panther_link($panther_url['admin_moderate_edit'], array($id)),
			'action' => $cur_action,
			'categories' => $categories,
			'forums' => $forums,
		)
	);
}
else if ($action == 'delete' && $id > '0')
{
	$tpl = load_template('delete_action.tpl');
	echo $tpl->render(
		array(
			'lang_admin_moderate' => $lang_admin_moderate,
			'lang_admin_common' => $lang_admin_common,
			'lang_common' => $lang_common,
			'form_action' => panther_link($panther_url['admin_moderate_delete'], array($id)),
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/moderate.php'),
		)
	);
}
else
{
	$ps = $db->select('multi_moderation', 'COUNT(id)');
	$total = $ps->fetchColumn();
	
	$num_pages = ceil($total/15);
	if ($page > $num_pages) $page = 1;
	$start_from = 15*($page-1);
	
	$ps = $db->select('multi_moderation', 'title, id', array(), '', 'id DESC LIMIT '.$start_from.', '.$panther_config['o_disp_topics_default']);

	$actions = array();
	foreach ($ps as $action)
		$actions[] = array(
			'title' => $action['title'],
			'edit_link' => panther_link($panther_url['admin_moderate_edit'], array($action['id'])),
			'delete_link' => panther_link($panther_url['admin_moderate_delete'], array($action['id'])),
		);
	
	$tpl = load_template('admin_moderate.tpl');
	echo $tpl->render(
		array(
			'lang_admin_common' => $lang_admin_common,
			'lang_admin_moderate' => $lang_admin_moderate,
			'lang_common' => $lang_common,
			'add_link' => panther_link($panther_url['admin_moderate_add']),
			'pagination' => paginate($num_pages, $page, $panther_url['admin_moderate'].'?'),
			'actions' => $actions,
		)
	);
}
require PANTHER_ROOT.'footer.php';