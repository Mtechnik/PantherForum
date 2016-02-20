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

// Include UTF-8 function
require PANTHER_ROOT.'include/utf8/substr_replace.php';
require PANTHER_ROOT.'include/utf8/ucwords.php'; // utf8_ucwords needs utf8_substr_replace
require PANTHER_ROOT.'include/utf8/strcasecmp.php';

$action = isset($_GET['action']) ? $_GET['action'] : null;
$section = isset($_GET['section']) ? $_GET['section'] : null;
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id < 2)
	message($lang_common['Bad request'], false, '404 Not Found');

if ($action != 'change_pass' || !isset($_GET['key']))
{
	if ($panther_user['g_read_board'] == '0')
		message($lang_common['No view'], false, '403 Forbidden');
	else if ($panther_user['g_view_users'] == '0' && ($panther_user['is_guest'] || $panther_user['id'] != $id))
		message($lang_common['No permission'], false, '403 Forbidden');
}

// Load the prof_reg.php language file
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/prof_reg.php';

// Load the profile.php language file
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/profile.php';

if ($action == 'change_pass')
{
	if (isset($_GET['key']))
	{
		// If the user is already logged in we shouldn't be here :)
		if (!$panther_user['is_guest'])
		{
			header('Location: '.panther_link($panther_url['index']));
			exit;
		}

		$key = $_GET['key'];

		$data = array(
			':id'	=>	$id,
		);

		$ps = $db->select('users', 'activate_string, activate_key, salt', $data, 'id=:id');
		$cur_user = $ps->fetch();

		if ($key == '' || $key != $cur_user['activate_key'])
			message($lang_profile['Pass key bad'].' '.$panther_config['o_admin_email']);
		else
		{
			$data = array(
				':password'	=>	$cur_user['activate_string'],
				':id'	=>	$id,
			);

			$db->run('UPDATE '.$db->prefix.'users SET password=:password, activate_string=NULL, activate_key=NULL WHERE id=:id', $data);
			message($lang_profile['Pass updated'], true);
		}
	}

	// Make sure we are allowed to change this user's password
	if ($panther_user['id'] != $id)
	{
		if (!$panther_user['is_admmod'])
			message($lang_common['No permission'], false, '403 Forbidden');
		else if ($panther_user['g_moderator'] == '1') // A moderator trying to change a user's password?
		{
			$ps = $db->select('SELECT u.group_id, g.g_moderator, g.g_admin FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON (g.g_id=u.group_id) WHERE u.id=:id', array(':id'=>$id));
			if (!$ps->rowCount())
				message($lang_common['Bad request'], false, '404 Not Found');

			list($group_id, $is_moderator) = $ps->fetch(PDO::FETCH_NUM);

			if ($panther_user['g_mod_edit_users'] == '0' || $panther_user['g_mod_change_passwords'] == '0' || $group_id == PANTHER_ADMIN || $is_admin == '1' || $is_moderator == '1')
				message($lang_common['No permission'], false, '403 Forbidden');
		}
	}

	if (isset($_POST['form_sent']))
	{
		// Make sure they got here from the site
		confirm_referrer('profile.php');

		$old_password = isset($_POST['req_old_password']) ? panther_trim($_POST['req_old_password']) : '';
		$new_password1 = isset($_POST['req_new_password1']) ? panther_trim($_POST['req_new_password1']) : '';
		$new_password2 = isset($_POST['req_new_password2']) ? panther_trim($_POST['req_new_password2']) : '';

		if ($new_password1 != $new_password2)
			message($lang_prof_reg['Pass not match']);
		if (panther_strlen($new_password1) < 6)
			message($lang_prof_reg['Pass too short']);
		
		$data = array(
			':id'	=>	$id,
		);

		$ps = $db->select('users', 'password, salt', $data, 'id=:id');
		$cur_user = $ps->fetch();

		$authorized = false;
		if (!empty($cur_user['password']))
		{
			$old_password_hash = panther_hash($old_password.$cur_user['salt']);
			if ($cur_user['password'] == $old_password_hash || $panther_user['is_admmod'])
				$authorized = true;
		}

		if (!$authorized)
			message($lang_profile['Wrong pass']);

		$new_salt = random_pass(16);
		$new_password_hash = panther_hash($new_password1.$new_salt);

		$update = array(
			'password'	=>	$new_password_hash,
			'salt'		=>	$new_salt,
		);
		
		$data = array(
			':id'	=>	$id,
		);
		
		$db->update('users', $update, 'id=:id', $data);

		if ($panther_user['id'] == $id)
			panther_setcookie($panther_user['id'], $new_password_hash, time() + $panther_config['o_timeout_visit']);

		redirect(panther_link($panther_url['profile_essentials'], array($id)), $lang_profile['Pass updated redirect']);
	}

	$page_title = array($panther_config['o_board_title'], $lang_common['Profile'], $lang_profile['Change pass']);
	$required_fields = array('req_old_password' => $lang_profile['Old pass'], 'req_new_password1' => $lang_profile['New pass'], 'req_new_password2' => $lang_profile['Confirm new pass']);
	$focus_element = array('change_pass', ((!$panther_user['is_admmod']) ? 'req_old_password' : 'req_new_password1'));
	define('PANTHER_ACTIVE_PAGE', 'profile');
	require PANTHER_ROOT.'header.php';
	
	$tpl = load_template('change_password.tpl');
	echo $tpl->render(
		array(
			'lang_profile' => $lang_profile,
			'lang_common' => $lang_common,
			'csrf_token' => generate_csrf_token(),
			'form_action' => panther_link($panther_url['change_password'], array($id)),
			'panther_user' => $panther_user,
		)
	);

	require PANTHER_ROOT.'footer.php';
}
else if ($action == 'change_email')
{
	// Make sure we are allowed to change this user's email
	if ($panther_user['id'] != $id)
	{
		if (!$panther_user['is_admmod']) // A regular user trying to change another user's email?
			message($lang_common['No permission'], false, '403 Forbidden');
		else if ($panther_user['g_moderator'] == '1') // A moderator trying to change a user's email?
		{
		    $data = array(
		        ':id' => $id,
            );

			$ps = $db->run('SELECT u.group_id, g.g_moderator, g.g_admin FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON (g.g_id=u.group_id) WHERE u.id=:id', $data);
			if (!$ps->rowCount())
				message($lang_common['Bad request'], false, '404 Not Found');

			list($group_id, $is_moderator, $is_admin) = $ps->fetch(PDO::FETCH_NUM);

			if ($panther_user['g_mod_edit_users'] == '0' || $group_id == PANTHER_ADMIN || $is_admin == '1' || $is_moderator == '1')
				message($lang_common['No permission'], false, '403 Forbidden');
		}
	}

	if (isset($_GET['key']))
	{
		$key = $_GET['key'];
		$update = array(
			':id'	=>	$id,
		);
		
		$ps = $db->select('users', 'activate_string, activate_key', $update, 'id=:id');
		list($new_email, $new_email_key) = $ps->fetch(PDO::FETCH_NUM);

		if ($key == '' || $key != $new_email_key)
			message(sprintf($lang_profile['Email key bad'], $panther_config['o_admin_email']));
		else
		{
			$data = array(
				':id'	=>	$id,
			);

			$db->run('UPDATE '.$db->prefix.'users SET email=activate_string, activate_string=NULL, activate_key=NULL WHERE id=:id', $data);
			message($lang_profile['Email updated'], true);
		}
	}
	else if (isset($_POST['form_sent']))
	{
		confirm_referrer('profile.php');

		if (panther_hash($_POST['req_password'].$panther_user['salt']) !== $panther_user['password'])
			message($lang_profile['Wrong pass']);

		require PANTHER_ROOT.'include/email.php';

		// Validate the email address
		$new_email = isset($_POST['req_new_email']) ? strtolower(panther_trim($_POST['req_new_email'])) : '';
		if (!$mailer->is_valid_email($new_email))
			message($lang_common['Invalid email']);

		// Check if it's a banned email address
		if ($mailer->is_banned_email($new_email))
		{
			if ($panther_config['p_allow_banned_email'] == '0')
				message($lang_prof_reg['Banned email']);
			else if ($panther_config['o_mailing_list'] != '')
			{
				$info = array(
					'message' => array(
						'<username>' => $panther_user['username'],
						'<email>' => $new_email,
						'<profile_url>' => panther_link($panther_url['profile_essentials'], array($id)),
					)
				);

				$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/banned_email_change.tpl', $info);
				$mailer->send($panther_config['o_mailing_list'], $mail_tpl['subject'], $mail_tpl['message']);
			}
		}

		// Check if someone else already has registered with that email address
		$data = array(
			':email' => $new_email,
		);

		$ps = $db->select('users', 'id, username', $data, 'email=:email');
		if ($ps->rowCount())
		{
			if ($panther_config['p_allow_dupe_email'] == '0')
				message($lang_prof_reg['Dupe email']);
			else if ($panther_config['o_mailing_list'] != '')
			{
				$ps->setFetchMode(PDO::FETCH_COLUMN, 0);
				foreach ($ps as $cur_dupe)
					$dupe_list[] = $cur_dupe;

				$info = array(
					'message' => array(
						'<username>' => $panther_user['username'],
						'<dupe_list>' => implode(', ', $dupe_list),
						'<profile_url>' => panther_link($panther_url['profile_essentials'], array($id)),
					)
				);

				$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/dupe_email_change.tpl', $info);
				$mailer->send($panther_config['o_mailing_list'], $mail_tpl['subject'], $mail_tpl['message']);
			}
		}

		$new_email_key = random_pass(8);
		$update = array(
			'activate_string' => $new_email,
			'activate_key' => $new_email_key,
		);
		
		$data = array(
			':id'	=>	$id,
		);
		
		$db->update('users', $update, 'id=:id', $data);
		
		$info = array(
			'message' => array(
				'<username>' => $panther_user['username'],
				'<base_url>' => get_base_url(),
				'<activation_url>' => panther_link($panther_url['change_email_key'], array($id, $new_email_key)),
			)
		);

		$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/activate_email.tpl', $info);
		$mailer->send($new_email, $mail_tpl['subject'], $mail_tpl['message']);
		message($lang_profile['Activate email sent'].' '.$panther_config['o_admin_email'], true);
	}

	$page_title = array($panther_config['o_board_title'], $lang_common['Profile'], $lang_profile['Change email']);
	$required_fields = array('req_new_email' => $lang_profile['New email'], 'req_password' => $lang_common['Password']);
	$focus_element = array('change_email', 'req_new_email');
	define('PANTHER_ACTIVE_PAGE', 'profile');
	require PANTHER_ROOT.'header.php';
	
	$tpl = load_template('change_email.tpl');
	echo $tpl->render(
		array(
			'lang_profile' => $lang_profile,
			'lang_common' => $lang_common,
			'form_action' => panther_link($panther_url['change_email'], array($id)),
			'csrf_token' => generate_csrf_token(),
		)
	);

	require PANTHER_ROOT.'footer.php';
}
else if ($action == 'use_gravatar')
{
	if ($panther_config['o_avatars'] == '0')
		message($lang_profile['Avatars disabled']);

	if ($panther_user['id'] != $id && !$panther_user['is_admmod'])
		message($lang_common['No permission']);

	confirm_referrer('profile.php');
	$data = array(
		':id'	=>	$id,
	);

	$ps = $db->select('users', 'use_gravatar', $data, 'id=:id');
	$use_gravatar = $ps->fetchColumn();
	
	if (!$use_gravatar)
		delete_avatar($id);

	$redirect_msg = ($use_gravatar) ? $lang_profile['Gravatar disabled redirect'] : $lang_profile['Gravatar enabled redirect'];
	$update = array(
		'use_gravatar'	=>	(($use_gravatar == 0) ? 1 : 0)
	);

	$db->update('users', $update, 'id=:id', $data);
	redirect(panther_link($panther_url['profile_personality'], array($id)), $redirect_msg);
}
else if ($action == 'upload_avatar')
{
	if ($panther_config['o_avatars'] == '0')
		message($lang_profile['Avatars disabled']);
	
	if ($panther_config['o_avatar_upload'] == '0')
		message($lang_profile['Avatars disabled']);

	if ($panther_user['id'] != $id && !$panther_user['is_admmod'])
		message($lang_common['No permission'], false, '403 Forbidden');

	if (isset($_POST['form_sent']))
	{
		if (!isset($_FILES['req_file']))
			message($lang_profile['No file']);
		
		$avatar_path = ($panther_config['o_avatars_dir'] != '') ? $panther_config['o_avatars_path'] : PANTHER_ROOT.$panther_config['o_avatars_path'].'/';
			
		// Make sure they got here from the site
		confirm_referrer('profile.php');

		$uploaded_file = $_FILES['req_file'];

		// Make sure the upload went smooth
		if (isset($uploaded_file['error']))
		{
			switch ($uploaded_file['error'])
			{
				case 1: // UPLOAD_ERR_INI_SIZE
				case 2: // UPLOAD_ERR_FORM_SIZE
					message($lang_profile['Too large ini']);
					break;

				case 3: // UPLOAD_ERR_PARTIAL
					message($lang_profile['Partial upload']);
					break;

				case 4: // UPLOAD_ERR_NO_FILE
					message($lang_profile['No file']);
					break;

				case 6: // UPLOAD_ERR_NO_TMP_DIR
					message($lang_profile['No tmp directory']);
					break;

				default:
					// No error occured, but was something actually uploaded?
					if ($uploaded_file['size'] == 0)
						message($lang_profile['No file']);
					break;
			}
		}

		if (is_uploaded_file($uploaded_file['tmp_name']))
		{
			// Preliminary file check, adequate in most cases
			$allowed_types = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png');
			if (!in_array($uploaded_file['type'], $allowed_types))
				message($lang_profile['Bad type']);

			// Make sure the file isn't too big
			if ($uploaded_file['size'] > $panther_config['o_avatars_size'])
				message($lang_profile['Too large'].' '.forum_number_format($panther_config['o_avatars_size']).' '.$lang_profile['bytes'].'.');

			// Move the file to the avatar directory. We do this before checking the width/height to circumvent open_basedir restrictions
			if (!@move_uploaded_file($uploaded_file['tmp_name'], $avatar_path.$id.'.tmp'))
				message($lang_profile['Move failed'].' '.$panther_config['o_admin_email']);

			list($width, $height, $type,) = @getimagesize($avatar_path.$id.'.tmp');

			// Determine type
			if ($type == IMAGETYPE_GIF)
				$extension = '.gif';
			else if ($type == IMAGETYPE_JPEG)
				$extension = '.jpg';
			else if ($type == IMAGETYPE_PNG)
				$extension = '.png';
			else
			{
				// Invalid type
				@unlink($avatar_path.$id.'.tmp');
				message($lang_profile['Bad type']);
			}

			// Now check the width/height
			if (empty($width) || empty($height) || $width > $panther_config['o_avatars_width'] || $height > $panther_config['o_avatars_height'])
			{
				@unlink($avatar_path.$id.'.tmp');
				message($lang_profile['Too wide or high'].' '.$panther_config['o_avatars_width'].'x'.$panther_config['o_avatars_height'].' '.$lang_profile['pixels'].'.');
			}

			// Delete any old avatars and put the new one in place
			delete_avatar($id);
			@rename($avatar_path.$id.'.tmp', $avatar_path.$id.$extension);
			compress_image($avatar_path.$id.$extension);
			@chmod($avatar_path.$id.$extension, 0644);

			// Disable Gravatar
			$update = array(
				'use_gravatar'	=>	0,
			);
			
			$data = array(
			':id'	=>	$id,
			);

			$db->update('users', $update, 'id=:id', $data);
		}
		else
			message($lang_profile['Unknown failure']);

		redirect(panther_link($panther_url['profile_personality'], array($id)), $lang_profile['Avatar upload redirect']);
	}

	$page_title = array($panther_config['o_board_title'], $lang_common['Profile'], $lang_profile['Upload avatar']);
	$required_fields = array('req_file' => $lang_profile['File']);
	$focus_element = array('upload_avatar', 'req_file');
	define('PANTHER_ACTIVE_PAGE', 'profile');
	require PANTHER_ROOT.'header.php';
	
	$csrf_token = generate_csrf_token();
	$tpl = load_template('upload_avatar.tpl');
	echo $tpl->render(
		array(
			'lang_profile' => $lang_profile,
			'lang_common' => $lang_common,
			'form_action' => panther_link($panther_url['upload_avatar'], array($id, $csrf_token)),
			'csrf_token' => $csrf_token,
			'panther_config' => $panther_config,
			'avatar_size' => forum_number_format($panther_config['o_avatars_size']),
			'file_size' => file_size($panther_config['o_avatars_size']),
		)
	);

	require PANTHER_ROOT.'footer.php';
}
else if ($action == 'delete_avatar')
{
	confirm_referrer('profile.php');

	if ($panther_user['id'] != $id && !$panther_user['is_admmod'])
		message($lang_common['No permission'], false, '403 Forbidden');

	delete_avatar($id);
	redirect(panther_link($panther_url['profile_personality'], array($id)), $lang_profile['Avatar deleted redirect']);
}

