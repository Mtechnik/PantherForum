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

// The plugin to load should be supplied via GET
$plugin = isset($_GET['plugin']) ? $_GET['plugin'] : '';
if (!preg_match('%^AM?P_(\w*?)\.php$%i', $plugin))
	message($lang_common['Bad request'], false, '404 Not Found');

// AP_ == Admins only, AMP_ == admins and moderators
$prefix = substr($plugin, 0, strpos($plugin, '_'));
if ($panther_user['g_moderator'] == '1' && $prefix == 'AP')
	message($lang_common['No permission'], false, '403 Forbidden');

// Make sure the file actually exists
if (!file_exists(PANTHER_ROOT.PANTHER_ADMIN_DIR.'/plugins/'.$plugin))
	message(sprintf($lang_admin_common['No plugin message'], $plugin));

// Construct REQUEST_URI if it isn't set
if (!isset($_SERVER['REQUEST_URI']))
	$_SERVER['REQUEST_URI'] = (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '').'?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '');

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], str_replace('_', ' ', substr($plugin, strpos($plugin, '_') + 1, -4)));
define('PANTHER_ACTIVE_PAGE', 'admin');

// Attempt to load the plugin. We don't use @ here to suppress error messages,
// because if we did and a parse error occurred in the plugin, we would only
// get the "blank page of death"
include PANTHER_ROOT.PANTHER_ADMIN_DIR.'/plugins/'.$plugin;
if (!defined('PANTHER_PLUGIN_LOADED'))
	message(sprintf($lang_admin_common['Plugin failed message'], $plugin));

require PANTHER_ROOT.'footer.php';