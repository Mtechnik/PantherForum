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

if (($panther_user['is_admmod'] && $panther_user['g_mod_cp'] == '0' && !$panther_user['is_admin']) || !$panther_user['is_admmod'] || $panther_user['g_moderator'] == '1' && $panther_user['g_mod_ban_users'] == '0')
	message($lang_common['No permission'], false, '403 Forbidden');

check_authentication();

// Load the admin_bans.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_bans.php';

// Add/edit a ban (stage 1)
if (isset($_GET['add_ban']) || isset($_POST['add_ban']) || isset($_GET['edit_ban']))
{
	if (isset($_GET['add_ban']) || isset($_POST['add_ban']))
	{
		// If the ID of the user to ban was provided through GET (a link from profile.php)
		if (isset($_GET['add_ban']))
		{
			$user_id = isset($_GET['add_ban']) ? intval($_GET['add_ban']) : 1;
			if ($user_id < 2)
				message($lang_common['Bad request'], false, '404 Not Found');
			
			$data = array(
				':id'	=>	$user_id,
			);

			$ps = $db->select('users', 'group_id, username, email', $data, 'id=:id');
			if ($ps->rowCount())
				list($group_id, $ban_user, $ban_email) = $ps->fetch(PDO::FETCH_NUM);
			else
				message($lang_admin_bans['No user ID message']);
		}
		else // Otherwise the username is in POST
		{
			$ban_user = isset($_POST['new_ban_user']) ? panther_trim($_POST['new_ban_user']) : '';

			if ($ban_user != '')
			{
				$data = array(
					':username' => $ban_user
				);

				$ps = $db->select('users', 'id, group_id, username, email', $data, 'username=:username AND id>1');
				if ($ps->rowCount())
					list($user_id, $group_id, $ban_user, $ban_email) = $ps->fetch(PDO::FETCH_NUM);
				else
					message($lang_admin_bans['No user message']);
			}
		}

		// Make sure we're not banning an admin or moderator
		if (isset($group_id))
		{
			if ($group_id == PANTHER_ADMIN || $panther_groups[$group_id]['g_admin'] == 1)
				message(sprintf($lang_admin_bans['User is admin message'], $ban_user));

			if ($panther_groups[$group_id]['g_moderator'] == 1)
				message(sprintf($lang_admin_bans['User is mod message'], $ban_user));
		}

		// If we have a $user_id, we can try to find the last known IP of that user
		if (isset($user_id))
		{
			$data = array(
				':id'	=>	$user_id
			);

			$ps = $db->select('posts', 'poster_ip', $data, 'poster_id=:id', 'posted DESC LIMIT 1');
			$ban_ip = ($ps->rowCount()) ? $ps->fetchColumn() : '';

			if ($ban_ip == '')
			{
				$data = array(
					':id'	=>	$user_id,
				);

				$ps = $db->select('users', 'registration_ip', $data, 'id=:id');
				$ban_ip = ($ps->rowCount()) ? $ps->fetchColumn() : '';
			}
		}

		$mode = 'add';
	}
	else // We are editing a ban
	{
		$ban_id = intval($_GET['edit_ban']);
		if ($ban_id < 1)
			message($lang_common['Bad request'], false, '404 Not Found');

		$data = array(
			':id'	=>	$ban_id,
		);

		$ps = $db->select('bans', 'username, ip, email, message, expire', $data, 'id=:id');
		if ($ps->rowCount())
			list($ban_user, $ban_ip, $ban_email, $ban_message, $ban_expire) = $ps->fetch(PDO::FETCH_NUM);
		else
			message($lang_common['Bad request'], false, '404 Not Found');

		$diff = ($panther_user['timezone'] + $panther_user['dst']) * 3600;
		$ban_expire = ($ban_expire != '') ? gmdate('Y-m-d', $ban_expire + $diff) : '';

		$mode = 'edit';
	}

	$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Bans']);
	$focus_element = array('bans2', 'ban_user');
	define('PANTHER_ACTIVE_PAGE', 'admin');
	require PANTHER_ROOT.'header.php';

	generate_admin_menu('bans');

	$tpl = load_template('add_ban.tpl');
	echo $tpl->render(
		array(
			'lang_admin_bans' => $lang_admin_bans,
			'lang_admin_common' => $lang_admin_common,
			'form_action' => panther_link($panther_url['admin_bans']),
			'mode' => $mode,
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/bans.php'),
			'ban_user' => (isset($ban_user)) ? $ban_user : '',
			'ban_ip' => (isset($ban_ip)) ? $ban_ip : '',
			'ban_id' => isset($ban_id) ? $ban_id : '',
			'user_id' => (isset($user_id)) ? $user_id : '',
			'ban_help' => $ban_user != '' && isset($user_id) ? panther_link($panther_url['admin_users_ip_stats'], array($user_id)) : '',
			'ban_email' => isset($ban_email) ? $ban_email : '',
			'ban_message' => isset($ban_message) ? $ban_message : '',
			'ban_expire' => isset($ban_expire) ? $ban_expire : '',
		)
	);

	require PANTHER_ROOT.'footer.php';
}
else if (isset($_POST['add_edit_ban'])) // Add/edit a ban (stage 2)
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/bans.php');

	$ban_user = isset($_POST['ban_user']) ? panther_trim($_POST['ban_user']) : '';
	$ban_ip = isset($_POST['ban_ip']) ? panther_trim($_POST['ban_ip']) : '';
	$ban_email = isset($_POST['ban_email']) ? strtolower(panther_trim($_POST['ban_email'])) : '';
	$ban_message = isset($_POST['ban_message']) ? panther_trim($_POST['ban_message']) : '';
	$ban_expire = isset($_POST['ban_expire']) ? panther_trim($_POST['ban_expire']) : '';

	if ($ban_user == '' && $ban_ip == '' && $ban_email == '')
		message($lang_admin_bans['Must enter message']);
	else if (strtolower($ban_user) == 'guest')
		message($lang_admin_bans['Cannot ban guest message']);

	// Make sure we're not banning an admin or moderator
	if (!empty($ban_user))
	{
		$data = array(
			':username' => $ban_user,
		);

		$ps = $db->select('users', 'group_id', $data, 'username=:username AND id>1');
		if ($ps->rowCount())
		{
			$group_id = $ps->fetchColumn();
			if ($group_id == PANTHER_ADMIN || $panther_groups[$group_id]['g_admin'] == 1)
				message(sprintf($lang_admin_bans['User is admin message'], $ban_user));

			if ($panther_groups[$group_id]['g_moderator'] == '1')
				message(sprintf($lang_admin_bans['User is mod message'], $ban_user));
		}
	}

	// Validate IP/IP range (it's overkill, I know)
	if ($ban_ip != '')
	{
		$ban_ip = preg_replace('%\s{2,}%S', ' ', $ban_ip);
		$addresses = explode(' ', $ban_ip);
		$addresses = array_map('panther_trim', $addresses);

		for ($i = 0; $i < count($addresses); ++$i)
		{
			if (strpos($addresses[$i], ':') !== false)
			{
				$octets = explode(':', $addresses[$i]);

				for ($c = 0; $c < count($octets); ++$c)
				{
					$octets[$c] = ltrim($octets[$c], "0");

					if ($c > 7 || (!empty($octets[$c]) && !ctype_xdigit($octets[$c])) || intval($octets[$c], 16) > 65535)
						message($lang_admin_bans['Invalid IP message']);
				}

				$cur_address = implode(':', $octets);
				$addresses[$i] = $cur_address;
			}
			else
			{
				$octets = explode('.', $addresses[$i]);
				for ($c = 0; $c < count($octets); ++$c)
				{
					$octets[$c] = (strlen($octets[$c]) > 1) ? ltrim($octets[$c], "0") : $octets[$c];

					if ($c > 3 || preg_match('%[^0-9]%', $octets[$c]) || intval($octets[$c]) > 255)
						message($lang_admin_bans['Invalid IP message']);
				}

				$cur_address = implode('.', $octets);
				$addresses[$i] = $cur_address;
			}
		}

		$ban_ip = implode(' ', $addresses);
	}

	require PANTHER_ROOT.'include/email.php';
	if ($ban_email != '' && !$mailer->is_valid_email($ban_email))
	{
		if (!preg_match('%^[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,63})$%', $ban_email))
			message($lang_admin_bans['Invalid e-mail message']);
	}

	if ($ban_expire != '' && $ban_expire != 'Never')
	{
		$ban_expire = strtotime($ban_expire.' GMT');

		if ($ban_expire == -1 || !$ban_expire)
			message($lang_admin_bans['Invalid date message'].' '.$lang_admin_bans['Invalid date reasons']);

		$diff = ($panther_user['timezone'] + $panther_user['dst']) * 3600;
		$ban_expire -= $diff;

		if ($ban_expire <= time())
			message($lang_admin_bans['Invalid date message'].' '.$lang_admin_bans['Invalid date reasons']);
	}
	else
		$ban_expire = NULL;

	$ban_user = ($ban_user != '') ? $ban_user : null;
	$ban_ip = ($ban_ip != '') ? $ban_ip : null;
	$ban_email = ($ban_email != '') ? $ban_email : null;
	$ban_message = ($ban_message != '') ? $ban_message : null;
	
	$fields = array(
		'username'	=>	$ban_user,
		'ip'		=>	$ban_ip,
		'email'		=>	$ban_email,
		'message'	=>	$ban_message,
		'expire'	=>	$ban_expire,
	);

	if ($_POST['mode'] == 'add')
	{
		$fields['ban_creator'] = $panther_user['id'];
		$db->insert('bans', $fields);
	}
	else
	{
		$data = array(
			':id'	=>	intval($_POST['ban_id']),
		);

		$db->update('bans', $fields, 'id=:id', $data);
	}

	// Regenerate the bans cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_bans_cache();

	if ($_POST['mode'] == 'add' && $panther_config['o_ban_email'] == '1' && $ban_user != null) // Make sure we're banning a user, not an IP range
	{
		if (is_null($ban_email))
		{
			$data = array(
				':username'	=>	$ban_user,
			);

			$ps = $db->select('users', 'email', $data, 'username=:username');
			$ban_email = $ps->fetchColumn();
		}

		$ban_expire = ($ban_expire != null) ? sprintf($lang_admin_bans['temporary ban'], format_time($ban_expire)) : $lang_admin_bans['permanent ban'];
		$ban_message = ($ban_message != '') ? $ban_message : $lang_admin_bans['no message left'];

		$info = array(
			'message' => array(
				'<username>' => $panther_user['username'],
				'<unban_date>' => $ban_expire,
				'<message>' => $ban_message,
			)
		);

		$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/banned.tpl', $info);
		$mailer->send($ban_email, $mail_subject, $mail_message);
	}

	$redirect_msg = ($_POST['mode'] == 'edit') ? $lang_admin_bans['Ban edited redirect'] : $lang_admin_bans['Ban added redirect'];
	redirect(panther_link($panther_url['admin_bans']), $redirect_msg);
}

