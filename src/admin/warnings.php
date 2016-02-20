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
	if(!is_null($admins[$panther_user['id']]['admin_warnings']))
	{
		if ($admins[$panther_user['id']]['admin_warnings'] == '0')
			message($lang_common['No permission'], false, '403 Forbidden');
	}
}

check_authentication();

// Load the admin_warnings.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_warnings.php';

if ($panther_user['g_read_board'] == '0')
	message($lang_common['No view'], false, '403 Forbidden');

if ($panther_config['o_warnings'] == '0')
	message($lang_warnings['Warnings disabled']);

if (isset($_POST['form_sent']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/warnings.php');
	$action = isset($_POST['action']) ? panther_trim($_POST['action']) : '';
	$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
	
	if ($action == '')
		message($lang_common['Bad request']);

	if ($action == 'types')
	{
		if (empty($_POST['warning_title']))
			message($lang_warnings['No title']);

		// Determine expiration time
		$expiration_time  = get_expiration_time($_POST['expiration_time'], $_POST['expiration_unit']);
		
		$warning_title = isset($_POST['warning_title']) ? panther_trim($_POST['warning_title']) : '';
		$warning_description = isset($_POST['warning_description']) ? panther_trim($_POST['warning_description']) : '';
		$points = isset($_POST['warning_points']) ? intval($_POST['warning_points']) : 0;

		if (strlen($warning_title) < 1)
			message($lang_warnings['No title']);
		else if (strlen($warning_title) > 70)
			message($lang_warnings['Title too long']);

		if ($warning_description == '')
			message($lang_warnings['Must enter descripiton']);
		else if (panther_strlen($warning_description) > PANTHER_MAX_POSTSIZE)
			message(sprintf($lang_warnings['Must enter descripiton'], forum_number_format(PANTHER_MAX_POSTSIZE)));
			
		$update = array(
			'title'	=>	$warning_title,
			'description'	=>	$warning_description,
			'points'	=>	$points,
			'expiration_time'	=>	$expiration_time,
		);

		if (isset($_POST['id']) && $id > 0) // Then we're editing
		{
			$data = array(
				':id'	=>	$id,
			);

			$ps = $db->select('warning_types', 'id, title, description, points, expiration_time', $data, 'id=:id');
			if ($ps->rowCount())
			{
				$warning_type = $ps->fetch();
				$data = array(
					':id'	=>	$warning_type['id'],
				);
				
				$db->update('warning_types', $update, 'id=:id', $data);
				$redirect_msg = $lang_warnings['Type updated redirect'];
			}
		}
		else // We're adding a new type
		{
			$db->insert('warning_types', $update);
			$redirect_msg = $lang_warnings['Type added redirect'];
		}
	}
	else // We're adding/editing warning levels
	{
		$warning_title = isset($_POST['warning_title']) ? panther_trim($_POST['warning_title']) : '';
		$warning_points = isset($_POST['warning_points']) ? intval($_POST['warning_points']) : 0;

		if ($warning_title == '')
			message($lang_warnings['No title']);

		// Determine expiration time
		$expiration_time  = get_expiration_time($_POST['expiration_time'], $_POST['expiration_unit']);

		$update = array(
			'points'	=>	$warning_points,
			'message'	=>	$warning_title,
			'period'	=>	$expiration_time,
		);

		if (isset($_POST['id']) && $id > 0)
		{
			$data = array(
				':id'	=>	$id,
			);

			$db->update('warning_levels', $update, 'id=:id', $data);
			$redirect_msg = $lang_warnings['Level update redirect'];
		}
		else
		{
			$db->insert('warning_levels', $update);
			$redirect_msg = $lang_warnings['Level added redirect'];
		}
	}

	redirect(panther_link($panther_url['admin_warnings']), $redirect_msg);
}

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Warnings']);
define('PANTHER_ACTIVE_PAGE', 'admin');

if (isset($_POST['add_type']))
{
	require PANTHER_ROOT.'header.php';
	generate_admin_menu('warnings');

	$tpl = load_template('add_warning_type.tpl');
	echo $tpl->render(
		array(
			'lang_warnings' => $lang_warnings,
			'form_action' => panther_link($panther_url['admin_warnings']),
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/warnings.php'),
		)
	);	
}
else if (isset($_GET['edit_type']))
{
	$id = isset($_GET['edit_type']) ? intval($_GET['edit_type']) : 0;
	if ($id < 1)
		message($lang_common['Bad request']);

	$data = array(
		':id'	=>	$id,
	);

	// Get information of the warning
	$ps = $db->select('warning_types', 'id, title, description, points, expiration_time', $data, 'id=:id');
	if (!$ps->rowCount())
		message($lang_common['Bad request']);

	$warning_type = $ps->fetch();

	// Get expiration time and unit
	$expiration = explode(' ', format_expiration_time($warning_type['expiration_time']));
	if ($expiration[0] == 'Never')
	{
		$expiration[0] = '';
		$expiration[1] = 'Never';
	}

	require PANTHER_ROOT.'header.php';
	generate_admin_menu('warnings');

	$tpl = load_template('edit_warning_type.tpl');
	echo $tpl->render(
		array(
			'lang_warnings' => $lang_warnings,
			'form_action' => panther_link($panther_url['admin_warnings']),
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/warnings.php'),
			'warning_type' => $warning_type,
			'expiration' => $expiration,
		)
	);
}
elseif (isset($_GET['del_type']))
{
	$id = isset($_GET['del_type']) ? intval($_GET['del_type']) : 0;
	if ($id < 1)
		message($lang_common['Bad request']);

	if (isset($_POST['del_type_comply']))
	{
		confirm_referrer(PANTHER_ADMIN_DIR.'/warnings.php');
		$data = array(
			':id'	=>	$id,
		);

		// Delete the warning type
		$db->delete('warning_types', 'id=:id', $data);
		redirect(panther_link($panther_url['admin_warnings']), $lang_warnings['Type delete redirect']);
	}
	else // If the user hasn't confirmed the delete
	{
		$data = array(
			':id'	=>	$id,
		);

		$ps = $db->select('warning_types', 'title', $data, 'id=:id');
		if (!$ps->rowCount())
			message($lang_common['Bad resuqest']);

		$warning_type = $ps->fetchColumn();
		
		require PANTHER_ROOT.'header.php';
		generate_admin_menu('warnings');

		$tpl = load_template('delete_warning_type.tpl');
		echo $tpl->render(
			array(
				'lang_warnings' => $lang_warnings,
				'lang_common' => $lang_common,
				'form_action' => panther_link($panther_url['warning_del_type'], array($id)),
				'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/warnings.php'),
				'warning_type' => $warning_type,
			)
		);
	}
}
else if (isset($_POST['add_level']))
{
	require PANTHER_ROOT.'header.php';
	generate_admin_menu('warnings');

	$tpl = load_template('add_warning_level.tpl');
	echo $tpl->render(
		array(
			'lang_warnings' => $lang_warnings,
			'form_action' => panther_link($panther_url['admin_warnings']),
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/warnings.php'),
		)
	);
}
else if (isset($_GET['edit_level']))
{
	$id = intval($_GET['edit_level']);
	$data = array(
		':id'	=>	$id,
	);

	// Fetch warning information
	$ps = $db->select('warning_levels', 'id, points, message, period', $data, 'id=:id');
	if (!$ps->rowCount())
		message($lang_common['Bad request']);

	$warning_level = $ps->fetch();
	
	require PANTHER_ROOT.'header.php';
	generate_admin_menu('warnings');

	// Get expiration time and unit
	$expiration = explode(' ', format_expiration_time($warning_level['period']));
	if ($expiration[0] == 'Never')
	{
		$expiration[0] = '';
		$expiration[1] = 'Never';
	}

	$tpl = load_template('edit_warning_level.tpl');
	echo $tpl->render(
		array(
			'lang_warnings' => $lang_warnings,
			'form_action' => panther_link($panther_url['admin_warnings']),
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/warnings.php'),
			'expiration' => $expiration,
			'warning_level' => $warning_level,
		)
	);
}
else if (isset($_GET['del_level']))
{
	$id = isset($_GET['del_level']) ? intval($_GET['del_level']) : 0;
	if ($id < 1)
		message($lang_common['Bad request']);

	if (isset($_POST['del_level_comply']))
	{
		confirm_referrer(PANTHER_ADMIN_DIR.'/warnings.php');
		$data = array(
			':id'	=>	$id,
		);

		// Delete the warning level
		$db->delete('warning_levels', 'id=:id', $data);
		redirect(panther_link($panther_url['admin_warnings']), $lang_warnings['Level del redirect']);
	}

	require PANTHER_ROOT.'header.php';
	generate_admin_menu('warnings');

	$tpl = load_template('delete_warning_level.tpl');
	echo $tpl->render(
		array(
			'lang_warnings' => $lang_warnings,
			'lang_common' => $lang_common,
			'form_action' => panther_link($panther_url['warning_del_level'], array($id)),
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/warnings.php'),
		)
	);
}
else
{
	$types = array();
	$ps = $db->select('warning_types', 'id, title, description, points, expiration_time', array(), '', 'points, id');
	foreach ($ps as $list_types)
	{
		$expiration = explode(' ', format_expiration_time($list_types['expiration_time']));
		if ($expiration[0] == $lang_warnings['Never'])
		{
			$expiration[0] = '';
			$expiration[1] = $lang_warnings['Never'];
		}

		$types[] = array(
			'edit_link' => panther_link($panther_url['warning_edit_type'], array($list_types['id'])),
			'delete_link' => panther_link($panther_url['warning_del_type'], array($list_types['id'])),
			'list_types' => $list_types,
			'expiration' => $expiration,
		);
	}

	$levels = array();
	$ps = $db->select('warning_levels', 'id, points, period', array(), '', 'points, id');
	foreach ($ps as $list_levels)
	{
		if ($list_levels['period'] == '0')
			$ban_title = $lang_warnings['Permanent ban'];
		else
		{
			$expiration = explode(' ', format_expiration_time($list_levels['period']));
			if ($expiration[0] == $lang_warnings['Never'])
			{
				$expiration[0] = '';
				$expiration[1] = $lang_warnings['Never'];
			}
			$ban_title = sprintf($lang_warnings['Temporary ban'], $expiration[0], $expiration[1]);
		}

		$levels[] = array(
			'edit_link' => panther_link($panther_url['warning_edit_level'], array($list_levels['id'])),
			'delete_link' => panther_link($panther_url['warning_del_level'], array($list_levels['id'])),
			'points' => $list_levels['points'],
			'ban_title' => $ban_title,
		);
	}

	// Display the admin navigation menu
	require PANTHER_ROOT.'header.php';
	generate_admin_menu('warnings');

	$tpl = load_template('admin_warnings.tpl');
	echo $tpl->render(
		array(
			'lang_admin_common' => $lang_admin_common,
			'lang_warnings' => $lang_warnings,
			'form_action' => panther_link($panther_url['admin_warnings']),
			'types' => $types,
			'levels' => $levels,
		)
	);
}

require PANTHER_ROOT.'footer.php';