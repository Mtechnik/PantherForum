<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

if (!defined('PANTHER'))
{
	define('PANTHER_ROOT', __DIR__.'/');
	require PANTHER_ROOT.'include/common.php';
}

if ($panther_user['is_bot'])
	message($lang_common['No permission']);

// If we are logged in, we shouldn't be here
if (!$panther_user['is_guest'])
{
	header('Location: '.panther_link($panther_url['index']));
	exit;
}

// Load the register.php language file
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/register.php';

// Load the register.php/profile.php language file
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/prof_reg.php';

if (file_exists(FORUM_CACHE_DIR.'cache_robots.php'))
	include FORUM_CACHE_DIR.'cache_robots.php';

if (!defined('PANTHER_ROBOTS_LOADED'))
{
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_robots_cache();
	require FORUM_CACHE_DIR.'cache_robots.php';
}

if ($panther_config['o_regs_allow'] == '0')
	message($lang_register['No new regs']);

// User pressed the cancel button
if (isset($_GET['cancel']))
	redirect(panther_link($panther_url['index']), $lang_register['Reg cancel redirect']);

else if ($panther_config['o_rules'] == '1' && !isset($_GET['agree']) && !isset($_POST['form_sent']))
{
	$page_title = array($panther_config['o_board_title'], $lang_register['Register'], $lang_register['Forum rules']);
	define('PANTHER_ACTIVE_PAGE', 'register');
	require PANTHER_ROOT.'header.php';

	$tpl = load_template('register_rules.tpl');
	echo $tpl->render(
		array(
			'lang_register' => $lang_register,
			'panther_config' => $panther_config,
			'form_action' => panther_link($panther_url['register']),
		)
	);

	require PANTHER_ROOT.'footer.php';
}

// Start with a clean slate
$errors = array();
if (isset($_POST['form_sent']))
{
	($hook = get_extensions('register_before_validation')) ? eval($hook) : null;
	confirm_referrer('register.php');

	// Check that someone from this IP didn't register a user within the last two hours (DoS prevention)
	$data = array(
		':remote_address'	=>	get_remote_address(),
		':registered'		=>	(time() - 7200),
	);

	$ps = $db->select('users', 1, $data, 'registration_ip=:remote_address AND registered>:registered');
	if ($ps->rowCount())
		$errors[] = $lang_register['Registration flood'];
	
	if (!empty($panther_robots))
	{
		$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		$answer = isset($_POST['answer']) ? panther_trim($_POST['answer']) : '';

		if (!isset($panther_robots[$id]) || $answer != $panther_robots[$id]['answer'])
			$errors[] = $lang_common['Robot test fail'];
	}

	$username = isset($_POST['req_user']) ? panther_trim($_POST['req_user']) : '';
	$email1 = isset($_POST['req_email1']) ? strtolower(panther_trim($_POST['req_email1'])) : '';
	$password_salt = random_pass(16);
	
	if ($panther_config['o_regs_verify'] == '1')
	{
		$email2 = isset($_POST['req_email2']) ? strtolower(panther_trim($_POST['req_email2'])) : '';

		$password1 = random_pass(12);
		$password2 = $password1;
	}
	else
	{
		$password1 = isset($_POST['req_password1']) ? panther_trim($_POST['req_password1']) : '';
		$password2 = isset($_POST['req_password2']) ? panther_trim($_POST['req_password2']) : '';
	}

	// Validate username and passwords
	check_username($username);

	if (panther_strlen($password1) < 6)
		$errors[] = $lang_prof_reg['Pass too short'];
	else if ($password1 != $password2)
		$errors[] = $lang_prof_reg['Pass not match'];

	// Validate email
	require PANTHER_ROOT.'include/email.php';

	if (!$mailer->is_valid_email($email1))
		$errors[] = $lang_common['Invalid email'];
	else if ($panther_config['o_regs_verify'] == '1' && $email1 != $email2)
		$errors[] = $lang_register['Email not match'];

	// Check if it's a banned email address
	if ($mailer->is_banned_email($email1))
	{
		if ($panther_config['p_allow_banned_email'] == '0')
			$errors[] = $lang_prof_reg['Banned email'];

		$banned_email = true; // Used later when we send an alert email
	}
	else
		$banned_email = false;

	// Check if someone else already has registered with that email address
	$dupe_list = array();
	$data = array(
		':email'	=>	$email1
	);
	$ps = $db->select('users', 'username', $data, 'email=:email');
	if ($ps->rowCount())
	{
		if ($panther_config['p_allow_dupe_email'] == '0')
			$errors[] = $lang_prof_reg['Dupe email'];

		$ps->setFetchMode(PDO::FETCH_COLUMN, 0);
		foreach ($ps as $cur_dupe)
			$dupe_list[] = $cur_dupe;
	}

	// Make sure we have a valid language string
	if (isset($_POST['language']))
	{
		$language = preg_replace('%[\.\\\/]%', '', $_POST['language']);
		if (!file_exists(PANTHER_ROOT.'lang/'.$language.'/common.php'))
			message($lang_common['Bad request'], false, '404 Not Found');
	}
	else
		$language = $panther_config['o_default_lang'];

	$timezone = isset($_POST['timezone']) ? round($_POST['timezone'], 1) : '';
	$dst = isset($_POST['dst']) ? 1 : 0;

	$email_setting = isset($_POST['email_setting']) && ($_POST['email_setting'] > 0 && $_POST['email_setting'] < 2) ? intval($_POST['email_setting']) : $panther_config['o_default_email_setting'];

	($hook = get_extensions('register_after_validation')) ? eval($hook) : null;
	$url_username = url_friendly($username);

	// Did everything go according to plan?
	if (empty($errors))
	{
		// Insert the new user into the database. We do this now to get the last inserted ID for later use
		$now = time();

		$initial_group_id = ($panther_config['o_regs_verify'] == '0') ? $panther_config['o_default_user_group'] : PANTHER_UNVERIFIED;
		$password_hash = panther_hash($password1.$password_salt);

		// Add the user
		$insert = array(
			'username'	=>	$username,
			'group_id'	=>	$initial_group_id,
			'password'	=>	$password_hash,
			'salt'		=>	$password_salt,
			'email'		=>	$email1,
			'email_setting'	=>	$email_setting,
			'timezone'	=>	$timezone,
			'dst'		=>	$dst,
			'language'	=>	$language,
			'style'		=>	$panther_config['o_default_style'],
			'registered'	=>	$now,
			'registration_ip'	=>	get_remote_address(),
			'last_visit'	=>	$now,
		);

		$db->insert('users', $insert);
		$new_uid = $db->lastInsertId($db->prefix.'users');
		$login_key = generate_login_key($new_uid);

		if ($panther_config['o_regs_verify'] == '0')
		{
			// Regenerate the users info cache
			if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
				require PANTHER_ROOT.'include/cache.php';

			generate_users_info_cache();
		}

		// If the mailing list isn't empty, we may need to send out some alerts
		if ($panther_config['o_mailing_list'] != '')
		{
			// If we previously found out that the email was banned
			if ($banned_email)
			{
				$info = array(
					'message' => array(
						'<username>' => $username,
						'<email>' => $email1,
						'<profile_url>' => panther_link($panther_url['profile'], array($new_uid, $url_username)),
					)
				);
				
				$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/banned_email_register.tpl', $info);
				$mailer->send($panther_config['o_mailing_list'], $mail_tpl['subject'], $mail_tpl['message']);
			}

			// If we previously found out that the email was a dupe
			if (!empty($dupe_list))
			{
				$info = array(
					'message' => array(
						'<username>' => $username,
						'<dupe_list>' => implode(', ', $dupe_list),
						'<profile_url>' => panther_link($panther_url['profile'], array($new_uid, $url_username)),
					),
				);

				$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/dupe_email_register.tpl', $info);
				$mailer->send($panther_config['o_mailing_list'], $mail_tpl['subject'], $mail_tpl['message']);
			}

			// Should we alert people on the admin mailing list that a new user has registered?
			if ($panther_config['o_regs_report'] == '1')
			{
				$info = array(
					'message' => array(
						'<username>' => $username,
						'<base_url>' => get_base_url(),
						'<profile_url>' => panther_link($panther_url['profile'], array($new_uid, $url_username)),
						'<admin_url>' => panther_link($panther_url['profile_admin'], array($new_uid)),
					),
				);

				$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/new_user.tpl', $info);
				$mailer->send($panther_config['o_mailing_list'], $mail_tpl['subject'], $mail_tpl['message']);
			}
		}

		// Must the user verify the registration or do we log him/her in right now?
		if ($panther_config['o_regs_verify'] == '1')
		{
			$info = array(
				'subject' => array(
					'<board_title>' => $panther_config['o_board_title'],
				),
				'message' => array(
					'<base_url>' => get_base_url(),
					'<username>' => $username,
					'<password>' => $password1,
					'<login_url>' => panther_link($panther_url['login']),
				)
			);

			$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/welcome.tpl', $info);
			$mailer->send($email1, $mail_tpl['subject'], $mail_tpl['message']);
			message(sprintf($lang_register['Reg email'], $panther_config['o_admin_email']), true);
		}

		panther_setcookie($new_uid, $login_key, time() + $panther_config['o_timeout_visit']);
		redirect(panther_link($panther_url['index']), $lang_register['Reg complete']);
	}
}

