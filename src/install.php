<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

// The Panther version this script installs
define('FORUM_VERSION', '1.1.0');

define('MIN_PHP_VERSION', '5.3.0');
define('MIN_MYSQL_VERSION', '5.0.15');
define('PANTHER_SEARCH_MIN_WORD', 3);
define('PANTHER_SEARCH_MAX_WORD', 20);

define('PANTHER_ROOT', __DIR__.'/');

// Send the Content-type header in case the web server is setup to send something else
header('Content-type: text/html; charset=utf-8');

$default_style = 'Pantherone';
require PANTHER_ROOT.'include/lib/Twig/Autoloader.php';

// Register Twig autoloader
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem(PANTHER_ROOT.'include/templates');
$loader->addPath(PANTHER_ROOT.'include/templates/', 'core');
$tpl_manager = new Twig_Environment($loader, 
	array(
		'cache' => PANTHER_ROOT.'cache/templates/'.$default_style,
		'debug' => false,
	)
);

// Load the functions script
require PANTHER_ROOT.'include/functions.php';

// Load UTF-8 functions
require PANTHER_ROOT.'include/utf8/utf8.php';

// Strip out "bad" UTF-8 characters
forum_remove_bad_characters();

// Disable error reporting for uninitialized variables
error_reporting(E_ALL);

// Force POSIX locale (to prevent functions such as strtolower() from messing up UTF-8 strings)
setlocale(LC_CTYPE, 'C');

// Turn off magic_quotes_runtime
if (get_magic_quotes_runtime())
	set_magic_quotes_runtime(0);

// Strip slashes from GET/POST/COOKIE (if magic_quotes_gpc is enabled)
if (get_magic_quotes_gpc())
{
	function stripslashes_array($array)
	{
		return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
	}

	$_GET = stripslashes_array($_GET);
	$_POST = stripslashes_array($_POST);
	$_COOKIE = stripslashes_array($_COOKIE);
	$_REQUEST = stripslashes_array($_REQUEST);
}

// Turn off PHP time limit
@set_time_limit(0);

// If we've been passed a default language, use it
$install_lang = isset($_POST['install_lang']) ? panther_trim($_POST['install_lang']) : 'English';

// Make sure we got a valid language string
$install_lang = preg_replace('%[\.\\\/]%', '', $install_lang);

// If such a language pack doesn't exist, or isn't up-to-date enough to translate this page, default to English
if (!file_exists(PANTHER_ROOT.'lang/'.$install_lang.'/install.php'))
	$install_lang = 'English';

require PANTHER_ROOT.'lang/'.$install_lang.'/install.php';

if (file_exists(PANTHER_ROOT.'include/config.php'))
{
	// Check to see whether Panther is already installed
	include PANTHER_ROOT.'include/config.php';

	// If PANTHER is defined, config.php is probably valid and thus the software is installed
	if (defined('PANTHER'))
		exit($lang_install['Already installed']);
}

// Define PANTHER because database.php requires it
define('PANTHER', 1);

// If the cache directory is not specified, we use the default setting
if (!defined('FORUM_CACHE_DIR'))
	define('FORUM_CACHE_DIR', PANTHER_ROOT.'cache/');

// Make sure we are running at least MIN_PHP_VERSION
if (!function_exists('version_compare') || version_compare(PHP_VERSION, MIN_PHP_VERSION, '<'))
	exit(sprintf($lang_install['You are running error'], 'PHP', PHP_VERSION, FORUM_VERSION, MIN_PHP_VERSION));

//
// Generate output to be used for config.php
//
function generate_config_file($config)
{
	return '<?php'."\n\n\$config = ".var_export($config, true).";\n\ndefine('PANTHER', 1);\n";
}

function is_valid_email($email)
{
	if (strlen($email) > 80)
		return false;

	return preg_match('%^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|("[^"]+"))@((\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\])|(([a-zA-Z\d\-]+\.)+[a-zA-Z]{2,}))$%', $email);
}

if (isset($_POST['generate_config']))
{
	header('Content-Type: text/x-delimtext; name="config.php"');
	header('Content-disposition: attachment; filename=config.php');

	$config = array(
		'host' => isset($_POST['db_host']) ? $_POST['db_host'] : '',
		'prefix' => isset($_POST['db_prefix']) ? $_POST['db_prefix'] : '',
		'username' => isset($_POST['db_username']) ? $_POST['db_username'] : '',
		'password' => isset($_POST['db_password']) ? $_POST['db_password'] : '',
		'name' => isset($_POST['db_name']) ? $_POST['db_name'] : '',
		'p_connect' => false,
	);

	$cookie_name = isset($_POST['cookie_name']) ? panther_trim($_POST['cookie_name']) : 'panther_cookie_'.random_key(6, false, true);
	$cookie_seed = isset($_POST['cookie_seed']) ? panther_trim($_POST['cookie_seed']) : random_key(16, false, true);

	echo generate_config_file($config);
	exit;
}

if (!isset($_POST['form_sent']))
{
	// Make an educated guess regarding base_url
	$base_url  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';	// protocol
	$base_url .= preg_replace('%:(80|443)$%', '', $_SERVER['HTTP_HOST']);							// host[:port]
	$base_url .= str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));							// path

	if (substr($base_url, -1) == '/')
		$base_url = substr($base_url, 0, -1);
	
	$config = array(
		'host' => 'localhost',
		'prefix' => 'panther'.random_key(5, false, true).'_',
		'username' => '',
		'password' => '',
		'db_name' => '',
		'p_connect' => false,
	);

	$username = $email = '';
	$title = $lang_install['My Panther Forum'];
	$description = '<p><span>'.$lang_install['Description'].'</span></p>';
	$default_lang = $install_lang;
	$panther_config = array('o_style_path' => 'style');
}
else
{
	$config = array(
		'host' => isset($_POST['req_db_host']) ? panther_trim($_POST['req_db_host']) : '',
		'prefix' => isset($_POST['db_prefix']) ? panther_trim($_POST['db_prefix']) : '',
		'username' => isset($_POST['db_username']) ? $_POST['db_username'] : '',
		'password' => isset($_POST['db_password']) ? $_POST['db_password'] : '',
		'db_name' => isset($_POST['req_db_name']) ? $_POST['req_db_name'] : '',
		'p_connect' => false,
	);

	$username = panther_trim($_POST['req_username']);
	$email = strtolower(panther_trim($_POST['req_email']));
	$password1 = panther_trim($_POST['req_password1']);
	$password2 = panther_trim($_POST['req_password2']);
	$title = panther_trim($_POST['req_title']);
	$description = panther_trim($_POST['desc']);
	$base_url = panther_trim($_POST['req_base_url']);
	$default_lang = panther_trim($_POST['req_default_lang']);
	$default_style = panther_trim($_POST['req_default_style']);
	$cookie_name = panther_trim($_POST['cookie_name']);
	$cookie_seed = panther_trim($_POST['cookie_seed']);
	$panther_config = array('o_style_path' => 'style', 'o_debug_mode' => 1, 'o_show_queries' => 1);
	$alerts = array();

	// Make sure base_url doesn't end with a slash
	if (substr($base_url, -1) == '/')
		$base_url = substr($base_url, 0, -1);

	// Validate username and passwords
	if (panther_strlen($username) < 2)
		$alerts[] = $lang_install['Username 1'];
	else if (panther_strlen($username) > 25) // This usually doesn't happen since the form element only accepts 25 characters
		$alerts[] = $lang_install['Username 2'];
	else if (!strcasecmp($username, 'Guest'))
		$alerts[] = $lang_install['Username 3'];
	else if (preg_match('%[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}%', $username) || preg_match('%((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))%', $username))
		$alerts[] = $lang_install['Username 4'];
	else if ((strpos($username, '[') !== false || strpos($username, ']') !== false) && strpos($username, '\'') !== false && strpos($username, '"') !== false)
		$alerts[] = $lang_install['Username 5'];
	else if (preg_match('%(?:\[/?(?:b|u|i|h|colou?r|quote|code|img|url|email|list)\]|\[(?:code|quote|list)=)%i', $username))
		$alerts[] = $lang_install['Username 6'];

	if (panther_strlen($password1) < 6)
		$alerts[] = $lang_install['Short password'];
	else if ($password1 != $password2)
		$alerts[] = $lang_install['Passwords not match'];

	if (!is_valid_email($email))
		$alerts[] = $lang_install['Wrong email'];

	if ($title == '')
		$alerts[] = $lang_install['No board title'];

	$languages = forum_list_langs();
	if (!in_array($default_lang, $languages))
		$alerts[] = $lang_install['Error default language'];

	$styles = forum_list_styles();
	if (!in_array($default_style, $styles))
		$alerts[] = $lang_install['Error default style'];
}

// Check if the cache directory is writable
if (!forum_is_writable(FORUM_CACHE_DIR))
	$alerts[] = sprintf($lang_install['Alert cache'], FORUM_CACHE_DIR);

// Check if default avatar directory is writable
if (!forum_is_writable(PANTHER_ROOT.'assets/images/avatars/'))
	$alerts[] = sprintf($lang_install['Alert avatar'], PANTHER_ROOT.'assets/images/avatars/');

