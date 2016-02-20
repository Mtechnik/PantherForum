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

// Load the admin_addons.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_extensions.php';

check_authentication();

if ($panther_user['id'] != '2')
{
	if(!is_null($admins[$panther_user['id']]['admin_addons']))
	{
		if ($admins[$panther_user['id']]['admin_addons'] == '0')
			message($lang_common['No permission']);
	}
}

$errors = array();
$action = isset($_GET['action']) ? $_GET['action'] : '';

if (isset($_POST['upload']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/addons.php');
	if (!isset($_FILES['req_file']))
		message($lang_common['No file']);

	$uploaded_file = $_FILES['req_file'];

	// Make sure the upload went smooth
	if (isset($uploaded_file['error']))
	{
		switch ($uploaded_file['error'])
		{
			case 1:	// UPLOAD_ERR_INI_SIZE
			case 2:	// UPLOAD_ERR_FORM_SIZE
				message($lang_common['Too large ini']);
			break;
			case 3:	// UPLOAD_ERR_PARTIAL
				message($lang_common['Partial upload']);
			break;
			case 4:	// UPLOAD_ERR_NO_FILE
				message($lang_common['No file']);
			break;
			case 6:	// UPLOAD_ERR_NO_TMP_DIR
				message($lang_common['No tmp directory']);
			break;
			default:
				// No error occured, but was something actually uploaded?
				if ($uploaded_file['size'] == 0)
					message($lang_common['No file']);
			break;
		}
	}

	if (!is_uploaded_file($uploaded_file['tmp_name']))
		$errors[] = $lang_common['Unknown failure'];

	$filename = $uploaded_file['name'];
	if (!preg_match('/^[a-z0-9-]+\.(xml)$/i', $uploaded_file['name']))
		$errors[] = $lang_admin_extensions['Bad type'];
	elseif (file_exists(PANTHER_ROOT.PANTHER_ADMIN_DIR.'/extensions/'.$filename)) // Make sure there is no file already under this name
		$errors[] = sprintf($lang_admin_extensions['Extension exists'], $filename);
	else if (!@move_uploaded_file($uploaded_file['tmp_name'], PANTHER_ROOT.PANTHER_ADMIN_DIR.'/extensions/'.$filename)) // Move the file to the addons directory.
		$errors[] = $lang_common['Move failed'];
	else if (!empty($errors))
		@unlink(PANTHER_ROOT.PANTHER_ADMIN_DIR.'/extensions/'.$filename);

	if (empty($errors))
	{
		@chmod(PANTHER_ROOT.PANTHER_ADMIN_DIR.'/extensions/'.$filename, 0644);		
		redirect(panther_link($panther_url['admin_addons']), $lang_admin_extensions['Extension uploaded']);
	}
}

if ($action == 'enable' || $action == 'disable')
{
	$file = isset($_GET['file']) ? panther_trim($_GET['file']) : '';
	$data = array(
		':id' => $file,
	);

	$ps = $db->select('extensions', 'title, enabled, author, description, version', $data, 'id=:id');
	if (!$ps->rowCount())
		message($lang_common['Bad request']);

	$update = array(
		'enabled' => ($action == 'enable') ? '1' : '0',
	);

	$db->update('extensions', $update, 'id=:id', $data);
	
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_extensions_cache();
	redirect(panther_link($panther_url['admin_addons']), ($action == 'enable' ? $lang_admin_extensions['Extension enabled'] : $lang_admin_extensions['Extension disabled']));
}
else if ($action == 'install')
{
	$file = isset($_GET['file']) ? panther_trim($_GET['file']) : '';

	if (!file_exists(PANTHER_ROOT.PANTHER_ADMIN_DIR.'/extensions/'.$file.'.xml'))
		message($lang_common['Bad request']);

	$data = array(
		':id' => $file,
	);

	$ps = $db->select('extensions', 1, $data, 'id=:id');
	if ($ps->rowCount())
		message($lang_admin_extensions['Already installed']);

	$content = file_get_contents(PANTHER_ROOT.PANTHER_ADMIN_DIR.'/extensions/'.$file.'.xml');
	$extension = xml_to_array($content);
	$errors = validate_xml($extension, $errors);
	$extension = $extension['extension'];

	$warnings = array();
	if (isset($_POST['form_sent']))
	{
		$enable = isset($_POST['enable']) ? '1' : '0';
		confirm_referrer(PANTHER_ADMIN_DIR.'/addons.php');

		if (empty($errors))
		{
			$insert = array(
				'id' => $file,
				'title' => $extension['title'],
				'version' => $extension['version'],
				'description' => $extension['description'],
				'author' => $extension['author'],
				'uninstall_note' => (isset($extension['uninstall_note']) ? $extension['uninstall_note'] : ''),
				'uninstall' => (isset($extension['uninstall']) ? $extension['uninstall'] : ''),
				'enabled' => $enable,
			);

			$db->insert('extensions', $insert);
			$extension_id = $db->lastInsertId($db->prefix.'extensions');

			foreach ($extension['hooks']['hook'] as $hook)
			{
				$insert = array(
					'extension_id' => $file,
					'hook' => $hook['attributes']['id'],
					'code' => panther_trim($hook['content']),
				);

				$db->insert('extension_code', $insert);
			}

			if (isset($extension['install']))
				eval($extension['install']);

			if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
				require PANTHER_ROOT.'include/cache.php';

			generate_extensions_cache();
			redirect(panther_link($panther_url['admin_addons']), $lang_admin_extensions['Extension installed']);
		}
	}

	$versions = explode(',', $extension['supported_versions']);
	if (!in_array($panther_config['o_cur_version'], $versions))
		$warnings[] = sprintf($lang_admin_extensions['Version warning'], $panther_config['o_cur_version'], $extension['supported_versions']);

	$id = sha1($content); // Make sure this extension is 'panther approved'
	$content = @file_get_contents('https://www.pantherforum.org/extension_check.php?id='.$id);
	if (!$content || $content != $id)
		$warnings[] = $lang_admin_extensions['Extension not approved'];

	$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Extensions']);
	define('PANTHER_ACTIVE_PAGE', 'admin');
	require PANTHER_ROOT.'header.php';
	generate_admin_menu('extensions');

	$tpl = load_template('install_extension.tpl');
	echo $tpl->render(
		array(
			'lang_admin_extensions' => $lang_admin_extensions,
			'lang_common' => $lang_common,
			'form_action' => panther_link($panther_url['install_extension'],  array($file)),
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/addons.php'),
			'extension' => $extension,
			'warnings' => $warnings,
			'errors' => $errors,
		)
	);
}
else if ($action == 'uninstall')
{
	$file = isset($_GET['file']) ? panther_trim($_GET['file']) : '';
	if (!file_exists(PANTHER_ROOT.PANTHER_ADMIN_DIR.'/extensions/'.$file.'.xml'))
		message($lang_common['Bad request']);

	$data = array(
		':id' => $file,
	);

	$ps = $db->select('extensions', 'uninstall_note, uninstall', $data, 'id=:id');
	if (!$ps->rowCount())
		message($lang_common['Bad request']);
	
	$extension = $ps->fetch();

	if (isset($_POST['form_sent']))
	{
		$data = array(
			'id' => $file,
		);

		$db->delete('extensions', 'id=:id', $data);
		$db->delete('extension_code', 'extension_id=:id', $data);

		eval($extension['uninstall']);

		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';
		
		generate_extensions_cache();
		redirect(panther_link($panther_url['admin_addons']), $lang_admin_extensions['Extension uninstalled']);
	}

	$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Extensions']);
	define('PANTHER_ACTIVE_PAGE', 'admin');
	require PANTHER_ROOT.'header.php';
	generate_admin_menu('extensions');

	$tpl = load_template('uninstall_extension.tpl');
	echo $tpl->render(
		array(
			'extension' => $extension,
			'lang_admin_extensions' => $lang_admin_extensions,
			'lang_common' => $lang_common,
			'form_action' => panther_link($panther_url['uninstall_extension'],  array($file)),
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/addons.php'),
		)
	);
}
else
{
	$extension_files = array();
	$files = array_diff(scandir(PANTHER_ROOT.PANTHER_ADMIN_DIR.'/extensions'), array('.', '..'));
	foreach ($files as $entry)
	{
		if (substr($entry, -4) == '.xml')
			$extension_files[$entry] = array(
				'title' => substr($entry, 0, -4),
				'file' => $entry,
				'install_link' => panther_link($panther_url['install_extension'], array(substr($entry, 0, -4))),
			);
	}

	$extensions = array();
	$ps = $db->select('extensions', 'id, title, enabled');
	foreach ($ps as $cur_extension)
	{
		if (file_exists(PANTHER_ROOT.PANTHER_ADMIN_DIR.'/extensions/'.$cur_extension['id'].'.xml'))
			unset($extension_files[$cur_extension['id'].'.xml']);

		$extensions[] = array(
			'id' => $cur_extension['id'],
			'title' => $cur_extension['title'],
			'enabled' => $cur_extension['enabled'],
			'enable_link' => ($cur_extension['enabled']) ? panther_link($panther_url['disable_extension'],  array($cur_extension['id'])) : panther_link($panther_url['enable_extension'],  array($cur_extension['id'])),
			'uninstall_link' => panther_link($panther_url['uninstall_extension'], array($cur_extension['id'])),
		);
	}

	$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Extensions']);
	define('PANTHER_ACTIVE_PAGE', 'admin');
	require PANTHER_ROOT.'header.php';
	generate_admin_menu('extensions');

	$tpl = load_template('admin_extensions.tpl');
	echo $tpl->render(
		array(
			'lang_admin_common' => $lang_admin_common,
			'lang_admin_extensions' => $lang_admin_extensions,
			'form_action' => panther_link($panther_url['admin_addons']),
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/addons.php'),
			'extensions' => $extensions,
			'extension_files' => $extension_files,
			'errors' => $errors,
		)
	);
}

require PANTHER_ROOT.'footer.php';