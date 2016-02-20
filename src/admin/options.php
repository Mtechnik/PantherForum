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
	if(!is_null($admins[$panther_user['id']]['admin_options']))
	{
		if ($admins[$panther_user['id']]['admin_options'] == '0')
			message($lang_common['No permission'], false, '403 Forbidden');
	}
}

check_authentication();

// Load the admin_options.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_options.php';
$max_file_size = str_replace('M', '', @ini_get('upload_max_filesize')) * pow(1024,2);

if (isset($_POST['form_sent']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/options.php');

	$form = array(
		'board_title'			=> panther_trim($_POST['form']['board_title']),
		'board_desc'			=> panther_trim($_POST['form']['board_desc']),
		'base_url'				=> panther_trim($_POST['form']['base_url']),
		'default_timezone'		=> floatval($_POST['form']['default_timezone']),
		'default_dst'			=> $_POST['form']['default_dst'] != '1' ? '0' : '1',
		'default_lang'			=> panther_trim($_POST['form']['default_lang']),
		'default_style'			=> panther_trim($_POST['form']['default_style']),
		'time_format'			=> panther_trim($_POST['form']['time_format']),
		'date_format'			=> panther_trim($_POST['form']['date_format']),
		'timeout_visit'			=> (intval($_POST['form']['timeout_visit']) > 0) ? intval($_POST['form']['timeout_visit']) : 1,
		'timeout_online'		=> (intval($_POST['form']['timeout_online']) > 0) ? intval($_POST['form']['timeout_online']) : 1,
		'redirect_delay'		=> (intval($_POST['form']['redirect_delay']) >= 0) ? intval($_POST['form']['redirect_delay']) : 0,
		'user_tags_max'			=> (intval($_POST['form']['user_tags_max']) > 0) ? intval($_POST['form']['user_tags_max']) : 5,
		'cookie_secure'			=> $_POST['form']['cookie_secure'] != '1' ? '0' : '1',
		'force_ssl'				=> $_POST['form']['force_ssl'] != '1' ? '0' : '1',
		'cookie_name'			=> panther_trim($_POST['form']['cookie_name']),
		'cookie_seed'			=> panther_trim($_POST['form']['cookie_seed']),
		'cookie_domain'			=> panther_trim($_POST['form']['cookie_domain']),
		'cookie_path'			=> panther_trim($_POST['form']['cookie_path']),
		'debug_mode'			=> $_POST['form']['debug_mode'] != '1' ? '0' : '1',
		'archiving'				=> $_POST['form']['archive'] != '1' ? '0' : '1',
		'update_type'			=> (in_array($_POST['form']['update_type'], array(0, 1, 2, 3))) ? intval($_POST['form']['update_type']) : 3,
		'show_queries'			=> $_POST['form']['show_queries'] != '1' ? '0' : '1',
		'login_queue'			=> $_POST['form']['login_queue'] != '1' ? '0' : '1',
		'queue_size'			=> (intval($_POST['form']['queue_size']) > 5) ? intval($_POST['form']['queue_size']) : 30,
		'max_attempts'			=> (intval($_POST['form']['max_attempts']) > 1) ? intval($_POST['form']['max_attempts']) : 5,
		'show_version'			=> $_POST['form']['show_version'] != '1' ? '0' : '1',
		'show_user_info'		=> $_POST['form']['show_user_info'] != '1' ? '0' : '1',
		'show_post_count'		=> $_POST['form']['show_post_count'] != '1' ? '0' : '1',
		'smilies'				=> $_POST['form']['smilies'] != '1' ? '0' : '1',
		'smilies_width'			=> intval($_POST['form']['smilies_width']),
		'smilies_height'		=> intval($_POST['form']['smilies_height']),
		'smilies_size'			=> intval($_POST['form']['smilies_size']),
		'private_messaging'		=> intval($_POST['form']['private_messaging']),
		'smilies_sig'			=> $_POST['form']['smilies_sig'] != '1' ? '0' : '1',
		'make_links'			=> $_POST['form']['make_links'] != '1' ? '0' : '1',
		'topic_review'			=> (intval($_POST['form']['topic_review']) >= 0) ? intval($_POST['form']['topic_review']) : 0,
		'disp_topics_default'	=> intval($_POST['form']['disp_topics_default']),
		'disp_posts_default'	=> intval($_POST['form']['disp_posts_default']),
		'indent_num_spaces'		=> (intval($_POST['form']['indent_num_spaces']) >= 0) ? intval($_POST['form']['indent_num_spaces']) : 0,
		'quote_depth'			=> (intval($_POST['form']['quote_depth']) > 0) ? intval($_POST['form']['quote_depth']) : 1,
		'quickpost'				=> $_POST['form']['quickpost'] != '1' ? '0' : '1',
		'attachments'			=> $_POST['form']['attachments'] != '1' ? '0' : '1',
		'create_orphans'		=> $_POST['form']['create_orphans'] != '1' ? '0' : '1',
		'max_upload_size'		=> intval($_POST['form']['max_upload_size']) ? intval($_POST['form']['max_upload_size']) : 10485760,
		'attachment_images'		=> panther_trim($_POST['form']['attachment_images']),
		'attachment_extensions'	=> panther_trim($_POST['form']['attachment_extensions']),
		'sfs_api'				=> panther_trim($_POST['form']['sfs_api']),
		'tinypng_api'			=> panther_trim($_POST['form']['tinypng_api']),
		'cloudflare_api'		=> panther_trim($_POST['form']['cloudflare_api']),
		'cloudflare_email'		=> panther_trim($_POST['form']['cloudflare_email']),
		'cloudflare_domain'		=> panther_trim($_POST['form']['cloudflare_domain']),
		'http_authentication'	=> $_POST['form']['http_authentication'] != '1' ? '0' : '1',
		'popular_topics'		=> (intval($_POST['form']['popular_topics']) > 0) ? intval($_POST['form']['popular_topics']) : 25,
		'max_pm_receivers'		=> (intval($_POST['form']['max_pm_receivers']) > 0) ? intval($_POST['form']['max_pm_receivers']) : 15,
		'url_type'				=> panther_trim($_POST['form']['url_type']),
		'always_deny'			=> panther_trim($_POST['form']['always_deny']),
		'users_online'			=> $_POST['form']['users_online'] != '1' ? '0' : '1',
		'delete_full'			=> $_POST['form']['delete_full'] != '1' ? '0' : '1',
		'attachments_dir'		=> panther_trim($_POST['form']['attachments_dir']),
		'warnings'				=> $_POST['form']['warnings'] != '1' ? '0' : '1',
		'custom_warnings'		=> $_POST['form']['custom_warnings'] != '1' ? '0' : '1',
		'warning_status'		=> (in_array($_POST['form']['warning_status'], array(0, 1, 2))) ? intval($_POST['form']['warning_status']) : 1,
		'censoring'				=> $_POST['form']['censoring'] != '1' ? '0' : '1',
		'signatures'			=> $_POST['form']['signatures'] != '1' ? '0' : '1',
		'polls'					=> $_POST['form']['polls'] != '1' ? '0' : '1',
		'ranks'					=> $_POST['form']['ranks'] != '1' ? '0' : '1',
		'reputation'			=> $_POST['form']['reputation'] != '1' ? '0' : '1',
		'rep_abuse'				=> (intval($_POST['form']['rep_abuse']) > 0) ? intval($_POST['form']['rep_abuse']) : 5,
		'max_poll_fields'		=> (intval($_POST['form']['max_poll_fields']) > 0) ? intval($_POST['form']['max_poll_fields']) : 20,
		'show_dot'				=> $_POST['form']['show_dot'] != '1' ? '0' : '1',
		'rep_type'				=> (in_array($_POST['form']['rep_type'], array(1, 2, 3))) ? intval($_POST['form']['rep_type']) : 1,
		'use_editor'			=> $_POST['form']['use_editor'] != '1' ? '0' : '1',
		'topic_views'			=> $_POST['form']['topic_views'] != '1' ? '0' : '1',
		'ban_email'				=> $_POST['form']['ban_email'] != '1' ? '0' : '1',
		'quickjump'				=> $_POST['form']['quickjump'] != '1' ? '0' : '1',
		'gzip'					=> $_POST['form']['gzip'] != '1' ? '0' : '1',
		'search_all_forums'		=> $_POST['form']['search_all_forums'] != '1' ? '0' : '1',
		'additional_navlinks'	=> panther_trim($_POST['form']['additional_navlinks']),
		'feed_type'				=> intval($_POST['form']['feed_type']),
		'feed_ttl'				=> intval($_POST['form']['feed_ttl']),
		'report_method'			=> intval($_POST['form']['report_method']),
		'mailing_list'			=> panther_trim($_POST['form']['mailing_list']),
		'avatars'				=> $_POST['form']['avatars'] != '1' ? '0' : '1',
		'avatar_upload'			=> $_POST['form']['avatar_upload'] != '1' ? '0' : '1',
		'task_type'				=> $_POST['form']['task_type'] == '1' && function_exists('exec') && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' ? '1' : '0',
		'avatars_dir'			=> panther_trim($_POST['form']['avatars_dir']),
		'avatars_path'			=> panther_trim($_POST['form']['avatars_path']),
		'image_dir'				=> panther_trim($_POST['form']['image_dir']),
		'image_path'			=> panther_trim($_POST['form']['image_path']),
		'js_dir'				=> panther_trim($_POST['form']['js_dir']),
		'smilies_dir'			=> panther_trim($_POST['form']['smilies_dir']),
		'smilies_path'			=> panther_trim($_POST['form']['smilies_path']),
		'image_group_width'		=> (intval($_POST['form']['image_group_width']) > 0) ? intval($_POST['form']['image_group_width']) : 1,
		'image_group_height'	=> (intval($_POST['form']['image_group_height']) > 0) ? intval($_POST['form']['image_group_height']) : 1,
		'image_group_size'		=> (intval($_POST['form']['image_group_size']) > 0) ? intval($_POST['form']['image_group_size']) : 1,
		'image_group_dir'		=> panther_trim($_POST['form']['image_group_dir']),
		'image_group_path'		=> panther_trim($_POST['form']['image_group_path']),
		'avatars_width'			=> (intval($_POST['form']['avatars_width']) > 0) ? intval($_POST['form']['avatars_width']) : 1,
		'avatars_height'		=> (intval($_POST['form']['avatars_height']) > 0) ? intval($_POST['form']['avatars_height']) : 1,
		'avatars_size'			=> (intval($_POST['form']['avatars_size']) > 0) ? intval($_POST['form']['avatars_size']) : 1,
		'attachment_icon_dir'	=> panther_trim($_POST['form']['attachment_icon_dir']),
		'attachment_icon_path'	=> panther_trim($_POST['form']['attachment_icon_path']),
		'theme'					=> isset($_POST['form']['theme']) ? panther_trim($_POST['form']['theme']) : '',
		'style_path'			=> panther_trim($_POST['form']['style_path']),
		'style_dir'				=> panther_trim($_POST['form']['style_dir']),
		'attachment_icons'		=> $_POST['form']['attachment_icons'] != '1' ? '0' : '1',
		'email_name'			=> panther_trim($_POST['form']['email_name']),
		'admin_email'			=> strtolower(panther_trim($_POST['form']['admin_email'])),
		'webmaster_email'		=> strtolower(panther_trim($_POST['form']['webmaster_email'])),
		'forum_subscriptions'	=> $_POST['form']['forum_subscriptions'] != '1' ? '0' : '1',
		'topic_subscriptions'	=> $_POST['form']['topic_subscriptions'] != '1' ? '0' : '1',
		'smtp_host'				=> panther_trim($_POST['form']['smtp_host']),
		'smtp_user'				=> panther_trim($_POST['form']['smtp_user']),
		'smtp_ssl'				=> $_POST['form']['smtp_ssl'] != '1' ? '0' : '1',
		'regs_allow'			=> $_POST['form']['regs_allow'] != '1' ? '0' : '1',
		'regs_verify'			=> $_POST['form']['regs_verify'] != '1' ? '0' : '1',
		'regs_report'			=> $_POST['form']['regs_report'] != '1' ? '0' : '1',
		'rules'					=> $_POST['form']['rules'] != '1' ? '0' : '1',
		'rules_message'			=> panther_trim($_POST['form']['rules_message']),
		'default_email_setting'	=> intval($_POST['form']['default_email_setting']),
		'announcement'			=> $_POST['form']['announcement'] != '1' ? '0' : '1',
		'announcement_message'	=> panther_trim($_POST['form']['announcement_message']),
		'maintenance'			=> $_POST['form']['maintenance'] != '1' ? '0' : '1',
		'maintenance_message'	=> panther_trim($_POST['form']['maintenance_message']),
	);

	if ($form['board_title'] == '')
		message($lang_admin_options['Must enter title message']);

	$style_root = ($panther_config['o_style_path'] != 'style') ? $panther_config['o_style_path'] : PANTHER_ROOT.$panther_config['o_style_path'];
	if (!file_exists($style_root.'/'.$panther_config['o_default_style'].'/themes/'.$form['theme'].'.css') && $form['theme'] != '')
		$form['theme'] = ''; // If it's not a valid theme, set it to the default

	// Make sure base_url doesn't end with a slash
	if (substr($form['base_url'], -1) == '/')
		$form['base_url'] = substr($form['base_url'], 0, -1);
	
	// Make sure cloudflare domain ends with a trailing slash
	if (substr($form['cloudflare_domain'], -1) != '/' && $form['cloudflare_domain'] != '')
		$form['cloudflare_domain'] .= '/';
	
	$form['email_name'] = panther_trim(preg_replace('/[^a-zA-Z0-9 ]/', '', $form['email_name']));
	if ($form['email_name'] == '')
		message($lang_admin_options['Email name problem']);
	
	if ($form['image_dir'] != '')
	{
		// Make sure this ends with a trailing slash if it's set
		if (substr($form['image_dir'], -1) != '/')
			$form['image_dir'] .= '/';
	}
	else
		$form['image_dir'] = $form['base_url'].'/assets/images/';
	
	if ($form['js_dir'] != '')
	{
		if (substr($form['js_dir'], -1) != '/')
			$form['js_dir'] .= '/';
	}
	else
		$form['js_dir'] = $form['base_url'].'/assets/js/';
	
	if (isset($_FILES['favicon']))
	{
		$favicon = $_FILES['favicon'];
		$favicon_dir = ($panther_config['o_image_dir'] != $panther_config['o_base_url'].'/assets/images/') ? $panther_config['o_image_path'] : PANTHER_ROOT.$panther_config['o_image_path'].'/';

		switch ($favicon['error'])
		{
			case 1:	// UPLOAD_ERR_INI_SIZE
			case 2:	// UPLOAD_ERR_FORM_SIZE
				message($lang_admin_options['Too large ini']);
				break;

			case 3:	// UPLOAD_ERR_PARTIAL
				message($lang_admin_options['Partial upload']);
				break;

			case 4:	// UPLOAD_ERR_NO_FILE
				break;
			case 6:	// UPLOAD_ERR_NO_TMP_DIR
				message($lang_admin_options['No tmp directory']);
				break;

			default:
				break;
		}
		
		if (is_uploaded_file($favicon['tmp_name']))
		{
			$allowed_types = array('image/x-icon', 'image/ico', 'image/png', 'image/jpg', 'image/jpeg', 'image/pjpeg', 'image/gif');
			if (!in_array($favicon['type'], $allowed_types))
				message($lang_admin_options['Bad type']);

			if (!@move_uploaded_file($favicon['tmp_name'], $favicon_dir.'favicon.ico'))
				message($lang_admin_options['Move failed'].' '.$panther_config['o_admin_email']);

			$form['favicon'] = $favicon['name'];
		}
	}

	if (isset($_FILES['avatar']))
	{
		$avatar = $_FILES['avatar'];
		switch ($avatar['error'])
		{
			case 1:	// UPLOAD_ERR_INI_SIZE
			case 2:	// UPLOAD_ERR_FORM_SIZE
				message($lang_admin_options['Too large ini']);
				break;

			case 3:	// UPLOAD_ERR_PARTIAL
				message($lang_admin_options['Partial upload']);
				break;

			case 4:	// UPLOAD_ERR_NO_FILE
				break;
			case 6:	// UPLOAD_ERR_NO_TMP_DIR
				message($lang_admin_options['No tmp directory']);
				break;

			default:
				break;
		}

		if (is_uploaded_file($avatar['tmp_name']))
		{
			$allowed_types = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png');
			if (!in_array($avatar['type'], $allowed_types))
				message($lang_admin_options['Bad type']);
			
			if ($avatar['size'] > $panther_config['o_avatars_size'])
				message(sprintf($lang_admin_options['Too large'], forum_number_format($panther_config['o_avatars_size'])));
			
			$avatar_dir = ($panther_config['o_avatars_dir'] != '') ? $panther_config['o_avatars_path'] : PANTHER_ROOT.$panther_config['o_avatars_path'].'/';

			// Move the file to the avatar directory. We do this before checking the width/height to circumvent open_basedir restrictions
			if (!@move_uploaded_file($avatar['tmp_name'], $avatar_dir.'1.tmp'))
				message($lang_admin_options['Move failed'].' '.$panther_config['o_admin_email']);

			list($width, $height, $type,) = @getimagesize($avatar_dir.'1.tmp');

			// Determine type
			if ($type == IMAGETYPE_GIF)
				$extension = 'gif';
			else if ($type == IMAGETYPE_JPEG)
				$extension = 'jpg';
			else if ($type == IMAGETYPE_PNG)
				$extension = 'png';
			else
			{
				// Invalid type
				@unlink($avatar_dir.'1.tmp');
				message($lang_admin_options['Bad type']);
			}

			// Now check the width/height
			if (empty($width) || empty($height) || $width > $panther_config['o_avatars_width'] || $height > $panther_config['o_avatars_height'])
			{
				@unlink($avatar_dir.'1.tmp');
				message(sprintf($lang_admin_options['Too wide or high'], $panther_config['o_avatars_width'], $panther_config['o_avatars_height']));
			}

			// Delete the old default avatar
			@unlink($avatar_dir.'1.'.$panther_config['o_avatar']);
			@rename($avatar_dir.'1.tmp', $avatar_dir.'1.'.$extension);
			compress_image($avatar_dir.'1.'.$extension);
			@chmod($avatar_dir.'1.'.$extension, 0644);

			$form['avatar'] = $extension;
		}
	}

	// Convert IDN to Punycode if needed
	if (preg_match('/[^\x00-\x7F]/', $form['base_url']))
	{
		if (!function_exists('idn_to_ascii'))
			message($lang_admin_options['Base URL problem']);
		else
			$form['base_url'] = idn_to_ascii($form['base_url']);
	}

	$max_file_size = return_bytes(@ini_get('upload_max_filesize'));
	$max_post_size = return_bytes(@ini_get('post_max_size'));

	$comparison = ($max_file_size > $max_post_size) ? $max_post_size : $max_file_size;
	if ($form['max_upload_size'] > $comparison)
		$form['max_upload_size'] = $comparison;

	$languages = forum_list_langs();
	if (!in_array($form['default_lang'], $languages))
		message($lang_common['Bad request'], false, '404 Not Found');

	$styles = forum_list_styles();
	if (!in_array($form['default_style'], $styles))
		message($lang_common['Bad request'], false, '404 Not Found');

	$schemes = get_url_schemes();
	if (!in_array($form['url_type'], $schemes))
		message($lang_common['Bad request'], false, '404 Not Found');		

	if ($form['time_format'] == '')
		$form['time_format'] = 'H:i:s';

	if ($form['date_format'] == '')
		$form['date_format'] = 'd-m-Y';

	require PANTHER_ROOT.'include/email.php';

	if (!$mailer->is_valid_email($form['admin_email']))
		message($lang_admin_options['Invalid e-mail message']);

	if (!$mailer->is_valid_email($form['webmaster_email']))
		message($lang_admin_options['Invalid webmaster e-mail message']);
	
	if (!$mailer->is_valid_email($form['cloudflare_email']) && $form['cloudflare_email'] != '')
		message($lang_admin_options['Invalid cloudflare e-mail message']);

	if ($form['mailing_list'] != '')
		$form['mailing_list'] = strtolower(preg_replace('%\s%S', '', $form['mailing_list']));

	// Make sure avatars_path ends with a slash
	if (substr($form['avatars_path'], -1) != '/' && $form['avatars_path'] != '')
		$form['avatars_path'] .= '/';

	// Make sure avatars_dir ends with a slash
	if (substr($form['avatars_dir'], -1) != '/' && $form['avatars_dir'] != '')
		$form['avatars_dir'] .= '/';
	
	// Make sure style_path doesn't end with a slash
	if (substr($form['style_path'], -1) == '/')
		$form['style_path'] = substr($form['style_path'], 0, -1);

	// Make sure style_dir ends with a slash
	if (substr($form['style_dir'], -1) != '/' && $form['style_dir'] != '')
		$form['style_dir'] .= '/';

	// Make sure smilies_path doesn't end with a slash
	if (substr($form['smilies_path'], -1) == '/')
		$form['smilies_path'] = substr($form['smilies_path'], 0, -1);

	// Make sure smilies_dir ends with a slash
	if (substr($form['smilies_dir'], -1) != '/' && $form['smilies_dir'] != '')
		$form['smilies_dir'] .= '/';
	
	// Make sure image_group_path ends with a slash
	if (substr($form['image_group_path'], -1) != '/' && $form['image_group_path'] != '')
		$form['image_group_path'] .= '/';

	// Make sure image_group_dir ends with a slash
	if (substr($form['image_group_dir'], -1) != '/' && $form['image_group_dir'] != '')
		$form['image_group_dir'] .= '/';
	
	// Make sure image_path doesn't end with a slash
	if (substr($form['image_path'], -1) == '/')
		$form['image_path'] = substr($form['image_path'], 0, -1);

	// Make sure attachment_icon_path doesn't end with a slash
	if (substr($form['attachment_icon_path'], -1) == '/')
		$form['attachment_icon_path'] = substr($form['attachment_icon_path'], 0, -1);

	// Make sure attachment_icon_dir ends with a slash
	if (substr($form['attachment_icon_dir'], -1) != '/' && $form['attachment_icon_dir'] != '')
		$form['attachment_icon_dir'] .= '/';

	if ($form['additional_navlinks'] != '')
		$form['additional_navlinks'] = panther_trim(panther_linebreaks($form['additional_navlinks']));

	// Change or enter a SMTP password
	if (isset($_POST['form']['smtp_change_pass']))
	{
		$smtp_pass1 = isset($_POST['form']['smtp_pass1']) ? panther_trim($_POST['form']['smtp_pass1']) : '';
		$smtp_pass2 = isset($_POST['form']['smtp_pass2']) ? panther_trim($_POST['form']['smtp_pass2']) : '';

		if ($smtp_pass1 == $smtp_pass2)
			$form['smtp_pass'] = $smtp_pass1;
		else
			message($lang_admin_options['SMTP passwords did not match']);
	}

	if ($form['announcement_message'] != '')
		$form['announcement_message'] = panther_linebreaks($form['announcement_message']);
	else
	{
		$form['announcement_message'] = $lang_admin_options['Enter announcement here'];
		$form['announcement'] = '0';
	}

	if ($form['rules_message'] != '')
		$form['rules_message'] = panther_linebreaks($form['rules_message']);
	else
	{
		$form['rules_message'] = $lang_admin_options['Enter rules here'];
		$form['rules'] = '0';
	}

	if ($form['maintenance_message'] != '')
		$form['maintenance_message'] = panther_linebreaks($form['maintenance_message']);
	else
	{
		$form['maintenance_message'] = $lang_admin_options['Default maintenance message'];
		$form['maintenance'] = '0';
	}

	// Make sure the number of displayed topics and posts is between 3 and 75
	if ($form['disp_topics_default'] < 3)
		$form['disp_topics_default'] = 3;
	else if ($form['disp_topics_default'] > 75)
		$form['disp_topics_default'] = 75;

	if ($form['disp_posts_default'] < 3)
		$form['disp_posts_default'] = 3;
	else if ($form['disp_posts_default'] > 75)
		$form['disp_posts_default'] = 75;

	if ($form['feed_type'] < 0 || $form['feed_type'] > 2)
		message($lang_common['Bad request'], false, '404 Not Found');

	if ($form['feed_ttl'] < 0)
		message($lang_common['Bad request'], false, '404 Not Found');

	if ($form['report_method'] < 0 || $form['report_method'] > 2)
		message($lang_common['Bad request'], false, '404 Not Found');

	if ($form['default_email_setting'] < 0 || $form['default_email_setting'] > 2)
		message($lang_common['Bad request'], false, '404 Not Found');

	if ($form['timeout_online'] >= $form['timeout_visit'])
		message($lang_admin_options['Timeout error message']);

	if ($form['archiving'] == 0 && $panther_config['o_archiving'] != $form['archiving'])	// If we've disabled archiving then we need to unarchive every topic
	{
		$update = array(
			'archived'	=>	0,
		);

		$db->update('topics', $update);
	}

	foreach ($form as $key => $input)
	{
		// Only update values that have changed
		if (array_key_exists('o_'.$key, $panther_config) && $panther_config['o_'.$key] != $input)
		{
			if ($input != '' || is_int($input))
				$value = $input;
			else
				$value = null;
			
			$update = array(
				'conf_value'	=>	$value,
			);

			$data = array(
				':conf_name'	=>	'o_'.$key,
			);

			$db->update('config', $update, 'conf_name=:conf_name', $data);
		}
	}

	// Regenerate the config cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_config_cache();
	clear_feed_cache();
	
	if ($form['url_type'] != $panther_config['o_url_type'])
	{
		//Load new URL pack to avoid 404 error after redirecting
		if (file_exists(PANTHER_ROOT.'include/url/'.$form['url_type'].'.php'))
			require PANTHER_ROOT.'include/url/'.$form['url_type'].'.php';
		else
			require PANTHER_ROOT.'include/url/default.php';
		
		generate_quickjump_cache();
	}

	redirect(panther_link($panther_url['admin_options']), $lang_admin_options['Options updated redirect']);
}

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Options']);
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

generate_admin_menu('options');

$diff = ($panther_user['timezone'] + $panther_user['dst']) * 3600;
$timestamp = time() + $diff;

$schemes = get_url_schemes();
$scheme_options = array();
foreach ($schemes as $scheme)
	$scheme_options[] = array('file' => $scheme, 'title' => substr(ucwords(str_replace('_', ' ', $scheme)), 0, -4));

$tpl = load_template('admin_options.tpl');
echo $tpl->render(
	array(
		'lang_admin_options' => $lang_admin_options,
		'lang_admin_common' => $lang_admin_common,
		'panther_config' => $panther_config,
		'form_action' => panther_link($panther_url['admin_options']),
		'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/options.php'),
		'max_file_size' => $max_file_size,
		'types' => $scheme_options,
		'languages' => forum_list_langs(),
		'styles' => forum_list_styles(),
		'time_format' => gmdate($panther_config['o_time_format'], $timestamp),
		'date_format' => gmdate($panther_config['o_date_format'], $timestamp),
		'censoring_link' => panther_link($panther_url['admin_censoring']),
		'archive_link' => panther_link($panther_url['admin_archive']),
		'ranks_link' => panther_link($panther_url['admin_ranks']),
		'tasks_link' => panther_link($panther_url['admin_tasks']),
		'feeds' => array(5, 15, 30, 60),
		'smtp_pass' => !empty($panther_config['o_smtp_pass']) ? random_key(panther_strlen($panther_config['o_smtp_pass']), true) : '',
		'themes' => forum_list_themes(),
	)
);

require PANTHER_ROOT.'footer.php';