<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

//
// Cookie stuff!
//
function check_cookie(&$panther_user)
{
	global $db, $panther_config;
	$now = time();

	// If the cookie is set and it matches the correct pattern, then read the values from it
	if (isset($_COOKIE[$panther_config['o_cookie_name']]) && preg_match('%^(\d+)\|([0-9a-fA-F]+)\|(\d+)\|([0-9a-fA-F]+)$%', $_COOKIE[$panther_config['o_cookie_name']], $matches))
	{
		$cookie = array(
			'user_id'			=> intval($matches[1]),
			'password_hash' 	=> $matches[2],
			'expiration_time'	=> intval($matches[3]),
			'cookie_hash'		=> $matches[4],
		);
	}

	// If it has a non-guest user, and hasn't expired
	if (isset($cookie) && $cookie['user_id'] > 1 && $cookie['expiration_time'] > $now)
	{
		// If the cookie has been tampered with
		if (!panther_hash_equals(hash_hmac('sha512', $cookie['user_id'].'|'.$cookie['expiration_time'], $panther_config['o_cookie_seed'].'_cookie_hash'), $cookie['cookie_hash']))
		{
			$expire = $now + 31536000; // The cookie expires after a year
			panther_setcookie(1, panther_hash(uniqid(rand(), true)), $expire);
			set_default_user();

			return;
		}

		$data = array(
			':id'	=>	$cookie['user_id'],
		);

		// Check if there's a user with the user ID and password hash from the cookie
		$ps = $db->run('SELECT u.*, g.*, o.logged, o.idle FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id LEFT JOIN '.$db->prefix.'online AS o ON o.user_id=u.id WHERE u.id=:id', $data);
		$panther_user = $ps->fetch();

		// If user authorisation failed
		if (!isset($panther_user['id']) || !panther_hash_equals(hash_hmac('sha512', $panther_user['login_key'], $panther_config['o_cookie_seed'].'_password_hash'), $cookie['password_hash']))
		{
			$expire = $now + 31536000; // The cookie expires after a year
			panther_setcookie(1, panther_hash(uniqid(rand(), true)), $expire);
			set_default_user();

			return;
		}

		// Send a new, updated cookie with a new expiration timestamp
		$expire = ($cookie['expiration_time'] > $now + $panther_config['o_timeout_visit']) ? $now + 1209600 : $now + $panther_config['o_timeout_visit'];
		panther_setcookie($panther_user['id'], $panther_user['login_key'], $expire);

		// Set a default language if the user selected language no longer exists
		if (!file_exists(PANTHER_ROOT.'lang/'.$panther_user['language']))
			$panther_user['language'] = $panther_config['o_default_lang'];

		$style_root = (($panther_config['o_style_path'] != 'style') ? $panther_config['o_style_path'] : PANTHER_ROOT.$panther_config['o_style_path']).'/';

		// Set a default style if the user selected style no longer exists
		if (!file_exists($style_root.$panther_user['style'].'.css'))
			$panther_user['style'] = $panther_config['o_default_style'];

		if (!$panther_user['disp_topics'])
			$panther_user['disp_topics'] = $panther_config['o_disp_topics_default'];

		if (!$panther_user['disp_posts'])
			$panther_user['disp_posts'] = $panther_config['o_disp_posts_default'];

		// Define this if you want this visit to affect the online list and the users last visit data
		if (!defined('PANTHER_QUIET_VISIT'))
		{
			// Update the online list
			if (!$panther_user['logged'])
			{
				$panther_user['logged'] = $now;

				$data = array(
					':id'	=>	$panther_user['id'],
					':ident'	=>	$panther_user['username'],
					':logged'	=>	$panther_user['logged'],
				);

				// REPLACE INTO avoids a user having two rows in the online table
				$db->run('REPLACE INTO '.$db->prefix.'online (user_id, ident, logged) VALUES (:id, :ident, :logged)', $data);

				// Reset tracked topics
				set_tracked_topics(null);
			}
			else
			{
				$data = array(
					':id'	=>	$panther_user['id'],
				);

				// Special case: We've timed out, but no other user has browsed the forums since we timed out
				if ($panther_user['logged'] < ($now-$panther_config['o_timeout_visit']))
				{
					$update = array(
						'last_visit'	=>	$panther_user['logged'],
					);

					$db->update('users', $update, 'id=:id', $data);
					$panther_user['last_visit'] = $panther_user['logged'];
				}

				$update = array(
					'logged'	=>	$now,
				);

				if ($panther_user['idle'] == '1')
					$update['idle'] = 0;

				$db->update('online', $update, 'user_id=:id', $data);

				// Update tracked topics with the current expire time
				if (isset($_COOKIE[$panther_config['o_cookie_name'].'_track']))
					forum_setcookie($panther_config['o_cookie_name'].'_track', $_COOKIE[$panther_config['o_cookie_name'].'_track'], $now + $panther_config['o_timeout_visit']);
			}
		}
		else
		{
			if (!$panther_user['logged'])
				$panther_user['logged'] = $panther_user['last_visit'];
		}

		$panther_user['is_guest'] = false;
		$panther_user['is_admmod'] = $panther_user['g_id'] == PANTHER_ADMIN || $panther_user['g_moderator'] == '1';
		$panther_user['is_admin'] = $panther_user['g_id'] == PANTHER_ADMIN || $panther_user['g_moderator'] == '1' && $panther_user['g_admin'] == '1';
		$panther_user['is_bot'] = false;
	}
	else
		set_default_user();
}

function panther_hash_equals($hash, $input)
{
	if (function_exists('hash_equals'))
		return hash_equals((string)$hash, $input);

	$input_length = strlen($input);
	if ($input_length !== strlen($hash))
		return false;

	$result = 0;
	for ($i = 0; $i < $input_length; $i++)
		$result |= ord($input[$i]) ^ ord($hash[$i]);

	return $result === 0;
}


//
// Try to determine the current URL
//
function get_current_url($max_length = 0)
{
	$protocol = get_current_protocol();
	$port = (isset($_SERVER['SERVER_PORT']) && (($_SERVER['SERVER_PORT'] != '80' && $protocol == 'http') || ($_SERVER['SERVER_PORT'] != '443' && $protocol == 'https')) && strpos($_SERVER['HTTP_HOST'], ':') === false) ? ':'.$_SERVER['SERVER_PORT'] : '';

	$url = urldecode($protocol.'://'.$_SERVER['HTTP_HOST'].$port.$_SERVER['REQUEST_URI']);

	if (strlen($url) <= $max_length || $max_length == 0)
		return $url;

	// We can't find a short enough url
	return null;
}

//
// Fetch the current protocol in use - http or https
//
function get_current_protocol()
{
	$protocol = 'http';

	// Check if the server is claiming to using HTTPS
	if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off')
		$protocol = 'https';

	// If we are behind a reverse proxy try to decide which protocol it is using
	if (defined('FORUM_BEHIND_REVERSE_PROXY'))
	{
		// Check if we are behind a Microsoft based reverse proxy
		if (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) != 'off')
			$protocol = 'https';

		// Check if we're behind a "proper" reverse proxy, and what protocol it's using
		if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']))
			$protocol = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
	}

	return $protocol;
}

function check_ssl_state()
{
	global $panther_config;
	if ($panther_config['o_force_ssl'] == '1' && get_current_protocol() == 'http')
	{
		header('Location: '.str_replace('http://', 'https://', get_current_url()));
		exit;
	}
}

//
// Fetch the base_url, optionally support HTTPS and HTTP
//
function get_base_url($support_https = true)
{
	global $panther_config;
	static $base_url;

	if (!$support_https)
		return $panther_config['o_base_url'];

	if (!isset($base_url))
	{
		// Make sure we are using the correct protocol
		$base_url = str_replace(array('http://', 'https://'), get_current_protocol().'://', $panther_config['o_base_url']);
	}

	return $base_url;
}

//
// Fetch admin IDs
//
function get_admin_ids()
{
	if (file_exists(FORUM_CACHE_DIR.'cache_admins.php'))
		include FORUM_CACHE_DIR.'cache_admins.php';

	if (!defined('PANTHER_ADMINS_LOADED'))
	{
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';

		generate_admins_cache();
		require FORUM_CACHE_DIR.'cache_admins.php';
	}

	return $panther_admins;
}

//
// Fill $panther_user with default values (for guests)
//
function set_default_user()
{
	global $db, $panther_user, $panther_config;

	$remote_addr = get_remote_address();
	$remote_addr = isbotex($remote_addr);

	$data = array(
		':ident'	=>	$remote_addr,
	);

	// Fetch guest user
	$ps = $db->run('SELECT u.*, g.*, o.logged, o.last_post, o.last_search FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id LEFT JOIN '.$db->prefix.'online AS o ON o.ident=:ident WHERE u.id=1', $data);
	if (!$ps->rowCount())
		error_handler(E_ERROR, 'Unable to fetch guest information. Your database must contain both a guest user and a guest user group.', __FILE__, __LINE__);

	$panther_user = $ps->fetch();

	// Update online list
	if (!$panther_user['logged'])
	{
		$panther_user['logged'] = time();
		$data = array(
			':ident'	=>	$remote_addr,
			':logged'	=>	$panther_user['logged'],
		);

		// REPLACE INTO avoids a user having two rows in the online table
		$db->run('REPLACE INTO '.$db->prefix.'online (user_id, ident, logged) VALUES(1, :ident, :logged)', $data);
	}
	else
	{
		$update = array(
			'logged'	=>	time(),
		);

		$data = array(
			':ident' => $remote_addr,
		);

		$db->update('online', $update, 'ident=:ident', $data);
	}

	$panther_user['disp_topics'] = $panther_config['o_disp_topics_default'];
	$panther_user['disp_posts'] = $panther_config['o_disp_posts_default'];
	$panther_user['timezone'] = $panther_config['o_default_timezone'];
	$panther_user['dst'] = $panther_config['o_default_dst'];
	$panther_user['language'] = $panther_config['o_default_lang'];
	$panther_user['style'] = $panther_config['o_default_style'];
	$panther_user['is_guest'] = true;
	$panther_user['is_admmod'] = false;
	$panther_user['is_admin'] = false;
	$panther_user['is_bot'] = (strpos($remote_addr, '[Bot]') !== false);
}

//
// Set a cookie, Panther style!
// Wrapper for forum_setcookie
//
function panther_setcookie($user_id, $password_hash, $expire)
{
	global $panther_config;
	forum_setcookie($panther_config['o_cookie_name'], $user_id.'|'.hash_hmac('sha512', $password_hash, $panther_config['o_cookie_seed'].'_password_hash').'|'.$expire.'|'.hash_hmac('sha512', $user_id.'|'.$expire, $panther_config['o_cookie_seed'].'_cookie_hash'), $expire);
}

//
// Set a cookie, Panther style!
//
function forum_setcookie($name, $value, $expire)
{
	global $panther_config;

	if ($expire - time() - $panther_config['o_timeout_visit'] < 1)
		$expire = 0;

	// Enable sending of a P3P header
	header('P3P: CP="CUR ADM"');
	setcookie($name, $value, $expire, $panther_config['o_cookie_path'], $panther_config['o_cookie_domain'], $panther_config['o_cookie_secure'], true);
}

//
// Check whether the connecting user is banned (and delete any expired bans while we're at it)
//
function check_bans()
{
	global $db, $panther_config, $lang_common, $panther_user, $panther_bans;

	// Admins and moderators aren't affected
	if ($panther_user['is_admmod'] || !$panther_bans)
		return;

	// Add a dot or a colon (depending on IPv4/IPv6) at the end of the IP address to prevent banned address
	// 192.168.0.5 from matching e.g. 192.168.0.50
	$user_ip = get_remote_address();
	$user_ip .= (strpos($user_ip, '.') !== false) ? '.' : ':';

	$bans_altered = false;
	$is_banned = false;

	foreach ($panther_bans as $cur_ban)
	{
		// Has this ban expired?
		if ($cur_ban['expire'] != '' && $cur_ban['expire'] <= time())
		{
			$data = array(
				':id'	=>	$cur_ban['id'],
			);

			$db->delete('bans', 'id=:id', $data);
			$bans_altered = true;
			continue;
		}

		if ($cur_ban['username'] != '' && utf8_strtolower($panther_user['username']) == utf8_strtolower($cur_ban['username']))
			$is_banned = true;

		if ($cur_ban['ip'] != '')
		{
			$cur_ban_ips = explode(' ', $cur_ban['ip']);

			$num_ips = count($cur_ban_ips);
			for ($i = 0; $i < $num_ips; ++$i)
			{
				// Add the proper ending to the ban
				if (strpos($user_ip, '.') !== false)
					$cur_ban_ips[$i] = $cur_ban_ips[$i].'.';
				else
					$cur_ban_ips[$i] = $cur_ban_ips[$i].':';

				if (substr($user_ip, 0, strlen($cur_ban_ips[$i])) == $cur_ban_ips[$i])
				{
					$is_banned = true;
					break;
				}
			}
		}

		if ($is_banned)
		{
			$data = array(
				':ident'	=>	$panther_user['username'],
			);

			$db->delete('online', 'ident=:ident', $data);
			message($lang_common['Ban message'].' '.(($cur_ban['expire'] != '') ? $lang_common['Ban message 2'].' '.strtolower(format_time($cur_ban['expire'], true)).'. ' : '').(($cur_ban['message'] != '') ? $lang_common['Ban message 3'].'<br /><br /><strong>'.$cur_ban['message'].'</strong><br /><br />' : '<br /><br />').$lang_common['Ban message 4'].' '.$panther_config['o_admin_email'], true);
		}
	}

	// If we removed any expired bans during our run-through, we need to regenerate the bans cache
	if ($bans_altered)
	{
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';

		generate_bans_cache();
	}
}