// Remove a ban
else if (isset($_GET['delete_ban']))
{
	$ban_id = intval($_GET['delete_ban']);
	if ($ban_id < 1)
		message($lang_common['Bad request'], false, '404 Not Found');

	$data = array(
		':id'	=>	$ban_id,
	);

	$db->delete('bans', 'id=:id', $data);

	// Regenerate the bans cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_bans_cache();
	redirect(panther_link($panther_url['admin_bans']), $lang_admin_bans['Ban removed redirect']);
}

// Find bans
else if (isset($_GET['find_ban']))
{
	$form = isset($_GET['form']) && is_array($_GET['form']) ? array_map('panther_trim', $_GET['form']) : array();
	$conditions = $query_str = $data = $sql = array();

	$expire_after = isset($_GET['expire_after']) ? panther_trim($_GET['expire_after']) : '';
	$expire_before = isset($_GET['expire_before']) ? panther_trim($_GET['expire_before']) : '';
	$order_by = isset($_GET['order_by']) && in_array($_GET['order_by'], array('username', 'ip', 'email', 'expire')) ? 'b.'.$_GET['order_by'] : 'b.username';
	$direction = isset($_GET['direction']) && $_GET['direction'] == 'DESC' ? 'DESC' : 'ASC';

	$sql[] = 'b.id>0';
	$query_str[] = 'order_by='.$order_by;
	$query_str[] = 'direction='.$direction;

	// Try to convert date/time to timestamps
	if ($expire_after != '')
	{
		$query_str[] = 'expire_after='.$expire_after;
		$expire_after = strtotime($expire_after);
		if ($expire_after === false || $expire_after == -1)
			message($lang_admin_bans['Invalid date message']);

		$sql[] = 'b.expire>?';
		$data[] = $expire_after;
	}

	if ($expire_before != '')
	{
		$query_str[] = 'expire_before='.$expire_before;

		$expire_before = strtotime($expire_before);
		if ($expire_before === false || $expire_before == -1)
			message($lang_admin_bans['Invalid date message']);

		$sql[] = 'b.expire<?';
		$data[] = $expire_before;
	}

	foreach ($form as $key => $input)
	{
		if ($input != '' && in_array($key, array('username', 'ip', 'email', 'message')))
		{
			$sql[] = 'b.'.$key.' LIKE ?';
			$data[] = str_replace('*', '%', $input);
			$query_str[] = 'form%5B'.$key.'%5D='.urlencode($input);
		}
	}

	// Fetch ban count
	$ps = $db->run('SELECT COUNT(id) FROM '.$db->prefix.'bans AS b WHERE '.implode(' AND ', $sql), $data);
	$num_bans = $ps->fetchColumn();

	// Determine the ban offset (based on $_GET['p'])
	$num_pages = ceil($num_bans / 50);

	$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);
	$start_from = 50 * ($p - 1);
	$data[] = $start_from;

	$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Bans'], $lang_admin_bans['Results head']);
	define('PANTHER_ACTIVE_PAGE', 'admin');
	require PANTHER_ROOT.'header.php';

	$ps = $db->run('SELECT b.id, b.username, b.ip, b.email, b.message, b.expire, b.ban_creator, u.username AS ban_creator_username FROM '.$db->prefix.'bans AS b LEFT JOIN '.$db->prefix.'users AS u ON b.ban_creator=u.id WHERE '.implode(' AND ', $sql).' ORDER BY '.$order_by.' '.$direction.' LIMIT ?, 50', $data);
	$bans = array();
	foreach ($ps as $ban_data)
		$bans[] = array(
			'username' => $ban_data['username'],
			'email' => $ban_data['email'],
			'ip' => $ban_data['ip'],
			'message' => $ban_data['message'],
			'expires' => format_time($ban_data['expire'], true),
			'creator' => ($ban_data['ban_creator_username'] != '') ? array('href' => panther_link($panther_url['profile'], array($ban_data['ban_creator'], url_friendly($ban_data['ban_creator_username']))), 'title' => $ban_data['ban_creator_username']) : '',
			'edit_link' => panther_link($panther_url['edit_ban'], array($ban_data['id'])),
			'delete_link' => panther_link($panther_url['del_ban'], array($ban_data['id'])),
		);

	$tpl = load_template('display_bans.tpl');
	echo $tpl->render(
		array(
			'lang_admin_common' => $lang_admin_common,
			'lang_admin_bans' => $lang_admin_bans,
			'index_link' => panther_link($panther_url['admin_index']),
			'bans_link' => panther_link($panther_url['admin_bans']),
			'pagination' => paginate($num_pages, $p, $panther_url['admin_bans'].'?find_ban=&amp;'.implode('&amp;', $query_str)),
			'bans' => $bans,
		)
	);

	require PANTHER_ROOT.'footer.php';
}

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Bans']);
$focus_element = array('bans', 'new_ban_user');
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

generate_admin_menu('bans');

$tpl = load_template('admin_bans.tpl');
echo $tpl->render(
	array(
		'lang_admin_bans' => $lang_admin_bans,
		'lang_admin_common' => $lang_admin_common,
		'form_action' => panther_link($panther_url['more_bans']),
		'search_action' => panther_link($panther_url['admin_bans']),
	)
);

require PANTHER_ROOT.'footer.php';