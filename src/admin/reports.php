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

if (($panther_user['is_admmod'] && $panther_user['g_mod_cp'] == '0' && !$panther_user['is_admin']) || !$panther_user['is_admmod'])
	message($lang_common['No permission'], false, '403 Forbidden');

check_authentication();

// Load the admin_reports.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_reports.php';

// Zap a report
if (isset($_POST['zap_id']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/reports.php');

	$zap_id = intval(key($_POST['zap_id']));
	$data = array(
		':id'	=>	$zap_id,
	);

	$ps = $db->select('reports', 'zapped', $data, 'id=:id');
	$zapped = $ps->fetchColumn();

	if ($zapped == '')
	{
		$update = array(
			'zapped'	=>	time(),
			'zapped_by'	=>	$panther_user['id'],
		);
		
		$data = array(
			':id'	=>	$zap_id
		);
		
		$db->update('reports', $update, 'id=:id', $data);
	}

	// Delete old reports (which cannot be viewed anyway)
	$ps = $db->select('reports', 'zapped', array(), 'zapped IS NOT NULL ORDER BY zapped DESC LIMIT 10, 1');
	if ($ps->rowCount())
	{
		$data = array(
			':zapped' => $ps->fetchColumn(),
		);

		$db->delete('reports', 'zapped <= :zapped', $data);
	}

	redirect(panther_link($panther_url['admin_reports']), $lang_admin_reports['Report zapped redirect']);
}

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Reports']);
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

generate_admin_menu('reports');

$reports = array();
$ps = $db->run('SELECT r.id, r.topic_id, r.forum_id, r.reported_by, r.created, r.message, p.id AS pid, t.subject, f.forum_name, u.username AS reporter FROM '.$db->prefix.'reports AS r LEFT JOIN '.$db->prefix.'posts AS p ON r.post_id=p.id LEFT JOIN '.$db->prefix.'topics AS t ON r.topic_id=t.id LEFT JOIN '.$db->prefix.'forums AS f ON r.forum_id=f.id LEFT JOIN '.$db->prefix.'users AS u ON r.reported_by=u.id WHERE r.zapped IS NULL ORDER BY created DESC');
foreach ($ps as $cur_report)
	$reports[] = array(
		'posted' => format_time($cur_report['created']),
		'id' => $cur_report['id'],
		'message' => str_replace("\n", '<br />', $cur_report['message']),
		'forum' =>  ($cur_report['forum_name'] != '') ? array('href' => panther_link($panther_url['forum'], array($cur_report['forum_id'], url_friendly($cur_report['forum_name']))), 'title' => $cur_report['forum_name']) : '',
		'topic' => ($cur_report['subject'] != '') ? array('href' => panther_link($panther_url['topic'], array($cur_report['topic_id'], url_friendly($cur_report['subject']))), 'title' => $cur_report['subject']) : '',
		'post' => ($cur_report['pid'] != '') ? array('href' => panther_link($panther_url['post'], array($cur_report['pid'])), 'title' => sprintf($lang_admin_reports['Post ID'], $cur_report['pid'])) : '',
		'reporter' => ($cur_report['reporter'] != '') ? array('href' => panther_link($panther_url['profile'], array($cur_report['reported_by'], url_friendly($cur_report['reporter']))), 'username' => $cur_report['reporter']) : ''
	);

// Now get the zapped reports
$zapped = array();
$ps = $db->run('SELECT r.id, r.topic_id, r.forum_id, r.reported_by, r.message, r.zapped, r.zapped_by AS zapped_by_id, p.id AS pid, t.subject, f.forum_name, u.username AS reporter, u2.username AS zapped_by FROM '.$db->prefix.'reports AS r LEFT JOIN '.$db->prefix.'posts AS p ON r.post_id=p.id LEFT JOIN '.$db->prefix.'topics AS t ON r.topic_id=t.id LEFT JOIN '.$db->prefix.'forums AS f ON r.forum_id=f.id LEFT JOIN '.$db->prefix.'users AS u ON r.reported_by=u.id LEFT JOIN '.$db->prefix.'users AS u2 ON r.zapped_by=u2.id WHERE r.zapped IS NOT NULL ORDER BY zapped DESC LIMIT 10');
foreach ($ps as $cur_report)
	$zapped[] = array(
		'zapped' => format_time($cur_report['zapped']),
		'id' => $cur_report['id'],
		'message' => str_replace("\n", '<br />', $cur_report['message']),
		'forum' =>  ($cur_report['forum_name'] != '') ? array('href' => panther_link($panther_url['forum'], array($cur_report['forum_id'], url_friendly($cur_report['forum_name']))), 'title' => $cur_report['forum_name']) : '',
		'topic' => ($cur_report['subject'] != '') ? array('href' => panther_link($panther_url['topic'], array($cur_report['topic_id'], url_friendly($cur_report['subject']))), 'title' => $cur_report['subject']) : '',
		'post' => ($cur_report['pid'] != '') ? array('href' => panther_link($panther_url['post'], array($cur_report['pid'])), 'title' => sprintf($lang_admin_reports['Post ID'], $cur_report['pid'])) : '',
		'reporter' => ($cur_report['reporter'] != '') ? array('href' => panther_link($panther_url['profile'], array($cur_report['reported_by'], url_friendly($cur_report['reporter']))), 'username' => $cur_report['reporter']) : '',
		'zapped_by' => $cur_report['zapped_by'],
	);

$tpl = load_template('admin_reports.tpl');
echo $tpl->render(
	array(
		'lang_admin_reports' => $lang_admin_reports,
		'lang_admin_common' => $lang_admin_common,
		'form_action' => panther_link($panther_url['admin_reports_zap']),
		'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/reports.php'),
		'reports' => $reports,
		'zapped' => $zapped,
	)
);

require PANTHER_ROOT.'footer.php';