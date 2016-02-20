<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

if (!defined('PANTHER_ROOT'))
	exit('The constant PANTHER_ROOT must be defined and point to a valid Panther installation root directory.');

// Check if we're using an AJAX request (i.e. reputation) 
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
	define('PANTHER_AJAX_REQUEST', 1);

// Block prefetch requests
if (isset($_SERVER['HTTP_X_MOZ']) && $_SERVER['HTTP_X_MOZ'] == 'prefetch')
{
	header('HTTP/1.1 403 Prefetching Forbidden');
    
	// Send no-cache headers
	header('Expires: Thu, 21 Jul 1977 07:30:00 GMT'); // When yours truly first set eyes on this world! :)
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache'); // For HTTP/1.0 compatibility
	exit;
}

// Attempt to load the configuration file config.php
if (file_exists(PANTHER_ROOT.'include/config.php'))
	require PANTHER_ROOT.'include/config.php';

require PANTHER_ROOT.'include/lib/Twig/Autoloader.php';

// Register Twig autoloader
Twig_Autoloader::register();

// Load the functions script
require PANTHER_ROOT.'include/functions.php';

// Load UTF-8 functions
require PANTHER_ROOT.'include/utf8/utf8.php';

// Strip out "bad" UTF-8 characters
forum_remove_bad_characters();

// If PANTHER isn't defined, config.php is missing or corrupt
if (!defined('PANTHER'))
{
	header('Location: install.php');
	exit;
}

// Record the start time (will be used to calculate the generation time for the page)
$panther_start = microtime(true);

// Make sure PHP reports no errors apart from parse errors (this is handled by the error handler)
error_reporting(E_ALL);

// Sort out error handling stuff ...
register_shutdown_function('error_handler');
set_error_handler('error_handler');
set_exception_handler('error_handler');

// Force POSIX locale (to prevent functions such as strtolower() from messing up UTF-8 strings)
setlocale(LC_CTYPE, 'C');

// Turn off magic_quotes_runtime
if (get_magic_quotes_runtime())
	set_magic_quotes_runtime(0);

// Strip slashes from GET/POST/COOKIE/REQUEST/FILES (if magic_quotes_gpc is enabled)
if (!defined('FORUM_DISABLE_STRIPSLASHES') && get_magic_quotes_gpc())
{
	function stripslashes_array($array)
	{
		return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
	}

	$_GET = stripslashes_array($_GET);
	$_POST = stripslashes_array($_POST);
	$_COOKIE = stripslashes_array($_COOKIE);
	$_REQUEST = stripslashes_array($_REQUEST);
	if (is_array($_FILES))
	{
		// Don't strip valid slashes from tmp_name path on Windows
		foreach ($_FILES AS $key => $value)
			$_FILES[$key]['tmp_name'] = str_replace('\\', '\\\\', $value['tmp_name']);
		$_FILES = stripslashes_array($_FILES);
	}
}

// If the cache directory is not specified, we use the default setting
if (!defined('FORUM_CACHE_DIR'))
	define('FORUM_CACHE_DIR', PANTHER_ROOT.'cache/');

// ... and the same for the admin directory
if (!defined('PANTHER_ADMIN_DIR'))
	define('PANTHER_ADMIN_DIR', 'admin');

// Define a few commonly used constants
define('PANTHER_UNVERIFIED', 0);
define('PANTHER_ADMIN', 1);
define('PANTHER_MOD', 2);
define('PANTHER_GUEST', 4);
define('PANTHER_MEMBER', 6);

// Brute force stuff
define('ATTEMPT_DELAY', 1000);
define('TIMEOUT', 5000);

// Load cached config
if (file_exists(FORUM_CACHE_DIR.'cache_config.php'))
	include FORUM_CACHE_DIR.'cache_config.php';

// Load database class and connect
require PANTHER_ROOT.'include/database.php';

// Start a transaction
$db->start_transaction();

if (!defined('PANTHER_CONFIG_LOADED'))
{
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_config_cache();
	require FORUM_CACHE_DIR.'cache_config.php';
}

// Load cached forums
if (file_exists(FORUM_CACHE_DIR.'cache_extensions.php'))
	include FORUM_CACHE_DIR.'cache_extensions.php';

if (!defined('PANTHER_EXTENSIONS_LOADED'))
{
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_extensions_cache();
	require FORUM_CACHE_DIR.'cache_extensions.php';
}

// Check whether we should be using https
check_ssl_state();

// Load URL rewriting functions
if (file_exists(PANTHER_ROOT.'include/url/'.$panther_config['o_url_type']))
	require PANTHER_ROOT.'include/url/'.$panther_config['o_url_type'];
