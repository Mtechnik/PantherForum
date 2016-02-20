<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

// Tell header.php we want jQuery included ...
define('JQUERY_REQUIRED', 1);

// ... and that we want the jQuery for the admin notes included.
define('ADMIN_INDEX', 1);

if (!defined('PANTHER'))
{
	define('PANTHER_ROOT', __DIR__.'/../');
	require PANTHER_ROOT.'include/common.php';
}
require PANTHER_ROOT.'include/common_admin.php';

if (($panther_user['is_admmod'] && $panther_user['g_mod_cp'] == '0' && !$panther_user['is_admin']) || !$panther_user['is_admmod'])
	message($lang_common['No permission'], false, '403 Forbidden');

check_authentication();

// Load the admin_index.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_index.php';

$action = isset($_GET['action']) ? $_GET['action'] : null;

// Check for upgrade
if ($action == 'check_upgrade')
{
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	$output = generate_update_cache();

	if (version_compare($panther_config['o_cur_version'], $output['version'], '>='))
		message($lang_admin_index['Running latest version message']);
	else
    		message(sprintf($lang_admin_index['New version available message'], '<a href="https://www.pantherforum.org/">Panther</a>'));
}
else if ($action == 'remove_install_file')
{
	if (!$panther_user['is_admin'])
		message($lang_common['No permission']);

	if (@unlink(PANTHER_ROOT.'install.php'))
		redirect(panther_link($panther_url['admin_index']), $lang_admin_index['Deleted install.php redirect']);
	else
		message($lang_admin_index['Delete install.php failed']);
}
else if ($action == 'phpinfo' && $panther_user['is_admin'])
{
	// Is phpinfo() a disabled function?
	if (strpos(strtolower((string) ini_get('disable_functions')), 'phpinfo') !== false)
		message($lang_admin_index['PHPinfo disabled message']);

	phpinfo();
	exit;
}
elseif ($action == 'save_notes')
{
	if (!defined('PANTHER_AJAX_REQUEST'))
		message($lang_common['No permission']);

	$notes = isset($_POST['notes']) ? panther_trim($_POST['notes']) : $lang_admin_index['admin notes'];
	$update = array(
		'conf_value' => $notes,
	);

	$db->update('config', $update, 'conf_name=\'o_admin_notes\'');

	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_config_cache();

	$db->end_transaction();
	exit;
}

$alerts = array();
if ($panther_user['is_admin'])
{
	if (is_file(PANTHER_ROOT.'install.php'))
		$alerts[] = sprintf($lang_admin_index['Install file exists'], '<a href="'.panther_link($panther_url['remove_install_file']).'">'.$lang_admin_index['Delete install file'].'</a>');

	foreach (get_admin_ids() as $admin)
	{
		if ($admin == '2') // No restrictions for the original administrator
			continue;

		$data = array(
			':admin'	=>	$admin,
		);

		$ps = $db->select('restrictions', 1, $data, 'admin_id=:admin');
		if (!$ps->rowCount())
		{
			$alerts[] = sprintf($lang_admin_index['No restrictions'], panther_link($panther_url['admin_restrictions']));
			break;
		}
	}

	$update_downloaded = (file_exists(PANTHER_ROOT.'include/updates/panther-update-patch-'.$updater->version_friendly($updater->panther_updates['version']).'.zip') ? true : false);

	if (version_compare($panther_config['o_cur_version'], $updater->panther_updates['version'], '<') && !$update_downloaded)
		$alerts[] = sprintf($lang_admin_index['New version'], $updater->panther_updates['version'], panther_link($panther_url['admin_updates']));

	if ($update_downloaded)
		$alerts[] = sprintf($lang_admin_index['update downloaded'], $updater->panther_updates['version'], panther_link($panther_url['admin_updates']));

	$avatar_path = ($panther_config['o_avatars_dir'] != '') ? $panther_config['o_avatars_path'].'/' : PANTHER_ROOT.$panther_config['o_avatars_path'].'/';
	$smiley_path = ($panther_config['o_smilies_dir'] != '') ? $panther_config['o_smilies_path'].'/' : PANTHER_ROOT.$panther_config['o_smilies_path'].'/';

	if (!forum_is_writable(FORUM_CACHE_DIR))
		$alerts[] = sprintf($lang_admin_index['Alert cache'], FORUM_CACHE_DIR);

	if (!forum_is_writable($avatar_path))
		$alerts[] = sprintf($lang_admin_index['Alert avatar'], $avatar_path);

	if (!forum_is_writable($smiley_path))
		$alerts[] = sprintf($lang_admin_index['Alert smilies'], $smiley_path);
}
$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Index']);
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

generate_admin_menu('index');

$tpl = load_template('admin_index.tpl');
echo $tpl->render(
	array(
		'lang_admin_common' => $lang_admin_common,
		'lang_admin_index' => $lang_admin_index,
		'form_action' => panther_link($panther_url['save_notes']),
		'panther_config' => $panther_config,
		'upgrade_link' => panther_link($panther_url['check_upgrade']),
		'stats_link' => panther_link($panther_url['admin_statistics']),
		'alerts' => $alerts,
	)
);

require PANTHER_ROOT.'footer.php';