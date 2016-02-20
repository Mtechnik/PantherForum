<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PANTHER'))
	exit;


//
// Generate the config cache PHP script
//
function generate_config_cache()
{
	global $db, $panther_config;

	$style = '';
	$ps = $db->select('groups', 'g_id, g_colour, g_global_moderator', array(), '', 'g_id ASC');
	$group_style = array();
	foreach ($ps as $cur_group)
	{
		$group_style = array();
		if (!empty($cur_group['g_colour']))
			$group_style[] = 'color: '.$cur_group['g_colour'];

			// Any group except default user group
		if ($cur_group['g_id'] != $panther_config['o_default_user_group'])
			$group_style[] = 'font-weight: bold';

			// Global moderators and admins should be italic
		if ($cur_group['g_global_moderator'] == '1' || $cur_group['g_id'] == PANTHER_ADMIN)
			$group_style[] = 'font-style: italic';

		if (!empty($group_style))
			$style .= '.gid'.$cur_group['g_id'].' {'.implode('; ', $group_style).'} ';
	}

	$update = array(
		'conf_value' => $style,
	);
		
	$db->update('config', $update, "conf_name = 'o_colourize_groups'");

	$output = array();
	$ps = $db->select('config');
	foreach ($ps as $cur_item)
		$output[$cur_item['conf_name']] = $cur_item['conf_value'];

	// Output config as PHP code
	$content = '<?php'."\n\n".'define(\'PANTHER_CONFIG_LOADED\', 1);'."\n\n".'$panther_config = '.var_export($output, true).';'."\n\n".'?>';
	panther_write_cache_file('cache_config.php', $content);
}

//
// Generate the groups cache PHP script
//
function generate_groups_cache()
{
	global $db;
	$output = '<?php'."\n\n".'if (!defined(\'PANTHER\')) exit;'."\n\n".'define(\'PANTHER_GROUPS_LOADED\', 1);'."\n\n".'$panther_groups = array();'."\n\n";

	$ps = $db->select('groups', '*', array(), '', 'g_id ASC');
	foreach ($ps as $cur_group)
		$output .= '$panther_groups[\''.$cur_group['g_id'].'\'] = '.var_export($cur_group, true).';'."\n\n";

	panther_write_cache_file('cache_groups.php', $output);
}

//
// Generate the bans cache PHP script
//
function generate_bans_cache()
{
	global $db;

	$output = array();
	$ps = $db->select('bans');
	foreach ($ps as $cur_ban)
		$output[] = $cur_ban;

	// Output ban list as PHP code
	$content = '<?php'."\n\n".'define(\'PANTHER_BANS_LOADED\', 1);'."\n\n".'$panther_bans = '.var_export($output, true).';'."\n\n".'?>';
	panther_write_cache_file('cache_bans.php', $content);
}

//
// Generate the ranks cache PHP script
//
function generate_ranks_cache()
{
	global $db;

	$output = array();
	$ps = $db->select('ranks', 'id, rank, min_posts', array(), '', 'min_posts');
	foreach ($ps as $cur_rank)
		$output[] = $cur_rank;

	$output = '<?php'."\n\n".'define(\'PANTHER_RANKS_LOADED\', 1);'."\n\n".'$panther_ranks = '.var_export($output, true).';'."\n\n".'?>';
	panther_write_cache_file('cache_ranks.php', $output);
}

//
// Generate the robot questions cache PHP script
//
function generate_robots_cache()
{
	global $db;

	$output = array();
	$ps = $db->select('robots', 'id, question, answer', array(), '', 'id');
	foreach ($ps as $cur_test)
		$output[$cur_test['id']] = $cur_test;

	$output = '<?php'."\n\n".'define(\'PANTHER_ROBOTS_LOADED\', 1);'."\n\n".'$panther_robots = '.var_export($output, true).';'."\n\n".'?>';
	panther_write_cache_file('cache_robots.php', $output);
}

//
// Generate forum cache script
//

function generate_forums_cache()
{
	global $db;

	$output = '<?php'."\n\n".'if (!defined(\'PANTHER\')) exit;'."\n\n".'define(\'PANTHER_FORUMS_LOADED\', 1);'."\n\n".'$panther_forums = array();'."\n\n";

	$ps = $db->select('forums');
	foreach ($ps as $cur_forum)
		$output .= '$panther_forums[\''.$cur_forum['id'].'\'] = '.var_export($cur_forum, true).';'."\n\n";

	panther_write_cache_file('cache_forums.php', $output);
}

