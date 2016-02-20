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

// Load the attach.php language file
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/attach.php';

$id = isset($_GET['item']) ? intval($_GET['item']) : 0;

if ($id < 1)
	message($lang_common['Bad request']);

$data = array(
	':id'	=>	$id,
	':gid'	=>	$panther_user['g_id'],
);

$ps = $db->run('SELECT a.post_id, a.filename, a.extension, a.mime, a.location, a.size, fp.download FROM '.$db->prefix.'attachments AS a LEFT JOIN '.$db->prefix.'posts AS p ON a.post_id=p.id LEFT JOIN '.$db->prefix.'topics AS t ON p.topic_id=t.id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON t.forum_id=fp.forum_id AND fp.group_id=:gid WHERE a.id=:id', $data);
if (!$ps->rowCount())
	message($lang_common['Bad request']);
else
	$attachment = $ps->fetch();

$download = false;
if ($panther_user['is_admin'])
	$download = true;
else if ($attachment['download'] != 0)
	$download = true;

if(!$download)
	message($lang_common['No permission']);

if (($attachment['extension'] == 'jpg' || $attachment['extension'] == 'jpeg' || $attachment['extension'] == 'gif' || $attachment['extension'] == 'png') && !isset($_GET['download']))
{
    ($hook = get_extensions('attachment_image_view')) ? eval($hook) : null;
	$page_title = array($panther_config['o_board_title'], $lang_attach['Image view'], $attachment['filename']);
	define('PANTHER_ALLOW_INDEX', 1);
	define('PANTHER_ACTIVE_PAGE', 'index');
	require PANTHER_ROOT.'header.php';

	$tpl = load_template('attachment.tpl');
	echo $tpl->render(
		array(
			'lang_attach' => $lang_attach,
			'lang_common' => $lang_common,
			'name' => $attachment['filename'],
			'download_link' => panther_link($panther_url['attachment_download'], array($id)),
		)
	);

	require PANTHER_ROOT.'footer.php';
}

$data = array(
	':id'	=>	$id,
);

$db->run('UPDATE '.$db->prefix.'attachments SET downloads=downloads+1 WHERE id=:id', $data);

($hook = get_extensions('attachment_before_output')) ? eval($hook) : null;
$db->end_transaction();

$fp = fopen($panther_config['o_attachments_dir'].$attachment['location'], "rb");
if(!$fp)
	message($lang_common['Bad request']);

$attachment['filename'] = rawurlencode($attachment['filename']);

// send some headers
header('Content-Disposition: attachment; filename='.$attachment['filename']);
if (strlen($attachment['mime']) > 0)
	header('Content-Type: '.$attachment['mime']);
else
	header('Content-type: application/octet-stream');

header('Pragma: no-cache');
header('Expires: 0'); 
header('Connection: close');
if ($attachment['size'] != 0)
	header('Content-Length: '.$attachment['size']);

fpassthru($fp);