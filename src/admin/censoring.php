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
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_censoring.php';

// Add a censor word
if (isset($_POST['add_word']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/censoring.php');

	$search_for = panther_trim($_POST['new_search_for']);
	$replace_with = panther_trim($_POST['new_replace_with']);

	if ($search_for == '')
		message($lang_admin_censoring['Must enter word message']);

	$insert = array(
		'search_for'	=>	$search_for,
		'replace_with'	=>	$replace_with,
	);
	
	$db->insert('censoring', $insert);

	// Regenerate the censoring cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_censoring_cache();
	redirect(panther_link($panther_url['admin_censoring']), $lang_admin_censoring['Word added redirect']);
}
else if (isset($_POST['update'])) // Update a censor word
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/censoring.php');
	$id = intval(key($_POST['update']));

	$search_for = isset($_POST['search_for'][$id]) ? panther_trim($_POST['search_for'][$id]) : '';
	$replace_with = isset($_POST['replace_with'][$id]) ? panther_trim($_POST['replace_with'][$id]) : '';

	if ($search_for == '')
		message($lang_admin_censoring['Must enter word message']);

	$update = array(
		'search_for' => $search_for,
		'replace_with' => $replace_with,
	);
	
	$data = array(
		':id'	=>	$id,
	);
	
	$db->update('censoring', $update, 'id=:id', $data);

	// Regenerate the censoring cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_censoring_cache();
	redirect(panther_link($panther_url['admin_censoring']), $lang_admin_censoring['Word updated redirect']);
}
else if (isset($_POST['remove'])) // Remove a censor word
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/censoring.php');
	$id = intval(key($_POST['remove']));
	$data = array(
		':id'	=>	$id,
	);

	$db->delete('censoring', 'id=:id', $data);

	// Regenerate the censoring cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_censoring_cache();
	redirect(panther_link($panther_url['admin_censoring']),  $lang_admin_censoring['Word removed redirect']);
}

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Censoring']);
$focus_element = array('censoring', 'new_search_for');
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

generate_admin_menu('censoring');

$words = array();
$ps = $db->select('censoring', 'id, search_for, replace_with', array(), '', 'id');
foreach ($ps as $cur_word)
	$words[] = array(
		'id' => $cur_word['id'],
		'search_for' => $cur_word['search_for'],
		'replace_with' => $cur_word['replace_with'],
	);

$tpl = load_template('admin_censoring.tpl');
echo $tpl->render(
	array(
		'form_action' => panther_link($panther_url['admin_censoring']),
		'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/censoring.php'),
		'lang_admin_censoring' => $lang_admin_censoring,
		'lang_admin_common' => $lang_admin_common,
		'panther_config' => $panther_config,
		'link' => panther_link($panther_url['admin_options']),
		'words' => $words,
	)
);

require PANTHER_ROOT.'footer.php';