else
	require PANTHER_ROOT.'include/url/default.php';

// Load cached groups
if (file_exists(FORUM_CACHE_DIR.'cache_groups.php'))
	include FORUM_CACHE_DIR.'cache_groups.php';

if (!defined('PANTHER_GROUPS_LOADED'))
{
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';
    
	generate_groups_cache();
	require FORUM_CACHE_DIR.'cache_groups.php';
}

// Load cached forums
if (file_exists(FORUM_CACHE_DIR.'cache_forums.php'))
	include FORUM_CACHE_DIR.'cache_forums.php';

if (!defined('PANTHER_FORUMS_LOADED'))
{
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_forums_cache();
	require FORUM_CACHE_DIR.'cache_forums.php';
}

// Enable output buffering
if (!defined('PANTHER_DISABLE_BUFFERING'))
{
	// Should we use gzip output compression?
	if ($panther_config['o_gzip'] && extension_loaded('zlib'))
		ob_start('ob_gzhandler');
	else
		ob_start();
}

// Define standard date/time formats
$forum_time_formats = array($panther_config['o_time_format'], 'H:i:s', 'H:i', 'g:i:s a', 'g:i a');
$forum_date_formats = array($panther_config['o_date_format'], 'd-m-Y', 'Y-m-d', 'Y-d-m', 'm-d-Y', 'M j Y', 'jS M Y');

// Check/update/set cookie and fetch user info
$panther_user = array();
check_cookie($panther_user);

$loader = new Twig_Loader_Filesystem(PANTHER_ROOT.'include/templates');

$style_root = (($panther_config['o_style_path'] != 'style') ? $panther_config['o_style_path'] : PANTHER_ROOT.$panther_config['o_style_path']).'/'.$panther_user['style'].'/templates/';
$loader->addPath(PANTHER_ROOT.'include/templates/', 'core');

if (file_exists($style_root)) // If the custom style doesn't use templates, then this is silly
	$loader->addPath($style_root, 'style');

$tpl_manager = new Twig_Environment($loader, 
	array(
		'cache' => FORUM_CACHE_DIR.'templates/'.$panther_user['style'],
		'debug' => ($panther_config['o_debug_mode'] == '1') ? true : false,
	)
);

// Attempt to load the common language file
if (file_exists(PANTHER_ROOT.'lang/'.$panther_user['language'].'/common.php'))
	include PANTHER_ROOT.'lang/'.$panther_user['language'].'/common.php';
else
	error_handler(E_ERROR, 'There is no valid language pack \''.$panther_user['language'].'\' installed.', __FILE__, __LINE__);

// Load the updater
require PANTHER_ROOT.'include/updater.php';
$updater = new panther_updater($db, $panther_config, $lang_common);

// Load the task manager
require PANTHER_ROOT.'include/tasks.php';
$tasks = new task_scheduler($db, $panther_config, $updater);

// Check if we are to display a maintenance message
if ($panther_config['o_maintenance'] && $panther_user['g_id'] != PANTHER_ADMIN && !defined('PANTHER_TURN_OFF_MAINT') && !defined('IN_CRON'))
	maintenance_message();

// Load cached bans
if (file_exists(FORUM_CACHE_DIR.'cache_bans.php'))
	include FORUM_CACHE_DIR.'cache_bans.php';

if (!defined('PANTHER_BANS_LOADED'))
{
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';
    
	generate_bans_cache();
	require FORUM_CACHE_DIR.'cache_bans.php';
}

// Check if current user is banned
check_bans();

// Update online list
if (($panther_config['o_url_type'] == 'default' || strstr($_SERVER['PHP_SELF'], 'index.php') || strstr($_SERVER['PHP_SELF'], '/index.php')) && !defined('IN_CRON'))
	$online = update_users_online();

// Check to see if we logged in without a cookie being set
if ($panther_user['is_guest'] && isset($_GET['login']))
	message($lang_common['No cookie']);

($hook = get_extensions('common_after_validation')) ? eval($hook) : null;

// The maximum size of a post, in bytes, since the field is now MEDIUMTEXT this allows ~16MB but lets cap at 1MB...
if (!defined('PANTHER_MAX_POSTSIZE'))
	define('PANTHER_MAX_POSTSIZE', 1048576);

if (!defined('PANTHER_SEARCH_MIN_WORD'))
	define('PANTHER_SEARCH_MIN_WORD', 3);
if (!defined('PANTHER_SEARCH_MAX_WORD'))
	define('PANTHER_SEARCH_MAX_WORD', 20);

if (!defined('FORUM_MAX_COOKIE_SIZE'))
	define('FORUM_MAX_COOKIE_SIZE', 4048);