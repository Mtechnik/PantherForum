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

// Load the admin_users.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_users.php';

// Show IP statistics for a certain user ID
if (isset($_GET['ip_stats']))
{
	$ip_stats = intval($_GET['ip_stats']);
	if ($ip_stats < 1)
		message($lang_common['Bad request'], false, '404 Not Found');

	// Fetch ip count
	$data = array(
		':id'	=>	$ip_stats,
	);

	$ps = $db->select('posts', 'poster_ip, MAX(posted) AS last_used', $data, 'poster_id=:id GROUP BY poster_ip');
	$num_ips = $ps->rowCount();

	// Determine the ip offset (based on $_GET['p'])
	$num_pages = ceil($num_ips / 50);

	$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);
	$start_from = 50 * ($p - 1);
	
	$data = array(
		':id'	=>	$ip_stats,
		':limit'	=>	$start_from,
	);

	$ps = $db->select('posts', 'poster_ip, MAX(posted) AS last_used, COUNT(id) AS used_times', $data, 'poster_id=:id GROUP BY poster_ip', 'last_used DESC LIMIT :limit, 50');

	$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Users'], $lang_admin_users['Results head']);
	define('PANTHER_ACTIVE_PAGE', 'admin');
	require PANTHER_ROOT.'header.php';
	
	$users = array();
	foreach ($ps as $cur_user)
		$users[] = array('host' => panther_link($panther_url['get_host'], array($cur_user['poster_ip'])), 'poster_ip' => $cur_user['poster_ip'], 'last_used' => format_time($cur_user['last_used']), 'used_times' => $cur_user['used_times'], 'show_link' => panther_link($panther_url['admin_users_users'], array($cur_user['poster_ip'])));

	$tpl = load_template('ip_stats.tpl');
	echo $tpl->render(
		array(
			'lang_admin_common' => $lang_admin_common,
			'lang_admin_users' => $lang_admin_users,
			'lang_common' => $lang_common,
			'index_link' => panther_link($panther_url['admin_index']),
			'users_link' => panther_link($panther_url['admin_users']),
			'pagination' => paginate($num_pages, $p, $panther_url['admin_users_ip_stats'], array($ip_stats)),
			'users' => $users,
		)
	);

	require PANTHER_ROOT.'footer.php';
}

if (isset($_GET['show_users']))
{
	$ip = panther_trim($_GET['show_users']);
	if (!@preg_match('%^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$%', $ip) && !@preg_match('%^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$%', $ip))
		message($lang_admin_users['Bad IP message']);

	// Fetch user count
	$data = array(
		':ip'	=>	$ip,
	);

	$ps = $db->select('posts', 'DISTINCT poster_id', $data, 'poster_ip=:ip');
	$num_users = $ps->rowCount();

	// Determine the user offset (based on $_GET['p'])
	$num_pages = ceil($num_users / 50);

	$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);
	$start_from = 50 * ($p - 1);
	
	$data = array(
		':ip'	=>	$ip,
		':limit'	=>	$start_from,
	);

	$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Users'], $lang_admin_users['Results head']);
	define('PANTHER_ACTIVE_PAGE', 'admin');
	require PANTHER_ROOT.'header.php';
	
	$ps = $db->select('posts', 'DISTINCT poster_id, poster', $data, 'poster_ip=:ip', 'poster ASC LIMIT :limit, 50');
	$num_posts = $ps->rowCount();

	$users = array();
	if ($num_posts)
	{
		$posters = $poster_ids = $markers = $results = array();
		foreach ($ps as $cur_poster)
		{
			$posters[] = $cur_poster;
			$markers[] = '?';
			$poster_ids[] = $cur_poster['poster_id'];
		}

		$ps = $db->run('SELECT u.id, u.username, u.email, u.title, u.num_posts, u.admin_note, g.g_id, g.g_user_title FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE u.id>1 AND u.id IN('.implode(',', $markers).')', $poster_ids);

		$user_data = array();
		foreach ($ps as $cur_user)
			$user_data[$cur_user['id']] = $cur_user;

		// Loop through users and print out some info
		foreach ($posters as $cur_poster)
		{
			if (isset($user_data[$cur_poster['poster_id']]))
			{
				$users[] = array(
					'link' => panther_link($panther_url['profile'], array($user_data[$cur_poster['poster_id']]['id'], url_friendly($user_data[$cur_poster['poster_id']]['username']))),
					'username' => $user_data[$cur_poster['poster_id']]['username'],
					'email' => $user_data[$cur_poster['poster_id']]['email'],
					'title' => get_title($user_data[$cur_poster['poster_id']]),
					'num_posts' => forum_number_format($user_data[$cur_poster['poster_id']]['num_posts']),
					'admin_note' => $user_data[$cur_poster['poster_id']]['admin_note'],
					'ip_stats_link' => panther_link($panther_url['admin_users_ip_stats'], array($user_data[$cur_poster['poster_id']]['id'])),
					'search_posts_link' => panther_link($panther_url['search_user_posts'], array($user_data[$cur_poster['poster_id']]['id'])),
				);
			}
			else
				$users[] = array('poster' => $cur_poster['poster']);
		}
	}

	$tpl = load_template('show_users.tpl');
	echo $tpl->render(
		array(
			'lang_admin_users' => $lang_admin_users,
			'index_link' => panther_link($panther_url['admin_index']),
			'users_link' => panther_link($panther_url['admin_users']),
			'lang_admin_common' => $lang_admin_common,
			'lang_common' => $lang_common,
			'users' => $users,
			'pagination' => paginate($num_pages, $p, $panther_url['admin_users_users'], array($ip)),
		)
	);
	require PANTHER_ROOT.'footer.php';
}

