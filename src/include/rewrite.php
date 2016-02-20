<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

define('PANTHER_ROOT', __DIR__.'/../');
require PANTHER_ROOT.'include/common.php';

// Fix the request URI (both IIS6 & IIS7 break it)
if (!isset($_SERVER['REQUEST_URI']) || (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']) && strpos($_SERVER['REQUEST_URI'], '?') === false))
{
	// Workaround for a bug in IIS7
	if (isset($_SERVER['HTTP_X_ORIGINAL_URL']))
		$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
	else if ($panther_config['o_url_type'] == 'default.php') // IIS6 also doesn't set REQUEST_URI ....
	{
		$requested_page = str_replace(array('%26', '%3D', '%2F', '%3F'), array('&', '=', '/', '?'), rawurlencode($_SERVER['PHP_SELF']));
		$_SERVER['REQUEST_URI'] = $requested_page.(isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '');
	}
	else // Nothing can be done
		message($lang_common['Request URI error']);
}

($hook = get_extensions('rewrite_before_rewrite')) ? eval($hook) : null;

// If query string is not set properly, create one and set $_GET manually
if ((!isset($_SERVER['QUERY_STRING']) || empty($_SERVER['QUERY_STRING'])) && strpos($_SERVER['REQUEST_URI'], '?') !== false)
{
	$_SERVER['QUERY_STRING'] = parse_url('http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
	$_SERVER['QUERY_STRING'] = isset($_SERVER['QUERY_STRING']['query']) ? $_SERVER['QUERY_STRING']['query'] : '';
	parse_str($_SERVER['QUERY_STRING'], $_GET);
}

// We determine the path to the script, since we need to separate the path from the data to be rewritten
$path_to_script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (substr($path_to_script, -1) != '/')
	$path_to_script = $path_to_script.'/';

// We create our own request URI with the path removed and only the parts to rewrite included
$request_uri = substr(urldecode($_SERVER['REQUEST_URI']), (strlen($path_to_script) - 8));
if (strpos($request_uri, '?') !== false)
	$request_uri = substr($request_uri, 0, strpos($request_uri, '?'));

$rewritten_url = '';
$url_parts = array();
foreach ($rewrite_rules as $rule => $rewrite_to)
{
	if (preg_match($rule, $request_uri))
	{ 
		$rewritten_url = preg_replace($rule, $rewrite_to, $request_uri);
		$url_parts = explode('?', $rewritten_url);

		// If there is a query string
		if (isset($url_parts[1]))
		{
			$query_string = explode('&', $url_parts[1]);

			// Set $_GET properly for all of the variables. We also set $_REQUEST if it's not already set
			foreach ($query_string as $cur_param)
			{
				$param_data = explode('=', $cur_param);

				// Sometimes, parameters don't set a value (eg: script.php?foo), so we set them to null
				$param_data[1] = isset($param_data[1]) ? $param_data[1] : null;

				// We don't want to be overwriting values in $_REQUEST that were set in POST or COOKIE
				if (!isset($_POST[$param_data[0]]) && !isset($_COOKIE[$param_data[0]]))
					$_REQUEST[$param_data[0]] = urldecode($param_data[1]);

				$_GET[$param_data[0]] = urldecode($param_data[1]);
			}
		}
		break;
	}
}

// If we don't know what to rewrite to, we show a bad request messsage
if (empty($rewritten_url))
    message(sprintf($lang_common['Not found'], $request_uri), false, '404 Not Found');

// We change $_SERVER['PHP_SELF'] so that it reflects the file we're actually loading
$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'] = str_replace('include/rewrite.php', $url_parts[0], $_SERVER['PHP_SELF']);
$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'].((isset($url_parts[1])) ? '?'.$url_parts[1] : '');

$online = update_users_online();
require PANTHER_ROOT.$url_parts[0];