else if (isset($_POST['update_group_membership']))
{
	confirm_referrer('profile.php');

	if (!$panther_user['is_admin'])
		message($lang_common['No permission'], false, '403 Forbidden');

	$new_group_id = intval($_POST['group_id']);
	$select = array(
		':id'	=>	$id,
	);

	$ps = $db->select('users', 'group_id', $select, 'id=:id');
	$old_group_id = $ps->fetchColumn();
	
	$update = array(
		'group_id'	=>	$new_group_id,
	);
	
	$data = array(
		':id'	=>	$id,
	);

	$db->update('users', $update, 'id=:id', $data);

	// Regenerate the users info cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_users_info_cache();

    if ($old_group_id !=0 && ($old_group_id == PANTHER_ADMIN || $new_group_id == PANTHER_ADMIN || $panther_groups[$old_group_id]['g_admin'] == '1' || $panther_groups[$new_group_id]['g_admin'] == '1'))
		generate_admins_cache();
	
	$data = array(
		':id'	=>	$new_group_id
	);

	$ps = $db->select('groups', 'g_moderator', $data, 'g_id=:id');
	$new_group_mod = $ps->fetchColumn();

	// If the user was a moderator or an administrator, we remove him/her from the moderator list in all forums as well
	if ($new_group_id != PANTHER_ADMIN && $new_group_mod != '1')
	{
		$ps = $db->select('forums', 'id, moderators');
		foreach ($ps as $cur_forum)
		{
			$cur_moderators = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();
			if (in_array($id, $cur_moderators))
			{
				$username = array_search($id, $cur_moderators);
				unset($cur_moderators[$username]);
				unset($cur_moderators['groups'][$id]);
				if (empty($cur_moderators['groups']))
					unset($cur_moderators['groups']);
				$cur_moderators = (!empty($cur_moderators)) ? serialize($cur_moderators) : NULL;
				$update = array(
					'moderators' => $cur_moderators,
				);
				
				$data = array(
					':id' => $cur_forum['id'],
				);

				$db->update('forums', $update, 'id=:id', $data);
			}
		}
	}
	else	// Else update moderator's group_id
	{
		$ps = $db->select('forums', 'id, moderators');
		foreach ($ps as $cur_forum)
		{
			$cur_moderators = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();
			if (in_array($id, $cur_moderators))
			{
				$cur_moderators['groups'][$id] = $new_group_id;
				$update = array(
					'moderators' => serialize($cur_moderators),
				);
				
				$data = array(
					':id' => $cur_forum['id'],
				);
				
				$db->update('forums', $update, 'id=:id', $data);
			}
		}
	}
	redirect(panther_link($panther_url['profile_admin'], array($id)), $lang_profile['Group membership redirect']);
}
else if (isset($_POST['update_forums']))
{
	confirm_referrer('profile.php');

	if (!$panther_user['is_admin'])
		message($lang_common['No permission'], false, '403 Forbidden');

	// Get the username of the user we are processing
	$data = array(
		':id'	=>	$id,
	);

	$ps = $db->select('users', 'username, group_id', $data, 'id=:id');
	list($username, $group_id) = $ps->fetch(PDO::FETCH_NUM);
	$moderator_in = (isset($_POST['moderator_in'])) ? array_keys($_POST['moderator_in']) : array();

	// Loop through all forums
	$ps = $db->select('forums', 'id, moderators');
	foreach ($ps as $cur_forum)
	{
		$cur_moderators = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();

		if (!isset($cur_moderators['groups']))
			$cur_moderators['groups'] = array();

		$cur_moderators['groups'][$id] = $group_id;

		// If the user should have moderator access (and he/she doesn't already have it)
		if (in_array($cur_forum['id'], $moderator_in) && !in_array($id, $cur_moderators))
		{
			$cur_moderators[$username] = $id;
			uksort($cur_moderators, 'utf8_strcasecmp');

			$update = array(
				'moderators' => serialize($cur_moderators),
			);
			
			$data = array(
				':id' => $cur_forum['id'],
			);

			$db->update('forums', $update, 'id=:id', $data);
		}
		// If the user shouldn't have moderator access (and he/she already has it)
		else if (!in_array($cur_forum['id'], $moderator_in) && in_array($id, $cur_moderators))
		{
			unset($cur_moderators[$username]);
			unset($cur_moderators['groups'][$id]);
			if (empty($cur_moderators['groups']))
					unset($cur_moderators['groups']);

			$cur_moderators = (!empty($cur_moderators)) ? serialize($cur_moderators) : NULL;
			$update = array(
				'moderators' => $cur_moderators,
			);
			
			$data = array(
				':id' => $cur_forum['id'],
			);
			
			$db->update('forums', $update, 'id=:id', $data);
		}
		elseif (in_array($cur_forum['id'], $moderator_in) || in_array($id, $cur_moderators))
		{
			$update = array(
				'moderators' => serialize($cur_moderators),
			);
			
			$data = array(
				':id'	=>	$cur_forum['id'],
			);
			
			$db->update('forums', $update, 'id=:id', $data);
		}
	}
	
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';
	
	generate_forums_cache();
	redirect(panther_link($panther_url['profile_admin'], array($id)), $lang_profile['Update forums redirect']);
}
else if (isset($_POST['update_posting_ban']))
{
	confirm_referrer('profile.php');

	if (!$panther_user['is_admin'])
		message($lang_common['No permission']);

	$data = array(
		':id'	=>	$id,
	);

	$ps = $db->select('users', 'username, group_id', $data, 'id=:id');
	$cur_user = $ps->fetch();

	if ($panther_groups[$cur_user['group_id']]['g_admin'] == '1' || $cur_user['group_id'] == PANTHER_ADMIN)
		message(sprintf($lang_profile['posting ban admin'], $cur_user['username']));

	if ($panther_groups[$cur_user['group_id']]['g_moderator'] == '1')
		message(sprintf($lang_profile['posting ban moderator'], $cur_user['username']));

	$expiration_time = isset($_POST['expiration_time']) ? intval($_POST['expiration_time']) : 0;
	$expiration_unit = isset($_POST['expiration_unit']) ? panther_trim($_POST['expiration_unit']) : $lang_profile['Days'];
	$delete_ban = isset($_POST['remove_ban']) ? '1' : '0';
	$time = ($delete_ban == '1') ? '0' : (time() + get_expiration_time($expiration_time, $expiration_unit)); 

	$update = array(
		'posting_ban' => $time,
	);
	
	$db->update('users', $update, 'id=:id', $data);
	redirect(panther_link($panther_url['profile_admin'], array($id)), $lang_profile['Update posting ban redirect']);
}
else if (isset($_POST['ban']))
{
	if (!$panther_user['is_admin'] && ($panther_user['g_moderator'] != '1' || $panther_user['g_mod_ban_users'] == '0'))
		message($lang_common['No permission'], false, '403 Forbidden');

	// Get the username of the user we are banning
	$data = array(
		':id' => $id,
	);

	$ps = $db->select('users', 'username', $data, 'id=:id');
	$username = $ps->fetchColumn();

	// Check whether user is already banned
	$data = array(
		':username'	=>	$username,
	);
	$ps = $db->select('bans', 'id', $data, 'username=:username', 'expire IS NULL DESC, expire DESC LIMIT 1');
	if ($ps->rowCount())
	{
		$ban_id = $ps->fetchColumn();
		redirect(panther_link($panther_url['edit_ban'], array($ban_id)), $lang_profile['Ban redirect']);
	}
	else
		redirect(panther_link($panther_url['admin_bans_add'], array($id)), $lang_profile['Ban redirect']);
}
else if ($action == 'promote')
{
	confirm_referrer('viewtopic.php');
	if (!$panther_user['is_admin'] && ($panther_user['g_moderator'] != '1' || $panther_user['g_mod_promote_users'] == '0'))
		message($lang_common['No permission']);

	$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
	if ($pid < 1)
		message($lag_common['Bad request']);

	$data = array(
		':id' => $id,
	);

	$ps = $db->run('SELECT g.g_promote_next_group FROM '.$db->prefix.'groups AS g INNER JOIN '.$db->prefix.'users AS u ON u.group_id=g.g_id WHERE u.id=:id AND g.g_promote_next_group>0', $data);

	if (!$ps->rowCount())
		message($lang_common['Bad request'], false, '404 Not Found');

	$update = array(
		'group_id' => $ps->fetchColumn(),
	);
	
	$data = array(
		':id' => $id,
	);

	$db->update('users', $update, 'id=:id', $data);
	redirect(panther_link($panther_url['post'], array($pid)), $lang_profile['User promote redirect']);
}
else if (isset($_POST['delete_user']) || isset($_POST['delete_user_comply']))
{
	confirm_referrer('profile.php');

	if ($panther_user['g_id'] != PANTHER_ADMIN && $panther_user['g_admin'] != '1')
		message($lang_common['No permission'], false, '403 Forbidden');
	
	if (file_exists(FORUM_CACHE_DIR.'cache_restrictions.php'))
		require FORUM_CACHE_DIR.'cache_restrictions.php';
	else
	{
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';

		generate_admin_restrictions_cache();
		require FORUM_CACHE_DIR.'cache_restrictions.php';
	}

	if (!isset($admins[$panther_user['id']]) || $panther_user['id'] == '2')
		$admins[$panther_user['id']] = array('admin_users' => '1');	
	
	if ($admins[$panther_user['id']]['admin_users'] == '0')
		message($lang_common['No permission']);

	// Get the username and group of the user we are deleting
	$data = array(
	':id'	=>	$id,
	);

	$ps = $db->select('users', 'group_id, username', $data, 'id=:id');
	list($group_id, $username) = $ps->fetch(PDO::FETCH_NUM);

	if ($group_id == PANTHER_ADMIN || $panther_groups[$group_id]['g_admin'] == '1')
		message($lang_profile['No delete admin message']);

	if (isset($_POST['delete_user_comply']))
	{
		// If the user is a moderator or an administrator, we remove him/her from the moderator list in all forums as well7
		$data = array(
			':id'	=>	$group_id,
		);
		$ps = $db->select('groups', 'g_moderator', $data, 'g_id=:id');
		$group_mod = $ps->fetchColumn();

		if ($group_id == PANTHER_ADMIN || $group_mod == '1')
		{
			$ps = $db->select('forums', 'id, moderators');
			foreach ($ps as $cur_forum)
			{
				$cur_moderators = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();
				if (in_array($id, $cur_moderators))
				{
					unset($cur_moderators[$username]);
					$cur_moderators = (!empty($cur_moderators)) ? serialize($cur_moderators) : NULL;
					$update = array(
						'moderators'	=>	$cur_moderators,
					);
					
					$data = array(
						':id'	=>	$cur_forum['id'],
					);
					
					$db->update('forums', $update, 'id=:id', $data);
				}
			}
		}
		
		$data = array(
			':id'	=>	$id,
		);

		// Delete any subscriptions
		$db->delete('topic_subscriptions', 'user_id=:id', $data);
		$db->delete('forum_subscriptions', 'user_id=:id', $data);
		
		// Remove any issued warnings
		$db->delete('warnings', 'user_id=:id', $data);

		// Remove them from the online list (if they happen to be logged in)
		$db->delete('online', 'user_id=:id', $data);

		// Should we delete all posts made by this user?
		if (isset($_POST['delete_posts']))
		{
			require PANTHER_ROOT.'include/search_idx.php';
			@set_time_limit(0);

			// Find all posts made by this user
			$ps = $db->run('SELECT p.id, p.topic_id, t.forum_id FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id WHERE p.poster_id=:id', $data);
			if ($ps->rowCount())
			{
				foreach ($ps as $cur_post)
				{
					// Determine whether this post is the "topic post" or not
					$select = array(
						':id'	=>	$cur_post['topic_id'],
					);

					$ps1 = $db->select('posts', 'id', $select, 'topic_id=:id', 'posted LIMIT 1');
					if ($ps1->fetchColumn() == $cur_post['id'])
						delete_topic($cur_post['topic_id']);
					else
						delete_post($cur_post['id'], $cur_post['topic_id']);
					
					$delete = array(
						':id'	=>	$cur_post['id'],
					);
					
					$db->delete('reputation', 'post_id=:id', $delete);

					update_forum($cur_post['forum_id']);
				}
			}
		}
		else	// Set all his/her posts to guest
		{
			$update = array(
				'poster_id'	=>	1,
			);

			$db->update('posts', $update, 'poster_id=:id', $data);
		}

		// Delete user avatar
		delete_avatar($id);

		// Delete the user
		$db->delete('users', 'id=:id', $data);

		// Regenerate the users info cache
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';

		generate_users_info_cache();

		if ($group_id == PANTHER_ADMIN || $panther_groups[$group_id]['g_admin'] == '1')
		{
			generate_admins_cache();
			generate_admin_restrictions_cache();
		}

		redirect(panther_link($panther_url['index']), $lang_profile['User delete redirect']);
	}

	$page_title = array($panther_config['o_board_title'], $lang_common['Profile'], $lang_profile['Confirm delete user']);
	define('PANTHER_ACTIVE_PAGE', 'profile');
	require PANTHER_ROOT.'header.php';
	
	$tpl = load_template('delete_user.tpl');
	echo $tpl->render(
		array(
			'lang_profile' => $lang_profile,
			'lang_common' => $lang_common,
			'form_action' => panther_link($panther_url['profile'], array($id, url_friendly($username))),
			'username' => $username,
			'csrf_token' => generate_csrf_token(),
		)
	);

	require PANTHER_ROOT.'footer.php';
}
else if (isset($_POST['form_sent']))
{
	confirm_referrer('profile.php');

	$data = array(
		':id'	=>	$id,
	);
	
	// Fetch the user group of the user we are editing
	$ps = $db->run('SELECT u.username, u.group_id, g.g_moderator FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON (g.g_id=u.group_id) WHERE u.id=:id', $data);
	if (!$ps->rowCount())
		message($lang_common['Bad request'], false, '404 Not Found');

	list($old_username, $group_id, $is_moderator) = $ps->fetch(PDO::FETCH_NUM);

	if ($panther_user['id'] != $id && !in_array($section, array('rep_received', 'rep_given')) &&																	// If we aren't the user (i.e. editing your own profile) and we aren't viewing what rep they have
		(!$panther_user['is_admmod'] ||																	// and we are not an admin or mod
		(!$panther_user['is_admin'] &&															// or we aren't an admin and ...
		($panther_user['g_mod_edit_users'] == '0' ||													// mods aren't allowed to edit users
		$group_id == PANTHER_ADMIN ||																	// or the user is an admin
		$is_moderator))))																			// or the user is another mod
		message($lang_common['No permission'], false, '403 Forbidden');

	$username_updated = false;

	// Validate input depending on section
	switch ($section)
	{
		case 'essentials':
		{
			$form = array(
				'timezone'		=> floatval($_POST['form']['timezone']),
				'dst'			=> isset($_POST['form']['dst']) ? '1' : '0',
				'time_format'	=> intval($_POST['form']['time_format']),
				'date_format'	=> intval($_POST['form']['date_format']),
			);

			// Make sure we got a valid language string
			if (isset($_POST['language']))
			{
				$languages = forum_list_langs();
				$form['language'] = panther_trim($_POST['language']);
				if (!in_array($form['language'], $languages))
					message($lang_common['Bad request'], false, '404 Not Found');
			}
			else
				$form['language'] = $panther_config['o_default_lang'];

			if ($panther_user['is_admmod'])
			{
				$form['admin_note'] = panther_trim($_POST['admin_note']);

				// Are we allowed to change usernames?
				if ($panther_user['is_admin'] || ($panther_user['g_moderator'] == '1' && $panther_user['g_mod_rename_users'] == '1'))
				{
					$form['username'] = panther_trim($_POST['req_username']);
					if ($form['username'] != $old_username)
					{
						// Check username
						require PANTHER_ROOT.'lang/'.$panther_user['language'].'/register.php';

						$errors = array();
						check_username($form['username'], $id);
						if (!empty($errors))
							message($errors[0]);

						$username_updated = true;
					}
				}

				// We only allow administrators to update the post count
				if ($panther_user['is_admin'])
					$form['num_posts'] = intval($_POST['num_posts']);
			}

			if ($panther_config['o_regs_verify'] == '0' || $panther_user['is_admmod'])
			{
				require PANTHER_ROOT.'include/email.php';

				// Validate the email address
				$form['email'] = strtolower(panther_trim($_POST['req_email']));
				if (!$mailer->is_valid_email($form['email']))
					message($lang_common['Invalid email']);
			}

			break;
		}

		case 'personal':
		{
			$form = array(
				'realname'		=> isset($_POST['form']['realname']) ? panther_trim($_POST['form']['realname']) : '',
				'url'			=> isset($_POST['form']['url']) ? panther_trim($_POST['form']['url']) : '',
				'location'		=> isset($_POST['form']['location']) ? panther_trim($_POST['form']['location']) : '',
			);

			// Add http:// if the URL doesn't contain it already (while allowing https://, too)
			if ($panther_user['g_post_links'] == '1')
			{
				if ($form['url'] != '')
				{
					$url = url_valid($form['url']);

					if ($url === false)
						message($lang_profile['Invalid website URL']);

					$form['url'] = $url['url'];
				}
			}
			else
			{
				if (!empty($form['url']))
					message($lang_profile['Website not allowed']);

				$form['url'] = '';
			}

			if ($panther_user['is_admin'])
				$form['title'] = panther_trim($_POST['title']);
			else if ($panther_user['g_set_title'] == '1')
			{
				$form['title'] = panther_trim($_POST['title']);

				if ($form['title'] != '')
				{
					// A list of words that the title may not contain
					// If the language is English, there will be some duplicates, but it's not the end of the world
					$forbidden = array('member', 'moderator', 'administrator', 'banned', 'guest', utf8_strtolower($lang_common['Member']), utf8_strtolower($lang_common['Moderator']), utf8_strtolower($lang_common['Administrator']), utf8_strtolower($lang_common['Banned']), utf8_strtolower($lang_common['Guest']));

					if (in_array(utf8_strtolower($form['title']), $forbidden))
						message($lang_profile['Forbidden title']);
				}
			}

			break;
		}

		case 'messaging':
		{
			$form = array(
				'facebook'		=> panther_trim($_POST['form']['facebook']),
				'steam'			=> panther_trim($_POST['form']['steam']),
				'skype'			=> panther_trim($_POST['form']['skype']),
				'google'		=> panther_trim($_POST['form']['google']),
				'twitter'		=> panther_trim($_POST['form']['twitter']),
			);

			break;
		}

		case 'personality':
		{
			$form = array();

			// Clean up signature from POST
			if ($panther_config['o_signatures'] == '1')
			{
				$form['signature'] = isset($_POST['signature']) ? panther_linebreaks(panther_trim($_POST['signature'])) : '';

				// Validate signature
				if (panther_strlen($form['signature']) > $panther_config['p_sig_length'])
					message(sprintf($lang_prof_reg['Sig too long'], $panther_config['p_sig_length'], panther_strlen($form['signature']) - $panther_config['p_sig_length']));
				else if (substr_count($form['signature'], "\n") > ($panther_config['p_sig_lines']-1))
					message(sprintf($lang_prof_reg['Sig too many lines'], $panther_config['p_sig_lines']));
				else if ($form['signature'] && $panther_config['p_sig_all_caps'] == '0' && is_all_uppercase($form['signature']) && !$panther_user['is_admmod'])
					$form['signature'] = utf8_ucwords(utf8_strtolower($form['signature']));

				// Validate BBCode syntax
				if ($panther_config['p_sig_bbcode'] == '1')
				{
					require PANTHER_ROOT.'include/parser.php';

					$errors = array();
					$form['signature'] = $parser->preparse_bbcode($form['signature'], $errors, true);

					if (count($errors) > 0)
						message('<ul><li>'.implode('</li><li>', $errors).'</li></ul>');
				}
			}

			break;
		}

		case 'display':
		{
			$form = array(
				'disp_topics'		=> panther_trim($_POST['form']['disp_topics']),
				'disp_posts'		=> panther_trim($_POST['form']['disp_posts']),
				'show_smilies'		=> isset($_POST['form']['show_smilies']) ? '1' : '0',
				'show_img'			=> isset($_POST['form']['show_img']) ? '1' : '0',
				'show_img_sig'		=> isset($_POST['form']['show_img_sig']) ? '1' : '0',
				'show_avatars'		=> isset($_POST['form']['show_avatars']) ? '1' : '0',
				'show_sig'			=> isset($_POST['form']['show_sig']) ? '1' : '0',
				'use_editor'		=> isset($_POST['form']['use_editor']) ? '1' : '0',
			);

			if ($form['disp_topics'] != '')
			{
				$form['disp_topics'] = intval($form['disp_topics']);
				if ($form['disp_topics'] < 3)
					$form['disp_topics'] = 3;
				else if ($form['disp_topics'] > 75)
					$form['disp_topics'] = 75;
			}

			if ($form['disp_posts'] != '')
			{
				$form['disp_posts'] = intval($form['disp_posts']);
				if ($form['disp_posts'] < 3)
					$form['disp_posts'] = 3;
				else if ($form['disp_posts'] > 75)
					$form['disp_posts'] = 75;
			}

			// Make sure we got a valid style string
			if (isset($_POST['form']['style']))
			{
				$styles = forum_list_styles();
				$form['style'] = panther_trim($_POST['form']['style']);
				if (!in_array($form['style'], $styles))
					message($lang_common['Bad request'], false, '404 Not Found');
			}
			else
				$form['style'] = $panther_config['o_default_style'];

			break;
		}

		case 'privacy':
		{
			$form = array(
				'email_setting'			=> intval($_POST['form']['email_setting']),
				'notify_with_post'		=> isset($_POST['form']['notify_with_post']) ? '1' : '0',
				'auto_notify'			=> isset($_POST['form']['auto_notify']) ? '1' : '0',
				'pm_enabled'			=> isset($_POST['form']['pm_enabled']) ? '1' : '0',
				'pm_notify'				=> isset($_POST['form']['pm_notify']) ? '1' : '0',
			);

			if ($form['email_setting'] < 0 || $form['email_setting'] > 2)
				$form['email_setting'] = $panther_config['o_default_email_setting'];

			break;
		}

		default:
			message($lang_common['Bad request'], false, '404 Not Found');
	}

	// Single quotes around non-empty values and NULL for empty values
	$temp = $data = array();
	foreach ($form as $key => $input)
	{
		$value = ($input !== '') ? $input : NULL;

		$temp[] = $key.'= ?';
		$data[] = $value;
	}

	if (empty($temp))
		message($lang_common['Bad request'], false, '404 Not Found');

	$data[] = $id;
	$db->run('UPDATE '.$db->prefix.'users SET '.implode(',', $temp).' WHERE id=?', $data);

	// If we changed the username we have to update some stuff
	if ($username_updated)
	{
		$update = array(
			'username'	=>	$form['username'],
		);
		
		$data = array(
			':user'	=>	$old_username,
		);

		$rows = $db->update('bans', $update, 'username=:user', $data);

		// If any bans were updated, we will need to know because the cache will need to be regenerated.
		if ($rows > 0)
			$bans_updated = true;
		
		$update = array(
			'poster'	=>	$form['username'],
		);

		$data = array(
			':id'	=>	$id,
		);
		
		$db->update('posts', $update, 'poster_id=:id', $data);

		$data = array(
			':username'	=>	$old_username,
		);
		
		$db->update('topics', $update, 'poster=:username', $data);

		$update = array(
			'edited_by'	=>	$form['username'],
		);
		
		$db->update('posts', $update, 'edited_by=:username', $data);
		
		$update = array(
			'last_poster'	=>	$form['username'],
		);
		
		$db->update('topics', $update, 'last_poster=:username', $data);
		$db->update('topics', $update, 'last_poster=:username', $data);
		$db->update('forums', $update, 'last_poster=:username', $data);
		
		$update = array(
			'ident'	=>	$form['username'],
		);

		$db->update('online', $update, 'ident=:username', $data);

		// If the user is a moderator or an administrator we have to update the moderator lists
		$data = array(
			':id'	=>	$id,
		);
		$ps = $db->select('users', 'group_id', $data, 'id=:id');
		$group_id = $ps->fetchColumn();

		if ($group_id == PANTHER_ADMIN || $panther_groups[$group_id]['g_moderator'] == '1')
		{
			$ps = $db->select('forums', 'id, moderators');
			foreach ($ps as $cur_forum)
			{
				$cur_moderators = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();
				if (in_array($id, $cur_moderators))
				{
					unset($cur_moderators[$old_username]);
					$cur_moderators[$form['username']] = $id;
					uksort($cur_moderators, 'utf8_strcasecmp');

					$update = array(
						'moderators' => serialize($cur_moderators),
					);
					
					$data = array(
						':id'	=>	$cur_forum['id'],
					);
					
					$db->update('forums', $update, 'id=:id', $data);
				}
			}
		}

		// Regenerate the users info cache
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';

		generate_users_info_cache();

		// Check if the bans table was updated and regenerate the bans cache when needed
		if (isset($bans_updated))
			generate_bans_cache();
	}

	redirect(panther_link($panther_url['profile_'.strtolower($section)], array($id)), $lang_profile['Profile redirect']);
}

