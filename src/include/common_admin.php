<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PANTHER'))
	exit;

define('PANTHER_ADMIN_CONSOLE', 1);

// Make sure we have a usable language pack for admin.
if (file_exists(PANTHER_ROOT.'lang/'.$panther_user['language'].'/admin_common.php'))
	$admin_language = $panther_user['language'];
else if (file_exists(PANTHER_ROOT.'lang/'.$panther_config['o_default_lang'].'/admin_common.php'))
	$admin_language = $panther_config['o_default_lang'];
else
	$admin_language = 'English';

if (file_exists(FORUM_CACHE_DIR.'cache_restrictions.php'))
	require FORUM_CACHE_DIR.'cache_restrictions.php';
else
{
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';
   
	generate_admin_restrictions_cache();
	require FORUM_CACHE_DIR.'cache_restrictions.php';
}

if (!isset($admins[$panther_user['id']]) || $panther_user['id'] == '2')
	$admins[$panther_user['id']] = array(
		'admin_options' => 1,
		'admin_permissions' => 1,
		'admin_categories' => 1,
		'admin_forums' => 1,
		'admin_groups' => 1,
		'admin_censoring' => 1,
		'admin_maintenance' => 1,
		'admin_plugins' => 1,
		'admin_restrictions' => 1,
		'admin_users' => 1,
		'admin_moderate' => 1,
		'admin_ranks' => 1,
		'admin_updates' => 1,
		'admin_archive' => 1,
		'admin_smilies' => 1,
		'admin_warnings' => 1,
		'admin_attachments' => 1,
		'admin_robots' => 1,
		'admin_addons' => 1,
		'admin_tasks' => 1,
	);

// Attempt to load the admin_common language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_common.php';

//
// Display the admin navigation menu
//
function generate_admin_menu($page = '')
{
	global $panther_config, $panther_user, $lang_admin_common, $admins, $panther_url, $updater;
	$admin_menu = array();

	if ($panther_user['is_admin'])
	{
		if ($admins[$panther_user['id']]['admin_options'] == '1')
			$admin_menu[] = array('page' => 'options', 'href' => panther_link($panther_url['admin_options']), 'title' => $lang_admin_common['Options']);

		if ($admins[$panther_user['id']]['admin_archive'] == '1')
			$admin_menu[] = array('page' => 'archive', 'href' => panther_link($panther_url['admin_archive']), 'title' => $lang_admin_common['Archive']);

		if ($admins[$panther_user['id']]['admin_permissions'] == '1')
			$admin_menu[] = array('page' => 'permissions', 'href' => panther_link($panther_url['admin_permissions']), 'title' => $lang_admin_common['Permissions']);

		if ($admins[$panther_user['id']]['admin_categories'] == '1')
			$admin_menu[] = array('page' => 'categories', 'href' => panther_link($panther_url['admin_categories']), 'title' => $lang_admin_common['Categories']);

		if ($admins[$panther_user['id']]['admin_forums'] == '1')
			$admin_menu[] = array('page' => 'forums', 'href' => panther_link($panther_url['admin_forums']), 'title' => $lang_admin_common['Forums']);
			
		if ($admins[$panther_user['id']]['admin_groups'] == '1')
			$admin_menu[] = array('page' => 'groups', 'href' => panther_link($panther_url['admin_groups']), 'title' => $lang_admin_common['User groups']);
			
		if ($admins[$panther_user['id']]['admin_censoring'] == '1')
			$admin_menu[] = array('page' => 'censoring', 'href' => panther_link($panther_url['admin_censoring']), 'title' => $lang_admin_common['Censoring']);

		if ($admins[$panther_user['id']]['admin_ranks'] == '1') 
			$admin_menu[] = array('page' => 'ranks', 'href' => panther_link($panther_url['admin_ranks']), 'title' => $lang_admin_common['Ranks']); 

		if ($admins[$panther_user['id']]['admin_robots'] == '1')
			$admin_menu[] = array('page' => 'robots', 'href' => panther_link($panther_url['admin_robots']), 'title' => $lang_admin_common['Robots']);

		if ($admins[$panther_user['id']]['admin_smilies'] == '1' && $panther_config['o_smilies'] == '1')
			$admin_menu[] = array('page' => 'smilies', 'href' => panther_link($panther_url['admin_smilies']), 'title' => $lang_admin_common['Smilies']);
			
		if ($admins[$panther_user['id']]['admin_warnings'] == '1' && $panther_config['o_warnings'] == '1')
			$admin_menu[] = array('page' => 'warnings', 'href' => panther_link($panther_url['admin_warnings']), 'title' => $lang_admin_common['Warnings']);

		if ($admins[$panther_user['id']]['admin_moderate'] == '1')
			$admin_menu[] = array('page' => 'moderate', 'href' => panther_link($panther_url['admin_moderate']), 'title' => $lang_admin_common['Moderate']);

		if ($admins[$panther_user['id']]['admin_attachments'] == '1' && $panther_config['o_attachments'] == '1')
			$admin_menu[] = array('page' => 'attachments', 'href' => panther_link($panther_url['admin_attachments']), 'title' => $lang_admin_common['Attachments']);

		if ($admins[$panther_user['id']]['admin_restrictions'] == '1')
			$admin_menu[] = array('page' => 'restrictions', 'href' => panther_link($panther_url['admin_restrictions']), 'title' => $lang_admin_common['Restrictions']);

		if ($admins[$panther_user['id']]['admin_tasks'] == '1')
			$admin_menu[] = array('page' => 'tasks', 'href' => panther_link($panther_url['admin_tasks']), 'title' => $lang_admin_common['Tasks']);

		if ($admins[$panther_user['id']]['admin_addons'] == '1')
			$admin_menu[] = array('page' => 'extensions', 'href' => panther_link($panther_url['admin_addons']), 'title' => $lang_admin_common['Extensions']);

		if ($admins[$panther_user['id']]['admin_maintenance'] == '1')
			$admin_menu[] = array('page' => 'maintenance', 'href' => panther_link($panther_url['admin_maintenance']), 'title' => $lang_admin_common['Maintenance']);

		if ($admins[$panther_user['id']]['admin_updates'] == '1' && version_compare($panther_config['o_cur_version'], $updater->panther_updates['version'], '<') && $panther_config['o_update_type'] != 0)
			$admin_menu[] = array('page' => 'updates', 'href' => panther_link($panther_url['admin_updates']), 'title' => $lang_admin_common['Updates']);
	}

	$plugin_menu = array();
	$plugins = forum_list_plugins($panther_user['is_admin']);
	if (!empty($plugins) && ($admins[$panther_user['id']]['admin_plugins'] == '1'))
	{
		foreach ($plugins as $plugin_name => $plugin)
			$plugin_menu[] = array('page' => $plugin_name, 'href' => panther_link($panther_url['admin_loader'], array($plugin_name)), 'title' => str_replace('_', ' ', $plugin));
	}

	$tpl = load_template('admin_sidebar.tpl');
	echo $tpl->render(
		array(
			'lang_admin_common' => $lang_admin_common,
			'panther_user' => $panther_user,
			'panther_config' => $panther_config,
			'page' => $page,
			'ban_link' => panther_link($panther_url['admin_bans']),
			'index_link' => panther_link($panther_url['admin_index']),
			'users_link' => panther_link($panther_url['admin_users']),
			'announce_link' => panther_link($panther_url['admin_announcements']),
			'posts_link' => panther_link($panther_url['admin_posts']),
			'reports_link' => panther_link($panther_url['admin_reports']),
			'deleted_link' => panther_link($panther_url['admin_deleted']),
			'admin_menu' => $admin_menu,
			'plugin_menu' => $plugin_menu,
		)
	);
}

