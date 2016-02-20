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

if ($panther_user['is_guest'])
	message($lang_common['No permission']);

if ($panther_config['o_private_messaging'] == '0')
	message($lang_common['No permission']);

if (file_exists(PANTHER_ROOT.'lang/'.$panther_user['language'].'/pms.php'))
	require PANTHER_ROOT.'lang/'.$panther_user['language'].'/pms.php';
else
	require PANTHER_ROOT.'lang/English/pms.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$box_id = isset($_GET['id']) ? intval($_GET['id']) : 2;	// Default to your inbox

if (isset($_POST['delete']))
{
	if (!isset($_POST['topics']))
		message($lang_pm['Select more than one topic']);

	$topics = isset($_POST['topics']) && is_array($_POST['topics']) ? array_map('intval', $_POST['topics']) : array_map('intval', explode(',', $_POST['topics']));
	
	if (empty($topics))
		message($lang_pm['Select more than one topic']);

	if (isset($_POST['delete_comply']))
	{
		confirm_referrer('pms_inbox.php');

		$markers = array();
		$data = array($panther_user['id']);
		for ($i = 0; $i < count($topics); $i++)
		{
			$markers[] = '?';
			$data[] = $topics[$i];
		}

		$ps = $db->run('SELECT SUM(c.num_replies) FROM '.$db->prefix.'conversations AS c INNER JOIN '.$db->prefix.'pms_data AS cd ON c.id=cd.topic_id AND cd.user_id=? WHERE c.id IN ('.implode(',', $markers).')', $data);
		$num_pms = ($ps->fetchColumn() + count($markers));	// The number of topic posts and the number of replies from all topics

		$db->run('UPDATE '.$db->prefix.'pms_data SET deleted=1 WHERE user_id=? AND topic_id IN ('.implode(',', $markers).')', $data);
		$update = array(
			':markers'	=>	$num_pms,
			':id'	=>	$panther_user['id'],
		);

		$db->run('UPDATE '.$db->prefix.'users SET num_pms=num_pms-:markers WHERE id=:id', $update);
		unset($data[0]);
		
		// Now check if anyone left in the conversation has any of these topics undeleted. If so, then we leave them. Otherwise, actually delete them.
		foreach (array_values($data) as $tid)
		{
			$delete = array(
				':id'	=>	$tid,
			);

			$ps = $db->select('pms_data', 1, $delete, 'topic_id=:id AND deleted=0');
			if ($ps->rowCount())	// People are still left
				continue;

			$db->delete('pms_data', 'topic_id=:id', $delete);
			$db->delete('conversations', 'id=:id', $delete);
			$db->delete('messages', 'topic_id=:id', $delete);
		}
		
		($hook = get_extensions('inbox_after_message_deletion')) ? eval($hook) : null;

		redirect(panther_link($panther_url['inbox']), $lang_pm['Messages deleted']);
	}
	else
	{
		$page_title = array($panther_config['o_board_title'], $lang_common['PM'], $lang_pm['PM Inbox']);
		define('PANTHER_ACTIVE_PAGE', 'pm');
		require PANTHER_ROOT.'header.php';
		
		$tpl = load_template('delete_messages.tpl');
		echo $tpl->render(
			array(
				'lang_common' => $lang_common,
				'lang_pm' => $lang_pm,
				'index_link' => panther_link($panther_url['index']),
				'inbox_link' => panther_link($panther_url['inbox']),
				'message_link' => panther_link($panther_url['send_message']),
				'pm_menu' => generate_pm_menu(),
				'topics' => $topics,
				'csrf_token' => generate_csrf_token(),
			)
		);

		require PANTHER_ROOT.'footer.php';
	}
}
elseif (isset($_POST['move']))
{
	if (!isset($_POST['topics']))
		message($lang_pm['Select more than one topic']);

	$topics	= isset($_POST['topics']) && is_array($_POST['topics']) ? array_map('intval', $_POST['topics']) : array_map('intval', explode(',', $_POST['topics']));

	if (empty($topics))
		message($lang_pm['Select more than one topic']);
		
	if (isset($_POST['move_comply']))
	{
		confirm_referrer('pms_inbox.php');
		$folder = isset($_POST['folder']) ? intval($_POST['folder']) : 1;

		$markers = array();
		$update = array($folder);
		for ($i = 0; $i < count($topics); $i++)
		{
			$markers[] = '?';
			$update[] = $topics[$i];
		}

		$data = array(
			':fid'	=>	$folder,
			':uid'	=>	$panther_user['id'],
		);

		$ps = $db->select('folders', 1, $data, 'id=:fid AND user_id=:uid OR user_id=1');
		if (!$ps->rowCount())
			message($lang_common['No permission']);	// Then they don't have permission to move them to this folder
		
		($hook = get_extensions('inbox_before_move_messages')) ? eval($hook) : null;

		$update[] = $panther_user['id'];
		$ps = $db->run('UPDATE '.$db->prefix.'pms_data SET folder_id=? WHERE topic_id IN ('.implode(',', $markers).') AND user_id=?', $update);
		redirect(panther_link($panther_url['inbox']), $lang_pm['Messages moved']);
	}
	
	$data = array(
		':uid'	=>	$panther_user['id'],
	);

	$ps = $db->select('folders', 'name, id', $data, 'user_id=:uid OR user_id=1', 'id, user_id ASC');
	if (!$ps->rowCount())
		message($lang_pm['No available folders']);

	$folders = array();
	foreach ($ps as $folder)
		$folders[] = array('id' => $folder['id'], 'name' => $folder['name']);

	($hook = get_extensions('inbox_before_move_messages_header')) ? eval($hook) : null;

	$page_title = array($panther_config['o_board_title'], $lang_common['PM'], $lang_pm['PM Inbox']);
	define('PANTHER_ACTIVE_PAGE', 'pm');
	require PANTHER_ROOT.'header.php';

	$tpl = load_template('move_messages.tpl');
	echo $tpl->render(
		array(
			'lang_common' => $lang_common,
			'lang_pm' => $lang_pm,
			'index_link' => panther_link($panther_url['index']),
			'inbox_link' => panther_link($panther_url['inbox']),
			'message_link' => panther_link($panther_url['send_message']),
			'csrf_token' => generate_csrf_token(),
			'pm_menu' => generate_pm_menu(),
			'folders' => $folders,
			'topics' => $topics,
		)
	);

	require PANTHER_ROOT.'footer.php';
}

