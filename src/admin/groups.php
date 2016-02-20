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
	if(!is_null($admins[$panther_user['id']]['admin_permissions']))
	{
		if ($admins[$panther_user['id']]['admin_permissions'] == '0')
			message($lang_common['No permission'], false, '403 Forbidden');
	}
}

check_authentication();

// Load the admin_censoring.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_groups.php';

$action = isset($_GET['action']) ? $_GET['action'] : null;
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($action == 'upload_image')
{
	if (!isset($panther_groups[$id]))
		message($lang_common['Bad request']);

	$image_path = ($panther_config['o_image_group_dir'] != '') ? $panther_config['o_image_group_path'] : PANTHER_ROOT.$panther_config['o_image_group_path'].'/';

	if (isset($_POST['form_sent']))
	{
		confirm_referrer(PANTHER_ADMIN_DIR.'/groups.php');

		if (!isset($_FILES['req_file']))
			message($lang_admin_groups['No file']);

		$uploaded_file = $_FILES['req_file'];

		// Make sure the upload went smooth
		if (isset($uploaded_file['error']))
		{ 
			switch ($uploaded_file['error'])
			{
				case 1:	// UPLOAD_ERR_INI_SIZE
				case 2:	// UPLOAD_ERR_FORM_SIZE
					message($lang_admin_groups['Too large ini']);
					break;

				case 3:	// UPLOAD_ERR_PARTIAL
					message($lang_admin_groups['Partial upload']);
					break;

				case 4:	// UPLOAD_ERR_NO_FILE
					message($lang_admin_groups['No file']);
					break;

				case 6:	// UPLOAD_ERR_NO_TMP_DIR 
					message($lang_admin_groups['No tmp directory']);
					break;

				default:
					// No error occured, but was something actually uploaded?
					if ($uploaded_file['size'] == 0)
						message($lang_admin_groups['No file']);
					break;
			}
		}

		if (is_uploaded_file($uploaded_file['tmp_name']))
		{
			$allowed_types = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png');
			if (!in_array($uploaded_file['type'], $allowed_types))
				message($lang_admin_groups['Bad type']);

			// Make sure the file isn't too big
			if ($uploaded_file['size'] > $panther_config['o_image_group_size'])
				message($lang_admin_groups['Too large'].' '.$panther_config['o_image_group_size'].' '.$lang_admin_groups['bytes'].'.');

			// Determine type
			switch($uploaded_file['type'])
			{
				case 'image/gif':
					$type = 'gif';
					break;
				case 'image/jpeg':
				case 'image/pjpeg':
					$type = 'jpg';
					break;
				default:
					$type = 'png';
					break;
			}

			// Move the file to the image directory. We do this before checking the width/height to circumvent open_basedir restrictions.
			if (!@move_uploaded_file($uploaded_file['tmp_name'], $image_path.$id.'.tmp'))
				message(sprintf($lang_admin_groups['Move failed'], ' '.$panther_config['o_admin_email']));

			// Now check the width/height
			list($width, $height, $file_type,) = getimagesize($image_path.$id.'.tmp');
			if (empty($width) || empty($height) || $width > $panther_config['o_image_group_width'] || $height > $panther_config['o_image_group_height'])
			{
				@unlink($image_path.$id.'.tmp');
				message(sprintf($lang_admin_groups['Too wide or high'], $panther_config['o_image_group_width'], $panther_config['o_image_group_height']));
			}
			else if ($file_type == 1 && $uploaded_file['type'] != 'image/gif')	// Prevent dodgy uploads
			{
				@unlink($image_path.$id.'.tmp');
				message($lang_admin_groups['Bad type']);
			}

			// Delete the old image (if it exists) and put the new one in place
			if ($panther_groups[$id]['g_image'] != '')
				@unlink($image_path.$id.'.'.$panther_groups[$id]['g_image']);

			@rename($image_path.$id.'.tmp', $image_path.$id.'.'.$type);
			compress_image($image_path.$id.'.'.$type);
			@chmod($image_path.$id.'.'.$type, 0644);

			$update = array(
				'g_image'	=>	$type,
			);

			$data = array(
				':id'	=>	$id,
			);

			$db->update('groups', $update, 'g_id=:id', $data);
		}
		else
			message($lang_admin_image_group['Unknown failure']);

		if (!defined('FORUM_CACHE_FUNCITONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';
	
		generate_groups_cache();
		redirect(panther_link($panther_url['admin_groups']), $lang_admin_groups['Image upload redirect']);
	}

	$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['User groups']);
	$required_fields = array('req_file' => $lang_admin_groups['File']);
	$focus_element = array('upload_image', 'req_file');
	define('PANTHER_ACTIVE_PAGE', 'admin');
	require PANTHER_ROOT.'header.php';
	generate_admin_menu('groups');

	$tpl = load_template('upload_image.tpl');
	echo $tpl->render(
		array(
			'lang_admin_groups' => $lang_admin_groups,
			'lang_common' => $lang_common,
			'form_action' => panther_link($panther_url['upload_image'], array($id)),
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/groups.php'),
			'panther_config' => $panther_config,
			'size' => sprintf($panther_config['o_image_group_size'], $lang_common['Size unit B']),
			'size_unit' => sprintf(ceil($panther_config['o_image_group_size'] / 1024), $lang_common['Size unit KiB']),
		)
	);

	require PANTHER_ROOT.'footer.php';
}
else if ($action == 'delete_image')
{
	if (!isset($panther_groups[$id]))
		message($lang_common['Bad request']);

	if ($panther_groups[$id]['g_image'] == '')
		message($lang_common['Bad request']);

	$image_path = ($panther_config['o_image_group_dir'] != '') ? $panther_config['o_image_group_path'] : PANTHER_ROOT.$panther_config['o_image_group_path'].'/';
	@unlink($image_path.$id.'.'.$panther_groups[$id]['g_image']);

	$update = array(
		'g_image'	=>	'',
	);

	$data = array(
		':id'	=>	$id,
	);

	$db->update('groups', $update, 'g_id=:id', $data);
	
	if (!defined('FORUM_CACHE_FUNCITONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_groups_cache();
	redirect(panther_link($panther_url['admin_groups']), $lang_admin_groups['Image deleted redirect']);
}

// Add/edit a group (stage 1)
if (isset($_POST['add_group']) || isset($_GET['edit_group']))
{
	if (isset($_POST['add_group']))
	{
		$group_id = isset($_POST['base_group']) ? intval($_POST['base_group']) : '';
		$group = $panther_groups[$group_id];

		$mode = 'add';
	}
	else // We are editing a group
	{
		$group_id = intval($_GET['edit_group']);
		if ($group_id < 1 || !isset($panther_groups[$group_id]))
			message($lang_common['Bad request'], false, '404 Not Found');

		$group = $panther_groups[$group_id];
		$mode = 'edit';
	}

	$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['User groups']);
	$required_fields = array('req_title' => $lang_admin_groups['Group title label']);
	$focus_element = array('groups2', 'req_title');
	define('PANTHER_ACTIVE_PAGE', 'admin');
	require PANTHER_ROOT.'header.php';
	
	generate_admin_menu('groups');

	$group_options = array();
	foreach ($panther_groups as $cur_group)
	{
		if (($cur_group['g_id'] != $group['g_id'] || $mode == 'add') && $cur_group['g_id'] != PANTHER_ADMIN && $group['g_admin'] != '1' && $cur_group['g_id'] != PANTHER_GUEST)
			$group_options[] = array('id' => $cur_group['g_id'], 'title' => $cur_group['g_title']);
	}
	
	$img_size = array();
	if ($mode == 'edit' && $group['g_image'] != '')
		$img_size = @getimagesize($panther_config['o_image_group_path'].'/'.$group_id.'.'.$group['g_image']);

	$tpl = load_template('edit_group.tpl');
	echo $tpl->render(
		array(
			'lang_admin_groups' => $lang_admin_groups,
			'lang_admin_common' => $lang_admin_common,
			'form_action' => panther_link($panther_url['admin_groups']),
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/groups.php'),
			'mode' => $mode,
			'group_id' => $group_id,
			'group' => $group,
			'lang' => ($group['g_id'] != PANTHER_GUEST ? $lang_common['Member'] : $lang_common['Guest']),
			'is_not_admin_group' => $group['g_id'] != PANTHER_ADMIN ? true : false,
			'robots_link' => panther_link($panther_url['admin_robots']),
			'is_not_guest_group' => ($group['g_id'] != PANTHER_GUEST) ? true : false,
			'group_options' => $group_options,
			'upload_link' => panther_link($panther_url['upload_image'], array($group_id)),
			'img_size' => $img_size,
			'delete_link' => panther_link($panther_url['delete_image'], array($group_id)),
			'image_dir' => ($panther_config['o_image_group_dir'] != '') ? $panther_config['o_image_group_dir'] : get_base_url().'/'.$panther_config['o_image_group_path'].'/',
		)
	);
	require PANTHER_ROOT.'footer.php';
}
else if (isset($_POST['add_edit_group'])) // Add/edit a group (stage 2)
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/groups.php');

	// Is this the original admin group? (special rules apply)
	$is_admin_group = (isset($_POST['group_id']) && $_POST['group_id'] == PANTHER_ADMIN) ? true : false;

	$title = isset($_POST['req_title']) ? panther_trim($_POST['req_title']) : '';
	$user_title = isset($_POST['user_title']) ? panther_trim($_POST['user_title']) : '';
	$group_colour = isset($_POST['group_colour']) ? panther_trim($_POST['group_colour']) : '';

	$promote_min_posts = isset($_POST['promote_min_posts']) ? intval($_POST['promote_min_posts']) : 0;
	$promote_next_group = (isset($_POST['promote_next_group']) && isset($panther_groups[$_POST['promote_next_group']]) && !in_array($_POST['promote_next_group'], array(PANTHER_ADMIN, PANTHER_GUEST)) && $panther_groups[$_POST['promote_next_group']]['g_admin'] != '1' && (!isset($_POST['group_id']) || $_POST['promote_next_group'] != $_POST['group_id'])) ? $_POST['promote_next_group'] : 0;

	$moderator = isset($_POST['moderator']) && $_POST['moderator'] == '1' ? '1' : '0';
	$global_moderator = $moderator == '1' && isset($_POST['global_moderator']) && $_POST['global_moderator'] == '1' ? '1' : '0';
	$mod_cp = $moderator == '1' && isset($_POST['mod_cp']) && $_POST['mod_cp'] == '1' ? '1' : '0';
	$admin = $moderator == '1' && isset($_POST['admin']) && $_POST['admin'] == '1' ? '1' : '0';
	$mod_edit_users = $moderator == '1' && isset($_POST['mod_edit_users']) && $_POST['mod_edit_users'] == '1' ? '1' : '0';
	$mod_rename_users = $moderator == '1' && isset($_POST['mod_rename_users']) && $_POST['mod_rename_users'] == '1' ? '1' : '0';
	$mod_change_passwords = $moderator == '1' && isset($_POST['mod_change_passwords']) && $_POST['mod_change_passwords'] == '1' ? '1' : '0';
	$mod_ban_users = $moderator == '1' && isset($_POST['mod_ban_users']) && $_POST['mod_ban_users'] == '1' ? '1' : '0';
	$mod_warn_users = $moderator == '1' && isset($_POST['mod_warn_users']) && $_POST['mod_warn_users'] == '1' ? '1' : '0';
	$mod_promote_users = $moderator == '1' && isset($_POST['mod_promote_users']) && $_POST['mod_promote_users'] == '1' ? '1' : '0';
	$read_board = isset($_POST['read_board']) ? intval($_POST['read_board']) : '1';
	$view_users = (isset($_POST['view_users']) && $_POST['view_users'] == '1') || $is_admin_group ? '1' : '0';
	$post_replies = isset($_POST['post_replies']) ? intval($_POST['post_replies']) : '1';
	$post_polls = isset($_POST['post_polls']) ? intval($_POST['post_polls']) : '1';
	$moderate_posts = isset($_POST['moderate_posts']) ? intval($_POST['moderate_posts']) : '0';
	$post_topics = isset($_POST['post_topics']) ? intval($_POST['post_topics']) : '1';
	$robot_test = isset($_POST['robot_test']) ? intval($_POST['robot_test']) : '0';
	$attach_files = isset($_POST['attach_files']) ? intval($_POST['attach_files']) : '0';
	$max_attachments = isset($_POST['max_attachments']) ? intval($_POST['max_attachments']) : '0';
	$max_size = isset($_POST['max_size']) ? intval($_POST['max_size']) : '0';
	$edit_posts = isset($_POST['edit_posts']) ? intval($_POST['edit_posts']) : ($is_admin_group) ? '1' : '0';
	$edit_subject = isset($_POST['edit_subject']) ? intval($_POST['edit_subject']) : ($is_admin_group) ? '1' : '0';
	$delete_posts = isset($_POST['delete_posts']) ? intval($_POST['delete_posts']) : ($is_admin_group) ? '1' : '0';
	$delete_topics = isset($_POST['delete_topics']) ? intval($_POST['delete_topics']) : ($is_admin_group) ? '1' : '0';
	$deledit_interval = isset($_POST['deledit_interval']) ? intval($_POST['deledit_interval']) : 0;
	$post_links = isset($_POST['post_links']) ? intval($_POST['post_links']) : '1';
	$set_title = isset($_POST['set_title']) ? intval($_POST['set_title']) : ($is_admin_group) ? '1' : '0';
	$search = isset($_POST['search']) ? intval($_POST['search']) : '1';
	$search_users = isset($_POST['search_users']) ? intval($_POST['search_users']) : '1';
	$send_email = (isset($_POST['send_email']) && $_POST['send_email'] == '1') || $is_admin_group ? '1' : '0';
	$post_flood = (isset($_POST['post_flood']) && $_POST['post_flood'] >= 0) ? intval($_POST['post_flood']) : '0';
	$search_flood = (isset($_POST['search_flood']) && $_POST['search_flood'] >= 0) ? intval($_POST['search_flood']) : '0';
	$email_flood = (isset($_POST['email_flood']) && $_POST['email_flood'] >= 0) ? intval($_POST['email_flood']) : '0';
	$report_flood = (isset($_POST['report_flood']) && $_POST['report_flood'] >= 0) ? intval($_POST['report_flood']) : '0';
	$reputation = isset($_POST['rep_enabled']) && intval($_POST['rep_enabled']) == '1' || $is_admin_group ? '1' : '0';
	$reputation_max = isset($_POST['g_rep_plus']) ? intval($_POST['g_rep_plus']) : '0';
	$reputation_min = isset($_POST['g_rep_minus']) ? intval($_POST['g_rep_minus']) : '0';
	$reputation_interval = isset($_POST['g_rep_interval']) ? intval($_POST['g_rep_interval']) : '0';
	$use_pm = isset($_POST['use_pm']) && $_POST['use_pm'] == '1' || $is_admin_group ? '1' : '0';
	$pm_limit = isset($_POST['pm_limit']) ? intval($_POST['pm_limit']) : '0';
	$folder_limit = isset($_POST['pm_folder_limit']) ? intval($_POST['pm_folder_limit']) : '0';

	if ($title == '')
		message($lang_admin_groups['Must enter title message']);

	if (!empty($group_colour) && !preg_match('/^#([a-fA-F0-9]){6}$/', $group_colour))
		message($lang_admin_groups['Invalid colour message']);
	
	$max_size = ($max_size > $panther_config['o_max_upload_size']) ? $panther_config['o_max_upload_size'] : $max_size;
	$user_title = ($user_title != '') ? $user_title : null;

	if ($_POST['mode'] == 'add')
	{
		$data = array(
			':title'	=>	$title,
		);

		$ps = $db->select('groups', 1, $data, 'g_title=:title');
		if ($ps->rowCount())
			message(sprintf($lang_admin_groups['Title already exists message'], $title));

		$insert = array(
			'g_title'				=>	$title,
			'g_user_title'			=>	$user_title,
			'g_promote_min_posts'	=>	$promote_min_posts,
			'g_promote_next_group'	=>	$promote_next_group,
			'g_moderator'			=>	$moderator,
			'g_mod_cp'				=>	$mod_cp,
			'g_admin'				=>	$admin,
			'g_global_moderator'	=>	$global_moderator,
			'g_mod_edit_users'		=>	$mod_edit_users,
			'g_mod_rename_users'	=>	$mod_rename_users,
			'g_mod_change_passwords'=>	$mod_change_passwords,
			'g_mod_warn_users'		=>	$mod_warn_users,
			'g_mod_ban_users'		=>	$mod_ban_users,
			'g_mod_promote_users'	=>	$mod_promote_users,
			'g_read_board'			=>	$read_board,
			'g_view_users'			=>	$view_users,
			'g_post_replies'		=>	$post_replies,
			'g_post_polls'			=>	$post_polls,
			'g_post_topics'			=>	$post_topics,
			'g_edit_posts'			=>	$edit_posts,
			'g_robot_test'			=>	$robot_test,
			'g_edit_subject'		=>	$edit_subject,
			'g_delete_posts'		=>	$delete_posts,
			'g_delete_topics'		=>	$delete_topics,
			'g_deledit_interval'	=>	$deledit_interval,
			'g_post_links'			=>	$post_links,
			'g_set_title'			=>	$set_title,
			'g_search'				=>	$search,
			'g_search_users'		=>	$search_users,
			'g_send_email'			=>	$send_email,
			'g_post_flood'			=>	$post_flood,
			'g_search_flood'		=>	$search_flood,
			'g_email_flood'			=>	$email_flood,
			'g_report_flood'		=>	$report_flood,
			'g_rep_enabled'			=>	$reputation,
			'g_rep_interval'		=>	$reputation_interval,
			'g_rep_plus'			=>	$reputation_max,
			'g_rep_minus'			=>	$reputation_min,
			'g_colour'				=>	$group_colour,
			'g_moderate_posts'		=>	$moderate_posts,
			'g_attach_files'		=>	$attach_files,
			'g_max_attachments'		=>	$max_attachments,
			'g_max_size'			=>	$max_size,
			'g_use_pm'				=>	$use_pm,
			'g_pm_limit'			=>	$pm_limit,
			'g_pm_folder_limit'		=>	$folder_limit,
		);

		$db->insert('groups', $insert);
		$new_group_id = $db->lastInsertId($db->prefix.'groups');

		$data = array(
			':id'	=>	isset($_POST['base_group']) ? intval($_POST['base_group']) : 0,
		);

		// Now let's copy the forum specific permissions from the group which this group is based on
		$ps = $db->select('forum_perms', 'forum_id, read_forum, post_replies, post_topics', $data, 'group_id=:id');

		foreach ($ps as $cur_forum_perm)
		{
			$insert = array(
				'group_id'	=>	$new_group_id,
				'forum_id'	=>	$cur_forum_perm['forum_id'],
				'read_forum'	=>	$cur_forum_perm['read_forum'],
				'post_replies'	=>	$cur_forum_perm['post_replies'],
				'post_topics'	=>	$cur_forum_perm['post_topics'],
			);

			$db->insert('forum_perms', $insert);
		}
	}
	else
	{
		$group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
		$data = array(
			':g_title'	=>	$title,
			':g_id'		=>	$group_id,
		);

		$ps = $db->select('groups', 1, $data, 'g_title=:g_title AND g_id!=:g_id');
		if ($ps->rowCount())
			message(sprintf($lang_admin_groups['Title already exists message'], $title));

		$update = array(
			'g_title'	=>	$title,
			'g_user_title'	=>	$user_title,
			'g_promote_min_posts'	=>	$promote_min_posts,
			'g_promote_next_group'	=>	$promote_next_group,
			'g_moderator'			=>	$moderator,
			'g_mod_cp'				=>	$mod_cp,
			'g_admin'				=>	$admin,
			'g_global_moderator'	=>	$global_moderator,
			'g_mod_edit_users'		=>	$mod_edit_users,
			'g_mod_rename_users'	=>	$mod_rename_users,
			'g_mod_change_passwords'=>	$mod_change_passwords,
			'g_mod_ban_users'		=>	$mod_ban_users,
			'g_mod_warn_users'		=>	$mod_warn_users,
			'g_mod_promote_users'	=>	$mod_promote_users,
			'g_read_board'			=>	$read_board,
			'g_view_users'			=>	$view_users,
			'g_post_replies'		=>	$post_replies,
			'g_post_polls'			=>	$post_polls,
			'g_post_topics'			=>	$post_topics,
			'g_robot_test'			=>	$robot_test,
			'g_edit_posts'			=>	$edit_posts,
			'g_edit_subject'		=>	$edit_subject,
			'g_delete_posts'		=>	$delete_posts,
			'g_delete_topics'		=>	$delete_topics,
			'g_deledit_interval'	=>	$deledit_interval,
			'g_post_links'			=>	$post_links,
			'g_set_title'			=>	$set_title,
			'g_search'				=>	$search,
			'g_search_users'		=>	$search_users,
			'g_send_email'			=>	$send_email,
			'g_post_flood'			=>	$post_flood,
			'g_search_flood'		=>	$search_flood,
			'g_email_flood'			=>	$email_flood,
			'g_report_flood'		=>	$report_flood,
			'g_rep_enabled'			=>	$reputation,
			'g_rep_interval'		=>	$reputation_interval,
			'g_rep_plus'			=>	$reputation_max,
			'g_rep_minus'			=>	$reputation_min,
			'g_colour'				=>	$group_colour,
			'g_moderate_posts'		=>	$moderate_posts,
			'g_attach_files'		=>	$attach_files,
			'g_max_attachments'		=>	$max_attachments,
			'g_max_size'			=>	$max_size,
			'g_use_pm'				=>	$use_pm,
			'g_pm_limit'			=>	$pm_limit,
			'g_pm_folder_limit'		=>	$folder_limit,
		);
		
		$data = array(
			':id'	=>	$group_id,
		);
		
		$db->update('groups', $update, 'g_id=:id', $data);

		// Promote all users who would be promoted to this group on their next post
		if ($promote_next_group)
		{
			$update = array(
				'group_id'	=>	$promote_next_group,
			);
			
			$data = array(
				':num_posts'	=>	$promote_min_posts,
				':gid'			=>	$group_id,
			);
	
			$db->update('users', $update, 'group_id=:gid AND num_posts>=:num_posts', $data);
		}
	}

	// Regenerate the quick jump cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_groups_cache();
	generate_config_cache();

	$group_id = $_POST['mode'] == 'add' ? $new_group_id : intval($_POST['group_id']);
	generate_quickjump_cache($group_id, $read_board);

	$redirect_msg = ($_POST['mode'] == 'edit') ? $lang_admin_groups['Group edited redirect'] : $lang_admin_groups['Group added redirect'];
	redirect(panther_link($panther_url['admin_groups']), $redirect_msg);
}
else if (isset($_POST['set_default_group'])) // Set default group
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/groups.php');
	$group_id = isset($_POST['default_group']) ? intval($_POST['default_group']) : 0;

	// Make sure it's not the admin or guest groups
	if ($group_id == PANTHER_ADMIN || $group_id == PANTHER_GUEST)
		message($lang_common['Bad request'], false, '404 Not Found');

	// Make sure it's not a moderator group
	if ($panther_groups[$group_id]['g_moderator'] != 0)
		message($lang_common['Bad request'], false, '404 Not Found');

	$update = array(
		'conf_value' => $group_id,
	);
	
	$data = array(
		'conf_name' => 'o_default_user_group',
	);

	$db->update('config', $update);

	// Regenerate the config cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_config_cache();
	redirect(panther_link($panther_url['admin_groups']), $lang_admin_groups['Default group redirect']);
}
else if (isset($_GET['delete_group'])) // Remove a group
{
	$group_id = isset($_POST['group_to_delete']) ? intval($_POST['group_to_delete']) : intval($_GET['delete_group']);
	if ($group_id < 5)
		message($lang_common['Bad request'], false, '404 Not Found');

	// Make sure we don't remove the default group
	if ($group_id == $panther_config['o_default_user_group'])
		message($lang_admin_groups['Cannot remove default message']);

	$data = array(
		':gid'	=>	$group_id,
	);

	// Check if this group has any members
	$ps = $db->run('SELECT g.g_title, COUNT(u.id) FROM '.$db->prefix.'groups AS g INNER JOIN '.$db->prefix.'users AS u ON g.g_id=u.group_id WHERE g.g_id=:gid GROUP BY g.g_id, g_title', $data);

	// If the group doesn't have any members or if we've already selected a group to move the members to
	if (!$ps->rowCount() || isset($_POST['del_group']))
	{
		if (isset($_POST['del_group_comply']) || isset($_POST['del_group']))
		{
			confirm_referrer(PANTHER_ADMIN_DIR.'/groups.php');
			if (isset($_POST['del_group']))
			{
				$move_to_group = intval($_POST['move_to_group']);
				$update = array(
					':gid'	=>	$move_to_group,
				);

				$data = array(
					':gid2'	=>	$group_id,
				);

				$db->update('users', $update, 'group_id=:gid2', $data);
			}

			$data = array(
				':gid'	=>	$group_id,
			);

			// Delete the group and any forum specific permissions
			$db->delete('groups', 'g_id=:gid', $data);
			$db->delete('forum_perms', 'group_id=:gid', $data);

			// Don't let users be promoted to this group
			$update = array(
				'g_promote_next_group' => 0,
			);

			$db->update('groups', $update, 'g_promote_next_group=:gid', $data);

			if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
				require PANTHER_ROOT.'include/cache.php';

			generate_groups_cache();
			redirect(panther_link($panther_url['admin_groups']), $lang_admin_groups['Group removed redirect']);
		}
		else
		{
			$group_title = $panther_groups[$group_id]['g_title'];

			$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['User groups']);
			define('PANTHER_ACTIVE_PAGE', 'admin');
			require PANTHER_ROOT.'header.php';

			generate_admin_menu('groups');

			$tpl = load_template('delete_group.tpl');
			echo $tpl->render(
				array(
					'lang_admin_groups' => $lang_admin_groups,
					'lang_admin_common' => $lang_admin_common,
					'form_action' => panther_link($panther_url['del_group'], array($group_id)),
					'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/groups.php'),
					'group_id' => $group_id,
					'group_title' => $group_title,
				)
			);

			require PANTHER_ROOT.'footer.php';
		}
	}

	list($group_title, $group_members) = $ps->fetch(PDO::FETCH_NUM);

	$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['User groups']);
	define('PANTHER_ACTIVE_PAGE', 'admin');
	require PANTHER_ROOT.'header.php';

	generate_admin_menu('groups');

	$group_options = array();
	foreach ($panther_groups as $cur_group)
	{
		if ($cur_group['g_id'] != PANTHER_GUEST && $cur_group['g_id'] != $group_id)
			$group_options[] = array('id' => $cur_group['g_id'], 'selected' => (($cur_group['g_id'] == PANTHER_MEMBER) ? true : false), 'title' => $cur_group['g_title']);
	}

	$tpl = load_templates('move_group.tpl');
	echo $tpl->render(
		array(
			'lang_admin_groups' => $lang_admin_groups,
			'lang_admin_common' => $lang_admin_common,
			'form_action' => panther_link($panther_url['del_group'], array($group_id)),
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/groups.php'),
			'group_id' => $group_id,
			'group_title' => $group_title,
			'group_members' => forum_number_format($group_members),
			'group_options' => $group_options,
		)
	);

	require PANTHER_ROOT.'footer.php';
}

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['User groups']);
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