//
// Delete topics from $forum_id that are "older than" $prune_date (if $prune_sticky is 1, sticky topics will also be deleted)
//
function prune($forum_id, $prune_sticky, $prune_date)
{
	global $db;
	
	$data = array(
		':id'	=>	$forum_id,
	);

	$where_cond = 'forum_id=:id';
	if ($prune_date != -1)
	{
		$where_cond .= ' And last_post<:last_post';
		$data[':last_post'] = $prune_date;
	}

	if (!$prune_sticky)
		$where_cond .= ' AND sticky=0';

	// Fetch topics to prune
	$ps = $db->select('topics', 'id', $data, $where_cond);

	$topic_ids = array();
	foreach ($ps as $cur_topic)
	{
		$topic_ids[] = $cur_topic['id'];
		$placeholders[] = '?';
	}

	if (!empty($topic_ids))
	{
		// Fetch posts to prune
		$ps = $db->run('SELECT id FROM '.$db->prefix.'posts WHERE topic_id IN('.implode(',', $placeholders).')', $topic_ids);

		$post_ids = array();
		foreach ($ps as $cur_post)
		{
			$markers[] = '?';
			$post_ids[] = $cur_post['id'];
		}

		if ($post_ids != '')
		{
			$db->run('DELETE FROM '.$db->prefix.'topics WHERE id IN('.implode(',', $placeholders).')', $topic_ids);
			$db->run('DELETE FROM '.$db->prefix.'topic_subscriptions WHERE topic_id IN('.implode(',', $placeholders).')', $topic_ids);
			$db->run('DELETE FROM '.$db->prefix.'posts WHERE id IN('.implode(',', $markers).')', $post_ids);

			// We removed a bunch of posts, so now we have to update the search index
			require_once PANTHER_ROOT.'include/search_idx.php';
			strip_search_index($post_ids);
		}
	}
}

//
// Fetch a list of available admin plugins
//
function forum_list_plugins($is_admin)
{
	$plugins = array();
	$files = array_diff(scandir(PANTHER_ROOT.PANTHER_ADMIN_DIR.'/plugins'), array('.', '..'));
	foreach ($files as $entry)
	{
		$prefix = substr($entry, 0, strpos($entry, '_'));
		$suffix = substr($entry, strlen($entry) - 4);

		if ($suffix == '.php' && ((!$is_admin && $prefix == 'AMP') || ($is_admin && ($prefix == 'AP' || $prefix == 'AMP'))))
			$plugins[$entry] = substr($entry, strpos($entry, '_') + 1, -4);
	}

	natcasesort($plugins);
	return $plugins;
}

function forum_list_themes()
{ 
	global $panther_config;
	$style_root = ($panther_config['o_style_path'] != 'style') ? $panther_config['o_style_path'] : PANTHER_ROOT.$panther_config['o_style_path'];
	if (!file_exists($style_root.'/'.$panther_config['o_default_style'].'/themes'))
		return;

	$themes = array();
	$files = array_diff(scandir($style_root.'/'.$panther_config['o_default_style'].'/themes'), array('.', '..'));
	foreach ($files as $theme)
	{
		if (substr($theme, -4) == '.css')
		$themes[] = substr($theme, 0, -4);
	}

	return $themes;
}