//
// Generate quick jump cache PHP scripts
//
function generate_quickjump_cache($group_id = false, $read_board = 1)
{
	global $db, $lang_common, $panther_url, $panther_groups;

	$groups = array();
	$base_url = get_base_url();
	// If a group_id was supplied, we generate the quick jump cache for that group only
	if ($group_id !== false)
		$groups[$group_id] = isset($panther_groups[$group_id]['g_read_board']) ? $panther_groups[$group_id]['g_read_board'] : $read_board;
	else
	{
		// A group_id was not supplied, so we generate the quick jump cache for all groups
		foreach ($panther_groups as $cur_group)
			$groups[$cur_group['g_id']] = $cur_group['g_read_board'];
	}

	// Loop through the groups in $groups and output the cache for each of them
	foreach ($groups as $group_id => $read_board)
	{
		// Output quickjump as PHP code
		$output = '<?php'."\n\n".'if (!defined(\'PANTHER\')) exit;'."\n".'define(\'PANTHER_QJ_LOADED\', 1);'."\n".'$forum_id = isset($forum_id) ? $forum_id : 0;'."\n\n".'?>';

		if ($read_board == '1')
		{
			$data = array(
				':id'	=>	$group_id,
			);

			$categories = $forums = array();
			$ps = $db->run('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name, f.redirect_url, f.parent_forum FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:id) WHERE f.quickjump=1 AND (fp.read_forum IS NULL OR fp.read_forum=1) ORDER BY c.disp_position, c.id, f.disp_position', $data);
			if ($ps->rowCount())
			{
				$tpl = load_template('quickjump.tpl');
				foreach ($ps as $cur_forum)
				{
					if (!isset($categories[$cur_forum['cid']]))
						$categories[$cur_forum['cid']] = array(
							'id' => $cur_forum['cid'],
							'name' => $cur_forum['cat_name'],
						);

					$forums[] = array(
						'id' => $cur_forum['fid'],
						'category_id' => $cur_forum['cid'],
						'name' => $cur_forum['forum_name'],
						'redirect_url' => $cur_forum['redirect_url'],
						'parent_forum' => $cur_forum['parent_forum'],
						'url' => url_friendly($cur_forum['forum_name']),
					);
				}

				$output .= $tpl->render(
					array(
						'lang_common' => $lang_common,
						'base_url' => $base_url,
						'categories' => $categories,
						'forums' => $forums,
						'forum_link' => panther_link($panther_url['forum'], array("'+this.options[this.selectedIndex].value)+'", '\'+this.options[this.selectedIndex].getAttribute(\'data-name\')+\'')),
					)
				);
			}
		}

		panther_write_cache_file('cache_quickjump_'.$group_id.'.php', $output);
	}
}

function generate_smilies_cache()
{
	global $db;

	$smilies = array();
	$ps = $db->select('smilies', 'image, code', array(), '', 'disp_position');
	foreach ($ps as $smiley)
		$smilies[] = "'".addslashes($smiley['code'])."' => '".$smiley['image']."',"."\n";

	$content = '<?php'."\n".'$smilies = array('."\n".(count($smilies) ? implode('', $smilies) : '').');'."\n".'?>'."\n";
	panther_write_cache_file('cache_smilies.php', $content);
}

//
// Generate the censoring cache PHP script
//
function generate_censoring_cache()
{
	global $db;

	$ps = $db->select('censoring', 'search_for, replace_with');
	$num_words = $ps->rowCount();

	$search_for = $replace_with = array();
	for ($i = 0; $i < $num_words; $i++)
	{
		list($search_for[$i], $replace_with[$i]) = $ps->fetch(PDO::FETCH_NUM);
		$search_for[$i] = '%(?<=[^\p{L}\p{N}])('.str_replace('\*', '[\p{L}\p{N}]*?', preg_quote($search_for[$i], '%')).')(?=[^\p{L}\p{N}])%iu';
	}

	// Output censored words as PHP code
	$content = '<?php'."\n\n".'define(\'PANTHER_CENSOR_LOADED\', 1);'."\n\n".'$search_for = '.var_export($search_for, true).';'."\n\n".'$replace_with = '.var_export($replace_with, true).';'."\n\n".'?>';
	panther_write_cache_file('cache_censoring.php', $content);
}

