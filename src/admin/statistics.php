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

if (!$panther_user['is_admmod'])
	message($lang_common['No permission'], false, '403 Forbidden');

check_authentication();

// Load the admin_index.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_index.php';

// Get the server load averages (if possible)
if (@file_exists('/proc/loadavg') && is_readable('/proc/loadavg'))
{
	// We use @ just in case
	$fh = @fopen('/proc/loadavg', 'r');
	$load_averages = @fread($fh, 64);
	@fclose($fh);

	if (($fh = @fopen('/proc/loadavg', 'r')))
	{
		$load_averages = fread($fh, 64);
		fclose($fh);
	}
	else
		$load_averages = '';

	$load_averages = @explode(' ', $load_averages);
	$server_load = isset($load_averages[2]) ? $load_averages[0].' '.$load_averages[1].' '.$load_averages[2] : $lang_admin_index['Not available'];
}
else if (!in_array(PHP_OS, array('WINNT', 'WIN32')) && preg_match('%averages?: ([0-9\.]+),?\s+([0-9\.]+),?\s+([0-9\.]+)%i', @exec('uptime'), $load_averages))
	$server_load = $load_averages[1].' '.$load_averages[2].' '.$load_averages[3];
else
	$server_load = $lang_admin_index['Not available'];

// Get number of current visitors
$ps = $db->select('online', 'COUNT(user_id)', array(), 'idle=0');
$num_online = $ps->fetchColumn();

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Server statistics']);
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

generate_admin_menu('index');

$render = array();
if ($panther_user['is_admin'])
{
	// Collect some additional info about MySQL
	$ps = $db->run('SHOW TABLE STATUS');
	$total_records = $total_size = 0;
	foreach ($ps as $status)
	{
		$total_records += $status['Rows'];
		$total_size += $status['Data_length'] + $status['Index_length'];
	}

	$total_size = file_size($total_size);

	// Check for the existence of various PHP opcode caches/optimizers
	if (function_exists('mmcache'))
		$php_accelerator = '<a href="http://'.$lang_admin_index['Turck MMCache link'].'">'.$lang_admin_index['Turck MMCache'].'</a>';
	else if (isset($_PHPA))
		$php_accelerator = '<a href="http://'.$lang_admin_index['ionCube PHP Accelerator link'].'">'.$lang_admin_index['ionCube PHP Accelerator'].'</a>';
	else if (ini_get('apc.enabled'))
		$php_accelerator ='<a href="http://'.$lang_admin_index['Alternative PHP Cache (APC) link'].'">'.$lang_admin_index['Alternative PHP Cache (APC)'].'</a>';
	else if (ini_get('zend_optimizer.optimization_level'))
		$php_accelerator = '<a href="http://'.$lang_admin_index['Zend Optimizer link'].'">'.$lang_admin_index['Zend Optimizer'].'</a>';
	else if (ini_get('eaccelerator.enable'))
		$php_accelerator = '<a href="http://'.$lang_admin_index['eAccelerator link'].'">'.$lang_admin_index['eAccelerator'].'</a>';
	else if (ini_get('xcache.cacher'))
		$php_accelerator = '<a href="http://'.$lang_admin_index['XCache link'].'">'.$lang_admin_index['XCache'].'</a>';
	else
		$php_accelerator = $lang_admin_index['NA'];

	$render = array(
		'PHP_OS' => PHP_OS,
		'php_version' => phpversion(),
		'phpinfo' => panther_link($panther_url['phpinfo']),
		'php_accelerator' => $php_accelerator,
		'db_version' => $db->get_version(),
		'total_records' => forum_number_format($total_records),
		'total_size' => $total_size
	);
}


$tpl = load_template('admin_statistics.tpl');
echo $tpl->render(
	array_merge(
			array(
			'lang_admin_index' => $lang_admin_index,
			'server_load' => $server_load,
			'num_online' => $num_online,
			'panther_user' => $panther_user,
		), $render
	)
);

require PANTHER_ROOT.'footer.php';