//
// Check username
//
function check_username($username, $exclude_id = null)
{
	global $db, $panther_config, $errors, $lang_prof_reg, $lang_register, $lang_common, $panther_bans;

	// Include UTF-8 function
	require_once PANTHER_ROOT.'include/utf8/strcasecmp.php';

	// Convert multiple whitespace characters into one (to prevent people from registering with indistinguishable usernames)
	$username = preg_replace('%\s+%s', ' ', $username);

	// Validate username
	if (panther_strlen($username) < 2)
		$errors[] = $lang_prof_reg['Username too short'];
	else if (panther_strlen($username) > 25) // This usually doesn't happen since the form element only accepts 25 characters
		$errors[] = $lang_prof_reg['Username too long'];
	else if (!strcasecmp($username, 'Guest') || !utf8_strcasecmp($username, $lang_common['Guest']))
		$errors[] = $lang_prof_reg['Username guest'];
	else if (preg_match('%[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}%', $username) || preg_match('%((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))%', $username))
		$errors[] = $lang_prof_reg['Username IP'];
	else if ((strpos($username, '[') !== false || strpos($username, ']') !== false) && strpos($username, '\'') !== false && strpos($username, '"') !== false)
		$errors[] = $lang_prof_reg['Username reserved chars'];
	else if (preg_match('%(?:\[/?(?:b|u|s|ins|del|em|i|h|colou?r|quote|code|img|url|email|list|\*|topic|post|forum|user)\]|\[(?:img|url|quote|list)=)%i', $username))
		$errors[] = $lang_prof_reg['Username BBCode'];

	// Check username for any censored words
	if ($panther_config['o_censoring'] == '1' && censor_words($username) != $username)
		$errors[] = $lang_register['Username censor'];
	
	$where_cond = '(UPPER(username)=UPPER(:username) OR UPPER(username)=UPPER(:username2)) AND id>1';
	
	$data = array(
		':username'	=>	$username,
		':username2'	=>	ucp_preg_replace('%[^\p{L}\p{N}]%u', '', $username),
	);

	// Check that the username (or a too similar username) is not already registered
	if (!is_null($exclude_id))
	{
		$where_cond .= ' AND id!=:id';
		$data[':id'] = $exclude_id;
	}

	$ps = $db->select('users', 'username', $data, $where_cond);
	if ($ps->rowCount())
	{
		$busy = $ps->fetchColumn();
		$errors[] = $lang_register['Username dupe 1'].' '.$busy.'. '.$lang_register['Username dupe 2'];
	}

	// Check username for any banned usernames
	foreach ($panther_bans as $cur_ban)
	{
		if ($cur_ban['username'] != '' && utf8_strtolower($username) == utf8_strtolower($cur_ban['username']))
		{
			$errors[] = $lang_prof_reg['Banned username'];
			break;
		}
	}
}

//
// Update "Users online"
//
function update_users_online()
{
	global $db, $panther_config, $panther_user;

	$cur_position = substr($_SERVER['REQUEST_URI'], 1);
	$server_base = dirname($_SERVER['PHP_SELF']);

	if ($server_base !== '/')
		$cur_position = substr($cur_position, strlen($server_base));

	$cur_position = ($cur_position == '') ? 'index.php' : $cur_position;

	$now = time();
	$online['users'] = $online['guests'] = array();

	// Fetch all online list entries that are older than "o_timeout_online"
	$ps = $db->run('SELECT o.user_id, o.ident, o.logged, o.idle, u.group_id FROM '.$db->prefix.'online AS o LEFT JOIN '.$db->prefix.'users AS u ON o.user_id=u.id');
	foreach ($ps as $cur_user)
	{
		if ($cur_user['logged'] < ($now - $panther_config['o_timeout_online']))
		{
			// If the entry is a guest, delete it
			if ($cur_user['user_id'] == '1')
			{
				$data = array(
					':ident'	=>	$cur_user['ident']
				);

				$db->delete('online', 'ident=:ident', $data);
			}
			else
			{
				// If the entry is older than "o_timeout_visit", update last_visit for the user in question, then delete him/her from the online list
				if ($cur_user['logged'] < ($now - $panther_config['o_timeout_visit']))
				{
					$update = array(
						'last_visit'	=>	$cur_user['logged'],
					);
					
					$data = array(
						':id'	=>	$cur_user['user_id'],
					);
					
					$db->update('users', $update, 'id=:id', $data);
					$db->delete('online', 'user_id=:id', $data);
				}
			}
		}
		else
		{
			if ($cur_user['user_id'] == 1)
				$online['guests'][] = array('ident' => $cur_user['ident'], 'group_id' => PANTHER_GUEST);
			else
				$online['users'][$cur_user['user_id']] = array('username' => $cur_user['ident'], 'group_id' => $cur_user['group_id'], 'id' => $cur_user['user_id']);
		}
	}
	
	if (!$panther_user['is_bot'])
	{
		$update = array(
			'currently'	=>	$cur_position,
		);
		
		$data = array();
		if ($panther_user['is_guest'])
		{
			$field = 'ident';
			$data[':ident'] = get_remote_address();
		}
		else
		{
			$field = 'user_id';
			$data[':ident'] = $panther_user['id'];
		}

		$db->update('online', $update, $field.'=:ident', $data);
	}
	return $online;
}

//
// Display the profile navigation menu
//
function generate_profile_menu($page = '')
{
	global $lang_profile, $panther_config, $panther_user, $id, $panther_url;
	
	$sections = array(
		array('page' => 'essentials', 'link' => panther_link($panther_url['profile_essentials'], array($id)), 'lang' => $lang_profile['Section essentials']),
		array('page' => 'personal', 'link' => panther_link($panther_url['profile_personal'], array($id)), 'lang' => $lang_profile['Section personal']),
		array('page' => 'messaging', 'link' => panther_link($panther_url['profile_messaging'], array($id)), 'lang' => $lang_profile['Section messaging']),
	);
	
	if ($panther_config['o_avatars'] == '1' || $panther_config['o_signatures'] == '1')
		$sections[] = array('page' => 'personality', 'link' => panther_link($panther_url['profile_personality'], array($id)), 'lang' => $lang_profile['Section personality']);
	
	$sections[] = array('page' => 'display', 'link' => panther_link($panther_url['profile_display'], array($id)), 'lang' => $lang_profile['Section display']);
	$sections[] = array('page' => 'privacy', 'link' => panther_link($panther_url['profile_privacy'], array($id)), 'lang' => $lang_profile['Section privacy']);
	$sections[] = array('page' => 'view', 'link' => panther_link($panther_url['profile_view'], array($id)), 'lang' => $lang_profile['Section view']);

	if ($panther_user['is_admin'] || ($panther_user['g_moderator'] == '1' && $panther_user['g_mod_ban_users'] == '1'))
		$sections[] = array('page' => 'admin', 'link' => panther_link($panther_url['profile_admin'], array($id)), 'lang' => $lang_profile['Section admin']);

	$tpl = load_template('profile_sidebar.tpl');
	echo $tpl->render(
		array(
			'lang_profile' => $lang_profile,
			'sections' => $sections,
			'page' => $page,
		)
	);
}

//
// Display PM menu
//

function generate_pm_menu($page = 2)	// Default to inbox
{
	global $panther_url, $lang_pm, $panther_user, $db;
	static $folders;

	$percent = ($panther_user['g_pm_limit'] != '0') ? round(ceil($panther_user['num_pms'] / $panther_user['g_pm_limit']*100), 0) : 0;
	$limit = ($panther_user['g_pm_limit'] == '0') ? '&infin;' : $panther_user['g_pm_limit'];
	
	$data = array(
		':uid'	=>	$panther_user['id'],
	);

	$folders = array();
	$ps = $db->select('folders', 'name, id', $data, 'user_id=:uid OR user_id=1', 'id, user_id ASC');
	foreach ($ps as $folder)
	{
		$data = array(
			':id'	=>	$folder['id'],
			':uid'	=>	$panther_user['id'],
		);

		$ps1 = $db->select('pms_data', 'COUNT(topic_id)', $data, 'user_id=:uid AND deleted=0 AND (folder_id=:id '.(($folder['id'] == 1) ? 'OR viewed=0)' : ')'));
		$amount = $ps1->fetchColumn();

		$folders[] = array(
			'id' => $folder['id'],
			'link' => panther_link($panther_url['box'], array($folder['id'])),
			'name' => $folder['name'],
			'amount' => $amount,
		);
	}
	
	$tpl = load_template('pm_sidebar.tpl');
	return $tpl->render(
		array(
			'lang_pm' => $lang_pm,
			'folders' => $folders,
			'percent' => $percent,
			'num_pms' => forum_number_format($panther_user['num_pms']),
			'limit' => forum_number_format($limit),
			'blocked_link' => panther_link($panther_url['pms_blocked']),
			'folders_link' => panther_link($panther_url['pms_folders']),
			'page' => $page,
		)
	);
}

//
// Outputs markup to display a user's avatar
//
function generate_avatar_markup($user_id, $user_email, $use_gravatar = 0, $size = array())
{
	global $panther_config;
	static $user_avatar_cache = array();
	$avatar_path = ($panther_config['o_avatars_dir'] != '') ? $panther_config['o_avatars_path'] : PANTHER_ROOT.$panther_config['o_avatars_path'];
	$avatar_dir = ($panther_config['o_avatars_dir'] != '') ? $panther_config['o_avatars_dir'] : get_base_url(true).'/'.$panther_config['o_avatars_path'];

	if (!isset($user_avatar_cache[$user_id]))
	{
		if ($use_gravatar == 1)
		{
			$params = (count($size) == 2) ? array($size[0], $size[1]) : array($panther_config['o_avatars_width'], $panther_config['o_avatars_height']);
			$user_avatar_cache[$user_id] = '<img src="https://www.gravatar.com/avatar.php?gravatar_id='.md5(strtolower($user_email)).'&amp;size='.$params[0].'" width="'.$params[0].'" height="'.$params[1].'" alt="" />';
		}
		else if ($panther_config['o_avatar_upload'] == '1')
		{
			$filetypes = array('jpg', 'gif', 'png');
			foreach ($filetypes as $cur_type)
			{
				$path = $avatar_path.$user_id.'.'.$cur_type;
				$url = $avatar_dir.$user_id.'.'.$cur_type;
				if (file_exists($path) && $img_size = getimagesize($path))
				{
					$size = (count($size) == 2 ? 'width="'.$size[0].'" height="'.$size[1].'"' : $img_size[3]);
					$user_avatar_cache[$user_id] = '<img src="'.$url.'?m='.filemtime($path).'" '.$size.' alt="" />';
					break;
				}
			}
		}

		// If there's no avatar set, we mustn't have one uploaded. Set the default!
		if (!isset($user_avatar_cache[$user_id]))
		{
			$path = $avatar_path.'1.'.$panther_config['o_avatar'];
			$url = $avatar_dir.'1.'.$panther_config['o_avatar'];
			$img_size = getimagesize($path);
			$size = (count($size) == 2 ? 'width="'.$size[0].'" height="'.$size[1].'"' : $img_size[3]);
			$user_avatar_cache[$user_id] = '<img src="'.$url.'?m='.filemtime($path).'" '.$size.' alt="" />';
		}
	}

	return $user_avatar_cache[$user_id];
}

//
// Generate browser's title
//
function generate_page_title($page_title, $p = null)
{
	global $lang_common;

	if (!is_array($page_title))
		$page_title = array($page_title);

	$page_title = array_reverse($page_title);

	if ($p > 1)
		$page_title[0] .= ' ('.sprintf($lang_common['Page'], forum_number_format($p)).')';

	$crumbs = implode($lang_common['Title separator'], $page_title);

	return $crumbs;
}

//
// Save array of tracked topics in cookie
//
function set_tracked_topics($tracked_topics)
{
	global $panther_config;

	$cookie_data = '';
	if (!empty($tracked_topics))
	{
		// Sort the arrays (latest read first)
		arsort($tracked_topics['topics'], SORT_NUMERIC);
		arsort($tracked_topics['forums'], SORT_NUMERIC);

		// Homebrew serialization (to avoid having to run unserialize() on cookie data)
		foreach ($tracked_topics['topics'] as $id => $timestamp)
			$cookie_data .= 't'.$id.'='.$timestamp.';';
		foreach ($tracked_topics['forums'] as $id => $timestamp)
			$cookie_data .= 'f'.$id.'='.$timestamp.';';

		// Enforce a byte size limit (4096 minus some space for the cookie name - defaults to 4048)
		if (strlen($cookie_data) > FORUM_MAX_COOKIE_SIZE)
		{
			$cookie_data = substr($cookie_data, 0, FORUM_MAX_COOKIE_SIZE);
			$cookie_data = substr($cookie_data, 0, strrpos($cookie_data, ';')).';';
		}
	}

	forum_setcookie($panther_config['o_cookie_name'].'_track', $cookie_data, time() + $panther_config['o_timeout_visit']);
	$_COOKIE[$panther_config['o_cookie_name'].'_track'] = $cookie_data; // Set it directly in $_COOKIE as well
}

//
// Extract array of tracked topics from cookie
//
function get_tracked_topics()
{
	global $panther_config;

	$cookie_data = isset($_COOKIE[$panther_config['o_cookie_name'].'_track']) ? $_COOKIE[$panther_config['o_cookie_name'].'_track'] : false;
	if (!$cookie_data)
		return array('topics' => array(), 'forums' => array());

	if (strlen($cookie_data) > FORUM_MAX_COOKIE_SIZE)
		return array('topics' => array(), 'forums' => array());

	// Unserialize data from cookie
	$tracked_topics = array('topics' => array(), 'forums' => array());
	$temp = explode(';', $cookie_data);
	foreach ($temp as $t)
	{
		$type = substr($t, 0, 1) == 'f' ? 'forums' : 'topics';
		$id = intval(substr($t, 1));
		$timestamp = intval(substr($t, strpos($t, '=') + 1));
		if ($id > 0 && $timestamp > 0)
			$tracked_topics[$type][$id] = $timestamp;
	}

	return $tracked_topics;
}

//
// Update posts, topics, last_post, last_post_id and last_poster for a forum
//
function update_forum($forum_id)
{
	global $db;
	$data = array(
		':id'	=>	$forum_id,
	);

	$ps = $db->select('topics', 'COUNT(id), SUM(num_replies)', $data, 'forum_id=:id AND approved=1 AND deleted=0');
	list($num_topics, $num_posts) = $ps->fetch(PDO::FETCH_NUM);

	$num_posts = $num_posts + $num_topics; // $num_posts is only the sum of all replies (we have to add the topic posts)
	$data = array(
		':id'	=>	$forum_id
	);

	$ps = $db->select('topics', 'last_post, last_post_id, last_poster, subject, id', $data, 'forum_id=:id AND approved=1 AND deleted=0 AND moved_to IS NULL ORDER BY last_post DESC LIMIT 1');
	if ($ps->rowCount()) // There are topics in the forum
	{
		list($last_post, $last_post_id, $last_poster, $last_topic, $last_topic_id) = $ps->fetch(PDO::FETCH_NUM);
		$update = array(
			'num_topics'	=>	$num_topics,
			'num_posts'		=>	$num_posts,
			'last_post'		=>	$last_post,
			'last_post_id'	=>	$last_post_id,
			'last_topic'	=>	$last_topic,
			'last_topic_id'	=>	$last_topic_id,
			'last_poster'	=>	$last_poster,
		);

		$data = array(
			':id'	=>	$forum_id,
		);

		$db->update('forums', $update, 'id=:id', $data);
	}
	else // There are no topics
	{
		$data = array(
			':num_topics'	=>	$num_topics,
			':num_posts'	=>	$num_posts,
			':id'		=>	$forum_id,
		);

		// Annoyingly PDO does not allow NULL values to be added in prepared statements. When added it becomes 'NULL', so we have to run the query manually instead.
		$db->run('UPDATE '.$db->prefix.'forums SET num_topics=:num_topics, num_posts=:num_posts, last_post=NULL, last_post_id=NULL, last_poster=NULL, last_topic=\'\', last_topic_id=0 WHERE id=:id', $data);
	}
}

