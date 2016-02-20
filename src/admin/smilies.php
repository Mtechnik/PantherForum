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

if ($panther_config['o_smilies'] == '0')
	message($lang_common['Bad request']);
	
// Load the admin_smilies.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_smilies.php';

check_authentication();

if ($panther_user['id'] != '2')
{
	if(!is_null($admins[$panther_user['id']]['admin_smilies']))
	{
		if ($admins[$panther_user['id']]['admin_smilies'] == '0')
			message($lang_common['No permission']);
	}
}

$smiley_path = ($panther_config['o_smilies_dir'] != '') ? $panther_config['o_smilies_path'] : PANTHER_ROOT.$panther_config['o_smilies_path'].'/';
$smiley_dir = ($panther_config['o_smilies_dir'] != '') ? $panther_config['o_smilies_dir'] : get_base_url(true).'/'.$panther_config['o_smilies_path'].'/';

// Retrieve the smiley images
$img_smilies = array_diff(scandir($smiley_path), array('.', '..'));
@natsort();

// Change smilies texts, images and positions
if (isset($_POST['reorder']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/smilies.php');

	$disp_position = isset($_POST['disp_position']) && is_array($_POST['disp_position']) ? array_map('intval', $_POST['disp_position']) : array();
	$image = isset($_POST['smilies_img']) && is_array($_POST['smilies_img']) ? array_map('panther_trim', $_POST['smilies_img']) : array();
	$smiley_code = isset($_POST['smilies_code']) && is_array($_POST['smilies_code']) ? array_map('panther_trim', $_POST['smilies_code']) : array();

	$duplicates = array();
	foreach ($smiley_code as $code)
	{
		if ($code == '')
			message($lang_admin_smilies['Create Smiley Code None']);

		if (in_array($code, $duplicates))
			message(sprintf($lang_admin_smilies['Duplicate smilies code'], $code));
		
		$duplicates[] = $code;
	}

	$ps = $db->select('smilies', 'id', array(), '', 'disp_position');
	foreach ($ps as $cur_smiley)
	{
		$update = array(
			'code'	=>	$smiley_code[$cur_smiley['id']],
			'image'	=>	$image[$cur_smiley['id']],
			'disp_position'	=>	$disp_position[$cur_smiley['id']],
		);

		$data = array(
			':id' => $cur_smiley['id'],
		);

		$db->update('smilies', $update, 'id=:id', $data);
	}
	
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_smilies_cache();
	redirect(panther_link($panther_url['admin_smilies']), $lang_admin_smilies['Smilies edited']);
}
else if (isset($_POST['remove'])) // Delete the emoticon codes from the database
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/smilies.php');

	$smilies = isset($_POST['remove_smilies']) && is_array($_POST['remove_smilies']) ? array_map('intval', $_POST['remove_smilies']) : array();
	if (empty($smilies))
		message($lang_admin_smilies['No Smileys']);

	$markers = $data = array();
	for ($i = 0; $i < count($smilies); $i++)
		$markers[] = '?';

	// Delete smilies
	$db->delete('smilies', 'id IN('.implode(', ', $markers).')', array_values($smilies));
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_smilies_cache();
	redirect(panther_link($panther_url['admin_smilies']), $lang_admin_smilies['Delete Smiley Redirect']);
}
else if (isset($_POST['delete'])) // We're actually removing smiley images from the server
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/smilies.php');

	$del_smilies = isset($_POST['del_smilies']) && is_array($_POST['del_smilies']) ? array_map('panther_trim', $_POST['del_smilies']) : array();
	if (empty($del_smilies))
		message($lang_admin_smilies['No Images']);

	// Check if the images are being used
	if (file_exists(FORUM_CACHE_DIR.'cache_smilies.php'))
		include FORUM_CACHE_DIR.'cache_smilies.php';
	else
	{
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';

		generate_smilies_cache();
		require FORUM_CACHE_DIR.'cache_smilies.php';
	}

	foreach ($del_smilies as $id => $img)
	{
		$data = array(
			':image' => $img,
		);

		$ps = $db->select('smilies', 1, $data, 'image=:image');
		if ($ps->rowCount())
			message(sprintf($lang_admin_smilies['Smiley in use'], $img));

		// Only remove if it's a valid image
		if (preg_match('/^[a-zA-Z0-9\-_]+\.(png|jpg|jpeg|gif)$/i', $img))
			@unlink($smiley_path.'/'.$img);
	}

	redirect(panther_link($panther_url['admin_smilies']), $lang_admin_smilies['Images deleted']);
}
else if (isset($_POST['add_smiley'])) // Create a new smiley code
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/smilies.php');

	$code = isset($_POST['smiley_code']) ? panther_trim($_POST['smiley_code']) : '';
	$image = isset($_POST['smiley_image']) ? panther_trim($_POST['smiley_image']) : '';

	if ($code == '')
		message($lang_admin_smilies['Create Smiley Code None']);

	if ($image == '')
		message($lang_admin_smilies['Create Smiley Image None']);

	$insert = array(
		'image'	=>	$image,
		'code'	=>	$code,
	);

	// Add the smiley
	$db->insert('smilies', $insert);
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_smilies_cache();
	redirect(panther_link($panther_url['admin_smilies']), $lang_admin_smilies['Successful Creation']);
}
else if (isset($_POST['add_image'])) // We're uploading a new image to the server
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/smilies.php');

	if (!isset($_FILES['req_file']))
		message($lang_admin_smilies['No file']);

	$uploaded_file = $_FILES['req_file'];

	// Make sure the upload went smooth
	if (isset($uploaded_file['error']))
	{
		switch ($uploaded_file['error'])
		{
			case 1:	// UPLOAD_ERR_INI_SIZE
			case 2:	// UPLOAD_ERR_FORM_SIZE
				message($lang_admin_smilies['Too large ini']);
			break;
			case 3:	// UPLOAD_ERR_PARTIAL
				message($lang_admin_smilies['Partial upload']);
			break;
			case 4:	// UPLOAD_ERR_NO_FILE
				message($lang_admin_smilies['No file']);
			break;
			case 6:	// UPLOAD_ERR_NO_TMP_DIR
				message($lang_admin_smilies['No tmp directory']);
			break;
			default:
				// No error occured, but was something actually uploaded?
				if ($uploaded_file['size'] == 0)
					message($lang_admin_smilies['No file']);
			break;
		}
	}

	if (is_uploaded_file($uploaded_file['tmp_name']))
	{
		$filename = substr($uploaded_file['name'], 0, strpos($uploaded_file['name'], '.'));

		// Check types
		$allowed_types = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png');
		if (!in_array($uploaded_file['type'], $allowed_types))
			message($lang_admin_smilies['Bad type']);

		// Make sure the file isn't too big
		if ($uploaded_file['size'] > $panther_config['o_smilies_size'])
			message($lang_admin_smilies['Too large'].' '.$panther_config['o_smilies_size'].' '.$lang_admin_smilies['bytes'].'.');

		// Determine type
		$extensions = null;
		if ($uploaded_file['type'] == 'image/gif')
			$extensions = array('.gif', '.jpg', '.png');
		else if ($uploaded_file['type'] == 'image/jpeg' || $uploaded_file['type'] == 'image/pjpeg')
			$extensions = array('.jpg', '.gif', '.png');
		else
			$extensions = array('.png', '.gif', '.jpg');

		// Move the file to the avatar directory. We do this before checking the width/height to circumvent open_basedir restrictions.
		if (!@move_uploaded_file($uploaded_file['tmp_name'], $smiley_path.'/'.$filename.'.tmp'))
			message($lang_admin_smilies['Move failed']);

		// Now check the width/height
		list($width, $height, $type,) = getimagesize($smiley_path.'/'.$filename.'.tmp');
		if (empty($width) || empty($height) || $width > $panther_config['o_smilies_width'] || $height > $panther_config['o_smilies_height'])
		{
			@unlink($smiley_path.'/'.$filename.'.tmp');
			message($lang_admin_smilies['Too wide or high'].' '.$panther_config['o_smilies_width'].'x'.$panther_config['o_smilies_height'].' '.$lang_admin_smilies['pixels'].'.');
		}
		else if ($type == 1 && $uploaded_file['type'] != 'image/gif') // Prevent dodgy uploads
		{
			@unlink($smiley_path.'/'.$filename.'.tmp');
			message($lang_admin_smilies['Bad type']);
		}
	
		// Delete any old images and put the new one in place
		@unlink($smiley_path.'/'.$filename.$extensions[0]);
		@unlink($smiley_path.'/'.$filename.$extensions[1]);
		@unlink($smiley_path.'/'.$filename.$extensions[2]);
		@rename($smiley_path.'/'.$filename.'.tmp', $smiley_path.'/'.$filename.$extensions[0]);

		compress_image($smiley_path.'/'.$filename.$extensions[0]);
		@chmod($smiley_path.'/'.$filename.$extensions[0], 0644);
	}
	else
		message($lang_admin_smilies['Unknown failure']);
	
	redirect(panther_link($panther_url['admin_smilies']), $lang_admin_smilies['Successful Upload']);
}

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Smilies']);
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';
generate_admin_menu('smilies');