$data = array(
	':uid'	=>	$panther_user['id'],
	':fid'	=>	$box_id,
);

// Check we own this box
$ps = $db->select('folders', 'name', $data, '(user_id=:uid OR user_id=1) AND id=:fid');
if (!$ps->rowCount())
	message($lang_common['Bad request']);
else
	$box_name = $ps->fetchColumn();

$data = array(
	':fid'	=>	$box_id,
	':uid'	=>	$panther_user['id'],
);

$ps = $db->run('SELECT COUNT(c.id) FROM '.$db->prefix.'conversations AS c INNER JOIN '.$db->prefix.'pms_data AS cd ON c.id=cd.topic_id WHERE cd.user_id=:uid AND cd.deleted=0 AND (cd.folder_id=:fid '.(($box_id == 1) ? 'OR cd.viewed=0)' : ')'), $data);
$messages = $ps->fetchColumn();

$num_pages = ceil($messages / $panther_user['disp_topics']);

$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);
$start_from = $panther_user['disp_topics'] * ($p - 1);

$data = array(
	':uid'	=>	$panther_user['id'],
	':fid'	=>	$box_id,
	':start'=>	$start_from,
);

$ps = $db->run('SELECT c.id, c.subject, c.poster, c.poster_id, c.num_replies, c.last_post, c.last_poster, c.last_post_id, cd.viewed, u.group_id AS poster_gid, u.email, u.use_gravatar, l.id AS last_poster_id, l.group_id AS last_poster_gid FROM '.$db->prefix.'conversations AS c INNER JOIN '.$db->prefix.'pms_data AS cd ON c.id=cd.topic_id LEFT JOIN '.$db->prefix.'users AS u ON u.id=c.poster_id LEFT JOIN '.$db->prefix.'users AS l ON l.username=c.last_poster WHERE cd.user_id=:uid AND cd.deleted=0 AND (cd.folder_id=:fid '.(($box_id == 1) ? 'OR cd.viewed=0)' : ')').'ORDER BY c.last_post DESC LIMIT :start, '.$panther_user['disp_topics'], $data);