//
// Deletes any avatars owned by the specified user ID
//
function delete_avatar($user_id)
{
	global $panther_config;

	$filetypes = array('jpg', 'gif', 'png');
	$avatar_path = ($panther_config['o_avatars_dir'] != '') ? $panther_config['o_avatars_path'] : PANTHER_ROOT.$panther_config['o_avatars_path'];

	// Delete user avatar
	foreach ($filetypes as $cur_type)
	{
		if (file_exists($avatar_path.$user_id.'.'.$cur_type))
			@unlink($avatar_path.$user_id.'.'.$cur_type);
	}
}

//
// Delete a topic and all of its posts
//
function delete_topic($topic_id)
{
	global $db, $panther_config;

	// Delete the topic and any redirect topics
	attach_delete_thread($topic_id);
	$data = array(
		':id'	=>	$topic_id,
	);

	$topic = array(
		':id'	=>	$topic_id,
		':moved_to'	=>	$topic_id,
	);

	$update = array(
		'deleted'	=>	1,
	);

	$post_ids = array();
	$db->update('topics', $update, 'id=:id OR moved_to=:moved_to', $topic);
	$db->delete('polls', 'topic_id=:id', $data);

	// Get all post IDs from this topic. 
	$ps = $db->select('posts', 'id', $data, 'topic_id=:id');
	foreach ($ps as $cur_post)
		$post_ids[] = $cur_post['id'];

	// Make sure we have a list of post IDs
	if (!empty($post_ids))
	{
		strip_search_index($post_ids);	// Should be an array
		$db->update('posts', $update, 'topic_id=:id', $data);
	}

	if ($panther_config['o_delete_full'] == '1')
		permanently_delete_topic($topic_id);
}

//
// Delete a single post
//
function delete_post($post_id, $topic_id)
{
	global $db, $panther_config;
	$topic_data = array(
		':id'	=>	$topic_id,
	);	

	$post_data = array(
		':id'	=>	$post_id,
	);

	$ps = $db->select('posts', 'id, poster, posted', $topic_data, 'topic_id=:id AND approved=1 AND deleted=0', 'id DESC LIMIT 2');
	list($last_id, ,) = $ps->fetch(PDO::FETCH_NUM);
	list($second_last_id, $second_poster, $second_posted) = $ps->fetch(PDO::FETCH_NUM);

	// Delete the post
	attach_delete_post($post_id);
	$update = array(
		'deleted'	=>	1,
	);

	$db->update('posts', $update, 'id=:id', $post_data);
	strip_search_index(array($post_id));

	// Count number of replies in the topic
	$ps = $db->select('posts', 'COUNT(id)', $topic_data, 'topic_id=:id AND approved=1 AND deleted=0');
	$num_replies = $ps->fetchColumn() - 1; // Decrement the deleted post

	// If the message we deleted is the most recent in the topic (at the end of the topic)
	if ($last_id == $post_id)
	{
		// If there is a $second_last_id there is more than 1 reply to the topic
		if (!empty($second_last_id))
		{
			$update = array(
				'last_post'	=>	$second_posted,
				'last_post_id'	=>	$second_last_id,
				'last_poster'	=>	$second_poster,
				'num_replies'	=>	$num_replies,
			);

			$data = array(
				':id'	=>	$topic_id,
			);

			$db->update('topics', $update, 'id=:id', $data);
		}
		else
		{
			$data = array(
				':id'	=>	$topic_id,
				':num_replies'	=>	$num_replies-1,
			);

			// We deleted the only reply, so now last_post/last_post_id/last_poster is posted/id/poster from the topic itself
			$db->run('UPDATE '.$db->prefix.'topics SET last_post=posted, last_post_id=id, last_poster=poster, num_replies=:num_replies WHERE id=:id', $data);
		}
	}
	else	// Otherwise we just decrement the reply counter
	{
		$update = array(
			'num_replies'	=>	$num_replies,
		);

		$db->update('topics', $update, 'id=:id', $topic_data);
	}

	if ($panther_config['o_delete_full'] == '1')
		permanently_delete_post($post_id);
}

//
// Permanently delete a single post
//
function permanently_delete_post($id)
{
	global $db;
	$data = array(
		':id'	=>	$id,
	);

	$db->delete('posts', 'id=:id AND deleted=1', $data);	// Since we've already stripped the search index, all we need to do is delete the row
}

//
// Permanently delete a topic
//
function permanently_delete_topic($id)
{
	global $db;
	$data = array(
		':id'	=>	$id,
		':moved_to'	=>	$id,
	);

	$db->delete('topics', '(id=:id OR moved_to=:moved_to) AND deleted=1', $data);
	unset($data[':moved_to']);
	$db->delete('posts', 'topic_id=? AND deleted=1', array_values($data));

	// Delete any subscriptions for this topic
	$db->delete('topic_subscriptions', 'topic_id=?', array_values($data));
}

//
// Delete every .php file in the forum's cache directory
//
function forum_clear_cache()
{
	$files = array_diff(scandir(FORUM_CACHE_DIR), array('.', '..'));
	foreach ($files as $file)
	{
		if (substr($file, -4) == '.php')
			@unlink(FORUM_CACHE_DIR.$file);
	}
}

//
// Replace censored words in $text
//
function censor_words($text)
{
	global $db;
	static $search_for, $replace_with;

	// If not already built in a previous call, build an array of censor words and their replacement text
	if (!isset($search_for))
	{
		if (file_exists(FORUM_CACHE_DIR.'cache_censoring.php'))
			include FORUM_CACHE_DIR.'cache_censoring.php';

		if (!defined('PANTHER_CENSOR_LOADED'))
		{
			if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
				require PANTHER_ROOT.'include/cache.php';

			generate_censoring_cache();
			require FORUM_CACHE_DIR.'cache_censoring.php';
		}
	}

	if (!empty($search_for))
		$text = substr(ucp_preg_replace($search_for, $replace_with, ' '.$text.' '), 1, -1);

	return $text;
}

//
// Determines the correct title for $user
// $user must contain the elements 'username', 'title', 'posts', 'g_id' and 'g_user_title'
//
function get_title($user)
{
	global $panther_bans, $lang_common, $panther_config;
	static $ban_list, $panther_ranks;

	// If not already built in a previous call, build an array of lowercase banned usernames
	if (empty($ban_list))
	{
		$ban_list = array();

		foreach ($panther_bans as $cur_ban)
			$ban_list[] = utf8_strtolower($cur_ban['username']);
	}

	// If not already loaded in a previous call, load the cached ranks
	if ($panther_config['o_ranks'] == '1' && !defined('PANTHER_RANKS_LOADED'))
	{
		if (file_exists(FORUM_CACHE_DIR.'cache_ranks.php'))
			include FORUM_CACHE_DIR.'cache_ranks.php';

		if (!defined('PANTHER_RANKS_LOADED'))
		{
			if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
				require PANTHER_ROOT.'include/cache.php';

			generate_ranks_cache();
			require FORUM_CACHE_DIR.'cache_ranks.php';
		}
	}
	// If the user has a custom title
	if ($user['title'] != '')
		$user_title = $user['title'];
	// If the user is banned
	else if (in_array(utf8_strtolower($user['username']), $ban_list))
		$user_title = $lang_common['Banned'];
	// If the user group has a default user title
	else if ($user['g_user_title'] != '')
		$user_title = $user['g_user_title'];
	// If the user is a guest
	else if ($user['g_id'] == PANTHER_GUEST)
		$user_title = $lang_common['Guest'];
	// If nothing else helps, we assign the default
	else
	{
		// Are there any ranks?
		if ($panther_config['o_ranks'] == '1' && !empty($panther_ranks))
		{
			foreach ($panther_ranks as $cur_rank)
			{
				if ($user['num_posts'] >= $cur_rank['min_posts'])
					$user_title = $cur_rank['rank'];
			}
		}

		// If the user didn't "reach" any rank (or if ranks are disabled), we assign the default
		if (!isset($user_title))
		  $user_title = $lang_common['Member'];
	}

	return $user_title;
}

//
// Generate a string with numbered links (for multipage scripts)
//
function paginate($num_pages, $cur_page, $link, $args = null)
{
	global $lang_common, $panther_config, $panther_url;

	$pages = array();
	$link_to_all = false;

	// If $cur_page == -1, we link to all pages (used in viewforum.php)
	if ($cur_page == -1)
	{
		$cur_page = 1;
		$link_to_all = true;
	}

	if ($num_pages > 1)
	{
		if ($cur_page > 1)
			$pages[] = array('item' => true, 'href' => str_replace('#', '', get_sublink($link, $panther_url['page'], ($cur_page - 1), $args)), 'current' => $lang_common['Previous']);
		
		if ($cur_page > 3)
		{
			$pages[] = array('item' => (empty($pages) ? true : false), 'href' => $link, 'current' => 1);
			if ($cur_page > 5)
				$pages[] = $lang_common['Spacer'];
		}

		// Don't ask me how the following works. It just does, OK? =)
		for ($current = ($cur_page == 5) ? $cur_page - 3 : $cur_page - 2, $stop = ($cur_page + 4 == $num_pages) ? $cur_page + 4 : $cur_page + 3; $current < $stop; ++$current)
		{
			if ($current < 1 || $current > $num_pages)
				continue;
			else if ($current != $cur_page || $link_to_all)
				$pages[] = array('item' => (empty($pages) ? true : false), 'href' => str_replace('#', '', get_sublink($link, $panther_url['page'], $current, $args)), 'current' => forum_number_format($current));
			else
				$pages[] = array('item' => (empty($pages) ? true : false), 'current' => forum_number_format($current));
		}

		if ($cur_page <= ($num_pages-3))
		{
			if ($cur_page != ($num_pages-3) && $cur_page != ($num_pages-4))
				$pages[] = $lang_common['Spacer'];

			$pages[] = array('item' => (empty($pages) ? true : false), 'href' => get_sublink($link, $panther_url['page'], $num_pages, $args), 'current' => forum_number_format($num_pages));
		}

		// Add a next page link
		if ($num_pages > 1 && !$link_to_all && $cur_page < $num_pages)
			$pages[] = array('item' => (empty($pages) ? true : false), 'rel' => 'next', 'href' => get_sublink($link, $panther_url['page'], ($cur_page + 1), $args), 'current' => $lang_common['Next']);
	}

	$tpl = load_template('pagination.tpl');
	return $tpl->render(
		array(
			'num_pages' => $num_pages,
			'cur_page' => $cur_page,
			'pages' => $pages,
			'link' => $link,
		)
	);
}

//
// Display a message
//
function message($message, $no_back_link = false, $http_status = null)
{
	global $db, $lang_common, $panther_config, $panther_start, $tpl_main, $panther_user, $panther_url;

	// Did we receive a custom header?
	if (!is_null($http_status))
		header('HTTP/1.1 '.$http_status);

	if (!defined('PANTHER_HEADER'))
	{
		$page_title = array($panther_config['o_board_title'], $lang_common['Info']);
		define('PANTHER_ACTIVE_PAGE', 'index');
		require PANTHER_ROOT.'header.php';
	}

	$tpl = load_template('message.tpl');
	echo $tpl->render(
		array(
			'lang_common' => $lang_common,
			'message' => $message,
			'no_back_link' => $no_back_link,
		)
	);

	require PANTHER_ROOT.'footer.php';
}

//
// Format a time string according to $time_format and time zones
//
function format_time($timestamp, $date_only = false, $date_format = null, $time_format = null, $time_only = false, $no_text = false)
{
	global $lang_common, $panther_user, $forum_date_formats, $forum_time_formats;

	if ($timestamp == '')
		return $lang_common['Never'];

	$diff = ($panther_user['timezone'] + $panther_user['dst']) * 3600;
	$timestamp += $diff;
	$now = time();

	if(is_null($date_format))
		$date_format = $forum_date_formats[$panther_user['date_format']];

	if(is_null($time_format))
		$time_format = $forum_time_formats[$panther_user['time_format']];

	$date = gmdate($date_format, $timestamp);
	$today = gmdate($date_format, $now+$diff);
	$yesterday = gmdate($date_format, $now+$diff-86400);

	if (!$no_text)
	{
		if ($date == $today)
			$date = $lang_common['Today'];
		else if ($date == $yesterday)
			$date = $lang_common['Yesterday'];
	}

	if ($date_only)
		return $date;
	else if ($time_only)
		return gmdate($time_format, $timestamp);
	else
		return $date.' '.gmdate($time_format, $timestamp);
}


//
// A wrapper for PHP's number_format function
//
function forum_number_format($number, $decimals = 0)
{
	global $lang_common;

	return is_numeric($number) ? number_format($number, $decimals, $lang_common['lang_decimal_point'], $lang_common['lang_thousands_sep']) : $number;
}

//
// Generate a random key of length $len
//
function random_key($len, $readable = false, $hash = false)
{
	if (!function_exists('secure_random_bytes'))
		include PANTHER_ROOT.'include/srand.php';

	$key = secure_random_bytes($len);

	if ($hash)
		return substr(bin2hex($key), 0, $len);
	else if ($readable)
	{
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

		$result = '';
		for ($i = 0; $i < $len; ++$i)
			$result .= substr($chars, (ord($key[$i]) % strlen($chars)), 1);

		return $result;
	}

	return $key;
}

//
// Make sure that user is using a valid token
// 
function confirm_referrer($script, $use_ip = true)
{
	global $lang_common, $panther_user;

	// Yeah, pretty complex ternary =)
	$sent_hash = ((isset($_POST['csrf_token'])) ? panther_trim($_POST['csrf_token']) : (isset($_GET['csrf_token']) ? panther_trim($_GET['csrf_token']) : ''));

	if (!panther_hash_equals(generate_csrf_token($script, $use_ip), $sent_hash))
		message($lang_common['Bad referrer']);
}

//
// Generate a csrf token
//
function generate_csrf_token($script = 'nothing', $use_ip = true)
{
	global $panther_user;

	$script = ($script != 'nothing') ? $script : panther_trim(basename($_SERVER['SCRIPT_NAME']));
	return panther_hash($panther_user['id'].$script.$panther_user['salt'].(($use_ip ? get_remote_address() : '')).$panther_user['login_key']);
}

//
// Validate the given redirect URL, use the fallback otherwise
//
function validate_redirect($redirect_url, $fallback_url)
{
	$referrer = parse_url(strtolower($redirect_url));

	// Make sure the host component exists
	if (!isset($referrer['host']))
		$referrer['host'] = '';

	// Remove www subdomain if it exists
	if (strpos($referrer['host'], 'www.') === 0)
		$referrer['host'] = substr($referrer['host'], 4);

	// Make sure the path component exists
	if (!isset($referrer['path']))
		$referrer['path'] = '';

	$valid = parse_url(strtolower(get_base_url()));

	// Remove www subdomain if it exists
	if (strpos($valid['host'], 'www.') === 0)
		$valid['host'] = substr($valid['host'], 4);

	// Make sure the path component exists
	if (!isset($valid['path']))
		$valid['path'] = '';

	if ($referrer['host'] == $valid['host'] && preg_match('%^'.preg_quote($valid['path'], '%').'/(.*?)\.php%i', $referrer['path']))
		return $redirect_url;
	else
		return $fallback_url;
}