// Move multiple users to other user groups
else if (isset($_POST['move_users']) || isset($_POST['move_users_comply']))
{
	if (!$panther_user['is_admin'])
		message($lang_common['No permission'], false, '403 Forbidden');

	confirm_referrer(PANTHER_ADMIN_DIR.'/users.php');

	if (isset($_POST['users']))
	{
		$user_ids = is_array($_POST['users']) ? array_keys($_POST['users']) : explode(',', $_POST['users']);
		$user_ids = array_map('intval', $user_ids);

		// Delete invalid IDs
		$user_ids = array_diff($user_ids, array(0, 1));
	}
	else
		$user_ids = array();

	if (empty($user_ids))
		message($lang_admin_users['No users selected']);

	$data = array(PANTHER_ADMIN);
	$markers = array();
	for($i = 0; $i < count($user_ids); $i++)
	{
		$markers[] = '?';
		$data[] = $user_ids[$i];
	}

	// Are we trying to batch move any admins?
	$ps = $db->select('users', 1, $data, 'group_id=? AND id IN ('.implode(',', $markers).')');
	if ($ps->rowCount())
		message($lang_admin_users['No move admins message']);

	// Fetch all user groups
	$all_groups = array();
	foreach ($panther_groups as $cur_group)
	{
		if ($cur_group['g_id'] != PANTHER_GUEST && $cur_group['g_id'] != PANTHER_ADMIN)
			$all_groups[$cur_group['g_id']] = $cur_group['g_title'];
	}

	if (isset($_POST['move_users_comply']))
	{
		$new_group = isset($_POST['new_group']) && isset($all_groups[$_POST['new_group']]) ? $_POST['new_group'] : message($lang_admin_users['Invalid group message']);

		// Is the new group a moderator group?
		$new_group_mod = $panther_groups[$new_group]['g_moderator'];
		unset($data[0]);	// Avoid a second loop =)

		// Fetch user groups
		$user_groups = array();
		$ps = $db->select('users', 'id, group_id', array_values($data), 'id IN ('.implode(',', $markers).')');
		foreach ($ps as $cur_user)
		{
			if ($panther_groups[$cur_user['group_id']]['g_moderator'] == '1')
			{
				if (!isset($user_groups[$cur_user['group_id']]))
					$user_groups[$cur_user['group_id']] = array();

				$user_groups[$cur_user['group_id']][] = $cur_user['id'];
			}
		}

		if (!empty($user_groups))
		{
			if ($new_group != PANTHER_ADMIN && $new_group_mod != '1')
			{
				// Fetch forum list and clean up their moderator list
				$ps = $db->select('forums', 'id, moderators');
				foreach ($ps as $cur_forum)
				{
					$cur_moderators = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();
					foreach ($user_groups as $uid)
						foreach ($uid as $id)
						{
							$username = array_search($id, $cur_moderators);
							unset($cur_moderators[$username]);
							unset($cur_moderators['groups'][$id]);
							
							if (empty($cur_moderators['groups']))
								unset($cur_moderators['groups']);
						}

					$cur_moderators = (!empty($cur_moderators)) ? serialize($cur_moderators) : null;
					$update = array(
						'moderators' => $cur_moderators,
					);

					$data1 = array(
						':id'	=>	$cur_forum['id'],
					);
					
					$db->update('forums', $update, 'id=:id', $data1);
				}
			}
			else
			{
				$ps = $db->select('forums', 'id, moderators');
				foreach ($ps as $cur_forum)
				{
					$cur_moderators = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();
					foreach ($user_groups as $uid)
					{
						foreach ($uid as $id)
						{
							if (in_array($id, $cur_moderators))
							{
								$cur_moderators['groups'][$id] = $new_group;
								$update = array(
									'moderators' => serialize($cur_moderators),
								);

								$data1 = array(
									':id' => $cur_forum['id'],
								);
								
								$db->update('forums', $update, 'id=:id', $data1);
							}
						}
					}
				}
			}
		}

		$data[0] = $new_group;
		// Change user group

		$db->run('UPDATE '.$db->prefix.'users SET group_id=? WHERE id IN ('.implode(',', $markers).')', $data);
		
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';

		generate_users_info_cache();
		redirect(panther_link($panther_url['admin_users']), $lang_admin_users['Users move redirect']);
	}

	$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Users'], $lang_admin_users['Move users']);
	define('PANTHER_ACTIVE_PAGE', 'admin');
	require PANTHER_ROOT.'header.php';

	generate_admin_menu('users');
	
	$group_options = array();
	foreach ($all_groups as $gid => $group)
		$group_options[] = array('id' => $gid, 'title' => $group);
	
	$tpl = load_template('move_users.tpl');
	echo $tpl->render(
		array (
			'lang_admin_users' => $lang_admin_users,
			'lang_admin_common' => $lang_admin_common,
			'form_action' => panther_link($panther_url['admin_users']),
			'user_ids' => $user_ids,
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/users.php'),
			'group_options' => $group_options,
		)
	);

	require PANTHER_ROOT.'footer.php';
}