generate_admin_menu('groups');

$group_options = $new_options = $default_options = array();
foreach ($panther_groups as $cur_group)
{
	if ($cur_group['g_id'] != PANTHER_ADMIN && $cur_group['g_id'] != PANTHER_GUEST)
		$new_options[] = array('id' => $cur_group['g_id'], 'title' => $cur_group['g_title']);

	if ($cur_group['g_id'] > PANTHER_GUEST && $cur_group['g_moderator'] == 0)
		$default_options[] = array('id' => $cur_group['g_id'], 'title' => $cur_group['g_title']);

	$group_options[] = array(
		'edit_link' => panther_link($panther_url['edit_group'], $cur_group['g_id']),
		'delete_link' => panther_link($panther_url['del_group'], $cur_group['g_id']),
		'title' => $cur_group['g_title'],
		'can_delete' => ($cur_group['g_id'] > PANTHER_MEMBER ? true : false),
	);
}

$tpl = load_template('admin_groups.tpl');
echo $tpl->render(
	array(
		'lang_admin_groups' => $lang_admin_groups,
		'lang_admin_common' => $lang_admin_common,
		'form_action' => panther_link($panther_url['admin_groups']),
		'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/groups.php'),
		'group_options' => $group_options,
		'default_options' => $default_options,
		'new_options' => $new_options,
	)
);

require PANTHER_ROOT.'footer.php';