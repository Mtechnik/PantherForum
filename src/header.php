<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PANTHER'))
	exit;

// Send no-cache headers
header('Expires: Thu, 21 Jul 1977 07:30:00 GMT'); // When yours truly first set eyes on this world! :)
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache'); // For HTTP/1.0 compatibility

// Send the Content-type header in case the web server is setup to send something else
header('Content-type: text/html; charset=utf-8');

// Prevent site from being embedded in a frame
header('X-Frame-Options: '.(defined('PANTHER_XFRAME_OPTIONS') ? PANTHER_XFRAME_OPTIONS : 'deny'));

($hook = get_extensions('header_after_headers')) ? eval($hook) : null;

$p = isset($p) ? $p : null;
$links = array();

// Index should always be displayed
$links[] = array('id' => 'navindex', 'class' => ((PANTHER_ACTIVE_PAGE == 'index') ? 'isactive' : ''), 'page' => panther_link($panther_url['index']), 'title' => $lang_common['Index']);

if ($panther_user['g_read_board'] == '1' && $panther_user['g_view_users'] == '1')
	$links[] = array('id' => 'navuserlist', 'class' => ((PANTHER_ACTIVE_PAGE == 'userlist') ? 'isactive' : ''), 'page' => panther_link($panther_url['userlist']), 'title' => $lang_common['User list']);

if ($panther_user['g_read_board'] == '1' && $panther_user['g_view_users'] == '1')
	$links[] = array('id' => 'navleaders', 'class' => ((PANTHER_ACTIVE_PAGE == 'leaders') ? 'isactive' : ''), 'page' => panther_link($panther_url['leaders']), 'title' => $lang_common['Moderating Team']);

if ($panther_user['g_read_board'] == '1' && $panther_user['g_view_users'] == '1' && $panther_config['o_users_online'] == '1')
	$links[] = array('id' => 'navonline', 'class' => ((PANTHER_ACTIVE_PAGE == 'online') ? 'isactive' : ''), 'page' => panther_link($panther_url['online']), 'title' => $lang_common['Online']);

if ($panther_config['o_rules'] == '1' && (!$panther_user['is_guest'] || $panther_user['g_read_board'] == '1' || $panther_config['o_regs_allow'] == '1'))
	$links[] = array('id' => 'navrules', 'class' => ((PANTHER_ACTIVE_PAGE == 'rules') ? 'isactive' : ''), 'page' => panther_link($panther_url['rules']), 'title' => $lang_common['Rules']);

if ($panther_user['g_read_board'] == '1' && $panther_user['g_search'] == '1')
	$links[] = array('id' => 'navsearch', 'class' => ((PANTHER_ACTIVE_PAGE == 'search') ? 'isactive' : ''), 'page' => panther_link($panther_url['search']), 'title' => $lang_common['Search']);

if ($panther_user['is_guest'])
{
	$links[] = array('id' => 'navregister', 'class' => ((PANTHER_ACTIVE_PAGE == 'register') ? 'isactive' : ''), 'page' => panther_link($panther_url['register']), 'title' => $lang_common['Register']);
	$links[] = array('id' => 'navlogin', 'class' => ((PANTHER_ACTIVE_PAGE == 'login') ? 'isactive' : ''), 'page' => panther_link($panther_url['login']), 'title' => $lang_common['Login']);
}
else
{	// To avoid another preg replace, link directly to the essentials section
	$links[] = array('id' => 'navprofile', 'class' => ((PANTHER_ACTIVE_PAGE == 'profile') ? 'isactive' : ''), 'page' => panther_link($panther_url['profile_essentials'], array($panther_user['id'])), 'title' => $lang_common['Profile']);

	if ($panther_config['o_private_messaging'] == '1' && $panther_user['g_use_pm'] == '1' && $panther_user['pm_enabled'] == '1')
	{
		$header_data = array(
			':uid'	=>	$panther_user['id'],
		);

		$ps_header = $db->run('SELECT COUNT(c.id) FROM '.$db->prefix.'conversations AS c INNER JOIN '.$db->prefix.'pms_data AS cd ON c.id=cd.topic_id AND cd.user_id=:uid WHERE cd.viewed=0 AND cd.deleted=0', $header_data);
		$num_messages = $ps_header->fetchColumn();

		$pm_lang = ($num_messages) ? sprintf($lang_common['PM amount'], $num_messages) : $lang_common['PM'];
		$links[] = array('id' => 'navpm', 'class' => ((PANTHER_ACTIVE_PAGE == 'pm') ? 'isactive' : ''), 'page' => panther_link($panther_url['inbox']), 'title' => $pm_lang);
	}

	if ($panther_user['is_admmod'] && ($panther_user['is_admin'] || $panther_user['g_mod_cp'] == '1'))
		$links[] = array('id' => 'navadmin', 'class' => ((PANTHER_ACTIVE_PAGE == 'admin') ? 'isactive' : ''), 'page' => panther_link($panther_url['admin_index']), 'title' => $lang_common['Admin']);

    $links[] = array('id' => 'navlogout', 'class' => '', 'page' => panther_link($panther_url['logout'], array($panther_user['id'], generate_csrf_token('login.php'))), 'title' => $lang_common['Logout']);
}

// Are there any additional navlinks we should insert into the array before imploding it?
if ($panther_user['g_read_board'] == '1' && $panther_config['o_additional_navlinks'] != '')
{
	if (preg_match_all('%([0-9]+)\s*=\s*(.*?)\n%s', $panther_config['o_additional_navlinks']."\n", $extra_links))
	{
		// Insert any additional links into the $links array (at the correct index)
		$num_links = count($extra_links[1]);
		for ($i = 0; $i < $num_links; ++$i)
		{
			$link = explode('|', $extra_links[2][$i]);
			array_splice($links, $extra_links[1][$i], 0, array(array('id' => 'navextra'.($i + 1), 'class' => '', 'page' => $link[0], 'title' => $link[1])));
		}
	}
}

