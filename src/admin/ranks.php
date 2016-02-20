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
	if(!is_null($admins[$panther_user['id']]['admin_ranks']))
	{
		if ($admins[$panther_user['id']]['admin_ranks'] == '0')
			message($lang_common['No permission'], false, '403 Forbidden');
	}
}

check_authentication();

// Load the admin_ranks.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_ranks.php';

// Add a rank
if (isset($_POST['add_rank']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/ranks.php');

	$rank = isset($_POST['new_rank']) ? panther_trim($_POST['new_rank']) : '';
	$min_posts = isset($_POST['new_min_posts']) ? panther_trim($_POST['new_min_posts']) : '';

	if ($rank == '')
		message($lang_admin_ranks['Must enter title message']);

	if ($min_posts == '' || preg_match('%[^0-9]%', $min_posts))
		message($lang_admin_ranks['Must be integer message']);

	// Make sure there isn't already a rank with the same min_posts value
	$data = array(
		':posts'	=>	$min_posts,
	);

	$ps = $db->select('ranks', 1, $data, 'min_posts=:posts');
	if ($ps->rowCount())
		message(sprintf($lang_admin_ranks['Dupe min posts message'], $min_posts));
	
	$insert = array(
		'rank'	=>	$rank,
		'min_posts'	=>	$min_posts,
	);

	$db->insert('ranks', $insert);

	// Regenerate the ranks cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_ranks_cache();
	redirect(panther_link($panther_url['admin_ranks']), $lang_admin_ranks['Rank added redirect']);
}
else if (isset($_POST['update'])) // Update a rank
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/ranks.php');

	$id = intval(key($_POST['update']));

	$rank = isset($_POST['rank'][$id]) ? panther_trim($_POST['rank'][$id]) : '';
	$min_posts = isset($_POST['min_posts'][$id]) ? panther_trim($_POST['min_posts'][$id]) : '';

	if ($rank == '')
		message($lang_admin_ranks['Must enter title message']);

	if ($min_posts == '' || preg_match('%[^0-9]%', $min_posts))
		message($lang_admin_ranks['Must be integer message']);

	// Make sure there isn't already a rank with the same min_posts value
	$data = array(
		':id' => $id,
		':posts' => $min_posts,
	);

	$ps = $db->select('ranks', 1, $data, 'id!=:id AND min_posts=:posts');
	if ($ps->rowCount())
		message(sprintf($lang_admin_ranks['Dupe min posts message'], $min_posts));

	$update = array(
		'rank'	=>	$rank,
		'min_posts'	=>	$min_posts,
	);

	$data = array(
		':id'	=>	$id,
	);

	$db->update('ranks', $update, 'id=:id', $data);

	// Regenerate the ranks cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_ranks_cache();
	redirect(panther_link($panther_url['admin_ranks']), $lang_admin_ranks['Rank updated redirect']);
}
else if (isset($_POST['remove'])) // Remove a rank
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/ranks.php');

	$id = intval(key($_POST['remove']));
	$data = array(
		':id'	=>	$id,
	);

	$db->delete('ranks', 'id=:id', $data);

	// Regenerate the ranks cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_ranks_cache();
	redirect(panther_link($panther_url['admin_ranks']), $lang_admin_ranks['Rank removed redirect']);
}

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Ranks']);
$focus_element = array('ranks', 'new_rank');
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

generate_admin_menu('ranks');

$ranks = array();
$ps = $db->select('ranks', 'id, rank, min_posts', array(), '', 'min_posts');
foreach ($ps as $cur_rank)
	$ranks[] = array('id' => $cur_rank['id'], 'rank' => $cur_rank['rank'], 'min_posts' => $cur_rank['min_posts']);

$tpl = load_template('admin_ranks.tpl');
echo $tpl->render(
	array(
		'lang_admin_ranks' => $lang_admin_ranks,
		'lang_admin_common' => $lang_admin_common,
		'form_action' => panther_link($panther_url['admin_ranks']),
		'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/ranks.php'),
		'admin_options' => panther_link($panther_url['admin_options']),
		'ranks' => $ranks,
		'panther_config' => $panther_config,
	)
);

require PANTHER_ROOT.'footer.php';