// Delete multiple users
else if (isset($_POST['delete_users']) || isset($_POST['delete_users_comply']))
{
	if (!$panther_user['is_admin'])
		message($lang_common['No permission'], false, '403 Forbidden');

	confirm_referrer(PANTHER_ADMIN_DIR.'/users.php');

	if (isset($_POST['users']))
	{
		$user_ids = is_array($_POST['users']) ? array_keys($_POST['users']) : explode(',', $_POST['users']);
		$user_ids = array_map('intval', $user_ids);

		// Delete invalid IDs
		$user_ids = array_diff($user_ids, array(0, 1));
	}
	else
		$user_ids = array();

	if (empty($user_ids))
		message($lang_admin_users['No users selected']);

	// Are we trying to delete any admins?
	$data = array(PANTHER_ADMIN);
	$markers = array();
	for($i = 0; $i < count($user_ids); $i++)
	{
		$markers[] = '?';
		$data[] = $user_ids[$i];
	}

	$ps = $db->select('users', 1, $data, 'group_id=? AND id IN ('.implode(',', $markers).')');
	if ($ps->rowCount())
		message($lang_admin_users['No delete admins message']);

	if (isset($_POST['delete_users_comply']))
	{
		unset($data[0]);
		// Fetch user groups
		$user_groups = array();
		$ps = $db->select('users', 'id, group_id', array_values($data), 'id IN ('.implode(',', $markers).')');
		foreach ($ps as $cur_user)
		{
			if ($cur_user['group_id'] == 0)
				continue;

			if ($panther_groups[$cur_user['group_id']]['g_moderator'] == '1')
			{
				if (!isset($user_groups[$cur_user['group_id']]))
					$user_groups[$cur_user['group_id']] = array();

				$user_groups[$cur_user['group_id']][] = $cur_user['id'];
			}
		}

		// Fetch forum list and clean up their moderator list
		$ps = $db->select('forums', 'id, moderators');
		foreach ($ps as $cur_forum)
		{
			$cur_moderators = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();

			foreach ($user_groups as $uid)
				foreach ($uid as $id)
				{
					$username = array_search($id, $cur_moderators);
					unset($cur_moderators[$username]);
					unset($cur_moderators['groups'][$id]);
							
					if (empty($cur_moderators['groups']))
						unset($cur_moderators['groups']);
				}

			$cur_moderators = (!empty($cur_moderators)) ? serialize($cur_moderators) : null;
			$update = array(
				'moderators'	=>	$cur_moderators,
			);

			$data1 = array(
				':id'	=>	$cur_forum['id'],
			);

			$db->update('forums', $update, 'id=:id', $data1);
		}

		// Delete any subscriptions
		$db->run('DELETE FROM '.$db->prefix.'topic_subscriptions WHERE user_id IN ('.implode(',', $markers).')', array_values($data));
		$db->run('DELETE FROM '.$db->prefix.'forum_subscriptions WHERE user_id IN ('.implode(',', $markers).')', array_values($data));

		// Remove them from the online list (if they happen to be logged in)
		$db->run('DELETE FROM '.$db->prefix.'online WHERE user_id IN ('.implode(',', $markers).')', array_values($data));		

		// Should we delete all posts made by these users?
		if (isset($_POST['delete_posts']))
		{
			require PANTHER_ROOT.'include/search_idx.php';
			@set_time_limit(0);

			// Find all posts made by this user
			$ps = $db->run('SELECT p.id, p.topic_id, t.forum_id FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id WHERE p.poster_id IN ('.implode(',', $markers).')', array_values($data));
			if ($ps->rowCount())
			{
				foreach ($ps as $cur_post)
				{
					// Determine whether this post is the "topic post" or not
					$data1 = array(
						':id'	=>	$cur_post['topic_id'],
					);

					$ps1 = $db->select('posts', 'id', $data1, 'topic_id=:id', 'posted DESC LIMIT 1');
					if ($ps1->fetchColumn() == $cur_post['id'])
						delete_topic($cur_post['topic_id']);
					else
						delete_post($cur_post['id'], $cur_post['topic_id']);

					update_forum($cur_post['forum_id']);
				}
			}
		}
		else
			$db->run('UPDATE '.$db->prefix.'posts SET poster_id=1 WHERE poster_id IN ('.implode(',', $markers).')', array_values($data));

		// Delete the users
		$db->run('DELETE FROM '.$db->prefix.'users WHERE id IN ('.implode(',', $markers).')', array_values($data));

		// Delete user avatars
		foreach ($user_ids as $user_id)
			delete_avatar($user_id);

		// Regenerate the users info cache
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';

		generate_users_info_cache();
		redirect(panther_link($panther_url['admin_users']), $lang_admin_users['Users delete redirect']);
	}

	$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Users'], $lang_admin_users['Delete users']);
	define('PANTHER_ACTIVE_PAGE', 'admin');
	require PANTHER_ROOT.'header.php';

	generate_admin_menu('users');
	
	$tpl = load_template('delete_users.tpl');
	echo $tpl->render(
		array(
			'lang_admin_users' => $lang_admin_users,
			'lang_admin_common' => $lang_admin_common,
			'form_action' => panther_link($panther_url['admin_users']),
			'user_ids' => $user_ids,
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/users.php'),
		)
	);

	require PANTHER_ROOT.'footer.php';
}

