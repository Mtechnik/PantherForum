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
	if(!is_null($admins[$panther_user['id']]['admin_categories']))
	{
		if ($admins[$panther_user['id']]['admin_categories'] == '0')
			message($lang_common['No permission'], false, '403 Forbidden');
	}
}

check_authentication();

// Load the admin_categories.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_categories.php';

// Add a new category
if (isset($_POST['add_cat']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/categories.php');

	$new_cat_name = isset($_POST['new_cat_name']) ? panther_trim($_POST['new_cat_name']) : '';
	if ($new_cat_name == '')
		message($lang_admin_categories['Must enter name message']);

	$insert = array(
		'cat_name'	=>	$new_cat_name,
	);

	$db->insert('categories', $insert);
	redirect(panther_link($panther_url['admin_categories']), $lang_admin_categories['Category added redirect']);
}

// Delete a category
else if (isset($_POST['del_cat']) || isset($_POST['del_cat_comply']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/categories.php');

	$cat_to_delete = isset($_POST['cat_to_delete']) ? intval($_POST['cat_to_delete']) : 0;
	if ($cat_to_delete < 1)
		message($lang_common['Bad request'], false, '404 Not Found');

	if (isset($_POST['del_cat_comply'])) // Delete a category with all forums and posts
	{
		@set_time_limit(0);
		$data = array(
			':id' => $cat_to_delete,
		);

		$ps = $db->select('forums', 'id', $data, 'cat_id=:id');
		$ps->setFetchMode(PDO::FETCH_COLUMN, 0);
		foreach ($ps as $cur_forum)
		{
			prune($cur_forum, 1, -1);
			$data = array(
				':id'	=>	$cur_forum,
			);

			// Delete the forum
			$db->delete('forums', 'id=:id', $data);
		}

		// Locate any "orphaned redirect topics" and delete them
		$ps = $db->run('SELECT t1.id FROM '.$db->prefix.'topics AS t1 LEFT JOIN '.$db->prefix.'topics AS t2 ON t1.moved_to=t2.id WHERE t2.id IS NULL AND t1.moved_to IS NOT NULL');
		if ($ps->rowCount())
		{
			$data = $markers = array();
			$ps->setFetchMode(PDO::FETCH_COLUMN, 0);
			foreach ($ps as $orphan)
			{
				$markers[] = '?';
				$data[] = $orphan;
			}
			
			$db->run('DELETE FROM '.$db->prefix.'topics WHERE id IN('.implode(',', $markers).')', $data);
		}
		
		$data = array(
			':id'	=>	$cat_to_delete
		);

		// Delete the category
		$db->delete('categories', 'id=:id', $data);

		// Regenerate the quick jump cache
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';

		generate_forums_cache();
		generate_quickjump_cache();
		generate_perms_cache();
		redirect(panther_link($panther_url['admin_categories']), $lang_admin_categories['Category deleted redirect']);
	}
	else // If the user hasn't confirmed the delete
	{
		$data = array(
			':id' => $cat_to_delete,
		);

		$ps = $db->select('categories', 'cat_name', $data, 'id=:id');
		$cat_name = $ps->fetchColumn();

		$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Categories']);
		define('PANTHER_ACTIVE_PAGE', 'admin');
		require PANTHER_ROOT.'header.php';

		generate_admin_menu('categories');
		
		$tpl = load_template('delete_category.tpl');
		echo $tpl->render(
			array(
				'lang_admin_categories' => $lang_admin_categories,
				'lang_admin_common' => $lang_admin_common,
				'form_action' => panther_link($panther_url['admin_categories']),
				'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/categories.php'),
				'cat_name' => $cat_name,
				'cat_to_delete' => $cat_to_delete,
			)
		);

		require PANTHER_ROOT.'footer.php';
	}
}
else if (isset($_POST['update'])) // Change position and name of the categories
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/categories.php');

	$categories = isset($_POST['cat']) && is_array($_POST['cat']) ? $_POST['cat'] : array();
	if (empty($categories))
		message($lang_common['Bad request'], false, '404 Not Found');

	foreach ($categories as $cat_id => $cur_cat)
	{
		$cur_cat['name'] = isset($cur_cat['name']) ? panther_trim($cur_cat['name']) : '';
		$cur_cat['order'] = isset($cur_cat['order']) ? intval($cur_cat['order']) : 0;

		if ($cur_cat['name'] == '')
			message($lang_admin_categories['Must enter name message']);

		if ($cur_cat['order'] < 0)
			message($lang_admin_categories['Must enter integer message']);
		
		$update = array(
			'cat_name'	=>	$cur_cat['name'],
			'disp_position'	=>	$cur_cat['order'],
		);
		
		$data = array(
			':id'	=>	intval($cat_id),
		);

		$db->update('categories', $update, 'id=:id', $data);
	}

	// Regenerate the quick jump cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_quickjump_cache();
	redirect(panther_link($panther_url['admin_categories']), $lang_admin_categories['Categories updated redirect']);
}

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Categories']);
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

generate_admin_menu('categories');

$categories = array();
$ps = $db->select('categories', 'id, cat_name, disp_position', array(), '', 'disp_position');
foreach ($ps as $cur_cat)
	$categories[] = array('id' => $cur_cat['id'], 'name' => $cur_cat['cat_name'], 'disp_position' => $cur_cat['disp_position']);

$tpl = load_template('admin_categories.tpl');
echo $tpl->render(
	array(
		'lang_admin_categories' => $lang_admin_categories,
		'lang_admin_common' => $lang_admin_common,
		'form_action' => panther_link($panther_url['admin_categories']),
		'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/categories.php'),
		'admin_forums' => panther_link($panther_url['admin_forums']),
		'categories' => $categories,
	)
);

require PANTHER_ROOT.'footer.php';