//
// Generate the stopwords cache PHP script
//
function generate_stopwords_cache()
{
	$stopwords = array();

	$d = dir(PANTHER_ROOT.'lang');
	while (($entry = $d->read()) !== false)
	{
		if ($entry{0} == '.')
			continue;

		if (is_dir(PANTHER_ROOT.'lang/'.$entry) && file_exists(PANTHER_ROOT.'lang/'.$entry.'/stopwords.txt'))
			$stopwords = array_merge($stopwords, file(PANTHER_ROOT.'lang/'.$entry.'/stopwords.txt'));
	}
	$d->close();

	// Tidy up and filter the stopwords
	$stopwords = array_map('panther_trim', $stopwords);
	$stopwords = array_filter($stopwords);

	// Output stopwords as PHP code
	$content = '<?php'."\n\n".'$cache_id = \''.generate_stopwords_cache_id().'\';'."\n".'if ($cache_id != generate_stopwords_cache_id()) return;'."\n\n".'define(\'PANTHER_STOPWORDS_LOADED\', 1);'."\n\n".'$stopwords = '.var_export($stopwords, true).';'."\n\n".'?>';
	panther_write_cache_file('cache_stopwords.php', $content);
}

//
// Load some information about the latest registered users
//
function generate_users_info_cache()
{
	global $db;

	$stats = array();
	$data = array(
		':id'	=>	PANTHER_UNVERIFIED,
	);

	$ps = $db->select('users', 'COUNT(id)-1', $data, 'group_id!=:id');
	$stats['total_users'] = $ps->fetchColumn();

	$ps = $db->select('users', 'id, username, group_id', $data, 'group_id!=:id', 'registered DESC LIMIT 1');
	$stats['last_user'] = $ps->fetch();

	// Output users info as PHP code
	$content = '<?php'."\n\n".'define(\'PANTHER_USERS_INFO_LOADED\', 1);'."\n\n".'$stats = '.var_export($stats, true).';'."\n\n".'?>';
	panther_write_cache_file('cache_users_info.php', $content);
}

//
// Generate the announcement cache (forum view)
//
function generate_announcements_cache()
{
	global $db;
	
	$output = array();
	$ps = $db->select('forums', 'id', array(), 'redirect_url IS NULL', 'disp_position');
	$ps->setFetchMode(PDO::FETCH_COLUMN, 0);
	foreach ($ps as $cur_fid)
	{
		// One forum can have many announcements
		if (!isset($output[$cur_fid]))
			$output[$cur_fid] = array();

		$ps1 = $db->select('announcements', 'subject, id, forum_id, user_id, message', array(), '', 'id DESC');
		if (!$ps1->rowCount())
			continue;

		foreach ($ps1 as $cur_announce)
		{
			$forums = explode(',', $cur_announce['forum_id']);
			if (in_array($cur_fid, $forums) || in_array(0, $forums))
			{
				// Cache the preg replace now to avoid it from eating up valuable time when displaying the forum
				$cur_announce['url_subject'] = url_friendly($cur_announce['subject']);
				$output[$cur_fid][] = $cur_announce;
			}
		}
	}
	
	$content = '<?php'."\n\n".'if (!defined(\'PANTHER\')) exit;'."\n"."\n\n".'$panther_announcements = '.var_export($output, true).';'."\n\n".'?>';
	panther_write_cache_file('cache_announcements.php', $content);
}

//
// Generate the admins cache PHP script
//
function generate_admins_cache()
{
	global $db;

	$data = array(
		':id'	=>	PANTHER_ADMIN,
	);

	$output = array();
	$ps = $db->run('SELECT id FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id WHERE u.group_id=:id OR g.g_admin=1', $data);
	foreach ($ps as $cur_admin)
		$output[] = $cur_admin['id'];

	// Output admin list as PHP code
	$content = '<?php'."\n\n".'define(\'PANTHER_ADMINS_LOADED\', 1);'."\n\n".'$panther_admins = '.var_export($output, true).';'."\n\n".'?>';
	panther_write_cache_file('cache_admins.php', $content);
}

//
// Safely write out a cache file.
//
function panther_write_cache_file($file, $content)
{
	$fh = @fopen(FORUM_CACHE_DIR.$file, 'wb');
	if (!$fh)
		error('Unable to write cache file '.$file.' to cache directory. Please make sure PHP has write access to the directory \''.FORUM_CACHE_DIR.'\'');

	flock($fh, LOCK_EX);
	ftruncate($fh, 0);

	fwrite($fh, $content);

	flock($fh, LOCK_UN);
	fclose($fh);

	panther_invalidate_cached_file(FORUM_CACHE_DIR.$file);
}


//
// Delete all feed caches
//
function clear_feed_cache()
{
    $files = array_diff(scandir(FORUM_CACHE_DIR), array('.', '..'));
    foreach ($files as $file)
    {
        if (substr($file, 0, 10) == 'cache_feed' && substr($file, -4) == '.php')
        {
            unlink(FORUM_CACHE_DIR.$file);
            panther_invalidate_cached_file(FORUM_CACHE_DIR.$file);
        }
    }
}