$page_title = array($panther_config['o_board_title'], $lang_register['Register']);
$required_fields = array('req_user' => $lang_common['Username'], 'req_password1' => $lang_common['Password'], 'req_password2' => $lang_prof_reg['Confirm pass'], 'req_email1' => $lang_common['Email'], 'req_email2' => $lang_common['Email'].' 2');
$focus_element = array('register', 'req_user');

if (!empty($panther_robots))
	$required_fields['answer'] = $lang_common['Robot title'];

($hook = get_extensions('register_before_header')) ? eval($hook) : null;

define('PANTHER_ACTIVE_PAGE', 'register');
require PANTHER_ROOT.'header.php';

$timezone = isset($timezone) ? $timezone : $panther_config['o_default_timezone'];
$dst = isset($dst) ? $dst : $panther_config['o_default_dst'];
$email_setting = isset($email_setting) ? $email_setting : $panther_config['o_default_email_setting'];

($hook = get_extensions('register_before_submit')) ? eval($hook) : null;

$render = array(
	'lang_register' => $lang_register,
	'errors' => $errors,
	'form_action' => panther_link($panther_url['register_register']),
	'csrf_token' => generate_csrf_token(),
	'lang_common' => $lang_common,
	'lang_prof_reg' => $lang_prof_reg,
	'POST' => $_POST,
	'panther_config' => $panther_config,
	'dst' => $dst,
	'timezone' => $timezone,
	'email_setting' => $email_setting,
	'languages' => forum_list_langs(),
);

if (!empty($panther_robots))
{
	$id = array_rand($panther_robots);
	$test = $panther_robots[$id];
	
	$render['robot_id'] = $id;
	$render['robot_test'] = $test;
}

$tpl = load_template('register.tpl');
echo $tpl->render($render);

($hook = get_extensions('register_after_output')) ? eval($hook) : null;

require PANTHER_ROOT.'footer.php';