//
// Generate a random password of length $len
// Compatibility wrapper for random_key
//
function random_pass($len)
{
	return random_key($len, true);
}

//
// Compute a hash of $str
//
function panther_hash($str)
{
	return hash('sha512', $str);
}

//
// Try to determine the correct remote IP-address
//
function get_remote_address()
{
	$remote_addr = $_SERVER['REMOTE_ADDR'];

	// If we are behind a reverse proxy try to find the real users IP
	if (defined('FORUM_BEHIND_REVERSE_PROXY'))
	{
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			// The general format of the field is:
			// X-Forwarded-For: client1, proxy1, proxy2
			// where the value is a comma+space separated list of IP addresses, the left-most being the farthest downstream client,
			// and each successive proxy that passed the request adding the IP address where it received the request from.
			$forwarded_for = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$forwarded_for = trim($forwarded_for[0]);

			if (@preg_match('%^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$%', $forwarded_for) || @preg_match('%^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$%', $forwarded_for))
				$remote_addr = $forwarded_for;
		}
	}

	return $remote_addr;
}

//
// Calls htmlspecialchars with a few options already set
// As of 1.1.0, this has been deprecated and will be removed soon. Use Twig instead.
//
function panther_htmlspecialchars($str)
{
	return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

//
// Calls htmlspecialchars_decode with a few options already set
//
function panther_htmlspecialchars_decode($str)
{
	if (function_exists('htmlspecialchars_decode'))
		return htmlspecialchars_decode($str, ENT_QUOTES);

	static $translations;
	if (!isset($translations))
	{
		$translations = get_html_translation_table(HTML_SPECIALCHARS, ENT_QUOTES);
		$translations['&#039;'] = '\''; // get_html_translation_table doesn't include &#039; which is what htmlspecialchars translates ' to, but apparently that is okay?! http://bugs.php.net/bug.php?id=25927
		$translations = array_flip($translations);
	}

	return strtr($str, $translations);
}

//
// A wrapper for utf8_strlen for compatibility
//
function panther_strlen($str)
{
	return utf8_strlen($str);
}

//
// Convert \r\n and \r to \n
//
function panther_linebreaks($str)
{
	return str_replace(array("\r\n", "\r"), "\n", $str);
}

//
// A wrapper for utf8_trim for compatibility
//
function panther_trim($str, $charlist = false)
{
	return is_string($str) ? utf8_trim($str, $charlist) : '';
}

//
// Checks if a string is in all uppercase
//
function is_all_uppercase($string)
{
	return utf8_strtoupper($string) == $string && utf8_strtolower($string) != $string;
}

//
// Inserts $element into $input at $offset
// $offset can be either a numerical offset to insert at (eg: 0 inserts at the beginning of the array)
// or a string, which is the key that the new element should be inserted before
// $key is optional: it's used when inserting a new key/value pair into an associative array
//
function array_insert(&$input, $offset, $element, $key = null)
{
	if (is_null($key))
		$key = $offset;

	// Determine the proper offset if we're using a string
	if (!is_int($offset))
		$offset = array_search($offset, array_keys($input), true);

	// Out of bounds checks
	if ($offset > count($input))
		$offset = count($input);
	else if ($offset < 0)
		$offset = 0;

	$input = array_merge(array_slice($input, 0, $offset), array($key => $element), array_slice($input, $offset));
}

//
// Return a template object from Twig
//
function load_template($tpl_file)
{
	global $panther_user, $panther_config, $tpl_manager;
	
	$style_root = (($panther_config['o_style_path'] != 'style') ? $panther_config['o_style_path'] : PANTHER_ROOT.$panther_config['o_style_path']).'/'.$panther_user['style'].'/templates/';
	if (file_exists($style_root.$tpl_file))
		$tpl_file = $tpl_manager->loadTemplate('@style/'.$tpl_file);
	else
		$tpl_file = $tpl_manager->loadTemplate('@core/'.$tpl_file);

	return $tpl_file;
}

//
// Display a message when board is in maintenance mode
//
function maintenance_message()
{
	global $db, $panther_config, $lang_common, $panther_user;

	// Send no-cache headers
	header('Expires: Thu, 21 Jul 1977 07:30:00 GMT'); // When yours truly first set eyes on this world! :)
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache'); // For HTTP/1.0 compatibility

	// Send the Content-type header in case the web server is setup to send something else
	header('Content-type: text/html; charset=utf-8');

	// Deal with newlines, tabs and multiple spaces
	$pattern = array("\t", '  ', '  ');
	$replace = array('&#160; &#160; ', '&#160; ', ' &#160;');
	$message = str_replace($pattern, $replace, $panther_config['o_maintenance_message']);

	$tpl = load_template('maintenance.tpl');
	echo $tpl->render(
		array(
			'lang_common' => $lang_common,
			'message' => $message,
			'page_title' => generate_page_title(array($panther_config['o_board_title'], $lang_common['Maintenance'])),
			'style' => (($panther_config['o_style_dir']) != '' ? $panther_config['o_style_dir'] : get_base_url().'/style/').$panther_user['style'],
			'panther_config' => $panther_config,
		)
	);

	// End the transaction
	$db->end_transaction();

	exit;
}

//
// Display $message and redirect user to $destination_url
//
function redirect($destination_url, $message)
{
	global $db, $panther_config, $lang_common, $panther_user;

	// Prefix with base_url (unless there's already a valid URI)
	if (strpos($destination_url, 'http://') !== 0 && strpos($destination_url, 'https://') !== 0 && strpos($destination_url, '/') !== 0)
		$destination_url = get_base_url(true).'/'.$destination_url;

	// Do a little spring cleaning
	$destination_url = preg_replace('%([\r\n])|(\%0[ad])|(;\s*data\s*:)%i', '', $destination_url);

	// If the delay is 0 seconds, we might as well skip the redirect all together
	if ($panther_config['o_redirect_delay'] == '0')
	{
		$db->end_transaction();

		header('Location: '.str_replace('&amp;', '&', $destination_url));
		exit;
	}

	// Send no-cache headers
	header('Expires: Thu, 21 Jul 1977 07:30:00 GMT'); // When yours truly first set eyes on this world! :)
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache'); // For HTTP/1.0 compatibility

	// Send the Content-type header in case the web server is setup to send something else
	header('Content-type: text/html; charset=utf-8');

	$tpl = load_template('redirect.tpl');
	echo $tpl->render(
		array(
			'lang_common' => $lang_common,
			'destination_url' => $destination_url,
			'message' => $message,
			'queries' => ($panther_config['o_show_queries'] == '1') ? display_saved_queries() : '',
			'panther_config' => $panther_config,
			'page_title' => generate_page_title(array($panther_config['o_board_title'], $lang_common['Redirecting'])),
			'css_url' => (($panther_config['o_style_dir']) != '' ? $panther_config['o_style_dir'] : get_base_url().'/style/').$panther_user['style'],
		)
	);
	
	$db->end_transaction();
	exit;
}

//
// Handle PHP errors
//

function error_handler($errno = 0, $errstr = 'Error', $errfile = 'unknown', $errline = 0)
{
	global $panther_config, $lang_common, $panther_user, $panther_url;

	if (!is_int($errno)) // Make sure set_exception_handler doesn't intefere
	{
		$errstr = $errno;
		$errno = 1;
	}

	// Needed to ensure it doesn't appear after completion on every page
	if ($errno < 1)
		exit;

	$error = error_get_last();
	
	// If we want to supress errors
	if (error_reporting() == 0)
		return;

	// Check if we're dealing with a fatal error (annoyingly these have to be handled seperately with register_shutdown_function)
	if ($error['type'] == E_ERROR)
	{
		$errno = $error['type'];
		$errstr = $error['message'];
		$errfile = $error['file'];
		$errline = $error['line'];
	}

	// Empty all output buffers and stop buffering (only if we've started)
	if (ob_get_level() > 0 && ob_get_length())
		@ob_clean();

	// Set some default settings if the script failed before $panther_config could be populated
	if (empty($panther_config))
	{
		// Make an educated guess regarding base_url
		$base_url  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';	// protocol
		$base_url .= preg_replace('%:(80|443)$%', '', $_SERVER['HTTP_HOST']);							// host[:port]
		$base_url .= str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));							// path

		if (substr($base_url, -1) == '/')
			$base_url = substr($base_url, 0, -1);

		$panther_config = array(
			'o_board_title'	=> 'Panther',
			'o_gzip'		=> '0',
			'o_debug_mode'	=>	'1',
			'o_webmaster_email'	=>	$_SERVER['SERVER_ADMIN'],
			'o_default_style'	=>	'Pantherone',
			'o_base_url'	=>	$base_url,
			'o_style_dir' => get_base_url().'/style/',
			'o_style_path' => 'style',
		);
	}

	// Don't send HTML
	if (defined('PANTHER_AJAX_REQUEST'))
		exit((($panther_config['o_debug_mode'] == '1') ? 'Errno ['.$errno.'] '.$errstr.' in '.$errfile.' on line '.$errline : 'A server error was encountered.'));

	if (empty($panther_user))
	{
	    $panther_user = array(
	        'style' => $panther_config['o_default_style'],
	        'is_admin' => false,
	        'is_admmod' => false,
	        'is_guest' => true,
	    );
	}

	// Set some default translations if the script failed before $lang_common could be populated
	$style_path = (($panther_config['o_style_path'] != 'style') ? $panther_config['o_style_path'] : PANTHER_ROOT.$panther_config['o_style_path']).'/';
	if (empty($lang_common))
	{
		$lang_common = array(
			'Title separator'	=> ' | ',
			'Page' => 'Page %s'
		);
	}

	// "Restart" output buffering if we are using ob_gzhandler (since the gzip header is already sent)
	if ($panther_config['o_gzip'] && extension_loaded('zlib') && !ob_get_length())
		ob_start('ob_gzhandler');

	// Send no-cache headers
	header('Expires: Thu, 21 Jul 1977 07:30:00 GMT'); // When yours truly first set eyes on this world! :)
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache'); // For HTTP/1.0 compatibility

	// Send the Content-type header in case the web server is setup to send something else
	header('Content-type: text/html; charset=utf-8');

	// Send headers telling people we're down
	header('HTTP/1.1 503 Service Temporarily Unavailable');
	header('Status: 503 Service Temporarily Unavailable');

	$tpl = load_template('server_error.tpl');
	echo $tpl->render(
		array(
			'panther_config' => $panther_config,
			'error_style' => (($panther_config['o_style_dir']) != '' ? $panther_config['o_style_dir'] : get_base_url().'/style/').(file_exists($style_path.$panther_user['style'].'/error.css') ? $panther_user['style'] : 'imports'),
			'page_title' => generate_page_title(array($panther_config['o_board_title'], 'Server Error')),
			'errrno' => $errno,
			'errstr' => $errstr,
			'errfile' => $errfile,
			'errline' => $errline,
			'index' => panther_link($panther_url['index']),
		)
	);

	exit;
}

//
// Display a database error message
//
function error($error, $sql = '', $parameters = array())
{
	global $panther_config, $lang_common, $panther_user, $panther_url;

	if (defined('PANTHER_AJAX_REQUEST'))
		exit('A database error was encountered.');

	// Empty all output buffers and stop buffering (only if we've started)
	if (ob_get_level() > 0 && ob_get_length())
		@ob_clean();
	
	// Set some default settings if the script failed before $panther_config could be populated
	if (!empty($panther_config))
	{
		// Make an educated guess regarding base_url
		$base_url  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';	// protocol
		$base_url .= preg_replace('%:(80|443)$%', '', $_SERVER['HTTP_HOST']);							// host[:port]
		$base_url .= str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));							// path

		if (substr($base_url, -1) == '/')
			$base_url = substr($base_url, 0, -1);

		$panther_config = array(
			'o_board_title'	=> 'Panther',
			'o_gzip'		=> '0',
			'o_debug_mode'	=>	'1',
			'o_webmaster_email'	=>	$_SERVER['SERVER_ADMIN'],
			'o_default_style'	=>	'Oxygen',
			'o_base_url'	=>	$base_url,
			'o_style_dir' => 'style/',
			'o_style_path' => '/style/',
		);
	}

	// "Restart" output buffering if we are using ob_gzhandler (since the gzip header is already sent)
	if ($panther_config['o_gzip'] && extension_loaded('zlib') && !ob_get_length())
		ob_start('ob_gzhandler');
	
	if (empty($panther_user))
	{
	    $panther_user = array(
	        'style' => $panther_config['o_default_style'],
	        'is_admin' => false,
	        'is_admmod' => false,
	        'is_guest' => true,
	    );
	}

	// Set some default translations if the script failed before $lang_common could be populated
	if (empty($lang_common))
	{
		$lang_common = array(
			'Title separator'	=> ' | ',
			'Page' => 'Page %s'
		);
	}

	$style_path = ($panther_config['o_style_dir'] != '') ? $panther_config['o_style_dir'] : PANTHER_ROOT.$panther_config['o_style_dir'].'style/';

	// Send no-cache headers
	header('Expires: Thu, 21 Jul 1977 07:30:00 GMT'); // When yours truly first set eyes on this world! :)
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache'); // For HTTP/1.0 compatibility

	// Send the Content-type header in case the web server is setup to send something else
	header('Content-type: text/html; charset=utf-8');

	if (file_exists($style_path.$panther_user['style'].'/error.css'))
		$error_style = (($panther_config['o_style_dir']) != '' ? $panther_config['o_style_dir'] : get_base_url().'/style/').$panther_user['style'];
	else
		$error_style = (($panther_config['o_style_dir']) != '' ? $panther_config['o_style_dir'] : get_base_url().'/style/').'imports/';

	// Send headers telling people we're down
	header('HTTP/1.1 503 Service Temporarily Unavailable');
	header('Status: 503 Service Temporarily Unavailable');
	
	// Annoyingly, print_r(), var_dump(), and our custom function dump() all print HTML out directtly. Time to use ob_get_contents ...
	ob_start();
	dump($parameters);
	$debug = trim(ob_get_contents());
	ob_end_clean();

	$tpl = load_template('db_error.tpl');
	echo $tpl->render(
		array(
			'panther_config' => $panther_config,
			'error_style' => $error_style,
			'page_title' => generate_page_title(array($panther_config['o_board_title'], 'Database Error')),
			'error' => $error,
			'sql' => $sql,
			'debug' => $debug,
			'index' => panther_link($panther_url['index']),
		)
	);

	exit;
}

//
// Removes any "bad" characters (characters which mess with the display of a page, are invisible, etc) from user input
//
function forum_remove_bad_characters()
{
	$_GET = remove_bad_characters($_GET);
	$_POST = remove_bad_characters($_POST);
	$_COOKIE = remove_bad_characters($_COOKIE);
	$_REQUEST = remove_bad_characters($_REQUEST);
}