//
// Invalidate updated php files that are cached by an opcache
//
function panther_invalidate_cached_file($file)
{
	if (function_exists('opcache_invalidate'))
		opcache_invalidate($file, true);
	else if (function_exists('apc_delete_file'))
		@apc_delete_file($file);
}

//
// Generate forum permissions cache script
//

function generate_perms_cache()
{
	global $db, $lang_common, $panther_user;

	$groups = array();
	$output = '<?php'."\n\n".'if (!defined(\'PANTHER\')) exit;'."\n\n".'define(\'PANTHER_FP_LOADED\', 1);'."\n"."\n\n".'$perms = array();'."\n\n";

	// A group_id was not supplied, so we generate the permission cache for all groups
	$ps = $db->run('SELECT g.g_read_board, fp.group_id AS id, fp.forum_id, fp.read_forum, fp.post_replies, fp.post_topics FROM '.$db->prefix.'groups AS g LEFT JOIN '.$db->prefix.'forum_perms AS fp ON g.g_id = fp.group_id ORDER BY fp.group_id ASC');
	foreach ($ps as $cur_group)
	{
		$groups = array('read_board' => $cur_group['g_read_board'], 'forum_id' => $cur_group['forum_id'], 'read_forum' => $cur_group['read_forum'], 'post_replies' => $cur_group['post_replies'], 'post_topics' => $cur_group['post_topics']);
		$output .= '$perms[\''.$cur_group['id'].'_'.$cur_group['forum_id'].'\'] = '.var_export($groups, true).';'."\n\n";
	}
	
	panther_write_cache_file('cache_perms.php', $output);
}

//
// Generate admin restrictions cache script
//

function generate_admin_restrictions_cache()
{
	global $db;

	$output = '<?php'."\n\n".'if (!defined(\'PANTHER\')) exit;'."\n"."\n\n".'$admins = array();'."\n\n";
	$data = array(
		':id'	=>	PANTHER_ADMIN,
	);

	$ps = $db->run('SELECT a.restrictions, u.id FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'restrictions AS a ON u.id=a.admin_id INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id WHERE (u.group_id=:id OR g.g_admin=1)AND u.id!=2 ORDER BY id ASC', $data);
	foreach ($ps as $cur_admin)
		$output .= '$admins[\''.$cur_admin['id'].'\'] = '.var_export(unserialize($cur_admin['restrictions']), true).';'."\n\n";

	panther_write_cache_file('cache_restrictions.php', $output);
}

//
// Check for updates to panther
//
function generate_update_cache()
{
	global $lang_admin_common;
   
	$output = trim(file_get_contents('https://www.pantherforum.org/update_check.php'));
	if (empty($output))
		message($lang_admin_common['Upgrade check failed message']);
   
	// Decode the response and set it as the new cache
	$output = json_decode($output, true);
	$output['cached'] = time();
   
	$content = '<?php'."\n\n".'define(\'PANTHER_UPDATES_LOADED\', 1);'."\n\n".'$this->panther_updates = '.var_export($output, true).';'."\n\n".'?>';
	panther_write_cache_file('cache_updates.php', $content);
   
	return $output;
}

//
// Generate the tasks cache
//
function generate_task_cache()
{
	global $db;
	
	$output = '<?php'."\n\n".'if (!defined(\'PANTHER\')) exit;'."\n"."\n\n".'define(\'PANTHER_TASKS_LOADED\', 1);'."\n\n".'$this->panther_tasks = array();'."\n\n";
	$ps = $db->select('tasks');
	foreach ($ps as $cur_task)
		$output .= '$this->panther_tasks['.$cur_task['id'].'] = '.var_export($cur_task, true).';'."\n\n";

	panther_write_cache_file('cache_tasks.php', $output);
}

function generate_extensions_cache()
{
	global $db;

	$output = '<?php'."\n\n".'if (!defined(\'PANTHER\')) exit;'."\n"."\n\n".'define(\'PANTHER_EXTENSIONS_LOADED\', 1);'."\n\n".'$panther_extensions = array();'."\n\n";
	$ps = $db->run('SELECT c.hook, c.code FROM '.$db->prefix.'extension_code AS c INNER JOIN '.$db->prefix.'extensions AS e ON c.extension_id=e.id WHERE e.enabled=1'); // If it's not even enabled, then why on earth attempt to run it and add extra work?
	foreach ($ps as $cur_extension)
		$output .= '$panther_extensions[\''.$cur_extension['hook'].'\'][] = '.var_export($cur_extension['code'], true).';'."\n\n";

	panther_write_cache_file('cache_extensions.php', $output);	
}

($hook = get_extensions('cache_after_functions')) ? eval($hook) : null;

define('FORUM_CACHE_FUNCTIONS_LOADED', true);