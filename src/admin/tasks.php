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

if (!$panther_user['is_admin'])
	message($lang_common['No permission'], false, '403 Forbidden');

if ($panther_user['id'] != '2')
{
	if(!is_null($admins[$panther_user['id']]['admin_tasks']))
	{
		if ($admins[$panther_user['id']]['admin_tasks'] == '0')
			message($lang_common['No permission'], false, '403 Forbidden');
	}
}

check_authentication();

// Load the admin_tasks.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_tasks.php';

// Add a task
if (isset($_POST['add_task']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/tasks.php');

	$title = isset($_POST['new_task_title']) ? panther_trim($_POST['new_task_title']) : '';
	$minute = isset($_POST['minute']) && $_POST['minute'] != '*' && $_POST['minute'] >= 0 && $_POST['minute'] <= 59 ? intval($_POST['minute']) : '*';
	$hour = isset($_POST['hour']) && $_POST['hour'] != '*' && $_POST['hour'] >= 0 && $_POST['hour'] <= 23 ? intval($_POST['hour']) : '*';
	$day = isset($_POST['day']) && $_POST['day'] != '*' && $_POST['day'] >= 1 && $_POST['day'] <= 31 ? intval($_POST['day']) : '*';
	$month = isset($_POST['month']) && $_POST['month'] != '*' && $_POST['month'] >= 1 && $_POST['month'] <= 12 ? intval($_POST['month']) : '*';
	$week_day = isset($_POST['week_day']) && $_POST['week_day'] != '*' && $_POST['week_day'] >= 0 && $_POST['week_day'] <= 6 ? intval($_POST['week_day']) : '*';
	$script = isset($_POST['script']) ? panther_trim($_POST['script']) : '';

	if (!file_exists(PANTHER_ROOT.'/include/tasks/'.$script.'.php') || !preg_match('/^[a-z-_0-9]+$/i', $script))
		message(sprintf($lang_admin_tasks['Not valid task'], $script));

	if (strlen($title) < 5)
		message($lang_admin_tasks['Too short title']);

	$insert = array(
		'title'	=>	$title,
		'next_run' => $tasks->get_next_run($minute, $hour, $day, $month, $week_day),
		'script' => $script,
		'minute' => $minute,
		'hour' => $hour,
		'day' => $day,
		'month' => $month,
		'week_day' => $week_day,
	);

	$db->insert('tasks', $insert);

	// If we're using proper cron jobs, then set it up
	if ($panther_config['o_task_type'] == '1' && function_exists('exec'))
		exec('echo -e "`crontab -l`\n'.$minute.' '.$hour.' '.$day.' '.$month.' '.$week_day.' '.substr(PANTHER_ROOT, 0, -3).'cron.php'.'" | crontab -');

	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';
	
	generate_task_cache();
	redirect(panther_link($panther_url['admin_tasks']), $lang_admin_tasks['Task added redirect']);
}

// Update a task
else if (isset($_POST['update']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/tasks.php');
	$id = isset($_POST['id']) ? intval($_POST['id']) : '';

	$title = isset($_POST['task_title']) ? panther_trim($_POST['task_title']) : '';
	$minute = isset($_POST['minute']) && $_POST['minute'] != '*' && $_POST['minute'] >= 0 && $_POST['minute'] <= 59 ? intval($_POST['minute']) : '*';
	$hour = isset($_POST['hour']) && $_POST['hour'] != '*' && $_POST['hour'] >= 0 && $_POST['hour'] <= 23 ? intval($_POST['hour']) : '*';
	$day = isset($_POST['day']) && $_POST['day'] != '*' && $_POST['day'] >= 1 && $_POST['day'] <= 31 ? intval($_POST['day']) : '*';
	$month = isset($_POST['month']) && $_POST['month'] != '*' && $_POST['month'] >= 1 && $_POST['month'] <= 12 ? intval($_POST['month']) : '*';
	$week_day = isset($_POST['week_day']) && $_POST['week_day'] != '*' && $_POST['week_day'] >= 0 && $_POST['week_day'] <= 6 ? intval($_POST['week_day']) : '*';
	$script = isset($_POST['script']) ? panther_trim($_POST['script']) : '';

	if (!file_exists(PANTHER_ROOT.'include/tasks/'.$script.'.php') || !preg_match('/^[a-z-_0-9]+$/i', $script))
		message(sprintf($lang_admin_tasks['Not valid task'], $script));

	if (strlen($title) < 5)
		message($lang_admin_tasks['Too short title']);

	$data = array(
		':id'	=>	$id,
	);

	$ps = $db->select('tasks', 'minute, hour, day, month, week_day, script', $data, 'id=:id');
	$cur_task = $ps->fetch();

	$update = array(
		'title'	=>	$title,
		'next_run' => $tasks->get_next_run($minute, $hour, $day, $month, $week_day),
		'script' => $script,
		'minute' => $minute,
		'hour' => $hour,
		'day' => $day,
		'month' => $month,
		'week_day' => $week_day,
	);

	$db->update('tasks', $update, 'id=:id', $data);
	if ($panther_config['o_task_type'] == '1' && function_exists('exec'))
	{
		$delete = $cur_task['minute']. ' '.$cur_task['hour'].' '.$cur_task['day'].' '.$cur_task['month'].' '.$cur_task['week_day'].' '.substr(PANTHER_ROOT, 0, -3).'cron.php';
		exec('crontab -l', $cron_jobs);

		$cron = array_search($delete, $cron_jobs);
		if ($cron !== false)
		{
			exec('crontab -r');
			unset($cron_jobs[$cron]);
		}
		else
			message($lang_admin_tasks['Unable to remove old task']);

		foreach ($cron_jobs as $cur_job) // We can only remove all cron jobs. So we now need to re-create them all ...
			exec('echo -e "`crontab -l`\n'.$cur_job.'" | crontab -');

		exec('echo -e "`crontab -l`\n'.$minute.' '.$hour.' '.$day.' '.$month.' '.$week_day.' '.substr(PANTHER_ROOT, 0, -3).'cron.php'.'" | crontab -');
	}

	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_task_cache();
	redirect(panther_link($panther_url['admin_tasks']), $lang_admin_tasks['Task updated redirect']);
}

// Remove a task
else if (isset($_POST['remove']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/tasks.php');
	$id = isset($_POST['id']) ? intval($_POST['id']) : '';
	$data = array(
		':id'	=>	$id,
	);

	$ps = $db->select('tasks', 'minute, hour, day, month, week_day, script', $data, 'id=:id');
	$cur_task = $ps->fetch();

	$db->delete('tasks', 'id=:id', $data);
	if ($panther_config['o_task_type'] == '1' && function_exists('exec'))
	{
		$delete = $cur_task['minute']. ' '.$cur_task['hour'].' '.$cur_task['day'].' '.$cur_task['month'].' '.$cur_task['week_day'].' '.substr(PANTHER_ROOT, 0, -3).'cron.php';

		exec('crontab -l', $cron_jobs);
		$cron = array_search($delete, $cron_jobs);
		if ($cron !== false)
		{
			exec('crontab -r');
			unset($cron_jobs[$cron]);
		}
		else
			message($lang_admin_tasks['Unable to remove']);

		foreach ($cron_jobs as $cur_job)
			exec('echo -e "`crontab -l`\n'.$cur_job.'" | crontab -');
	}

	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_task_cache();
	redirect(panther_link($panther_url['admin_tasks']),  $lang_admin_tasks['Task removed redirect']);
}
// Upload a task
else if (isset($_POST['upload_task']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/tasks.php');
	if (!isset($_FILES['req_file']))
		message($lang_admin_tasks['No file']);
	
	$uploaded_file = $_FILES['req_file'];
	
	// Make sure the upload went smooth
	if (isset($uploaded_file['error']))
	{
		switch ($uploaded_file['error'])
		{
			case 1:	// UPLOAD_ERR_INI_SIZE
			case 2:	// UPLOAD_ERR_FORM_SIZE
				message($lang_admin_tasks['Too large ini']);
			break;
			case 3:	// UPLOAD_ERR_PARTIAL
				message($lang_admin_tasks['Partial upload']);
			break;
			case 4:	// UPLOAD_ERR_NO_FILE
				message($lang_admin_tasks['No file']);
			break;
			case 6:	// UPLOAD_ERR_NO_TMP_DIR
				message($lang_admin_tasks['No tmp directory']);
			break;
			default:
				// No error occured, but was something actually uploaded?
				if ($uploaded_file['size'] == 0)
					message($lang_admin_tasks['No file']);
			break;
		}
	}
	
	if (is_uploaded_file($uploaded_file['tmp_name']))
	{
		$filename = $uploaded_file['name'];
		if (!preg_match('/^[a-z0-9-_]+\.(php)$/i', $uploaded_file['name']))
			message($lang_admin_tasks['Bad type']);
		
		// Make sure there is no file already under this name
		if (file_exists(PANTHER_ROOT.'include/tasks/'.$filename))
			message(sprintf($lang_admin_tasks['Task exists'], $filename));
	
		// Move the file to the addons directory.
		if (!@move_uploaded_file($uploaded_file['tmp_name'], PANTHER_ROOT.'include/tasks/'.$filename))
			message($lang_admin_tasks['Move failed']);

		@chmod(PANTHER_ROOT.'include/tasks/'.$filename, 0644);
	}
	else
		message($lang_admin_tasks['Unknown failure']);

	redirect(panther_link($panther_url['admin_tasks']), $lang_admin_tasks['Successful Upload Task']);
}

else if (isset($_POST['delete_task']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/tasks.php');
	$tasks = isset($_POST['del_tasks']) && is_array($_POST['del_tasks']) ? array_map('panther_trim', $_POST['del_tasks']) : array();

	if (empty($tasks))
		message($lang_admin_tasks['No tasks']);

	foreach ($tasks as $task)
	{
		// Make sure it doesn't look suspicious
		if (file_exists(PANTHER_ROOT.'include/tasks/'.$task.'.php') && (preg_match('/^[a-z0-9-_]+$/i', $task)) && !preg_match('/^[\/\.@[{}]!"£\|<>:]+$/i', $task))
			unlink(PANTHER_ROOT.'include/tasks/'.$task.'.php');
	}

	redirect(panther_link($panther_url['admin_tasks']), $lang_admin_tasks['Task removed redirect']);
}

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Tasks']);
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

generate_admin_menu('tasks');

if (isset($_GET['edit']))
{
	$id = intval($_GET['edit']);
	$data = array(
		':id' => $id,
	);

	$ps = $db->select('tasks', 'title, minute, hour, day, month, week_day, script', $data, 'id=:id');
	if (!$ps->rowCount())
		message($lang_common['Bad request']);

	$cur_task = $ps->fetch();

	$options = array();
	$tasks = array_diff(scandir(PANTHER_ROOT.'include/tasks'), array('.', '..'));
	foreach ($tasks as $cur_file)
		$options[] = array('option' => substr($cur_file, 0, -4), 'title' => ucwords(str_replace('_', ' ', substr($cur_file, 0, -4))));

	$tpl = load_template('edit_task.tpl');
	echo $tpl->render(
		array(
			'lang_admin_tasks' => $lang_admin_tasks,
			'cur_task' => $cur_task,
			'lang_common' => $lang_common,
			'form_action' => panther_link($panther_url['admin_tasks']),
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/tasks.php'),
			'id' => $id,
			'tasks' => $options,
		)
	);
}
else if (isset($_GET['delete']))
{
	$id = intval($_GET['delete']);
	$data = array(
		':id' => $id,
	);

	$ps = $db->select('tasks', 1, $data, 'id=:id');
	if (!$ps->rowCount())
		message($lang_common['Bad request']);

	$tpl = load_template('delete_task.tpl');
	echo $tpl->render(
		array(
			'lang_admin_tasks' => $lang_admin_tasks,
			'lang_common' => $lang_common,
			'form_action' => panther_link($panther_url['admin_tasks']),
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/tasks.php'),
			'id' => $id,
		)
	);
}
else
{
	$configured_tasks = array();
	$ps = $db->select('tasks', 'id, title, minute, hour, day, month, week_day, script, next_run', array(), '', 'id');
	foreach ($ps as $cur_task)
		$configured_tasks[] = array(
			'minute' => $cur_task['minute'],
			'hour' => $cur_task['hour'],
			'day' => $cur_task['day'],
			'month' => $cur_task['month'],
			'week_day' => $cur_task['week_day'],
			'delete_link' => panther_link($panther_url['delete_task'], array($cur_task['id'])),
			'edit_link' => panther_link($panther_url['edit_task'], array($cur_task['id'])),
			'next_run' => format_time($cur_task['next_run']),
			'title' => $cur_task['title'],
		);

	$options = array();
	$tasks = array_diff(scandir(PANTHER_ROOT.'include/tasks'), array('.', '..'));
	foreach ($tasks as $cur_task)
		$options[] = array(
			'title' => ucwords(str_replace('_', ' ', substr($cur_task, 0, -4))),
			'file' => substr($cur_task, 0, -4),
		);

	$tpl = load_template('admin_tasks.tpl');
	echo $tpl->render(
		array(
			'lang_admin_tasks' => $lang_admin_tasks,
			'lang_admin_common' => $lang_admin_common,
			'form_action' => panther_link($panther_url['admin_tasks']),
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/tasks.php'),
			'configured_tasks' => $configured_tasks,
			'tasks' => $options,
		)
	);
}

require PANTHER_ROOT.'footer.php';