//
// Removes any "bad" characters (characters which mess with the display of a page, are invisible, etc) from the given string
// See: http://kb.mozillazine.org/Network.IDN.blacklist_chars
//
function remove_bad_characters($array)
{
	static $bad_utf8_chars;

	if (!isset($bad_utf8_chars))
	{
		$bad_utf8_chars = array(
			"\xcc\xb7"		=> '',		// COMBINING SHORT SOLIDUS OVERLAY		0337	*
			"\xcc\xb8"		=> '',		// COMBINING LONG SOLIDUS OVERLAY		0338	*
			"\xe1\x85\x9F"	=> '',		// HANGUL CHOSEONG FILLER				115F	*
			"\xe1\x85\xA0"	=> '',		// HANGUL JUNGSEONG FILLER				1160	*
			"\xe2\x80\x8b"	=> '',		// ZERO WIDTH SPACE						200B	*
			"\xe2\x80\x8c"	=> '',		// ZERO WIDTH NON-JOINER				200C
			"\xe2\x80\x8d"	=> '',		// ZERO WIDTH JOINER					200D
			"\xe2\x80\x8e"	=> '',		// LEFT-TO-RIGHT MARK					200E
			"\xe2\x80\x8f"	=> '',		// RIGHT-TO-LEFT MARK					200F
			"\xe2\x80\xaa"	=> '',		// LEFT-TO-RIGHT EMBEDDING				202A
			"\xe2\x80\xab"	=> '',		// RIGHT-TO-LEFT EMBEDDING				202B
			"\xe2\x80\xac"	=> '', 		// POP DIRECTIONAL FORMATTING			202C
			"\xe2\x80\xad"	=> '',		// LEFT-TO-RIGHT OVERRIDE				202D
			"\xe2\x80\xae"	=> '',		// RIGHT-TO-LEFT OVERRIDE				202E
			"\xe2\x80\xaf"	=> '',		// NARROW NO-BREAK SPACE				202F	*
			"\xe2\x81\x9f"	=> '',		// MEDIUM MATHEMATICAL SPACE			205F	*
			"\xe2\x81\xa0"	=> '',		// WORD JOINER							2060
			"\xe3\x85\xa4"	=> '',		// HANGUL FILLER						3164	*
			"\xef\xbb\xbf"	=> '',		// ZERO WIDTH NO-BREAK SPACE			FEFF
			"\xef\xbe\xa0"	=> '',		// HALFWIDTH HANGUL FILLER				FFA0	*
			"\xef\xbf\xb9"	=> '',		// INTERLINEAR ANNOTATION ANCHOR		FFF9	*
			"\xef\xbf\xba"	=> '',		// INTERLINEAR ANNOTATION SEPARATOR		FFFA	*
			"\xef\xbf\xbb"	=> '',		// INTERLINEAR ANNOTATION TERMINATOR	FFFB	*
			"\xef\xbf\xbc"	=> '',		// OBJECT REPLACEMENT CHARACTER			FFFC	*
			"\xef\xbf\xbd"	=> '',		// REPLACEMENT CHARACTER				FFFD	*
			"\xe2\x80\x80"	=> ' ',		// EN QUAD								2000	*
			"\xe2\x80\x81"	=> ' ',		// EM QUAD								2001	*
			"\xe2\x80\x82"	=> ' ',		// EN SPACE								2002	*
			"\xe2\x80\x83"	=> ' ',		// EM SPACE								2003	*
			"\xe2\x80\x84"	=> ' ',		// THREE-PER-EM SPACE					2004	*
			"\xe2\x80\x85"	=> ' ',		// FOUR-PER-EM SPACE					2005	*
			"\xe2\x80\x86"	=> ' ',		// SIX-PER-EM SPACE						2006	*
			"\xe2\x80\x87"	=> ' ',		// FIGURE SPACE							2007	*
			"\xe2\x80\x88"	=> ' ',		// PANTHERCTUATION SPACE					2008	*
			"\xe2\x80\x89"	=> ' ',		// THIN SPACE							2009	*
			"\xe2\x80\x8a"	=> ' ',		// HAIR SPACE							200A	*
			"\xE3\x80\x80"	=> ' ',		// IDEOGRAPHIC SPACE					3000	*
		);
	}

	if (is_array($array))
		return array_map('remove_bad_characters', $array);

	// Strip out any invalid characters
	$array = utf8_bad_strip($array);

	// Remove control characters
	$array = preg_replace('%[\x00-\x08\x0b-\x0c\x0e-\x1f]%', '', $array);

	// Replace some "bad" characters
	$array = str_replace(array_keys($bad_utf8_chars), array_values($bad_utf8_chars), $array);

	return $array;
}

//
// Converts the file size in bytes to a human readable file size
//
function file_size($size)
{
	global $lang_common;

	$units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB');

	for ($i = 0; $size > 1024; $i++)
		$size /= 1024;

	return sprintf($lang_common['Size unit '.$units[$i]], round($size, 2));
}

//
// Fetch a list of available styles
//
function forum_list_styles()
{
	global $panther_config;
	$styles = array();
	$style = ($panther_config['o_style_path'] != 'style') ? $panther_config['o_style_path'] : PANTHER_ROOT.$panther_config['o_style_path'];

	$styles = array();
	$files = array_diff(scandir($style), array('.', '..'));
	foreach ($files as $style)
	{
		if (substr($style, -4) == '.css')
		$styles[] = substr($style, 0, -4);
	}

	natcasesort($styles);
	return $styles;
}

//
// Fetch a list of available language packs
//
function forum_list_langs()
{
	$languages = array();
	$files = array_diff(scandir(PANTHER_ROOT.'lang'), array('.', '..'));
	foreach ($files as $language)
	{
		if (is_dir(PANTHER_ROOT.'lang/'.$language) && file_exists(PANTHER_ROOT.'lang/'.$language.'/common.php'))
		$languages[] = $language;
	}

	natcasesort($languages);
	return $languages;
}

//
// Get all URL schemes
//
function get_url_schemes()
{
	$schemes = array();
	$files = array_diff(scandir(PANTHER_ROOT.'include/url'), array('.', '..'));
	foreach ($files as $scheme)
	{
		if (preg_match('/^[a-z0-9-_]+\.(php)$/i', $scheme))
			$schemes[] = $scheme;
	}

	return $schemes;
}

//
// For forum passwords, show this...
//

function show_forum_login_box($id)
{
	global $lang_common, $panther_config, $panther_start, $tpl_main, $panther_user, $db, $panther_url;
	$required_fields = array('req_password' => $lang_common['Password']);
	$focus_element = array('request_pass', 'req_password');
	
	$data = array(
		':id'	=>	$id,
	);
	
	$ps = $db->select('forums', 'forum_name', $data, 'id=:id');
	$forum_name = url_friendly($ps->fetchColumn());
	
	$redirect_url = validate_redirect(get_current_url(), null);

	if (!isset($redirect_url))
		$redirect_url = panther_link($panther_url['forum'], array($id, $forum_name));
	else if (preg_match('%viewtopic\.php\?pid=(\d+)$%', $redirect_url, $matches))
		$redirect_url .= '#p'.$matches[1];

	$page_title = array($panther_config['o_board_title'], $lang_common['Info']);
	define('PANTHER_ACTIVE_PAGE', 'index');
	require PANTHER_ROOT.'header.php';

	$tpl = load_template('forum_password.tpl');
	echo $tpl->render(
		array(
			'lang_common' => $lang_common,
			'form_action' => panther_link($panther_url['forum'], array($id, $forum_name)),
			'csrf_token' => generate_csrf_token('viewforum.php'),
			'redirect_url' => $redirect_url,
		)
	);

	require PANTHER_ROOT.'footer.php';
}

//
// Check if given forum password is indeed valid
//
function validate_login_attempt($id)
{
	global $lang_common, $db, $panther_url;
	confirm_referrer('viewforum.php');

	$password = isset($_POST['req_password']) ? panther_trim($_POST['req_password']) : '';
	$redirect_url = validate_redirect($_POST['redirect_url'], panther_link($panther_url['index']));	// If we've tampered, or maybe something just went wrong, send them back to the board index
	$data = array(
		':id'	=>	$id,
	);

	$ps = $db->select('forums', 'password, salt', $data, 'id=:id');
	if (!$ps->rowCount())
		message($lang_common['Bad request']);
	else
		$cur_forum = $ps->fetch();

	if (panther_hash($password.panther_hash($cur_forum['salt'])) == $cur_forum['password'])
	{
		set_forum_login_cookie($id, $cur_forum['password']);
		header('Location: '.$redirect_url);
		exit;
	}
	else
		message($lang_common['incorrect password']);
}

function set_forum_login_cookie($id, $forum_password)
{
	global $panther_config;

	$cookie_data = isset($_COOKIE[$panther_config['o_cookie_name'].'_forums']) ? $_COOKIE[$panther_config['o_cookie_name'].'_forums'] : '';
	if (!$cookie_data || strlen($cookie_data) > FORUM_MAX_COOKIE_SIZE)
		$cookie_data = '';

	$cookie_data = unserialize($cookie_data);
	$salt = random_key(64, true);
	$cookie_hash = panther_hash($forum_password.panther_hash($salt));
	$cookie_data[$id] = array('hash' => $cookie_hash, 'salt' => $salt);

	forum_setcookie($panther_config['o_cookie_name'].'_forums', serialize($cookie_data), time() + $panther_config['o_timeout_visit']);
	$_COOKIE[$panther_config['o_cookie_name'].'_forums'] = serialize($cookie_data);
}

function check_forum_login_cookie($id, $forum_password, $return = false)
{
	global $panther_config;

	$cookie_data = isset($_COOKIE[$panther_config['o_cookie_name'].'_forums']) ? $_COOKIE[$panther_config['o_cookie_name'].'_forums'] : '';
	if (!$cookie_data || strlen($cookie_data) > FORUM_MAX_COOKIE_SIZE)
		$cookie_data = '';

	// If it's empty, define as a blank array to avoid 'must be a boolean' error
	$cookie_data = ($cookie_data !== '') ? unserialize($cookie_data) : array();
	if (!array_key_exists($id, $cookie_data))
	{
		if (!$return)
			show_forum_login_box($id);
		else
			return false;
	}
	else
	{
		if ($cookie_data[$id]['hash'] !== panther_hash($forum_password.panther_hash($cookie_data[$id]['salt'])))
		{
			if (!$return)
				show_forum_login_box($id);
			else
				return false;
		}
		else
			return true;
	}
}

//
// Generate a cache ID based on the last modification time for all stopwords files
//
function generate_stopwords_cache_id()
{
	$files = glob(PANTHER_ROOT.'lang/*/stop_words.txt');
	if ($files === false)
		return 'cache_id_error';

	$hash = array();

	foreach ($files as $file)
	{
		$hash[] = $file;
		$hash[] = filemtime($file);
	}

	return panther_hash(implode('|', $hash));
}