$emoticons = $options = array();
$ps = $db->select('smilies', 'id, image, code, disp_position', array(), '', 'disp_position');
foreach ($ps as $cur_smiley)
{
	foreach ($img_smilies as $img)
		$options[$cur_smiley['id']][] = $img;

	$emoticons[] = array(
		'id' => $cur_smiley['id'],
		'disp_position' => $cur_smiley['disp_position'],
		'code' => $cur_smiley['code'],
		'image' => $smiley_dir.$cur_smiley['image'],
		'file' => $cur_smiley['image'],
	);
}

$smiley_list = $images = array();
foreach ($img_smilies as $id => $img)
{
	$smiley_list[] = array(
		'file' => $img,
		'image' => $smiley_dir.$img,
		'id' => $id,
	);
	
	$images[] = $img;
}

$tpl = load_template('admin_smilies.tpl');
echo $tpl->render(
	array(
		'lang_admin_common' => $lang_admin_common,
		'lang_admin_smilies' => $lang_admin_smilies,
		'form_action' => panther_link($panther_url['admin_smilies']),
		'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/smilies.php'),
		'emoticons' => $emoticons,
		'img_smilies' => $img_smilies,
		'options' => $options,
		'smiley_list' => $smiley_list,
		'images' => $images,
	)
);

require PANTHER_ROOT.'footer.php';