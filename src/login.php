<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

if (isset($_GET['action']))
	define('PANTHER_QUIET_VISIT', 1);

if (!defined('PANTHER'))
{
	define('PANTHER_ROOT', __DIR__.'/');
	require PANTHER_ROOT.'include/common.php';
}

if ($panther_user['is_bot'])	// I can't think of one valid reason why bots should be able to attempt to login
	message($lang_common['No permission']);

// Load the login.php language file
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/login.php';

$action = isset($_GET['action']) ? $_GET['action'] : null;

$errors = array();
if (isset($_POST['form_sent']) && $action == 'in')
{
	($hook = get_extensions('login_before_validation')) ? eval($hook) : null;

	$form_username = isset($_POST['req_username']) ? panther_trim($_POST['req_username']) : '';
	$form_password = isset($_POST['req_password']) ? panther_trim($_POST['req_password']) : '';
	$save_pass = isset($_POST['save_pass']);

	if ($panther_config['o_login_queue'] == '1')
	{
		$data = array(
			':username'	=>	$form_username,
			':timeout'	=>	(TIMEOUT * 1000),
		);

		$ps = $db->select('login_queue', 'COUNT(*) AS overall, COUNT(IF(username = :username, TRUE, NULL)) AS user', $data, 'last_checked > NOW() - INTERVAL :timeout MICROSECOND');
		$count = $ps->fetch();

		if (!$count)
			message($lang_login['Unable to query size']);
		else if ($count['overall'] >= $panther_config['o_queue_size'] || $count['user'] >= $panther_config['o_max_attempts'])
			message($lang_login['Login queue exceeded']);	

		$insert = array(
			'ip_address' => get_remote_address(),
			'username' => $form_username,
		);

		$db->insert('login_queue', $insert) or message($lang_login['IP address in queue']);
		$attempt = $db->lastInsertId($db->prefix.'login_queue');

		// This time, it's actually in our favour. Yes, I know!
		while (!check_queue($form_username, $attempt, $db))
			usleep(250 * 1000);

		//Force delay between logins, remove dead attempts
		usleep(ATTEMPT_DELAY * 1000);

		$data = array(
			':id'	=>	$attempt,
			':timeout'	=>	(TIMEOUT * 1000),
		);

		$db->delete('login_queue', 'id=:id OR last_checked < NOW() - INTERVAL :timeout MICROSECOND', $data);
	}

	$data = array(
		':username'	=>	$form_username,
	);

	$ps = $db->select('users', 'password, salt, group_id, id, login_key', $data, 'username=:username');
	$cur_user = $ps->fetch();

	if (!panther_hash_equals($cur_user['password'], panther_hash($form_password.$cur_user['salt'])))
		$errors[] = sprintf($lang_login['Wrong user/pass'], ' <a href="'.panther_link($panther_url['request_password']).'">'.$lang_login['Forgotten pass'].'</a>');

	($hook = get_extensions('login_after_validation')) ? eval($hook) : null;

	if (empty($errors))
	{
		// Update the status if this is the first time the user logged in
		if ($cur_user['group_id'] == PANTHER_UNVERIFIED)
		{
			$update = array(
				'group_id'	=>	$panther_config['o_default_user_group'],
			);

			$data = array(
				':id'	=>	$cur_user['id'],
			);

			$db->update('users', $update, 'id=:id', $data);

			// Regenerate the users info cache
			if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
				require PANTHER_ROOT.'include/cache.php';

			generate_users_info_cache();
		}

		// Remove this user's guest entry from the online list
		$data = array(
			':ident' => get_remote_address(),
		);

		$db->delete('online', 'ident=:ident', $data);

		$expire = ($save_pass == '1') ? time() + 1209600 : time() + $panther_config['o_timeout_visit'];
		panther_setcookie($cur_user['id'], $cur_user['login_key'], $expire);

		// Reset tracked topics
		set_tracked_topics(null);

		// Try to determine if the data in redirect_url is valid (if not, we redirect to index.php after login)
		$redirect_url = validate_redirect($_POST['redirect_url'], panther_link($panther_url['index']));
		redirect($redirect_url, $lang_login['Login redirect']);
	}
}
else if ($action == 'out')
{
	if ($panther_user['is_guest'] || !isset($_GET['id']) || $_GET['id'] != $panther_user['id'])
	{
		header('Location: '.panther_link($panther_url['index']));
		exit;
	}
	
	confirm_referrer('login.php');
	$data = array(
		':id'	=>	$panther_user['id'],
	);

	// Remove user from "users online" list
	$db->delete('online', 'user_id=:id', $data);
	generate_login_key();

	// Update last_visit (make sure there's something to update it with)
	if (isset($panther_user['logged']))
	{
		$update = array(
			'last_visit'	=>	$panther_user['logged'],
		);

		$data = array(
			':id'	=>	$panther_user['id'],
		);
		
		$db->update('users', $update, 'id=:id', $data);
	}

	panther_setcookie(1, panther_hash(uniqid(rand(), true)), time() + 31536000);
	redirect(panther_link($panther_url['index']), $lang_login['Logout redirect']);
}
else if ($action == 'forget')
{
	if (!$panther_user['is_guest'])
	{
		header('Location: '.panther_link($panther_url['index']));
		exit;
	}

	if (isset($_POST['form_sent']))
	{
		confirm_referrer('login.php');
		($hook = get_extensions('forget_password_before_validation')) ? eval($hook) : null;

		// Start with a clean slate
		$errors = array();

		require PANTHER_ROOT.'include/email.php';

		// Validate the email address
		$email = isset($_POST['req_email']) ? strtolower(panther_trim($_POST['req_email'])) : '';
		if (!$mailer->is_valid_email($email))
			$errors[] = $lang_common['Invalid email'];

		($hook = get_extensions('forget_password_after_validation')) ? eval($hook) : null;

		// Did everything go according to plan?
		if (empty($errors))
		{
			$data = array(
				':email'	=>	$email,
			);

			$ps = $db->select('users', 'id, username, last_email_sent', $data, 'email=:email');
			if ($ps->rowCount())
			{
				// Loop through users we found
				foreach ($ps as $cur_hit)
				{
					if ($cur_hit['last_email_sent'] != '' && (time() - $cur_hit['last_email_sent']) < 3600 && (time() - $cur_hit['last_email_sent']) >= 0)
						message(sprintf($lang_login['Email flood'], intval((3600 - (time() - $cur_hit['last_email_sent'])) / 60)), true);

					// Generate a new password and a new password activation code
					$new_password = random_pass(12);
					$new_salt = random_pass(16);
					$new_password_key = random_pass(8);
					
					$update = array(
						'activate_string'	=>	panther_hash($new_password.$new_salt),
						'salt'	=>	$new_salt,
						'activate_key'	=>	$new_password_key,
						'last_email_sent'	=>	time(),
					);
					
					$data = array(
						':id'	=>	$cur_hit['id'],
					);

					$db->update('users', $update, 'id=:id', $data);

					$info = array(
						'message' => array(
							'<base_url>' => get_base_url(),
							'<username>' => $cur_hit['username'],
							'<activation_url>' => panther_link($panther_url['change_password_key'], array($cur_hit['id'], $new_password_key)),
							'<new_password>' => $new_password,
						)
					);

					$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/activate_password.tpl', $info);
					$mailer->send($email, $mail_tpl['subject'], $mail_tpl['message']);
				}

				message($lang_login['Forget mail'].' '.$panther_config['o_admin_email'], true);
			}
			else
				$errors[] = $lang_login['No email match'].' '.$email.'.';
			}
		}

	$page_title = array($panther_config['o_board_title'], $lang_login['Request pass']);
	$required_fields = array('req_email' => $lang_common['Email']);
	$focus_element = array('request_pass', 'req_email');

	($hook = get_extensions('forgot_password_before_header')) ? eval($hook) : null;

	define ('PANTHER_ACTIVE_PAGE', 'login');
	require PANTHER_ROOT.'header.php';

	$tpl = load_template('forgot_password.tpl');
	echo $tpl->render(
		array (
			'lang_login' => $lang_login,
			'form_url' => panther_link($panther_url['request_password']),
			'csrf_token' => generate_csrf_token(),
			'lang_common' => $lang_common,
			'errors' => $errors
		)
	);

	require PANTHER_ROOT.'footer.php';
}