define('COMMON_JAVASCRIPT', true);
$page_title = array($panther_config['o_board_title'], $lang_common['PM'], $lang_pm['PM Inbox']);
define('PANTHER_ALLOW_INDEX', 1);
define('PANTHER_ACTIVE_PAGE', 'pm');
require PANTHER_ROOT.'header.php';

($hook = get_extensions('inbox_before_display')) ? eval($hook) : null;

$topics = array();
foreach ($ps as $cur_topic)
{
	$data = array(
			':tid'	=>	$cur_topic['id']
	);

	$users = array();
	$ps1 = $db->run('SELECT cd.user_id AS id, u.username, u.group_id FROM '.$db->prefix.'pms_data AS cd INNER JOIN '.$db->prefix.'users AS u ON cd.user_id=u.id WHERE topic_id=:tid', $data);
	foreach ($ps1 as $user_data)
		$users[] = colourize_group($user_data['username'], $user_data['group_id'], $user_data['id']);

	if ($panther_config['o_censoring'] == '1')
		$cur_topic['subject'] = censor_words($cur_topic['subject']);

	$num_pages_topic = ceil(($cur_topic['num_replies'] + 1) / $panther_user['disp_posts']);
	$topics[] = array(
		'viewed' => $cur_topic['viewed'],
		'id' => $cur_topic['id'],
		'poster' => colourize_group($cur_topic['poster'], $cur_topic['poster_gid'], $cur_topic['poster_id']),
		'users' => $users,
		'last_post_avatar' => generate_avatar_markup($cur_topic['last_poster_id'], $cur_topic['email'], $cur_topic['use_gravatar'], array(32, 32)),
		'last_post_link' => panther_link($panther_url['pms_post'], array($cur_topic['last_post_id'])),
		'last_post' => format_time($cur_topic['last_post']),
		'last_poster' => colourize_group($cur_topic['last_poster'], $cur_topic['last_poster_gid'], $cur_topic['last_poster_id']),
		'num_replies' => forum_number_format($cur_topic['num_replies']),
		'new_post_link' => panther_link($panther_url['pms_new'], array($cur_topic['id'])),
		'pagination' => paginate($num_pages_topic, -1, $panther_url['pms_paginate'], array($cur_topic['id'])),
		'num_pages' => $num_pages_topic,
		'url' => panther_link($panther_url['pms_view'], array($cur_topic['id'])),
		'subject' => $cur_topic['subject']
	);
}

$tpl = load_template('inbox.tpl');
echo $tpl->render(
	array(
		'lang_common' => $lang_common,
		'lang_pm' => $lang_pm,
		'index_link' => panther_link($panther_url['index']),
		'inbox_link' => panther_link($panther_url['inbox']),
		'box_link' => panther_link($panther_url['box'], array($box_id)),
		'message_link' => panther_link($panther_url['send_message']),
		'box_name' => $box_name,
		'pm_menu' => generate_pm_menu($box_id),
		'csrf_token' => generate_csrf_token(),
		'page' => $p,
		'pagination' => paginate($num_pages, $p, $panther_url['box'], array($box_id)),
		'box_id' => $box_id,
		'topics' => $topics,
	)
);

require PANTHER_ROOT.'footer.php';