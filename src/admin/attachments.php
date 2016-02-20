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
	if(!is_null($admins[$panther_user['id']]['admin_attachments']))
	{
		if ($admins[$panther_user['id']]['admin_attachments'] == '0')
			message($lang_common['No permission'], false, '403 Forbidden');
	}
}

// Load the admin_attachments.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_attachments.php';

check_authentication();

if ($panther_config['o_attachments'] == '0')
	message($lang_common['Bad request']);

$action = isset($_GET['action']) ? $_GET['action'] : null;

if (isset($_POST['delete_attachment']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/attachments.php');
	$id = intval(key($_POST['delete_attachment']));

	if (!delete_attachment($id))
		message($lang_admin_attachments['Unable to delete attachment']);

	redirect(panther_link($panther_url['admin_attachments']), $lang_admin_attachments['Attachment del redirect']);
}
elseif (isset($_POST['delete_orphans']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/attachments.php');
	$ps = $db->run('SELECT a.id FROM '.$db->prefix.'attachments AS a LEFT JOIN '.$db->prefix.'posts AS p ON p.id=a.post_id WHERE p.id IS NULL');
	if (!$ps->rowCount())
		message($lang_admin_attachments['No orphans']);

	$i = 0;
	$ps->setFetchMode(PDO::FETCH_COLUMN, 0);
	foreach ($ps as $attachment)
	{
		if (!delete_attachment($attachment))
			continue;
		else
			$i++;
	}

	message(sprintf($lang_admin_attachmetns['X orphans deleted'], array($i)));
}

$start = (isset($_POST['start'])) ? intval($_POST['start']) : 0;
$limit = (isset($_POST['number'])) ? intval($_POST['number']) : 50;
$increase = (isset($_POST['auto_increase']) && $_POST['auto_increase'] == '1') ? $start + $limit : $start;
$direction = (isset($_POST['direction']) && $_POST['direction'] == '1') ? 'ASC' : 'DESC';
$order = isset($_POST['order']) ? intval($_POST['order']) : 0;

switch ($order)
{
	case 1:
		$order = 'a.downloads';
		break;
	case 2:
		$order = 'a.size';
		break;
	case 3:
		$order = 'a.downloads*a.size';
		break;
	case 0:
	default:
		$order = 'a.id';
		break;
}

$data = array(
	':start'	=>	$start,
	':limit'	=>	$limit,
);

$ps = $db->run('SELECT a.id, a.owner, a.post_id, a.filename, a.extension, a.size, a.downloads, u.username, u.group_id FROM '.$db->prefix.'attachments AS a LEFT JOIN '.$db->prefix.'users AS u ON u.id=a.owner ORDER BY '.$order.' '.$direction.' LIMIT :start, :limit', $data);

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Attachments']);
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

generate_admin_menu('attachments');

$attachments = array();
foreach ($ps as $cur_item)
{
	$attachments[] = array(
		'icon' => attach_icon($cur_item['extension']),
		'link' => panther_link($panther_url['attachment'], array($cur_item['id'])),
		'name' => $cur_item['filename'],
		'username' => colourize_group($cur_item['username'], $cur_item['group_id'], $cur_item['owner']),
		'post_link' => panther_link($panther_url['post'], array($cur_item['post_id'])),
		'post_id' => $cur_item['post_id'],
		'size' => file_size($cur_item['size']),
		'downloads' => forum_number_format($cur_item['downloads']),
		'transfer' => file_size($cur_item['size'] * $cur_item['downloads']),
		'id' => $cur_item['id'],
	);
}

$tpl = load_template('admin_attachments.tpl');
echo $tpl->render(
	array(
		'lang_admin_attachments' => $lang_admin_attachments,
		'lang_admin_common' => $lang_admin_common,
		'form_action' => panther_link($panther_url['admin_attachments']),
		'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/attachments.php'),
		'increase' => $increase,
		'start' => $start,
		'limit' => $limit,
		'order' => $order,
		'direction' => $direction,
		'attachments' => $attachments,
	)
);

require PANTHER_ROOT.'footer.php';