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
	if(!is_null($admins[$panther_user['id']]['admin_permissions']))
	{
		if ($admins[$panther_user['id']]['admin_permissions'] == '0')
			message($lang_common['No permission'], false, '403 Forbidden');
	}
}

check_authentication();

// Load the admin_permissions.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_permissions.php';

if (isset($_POST['form_sent']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/permissions.php');
	$form = isset($_POST['form']) && is_array($_POST['form']) ? array_map('intval', $_POST['form']) : array();
	foreach ($form as $key => $input)
	{
		// Make sure the input is never a negative value
		if($input < 0)
			$input = 0;

		// Only update values that have changed
		if (array_key_exists('p_'.$key, $panther_config) && $panther_config['p_'.$key] != $input)
		{
			$update = array(
				'conf_value'	=>	$input,
			);
			
			$data = array(
				':conf_name'	=>	'p_'.$key,
			);
			
			$db->update('config', $update, 'conf_name=:conf_name', $data);
		}
	}

	// Regenerate the config cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_config_cache();
	redirect(panther_link($panther_url['admin_permissions']), $lang_admin_permissions['Perms updated redirect']);
}

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Permissions']);
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

generate_admin_menu('permissions');

$tpl = load_template('admin_permissions.tpl');
echo $tpl->render(
	array(
		'lang_admin_permissions' => $lang_admin_permissions,
		'panther_config' => $panther_config,
		'lang_admin_common' => $lang_admin_common,
		'form_action' => panther_link($panther_url['admin_permissions']),
		'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/permissions.php'),
	)
);

require PANTHER_ROOT.'footer.php';