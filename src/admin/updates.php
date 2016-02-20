<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

define('JQUERY_REQUIRED', 1);

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
	if(!is_null($admins[$panther_user['id']]['admin_updates']))
	{
		if ($admins[$panther_user['id']]['admin_updates'] == '0')
			message($lang_common['No permission'], false, '403 Forbidden');
	}
}

check_authentication();

// Load the admin_update.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_update.php';

if (version_compare($panther_config['o_cur_version'], $updater->panther_updates['version'], '>='))
	message($lang_admin_update['no updates']);

$action = isset($_GET['action']) ? $_GET['action'] : '';
if ($action == 'install_update')
{
	$updater->download();
	$updater->install();
	exit;
}

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Update']);
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

generate_admin_menu('updates');

$tpl = load_template('admin_updates.tpl');
echo $tpl->render(
	array(
		'lang_admin_update' => $lang_admin_update,
		'panther_updates' => $updater->panther_updates,
		'released' => format_time($updater->panther_updates['released']),
		'updater' => $updater,
		'changelog' => $updater->panther_updates['changelog'],
		'form_action' => panther_link($panther_url['admin_updates']),
		'panther_config' => $panther_config,
	)
);

require PANTHER_ROOT.'footer.php';