if (defined('PANTHER_ADMIN_CONSOLE'))
{
	if (file_exists(PANTHER_ROOT.'style/'.$panther_user['style'].'/base_admin.css'))
		$style_root = (($panther_config['o_style_dir']) != '' ? $panther_config['o_style_dir'] : get_base_url().'/style/').$panther_user['style'];
	else
		$style_root = (($panther_config['o_style_dir']) != '' ? $panther_config['o_style_dir'] : get_base_url().'/style/').'imports';
}
else
	$style_root = '';

$reports = array();
if ($panther_user['is_admmod'])
{
	if ($panther_config['o_report_method'] == '0' || $panther_config['o_report_method'] == '2')
	{
		$ps_header = $db->select('reports', 1, array(), 'zapped IS NULL');
		if ($ps_header->rowCount())
			$reports[] = array('link' => panther_link($panther_url['admin_reports']), 'title' => $lang_common['New reports']);
	}

	$ps_header = $db->select('posts', 1, array(), 'approved=0 AND deleted=0');
	if ($ps_header->rowCount())
		$reports[] = array('link' => panther_link($panther_url['admin_posts']), 'title' => $lang_common['New unapproved posts']);
}

$status_info = array();
if ($panther_user['g_read_board'] == '1' && $panther_user['g_search'] == '1')
{
	if (!$panther_user['is_guest'])
	{
		$status_info[] = array('link' => panther_link($panther_url['search_replies']), 'title' => $lang_common['Show posted topics'], 'display' => $lang_common['Posted topics']);
		$status_info[] = array('link' => panther_link($panther_url['search_new']), 'title' => $lang_common['Show new posts'], 'display' => $lang_common['New posts header']);
	}

	$status_info[] = array('link' => panther_link($panther_url['search_recent']), 'title' => $lang_common['Show active topics'], 'display' => $lang_common['Active topics']);
	$status_info[] = array('link' => panther_link($panther_url['search_unanswered']), 'title' => $lang_common['Show unanswered topics'], 'display' => $lang_common['Unanswered topics']);
}

if (isset($required_fields))
{
	$element = '';
	$tpl_temp = count($required_fields);
	foreach ($required_fields as $elem_orig => $elem_trans)
	{
		$element .= "\t\t\"".$elem_orig.'": "'.addslashes(str_replace('&#160;', ' ', $elem_trans));
		if (--$tpl_temp) $element .= "\",\n";
		else $element .= "\"\n\t};\n";
	}
}
else
	$element = '';

ob_start();
($hook = get_extensions('header_before_output')) ? eval($hook) : null;

$style_path = (($panther_config['o_style_path'] != 'style') ? $panther_config['o_style_path'] : PANTHER_ROOT.$panther_config['o_style_path']).'/'.$panther_user['style'].'/templates/';
$tpl = (defined('PANTHER_ADMIN_CONSOLE') && (file_exists($style_path.'admin_header.tpl') || $panther_user['style'] == $panther_config['o_default_style'] && !file_exists($style_path)) ? 'admin_header.tpl' : 'header.tpl');
$tpl = load_template($tpl);
echo $tpl->render(
	array(
		'panther_config' => $panther_config,
		'panther_user' => $panther_user,
		'username' => colourize_group($panther_user['username'], $panther_user['group_id'], $panther_user['id']),
		'last_visit' => format_time($panther_user['last_visit']),
		'lang_common' => $lang_common,
		'page_title' => generate_page_title($page_title, $p),
		'stylesheet' => (($panther_config['o_style_dir']) != '' ? $panther_config['o_style_dir'] : get_base_url().'/style/').$panther_user['style'],
		'favicon' => $panther_config['o_image_dir'].$panther_config['o_favicon'],
		'page' => basename($_SERVER['PHP_SELF'], '.php'),
		'index_url' => panther_link($panther_url['index']),
		'links' => $links,
		'inbox_link' => panther_link($panther_url['inbox']),
		'maintenance_link' => panther_link($panther_url['admin_options_direct'], array('maintenance')),
		'status_info' => $status_info,
		'reports' => $reports,
		'admin_style' => $style_root,
		'smiley_path' => (($panther_config['o_smilies_dir'] != '') ? $panther_config['o_smilies_dir'] : get_base_url().'/'.$panther_config['o_smilies_path'].'/'),
		'jquery' => (defined('JQUERY_REQUIRED') || (defined('POSTING') && $panther_config['o_use_editor'] == '1' && $panther_user['use_editor'] == '1') || defined('REPUTATION'))?  '1' : '0',
		'reputation' => defined('REPUTATION') ? '1' : '0',
		'posting' => defined('POSTING') && $panther_config['o_use_editor'] == '1' && $panther_user['use_editor'] == '1' ? '1' : '0',
		'admin_index' => defined('ADMIN_INDEX') ? '1' : '0',
		'required_fields' => $element,
		'focus_element' => isset($focus_element) ? $focus_element : array(),
		'page_head' => !empty($page_head) ? $page_head : array(),
		'allow_index' => defined('PANTHER_ALLOW_INDEX') ? '1' : '0',
		'common' => defined('COMMON_JAVASCRIPT') ? true : false,
	)
);

($hook = get_extensions('header_after_output')) ? eval($hook) : null;

define('PANTHER_HEADER', 1);
