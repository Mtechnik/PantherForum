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
	if(!is_null($admins[$panther_user['id']]['admin_archive']))
	{
		if ($admins[$panther_user['id']]['admin_archive'] == '0')
			message($lang_common['No permission'], false, '403 Forbidden');
	}
}

check_authentication();

// Load the admin_ranks.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_archive.php';

$ps = $db->select('topics', 'COUNT(id)', array(), 'deleted=0 AND approved=1');
$total = $ps->fetchColumn();

$ps = $db->select('topics', 'COUNT(id)', array(), 'archived=1 AND deleted=0 AND approved=1');
$archived = $ps->fetchColumn();

if (isset($_POST['form_sent']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/archive.php');
	$units = array('day', 'months', 'years');	// Set an array of valid time expiration strings

	$time = isset($_POST['time']) ? intval($_POST['time']) : 0;
	$unit = isset($_POST['unit']) && in_array($_POST['unit'], $units) ? panther_trim($_POST['unit']) : 'days';
	$closed = isset($_POST['closed']) ? intval($_POST['closed']) : 0;
	$sticky = isset($_POST['sticky']) ? intval($_POST['sticky']) : 0;
	$forums = isset($_POST['forums']) && is_array($_POST['forums']) ? array_map('intval', $_POST['forums']) : array(0);

	if (in_array(0, $forums) && count($forums) > 1)
		message($lang_admin_archive['All forums message']);

	if ($sticky > 2 || $sticky < 0 || $closed > 2 || $closed < 0)
		message($lang_admin_archive['Open/close message']);
	
	if ($time < 1)
		message(sprintf($lang_admin_archive['Invalid time value'], strtolower($unit)));

	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	$rules = array(
		'closed'	=>	$closed,
		'sticky'	=>	$sticky,
		'time'		=>	$time,
		'unit'		=>	$unit,
		'forums'	=>	$forums,
	);

	$topics = check_archive_rules($rules);
	$percentage = 0;
	if ($topics['count'] != 0 && $panther_config['o_archiving'] == '1')
	{
		$markers = $data = array();
		for ($i = 0; $i < count($topics['topics']); $i++)
		{
			$markers[] = '?';
			$data[] = $topics['topics'][$i];
		}

		$db->run('UPDATE '.$db->prefix.'topics SET archived=1 WHERE id IN ('.implode(',', $markers).')', $data);
		$percentage = round(($topics['count']/$total)*100, 2);
	}

	$update = array(
		'conf_value' => serialize($rules),
	);

	$data = array(
		':conf_name' => 'o_archive_rules',
	);

	$db->update('config', $update, 'conf_name=:conf_name', $data);
	generate_config_cache();

	$redirect_lang = ($panther_config['o_archiving'] == '1') ? sprintf($lang_admin_archive['Archive rules updated'], $topics['count'], $total, $percentage.'%') : $lang_admin_archive['Updated redirect'];
	redirect(panther_link($panther_url['admin_archive']), $redirect_lang);
}

$archive_rules = ($panther_config['o_archive_rules'] != '') ? unserialize($panther_config['o_archive_rules']) : array('closed' => 0, 'sticky' => 0, 'time' => 0, 'unit' => 'days', 'forums' => array(0));
$percentage = ($ps->rowCount() != 0) ? round(($archived/$total)*100, 2) : 0;

$categories = $forums = array();
$ps = $db->run('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id WHERE f.redirect_url IS NULL ORDER BY c.disp_position, c.id, f.disp_position');
foreach ($ps as $cur_forum)
{
	if (!isset($categories[$cur_forum['cid']]))
		$categories[$cur_forum['cid']] = array(
		'name' => $cur_forum['cat_name'],
		'id' => $cur_forum['cid'],
	);
		
	$forums[] = array(
		'id' => $cur_forum['fid'],
		'selected' => ((in_array($cur_forum['fid'], $archive_rules['forums'])) ? true : false),
		'name' => $cur_forum['forum_name'],
		'category_id' => $cur_forum['cid'],
	);
}
	
$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Archive']);
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

generate_admin_menu('archive');

$tpl = load_template('admin_archive.tpl');
echo $tpl->render(
	array(
		'lang_admin_common' => $lang_admin_common,
		'lang_admin_archive' => $lang_admin_archive,
		'form_action' => panther_link($panther_url['admin_archive']),
		'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/archive.php'),
		'archive_lang' => $panther_config['o_archiving'] == '1' ? $lang_admin_archive['Archive enabled'] : $lang_admin_archive['Archive disabled'],
		'admin_options' => panther_link($panther_url['admin_options']),
		'archived' => $archived,
		'percentage' => $percentage,
		'archive_rules' => $archive_rules,
		'lang_common' => $lang_common,
		'categories' => $categories,
		'forums' => $forums,
	)
);

require PANTHER_ROOT.'footer.php';