//
// Split text into chunks ($inside contains all text inside $start and $end, and $outside contains all text outside)
//
function split_text($text, $start, $end, $retab = true)
{
	global $panther_config;

	$result = array(0 => array(), 1 => array()); // 0 = inside, 1 = outside

	// split the text into parts
	$parts = preg_split('%'.preg_quote($start, '%').'(.*)'.preg_quote($end, '%').'%Us', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
	$num_parts = count($parts);

	// preg_split results in outside parts having even indices, inside parts having odd
	for ($i = 0;$i < $num_parts;$i++)
		$result[1 - ($i % 2)][] = $parts[$i];

	if ($panther_config['o_indent_num_spaces'] != 8 && $retab)
	{
		$spaces = str_repeat(' ', $panther_config['o_indent_num_spaces']);
		$result[1] = str_replace("\t", $spaces, $result[1]);
	}

	return $result;
}

function extract_blocks($text, $start, $end, $retab = true)
{
	global $panther_config;

	$code = array();
	$start_len = strlen($start);
	$end_len = strlen($end);
	$regex = '%(?:'.preg_quote($start, '%').'|'.preg_quote($end, '%').')%';
	$matches = array();

	if (preg_match_all($regex, $text, $matches))
	{
		$counter = $offset = 0;
		$start_pos = $end_pos = false;

		foreach ($matches[0] as $match)
		{
			if ($match == $start)
			{
				if ($counter == 0)
					$start_pos = strpos($text, $start);
				$counter++;
			}
			elseif ($match == $end)
			{
				$counter--;
				if ($counter == 0)
					$end_pos = strpos($text, $end, $offset + 1);
				$offset = strpos($text, $end, $offset + 1);
			}

			if ($start_pos !== false && $end_pos !== false)
			{
				$code[] = substr($text, $start_pos + $start_len,
					$end_pos - $start_pos - $start_len);
				$text = substr_replace($text, "\1", $start_pos,
					$end_pos - $start_pos + $end_len);
				$start_pos = $end_pos = false;
				$offset = 0;
			}
		}
	}

	if ($panther_config['o_indent_num_spaces'] != 8 && $retab)
	{
		$spaces = str_repeat(' ', $panther_config['o_indent_num_spaces']);
		$text = str_replace("\t", $spaces, $text);
	}

	return array($code, $text);
}

function url_valid($url)
{
	if (strpos($url, 'www.') === 0) $url = 'http://'. $url;
	if (strpos($url, 'ftp.') === 0) $url = 'ftp://'. $url;
	if (!preg_match('/# Valid absolute URI having a non-empty, valid DNS host.
		^
		(?P<scheme>[A-Za-z][A-Za-z0-9+\-.]*):\/\/
		(?P<authority>
		  (?:(?P<userinfo>(?:[A-Za-z0-9\-._~!$&\'()*+,;=:]|%[0-9A-Fa-f]{2})*)@)?
		  (?P<host>
			(?P<IP_literal>
			  \[
			  (?:
				(?P<IPV6address>
				  (?:												 (?:[0-9A-Fa-f]{1,4}:){6}
				  |												   ::(?:[0-9A-Fa-f]{1,4}:){5}
				  | (?:							 [0-9A-Fa-f]{1,4})?::(?:[0-9A-Fa-f]{1,4}:){4}
				  | (?:(?:[0-9A-Fa-f]{1,4}:){0,1}[0-9A-Fa-f]{1,4})?::(?:[0-9A-Fa-f]{1,4}:){3}
				  | (?:(?:[0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})?::(?:[0-9A-Fa-f]{1,4}:){2}
				  | (?:(?:[0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})?::	[0-9A-Fa-f]{1,4}:
				  | (?:(?:[0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})?::
				  )
				  (?P<ls32>[0-9A-Fa-f]{1,4}:[0-9A-Fa-f]{1,4}
				  | (?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}
					   (?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)
				  )
				|	(?:(?:[0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})?::	[0-9A-Fa-f]{1,4}
				|	(?:(?:[0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})?::
				)
			  | (?P<IPvFuture>[Vv][0-9A-Fa-f]+\.[A-Za-z0-9\-._~!$&\'()*+,;=:]+)
			  )
			  \]
			)
		  | (?P<IPv4address>(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}
							   (?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))
		  | (?P<regname>(?:[A-Za-z0-9\-._~!$&\'()*+,;=]|%[0-9A-Fa-f]{2})+)
		  )
		  (?::(?P<port>[0-9]*))?
		)
		(?P<path_abempty>(?:\/(?:[A-Za-z0-9\-._~!$&\'()*+,;=:@]|%[0-9A-Fa-f]{2})*)*)
		(?:\?(?P<query>		  (?:[A-Za-z0-9\-._~!$&\'()*+,;=:@\\/?]|%[0-9A-Fa-f]{2})*))?
		(?:\#(?P<fragment>	  (?:[A-Za-z0-9\-._~!$&\'()*+,;=:@\\/?]|%[0-9A-Fa-f]{2})*))?
		$
		/mx', $url, $m)) return FALSE;
	switch ($m['scheme'])
	{
	case 'https':
	case 'http':
		if ($m['userinfo']) return FALSE; // HTTP scheme does not allow userinfo.
		break;
	case 'ftps':
	case 'ftp':
		break;
	default:
		return FALSE;	// Unrecognised URI scheme. Default to FALSE.
	}
	// Validate host name conforms to DNS "dot-separated-parts".
	if ($m{'regname'}) // If host regname specified, check for DNS conformance.
	{
		if (!preg_match('/# HTTP DNS host name.
			^					   # Anchor to beginning of string.
			(?!.{256})			   # Overall host length is less than 256 chars.
			(?:					   # Group dot separated host part alternatives.
			  [0-9A-Za-z]\.		   # Either a single alphanum followed by dot
			|					   # or... part has more than one char (63 chars max).
			  [0-9A-Za-z]		   # Part first char is alphanum (no dash).
			  [\-0-9A-Za-z]{0,61}  # Internal chars are alphanum plus dash.
			  [0-9A-Za-z]		   # Part last char is alphanum (no dash).
			  \.				   # Each part followed by literal dot.
			)*					   # One or more parts before top level domain.
			(?:					   # Top level domains
			  [A-Za-z]{2,63}|	   # Country codes are exactly two alpha chars.
			  xn--[0-9A-Za-z]{4,59})		   # Internationalized Domain Name (IDN)
			$					   # Anchor to end of string.
			/ix', $m['host'])) return FALSE;
	}
	$m['url'] = $url;
	for ($i = 0; isset($m[$i]); ++$i) unset($m[$i]);
	return $m; // return TRUE == array of useful named $matches plus the valid $url.
}

//
// Replace string matching regular expression
//
// This function takes care of possibly disabled unicode properties in PCRE builds
//
function ucp_preg_replace($pattern, $replace, $subject, $callback = false)
{
	if($callback) 
		$replaced = preg_replace_callback($pattern, create_function('$matches', 'return '.$replace.';'), $subject);
	else
		$replaced = preg_replace($pattern, $replace, $subject);

	// If preg_replace() returns false, this probably means unicode support is not built-in, so we need to modify the pattern a little
	if ($replaced === false)
	{
		if (is_array($pattern))
		{
			foreach ($pattern as $cur_key => $cur_pattern)
				$pattern[$cur_key] = str_replace('\p{L}\p{N}', '\w', $cur_pattern);

			$replaced = preg_replace($pattern, $replace, $subject);
		}
		else
			$replaced = preg_replace(str_replace('\p{L}\p{N}', '\w', $pattern), $replace, $subject);
	}

	return $replaced;
}

//
// A wrapper for ucp_preg_replace
//
function ucp_preg_replace_callback($pattern, $replace, $subject)
{
	return ucp_preg_replace($pattern, $replace, $subject, true);
}

//
// Replace four-byte characters with a question mark
//
// As MySQL cannot properly handle four-byte characters with the default utf-8
// charset up until version 5.5.3 (where a special charset has to be used), they
// need to be replaced, by question marks in this case.
//
function strip_bad_multibyte_chars($str)
{
	$result = '';
	$length = strlen($str);

	for ($i = 0; $i < $length; $i++)
	{
		// Replace four-byte characters (11110www 10zzzzzz 10yyyyyy 10xxxxxx)
		$ord = ord($str[$i]);
		if ($ord >= 240 && $ord <= 244)
		{
			$result .= '?';
			$i += 3;
		}
		else
			$result .= $str[$i];
	}

	return $result;
}

//
// Check whether a file/folder is writable.
//
// This function also works on Windows Server where ACLs seem to be ignored.
//
function forum_is_writable($path)
{
	if (is_dir($path))
	{
		$path = rtrim($path, '/').'/';
		return forum_is_writable($path.uniqid(mt_rand()).'.tmp');
	}

	// Check temporary file for read/write capabilities
	$rm = file_exists($path);
	$f = @fopen($path, 'a');

	if ($f === false)
		return false;

	fclose($f);

	if (!$rm)
		@unlink($path);

	return true;
}

//
// Display executed queries (if enabled)
//
function display_saved_queries()
{
	global $db, $lang_common;

	// Get the queries so that we can print them out
	$saved_queries = $db->get_saved_queries();

	$queries = array();
	$query_time_total = 0.0;
	foreach ($saved_queries as $cur_query)
	{
		$query_time_total += $cur_query[1];
		$queries[] = array(
			'sql' => $cur_query[0],
			'time' => $cur_query[1],
		);
	}

	$tpl = load_template('debug.tpl');
	return $tpl->render(
		array(
			'lang_common' => $lang_common,
			'query_time_total' => $query_time_total,
			'queries' => $queries,
		)
	);
}

//
// Dump contents of variable(s)
//
function dump()
{
	echo '<pre>';

	$num_args = func_num_args();

	for ($i = 0; $i < $num_args; ++$i)
	{
		print_r(func_get_arg($i));
		echo "\n\n";
	}

	echo '</pre>';
}

function check_queue($form_username, $attempt, $db)
{
	$data = array(
		':timeout'	=>	(TIMEOUT * 1000),
		':username'	=>	$form_username,
	);

	$ps = $db->select('login_queue', 'id', $data, 'last_checked > NOW() - INTERVAL :timeout MICROSECOND AND username = :username', 'id ASC LIMIT 1');
	$id = $ps->fetchColumn();

	// Due to the fact we're not updating with data, we can't use the update() method. Instead, we have to use run() to avoid the string 'CURRENT_TIMESTAMP' being the value entered.
	$db->run('UPDATE '.$db->prefix.'login_queue SET last_checked = CURRENT_TIMESTAMP WHERE id=? LIMIT 1', array($attempt));
	return ($id == $attempt) ? true : false;
}

function isbot($ua)
{
	if ('' == panther_trim($ua)) return false;

	$ual = strtolower($ua);
	if (strstr($ual, 'bot') || strstr($ual, 'spider') || strstr($ual, 'crawler')) return true;

	if (strstr($ua, 'Mozilla/'))
	{
		if (strstr($ua, 'Gecko')) return false;
		if (strstr($ua, '(compatible; MSIE ') && strstr($ua, 'Windows')) return false;
	}
	else if (strstr($ua, 'Opera/'))
	{
		if (strstr($ua, 'Presto/')) return false;
	}

	return true;
}

function isbotex($ra)
{
	$ua = getenv('HTTP_USER_AGENT');

	if (!isbot($ua)) return $ra;

	$pat = array(
		'%(https?://|www\.).*%i',
		'%.*compatible[^\s]*%i',
		'%[\w\.-]+@[\w\.-]+.*%',
		'%.*?([^\s]+(bot|spider|crawler)[^\s]*).*%i',
		'%(?<=[\s_-])(bot|spider|crawler).*%i',
		'%(Mozilla|Gecko|Firefox|AppleWebKit)[^\s]*%i',
//		'%(MSIE|Windows|\.NET|Linux)[^;]+%i',
//		'%[^\s]*\.(com|html)[^\s]*%i',
		'%\/[v\d]+.*%',
		'%[^0-9a-z\.]+%i'
	);
	$rep = array(
		' ',
		' ',
		' ',
		'$1',
		' ',
		' ',
//		' ',
//		' ',
		' ',
		' '
	);
	$ua = panther_trim(preg_replace($pat, $rep, $ua));

	if (empty($ua)) return $ra.'[Bot]Unknown';

	$a = explode(' ', $ua);
	$ua = $a[0];
	if (strlen($ua) < 20 && !empty($a[1])) $ua.= ' '.$a[1];
	if (strlen($ua) > 25) $ua = 'Unknown';

	return $ra.'[Bot]'.$ua;
}

function generate_user_location($url)
{
	global $db, $panther_user, $lang_online, $panther_url;
	static $perms;

	if (!defined('PANTHER_FP_LOADED'))
	{
		$perms = array();
		if (file_exists(FORUM_CACHE_DIR.'cache_perms.php'))
			require FORUM_CACHE_DIR.'cache_perms.php';
		else
		{
			if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
				require PANTHER_ROOT.'include/cache.php';

			generate_perms_cache();
			require FORUM_CACHE_DIR.'cache_perms.php';
		}
	}

	switch ($url)
	{
		case null:
			$location = $lang_online['bot'];
		break;
		case 'index.php':
			$location = $lang_online['viewing index'];
		break;
		case stristr($url, 'userlist.php'):	
			$location = $lang_online['viewing userlist'];
		break;
		case 'online.php':
			$location = $lang_online['viewing online'];
		break;
		case 'misc.php?action=rules':
			$location = $lang_online['viewing rules'];
		break;
		case stristr($url, 'search'):
			$location = $lang_online['searching'];
		break;
		case stristr($url, 'help'):	
			$location = $lang_online['bbcode help'];
		break;
		case stristr($url, 'profile'):
			$id = filter_var($url, FILTER_SANITIZE_NUMBER_INT);
			
			$data = array(
				':id'	=>	$id,
			);

			$ps = $db->select('users', 'username, group_id', $data, 'id=:id');
			$user = $ps->fetch();

			$username = colourize_group($user['username'], $user['group_id'], $id);
			$location = sprintf($lang_online['viewing profile'], $username);
		break;
		case stristr($url, 'pms_'):
			$location = $lang_online['private messaging'];
		break;
		case stristr($url, 'admin'):
			$location = $lang_online['administration'];
		break;
		case stristr($url, 'login'):	
			$location = $lang_online['login'];
		break;
		case stristr($url, 'viewforum.php'):

			if (strpos($url, '&p=')!== false)
			{
				preg_match('~&p=(.*)~', $url, $replace);
				$url = str_replace($replace[0], '', $url);
			}
			$id = filter_var($url, FILTER_SANITIZE_NUMBER_INT);
			$data = array(
				':id'	=>	$id,
			);

			$ps = $db->select('forums', 'forum_name', $data, 'id=:id');
			$forum_name = $ps->fetchColumn();

			if (!isset($perms[$panther_user['g_id'].'_'.$id]))
				$perms[$panther_user['g_id'].'_'.$id] = $perms['_'];
			
			if ($perms[$panther_user['g_id'].'_'.$id]['read_forum'] == '1' || is_null($perms[$panther_user['g_id'].'_'.$id]['read_forum']))
				$location = array('href' => panther_link($panther_url['forum'], array($id, url_friendly($forum_name))), 'name' => $forum_name, 'lang' => $lang_online['viewing forum']);
			else
				$location = $lang_online['in hidden forum'];
		break;
		case stristr($url, 'viewtopic.php?pid'): //Now for the nasty part =)
			$pid = filter_var($url, FILTER_SANITIZE_NUMBER_INT);
			$data = array(
				':id'	=>	$pid,
			);

			$ps = $db->run('SELECT t.subject, t.forum_id AS fid FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON p.topic_id=t.id WHERE p.id=:id', $data);
			$info = $ps->fetch();

			if (!isset($perms[$panther_user['g_id'].'_'.$info['fid']]))
				$perms[$panther_user['g_id'].'_'.$info['fid']] = $perms['_'];
			
			if ($perms[$panther_user['g_id'].'_'.$info['fid']]['read_forum'] == '1' || is_null($perms[$panther_user['g_id'].'_'.$info['fid']]['read_forum']))
				$location = array('href' => panther_link($panther_url['post'], array($pid)), 'name' => $info['subject'], 'lang' => $lang_online['viewing topic']);
			else
				$location = $lang_online['in hidden forum'];
		break;
		case stristr($url, 'viewtopic.php?id'):	
			if (strpos($url, '&p=')!== false)
			{
				preg_match('~&p=(.*)~', $url, $replace);
				$url = str_replace($replace[0], '', $url);
			}
			$id = filter_var($url, FILTER_SANITIZE_NUMBER_INT);
			$data = array(
				':id'	=>	$id,
			);
			
			$ps = $db->select('topics', 'subject, forum_id AS fid', $data, 'id=:id');
			$info = $ps->fetch();

			if (!isset($perms[$panther_user['g_id'].'_'.$info['fid']]))
				$perms[$panther_user['g_id'].'_'.$info['fid']] = $perms['_'];

			if ($perms[$panther_user['g_id'].'_'.$info['fid']]['read_forum'] == '1' || is_null($perms[$panther_user['g_id'].'_'.$info['fid']]['read_forum']))
				$location = array('href' => panther_link($panther_url['topic'], array($id, url_friendly($info['subject']))), 'name' => $info['subject'], 'lang' => $lang_online['viewing topic']);
			else
				$location = $lang_online['in hidden forum'];
		break;
		case stristr($url, 'post.php?action=post'):
			$location = $lang_online['posting'];
		break;
		case stristr($url, 'post.php?fid'):	
			$fid = filter_var($url, FILTER_SANITIZE_NUMBER_INT);
			$data = array(
				':id'	=>	$fid,
			);
			
			$ps = $db->select('forums', 'forum_name', $data, 'id=:id');
			$forum_name = $ps->fetchColumn();

			if (!isset($perms[$panther_user['g_id'].'_'.$fid]))
				$perms[$panther_user['g_id'].'_'.$fid] = $perms['_'];
			
			if ($perms[$panther_user['g_id'].'_'.$fid]['read_forum'] == '1' || is_null($perms[$panther_user['g_id'].'_'.$fid]['read_forum']))
				$location = array('href' => panther_link($panther_url['forum'], array($fid, url_friendly($forum_name))), 'lang' => $forum_name, 'lang' => $lang_online['posting topic']);
			else
				$location = $lang_online['in hidden forum'];
		break;
		case stristr($url, 'post.php?tid'):
			$tid = filter_var($url, FILTER_SANITIZE_NUMBER_INT);
			$data = array(
				':id'	=>	$tid,
			);

			$ps = $db->select('topics', 'subject, forum_id AS fid', $data, 'id=:id');
			$info = $ps->fetch();	

			if (!isset($perms[$panther_user['g_id'].'_'.$info['fid']]))
				$perms[$panther_user['g_id'].'_'.$info['fid']] = $perms['_'];

			if ($perms[$panther_user['g_id'].'_'.$info['fid']]['read_forum'] == '1' || is_null($perms[$panther_user['g_id'].'_'.$info['fid']]['read_forum']))
				$location = array('href' => panther_link($panther_url['topic'], array($tid, url_friendly($info['subject']))), 'name' => $info['subject'], 'lang' => $lang_online['replying to topic']);
			else
				$location = $lang_online['in hidden forum'];
		break;
		case stristr($url, 'edit.php?id'):
			$id = filter_var($url, FILTER_SANITIZE_NUMBER_INT);
			$data = array(
				':id'	=>	$id,
			);

			$ps = $db->run('SELECT t.subject, t.forum_id AS fid FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON p.topic_id=t.id WHERE p.id=:id', $data);
			$info = $ps->fetch();

			if (!isset($perms[$panther_user['g_id'].'_'.$info['fid']]))
				$perms[$panther_user['g_id'].'_'.$info['fid']] = $perms['_'];

			if ($perms[$panther_user['g_id'].'_'.$info['fid']]['read_forum'] == '1' || is_null($perms[$panther_user['g_id'].'_'.$info['fid']]['read_forum']))
				$location = array('href' => panther_link($panther_url['post'], array($id)), 'name' => $info['subject'], 'lang' => $lang_online['editing topic']);
			else
				$location = $lang_online['in hidden forum'];
		break;
		case stristr($url, 'delete.php?id'):
			$id = filter_var($url, FILTER_SANITIZE_NUMBER_INT);
			$data = array(
				':id'	=>	$id,
			);

			$ps = $db->run('SELECT t.subject, t.forum_id AS fid FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON p.topic_id=t.id WHERE p.id=:id', $data);
			$info = $ps->fetch();

			if (!isset($perms[$panther_user['g_id'].'_'.$info['fid']]))
				$perms[$panther_user['g_id'].'_'.$info['fid']] = $perms['_'];
			
			if ($perms[$panther_user['g_id'].'_'.$info['fid']]['read_forum'] == '1' || is_null($perms[$panther_user['g_id'].'_'.$info['fid']]['read_forum']))
				$location = array('href' => panther_link($panther_url['post'], array($id)), 'name' => $info['subject'], 'lang' => $lang_online['deleting post']);
			else
				$location = $lang_online['in hidden forum'];
		break;
		case stristr($url, 'moderate.php'):
			$location = $lang_online['moderating'];
		break;
		case stristr($url, 'register.php'):	
			$location = $lang_online['register'];
		break;
		case stristr($url, 'misc.php?action=leaders'):
			$location = $lang_online['viewing team'];
		break;
		case '-':
			$location = $lang_online['not online'];
		break;
		default:
			$location = $url;
		break;
	}
	return $location;
}

function format_time_difference($logged, $lang_online)
{ 
	$difference = time() - $logged;
	$intervals = array('minute'=> 60); 
	if ($difference < 60)
	{
		if ($difference == '1')
			$difference = sprintf($lang_online['second ago'], $difference);
		else
			$difference = sprintf($lang_online['seconds ago'], $difference);
	}        

	if ($difference >= 60)
	{
		$difference = floor($difference/$intervals['minute']);
		if ($difference == '1')
			$difference = sprintf($lang_online['minute ago'], $difference);
		else
			$difference = sprintf($lang_online['minutes ago'], $difference);
	}  
	return $difference;
}

function colourize_group($username, $gid, $user_id = 1)
{
	global $panther_user, $panther_url;
	static $colourize_cache = array();

	if (!isset($colourize_cache[$username]))
	{
		$name = '<span class="gid'.$gid.'">'.panther_htmlspecialchars($username).'</span>';

		if ($panther_user['g_view_users'] == 1 && $user_id > 1)
			$colourize_cache[$username] = '<a href="'.panther_link($panther_url['profile'], array($user_id, url_friendly($username))).'">'.$name.'</a>';
		else
			$colourize_cache[$username] = $name;
	}

	return $colourize_cache[$username];
}

function attach_delete_thread($id = 0)
{
	global $db, $panther_config;

	// Should we orhpan any attachments
	if ($panther_config['o_create_orphans'] == 0)
	{
		$data = array(
			':id'	=>	$id,	
		);
		$ps = $db->run('SELECT a.id FROM '.$db->prefix.'attachments AS a LEFT JOIN '.$db->prefix.'posts AS p ON a.post_id=p.id WHERE p.topic_id=:id', $data);
		if ($ps->rowCount())
		{
			$ps->setFetchMode(PDO::FETCH_COLUMN, 0);
			foreach ($ps as $attach_id)
			{
				if (!delete_attachment($attach_id))
					continue;
			}
		}
	}
}

function attach_delete_post($id = 0)
{
	global $db;
	$data = array(
		':id'	=>	$id,
	);

	$ps = $db->run('SELECT a.id FROM '.$db->prefix.'attachments AS a WHERE a.post_id=:id', $data);
	if ($ps->rowCount())
	{
		$ps->setFetchMode(PDO::FETCH_COLUMN, 0);		
		foreach ($ps as $attach_id)
		{
				if (!delete_attachment($attach_id))
					continue;
		}
	}
}

function delete_attachment($item = 0)
{
	global $db, $panther_user, $panther_config;
	$data = array(
		':uid'	=>	$panther_user['g_id'],
		':id'	=>	$item,
	);
	
	// Make sure the item actually exists
	$can_delete = false;
	$ps = $db->run('SELECT a.owner, a.location FROM '.$db->prefix.'attachments AS a LEFT JOIN '.$db->prefix.'posts AS p ON a.post_id=p.id LEFT JOIN '.$db->prefix.'topics AS t ON p.topic_id=t.id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON t.forum_id=fp.forum_id AND fp.group_id=:uid WHERE a.id=:id', $data);
	if (!$ps->rowCount())
		message($lang_common['Bad request']);
	else
		$attachment = $ps->fetch();
	
	$data = array(
		':id'	=>	$item,
	);

	$db->delete('attachments', 'id=:id', $data);
	if ($panther_config['o_create_orphans'] == '0')
		@unlink($panther_config['o_attachments_dir'].$attachment['location']);

	return true;
}

function file_upload_error_message($code) 
{
	switch ($code) 
	{ 
		case UPLOAD_ERR_INI_SIZE: 
			return 'The uploaded file exceeds the upload_max_filesize configuration setting in php.ini'; 
		case UPLOAD_ERR_FORM_SIZE: 
			return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'; 
		case UPLOAD_ERR_PARTIAL: 
			return 'The uploaded file was only partially uploaded'; 
		case UPLOAD_ERR_NO_FILE: 
			return 'No file was uploaded'; 
		case UPLOAD_ERR_NO_TMP_DIR: 
			return 'Missing a temporary folder'; 
		case UPLOAD_ERR_CANT_WRITE: 
			return 'Failed to write file to disk'; 
		case UPLOAD_ERR_EXTENSION: 
			return 'File upload stopped by extension'; 
		default: 
			return 'An unknown upload error was encountered'; 
	} 
}

function create_attachment($name = '', $mime = '', $size = 0, $tmp_name = '', $post_id = 0, $message = 0)
{
		global $db, $panther_user, $panther_config;

		$unique_name = attach_generate_filename($panther_config['o_attachments_dir'], $message, $size);
		if (!move_uploaded_file($tmp_name, $panther_config['o_attachments_dir'].$unique_name))
			error_handler(E_ERROR, 'Unable to move file from: '.$tmp_name.' to '.$panther_config['o_attachments_dir'].$unique_name, __FILE__, __LINE__);

		if (strlen($mime) < 1)
			$mime = attach_create_mime(attach_find_extention($name));

		$insert	= array(
			'owner'	=>	$panther_user['id'],
			'post_id'	=>	$post_id,
			'filename'	=>	$name,
			'extension'	=>	attach_get_extension($name),
			'mime'		=>	$mime,
			'location'	=>	$unique_name,
			'size'		=>	$size
		);

		$db->insert('attachments', $insert);
		return true;
}

function attach_generate_filename($storagepath, $messagelength = 0, $size = 0)
{
	// Login keys are one time use only. Use this as salt too.
	global $panther_user;

	$newfile = md5($messagelength.$size.$panther_user['login_key'].random_key(18)).'.attach';
	if (!is_file($storagepath.$newfile))
		return $newfile;
	else
		return attach_generate_filename($storagepath, $messagelength, $size);
}

function attach_create_mime($extension = '')
{
	// Some of these may well no longer exist.
	$mimes = array (
		'diff'			=> 		'text/x-diff',
		'patch'			=> 		'text/x-diff',
		'rtf' 			=>		'text/richtext',
		'html'			=>		'text/html',
		'htm'			=>		'text/html',
		'aiff'			=>		'audio/x-aiff',
		'iff'			=>		'audio/x-aiff',
		'basic'			=>		'audio/basic',
		'wav'			=>		'audio/wav',
		'gif'			=>		'image/gif',
		'jpg'			=>		'image/jpeg',
		'jpeg'			=>		'image/pjpeg',
		'tif'			=>		'image/tiff',
		'png'			=>		'image/x-png',
		'xbm'			=>		'image/x-xbitmap',
		'bmp'			=>		'image/bmp',
		'xjg'			=>		'image/x-jg',
		'emf'			=>		'image/x-emf',
		'wmf'			=>		'image/x-wmf',
		'avi'			=>		'video/avi',
		'mpg'			=>		'video/mpeg',
		'mpeg'			=>		'video/mpeg',
		'ps'			=>		'application/postscript',
		'b64'			=>		'application/base64',
		'macbinhex'		=>		'application/macbinhex40',
		'pdf'			=>		'application/pdf',
		'xzip'			=>		'application/x-compressed',
		'zip'			=>		'application/x-zip-compressed',
		'gzip'			=>		'application/x-gzip-compressed',
		'java'			=>		'application/java',
		'msdownload'	=>		'application/x-msdownload'
	);

	foreach ($mimes as $type => $mime)
	{
		if ($extension == $type)
			return $mime;
	}
	return 'application/octet-stream';
}

function attach_get_extension($filename = '')
{
	if (strlen($filename) < 1)
		return '';

	return strtolower(ltrim(strrchr($filename, "."), "."));
}

function check_file_extension($file_name)
{
	global $panther_config;

	$actual_extension = attach_get_extension($file_name);
	$always_deny = explode(',', $panther_config['o_always_deny']);
	foreach ($always_deny as $ext)
	{
		if ($ext == $actual_extension)
			return false;
	}
	
	return true;
}

function attach_icon($extension)
{
	global $panther_config, $panther_user;
	static $base_url, $attach_icons;

	$icon_dir = ($panther_config['o_attachment_icon_dir'] != '') ? $panther_config['o_attachment_icon_dir'] : get_base_url(true).'/'.$panther_config['o_attachment_icon_path'].'/';
	if ($panther_user['show_img'] == 0 || $panther_config['o_attachment_icons'] == 0)
		return '';

	if (!isset($attach_icons))
	{
		$attach_icons = array();
		$extensions = explode(',', $panther_config['o_attachment_extensions']);
		$icons = explode(',', $panther_config['o_attachment_images']);

		for ($i = 0; $i < count($extensions); $i++)
		{
			if (!isset($extensions[$i]) || !isset($icons[$i]))
				break;

			$attach_icons[$extensions[$i]] = $icons[$i];
		}
	}

	$icon = isset($attach_icons[$extension]) ? $attach_icons[$extension] : 'unknown.png';
	return array('file' => $icon_dir.$icon, 'extension' => $extension);
}

function return_bytes($val)
{
    $last = strtolower($val[strlen($val)-1]);
    switch($last)
	{
		case 'g':	// The 'G' modifier is available since PHP 5.1.0
            $val *= 1024;
		case 'm':
            $val *= 1024;
		case 'k':
            $val *= 1024;
    }

    return $val;
}

function check_authentication()
{
	global $lang_admin_common, $db, $panther_config, $panther_user;
	
	function send_authentication()
	{
		global $lang_admin_common;

		header('WWW-Authenticate: Basic realm="Panther Admin CP"');
		header('HTTP/1.1 401 Unauthorized');
		message($lang_admin_common['Unauthorised']);
	}

	if ($panther_config['o_http_authentication'] == '1')
	{
		if (!isset($_SERVER['PHP_AUTH_USER']) && !isset($_SERVER['PHP_AUTH_PW']))
			send_authentication();
		else if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
		{
			$form_username = panther_trim($_SERVER['PHP_AUTH_USER']);
			$form_password = panther_trim($_SERVER['PHP_AUTH_PW']);
			
			$data = array(
				':id'	=>	$panther_user['id'],
				':username'	=>	$form_username,
			);

			$ps = $db->select('users', 'password, salt', $data, 'username=:username AND id=:id');
			if (!$ps->rowCount())
				send_authentication();
			else
			{
				$cur_user = $ps->fetch();
				if (panther_hash($form_password.$cur_user['salt']) != $cur_user['password'])
					send_authentication();
			}
		}
	}
}

// Generate link to another page on the forum
function panther_link($link, $args = null)
{
	global $panther_config;
	static $base_url = null;

	$base_url = (!$base_url) ? get_base_url() : $base_url;

	$gen_link = $link;
	if ($args == null)
		$gen_link = $base_url.'/'.$link;
	else if (!is_array($args))
		$gen_link = $base_url.'/'.str_replace('$1', $args, $link);
	else
	{
		for ($i = 0; isset($args[$i]); ++$i)
			$gen_link = str_replace('$'.($i + 1), $args[$i], $gen_link);
		$gen_link = $base_url.'/'.$gen_link;
	}

	return $gen_link;
}

// Generate a hyperlink with parameters and anchor and a subsection such as a subpage
function get_sublink($link, $sublink, $subarg, $args = null)
{
	global $panther_config, $panther_url;
	static $base_url = null;

	$base_url = (!$base_url) ? get_base_url() : $base_url;

	if ($sublink == $panther_url['page'] && $subarg == 1)
		return panther_link($link, $args);

	$gen_link = $link;
	if (!is_array($args) && $args != null)
		$gen_link = str_replace('$1', $args, $link);
	else
	{
		for ($i = 0; isset($args[$i]); ++$i)
			$gen_link = str_replace('$'.($i + 1), $args[$i], $gen_link);
	}

	if (isset($panther_url['insertion_find']))
		$gen_link = $base_url.'/'.str_replace($panther_url['insertion_find'], str_replace('$1', str_replace('$1', $subarg, $sublink), $panther_url['insertion_replace']), $gen_link);
	else
		$gen_link = $base_url.'/'.$gen_link.str_replace('$1', $subarg, $sublink);

	return $gen_link;
}

// Make a string safe to use in a URL
function url_friendly($str)
{
	static $url_replace;

	if (!isset($url_replace))
		require PANTHER_ROOT.'include/url_replace.php';

	$str = strtr($str, $url_replace);
	$str = strtolower(utf8_decode($str));
	$str = panther_trim(preg_replace(array('/[^a-z0-9\s]/', '/[\s]+/'), array('', '-'), $str), '-');

	return $str;
}

//
//	Generate a one time use login key to set in the cookie
//
function generate_login_key($uid = 1)
{
	global $db, $panther_user;
	$key = random_pass(60);

	$data = array(
		':key'	=>	$key,
	);
	
	$ps = $db->select('users', 1, $data, 'login_key=:key');
	if ($ps->rowCount())	// There is already a key with this string (keys are unique)
		generate_login_key();
	else
	{
		$data = array(
			':id'	=>	($uid !=1) ? $uid : $panther_user['id'],
		);
		
		$update = array(
			'login_key'	=>	$key,
		);

		$db->update('users', $update, 'id=:id', $data);
		return $key;
	}
}

function check_archive_rules($archive_rules, $tid = 0)
{
	global $cur_topic, $db, $lang_common;
	
	$day = (24*60*60);	// Set some useful time related stuff
	$month = (24*60*60*date('t'));
	$year = ($month*12);

	$sql = $data = array();
	if ($archive_rules['closed'] != '2')
	{
		$data[] = $archive_rules['closed'];
		$sql[] = 'closed=?';
	}
	
	if ($archive_rules['sticky'] != '2')
	{
		$data[] = $archive_rules['sticky'];
		$sql[] = 'sticky=?';
	}
	
	if ($archive_rules['time'] != '0')
	{
		switch ($archive_rules['unit'])
		{
			case 'years':
				$seconds = $archive_rules['time']*$year;
			break;
			case 'months':
				$seconds = $archive_rules['time']*$month;
			break;
			case 'days':
			default:
				$seconds = $archive_rules['time']*$day;
			break;
		}

		$data[] = (time()-$seconds);
		$sql[] = 'last_post<?';
	}
	
	if ($archive_rules['forums'][0] != '0')
	{
		$forums = '';
		for ($i = 0; $i < count($i); $i++)
		{
			$forums .= ($forums != '') ? ',?' : '?';
			$data[] = $archive_rules['forums'][$i];
		}
		$sql[] = 'forum_id IN('.$forums.')';
	}

	if ($tid != 0)
	{
		$sql[] = 'id=?';
		$data[] = $tid;
		$fetch = 1;
	}
	else
	{
		$fetch = 'id';
		$sql[] = 'archived=0 AND deleted=0 AND approved=1';	// Make sure to get topics that have the ability to be archived
	}

	$ps = $db->select('topics', $fetch, $data, implode(' AND ', $sql));
	
	if ($tid != 0)
	{
		if ($ps->rowCount())	// Time to archive!
		{
			$update = array(
				'archived'	=>	1,
			);
			
			$data = array(
				':id'	=>	$tid,
			);

			return $db->update('topics', $update, 'id=:id', $data);
		}
		else
			return 0;
	}
	else
	{
		$topics = array(
			'count'	=>	$ps->rowCount(),
			'topics'	=>	array(),
		);

		$ps->setFetchMode(PDO::FETCH_COLUMN, 0);
		foreach ($ps as $tid)
			$topics['topics'][] = $tid;
	}
		return $topics;
}

//
// Format time in seconds to display as hours/days/months/never
//
function format_expiration_time($seconds)
{
	global $lang_warnings;
	$seconds = intval($seconds);

	if ($seconds <= 0)
		return $lang_warnings['Never'];
	else if ($seconds % (30*24*60*60) == '0')
	{
		//Months
		$expiration_value = $seconds / (30*24*60*60);
		return sprintf($lang_warnings['No of months'], $expiration_value);
	}
	else if ($seconds % (24*60*60) == '0')
	{
		//Days
		$expiration_value = $seconds / (24*60*60);
		return sprintf($lang_warnings['No of days'], $expiration_value);
	}
	else
	{
		//Hours
		$expiration_value = $seconds / (60*60);
		return sprintf($lang_warnings['No of hours'], $expiration_value);
	}
}

//
// Get expiration time (in seconds)
//
function get_expiration_time($value = 0, $unit)
{
	$value = abs(intval($value));

	if ($value == '0')
		$expiration_time = 0;
	else if ($unit == 'minutes')
		$expiration_time = $value*60;
	else if ($unit == 'hours')
		$expiration_time = $value*60*60;
	else if ($unit == 'days')
		$expiration_time = $value*24*60*60;
	else if ($unit == 'months')
		$expiration_time = $value*30*24*60*60;
	else if ($unit == 'never')
		$expiration_time = 0;
	else 
		$expiration_time = 0;

	return $expiration_time;
}

//
// Checks when a posting ban has expired
//
function format_posting_ban_expiration($expires, $lang_profile)
{
	$month = (24*60*60*date('t'));
	$day = (24*60*60);
	$hour = (60*60);
	$minute = 60;
	switch(true)
	{
		case ($expires > $month):
			$seconds = array(round($expires/$month), ($expires % $month), $lang_profile['Months']);
		break;
		case ($expires > $day):
			$seconds = array(round($expires/$day), ($expires % $day), $lang_profile['Days']);
		break;
		case ($expires > $hour):
			$seconds = array(round($expires/$hour), ($expires % $hour), $lang_profile['Hours']);
		break;
		case ($expires > $minute):
			$seconds = array(round($expires/$minute), ($expires % $minute), $lang_profile['Minutes']);
		break;
		default:
			$seconds = array(10, 0, $lang_profile['Never']);			
		break;
	}
	return $seconds;
}

function check_posting_ban()
{
	global $panther_user, $db, $lang_common;

	if ($panther_user['posting_ban'] != '0')
	{
		if ($panther_user['posting_ban'] < time())
		{
			$update = array(
				'posting_ban'	=>	0,
			);

			$data = array(
				':id'	=>	$panther_user['id'],
			);

			$db->update('users', $update, 'id=:id', $data);
		}
		else
			message(sprintf($lang_common['posting_ban'], format_time($panther_user['posting_ban'])));
	}
}

function stopforumspam_report($api_key, $remote_address, $email, $username, $message)
{
	$context = stream_context_create(
		array('http' => 
			array(
				'method'	=> 'POST',
				'header'	=> 'Content-type: application/x-www-form-urlencoded',
				'content'	=> http_build_query(
					array(
						'ip_addr'	=> $remote_address,
						'email'		=> $email,
						'username'	=> $username,
						'evidence'	=> $message,
						'api_key'	=> $api_key,
					)
				),
			)
		)
	);

	return @file_get_contents('http://www.stopforumspam.com/add', false, $context) ? true : false;
}

//
// Compress image using TinyPNG compression API
//
function compress_image($image)
{
	global $panther_config;
	if ($panther_config['o_tinypng_api'] == '')
		return;

	if (substr($image, strrpos($image, '.')+1) == 'gif') // Then it can't be compressed, and will return nothing causing the error handler
	{
		$key = max(array_keys(explode('/', $image)));
		cache_cloudflare($image[$key]);
		return;
	}

	if (is_callable('curl_init'))
	{
		$request = curl_init();
		curl_setopt_array($request, array(
			CURLOPT_URL => "https://api.tinypng.com/shrink",
			CURLOPT_USERPWD => "api:".$panther_config['o_tinypng_api'],
			CURLOPT_POSTFIELDS => file_get_contents($image),
			CURLOPT_BINARYTRANSFER => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => true,
			CURLOPT_SSL_VERIFYPEER => true
		));

		$response = curl_exec($request);
		if (curl_getinfo($request, CURLINFO_HTTP_CODE) === 201)
		{
			$headers = substr($response, 0, curl_getinfo($request, CURLINFO_HEADER_SIZE));
			foreach (explode("\r\n", $headers) as $header)
			{
				if (substr($header, 0, 10) === "Location: ")
				{
					$request = curl_init();
					curl_setopt_array($request, array(
						CURLOPT_URL => substr($header, 10),
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_SSL_VERIFYPEER => true
					));

					// Replace the image with the compressed one
					file_put_contents($image, curl_exec($request));
				}
			}
		}
		else
			error_handler(E_ERROR, curl_error($request), __FILE__, __LINE__);
			
		// We only want this doing if the first one works
		$key = max(array_keys(explode('/', $image)));
		cache_cloudflare($image[$key]);
	}
}

//
// Cache images through CloudFlare API
//
function cache_cloudflare($file)
{
	global $panther_config;

	if ($panther_config['o_cloudflare_api'] != '')
		return;

	if (is_callable('curl_init'))
	{
		$request = curl_init("https://www.cloudflare.com/api_json.html");
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($request, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);

		$params = array(
			'a' => 'zone_file_purge',
			'tkn' => $panther_config['o_cloudflare_api'],
			'email' => $panther_config['o_clouflare_email'],
			'z' => $panther_config['o_clouflare_domain'],
			'url' => $panther_config['o_clouflare_domain'].$file,
		);
		curl_setopt($request, CURLOPT_POST, 1);
		curl_setopt($request, CURLOPT_POSTFIELDS, http_build_query($params));
		$response = curl_exec($request);
		if (curl_getinfo($request, CURLINFO_HTTP_CODE) === 201)
		{
			$result = json_decode($response, true);
			curl_close($request);

			if ($result['msg'] != 'success')
				error_handler(E_ERROR, $result['msg'], __FILE__, __LINE__);

			return true;
		}
	}
}

function xml_to_array($raw_xml)
{
	$xml_array = array();
	$xml_parser = xml_parser_create();
	xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 0);
	xml_parse_into_struct($xml_parser, $raw_xml, $parsed_xml);
	xml_parser_free($xml_parser);

	foreach ($parsed_xml as $xml_elem)
	{
		$x_tag = $xml_elem['tag'];
		$x_level = $xml_elem['level'];
		$x_type = $xml_elem['type'];

		if ($x_level != 1 && $x_type == 'close')
		{
			if (isset($multi_key[$x_tag][$x_level]))
				$multi_key[$x_tag][$x_level] = 1;
			else
				$multi_key[$x_tag][$x_level] = 0;
		}

		if ($x_level != 1 && $x_type == 'complete')
		{
			if (isset($tmp) && $tmp == $x_tag)
				$multi_key[$x_tag][$x_level] = 1;

			$tmp = $x_tag;
		}
	}

	foreach ($parsed_xml as $xml_elem)
	{
		$x_tag = $xml_elem['tag'];
		$x_level = $xml_elem['level'];
		$x_type = $xml_elem['type'];

		if ($x_type == 'open')
			$level[$x_level] = $x_tag;

		$start_level = 1;
		$php_stmt = '$xml_array';
		if ($x_type == 'close' && $x_level != 1)
			$multi_key[$x_tag][$x_level]++;

		while ($start_level < $x_level)
		{
			$php_stmt .= '[$level['.$start_level.']]';
			if (isset($multi_key[$level[$start_level]][$start_level]) && $multi_key[$level[$start_level]][$start_level])
				$php_stmt .= '['.($multi_key[$level[$start_level]][$start_level]-1).']';

			++$start_level;
		}

		$add = '';
		if (isset($multi_key[$x_tag][$x_level]) && $multi_key[$x_tag][$x_level] && ($x_type == 'open' || $x_type == 'complete'))
		{
			if (!isset($multi_key2[$x_tag][$x_level]))
				$multi_key2[$x_tag][$x_level] = 0;
			else
				$multi_key2[$x_tag][$x_level]++;

			$add = '['.$multi_key2[$x_tag][$x_level].']';
		}

		if (isset($xml_elem['value']) && panther_trim($xml_elem['value']) != '' && !isset($xml_elem['attributes']))
		{
			if ($x_type == 'open')
				$php_stmt_main = $php_stmt.'[$x_type]'.$add.'[\'content\'] = $xml_elem[\'value\'];';
			else
				$php_stmt_main = $php_stmt.'[$x_tag]'.$add.' = $xml_elem[\'value\'];';

			eval($php_stmt_main);
		}

		if (isset($xml_elem['attributes']))
		{
			if (isset($xml_elem['value']))
			{
				$php_stmt_main = $php_stmt.'[$x_tag]'.$add.'[\'content\'] = $xml_elem[\'value\'];';
				eval($php_stmt_main);
			}

			foreach ($xml_elem['attributes'] as $key=>$value)
			{
				$php_stmt_att=$php_stmt.'[$x_tag]'.$add.'[\'attributes\'][$key] = $value;';
				eval($php_stmt_att);
			}
		}
	}

	// Make sure there's an array of hooks (even if there is only one)
	if (isset($xml_array['extension']['hooks']) && isset($xml_array['extension']['hooks']['hook']))
	{
		if (!is_array(current($xml_array['extension']['hooks']['hook'])))
			$xml_array['extension']['hooks']['hook'] = array($xml_array['extension']['hooks']['hook']);
	}

	return $xml_array;
}

function validate_xml($xml, $errors)
{
	global $lang_admin_extensions;

	if (!isset($xml['extension']))
		$errors[] = $lang_admin_extensions['Extension not valid'];
	else
	{
		$extension = $xml['extension'];
		if (!isset($extension['attributes']['engine']) || $extension['attributes']['engine'] != '1.0')
			$errors[] = $lang_admin_extensions['Extension engine malformed'];
		else if (!isset($extension['title']) || $extension['title'] == '')
			$errors[] = $lang_admin_extensions['Extension title malformed'];
		else if (!isset($extension['version']) || !is_numeric($extension['version']))
			$errors[] = $lang_admin_extensions['Extension version malformed'];
		else if (!isset($extension['description']) || $extension['description'] == '')
			$errors[] = $lang_admin_extensions['Extension description malformed'];
		else if (!isset($extension['author']) || $extension['author'] == '')
			$errors[] = $lang_admin_extensions['Extension author malformed'];
		else if (!isset($extension['supported_versions']))
			$errors[] = $lang_admin_extensions['Extension versions malformed'];

		if (!isset($extension['hooks']) || !is_array($extension['hooks']))
			$errors[] = $lang_admin_extensions['Extension hooks malformed'];
		else
		{
			if (!isset($extension['hooks']['hook']) || !is_array($extension['hooks']['hook']))
				$errors[] = $lang_admin_extensions['Extension hooks missing'];
			else
			{
				foreach ($extension['hooks']['hook'] as $hook)
				{
					if (!isset($hook['content']) || $hook['content'] == '')
						$errors[] = $lang_admin_extensions['Extension hook content missing'];
					else if (!isset($hook['attributes']['id']) || $hook['attributes']['id'] == '')
						$errors[] = $lang_admin_extensions['Extension hook id missing'];
				}
			}
		}
	}

	return $errors;
}

function get_extensions($hook)
{
	global $panther_extensions;
	return (isset($panther_extensions[$hook])) ? implode("\n", $panther_extensions[$hook]) : false;
}

($hook = get_extensions('functions_after_functions')) ? eval($hook) : null;