// Ban multiple users
else if (isset($_POST['ban_users']) || isset($_POST['ban_users_comply']))
{
	if (!$panther_user['is_admin'] && ($panther_user['g_moderator'] != '1' || $panther_user['g_mod_ban_users'] == '0'))
		message($lang_common['No permission'], false, '403 Forbidden');

	confirm_referrer(PANTHER_ADMIN_DIR.'/users.php');

	if (isset($_POST['users']))
	{
		$user_ids = is_array($_POST['users']) ? array_keys($_POST['users']) : explode(',', $_POST['users']);
		$user_ids = array_map('intval', $user_ids);

		// Delete invalid IDs
		$user_ids = array_diff($user_ids, array(0, 1));
	}
	else
		$user_ids = array();

	if (empty($user_ids))
		message($lang_admin_users['No users selected']);
	
	$data = array(PANTHER_ADMIN);
	$markers = array();
	for ($i = 0; $i < count($user_ids); $i++)
	{
		$data[] = $user_ids[$i];
		$markers[] = '?';
	}

	// Are we trying to ban any admins?
	$ps = $db->select('users', 1, $data, 'group_id=? AND id IN('.implode(',', $markers).')');
	if ($ps->rowCount())
		message($lang_admin_users['No ban admins message']);
	
	unset($data[0]);

	// Also, we cannot ban moderators
	$ps = $db->run('SELECT 1 FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id WHERE g.g_moderator=1 AND u.id IN ('.implode(',', $markers).')', array_values($data));
	if ($ps->rowCount())
		message($lang_admin_users['No ban mods message']);

	if (isset($_POST['ban_users_comply']))
	{
		$ban_message = panther_trim($_POST['ban_message']);
		$ban_expire = panther_trim($_POST['ban_expire']);
		$ban_the_ip = isset($_POST['ban_the_ip']) ? intval($_POST['ban_the_ip']) : 0;

		if ($ban_expire != '' && $ban_expire != 'Never')
		{
			$ban_expire = strtotime($ban_expire.' GMT');

			if ($ban_expire == -1 || !$ban_expire)
				message($lang_admin_users['Invalid date message'].' '.$lang_admin_users['Invalid date reasons']);

			$diff = ($panther_user['timezone'] + $panther_user['dst']) * 3600;
			$ban_expire -= $diff;

			if ($ban_expire <= time())
				message($lang_admin_users['Invalid date message'].' '.$lang_admin_users['Invalid date reasons']);
		}
		else
			$ban_expire = null;

		$ban_message = ($ban_message != '') ? $ban_message : null;

		// Fetch user information
		$user_info = array();
		$ps = $db->select('users', 'id, username, email, registration_ip', array_values($data), 'id IN ('.implode(',', $markers).')');
		foreach ($ps as $cur_user)
			$user_info[$cur_user['id']] = array('username' => $cur_user['username'], 'email' => $cur_user['email'], 'ip' => $cur_user['registration_ip']);

		// Overwrite the registration IP with one from the last post (if it exists)
		if ($ban_the_ip != 0)
		{
			$ps = $db->run('SELECT p.poster_id, p.poster_ip FROM '.$db->prefix.'posts AS p INNER JOIN (SELECT MAX(id) AS id FROM '.$db->prefix.'posts WHERE poster_id IN ('.implode(',', $markers).') GROUP BY poster_id) AS i ON p.id=i.id', array_values($data));
			foreach ($ps as $cur_address)
				$user_info[$cur_address['poster_id']]['ip'] = $cur_address['poster_ip'];
		}

		// And insert the bans!
		foreach ($user_ids as $user_id)
		{

			$insert = array(
				'username'	=>	$user_info[$user_id]['username'],
				'ip'		=>	($ban_the_ip != 0) ? $user_info[$user_id]['ip'] : null,
				'email'	=>	$user_info[$user_id]['email'],
				'message'	=>	$ban_message,
				'expire'	=>	$ban_expire,
				'ban_creator'	=>	$panther_user['id'],
			);
			
			$db->insert('bans', $insert);
		}

		// Regenerate the bans cache
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';

		generate_bans_cache();
		redirect(panther_link($panther_url['admin_users']), $lang_admin_users['Users banned redirect']);
	}

	$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Bans']);
	$focus_element = array('bans2', 'ban_message');
	define('PANTHER_ACTIVE_PAGE', 'admin');
	require PANTHER_ROOT.'header.php';

	generate_admin_menu('users');
	
	$tpl = load_template('ban_users.tpl');
	echo $tpl->render(
		array(
			'lang_admin_common' => $lang_admin_common,
			'lang_admin_users' => $lang_admin_users,
			'form_action' => panther_link($panther_url['admin_users']),
			'user_ids' => $user_ids,
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/users.php'),
		)
	);

	require PANTHER_ROOT.'footer.php';
}
else if (isset($_GET['find_user']))
{
	$form = isset($_GET['form']) ? $_GET['form'] : array();

	// trim() all elements in $form
	$form = array_map('panther_trim', $form);
	$conditions = $query_str = $sql = $data = array();

	$posts_greater = isset($_GET['posts_greater']) ? panther_trim($_GET['posts_greater']) : '';
	$posts_less = isset($_GET['posts_less']) ? panther_trim($_GET['posts_less']) : '';
	$last_post_after = isset($_GET['last_post_after']) ? panther_trim($_GET['last_post_after']) : '';
	$last_post_before = isset($_GET['last_post_before']) ? panther_trim($_GET['last_post_before']) : '';
	$last_visit_after = isset($_GET['last_visit_after']) ? panther_trim($_GET['last_visit_after']) : '';
	$last_visit_before = isset($_GET['last_visit_before']) ? panther_trim($_GET['last_visit_before']) : '';
	$registered_after = isset($_GET['registered_after']) ? panther_trim($_GET['registered_after']) : '';
	$registered_before = isset($_GET['registered_before']) ? panther_trim($_GET['registered_before']) : '';
	$order_by = isset($_GET['order_by']) && in_array($_GET['order_by'], array('username', 'email', 'num_posts', 'last_post', 'last_visit', 'registered')) ? $_GET['order_by'] : 'username';
	$direction = isset($_GET['direction']) && $_GET['direction'] == 'DESC' ? 'DESC' : 'ASC';
	$user_group = isset($_GET['user_group']) ? intval($_GET['user_group']) : -1;

	$query_str[] = 'order_by='.$order_by;
	$query_str[] = 'direction='.$direction;
	$query_str[] = 'user_group='.$user_group;

	if (preg_match('%[^0-9]%', $posts_greater.$posts_less))
		message($lang_admin_users['Non numeric message']);

	$sql[] = 'u.id>1';
	// Try to convert date/time to timestamps
	if ($last_post_after != '')
	{
		$query_str[] = 'last_post_after='.$last_post_after;

		$last_post_after = strtotime($last_post_after);
		if ($last_post_after === false || $last_post_after == -1)
			message($lang_admin_users['Invalid date time message']);

		$sql[] = 'u.last_post>?';
		$data[] = $last_post_after;
	}
	if ($last_post_before != '')
	{
		$query_str[] = 'last_post_before='.$last_post_before;

		$last_post_before = strtotime($last_post_before);
		if ($last_post_before === false || $last_post_before == -1)
			message($lang_admin_users['Invalid date time message']);

		$sql[] = 'u.last_post<?';
		$data[] = $last_post_before;
	}
	if ($last_visit_after != '')
	{
		$query_str[] = 'last_visit_after='.$last_visit_after;

		$last_visit_after = strtotime($last_visit_after);
		if ($last_visit_after === false || $last_visit_after == -1)
			message($lang_admin_users['Invalid date time message']);

		$sql[] = 'u.last_visit>?';
		$data[] = $last_visit_after;
	}
	if ($last_visit_before != '')
	{
		$query_str[] = 'last_visit_before='.$last_visit_before;

		$last_visit_before = strtotime($last_visit_before);
		if ($last_visit_before === false || $last_visit_before == -1)
			message($lang_admin_users['Invalid date time message']);

		$sql[] = 'u.last_visit<?';
		$data[] = $last_visit_before;
	}
	if ($registered_after != '')
	{
		$query_str[] = 'registered_after='.$registered_after;

		$registered_after = strtotime($registered_after);
		if ($registered_after === false || $registered_after == -1)
			message($lang_admin_users['Invalid date time message']);

		$sql[] = 'u.registered>?';
		$data[] = $registered_after;
	}
	if ($registered_before != '')
	{
		$query_str[] = 'registered_before='.$registered_before;

		$registered_before = strtotime($registered_before);
		if ($registered_before === false || $registered_before == -1)
			message($lang_admin_users['Invalid date time message']);

		$sql[] = 'u.registered<?';
		$data[] = $registered_before;
	}

	foreach ($form as $key => $input)
	{
		if ($input != '' && in_array($key, array('username', 'email', 'title', 'realname', 'url', 'facebook', 'steam', 'skype', 'twitter', 'google', 'location', 'signature', 'admin_note')))
		{
			$sql[] = 'u.'.$key.' LIKE ?';
			$data[] = str_replace('*', '%', $input);
			$query_str[] = 'form%5B'.$key.'%5D='.urlencode($input);
		}
	}

	if ($posts_greater != '')
	{
		$query_str[] = 'posts_greater='.$posts_greater;
		$conditions[] = 'u.num_posts>'.$posts_greater;
	}
	if ($posts_less != '')
	{
		$query_str[] = 'posts_less='.$posts_less;
		$conditions[] = 'u.num_posts<'.$posts_less;
	}

	if ($user_group > -1)
	{
		$sql[] = 'u.group_id=?';
		$data[] = $user_group;
	}

	// Fetch user count
	$ps = $db->run('SELECT COUNT(id) FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE '.implode(' AND ', $sql), $data);
	$num_users = $ps->fetchColumn();

	// Determine the user offset (based on $_GET['p'])
	$num_pages = ceil($num_users / 50);

	$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);
	$start_from = 50 * ($p - 1);

	// Some helper variables for permissions
	$can_delete = $can_move = $panther_user['is_admin'];
	$can_ban = $panther_user['is_admin'] || ($panther_user['g_moderator'] == '1' && $panther_user['g_mod_ban_users'] == '1');
	$can_action = ($can_delete || $can_ban || $can_move) && $num_users > 0;

	$data[] = $start_from;
	$ps = $db->run('SELECT u.id, u.username, u.email, u.title, u.num_posts, u.admin_note, g.g_id, g.g_user_title FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE '.implode(' AND ', $sql).' ORDER BY '.$order_by.' '.$direction.' LIMIT ?, 50', $data);

	define('COMMON_JAVASCRIPT', true);
	$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Users'], $lang_admin_users['Results head']);
	define('PANTHER_ACTIVE_PAGE', 'admin');
	require PANTHER_ROOT.'header.php';

	$users = array();
	foreach ($ps as $user_data)
	{
		$user_title = get_title($user_data);
		$users[] = array(
			'id' => $user_data['id'],
			'title' => $user_title,
			'email' => $user_data['email'],
			'ip_stats_link' => panther_link($panther_url['admin_users_ip_stats'], array($user_data['id'])),
			'search_posts_link' => panther_link($panther_url['search_user_posts'], array($user_data['id'])),
			'profile_link' => panther_link($panther_url['profile'], array($user_data['id'], url_friendly($user_data['username']))),
			'username' => $user_data['username'],
			'num_posts' => forum_number_format($user_data['num_posts']),
			'admin_note' => $user_data['admin_note'],
			'unverified' => (($user_data['g_id'] == '' || $user_data['g_id'] == PANTHER_UNVERIFIED) && $user_title != $lang_common['Banned']) ? true : false,
		);
	}

	$tpl = load_template('admin_users_result.tpl');
	echo $tpl->render(
		array(
			'lang_admin_users' => $lang_admin_users,
			'lang_admin_common' => $lang_admin_common,
			'index_link' => panther_link($panther_url['admin_index']),
			'form_action' => panther_link($panther_url['admin_users']),
			'can_action' => $can_action,
			'can_ban' => $can_ban,
			'can_delete' => $can_delete,
			'can_move' => $can_move,
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/users.php'),
			'pagination' => paginate($num_pages, $p, $panther_url['admin_users'].'?find_user=&amp;'.implode('&amp;', $query_str)),
			'users' => $users,
		)
	);

	require PANTHER_ROOT.'footer.php';
}
else
{
	$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Users']);
	$focus_element = array('find_user', 'form[username]');
	define('PANTHER_ACTIVE_PAGE', 'admin');
	require PANTHER_ROOT.'header.php';

	$groups = array();
	foreach ($panther_groups as $cur_group)
	{
		if ($cur_group['g_id'] != PANTHER_GUEST)
			$groups[] = array('id' => $cur_group['g_id'], 'title' => $cur_group['g_title']);
	}

	generate_admin_menu('users');
	$tpl = load_template('admin_users.tpl');
	echo $tpl->render(
		array(
			'lang_admin_users' => $lang_admin_users,
			'form_action' => panther_link($panther_url['admin_users']),
			'groups' => $groups
		)
	);

	require PANTHER_ROOT.'footer.php';
}