if (!$panther_user['is_guest'])
{
	header('Location: '.panther_link($panther_url['index']));
	exit;
}

// Try to determine if the data in HTTP_REFERER is valid (if not, we redirect to index.php after login)
if (!empty($_SERVER['HTTP_REFERER']))
	$redirect_url = validate_redirect($_SERVER['HTTP_REFERER'], null);

if (!isset($redirect_url))
	$redirect_url = panther_link($panther_url['index']);
else if (preg_match('%viewtopic\.php\?pid=(\d+)$%', $redirect_url, $matches))
	$redirect_url .= '#p'.$matches[1];

$page_title = array($panther_config['o_board_title'], $lang_common['Login']);
$required_fields = array('req_username' => $lang_common['Username'], 'req_password' => $lang_common['Password']);
$focus_element = array('login', 'req_username');

($hook = get_extensions('login_before_header')) ? eval($hook) : null;

define('PANTHER_ACTIVE_PAGE', 'login');
require PANTHER_ROOT.'header.php';

$tpl = load_template('login.tpl');
echo $tpl->render(
	array (
		'lang_login' => $lang_login,
		'lang_common' => $lang_common,
		'form_action' => panther_link($panther_url['login_in']),
		'redirect_url' => $redirect_url,
		'register' => panther_link($panther_url['register']),
		'request_password' => panther_link($panther_url['request_password']),
		'errors' => $errors,
	)
);

require PANTHER_ROOT.'footer.php';