($hook = get_extensions('profile_after_form_handling')) ? eval($hook) : null;
$data = array(
	':id'	=>	$id,
);

$ps = $db->run('SELECT u.username, u.email, u.title, u.realname, u.url, u.facebook, u.steam, u.skype, u.google, u.twitter, u.location, u.signature, u.disp_topics, u.disp_posts, u.email_setting, u.notify_with_post, u.auto_notify, u.use_editor, u.pm_enabled, u.pm_notify, u.use_gravatar, u.show_smilies, u.show_img, u.show_img_sig, u.show_avatars, u.show_sig, u.timezone, u.dst, u.language, u.style, u.num_posts, u.last_post, u.last_visit, u.registered, u.registration_ip, u.reputation, u.admin_note, u.date_format, u.time_format, u.last_visit, u.posting_ban, g.g_id, g.g_user_title, g.g_moderator, g.g_use_pm, g.g_admin FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE u.id=:id', $data);
if (!$ps->rowCount())
	message($lang_common['Bad request'], false, '404 Not Found');
else
	$user = $ps->fetch();

// View or edit?
if ($panther_user['id'] != $id && !in_array($section, array('rep_received', 'rep_given', 'view')) && // If we aren't the user (i.e. editing your own profile) and we aren't viewing what rep they have
	(!$panther_user['is_admmod'] ||	// and we are not an admin or mod
	(!$panther_user['is_admin'] &&	// or we aren't an admin and ...
	($panther_user['g_mod_edit_users'] == '0' ||	// mods aren't allowed to edit users
	$user['g_id'] == PANTHER_ADMIN ||	// or the user is an admin
	$user['g_moderator'] == '1'))) || $section == 'view')	// or the user is another mod
{
	$user_personal = array();
	if ($panther_config['o_users_online'] == '1')
	{
		require PANTHER_ROOT.'lang/'.$panther_user['language'].'/online.php';
		$data = array(
			':id'	=>	$id,
		);
		
		$ps = $db->select('online', 'currently', $data, 'user_id=:id');
		$online = $ps->fetch();
	
		if ($online['currently'] == NULL || $online['currently'] == '')
		{
			$icon = 'status_offline';
			$status = $lang_online['user is offline'];
			$location = $lang_online['not online'];
		}
		else
		{
			$icon = 'status_online';
			$status = $lang_online['user is online'];
			$location = generate_user_location($online['currently']);
		}
	}

	$user_personal[] = array('title' => $lang_common['Username']);
	$user_personal[] = array('data' => colourize_group($user['username'], $user['g_id']), 'raw' => true, 'icon' => $panther_config['o_image_dir'].$icon.'.png', 'icon_title' => $status);

	$user_title_field = get_title($user);
	$user_personal[] = array('title' => $lang_common['Title']);
	$user_personal[] = array('data' => (($panther_config['o_censoring'] == '1') ? censor_words($user_title_field) : $user_title_field));

	if ($user['realname'] != '')
	{
		$user_personal[] = array('title' => $lang_profile['Realname']);
		$user_personal[] = array('data' => (($panther_config['o_censoring'] == '1') ? censor_words($user['realname']) : $user['realname']));
	}

	if ($user['location'] != '')
	{
		$user_personal[] = array('title' => $lang_profile['Location']);
		$user_personal[] = array('data' => (($panther_config['o_censoring'] == '1') ? censor_words($user['location']) : $user['location']));
	}

	if ($user['url'] != '')
	{
		$user['url'] = ($panther_config['o_censoring'] == '1') ? censor_words($user['url']) : $user['url'];
		$user_personal[] = array('title' => $lang_profile['Website']);
		$user_personal[] = array('data' => $user['url'], 'class' => 'website', 'href' => true, 'lang' => $user['url']);
	}

	if ($user['email_setting'] == '0' && !$panther_user['is_guest'] && $panther_user['g_send_email'] == '1')
		$email_field = array('data' => 'mailto:'.$user['email'], 'class' => 'email', 'href' => true, 'lang' => $user['email']);
	else if ($user['email_setting'] == '1' && !$panther_user['is_guest'] && $panther_user['g_send_email'] == '1')
		$email_field = array('data' => panther_link($panther_url['email'], array($id)), 'class' => 'email', 'href' => true, 'lang' => $lang_common['Send email']);

	if (isset($email_field))
	{
		$user_personal[] = array('title' => $lang_common['Email']);
		$user_personal[] = $email_field;
	}

	$user_personal[] = array('title' => $lang_online['currently']);
	$user_personal[] = array('data' => $location, 'raw' => true);
	$user_messaging = array();

	if ($user['facebook'] != '')
	{
		$user_messaging[] = array('title' => $lang_profile['Facebook']);
		$user_messaging[] = array('data' => (($panther_config['o_censoring'] == '1') ? censor_words($user['facebook']) : $user['facebook']));
	}

	if ($user['steam'] != '')
	{
		$user_messaging[] = array('title' => $lang_profile['Steam']);
		$user_messaging[] = array('data' => $user['steam']);
	}

	if ($user['skype'] != '')
	{
		$user_messaging[] = array('title' => $lang_profile['Skype']);
		$user_messaging[] = array('data' => (($panther_config['o_censoring'] == '1') ? censor_words($user['skype']) : $user['skype']));
	}

	if ($user['twitter'] != '')
	{
		$user_messaging[] = array('title' => $lang_profile['Twitter']);
		$user_messaging[] = array('data' => (($panther_config['o_censoring'] == '1') ? censor_words($user['twitter']) : $user['twitter']));
	}

	if ($user['google'] != '')
	{
		$user_messaging[] = array('title' => $lang_profile['Google']);
		$user_messaging[] = array('data' => (($panther_config['o_censoring'] == '1') ? censor_words($user['google']) : $user['google']));
	}

	$user_personality = array();
	if ($panther_config['o_avatars'] == '1')
	{
		$user_personality[] = array('title' => $lang_profile['Avatar']);
		$user_personality[] = array('data' => generate_avatar_markup($id, $user['email'], $user['use_gravatar']));
	}

	if ($panther_config['o_signatures'] == '1')
	{
		if ($user['signature'] != '')
		{
			require PANTHER_ROOT.'include/parser.php';
			$user_personality[] = array('title' => $lang_profile['Signature']);
			$user_personality[] = array('data' => $parser->parse_signature($user['signature']), 'signature' => true);
		}
	}

	$user_activity = $quick_searches = array();
	if ($panther_config['o_show_post_count'] == '1' || $panther_user['is_admmod'])
		$quick_searches[]['data'] = forum_number_format($user['num_posts']);
	if ($panther_user['g_search'] == '1')
	{
		if ($panther_user['is_admmod'] && $panther_config['o_warnings'] == '1')
		{
			// Load the warnings.php language file
			require PANTHER_ROOT.'lang/'.$panther_user['language'].'/warnings.php';

			// Does the user have active warnings?
			$data = array(
				':id'	=>	$id,
				':time'	=>	time(),
			);

			$ps = $db->select('warnings', 'SUM(points)', $data, 'user_id=:id AND (date_expire>:time OR date_expire=0)');
			$has_active = $ps->fetchColumn();

			if ($has_active)
			{
				$warning_level = $lang_warnings['Warning level'];
				$points_active = $has_active;
			}
			else
			{
				$warning_level = $lang_warnings['Warning level'];
				$points_active = '0';
			}
		}

		if (($panther_user['is_admin'] || ($panther_user['is_admmod'] && $panther_user['g_mod_warn_users'] == '1')) && $panther_config['o_warnings'] == '1')
		{
			$user_activity[] = array('title' => $warning_level);
			$user_activity[] = array('data' => $points_active, 'href' => panther_link($panther_url['warning_view'], array($id)), 'lang' => $lang_warnings['Show all warnings'], 'href2' => panther_link($panther_url['warn_user'], array($id)), 'lang2' => $lang_warnings['Warn user']);
		}
		else if ($panther_user['is_admmod'] && $panther_config['o_warnings'] == '1')
		{
			$user_activity[] = array('title' => $warning_level);
			$user_activity[] = array('data' => $points_active);
		}

		$quick_searches = array();
		if ($user['num_posts'] > 0)
		{
			$quick_searches[] = array(panther_link($panther_url['search_user_topics'], array($id)), $lang_profile['Show topics']);
			$quick_searches[] = array(panther_link($panther_url['search_user_posts'], array($id)), $lang_profile['Show posts']);
		}
		if ($panther_user['is_admmod'] && $panther_config['o_topic_subscriptions'] == '1')
			$quick_searches[] = array(panther_link($panther_url['search_subscriptions'], array($id)), $lang_profile['Show subscriptions']);
	}

	if (!empty($quick_searches))
	{
		$user_activity[] = array('title' => $lang_common['Posts']);
		$user_activity[] = array('implode' => true, 'data' => $quick_searches);
	}

	if ($user['num_posts'] > 0)
	{
		$user_activity[] = array('title' => $lang_common['Last post']);
		$user_activity[] = array('data' => format_time($user['last_post']));
	}

	$user_activity[] = array('title' => $lang_profile['Last visit']);
	$user_activity[] = array('data' => format_time($user['last_visit']));

	$user_activity[] = array('title' => $lang_common['Registered']);
	$user_activity[] = array('data' => format_time($user['registered'], true));
	
	$render = array(
		'lang_profile' => $lang_profile,
		'lang_common' => $lang_common,
		'user_personal' => $user_personal,
		'user_messaging' => $user_messaging,
		'user_personality' => $user_personality,
		'user_activity' => $user_activity,
		'panther_config' => $panther_config,
	);

	if ($panther_config['o_reputation'] == '1')
	{
		switch(true)
		{
			case $user['reputation'] > '0':
				$type = 'positive';
			break;
			case $user['reputation'] < '0':
				$type = 'negative';
			break;
			default:
				$type = 'zero';
			break;
		}

		$render['reputation'] = array('type' => $type, 'value' => forum_number_format($user['reputation']), 'link_received' => panther_link($panther_url['profile_rep_received'], array($id)), 'link_given' => panther_link($panther_url['profile_rep_given'], array($id)));
	}

	$page_title = array($panther_config['o_board_title'], sprintf($lang_profile['Users profile'], $user['username']));
	define('PANTHER_ALLOW_INDEX', 1);
	define('PANTHER_ACTIVE_PAGE', 'index');
	require PANTHER_ROOT.'header.php';

	if ($section == 'view' && $panther_user['id'] == $id || $panther_user['is_admmod'] && ($panther_user['is_admin'] || $panther_user['g_mod_edit_users'] == '1'))
		generate_profile_menu('view');

	$tpl = load_template('view_profile.tpl');
	echo $tpl->render($render);

	require PANTHER_ROOT.'footer.php';
}
else
{
	if (!$section || $section == 'essentials')
	{
		if ($panther_user['is_admmod'])
			$email_link = panther_link($panther_url['email'], array($id));
		else if ($panther_config['o_regs_verify'] == '1')
			$email_link = panther_link($panther_url['change_email'], array($id));
		else
			$email_link = '';

		$posts_field = '';
		$posts_actions = array();
		if ($panther_user['g_search'] == '1' || $panther_user['is_admin'])
		{
			$posts_actions[] = array('href' => panther_link($panther_url['search_user_topics'], array($id)), 'lang' => $lang_profile['Show topics']);
			$posts_actions[] = array('href' => panther_link($panther_url['search_user_posts'], array($id)), 'lang' => $lang_profile['Show posts']);

			if ($panther_config['o_topic_subscriptions'] == '1')
				$posts_actions[] = array('href' => panther_link($panther_url['search_subscriptions'], array($id)), 'lang' => $lang_profile['Show subscriptions']);
		}

		require PANTHER_ROOT.'lang/'.$panther_user['language'].'/warnings.php';

		// Does the user have active warnings?
		$data = array(
			':id'	=>	$id,
			':time'	=>	time(),
		);

		$ps = $db->select('warnings', 'SUM(points)', $data, 'user_id=:id AND (date_expire>:time OR date_expire=0)');
		$has_active = $ps->fetchColumn();

		$page_title = array($panther_config['o_board_title'], $lang_common['Profile'], $lang_profile['Section essentials']);
		$required_fields = array('req_username' => $lang_common['Username'], 'req_email' => $lang_common['Email']);
		define('PANTHER_ACTIVE_PAGE', 'profile');
		require PANTHER_ROOT.'header.php';

		generate_profile_menu('essentials');

		$time_formats = $date_formats = array();
		foreach (array_unique($forum_time_formats) as $key => $time_format)
			$time_formats[] = array('value' => $key, 'time' => (format_time(time(), false, null, $time_format, true, true).(($key == 0) ? ' ('.$lang_prof_reg['Default'].')' : '')));

		foreach (array_unique($forum_date_formats) as $key => $date_format)
			$date_formats[] = array('value' => $key, 'time' => (format_time(time(), true, $date_format, null, false, true).(($key == 0) ? ' ('.$lang_prof_reg['Default'].')' : '')));

		$tpl = load_template('profile_essentials.tpl');
		echo $tpl->render(
			array(
				'id' => $id,
				'lang_profile' => $lang_profile,
				'lang_common' => $lang_common,
				'lang_prof_reg' => $lang_prof_reg,
				'panther_user' => $panther_user,
				'user' => $user,
				'csrf_token' => generate_csrf_token(),
				'form_action' => panther_link($panther_url['profile_essentials'], array($id)),
				'panther_config' => $panther_config,
				'posts_actions' => $posts_actions,
				'time_formats' => $time_formats,
				'date_formats' => $date_formats,
				'languages' => forum_list_langs(),
				'change_pass_link' => panther_link($panther_url['change_password'], array($id)),
				'last_visit' => format_time($user['last_visit']),
				'last_post' => format_time($user['last_post']),
				'registered' => format_time($user['registered'], true),
				'ip_link' => panther_link($panther_url['get_host'], array($user['registration_ip'])),
				'warning_link' => panther_link($panther_url['warning_view'], array($id)),
				'warn_link' => panther_link($panther_url['warn_user'], array($id)),
				'has_active' => $has_active,
				'lang_warnings' => $lang_warnings,
				'posts' => forum_number_format($user['num_posts']),
				'email_link' => $email_link,
			)
		);
	}
	else if ($section == 'personal')
	{
		$page_title = array($panther_config['o_board_title'], $lang_common['Profile'], $lang_profile['Section personal']);
		define('PANTHER_ACTIVE_PAGE', 'profile');
		require PANTHER_ROOT.'header.php';

		generate_profile_menu('personal');
		
		$tpl = load_template('profile_personal.tpl');
		echo $tpl->render(
			array(
				'user' => $user,
				'lang_profile' => $lang_profile,
				'csrf_token' => generate_csrf_token(),
				'form_action' => panther_link($panther_url['profile_personal'], array($id)),
				'panther_user' => $panther_user,
				'user' => $user,
				'lang_common' => $lang_common,
			)
		);
	}
	else if ($section == 'messaging')
	{
		$page_title = array($panther_config['o_board_title'], $lang_common['Profile'], $lang_profile['Section messaging']);
		define('PANTHER_ACTIVE_PAGE', 'profile');
		require PANTHER_ROOT.'header.php';

		generate_profile_menu('messaging');
		
		$tpl = load_template('profile_messaging.tpl');
		echo $tpl->render(
			array(
				'form_action' => panther_link($panther_url['profile_messaging'], array($id)),
				'csrf_token' => generate_csrf_token(),
				'lang_profile' => $lang_profile,
				'lang_common' => $lang_common,
				'user' => $user,
			)
		);
	}
	else if ($section == 'personality')
	{
		if ($panther_config['o_avatars'] == '0' && $panther_config['o_signatures'] == '0')
			message($lang_common['Bad request'], false, '404 Not Found');

		$page_title = array($panther_config['o_board_title'], $lang_common['Profile'], $lang_profile['Section personality']);
		define('POSTING', 1);
		define('PANTHER_ACTIVE_PAGE', 'profile');
		require PANTHER_ROOT.'header.php';

		generate_profile_menu('personality');
		if ($user['signature'] != '')
		{
			require PANTHER_ROOT.'include/parser.php';
			$signature = $parser->parse_signature($user['signature']);
		}
		else
			$signature = '';

		$csrf_token = generate_csrf_token();
		$user_avatar = generate_avatar_markup($id, $user['email'], $user['use_gravatar']);
		$tpl = load_template('profile_personality.tpl');
		echo $tpl->render(
			array(
				'lang_profile' => $lang_profile,
				'lang_common' => $lang_common,
				'user' => $user,
				'form_action' => panther_link($panther_url['profile_personality'], array($id)),
				'csrf_token' => $csrf_token,
				'panther_config' => $panther_config,
				'avatar_link' => panther_link($panther_url['upload_avatar'], array($id, $csrf_token)),
				'user_avatar' => $user_avatar,
				'can_delete' => (stristr($user_avatar, '1.'.$panther_config['o_avatar']) === false && $user['use_gravatar'] == '0') ? true : false,
				'delete_link' => panther_link($panther_url['delete_avatar'], array($id, $csrf_token)),
				'upload_link' => panther_link($panther_url['upload_avatar'], array($id, $csrf_token)),
				'gravatar_link' => panther_link($panther_url['use_gravatar'], array($id, generate_csrf_token())),
				'signature_length' => forum_number_format($panther_config['p_sig_length']),
				'signature' => $signature,
				'quickpost' => array(
					'bbcode' => panther_link($panther_url['help'], array('bbcode')),
					'url' => panther_link($panther_url['help'], array('url')),
					'img' => panther_link($panther_url['help'], array('img')),
					'smilies' => panther_link($panther_url['help'], array('smilies')),
				)
			)
		);
	}
	else if ($section == 'display')
	{
		$page_title = array($panther_config['o_board_title'], $lang_common['Profile'], $lang_profile['Section display']);
		define('PANTHER_ACTIVE_PAGE', 'profile');
		require PANTHER_ROOT.'header.php';

		generate_profile_menu('display');
		
		$checkboxes = array();
		if ($panther_config['o_smilies'] == '1' || $panther_config['o_smilies_sig'] == '1')
			$checkboxes[] = array('name' => 'show_smilies', 'checked' => (($user['show_smilies'] == '1') ? true : false), 'title' => $lang_profile['Show smilies']);

		if ($panther_config['o_signatures'] == '1')
			$checkboxes[] = array('name' => 'show_sig', 'checked' => (($user['show_sig'] == '1') ? true : false), 'title' => $lang_profile['Show sigs']);

		if ($panther_config['o_avatars'] == '1')
			$checkboxes[] = array('name' => 'show_avatars', 'checked' => (($user['show_avatars'] == '1') ? true : false), 'title' => $lang_profile['Show avatars']);

		if ($panther_config['p_message_bbcode'] == '1' && $panther_config['p_message_img_tag'] == '1')
			$checkboxes[] = array('name' => 'show_img', 'checked' => (($user['show_img'] == '1') ? true : false), 'title' => $lang_profile['Show images']);

		if ($panther_config['o_signatures'] == '1' && $panther_config['p_sig_bbcode'] == '1' && $panther_config['p_sig_img_tag'] == '1')
			$checkboxes[] = array('name' => 'show_img_sig', 'checked' => (($user['show_img_sig'] == '1') ? true : false), 'title' => $lang_profile['Show images sigs']);

		if ($panther_config['o_use_editor'])
			$checkboxes[] = array('name' => 'use_editor', 'checked' => (($user['use_editor'] == '1') ? true : false), 'title' => $lang_profile['Use editor']);

		($hook = get_extensions('profile_display')) ? eval($hook) : null;

		if ($panther_config['o_reputation'] == '1')
		{
			switch(true)
			{
				case $user['reputation'] > '0':
					$type = 'positive';
				break;
				case $user['reputation'] < '0':
					$type = 'negative';
				break;
				default:
					$type = 'zero';
				break;
			}

			$reputation = array('type' => $type, 'value' => forum_number_format($user['reputation']));
		}
		
		$tpl = load_template('profile_display.tpl');
		echo $tpl->render(
			array(
				'lang_profile' => $lang_profile,
				'lang_common' => $lang_common,
				'user' => $user,
				'checkboxes' => $checkboxes,
				'styles' => forum_list_styles(),
				'form_action' => panther_link($panther_url['profile_display'], array($id)),
				'csrf_token' => generate_csrf_token(),
				'given_link' => panther_link($panther_url['profile_rep_given'], array($id)),
				'received_link' => panther_link($panther_url['profile_rep_received'], array($id)),
				'reputation' => isset($reputation) ? $reputation : '',
				'panther_config' => $panther_config,
			)
		);
	}
	elseif ($section == 'rep_received' || $section == 'rep_given')
	{
		if ($panther_config['o_reputation'] == '0')
			message($lang_common['Bad request']);

		define('REPUTATION', 1);
		$page = (!isset($_GET['p']) || $_GET['p'] <= '1') ? '1' : intval($_GET['p']);
		$data = array(
			':id'	=>	$id,
		);

		if ($section == 'rep_received')
			$sql = "SELECT COUNT(r.id) FROM ".$db->prefix."reputation AS r LEFT JOIN ".$db->prefix."posts AS p ON r.post_id=p.id WHERE p.poster_id=:id";
		else
			$sql = "SELECT COUNT(id) FROM ".$db->prefix."reputation WHERE given_by=:id";

		$ps = $db->run($sql, $data);
		$total = $ps->fetchColumn();

		//What page are we on?
		$num_pages = ceil($total/$panther_config['o_disp_topics_default']);
		if ($page > $num_pages) $page = 1;
		$start_from = intval($panther_config['o_disp_topics_default'])*($page-1);
		$limit = $start_from.','.$panther_config['o_disp_topics_default'];

		switch ($section)
		{
			case 'rep_received':
				$data[':id1'] = $id;
			
				$sql = "SELECT r.id, r.given_by, u.group_id, u.username, r.time_given, r.post_id, r.vote, p.topic_id, t.subject, :id1 AS given_to FROM ".$db->prefix."reputation AS r LEFT JOIN ".$db->prefix."users AS u ON u.id = r.given_by LEFT JOIN ".$db->prefix."posts AS p ON p.id = r.post_id LEFT JOIN ".$db->prefix."topics AS t ON t.id = p.topic_id WHERE p.poster_id=:id ORDER BY r.id DESC LIMIT ".$limit;
				$ps = $db->run($sql, $data);
				
				if (!$ps->rowCount())
					message($lang_profile['No received reputation']);
			break;
			default:
				$sql = "SELECT r.id, p.poster_id AS given_to, u.group_id, r.given_by, r.time_given, r.post_id, r.vote, u.username, p.topic_id, t.subject FROM ".$db->prefix."reputation AS r LEFT JOIN ".$db->prefix."posts AS p ON p.id = r.post_id LEFT JOIN ".$db->prefix."users AS u ON u.id = p.poster_id LEFT JOIN ".$db->prefix."topics AS t ON t.id = p.topic_id WHERE r.given_by=:id ORDER BY r.id DESC LIMIT ".$limit;
				$ps = $db->run($sql, $data); 
				
				if (!$ps->rowCount())
					message($lang_profile['No given reputation']);
			break;
		}

		define('PANTHER_ACTIVE_PAGE', 'profile');
		$page_title = array($panther_config['o_board_title'], $lang_common['Profile'], $user['username'], $lang_profile['Reputation']);
		require PANTHER_ROOT.'header.php';
		$rep_count = '0';

		$reputation = array();
		foreach ($ps as $cur_row)
		{
			if ($cur_row['username'] == '')
			{
				$cur_row['username'] = $lang_profile['Deleted user'];
				$cur_row['group_id'] = PANTHER_GUEST;
			}

			if ($cur_row['given_by'] == '')
				$cur_row['given_by'] = PANTHER_GUEST;
				
			if ($cur_row['given_to'] == '')
				$cur_row['given_to'] = PANTHER_GUEST;

			if ($section == 'rep_received')
				$username = colourize_group($cur_row['username'], $cur_row['group_id'], $cur_row['given_by']);
			else
				$username = colourize_group($cur_row['username'], $cur_row['group_id'], $cur_row['given_to']);

			$reputation[] = array(
				'given' => format_time($cur_row['time_given']),
				'user' => $username,
				'vote' => $cur_row['vote'],
				'id' => $cur_row['id'],
				'subject' => $cur_row['subject'],
				'link' => panther_link($panther_url['post'], array($cur_row['post_id'])),
			);
		}
		
		$tpl = load_template('profile_reputation.tpl');
		echo $tpl->render(
			array(
				'lang_profile' => $lang_profile,
				'lang_common' => $lang_common,
				'panther_config' => $panther_config,
				'index_link' => panther_link($panther_url['index']),
				'profile_link' => panther_link($panther_url['profile'], array($id, url_friendly($user['username']))),
				'user' => $user,
				'rep_section' => $lang_profile[ucfirst($section)],
				'pagination' => paginate($num_pages, $page, $panther_url['profile_'.strtolower($section)], array($id)),
				'section' => $section,
				'panther_user' => $panther_user,
				'id' => $id,
				'page' => $page,
				'reputation' => $reputation,
			)
		);
	}
	else if ($section == 'privacy')
	{
		$page_title = array($panther_config['o_board_title'], $lang_common['Profile'], $lang_profile['Section privacy']);
		define('PANTHER_ACTIVE_PAGE', 'profile');
		require PANTHER_ROOT.'header.php';

		generate_profile_menu('privacy');

		$tpl = load_template('profile_privacy.tpl');
		echo $tpl->render(
			array(
				'lang_profile' => $lang_profile,
				'lang_common' => $lang_common,
				'lang_prof_reg' => $lang_prof_reg,
				'csrf_token' => generate_csrf_token(),
				'form_action' => panther_link($panther_url['profile_privacy'], array($id)),
				'panther_config' => $panther_config,
				'panther_user' => $panther_user,
				'user' => $user,
			)
		);
	}
	else if ($section == 'admin')
	{
		if (!$panther_user['is_admmod'] || ($panther_user['g_moderator'] == '1' && $panther_user['g_mod_ban_users'] == '0'))
			message($lang_common['Bad request'], false, '403 Forbidden');

		$posting_ban = format_posting_ban_expiration(($user['posting_ban'] - time()), $lang_profile);
		$page_title = array($panther_config['o_board_title'], $lang_common['Profile'], $lang_profile['Section admin']);

		($hook = get_extensions('profile_admin_before_header')) ? eval($hook) : null;

		define('PANTHER_ACTIVE_PAGE', 'profile');
		require PANTHER_ROOT.'header.php';

		generate_profile_menu('admin');
		
		$render = array(
			'lang_profile' => $lang_profile,
			'form_action' => panther_link($panther_url['profile_admin'], array($id)),
			'csrf_token' => generate_csrf_token(),
			'user' => $user,
			'posting_ban' => $user['g_moderator'] == '0' && $user['g_id'] != PANTHER_ADMIN && $user['g_admin'] == '0' && ($panther_user['is_admin'] == '1') ? true : false,
			'ban_info' => (($posting_ban[2] != $lang_profile['Never']) ? sprintf($lang_profile['current ban'], format_time($user['posting_ban'])) : ''),
			'posting_ban' => $posting_ban,
			'is_moderator' => ($panther_user['g_moderator'] == '1' && $panther_user['g_admin'] == '0' && $user['g_id'] != PANTHER_ADMIN) ? true : false,
		);

		if ($panther_user['is_admin'])
		{
			if (file_exists(FORUM_CACHE_DIR.'cache_restrictions.php'))
				require FORUM_CACHE_DIR.'cache_restrictions.php';
			else
			{
				if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
					require PANTHER_ROOT.'include/cache.php';

				generate_admin_restrictions_cache();
				require FORUM_CACHE_DIR.'cache_restrictions.php';
			}

			if (!isset($admins[$panther_user['id']]) || $panther_user['id'] == '2')
				$admins[$panther_user['id']] = array('admin_users' => '1');

			if ($panther_user['id'] != $id && $admins[$panther_user['id']]['admin_users'] == '1')
			{
				$groups = array();
				$render['edit_groups'] = true;
				foreach ($panther_groups as $cur_group)
					if ($cur_group['g_id'] != PANTHER_GUEST)
						$groups[] = array('id' => $cur_group['g_id'], 'checked' => (($cur_group['g_id'] == $user['g_id'] || ($cur_group['g_id'] == $panther_config['o_default_user_group'] && $user['g_id'] == '')) ? true : false), 'title' => $cur_group['g_title']);

				$render['groups'] = $groups;
			}
			
			$categories = $forums = array();
			$render['can_delete'] = (($admins[$panther_user['id']]['admin_users'] == '1') ? true : false);
			if ($user['g_moderator'] == '1' || $user['g_id'] == PANTHER_ADMIN)
			{
				$render['user_is_moderator'] = true;
				$ps = $db->run('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name, f.moderators FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id WHERE f.redirect_url IS NULL ORDER BY c.disp_position, c.id, f.disp_position');
				foreach ($ps as $cur_forum)
				{
					if (!isset($categories[$cur_forum['cid']]))
					{
						$categories[$cur_forum['cid']] = array(
							'name' => $cur_forum['cat_name'],
							'cid' => $cur_forum['cid'],
						);
					}

					$moderators = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();
					$forums[] = array(
						'id' => $cur_forum['fid'],
						'name' => $cur_forum['forum_name'],
						'category_id' => $cur_forum['cid'],
						'checked' => ((in_array($id, $moderators)) ? true : false),
					);
				}
				
				$render['categories'] = $categories;
				$render['forums'] = $forums;
			}
		}

		$tpl = load_template('profile_admin.tpl');
		echo $tpl->render($render);
		
		($hook = get_extensions('profile_admin_after_form')) ? eval($hook) : null;
	}
	else
		message($lang_common['Bad request'], false, '404 Not Found');

	require PANTHER_ROOT.'footer.php';
}