if (!isset($_POST['form_sent']) || !empty($alerts))
{
	// Determine available database extensions
	$db_extensions = array();
	$panther_drivers = array(
		'mysql' => 'MySQL',
	);

	if (!class_exists('PDO'))	
		error_handler(E_ERROR, $lang_install['No DB extensions'], __FILE__, __LINE__);

	$drivers = PDO::getAvailableDrivers();
	foreach ($drivers as $driver)
	{
		if (in_array($driver, array_keys($panther_drivers)))
			$db_extensions[] = array($driver, $panther_drivers[$driver]);
	}

	if (empty($db_extensions))
		error($lang_install['No DB extensions']);

	// Fetch a list of installed languages
	$languages = forum_list_langs();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $lang_install['Panther Installation'] ?></title>
<style type="text/css">
html {
  font-family: sans-serif;
  -ms-text-size-adjust: 100%;
  -webkit-text-size-adjust: 100%;
  	box-sizing: border-box;
}

body { 
    font-family: Arial, Verdana, sans-serif; 
	font-size: 14px;
	background: #e4e4e4;
	color: #383838;
    margin: 0;
}

*,
*:before,
*:after {
	box-sizing: inherit;
}

.clearfix:before,
.clearfix:after,
.brd-header:before, 
.brd-header:after,
.brd-footer:before,
.brd-footer:after,
.brd-main:before,
.brd-main:after,
.box .inbox:before,
.box .inbox:after { 
  content: ""; 
  display: block; 
  height: 0; 
  overflow: hidden; 
}  

.clearfix:after,
.brd-header:after,
.brd-footer:after,
.brd-main:after,
.box .inbox:after { 
  clear: both; 
}  

.clearfix { 
  zoom: 1; 
}

.brd-main, .brd-header {
	width: 800px;
	margin: auto;
}

.row, .box, .col {
	float: left;
	width: 100%;
}
.row {
	margin: 0 0 10px 0;
}
.col {
	padding: 5px;
}

.brd-header {
    background: #34495e;
    padding: 30px;
    margin: 20px auto;
    color: white;
}
.brd-header p {
   margin: 0;
   padding: 0;
}
.brd-header h1 {
	font-size: 30px;
   margin: 0;
   padding: 0 0 5px 0;
   text-transform: uppercase;
}

.brd-main {
    background: none;
	border: 0;
	padding: 0;
}

h2 {
    background-color: #2c3e50;
    padding: 15px;
    color: white;
    font-size: 18px;
    font-weight: 400;
    font-family: lato;
	width: 100%;
	margin: 0 0 20px 0;
}

.blockinfo {
    margin: 0;
    background: #2980B9;
    display: flex;
    align-items: center;
    padding: 15px;
    color: white;
}
.blockinfo h3{
    font-size: 16px;
    font-weight: 400;
    width: 100%;
    margin: 0;
    width: 40%;
			width: 40%;
}

.blockinfo p {
	margin: 0;
	width: 60%;
	text-align: right;

}
.box {
	margin: 0;
}

.inbox {
	width: 90%;
	margin: auto;
	padding: 20px 0;
}
.row .col {
	width: 100%;
}
.block {
    float: left;
    width: 100%;
    margin: 0 0 20px 0;
    background: #f7f7f7;
    padding: 1px;
}

.boxtitle {
	background: #34495E;
    font-size: 12px;
    font-weight: bold;
    padding: 5px 15px;
    margin: 0 0 1px 0;
    width: 100%;
    color: white;
}
.title {
    font-size: 14px;
    padding: 0 0 3px 0;
    float: left;
    width: 100%;
	font-weight: bold;
}

p.boxinfo {
	margin: 0;
    background: #D8D8D8;
    padding: 5px 10px;
}

input[type="submit"] {
    cursor: pointer;
}
	
input, select {
	float: left;
    width: 100%;
    border: none;
    border-radius: 0;
    outline: none;
    padding: 8px 10px;
    font-weight: normal;
    border: 1px solid #CACACA;
    transition: border 0.5s;
    color: #383838;
    font-size: 14px;
}

.required  {
	color: #e74c3c;
	font-weight: normal;
}

input.start-install {
    background: #27ae60;
    font-size: 18px;
    text-transform: uppercase;
    font-weight: bold;
    padding: 50px 10px;
    float: left;
    margin: 0 0 20px 0;
    width: 100%;
	color: white;
	letter-spacing: 1px;
	border: 0;
}

input.start-install:hover {
    background: #187D43;

}
input.start-install:focus {
    background: #0C582C;
}
</style>
<script type="text/javascript">
/* <![CDATA[ */
function process_form(the_form)
{
	var required_fields = {
		"req_db_host": "<?php echo $lang_install['Database server hostname'] ?>",
		"req_db_name": "<?php echo $lang_install['Database name'] ?>",
		"req_username": "<?php echo $lang_install['Administrator username'] ?>",
		"req_password1": "<?php echo $lang_install['Password'] ?>",
		"req_password2": "<?php echo $lang_install['Confirm password'] ?>",
		"req_email": "<?php echo $lang_install['Administrator email'] ?>",
		"req_title": "<?php echo $lang_install['Board title'] ?>",
		"req_base_url": "<?php echo $lang_install['Base URL'] ?>"
	};
	if (document.all || document.getElementById)
	{
		for (var i = 0; i < the_form.length; ++i)
		{
			var elem = the_form.elements[i];
			if (elem.name && required_fields[elem.name] && !elem.value && elem.type && (/^(?:text(?:area)?|password|file)$/i.test(elem.type)))
			{
				alert('"' + required_fields[elem.name] + '" <?php echo $lang_install['Required field'] ?>');
				elem.focus();
				return false;
			}
		}
	}
	return true;
}
/* ]]> */
</script>
</head>
<body onload="document.getElementById('install').req_db_type.focus();document.getElementById('install').start.disabled=false;" onunload="">

<div id="pantherinstall" class="panther">
<div class="pantherwrap">

<div id="brdheader" class="brd-header">

		<div id="brdtitle" class="inbox">
			<h1><?php echo $lang_install['Panther Installation'] ?></h1>
			<div id="brddesc"><p><?php echo $lang_install['Welcome'] ?></p></div>
		</div>

</div>

<div id="brdmain" class="brd-main">

<?php if (count($languages) > 1): ?>
<div class="block">
	<h2><?php echo $lang_install['Choose install language'] ?></h2>

		<form id="install" method="post" action="install.php">
			<div class="box">
				
					<p class="boxtitle"><?php echo $lang_install['Install language'] ?></p>
					<p class="boxinfo"><?php echo $lang_install['Choose install language info'] ?></p>
					<div class="inbox">
						<label><span class="title"><?php echo $lang_install['Install language'] ?></span>
						<select name="install_lang">
<?php

		foreach ($languages as $temp)
		{
			if ($temp == $install_lang)
				echo "\t\t\t\t\t".'<option value="'.$temp.'" selected="selected">'.$temp.'</option>'."\n";
			else
				echo "\t\t\t\t\t".'<option value="'.$temp.'">'.$temp.'</option>'."\n";
		}

?>
						</select>
						</label>
					</div>
				
			</div>
			<p class="buttons"><input type="submit" name="start" value="<?php echo $lang_install['Change language'] ?>" /></p>
		</form>
	
</div>
<?php endif; ?>


	<h2><?php echo sprintf($lang_install['Install'], FORUM_VERSION) ?></h2>
	
		<form id="install" method="post" action="install.php" onsubmit="this.start.disabled=true;if(process_form(this)){return true;}else{this.start.disabled=false;return false;}">
		<input type="hidden" name="form_sent" value="1" /><input type="hidden" name="install_lang" value="<?php echo panther_htmlspecialchars($install_lang) ?>" />
			
<?php if (!empty($alerts)): ?>		<div class="box">		<div class="blockinfo error-info">
					<h3><?php echo $lang_install['Errors'] ?></h3>
					<ul class="error-list">
<?php

foreach ($alerts as $cur_alert)
	echo "\t\t\t\t\t\t".'<li><span class="title">'.$cur_alert.'</span></li>'."\n";
?>
					</ul>
				</div></div>
<?php endif; ?>

<div class="block">		
		<div class="blockinfo">
					<h3><?php echo $lang_install['Database setup'] ?></h3>
					<p><?php echo $lang_install['Info 1'] ?></p>
	    </div>

			<div class="box">

				
					<p class="boxtitle"><?php echo $lang_install['Database hostname'] ?></p>
					<p class="boxinfo"><?php echo $lang_install['Info 3'] ?></p>
					
					<div class="inbox">	
						<div class="row">
						
						<label for="reqdbhost" class="req"><span class="title"><?php echo $lang_install['Database server hostname'] ?> <span class="required"><?php echo $lang_install['Required'] ?></span></span></label>
						<input id="reqdbhost" type="text" name="req_db_host" value="<?php echo panther_htmlspecialchars($config['host']) ?>"/>
						
						</div>
						
					</div>
				
			</div>
			<div class="box">
				
					<p class="boxtitle"><?php echo $lang_install['Database enter name'] ?></p>
					<p class="boxinfo"><?php echo $lang_install['Info 4'] ?></p>
					
					<div class="inbox">	
						<div class="row">
						<label class="req"><span class="title"><?php echo $lang_install['Database name'] ?> <span class="required"><?php echo $lang_install['Required'] ?></span></span>
						<input id="req_db_name" type="text" name="req_db_name" value="<?php echo panther_htmlspecialchars($config['db_name']) ?>"/></label>
					    </div>
						
					</div>
				
			</div>
			<div class="box">
				
					<p class="boxtitle"><?php echo $lang_install['Database enter informations'] ?></p>
					<p class="boxinfo"><?php echo $lang_install['Info 5'] ?></p>
					
					<div class="inbox">	
						<div class="row">
						<label><span class="title"><?php echo $lang_install['Database username'] ?></span>
						<input type="text" name="db_username" value="<?php echo panther_htmlspecialchars($config['username']) ?>"/></label>
						</div>
						<div class="row">
						<label><span class="title"><?php echo $lang_install['Database password'] ?></span>
						<input type="password" name="db_password" /></label>
						</div>
						
					</div>
				
			</div>
			<div class="box">
				
					<p class="boxtitle"><?php echo $lang_install['Database enter prefix'] ?></p>
					<p class="boxinfo"><?php echo $lang_install['Info 6'] ?></p>
					
					<div class="inbox">	
						<div class="row">
						<label><span class="title"><?php echo $lang_install['Table prefix'] ?></span><input id="db_prefix" type="text" name="db_prefix" value="<?php echo panther_htmlspecialchars($config['prefix']) ?>" maxlength="30"/></label>
					    </div>
						
					</div>
				
			</div>
			
</div>
<div class="block">
				<div class="blockinfo">
					<h3><?php echo $lang_install['Administration setup'] ?></h3>
					<p><?php echo $lang_install['Info 7'] ?></p>
				</div>
				
			<div class="box">
				
					<p class="boxtitle"><?php echo $lang_install['Administration setup'] ?></p>
					<p class="boxinfo"><?php echo $lang_install['Info 8'] ?></p>
					
					<div class="inbox">	
					
						<div class="row">
						<label class="req"><span class="title"><?php echo $lang_install['Administrator username'] ?> <span class="required"><?php echo $lang_install['Required'] ?></span></span>
						<input type="text" name="req_username" value="<?php echo panther_htmlspecialchars($username) ?>" maxlength="25" /></label>
						</div>
						
					    <div class="row">	
						<label class="req"><span class="title"><?php echo $lang_install['Administrator email'] ?> <span class="required"><?php echo $lang_install['Required'] ?></span></span>
					 	<input id="req_email" type="text" name="req_email" value="<?php echo panther_htmlspecialchars($email) ?>" maxlength="80" /></label>
				        </div>
					
						<div class="row">	
						<label class="req"><span class="title"><?php echo $lang_install['Password'] ?> <span class="required"><?php echo $lang_install['Required'] ?></span></span>
						<input id="req_password1" type="password" name="req_password1" /></label>
						  </div>
						
						<div class="row">	
						<label class="req"><span class="title"><?php echo $lang_install['Confirm password'] ?> <span class="required"><?php echo $lang_install['Required'] ?></span></span>
						<input type="password" name="req_password2" /></label>
						</div>
						  
						  
						</div>
					
						
					
				
			</div>
</div>
<div class="block">	
			<div class="blockinfo">
					<h3><?php echo $lang_install['Board setup'] ?></h3>
					<p><?php echo $lang_install['Info 11'] ?></p>
				</div>
			<div class="box">
				
					<p class="boxtitle"><?php echo $lang_install['General information'] ?></p>
					<div class="inbox">
					<div class="row">
						<label class="req"><span class="title"><?php echo $lang_install['Board title'] ?> <span class="required"><?php echo $lang_install['Required'] ?></span></span>
						<input id="req_title" type="text" name="req_title" value="<?php echo panther_htmlspecialchars($title) ?>" maxlength="255"/></label>
					</div>
					<div class="row">	
						<label><span class="title"><?php echo $lang_install['Board description'] ?></span>
						<input id="desc" type="text" name="desc" value="<?php echo panther_htmlspecialchars($description) ?>" maxlength="255" /></label>
					</div>
					<div class="row">
						<label class="req"><span class="title"><?php echo $lang_install['Base URL'] ?> <span class="required"><?php echo $lang_install['Required'] ?></span></span>
						<input id="req_base_url" type="text" name="req_base_url" value="<?php echo panther_htmlspecialchars($base_url) ?>" maxlength="100" /></label>
					</div>
					</div>
				
			</div>
			<div class="box">
				
					<p class="boxtitle"><?php echo $lang_install['Cookie setup'] ?></p>
					<p class="boxinfo"><?php echo $lang_install['Info 19'] ?></p>
					
					<div class="inbox">
					
						
						<div class="row">
						<label class="req"><span class="title"><?php echo $lang_install['Cookie name'] ?> <span class="required"><?php echo $lang_install['Required'] ?></span></span>
						<input type="text" name="cookie_name" value="<?php echo panther_htmlspecialchars('panther_cookie_'.random_key(6, false, true)) ?>" maxlength="25" /></label>
						</div>
						<div class="row">
						<label class="req"><span class="title"><?php echo $lang_install['Cookie seed'] ?> <span class="required"><?php echo $lang_install['Required'] ?></span></span>
						<input type="text" name="cookie_seed" value="<?php echo panther_htmlspecialchars(random_key(16, false, true)) ?>" maxlength="25" /></label>
					    </div>
						
					</div>
				
			</div>
			<div class="box">
				
					<p class="boxtitle"><?php echo $lang_install['Appearance'] ?></p>
					<p class="boxinfo"><?php echo $lang_install['Info 15'] ?></p>
					
					<div class="inbox">	
						<div class="row">
						<label class="req"><span class="title"><?php echo $lang_install['Default language'] ?> <span class="required"><?php echo $lang_install['Required'] ?></span></span>
						<select id="req_default_lang" name="req_default_lang">
<?php

		$languages = forum_list_langs();
		foreach ($languages as $temp)
		{
			if ($temp == $default_lang)
				echo "\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$temp.'" selected="selected">'.$temp.'</option>'."\n";
			else
				echo "\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$temp.'">'.$temp.'</option>'."\n";
		}

?>
						</select></label>
						</div>
						<div class="row">
						<label class="req"><span class="title"><?php echo $lang_install['Default style'] ?> <span class="required"><?php echo $lang_install['Required'] ?></span></span>
						
						<select id="req_default_style" name="req_default_style">
<?php

		$styles = forum_list_styles();
		foreach ($styles as $temp)
		{
			if ($temp == $default_style)
				echo "\t\t\t\t\t\t\t\t\t".'<option value="'.$temp.'" selected="selected">'.str_replace('_', ' ', $temp).'</option>'."\n";
			else
				echo "\t\t\t\t\t\t\t\t\t".'<option value="'.$temp.'">'.str_replace('_', ' ', $temp).'</option>'."\n";
		}

?>
						</select></label>
						</div>
					</div>
				
			</div>
</div>
			
			<input type="submit" name="start" value="<?php echo $lang_install['Start install'] ?>" class="start-install" />
		</form>

</div>

</div>
</div>

</body>
</html>
<?php
}
else
{
	$alerts = array();

	// Make sure we support sha512 (Overkill really - after PHP 5.1.2 all installations do by default)
	if (!in_array('sha512', hash_algos()))
		error_handler(E_ERROR, $lang_install['sha512 not supported'], __FILE__, __LINE__);

	$email_title = panther_trim(preg_replace('/[^a-zA-Z0-9 ]/', '', $title));
	if ($email_title == '')
		error_handler(E_ERROR, $lang_install['Email name problem'], __FILE__, __LINE__);

	// Load the database class
	require PANTHER_ROOT.'include/database.php';

	// Validate prefix
	if (strlen($config['prefix']) > 0 && (!preg_match('%^[a-zA-Z_][a-zA-Z0-9_]*$%', $config['prefix']) || strlen($config['prefix']) > 40))
		error_handler(E_ERROR, sprintf($lang_install['Table prefix error'], $db->prefix), __FILE__, __LINE__);

	// Do some DB type specific checks
		$mysql_info = $db->get_version();
		if (version_compare($mysql_info['version'], MIN_MYSQL_VERSION, '<'))
			error(sprintf($lang_install['You are running error'], $mysql_info['version'], FORUM_VERSION, MIN_MYSQL_VERSION));

	// Make sure Panther isn't already installed
	if ($db->table_exists('users'))
		error_handler(E_ERROR, sprintf($lang_install['Existing table error'], $config['prefix'], $config['name']), __FILE__, __LINE__);

	// Start a transaction
	$db->start_transaction();

	// Create all tables
	$schema = array(
			'FIELDS'			=> array(
					'id'				=> array(
							'datatype'			=> 'INT(10) UNSIGNED AUTO_INCREMENT',
							'allow_null'    	=> false
					),
					'admin_id'			=> array(
							'datatype'			=> 'INT(10)',
							'allow_null'		=> false,
							'default'			=> '0'
					),
					'restrictions'		=> array(
							'datatype'			=> 'TEXT',
							'allow_null'		=> false,
							'default'			=> '\'\''
					),
			),
			'PRIMARY KEY'		=> array('id'),
	);	
	
	$db->create_table('restrictions', $schema);
	
	$schema = array(
			'FIELDS'			=> array(
					'id'				=> array(
							'datatype'			=> 'VARCHAR(50)',
							'allow_null'    	=> false
					),
					'title'			=> array(
							'datatype'			=> 'VARCHAR(70)',
							'allow_null'		=> false,
							'default'			=> '\'\''
					),
					'version'		=> array(
							'datatype'			=> 'VARCHAR(25)',
							'allow_null'		=> false,
							'default'			=> '\'\''
					),
					'description'		=> array(
							'datatype'			=> 'TEXT',
							'allow_null'		=> true,
					),
					'author'		=> array(
							'datatype'			=> 'VARCHAR(50)',
							'allow_null'		=> false,
							'default'			=> '\'\''
					),
					'uninstall'		=> array(
							'datatype'			=> 'TEXT',
							'allow_null'		=> true,
					),
					'uninstall_note'		=> array(
							'datatype'			=> 'TEXT',
							'allow_null'		=> true,
					),
					'enabled'		=> array(
							'datatype'			=> 'TINYINT(1)',
							'allow_null'		=> false,
							'default'			=> '0'
					),
			),
			'PRIMARY KEY'		=> array('id'),
	);	

	$db->create_table('extensions', $schema);
	
	$schema = array(
			'FIELDS'			=> array(
					'extension_id'				=> array(
							'datatype'			=> 'VARCHAR(70)',
							'allow_null'    	=> false
					),
					'code'			=> array(
							'datatype'			=> 'TEXT',
							'allow_null'		=> false,
							'default'			=> '\'\''
					),
					'hook'		=> array(
							'datatype'			=> 'VARCHAR(50)',
							'allow_null'		=> false,
							'default'			=> '\'common_after_validation\''
					),
			),
	);	

	$db->create_table('extension_code', $schema);
	
	$schema = array(
			'FIELDS'			=> array(
					'id'				=> array(
							'datatype'			=> 'INT(10) UNSIGNED AUTO_INCREMENT',
							'allow_null'    	=> false
					),
					'owner'	=> array(
							'datatype'			=> 'INT(10)',
							'allow_null'		=> false,
							'default'		=> '0'
					),
					'post_id'	=> array(
							'datatype'			=> 'INT(10)',
							'allow_null'		=> false,
							'default'		=> '0'
					),
					'filename'	=> array(
							'datatype'			=> 'VARCHAR(255)',
							'allow_null'		=> false,
					),
					'extension'		=> array(
							'datatype'			=> 'VARCHAR(64)',
							'allow_null'		=> false,
					),
					'mime'	=> array(
							'datatype'			=> 'VARCHAR(64)',
							'allow_null'		=> false
					),
					'location'	=> array(
							'datatype'			=> 'TEXT',
							'allow_null'		=> false
					),
					'size'	=> array(
							'datatype'		=> 'INT(10)',
							'allow_null'	=> false,
							'default'		=> '0'
					),
					'downloads'	=> array(
							'datatype'			=> 'INT(10)',
							'allow_null'		=> false,
							'default'		=> '0'
					)
			),
			'PRIMARY KEY'		=> array('id'),
	);

	$db->create_table('attachments', $schema);

	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null'	=> false
			),
			'username'		=> array(
				'datatype'		=> 'VARCHAR(200)',
				'allow_null'	=> true
			),
			'ip'			=> array(
				'datatype'		=> 'VARCHAR(255)',
				'allow_null'	=> true
			),
			'email'			=> array(
				'datatype'		=> 'VARCHAR(80)',
				'allow_null'	=> true
			),
			'message'		=> array(
				'datatype'		=> 'VARCHAR(255)',
				'allow_null'	=> true
			),
			'expire'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> true
			),
			'ban_creator'	=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			)
		),
		'PRIMARY KEY'	=> array('id'),
		'INDEXES'		=> array(
			'username_idx'	=> array('username(25)')
		)
	);

	$db->create_table('bans', $schema);

	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null'	=> false
			),
			'cat_name'		=> array(
				'datatype'		=> 'VARCHAR(80)',
				'allow_null'	=> false,
				'default'		=> '\'New Category\''
			),
			'disp_position'	=> array(
				'datatype'		=> 'INT(10)',
				'allow_null'	=> false,
				'default'		=> '0'
			)
		),
		'PRIMARY KEY'	=> array('id')
	);

	$db->create_table('categories', $schema);


	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null'	=> false
			),
			'search_for'	=> array(
				'datatype'		=> 'VARCHAR(60)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'replace_with'	=> array(
				'datatype'		=> 'VARCHAR(60)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			)
		),
		'PRIMARY KEY'	=> array('id')
	);

	$db->create_table('censoring', $schema);
	
	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null'	=> false
			),
			'question'	=> array(
				'datatype'		=> 'VARCHAR(90)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'answer'	=> array(
				'datatype'		=> 'VARCHAR(60)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			)
		),
		'PRIMARY KEY'	=> array('id')
	);

	$db->create_table('robots', $schema);

	$schema = array(
		'FIELDS'		=> array(
			'conf_name'		=> array(
				'datatype'		=> 'VARCHAR(255)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'conf_value'	=> array(
				'datatype'		=> 'TEXT',
				'allow_null'	=> true
			)
		),
		'PRIMARY KEY'	=> array('conf_name')
	);

	$db->create_table('config', $schema);


	$schema = array(
		'FIELDS'		=> array(
			'group_id'		=> array(
				'datatype'		=> 'INT(10)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'forum_id'		=> array(
				'datatype'		=> 'INT(10)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'read_forum'	=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'post_replies'	=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'post_topics'	=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'post_polls'	=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'upload'		=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'download'	=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'delete_files'	=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			)
		),
		'PRIMARY KEY'	=> array('group_id', 'forum_id')
	);

	$db->create_table('forum_perms', $schema);

	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null'	=> false
			),
			'forum_name'	=> array(
				'datatype'		=> 'VARCHAR(80)',
				'allow_null'	=> false,
				'default'		=> '\'New forum\''
			),
			'forum_desc'	=> array(
				'datatype'		=> 'TEXT',
				'allow_null'	=> true
			),
			'redirect_url'	=> array(
				'datatype'		=> 'VARCHAR(100)',
				'allow_null'	=> true
			),
			'moderators'	=> array(
				'datatype'		=> 'TEXT',
				'allow_null'	=> true
			),
			'show_post_info'	=> array(
				'datatype'		=> 'INT(1) UNSIGNED',
				'allow_null'	=> false,
				'default'		=>	'1',
			),
			'num_topics'	=> array(
				'datatype'		=> 'MEDIUMINT(8) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'num_posts'		=> array(
				'datatype'		=> 'MEDIUMINT(8) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'last_post'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> true
			),
			'last_post_id'	=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> true
			),
			'last_poster'	=> array(
				'datatype'		=> 'VARCHAR(200)',
				'allow_null'	=> true
			),
			'last_topic'	=> array(
				'datatype'		=> 'VARCHAR(200)',
				'allow_null'	=> true
			),
			'last_topic_id'	=> array(
				'datatype'		=> 'INT(10)',
				'allow_null'	=> true
			),
			'use_reputation'	=> array(
				'datatype'		=> 'INT',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'sort_by'		=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'disp_position'	=> array(
				'datatype'		=> 'INT(10)',
				'allow_null'	=> false,
				'default'		=>	'0'
			),
			'cat_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=>	'0'
			),
			'force_approve'		=> array(
				'datatype'		=> 'TINYINT(1) UNSIGNED',
				'allow_null'	=> false,
				'default'		=>	'0'
			),
			'quickjump'		=> array(
				'datatype'		=> 'TINYINT(1) UNSIGNED',
				'allow_null'	=> false,
				'default'		=>	'1'
			),
			'protected'		=> array(
				'datatype'		=> 'TINYINT(1) UNSIGNED',
				'allow_null'	=> false,
				'default'		=>	'0'
			),
			'increment_posts'		=> array(
				'datatype'		=> 'TINYINT(1) UNSIGNED',
				'allow_null'	=> false,
				'default'		=>	'1'
			),
			'parent_forum'		=> array(
				'datatype'		=> 'TINYINT(1) UNSIGNED',
				'allow_null'	=> false,
				'default'		=>	'0'
			),
			'password'		=> array(
				'datatype'		=> 'VARCHAR(128)',	// 128 characters in length for sha512 hash
				'allow_null'	=> false,
				'default'		=>	'\'\''
			),
			'salt'		=> array(
				'datatype'		=> 'VARCHAR(12)',
				'allow_null'	=> false,
				'default'		=>	'\'\''
			),
		),
		'PRIMARY KEY'	=> array('id')
	);

	$db->create_table('forums', $schema);

	$schema = array(
		'FIELDS'		=> array(
			'g_id'						=> array(
				'datatype'		=> 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null'	=> false
			),
			'g_title'					=> array(
				'datatype'		=> 'VARCHAR(50)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'g_user_title'				=> array(
				'datatype'		=> 'VARCHAR(50)',
				'allow_null'	=> true
			),
			'g_image'			=> array(
				'datatype'		=> 'VARCHAR(3)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'g_promote_min_posts'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_promote_next_group'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_moderator'				=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_mod_cp'				=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_admin'				=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_global_moderator'=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),			
			'g_mod_edit_users'			=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_mod_rename_users'		=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_mod_change_passwords'	=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_mod_ban_users'			=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_mod_warn_users'			=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_mod_edit_admin_posts'	=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_mod_promote_users'			=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_mod_sfs_report'			=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_read_board'				=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'g_view_users'				=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'g_post_replies'			=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'g_post_topics'				=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'g_post_polls'				=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'g_edit_posts'				=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'g_edit_subject'			=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'g_deledit_interval'	=> array(
				'datatype'		=> 'INT(10)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_delete_posts'			=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'g_delete_topics'			=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'g_post_links'				=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'g_set_title'				=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'g_search'					=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'g_search_users'			=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'g_send_email'				=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'g_rep_enabled'				=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'g_rep_interval'			=> array(
				'datatype'		=> 'INT(10)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_rep_plus'			=> array(
				'datatype'		=> 'INT(10)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_rep_minus'			=> array(
				'datatype'		=> 'INT(10)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_post_flood'				=> array(
				'datatype'		=> 'SMALLINT(6)',
				'allow_null'	=> false,
				'default'		=> '30'
			),
			'g_robot_test'				=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_search_flood'			=> array(
				'datatype'		=> 'SMALLINT(6)',
				'allow_null'	=> false,
				'default'		=> '30'
			),
			'g_email_flood'				=> array(
				'datatype'		=> 'SMALLINT(6)',
				'allow_null'	=> false,
				'default'		=> '60'
			),
			'g_report_flood'			=> array(
				'datatype'		=> 'SMALLINT(6)',
				'allow_null'	=> false,
				'default'		=> '60'
			),
			'g_colour'			=> array(
				'datatype'		=> 'VARCHAR(15)',
				'allow_null'	=> true,
			),
			'g_moderate_posts'			=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_pm_limit'		=> array(
				'datatype'		=> 'SMALLINT(3)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_use_pm'				=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_pm_folder_limit'		=> array(
				'datatype'		=> 'INT(3)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_attach_files'		=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_max_attachments'		=> array(
				'datatype'		=> 'INT(10)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'g_max_size'			=> array(
				'datatype'		=> 'INT(10)',
				'allow_null'	=> false,
				'default'		=> '0'
			)
		),
		'PRIMARY KEY'	=> array('g_id')
	);

	$db->create_table('groups', $schema);

	$schema = array(
		'FIELDS'		=> array(
			'user_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'ident'			=> array(
				'datatype'		=> 'VARCHAR(200)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'logged'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'idle'			=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'last_post'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> true
			),
			'last_search'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> true
			),
			'currently'		=> array(
				'datatype'		=> 'TEXT',
				'allow_null'	=> true
			),
		),
		'UNIQUE KEYS'	=> array(
			'user_id_ident_idx'	=> array('user_id', 'ident(25)')
		),
		'INDEXES'		=> array(
			'ident_idx'		=> array('ident(25)'),
			'logged_idx'	=> array('logged')
		)
	);

	$db->create_table('online', $schema);
	
	$schema = array(
		'FIELDS' => array(
			'id' => array(
				'datatype' => 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null' => false
			),
			'user_id' => array(
				'datatype' => 'INT(10)',
				'allow_null' => false,
				'default' => '0'
			),
			'block_id' => array(
				'datatype' => 'INT(10)',
				'allow_null' => false,
				'default' => '0'
			),
		),
		'PRIMARY KEY' => array('id')
	);
	$db->create_table('blocks', $schema);

	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null'	=> false
			),
			'poster'		=> array(
				'datatype'		=> 'VARCHAR(200)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'poster_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'poster_ip'		=> array(
				'datatype'		=> 'VARCHAR(39)',
				'allow_null'	=> true
			),
			'poster_email'	=> array(
				'datatype'		=> 'VARCHAR(80)',
				'allow_null'	=> true
			),
			'message'		=> array(
				'datatype'		=> 'MEDIUMTEXT',
				'allow_null'	=> true
			),
			'reputation'	=> array(
				'datatype'		=> 'INT',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'approved'		=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'deleted'		=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'hide_smilies'	=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'posted'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'edited'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> true
			),
			'edit_reason'		=> array(
				'datatype'		=> 'VARCHAR(50)',
				'allow_null'	=> true
			),
			'edited_by'		=> array(
				'datatype'		=> 'VARCHAR(200)',
				'allow_null'	=> true
			),
			'topic_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			)
		),
		'PRIMARY KEY'	=> array('id'),
		'INDEXES'		=> array(
			'topic_id_idx'	=> array('topic_id'),
			'multi_idx'		=> array('poster_id', 'topic_id')
		)
	);

	$db->create_table('posts', $schema);

	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null'	=> false
			),
			'post_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'topic_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'forum_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'reported_by'	=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'created'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'message'		=> array(
				'datatype'		=> 'TEXT',
				'allow_null'	=> true
			),
			'zapped'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> true
			),
			'zapped_by'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> true
			)
		),
		'PRIMARY KEY'	=> array('id'),
		'INDEXES'		=> array(
			'zapped_idx'	=> array('zapped')
		)
	);

	$db->create_table('reports', $schema);

	$schema = array(
			'FIELDS'		=> array(
				'id'			=> array(
					'datatype'		=> 'INT(10) UNSIGNED AUTO_INCREMENT',
					'allow_null'	=> false,
				),
				'post_id'		=> array(
					'datatype'		=> 'INT UNSIGNED',
					'allow_null'	=> false,
					'default'		=> '\'0\'',
				),
				'vote'			=> array(
					'datatype'		=> 'INT(2)',
					'allow_null'	=> false,
					'default'		=> '\'0\'',
				),
				'given_by'			=> array(
					'datatype'		=> 'INT UNSIGNED',
					'allow_null'	=> false,
					'default'		=> '\'0\'',
				),
				'time_given'			=> array(
					'datatype'		=> 'INT (10) UNSIGNED',
					'allow_null'	=> false,
					'default'		=> '\'0\'',
				),
			),
			'PRIMARY KEY'	=> array('id'),
		);
	
	$db->create_table('reputation', $schema);
	
	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'ident'			=> array(
				'datatype'		=> 'VARCHAR(200)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'search_data'	=> array(
				'datatype'		=> 'MEDIUMTEXT',
				'allow_null'	=> true
			)
		),
		'PRIMARY KEY'	=> array('id'),
		'INDEXES'		=> array(
			'ident_idx'	=> array('ident(8)')
		)
	);

	$db->create_table('search_cache', $schema);

	$schema = array(
		'FIELDS'		=> array(
			'post_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'word_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'subject_match'	=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			)
		),
		'INDEXES'		=> array(
			'word_id_idx'	=> array('word_id'),
			'post_id_idx'	=> array('post_id')
		)
	);

	$db->create_table('search_matches', $schema);

	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null'	=> false
			),
			'word'			=> array(
				'datatype'		=> 'VARCHAR(20)',
				'allow_null'	=> false,
				'default'		=> '\'\'',
				'collation'		=> 'bin'
			)
		),
		'PRIMARY KEY'	=> array('word'),
		'INDEXES'		=> array(
			'id_idx'	=> array('id')
		)
	);

	$db->create_table('search_words', $schema);

	$schema = array(
		'FIELDS'		=> array(
			'user_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'topic_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			)
		),
		'PRIMARY KEY'	=> array('user_id', 'topic_id')
	);

	$db->create_table('topic_subscriptions', $schema);

	$schema = array(
		'FIELDS'		=> array(
			'user_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'forum_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			)
		),
		'PRIMARY KEY'	=> array('user_id', 'forum_id')
	);

	$db->create_table('forum_subscriptions', $schema);
	
	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null'	=> false
			),
			'poster'		=> array(
				'datatype'		=> 'VARCHAR(200)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'poster_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'poster_ip'		=> array(
				'datatype'		=> 'VARCHAR(39)',
				'allow_null'	=> true
			),
			'message'		=> array(
				'datatype'		=> 'TEXT',
				'allow_null'	=> true
			),
			'hide_smilies'	=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'posted'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'edited'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> true
			),
			'edited_by'		=> array(
				'datatype'		=> 'VARCHAR(200)',
				'allow_null'	=> true
			),
			'topic_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			)
		),
		'PRIMARY KEY'	=> array('id'),
		'INDEXES'		=> array(
			'topic_id_idx'	=> array('topic_id'),
		)
	);

	$db->create_table('messages', $schema);

	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null'	=> false
			),
			'subject'		=> array(
				'datatype'		=> 'VARCHAR(255)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'first_post_id'	=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'poster'	=> array(
				'datatype'		=> 'VARCHAR(200)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'poster_id'	=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'num_replies'	=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'last_post'	=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'last_post_id'	=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'last_poster'		=> array(
				'datatype'		=> 'VARCHAR(200)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
		),
		'PRIMARY KEY'	=> array('id'),
	);

	$db->create_table('conversations', $schema);
	
	$schema = array(
		'FIELDS'		=> array(
			'topic_id'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false
			),
			'user_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'deleted'	=> array(
				'datatype'		=> 'TINYINT(1) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'viewed'	=> array(
				'datatype'		=> 'TINYINT(1) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'folder_id'	=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '2'
			),
		),
		'PRIMARY KEY'	=> array('user_id', 'topic_id'),
		'INDEXES'		=> array(
			'folder_idx'		=> array('folder_id'),
		)
	);

	$db->create_table('pms_data', $schema);
	
	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null'	=> false
			),
			'name'		=> array(
				'datatype'		=> 'VARCHAR(100)',
				'allow_null'	=> false,
				'default'		=> '\'New Folder\''
			),
			'user_id'	=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
		),
		'PRIMARY KEY'	=> array('id'),
		'INDEXES'		=> array(
			'user_idx'		=> array('user_id'),
		)
	);

	$db->create_table('folders', $schema);

	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null'	=> false
			),
			'poster'		=> array(
				'datatype'		=> 'VARCHAR(200)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'subject'		=> array(
				'datatype'		=> 'VARCHAR(255)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'question'		=> array(
				'datatype'		=> 'VARCHAR(255)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'posted'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'first_post_id'	=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'last_post'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'last_post_id'	=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'last_poster'	=> array(
				'datatype'		=> 'VARCHAR(200)',
				'allow_null'	=> true
			),
			'num_views'		=> array(
				'datatype'		=> 'MEDIUMINT(8) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'num_replies'	=> array(
				'datatype'		=> 'MEDIUMINT(8) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'closed'		=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'sticky'		=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'archived'		=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'deleted'		=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'moved_to'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> true
			),
			'forum_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'approved'		=> array(
				'datatype'		=> 'TINYINT(1) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '1'
			)
		),
		'PRIMARY KEY'	=> array('id'),
		'INDEXES'		=> array(
			'forum_id_idx'		=> array('forum_id'),
			'moved_to_idx'		=> array('moved_to'),
			'last_post_idx'		=> array('last_post'),
			'first_post_id_idx'	=> array('first_post_id'),
			'approved_idx'	=> array('approved'),
			'deleted_idx'	=> array('deleted'),
		)
	);

	$db->create_table('topics', $schema);
	
	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null'	=> false
			),
			'topic_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'options'		=> array(
				'datatype'		=> 'MEDIUMTEXT',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'voters'		=> array(
				'datatype'		=> 'MEDIUMTEXT',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'votes'		=> array(
				'datatype'		=> 'MEDIUMTEXT',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'type'	=> array(
				'datatype'		=> 'TINYINT(1) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'posted'	=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
		),
		'PRIMARY KEY'	=> array('id'),
		'UNIQUE KEYS'	=> array(
			'topic_id_idx'	=> array('topic_id') // Speed up gathering results in viewtopic
		),
	);
	
	$db->create_table('polls', $schema);

	$schema = array(
		'FIELDS'		=> array(
			'id'				=> array(
				'datatype'		=> 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null'	=> false
			),
			'group_id'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '3'
			),
			'username'			=> array(
				'datatype'		=> 'VARCHAR(200)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'password'			=> array(
				'datatype'		=> 'VARCHAR(128)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'salt'			=> array(
				'datatype'		=> 'VARCHAR(16)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'email'				=> array(
				'datatype'		=> 'VARCHAR(80)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'title'				=> array(
				'datatype'		=> 'VARCHAR(50)',
				'allow_null'	=> true
			),
			'realname'			=> array(
				'datatype'		=> 'VARCHAR(40)',
				'allow_null'	=> true
			),
			'url'				=> array(
				'datatype'		=> 'VARCHAR(100)',
				'allow_null'	=> true
			),
			'facebook'			=> array(
				'datatype'		=> 'VARCHAR(50)',
				'allow_null'	=> true
			),
			'steam'				=> array(
				'datatype'		=> 'VARCHAR(50)',
				'allow_null'	=> true
			),
			'skype'				=> array(
				'datatype'		=> 'VARCHAR(80)',
				'allow_null'	=> true
			),
			'twitter'			=> array(
				'datatype'		=> 'VARCHAR(50)',
				'allow_null'	=> true
			),
			'google'			=> array(
				'datatype'		=> 'VARCHAR(50)',
				'allow_null'	=> true
			),
			'location'			=> array(
				'datatype'		=> 'VARCHAR(30)',
				'allow_null'	=> true
			),
			'signature'			=> array(
				'datatype'		=> 'TEXT',
				'allow_null'	=> true
			),
			'disp_topics'		=> array(
				'datatype'		=> 'TINYINT(3) UNSIGNED',
				'allow_null'	=> true
			),
			'disp_posts'		=> array(
				'datatype'		=> 'TINYINT(3) UNSIGNED',
				'allow_null'	=> true
			),
			'email_setting'		=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'notify_with_post'	=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'auto_notify'		=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'pm_notify'		=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'show_smilies'		=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'show_img'			=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'show_img_sig'		=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'show_avatars'		=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'show_sig'			=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'timezone'			=> array(
				'datatype'		=> 'FLOAT',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'dst'				=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'time_format'		=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'date_format'		=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'language'			=> array(
				'datatype'		=> 'VARCHAR(25)',
				'allow_null'	=> false,
				'default'		=> '\''.$default_lang.'\'',
			),
			'style'				=> array(
				'datatype'		=> 'VARCHAR(25)',
				'allow_null'	=> false,
				'default'		=> '\''.$default_style.'\'',
			),
			'num_posts'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'num_pms'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'pm_enabled'		=> array(
				'datatype'		=> 'TINYINT(1) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'use_editor'		=> array(
				'datatype'		=> 'TINYINT(1) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '1'
			),
			'use_gravatar'		=> array(
				'datatype'		=> 'TINYINT(1) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'last_post'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> true
			),
			'last_search'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> true
			),
			'last_email_sent'	=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> true
			),
			'last_report_sent'	=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> true
			),
			'registered'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'registration_ip'	=> array(
				'datatype'		=> 'VARCHAR(39)',
				'allow_null'	=> false,
				'default'		=> '\'0.0.0.0\''
			),
			'reputation'	=> array(
				'datatype'		=> 'INT',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'last_visit'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'posting_ban'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'admin_note'		=> array(
				'datatype'		=> 'VARCHAR(30)',
				'allow_null'	=> true
			),
			'activate_string'	=> array(
				'datatype'		=> 'VARCHAR(128)',
				'allow_null'	=> true
			),
			'activate_key'		=> array(
				'datatype'		=> 'VARCHAR(8)',
				'allow_null'	=> true
			),
			'login_key'		=> array(
				'datatype'		=> 'VARCHAR(60)',
				'allow_null'	=> true
			),
		),
		'PRIMARY KEY'	=> array('id'),
		'UNIQUE KEYS'	=> array(
			'username_idx'		=> array('username(25)'),
			'login_key_idx'		=> array('login_key')
		),
		'INDEXES'		=> array(
			'registered_idx'	=> array('registered'),
			'group_id_idx'	=> array('group_id'),	// Set a group id index to help speed up JOINs later
		)
	);

	$db->create_table('users', $schema);
	
	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null'	=> false,
			),
			'subject'		=> array(
				'datatype'		=> 'VARCHAR(50)',
				'allow_null'	=> false,
				'default'		=> '\'\'',
			),
			'message'			=> array(
				'datatype'		=> 'text',
				'allow_null'	=> false,
			),
			'forum_id'			=> array(
				'datatype'		=> 'text',
				'allow_null'	=> false,
			),
			'user_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '2',
			),
		),
		'PRIMARY KEY'	=> array('id'),
	);

	$db->create_table('announcements', $schema);

	$schema = array
	(
		'FIELDS'	=> array(
				'id' => array(
				'datatype'		=> 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null'	=> false,
			),
			'last_checked'		=> array(
				'datatype'		=> 'timestamp',
				'allow_null'	=> false,
				'default'		=> 'CURRENT_TIMESTAMP'
			),
			'ip_address'		=> array(
				'datatype'		=> 'varchar(39)',
				'allow_null'	=> false,
			),
			'username'		=> array(
				'datatype'		=> 'varchar(100)',
				'allow_null'	=> false,
			),				
		),
		'PRIMARY KEY'	=> array('id'),
		'UNIQUE KEYS'	=> array(
			'ip_address_idx'	=> array('ip_address')
		),
	);
	
	$db->create_table('login_queue', $schema);
	
	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null'	=> false,
			),
			'title'		=> array(
				'datatype'		=> 'varchar(50)',
				'allow_null'	=> false,
				'default'		=> '\'New Action\'',
			),
			'close'			=> array(
				'datatype'		=> 'TINYINT(1) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '\'2\'',
			),
			'stick'			=> array(
				'datatype'		=> 'TINYINT(1) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '\'2\'',
			),
			'move'			=> array(
				'datatype'		=> 'TINYINT(1) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '\'0\'',
			),
			'archive'			=> array(
				'datatype'		=> 'TINYINT(1) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '\'0\'',
			),
			'leave_redirect'			=> array(
				'datatype'		=> 'TINYINT(1) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '\'0\'',
			),
			'reply_message'			=> array(
				'datatype'		=> 'mediumtext',
				'allow_null'	=> true,
			),
			'add_start'			=> array(
				'datatype'		=> 'VARCHAR(50)',
				'allow_null'	=> true,
				'default'		=> null,
			),
			'add_end'			=> array(
				'datatype'		=> 'VARCHAR(50)',
				'allow_null'	=> true,
				'default'		=> null,
			),
			'send_email'			=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0',
			),
			'increment_posts'	=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0',
			),
		),
		'PRIMARY KEY'	=> array('id'),
	);

	$db->create_table('multi_moderation', $schema);
	
	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null'	=> false
			),
			'rank'			=> array(
				'datatype'		=> 'VARCHAR(50)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'min_posts'		=> array(
				'datatype'		=> 'MEDIUMINT(8) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			)
		),
		'PRIMARY KEY'	=> array('id')
	);

	$db->create_table('ranks', $schema);
	
	$schema = array(
		'FIELDS' => array(
			'id' => array(
				'datatype' => 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null' => false
			),
			'image' => array(
				'datatype' => 'VARCHAR(40)',
				'allow_null' => false,
				'default' => '\'\''
			),
			'code' => array(
				'datatype' => 'VARCHAR(20)',
				'allow_null' => false,
				'default' => '\'\''
			),
			'disp_position' => array(
				'datatype' => 'TINYINT(2) UNSIGNED',
				'allow_null' => false,
				'default' => '0'
			)
		),
		'PRIMARY KEY' => array('id')
	);
	$db->create_table('smilies', $schema);
	
	$schema = array(
		'FIELDS' => array(
			'id' => array(
				'datatype' => 'INT(10) UNSIGNED AUTO_INCREMENT',
				'allow_null' => false
			),
			'title' => array(
				'datatype' => 'VARCHAR(50)',
				'allow_null' => false,
				'default' => '\'New Task\''
			),
			'next_run' => array(
				'datatype' => 'INT(10) UNSIGNED',
				'allow_null' => false,
				'default' => '\'0\''
			),
			'script' => array(
				'datatype' => 'VARCHAR(30)',
				'allow_null' => false,
			),
			'minute' => array(
				'datatype' => 'VARCHAR(2)',
				'allow_null' => false,
				'default' => '\'*\''
			),
			'hour' => array(
				'datatype' => 'VARCHAR(2)',
				'allow_null' => false,
				'default' => '\'*\''
			),
			'day' => array(
				'datatype' => 'VARCHAR(2)',
				'allow_null' => false,
				'default' => '\'*\''
			),
			'month' => array(
				'datatype' => 'VARCHAR(2)',
				'allow_null' => false,
				'default' => '\'*\''
			),
			'week_day' => array(
				'datatype' => 'VARCHAR(1)',
				'allow_null' => false,
				'default' => '\'*\''
			),
			'locked' => array(
				'datatype' => 'TINYINT(1)',
				'allow_null' => false,
				'default' => '0'
			),
		),
		'PRIMARY KEY' => array('id')
	);
	$db->create_table('tasks', $schema);
	
	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'SERIAL',
				'allow_null'	=> false
			),
			'user_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'type_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'post_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'title'			=> array(
				'datatype'		=> 'VARCHAR(120)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'points'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'date_issued'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'date_expire'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'issued_by'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'expired'		=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'note_admin'		=> array(
				'datatype'		=> 'TEXT',
				'allow_null'	=> true
			),
			'note_post'		=> array(
				'datatype'		=> 'MEDIUMTEXT',
				'allow_null'	=> true
			),
			'note_pm'		=> array(
				'datatype'		=> 'TEXT',
				'allow_null'	=> true
			)
		),
		'PRIMARY KEY'	=> array('id'),
	);

	$db->create_table('warnings', $schema);
		
	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'SERIAL',
				'allow_null'	=> false
			),
			'title'			=> array(
				'datatype'		=> 'VARCHAR(120)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'description'		=> array(
				'datatype'		=> 'TEXT',
				'allow_null'	=> true
			),
			'points'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'expiration_time'	=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			)
		),
		'PRIMARY KEY'	=> array('id'),
	);

	$db->create_table('warning_types', $schema);
	
	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'SERIAL',
				'allow_null'	=> false
			),
			'points'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'message'		=> array(
				'datatype'		=> 'VARCHAR(255)',
				'allow_null'	=> false,
				'default'		=> '\'\''
			),
			'period'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			)
		),
		'PRIMARY KEY'	=> array('id'),
	);
	
	$db->create_table('warning_levels', $schema);
	
	$now = time();
	$password_salt = random_pass(16);
	
	$insert = array(
		'g_id'		=>	1,
		'g_title'	=>	$lang_install['Administrators'],
		'g_user_title'	=>	$lang_install['Administrator'],
		'g_moderator'	=> 0,
		'g_mod_cp'	=>	0,
		'g_global_moderator'	=>	0,
		'g_mod_edit_users'	=>	0,
		'g_mod_rename_users'	=>	0,
		'g_mod_change_passwords'	=>	0,
		'g_mod_ban_users'	=>	0,
		'g_mod_edit_admin_posts'	=>	0,
		'g_read_board'	=>	1,
		'g_post_polls'	=>	1,
		'g_view_users'	=>	1,
		'g_post_replies'	=>	1,
		'g_post_topics'	=> 1,
		'g_edit_posts'	=> 1,
		'g_edit_subject'	=> 1,
		'g_delete_posts'	=> 1,
		'g_delete_topics'	=> 1,
		'g_set_title'	=> 1,
		'g_search'	=> 1,
		'g_search_users'	=> 1,
		'g_send_email'	=> 1,
		'g_post_flood'	=> 0,
		'g_use_pm'		=> 1,
		'g_pm_limit'	=> 0,
		'g_search_flood'	=> 0,
		'g_email_flood'	=> 0,
		'g_report_flood'	=> 0,
		'g_rep_enabled'	=> 1,
		'g_rep_interval'	=> 0,
		'g_rep_plus'	=> 0,
		'g_rep_minus'	=> 0,
		'g_colour'		=> '#AA0000',
	);

	// Insert the five preset groups
	$db->insert('groups', $insert);
	
	$insert = array(
		'g_id'		=>	2,
		'g_title'	=>	$lang_install['Global Moderators'],
		'g_user_title'	=>	$lang_install['Global Moderator'],
		'g_moderator'	=> 1,
		'g_mod_cp'	=>	1,
		'g_global_moderator'	=>	1,
		'g_mod_edit_users'	=>	1,
		'g_mod_sfs_report'	=>	1,
		'g_mod_rename_users'	=>	0,
		'g_mod_change_passwords'	=>	1,
		'g_mod_ban_users'	=>	1,
		'g_post_polls'	=>	1,
		'g_mod_warn_users'	=>	1,
		'g_mod_edit_admin_posts'	=>	1,
		'g_read_board'	=>	1,
		'g_view_users'	=>	1,
		'g_post_replies'	=>	1,
		'g_post_topics'	=> 1,
		'g_edit_posts'	=> 1,
		'g_edit_subject'	=> 1,
		'g_delete_posts'	=> 1,
		'g_delete_topics'	=> 1,
		'g_set_title'	=> 1,
		'g_search'	=> 1,
		'g_search_users'	=> 1,
		'g_send_email'	=> 1,
		'g_post_flood'	=> 0,
		'g_use_pm'		=> 1,
		'g_pm_limit'	=> 0,
		'g_search_flood'	=> 0,
		'g_email_flood'	=> 0,
		'g_report_flood'	=> 0,
		'g_rep_enabled'	=> 1,
		'g_rep_interval'	=> 0,
		'g_rep_plus'	=> 0,
		'g_rep_minus'	=> 0,
		'g_colour'		=> '#0000CC',
		'g_attach_files'	=> 1,
		'g_max_attachments'	=> 5,
		'g_max_size'		=> 10485760,
	);
	
	$db->insert('groups', $insert);
	
	$insert = array(
		'g_id'		=>	3,
		'g_title'	=>	$lang_install['Moderators'],
		'g_user_title'	=>	$lang_install['Moderator'],
		'g_moderator'	=> 1,
		'g_mod_cp'	=>	1,
		'g_global_moderator'	=>	0,
		'g_mod_edit_users'	=>	0,
		'g_mod_sfs_report'	=>	1,
		'g_mod_rename_users'	=>	0,
		'g_mod_change_passwords'	=>	0,
		'g_mod_ban_users'	=>	1,
		'g_mod_warn_users'	=>	1,
		'g_mod_edit_admin_posts'	=>	0,
		'g_read_board'	=>	1,
		'g_post_polls'	=>	1,
		'g_view_users'	=>	1,
		'g_post_replies'	=>	1,
		'g_post_topics'	=> 1,
		'g_edit_posts'	=> 1,
		'g_edit_subject'	=> 1,
		'g_delete_posts'	=> 1,
		'g_delete_topics'	=> 1,
		'g_set_title'	=> 1,
		'g_search'	=> 1,
		'g_search_users'	=> 1,
		'g_send_email'	=> 1,
		'g_post_flood'	=> 0,
		'g_use_pm'		=> 1,
		'g_pm_limit'	=> 0,
		'g_search_flood'	=> 0,
		'g_email_flood'	=> 0,
		'g_report_flood'	=> 0,
		'g_rep_enabled'	=> 1,
		'g_rep_interval'	=> 0,
		'g_rep_plus'	=> 0,
		'g_rep_minus'	=> 0,
		'g_colour'		=> '#00AA00',
		'g_attach_files'	=> 1,
		'g_max_attachments'	=> 5,
		'g_max_size'		=> 10485760,
		'g_pm_folder_limit'	=>	10,
	);
	
	$db->insert('groups', $insert);
	
	$insert = array(
		'g_id'		=>	4,
		'g_title'	=>	$lang_install['Guests'],
		'g_moderator'	=> 0,
		'g_mod_cp'	=>	0,
		'g_global_moderator'	=>	0,
		'g_mod_edit_users'	=>	0,
		'g_mod_rename_users'	=>	0,
		'g_mod_change_passwords'	=>	0,
		'g_mod_ban_users'	=>	0,
		'g_mod_warn_users'	=>	0,
		'g_mod_edit_admin_posts'	=>	0,
		'g_read_board'	=>	1,
		'g_view_users'	=>	0,
		'g_post_replies'	=>	0,
		'g_post_topics'	=> 0,
		'g_edit_posts'	=> 0,
		'g_edit_subject'	=> 0,
		'g_delete_posts'	=> 0,
		'g_delete_topics'	=> 0,
		'g_set_title'	=> 0,
		'g_search'	=> 1,
		'g_search_users'	=> 0,
		'g_send_email'	=> 0,
		'g_post_flood'	=> 60,
		'g_use_pm'		=> 0,
		'g_pm_limit'	=> 0,
		'g_search_flood'	=> 30,
		'g_email_flood'	=> 0,
		'g_report_flood'	=> 0,
		'g_rep_enabled'	=> 0,
		'g_rep_interval'	=> 0,
		'g_rep_plus'	=> 0,
		'g_rep_minus'	=> 0,
		'g_colour'		=> '',
		'g_attach_files'	=> 0,
	);
	
	$db->insert('groups', $insert);
	
	$insert = array(
		'g_id'		=>	5,
		'g_title'	=>	$lang_install['Members'],
		'g_moderator'	=> 0,
		'g_global_moderator'	=>	0,
		'g_mod_edit_users'	=>	0,
		'g_mod_rename_users'	=>	0,
		'g_mod_change_passwords'	=>	0,
		'g_mod_ban_users'	=>	0,
		'g_mod_warn_users'	=>	0,
		'g_mod_edit_admin_posts'	=>	0,
		'g_read_board'	=>	1,
		'g_view_users'	=>	1,
		'g_post_polls'	=>	1,
		'g_post_replies'	=>	1,
		'g_post_topics'	=> 1,
		'g_edit_posts'	=> 1,
		'g_edit_subject'	=> 0,
		'g_delete_posts'	=> 0,
		'g_delete_topics'	=> 0,
		'g_set_title'	=> 0,
		'g_search'	=> 1,
		'g_search_users'	=> 1,
		'g_send_email'	=> 1,
		'g_post_flood'	=> 5,
		'g_use_pm'		=> 1,
		'g_pm_limit'	=> 100,
		'g_search_flood'	=> 30,
		'g_email_flood'	=> 30,
		'g_report_flood'	=> 30,
		'g_rep_enabled'	=> 1,
		'g_rep_interval'	=> 5,
		'g_rep_plus'	=> 10,
		'g_rep_minus'	=> 5,
		'g_colour'		=> '',
		'g_attach_files'	=> 1,
		'g_max_attachments'	=> 2,
		'g_max_size'		=> 5242880,
		'g_pm_folder_limit'	=>	5,
	);
	
	$db->insert('groups', $insert);
	
	$insert = array(
		'g_id'		=>	6,
		'g_title'	=>	$lang_install['New members'],
		'g_moderator'	=> 0,
		'g_global_moderator'	=>	0,
		'g_mod_edit_users'	=>	0,
		'g_promote_min_posts'	=>	5,
		'g_promote_next_group'	=>	5,
		'g_mod_rename_users'	=>	0,
		'g_mod_change_passwords'	=>	0,
		'g_mod_ban_users'	=>	0,
		'g_mod_warn_users'	=>	0,
		'g_mod_edit_admin_posts'	=>	0,
		'g_read_board'	=>	1,
		'g_post_polls'	=>	1,
		'g_view_users'	=>	0,
		'g_post_replies'	=>	1,
		'g_post_topics'	=> 1,
		'g_edit_posts'	=> 1,
		'g_edit_subject'	=> 0,
		'g_deledit_interval'	=>	600,
		'g_delete_posts'	=> 0,
		'g_delete_topics'	=> 0,
		'g_set_title'	=> 0,
		'g_search'	=> 1,
		'g_search_users'	=> 0,
		'g_send_email'	=> 1,
		'g_post_flood'	=> 60,
		'g_use_pm'		=> 0,
		'g_pm_limit'	=> 0,
		'g_search_flood'	=> 30,
		'g_email_flood'	=> 60,
		'g_report_flood'	=> 60,
		'g_rep_enabled'	=> 1,
		'g_rep_interval'	=> 5,
		'g_rep_plus'	=> 5,
		'g_rep_minus'	=> 5,
		'g_colour'		=> '',
		'g_attach_files'	=> 0,
		'g_max_attachments'	=> 0,
		'g_max_size'		=> 1,
		'g_pm_folder_limit'	=>	1,
	);
	
	$db->insert('groups', $insert);
	
	$insert = array(
		'group_id'	=>	4,
		'username'	=>	$lang_install['Guest'],
		'password'	=>	$lang_install['Guest'],
		'email'		=>	$lang_install['Guest'],
	);

	// Insert guest and first admin user
	$db->insert('users', $insert);
	
	$insert = array(
		'group_id'	=>	1,
		'username'	=>	$username,
		'password'	=>	panther_hash($password1.$password_salt),
		'salt'		=>	$password_salt,
		'email'		=>	$email,
		'language'	=>	$default_lang,
		'style'		=>	$default_style,
		'num_posts'	=>	1,
		'last_post'	=>	$now,
		'registered'=>	$now,
		'registration_ip'	=>	get_remote_address(),
		'last_visit'	=>	$now,
		'pm_enabled'	=>	1,
		'use_gravatar'	=>	1, // Set gravatar enabled (we don't know if file uploads are enabled yet, and there is definitely no avatar uploaded for them)
	);
	
	$db->insert('users', $insert);
	
	$insert = array(
		'rank'	=>	$lang_install['New member'],
		'min_posts'	=>	0,
	);

	$db->insert('ranks', $insert);
	$insert = array(
		'rank'	=>	$lang_install['Member'],
		'min_posts'	=>	10,
	);

	$db->insert('ranks', $insert);
	$avatars = in_array(strtolower(@ini_get('file_uploads')), array('on', 'true', '1')) ? 1 : 0;	// Enable/disable avatars depending on file_uploads setting in PHP configuration

	$panther_smilies = array(
		':)' => 'smile.png',
		'=)' => 'smile.png',
		':|' => 'neutral.png',
		'=|' => 'neutral.png',
		':(' => 'sad.png',
		'=(' => 'sad.png',
		':D' => 'big_smile.png',
		'=D' => 'big_smile.png',
		':o' => 'yikes.png',
		':O' => 'yikes.png',
		';)' => 'wink.png',
		':/' => 'hmm.png',
		':P' => 'tongue.png',
		':p' => 'tongue.png',
		':lol:' => 'lol.png',
		':mad:' => 'mad.png',
		':rolleyes:' => 'roll.png',
		':cool:' => 'cool.png',
		'xD'	=>	'xd.png',
		'XD'	=>	'xd.png',
		':what:'	=>	'what.png',
		'ZzZz'	=>	'sleep.png',
		':angel:'	=>	'angel.png',
		':angry:'	=>	'angry.png',
		':\'('	=>	'cry.png',
		':\')'	=>	'happycry.png',
		':blush:'	=>	'ashame.png',
	);
	
	$i = 0;
	foreach ($panther_smilies as $code => $image)
	{
		$insert = array(
			'code'	=>	$code,
			'image'	=>	$image,
			'disp_position'	=>	$i,
		);

		$db->insert('smilies', $insert);
		$i++;
	}
	
	// Take an educated guess at the correct path- and replace backslashes with forward slashes in case we're using a Windows server.
	$current_path = str_replace('\\', '/', __DIR__);

	if (!function_exists('exec') || strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
	{
		$task_type = 0;
		$alerts[] = $lang_install['Tasks alert'];
	}
	else
	{
		$task_type = 1;
		exec('echo -e "`crontab -l`\n0 0 1 * * '.substr(PANTHER_ROOT, 0, -3).'cron.php'.'" | crontab -');
	}

	$insert = array(
		'title' => $lang_install['Task title'],
		'next_run' => strtotime('+10 minutes'),
		'script' => 'forum_updater',
		'minute' => 0,
		'hour' => 0,
		'day' => 1,
		'month' => '*',
		'week_day' => '*',
	);

	$db->insert('tasks', $insert);

	// Insert config data
	$panther_config = array(
		'o_cur_version'				=> FORUM_VERSION,
		'o_board_title'				=> $title,
		'o_board_desc'				=> $description,
		'o_default_timezone'		=> 0,
		'o_time_format'				=> 'H:i:s',
		'o_date_format'				=> 'd-m-Y',
		'o_timeout_visit'			=> 1800,
		'o_timeout_online'			=> 300,
		'o_redirect_delay'			=> 1,
		'o_favicon'					=> 'favicon.ico',
		'o_show_version'			=> 0,
		'o_show_user_info'			=> 1,
		'o_show_post_count'			=> 1,
		'o_signatures'				=> 1,
		'o_login_queue'				=> 1,
		'o_queue_size'				=> 30,
		'o_max_attempts'			=> 5,
		'o_cookie_name'				=> $cookie_name,
		'o_cookie_seed'				=> $cookie_seed,
		'o_cookie_secure'			=> (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? '1' : '0',
		'o_force_ssl'				=> 0,
		'o_email_banned'			=> 0,
		'o_polls'					=> 1,
		'o_cookie_path'				=> '/',
		'o_cookie_domain'			=> '',
		'o_reputation'				=> 1,
		'o_rep_type'				=> 1,
		'o_ban_email'				=> 0,
		'o_ranks'					=> 1,
		'o_task_type'				=> $task_type,
		'o_attachments'				=> $avatars,
		'o_max_upload_size'			=> 10485760,
		'o_http_authentication'		=> (PHP_SAPI == 'cgi-fcgi') ? 0 : 1,
		'o_create_orphans'			=> 0,
		'o_admin_notes'				=> $lang_install['admin notes'],
		'o_attachment_icons'		=> 1,
		'o_attachment_icon_path'	=> 'assets/images/attachments/',
		'o_attachment_icon_dir'		=> '',
		'o_attachments_dir'			=> $current_path.'/attachments/',
		'o_attachment_images'		=> 'text.png,text.png,doc.png,doc.png,doc.png,audio.png,audio.png,audio.png,video.png,video.png,video.png,image.png,image.png,image.png,image.png,compress.png,compress.png,compress.png,compress.png,compress.png',
		'o_attachment_extensions'	=> 'txt,log,doc,docx,pdf,wav,mp3,ogg,avi,mpg,mpeg,png,jpg,jpeg,gif,zip,rar,7z,gz,tar',
		'o_always_deny'				=> 'html,htm,php,php3,php4,php5,phtml,exe,com,bat,pif,application,gadget,msi,msp,scr,hta,cpl,msc,jar,cmd,vb,vbs,vbe,sbe,js,jse,ws,wsf,wsc,wsh,ps1,ps1xml,ps2,ps2xml,psc2,msh,msh1,msh2,msh1xml,msh2xml,scf,lnk,inf,reg,css,dll,xml,docm,cgi,pl,py,asp,shtml,sh,ini,exe1,exe_renamed',
		'o_attachment_extensions'	=> 'txt,log,doc,pdf,wav,mp3,ogg,avi,mpg,mpeg,png,jpg,jpeg,gif,zip,rar,7z,gz,tar',		
		'o_rep_abuse'				=> 5,
		'o_debug_mode'				=> 0,
		'o_url_type'				=> 'default',
		'o_style_path'				=> 'style',
		'o_theme'					=> '',
		'o_style_dir'				=> '',
		'o_show_queries'			=> 0,
		'o_warnings'				=> 1,
		'o_custom_warnings'			=> 1,
		'o_warning_status'			=> 1,
		'o_private_messaging'		=> 1,
		'o_update_type'				=> 3,
		'o_sfs_api'					=> '',
		'o_tinypng_api'				=> '',
		'o_cloudflare_api'			=> '',
		'o_cloudflare_domain'		=> '',
		'o_cloudflare_email'		=> '',
		'o_smilies'					=> 1,
		'o_smilies_path'			=> 'assets/images/smilies',
		'o_smilies_dir'				=> '',
		'o_smilies_width'			=> 20,
		'o_smilies_height'			=> 20,
		'o_smilies_size'			=> 10240,
		'o_smilies_sig'				=> 1,
		'o_make_links'				=> 1,
		'o_default_lang'			=> $default_lang,
		'o_default_style'			=> $default_style,
		'o_default_user_group'		=> 6,
		'o_topic_review'			=> 15,
		'o_disp_topics_default'		=> 30,
		'o_disp_posts_default'		=> 25,
		'o_image_group_width'		=> 120,
		'o_image_group_height'		=> 28,
		'o_image_group_size'		=> 10240,
		'o_image_group_path'		=> 'assets/images/group/',
		'o_image_group_dir'			=> '',
		'o_indent_num_spaces'		=> 4,
		'o_quote_depth'				=> 3,
		'o_user_tags_max'			=> 7,
		'o_quickpost'				=> 1,
		'o_users_online'			=> 1,
		'o_censoring'				=> 0,
		'o_use_editor'				=> 1,
		'o_popular_topics'			=> 25,
		'o_delete_full'				=> 0,
		'o_polls'					=> 1,
		'o_max_poll_fields'			=> 20,		
		'o_show_dot'				=> 0,
		'o_topic_views'				=> 1,
		'o_quickjump'				=> 1,
		'o_gzip'					=> 0,
		'o_additional_navlinks'		=> '',
		'o_report_method'			=> 0,
		'o_regs_report'				=> 0,
		'o_default_email_setting'	=> 1,
		'o_mailing_list'			=> $email,
		'o_avatars'					=> 1,
		'o_avatar_upload'			=> $avatars,
		'o_avatars_path'			=> 'assets/images/avatars/',
		'o_avatars_dir'				=> '',
		'o_image_dir'				=> $base_url.'/assets/images/',
		'o_image_path'				=> 'assets/images/',
		'o_avatar'					=> 'jpg',
		'o_js_dir'					=> $base_url.'/assets/js/',
		'o_email_name'				=> $email_title,
		'o_avatars_width'			=> 150,
		'o_avatars_height'			=> 150,
		'o_avatars_size'			=> 100000,
		'o_search_all_forums'		=> 1,
		'o_base_url'				=> $base_url,
		'o_admin_email'				=> $email,
		'o_webmaster_email'			=> $email,
		'o_forum_subscriptions'		=> 1,
		'o_topic_subscriptions'		=> 1,
		'o_smtp_host'				=> NULL,
		'o_smtp_user'				=> NULL,
		'o_smtp_pass'				=> NULL,
		'o_smtp_ssl'				=> 0,
		'o_regs_allow'				=> 1,
		'o_regs_verify'				=> 1,
		'o_announcement'			=> 0,
		'o_announcement_message'	=> $lang_install['Announcement'],
		'o_archiving'				=> 0,
		'o_archive_rules'			=> '',
		'o_rules'					=> 0,
		'o_rules_message'			=> $lang_install['Rules'],
		'o_maintenance'				=> 0,
		'o_max_pm_receivers'		=> 15,
		'o_maintenance_message'		=> $lang_install['Maintenance message'],
		'o_default_dst'				=> 0,
		'o_feed_type'				=> 2,
		'o_feed_ttl'				=> 0,
		'o_colourize_groups'		=> '',
		'p_message_bbcode'			=> 1,
		'p_message_img_tag'			=> 1,
		'p_message_all_caps'		=> 1,
		'p_subject_all_caps'		=> 1,
		'p_sig_all_caps'			=> 1,
		'p_sig_bbcode'				=> 1,
		'p_sig_img_tag'				=> 0,
		'p_sig_length'				=> 500,
		'p_sig_lines'				=> 6,
		'p_allow_banned_email'		=> 0,
		'p_allow_dupe_email'		=> 0,
		'p_force_guest_email'		=> 1,
	);

	foreach ($panther_config as $conf_name => $conf_value)
	{
		$insert = array(
			'conf_name'	=>	$conf_name,
			'conf_value'	=>	$conf_value,
		);

		$db->insert('config', $insert);
	}

	$folders = array($lang_install['New'], $lang_install['Inbox'], $lang_install['Archived']);
	foreach ($folders as $folder)
	{
		$insert = array(
			'name'	=>	$folder,
			'user_id'	=>	1,
		);

		$db->insert('folders', $insert);
	}

	$insert = array(
		'cat_name'	=>	$lang_install['Test category'],
		'disp_position'	=>	1,
	);

	$db->insert('categories', $insert);

	$insert = array(
		'forum_name'	=>	$lang_install['Test forum'],
		'forum_desc'	=>	$lang_install['This is just a test forum'],
		'num_topics'	=>	1,
		'num_posts'		=>	1,
		'last_post'		=>	$now,
		'last_post_id'	=>	1,
		'last_topic'	=>	sprintf($lang_install['Test post'], FORUM_VERSION),
		'last_topic_id'	=>	1,
		'last_poster'	=>	$username,
		'disp_position'	=>	1,
		'cat_id'		=>	1,
		'quickjump'		=>	1,
	);

	$db->insert('forums', $insert);

	$insert = array(
		'poster'	=>	$username,
		'subject'	=>	sprintf($lang_install['Test post'], FORUM_VERSION),
		'posted'	=>	$now,
		'first_post_id'		=>	1,
		'last_post'		=>	$now,
		'last_post_id'	=>	1,
		'last_poster'	=>	$username,
		'forum_id'		=>	1,
	);

	$db->insert('topics', $insert);

	$insert = array(
		'poster'	=>	$username,
		'poster_id'	=>	2,
		'poster_ip'	=>	get_remote_address(),
		'message'	=>	$lang_install['Message'],
		'posted'	=>	$now,
		'topic_id'		=>	1,
	);

	$db->insert('posts', $insert);

	// Index the test post so searching for it works
	require PANTHER_ROOT.'include/search_idx.php';
	update_search_index('post', 1, $lang_install['Message'], $lang_install['Test post']);

	$db->end_transaction();

	// Check if we disabled uploading avatars because file_uploads was disabled
	if ($avatars == '0')
		$alerts[] = $lang_install['Alert upload'];

	// Generate the config.php file data
	$file = generate_config_file($config);

	// Attempt to write config.php and serve it up for download if writing fails
	$written = false;
	if (forum_is_writable(PANTHER_ROOT.'include'))
	{
		$fh = @fopen(PANTHER_ROOT.'include/config.php', 'wb');
		if ($fh)
		{
			fwrite($fh, $file);
			fclose($fh);

			$written = true;
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $lang_install['Panther Installation'] ?></title>
<style type="text/css">
html {
  font-family: sans-serif;
  -ms-text-size-adjust: 100%;
  -webkit-text-size-adjust: 100%;
  	box-sizing: border-box;
}

body { 
    font-family: Arial, Verdana, sans-serif; 
	font-size: 14px;
	background: #e4e4e4;
	color: #383838;
    margin: 0;
}

*,
*:before,
*:after {
	box-sizing: inherit;
}

.clearfix:before,
.clearfix:after,
.brd-header:before, 
.brd-header:after,
.brd-footer:before,
.brd-footer:after,
.brd-main:before,
.brd-main:after,
.box .inbox:before,
.box .inbox:after { 
  content: ""; 
  display: block; 
  height: 0; 
  overflow: hidden; 
}  

.clearfix:after,
.brd-header:after,
.brd-footer:after,
.brd-main:after,
.box .inbox:after { 
  clear: both; 
}  

.clearfix { 
  zoom: 1; 
}

.brd-main, .brd-header {
	width: 800px;
	margin: auto;
}

.row, .box, .col {
	float: left;
	width: 100%;
}
.row {
	margin: 0 0 10px 0;
}
.col {
	padding: 5px;
}

.brd-header {
    background: #34495e;
    padding: 30px;
    margin: 20px auto;
    color: white;
}
.brd-header p {
   margin: 0;
   padding: 0;
}
.brd-header h1 {
	font-size: 30px;
   margin: 0;
   padding: 0 0 5px 0;
   text-transform: uppercase;
}

.brd-main {
    background: none;
	border: 0;
	padding: 0;
}

h2 {
    background-color: #27ae60;
    padding: 15px;
    color: white;
    font-size: 18px;
    font-weight: 400;
    font-family: lato;
	width: 100%;
	margin: 0 0 20px 0;
}

.blockinfo {
    margin: 0;
    background: #2980B9;
    display: flex;
    align-items: center;
    padding: 15px;
    color: white;
}
.blockinfo h3{
    font-size: 16px;
    font-weight: 400;
    width: 100%;
    margin: 0;
    width: 40%;
			width: 40%;
}

.blockinfo p {
	margin: 0;
	width: 60%;
	text-align: right;

}
.box {
	margin: 0;
}

.inbox {
	width: 90%;
	margin: auto;
	padding: 20px 0;
}
.row .col {
	width: 100%;
}
.block {
    float: left;
    width: 100%;
    margin: 0 0 20px 0;
    background: #f7f7f7;
    padding: 1px;
}

.boxtitle {
	background: #34495E;
    font-size: 12px;
    font-weight: bold;
    padding: 5px 15px;
    margin: 0 0 1px 0;
    width: 100%;
    color: white;
}
.title {
    font-size: 14px;
    padding: 0 0 3px 0;
    float: left;
    width: 100%;
	font-weight: bold;
}

p.boxinfo {
	margin: 0;
    background: #D8D8D8;
    padding: 5px 10px;
}

input[type="submit"] {
    cursor: pointer;
}
	
input, select {
	float: left;
    width: 100%;
    border: none;
    border-radius: 0;
    outline: none;
    padding: 8px 10px;
    font-weight: normal;
    border: 1px solid #CACACA;
    transition: border 0.5s;
    color: #383838;
    font-size: 14px;
}

.required  {
	color: #e74c3c;
	font-weight: normal;
}

input.start-install {
    background: #27ae60;
    font-size: 18px;
    text-transform: uppercase;
    font-weight: bold;
    padding: 50px 10px;
    float: left;
    margin: 0 0 20px 0;
    width: 100%;
	color: white;
	letter-spacing: 1px;
	border: 0;
}

input.start-install:hover {
    background: #187D43;

}
input.start-install:focus {
    background: #0C582C;
}

</style>
</head>
<body>

<div id="pantherinstall" class="panther">
<div class="pantherwrap">

<div id="brdheader" class="brd-header">
		<div id="brdtitle" class="inbox">
			<h1><?php echo $lang_install['Panther Installation'] ?></h1>
			<div id="brddesc"><p><?php echo $lang_install['Panther has been installed'] ?></p></div>
		</div>
</div>

<div class="brd-main">

<div class="block">
	<h2><?php echo $lang_install['Final instructions'] ?></span></h2>

<?php

if (!$written)
{

?>
		<form method="post" action="install.php">
			
				<div class="box">
					<p class="boxinfo"><?php echo $lang_install['Info 17'] ?></p>
					<p class="boxinfo"><?php echo $lang_install['Info 18'] ?></p>
				</div>
				<input type="hidden" name="generate_config" value="1" />
				<input type="hidden" name="db_host" value="<?php echo $config['host']; ?>" />
				<input type="hidden" name="db_name" value="<?php echo panther_htmlspecialchars($config['db_name']); ?>" />
				<input type="hidden" name="db_username" value="<?php echo panther_htmlspecialchars($config['username']); ?>" />
				<input type="hidden" name="db_password" value="<?php echo panther_htmlspecialchars($config['password']); ?>" />
				<input type="hidden" name="db_prefix" value="<?php echo panther_htmlspecialchars($config['prefix']); ?>" />

<?php if (!empty($alerts)): ?>				<div class="box">
					<ul class="row error-list">
<?php

foreach ($alerts as $cur_alert)
	echo "\t\t\t\t\t".'<li>'.$cur_alert.'</li>'."\n";
?>
					</ul>
				</div>
<?php endif; ?>			
			<input type="submit" value="<?php echo $lang_install['Download config.php file'] ?>"/>
		</form>

<?php

}
else
{

?>
		<div class="box">
	
				
					<p class="boxinfo"><strong><?php echo $lang_install['Panther fully installed'] ?></strong></p>
			
	
		</div>

<?php if (!empty($alerts)): ?>			<div class="box">		
					<ul class="row error-list">
<?php

foreach ($alerts as $cur_alert)
	echo "\t\t\t\t\t".'<li>'.$cur_alert.'</li>'."\n";
?>
					</ul>
				</div>
<?php endif;

}

?>

</div>

</div>

</div>

</div>

</body>
</html>
<?php
}