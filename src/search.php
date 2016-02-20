<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

// The contents of this file are very much inspired by the file search.php
// from the phpBB Group forum software phpBB2 (http://www.phpbb.com)

if (!defined('PANTHER'))
{
	define('PANTHER_ROOT', __DIR__.'/');
	require PANTHER_ROOT.'include/common.php';
}

// Load the search.php language file
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/search.php';
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/forum.php';

if ($panther_user['g_read_board'] == '0')
	message($lang_common['No view'], false, '403 Forbidden');
else if ($panther_user['g_search'] == '0')
	message($lang_search['No search permission'], false, '403 Forbidden');

require PANTHER_ROOT.'include/search_idx.php';

($hook = get_extensions('search_before_actions')) ? eval($hook) : null;

// Figure out what to do :-)
if (isset($_GET['action']) || isset($_GET['search_id']))
{
	$action = (isset($_GET['action'])) ? $_GET['action'] : null;
	$url_forums = isset($_GET['forums']) ? (is_array($_GET['forums']) ? $_GET['forums'] : array_filter(explode(',', $_GET['forums']))) : (isset($_GET['forum']) ? array($_GET['forum']) : array());
	$sort_dir = (isset($_GET['sort_dir']) && $_GET['sort_dir'] == 'DESC') ? 'DESC' : 'ASC';
	$forum_sql = '';
	$url_forums = array_map('intval', $url_forums);

	// If a search_id was supplied
	if (isset($_GET['search_id']))
	{
		$search_id = intval($_GET['search_id']);
		if ($search_id < 1)
			message($lang_common['Bad request'], false, '404 Not Found');
	}
	// If it's a regular search (keywords and/or author)
	else if ($action == 'search')
	{
		$keywords = (isset($_GET['keywords'])) ? utf8_strtolower(panther_trim($_GET['keywords'])) : null;
		$author = (isset($_GET['author'])) ? utf8_strtolower(panther_trim($_GET['author'])) : null;

		if (preg_match('%^[\*\%]+$%', $keywords) || (panther_strlen(str_replace(array('*', '%'), '', $keywords)) < PANTHER_SEARCH_MIN_WORD && !is_cjk($keywords)))
			$keywords = '';

		if (preg_match('%^[\*\%]+$%', $author) || panther_strlen(str_replace(array('*', '%'), '', $author)) < 2)
			$author = '';

		if (!$keywords && !$author)
			message($lang_search['No terms']);

		if ($author)
			$author = str_replace('*', '%', $author);

		$show_as = (isset($_GET['show_as']) && $_GET['show_as'] == 'topics') ? 'topics' : 'posts';
		$sort_by = (isset($_GET['sort_by'])) ? intval($_GET['sort_by']) : 0;
		$search_in = (!isset($_GET['search_in']) || $_GET['search_in'] == '0') ? 0 : (($_GET['search_in'] == '1') ? 1 : -1);
	}
	// If it's a user search (by ID)
	else if ($action == 'show_user_posts' || $action == 'show_user_topics' || $action == 'show_subscriptions')
	{
		$user_id = (isset($_GET['user_id'])) ? intval($_GET['user_id']) : $panther_user['id'];
		if ($user_id < 2)
			message($lang_common['Bad request'], false, '404 Not Found');

		// Subscribed topics can only be viewed by admins, moderators and the users themselves
		if ($action == 'show_subscriptions' && !$panther_user['is_admmod'] && $user_id != $panther_user['id'])
			message($lang_common['No permission'], false, '403 Forbidden');
	}
	else if ($action == 'show_recent')
		$interval = isset($_GET['value']) ? intval($_GET['value']) : 86400;
	else if ($action == 'show_replies')
	{
		if ($panther_user['is_guest'])
			message($lang_common['Bad request'], false, '404 Not Found');
	}
	else if ($action != 'show_new' && $action != 'show_unanswered')
		message($lang_common['Bad request'].'1', false, '404 Not Found');
	
	($hook = get_extensions('search_before_search_id')) ? eval($hook) : null;

	// If a valid search_id was supplied we attempt to fetch the search results from the db
	if (isset($search_id))
	{
		$ident = ($panther_user['is_guest']) ? get_remote_address() : $panther_user['username'];
		$data = array(
			':id'	=>	$search_id,
			':ident'	=>	$ident,
		);

		$ps = $db->select('search_cache', 'search_data', $data, 'id=:id AND ident=:ident');
		if ($row = $ps->fetch())
		{
			$placeholders = array();
			$temp = unserialize($row['search_data']);

			$search_ids = unserialize($temp['search_ids']);
			$num_hits = $temp['num_hits'];
			$sort_by = $temp['sort_by'];
			$sort_dir = $temp['sort_dir'];
			$show_as = $temp['show_as'];
			$search_type = $temp['search_type'];
			
			for ($i = 0; $i < count($search_ids); $i++)
				$markers[] = '?';

			unset($temp);
		}
		else
			message($lang_search['No hits']);
	}
	else
	{
		$keyword_results = $author_results = $forums = $no_view = $no_forums = array();

		// Search a specific forum?
		if (!empty($url_forums) || (empty($url_forums) && $panther_config['o_search_all_forums'] == '0' && !$panther_user['is_admmod']))
		{
			for ($i = 0; $i < count($url_forums); $i++)
			{
				if ($panther_forums[$url_forums[$i]]['password'] != '' && check_forum_login_cookie($url_forums[$i], $panther_forums[$url_forums[$i]]['password'], true) === false || $panther_forums[$url_forums[$i]]['protected'] == '1' && !$panther_user['is_admmod'])
				{
					$no_view[] = '?';
					$no_forums[] = $url_forums[$i];
					continue;
				}

				$forums[] = $url_forums[$i];
				$markers[] = '?';
			}
			
			$forums = array_merge($forums, $no_forums);
			$forum_sql = (!empty($markers) ? ' AND t.forum_id IN ('.implode(',', $markers).')' : '').(!empty($no_view) ? ' AND t.forum_id NOT IN ('.implode(',', $no_view).')' : '');
		}
		else	// If there are no forums selected, we need to ensure that there are no 'protected' forums we can't view
		{
			foreach($panther_forums as $cur_forum)
			{
				if ($panther_forums[$cur_forum['id']]['password'] != '' && check_forum_login_cookie($cur_forum['id'], $panther_forums[$cur_forum['id']]['password'], true) === false || $panther_forums[$cur_forum['id']]['protected'] == '1' && !$panther_user['is_admmod'])
				{
					$no_view[] = '?';
					$forums[] = $cur_forum['id'];
				}
			}
			$forum_sql = (!empty($no_view) ? ' AND t.forum_id NOT IN ('.implode(',', $no_view).')' : '');
		}

		($hook = get_extensions('search_before_flood_protection')) ? eval($hook) : null;

		if (!empty($author) || !empty($keywords))
		{
			// Flood protection
			if ($panther_user['last_search'] && (time() - $panther_user['last_search']) < $panther_user['g_search_flood'] && (time() - $panther_user['last_search']) >= 0)
				message(sprintf($lang_search['Search flood'], $panther_user['g_search_flood'], $panther_user['g_search_flood'] - (time() - $panther_user['last_search'])));
			
			$update = array(
				'last_search'	=>	time(),
			);
			
			$data = array(
				':id'	=>	(($panther_user['is_guest']) ? get_remote_address() : $panther_user['username']),
			);

			if (!$panther_user['is_guest'])
				$db->update('users', $update, 'id=:id', $data);
			else
				$db->update('online', $update, 'ident=:id', $data);

			switch ($sort_by)
			{
				case 1:
					$sort_by_sql = ($show_as == 'topics') ? 't.poster' : 'p.poster';
					$sort_type = SORT_STRING;
					break;

				case 2:
					$sort_by_sql = 't.subject';
					$sort_type = SORT_STRING;
					break;

				case 3:
					$sort_by_sql = 't.forum_id';
					$sort_type = SORT_NUMERIC;
					break;

				case 4:
					$sort_by_sql = 't.last_post';
					$sort_type = SORT_NUMERIC;
					break;

				default:
					$sort_by_sql = ($show_as == 'topics') ? 't.last_post' : 'p.posted';
					$sort_type = SORT_NUMERIC;
					break;
			}

			// If it's a search for keywords
			if ($keywords)
			{
				// split the keywords into words
				$keywords_array = split_words($keywords, false);

				if (empty($keywords_array))
					message($lang_search['No hits']);

				// Should we search in message body or topic subject specifically?
				$search_in_cond = ($search_in) ? (($search_in > 0) ? ' AND m.subject_match = 0' : ' AND m.subject_match = 1') : '';

				$word_count = 0;
				$match_type = 'and';

				$sort_data = array();
				foreach ($keywords_array as $cur_word)
				{
					$data = array();
					$data[] = $panther_user['g_id'];
					switch ($cur_word)
					{
						case 'and':
						case 'or':
						case 'not':
							$match_type = $cur_word;
							break;

						default:
						{
							$where_cond = str_replace('*', '%', $cur_word);
							$data[] = '%'.$where_cond.'%';

							if (is_cjk($cur_word))
							{
								if ($search_in)
								{
									if ($search_in > 0)
										$where_cond = 'p.message LIKE ?';
									else
										$where_cond = 't.subject LIKE ?';
								}
								else
								{
									$data[] = '%'.$where_cond.'%';
									$where_cond = 'p.message LIKE ? OR t.subject LIKE ?';
								}

								$data = array_merge($data, $forums);	// Now merge the forums in with the other data
								$ps = $db->run('SELECT p.id AS post_id, p.topic_id, '.$sort_by_sql.' AS sort_by FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=t.forum_id AND fp.group_id=?) WHERE (p.approved=1 AND t.deleted=0 AND '.$where_cond.') AND (fp.read_forum IS NULL OR fp.read_forum=1) '.$forum_sql, $data);
							}
							else
							{
								$data = array_merge($data, $forums);	
								$ps = $db->run('SELECT m.post_id, p.topic_id, '.$sort_by_sql.' AS sort_by FROM '.$db->prefix.'search_words AS w INNER JOIN '.$db->prefix.'search_matches AS m ON m.word_id=w.id INNER JOIN '.$db->prefix.'posts AS p ON p.id=m.post_id INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=t.forum_id AND fp.group_id=?) WHERE p.approved=1 AND p.deleted=0 AND w.word LIKE ?'.$search_in_cond.' AND (fp.read_forum IS NULL OR fp.read_forum=1) '.$forum_sql, $data);
							}
							$row = array();
							foreach ($ps as $temp)
							{
								$row[$temp['post_id']] = $temp['topic_id'];

								if (!$word_count)
								{
									$keyword_results[$temp['post_id']] = $temp['topic_id'];
									$sort_data[$temp['post_id']] = $temp['sort_by'];
								}
								else if ($match_type == 'or')
								{
									$keyword_results[$temp['post_id']] = $temp['topic_id'];
									$sort_data[$temp['post_id']] = $temp['sort_by'];
								}
								else if ($match_type == 'not')
								{
									unset($keyword_results[$temp['post_id']]);
									unset($sort_data[$temp['post_id']]);
								}
							}

							if ($match_type == 'and' && $word_count)
							{
								foreach ($keyword_results as $post_id => $topic_id)
								{
									if (!isset($row[$post_id]))
									{
										unset($keyword_results[$post_id]);
										unset($sort_data[$post_id]);
									}
								}
							}

							++$word_count;
							$db->free_result($ps);

							break;
						}
					}
				}

				// Sort the results - annoyingly array_multisort re-indexes arrays with numeric keys, so we need to split the keys out into a separate array then combine them again after
				$post_ids = array_keys($keyword_results);
				$topic_ids = array_values($keyword_results);

				array_multisort(array_values($sort_data), $sort_dir == 'DESC' ? SORT_DESC : SORT_ASC, $sort_type, $post_ids, $topic_ids);

				// combine the arrays back into a key=>value array (array_combine is PHP5 only unfortunately)
				$num_results = count($keyword_results);
				$keyword_results = array();
				for ($i = 0;$i < $num_results;$i++)
					$keyword_results[$post_ids[$i]] = $topic_ids[$i];

				unset($sort_data, $post_ids, $topic_ids);
			}

			// If it's a search for author name (and that author name isn't Guest)
			if ($author && $author != 'guest' && $author != utf8_strtolower($lang_common['Guest']))
			{
				$data = array(
					':username'	=>	$author
				);
				
				$ps = $db->select('users', 'id', $data, 'username LIKE :username');
				if ($ps->rowCount())
				{
					$user_ids = $data = $markers = array();
					$ps->setFetchMode(PDO::FETCH_COLUMN, 0);
					
					$data[] = $panther_user['g_id'];
					foreach ($ps as $uid)
					{
						$data[] = $uid;
						$markers[] = '?';
					}

					$data = array_merge($data, $forums);
					$ps = $db->run('SELECT p.id AS post_id, p.topic_id FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=t.forum_id AND fp.group_id=?) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND p.approved=1 AND p.deleted=0 AND p.poster_id IN('.implode(',', $markers).')'.$forum_sql.' ORDER BY '.$sort_by_sql.' '.$sort_dir, $data);
					foreach ($ps as $temp)
						$author_results[$temp['post_id']] = $temp['topic_id'];

					$db->free_result($ps);
				}
			}

			// If we searched for both keywords and author name we want the intersection between the results
			if ($author && $keywords)
			{
				$search_ids = array_intersect_assoc($keyword_results, $author_results);
				$search_type = array('both', array($keywords, panther_trim($_GET['author'])), implode(',', $forums), $search_in);
			}
			else if ($keywords)
			{
				$search_ids = $keyword_results;
				$search_type = array('keywords', $keywords, implode(',', $forums), $search_in);
			}
			else
			{
				$search_ids = $author_results;
				$search_type = array('author', panther_trim($_GET['author']), implode(',', $forums), $search_in);
			}

			unset($keyword_results, $author_results);
			$search_ids = ($show_as == 'topics') ? array_values($search_ids) : array_keys($search_ids);

			$markers = array();
			$search_ids = array_unique($search_ids);
			for ($i= 0; $i < count($search_ids); $i++)
				$markers[] = '?';

			$num_hits = count($search_ids);
			if (!$num_hits)
				message($lang_search['No hits']);
		}
		else if ($action == 'show_new' || $action == 'show_recent' || $action == 'show_replies' || $action == 'show_user_posts' || $action == 'show_user_topics' || $action == 'show_subscriptions' || $action == 'show_unanswered')
		{
			$search_type = array('action', $action);
			$show_as = 'topics';
			// We want to sort things after last post
			$sort_by = 0;
			$sort_dir = 'DESC';

			// If it's a search for new posts since last visit
			if ($action == 'show_new')
			{
				if ($panther_user['is_guest'])
					message($lang_common['No permission'], false, '403 Forbidden');

				$fid = isset($_GET['fid']) ? intval($_GET['fid']) : null;
				$data = array($panther_user['g_id'], $panther_user['last_visit']);

				if ($fid != '')
				{
					if (!isset($panther_forums[$fid]))
						message($lang_common['Bad request']);
					
					if ($panther_forums[$fid]['password'] != '')
						check_forum_login_cookie($fid, $panther_forums[$fid]['password']);

					$forum_sql = ' AND t.forum_id = ?';
					$data[] = $fid;
				}
				else
					$data = array_merge($data, $forums);

				$ps = $db->run('SELECT t.id FROM '.$db->prefix.'topics AS t LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=t.forum_id AND fp.group_id = ?) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.approved=1 AND t.deleted=0 AND t.last_post>? AND t.moved_to IS NULL'.$forum_sql.' ORDER BY t.last_post DESC', $data);
				$num_hits = $ps->rowCount();

				if (!$num_hits)
					message($lang_search['No new posts']);
			}
			// If it's a search for recent posts (in a certain time interval)
			else if ($action == 'show_recent')
			{
				$fid = isset($_GET['fid']) ? intval($_GET['fid']) : null;
				$data = array($panther_user['g_id'], (time() - $interval));
				
				if ($fid != '')
				{
					$forum_sql .= ' AND t.forum_id = ?';
					$data[] = $fid;
				}

				$data = array_merge($data, $forums);
				$ps = $db->run('SELECT t.id FROM '.$db->prefix.'topics AS t LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=t.forum_id AND fp.group_id=?) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.approved=1 AND t.deleted=0 AND t.last_post>? AND t.moved_to IS NULL'.$forum_sql.' ORDER BY t.last_post DESC', $data);
				$num_hits = $ps->rowCount();

				if (!$num_hits)
					message($lang_search['No recent posts']);
			}
			// If it's a search for topics in which the user has posted
			else if ($action == 'show_replies')
			{
				$data = array($panther_user['g_id'], $panther_user['id']);
				$ps = $db->run('SELECT t.id FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'posts AS p ON t.id=p.topic_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=t.forum_id AND fp.group_id=?) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.approved=1 AND t.deleted=0 AND p.poster_id=? GROUP BY t.id ORDER BY t.last_post DESC', $data);
				$num_hits = $ps->rowCount();

				if (!$num_hits)
					message($lang_search['No user posts']);
			}
			// If it's a search for posts by a specific user ID
			else if ($action == 'show_user_posts')
			{
				$show_as = 'posts';

				$data = array($panther_user['g_id'], $user_id);
				$ps = $db->run('SELECT p.id FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON p.topic_id=t.id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=t.forum_id AND fp.group_id=?) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND p.approved=1 AND p.deleted=0 AND p.poster_id=? ORDER BY p.posted DESC', $data);
				$num_hits = $ps->rowCount();

				if (!$num_hits)
					message($lang_search['No user posts']);

				// Pass on the user ID so that we can later know whose posts we're searching for
				$search_type[2] = $user_id;
			}
			// If it's a search for topics by a specific user ID
			else if ($action == 'show_user_topics')
			{
				$data = array($panther_user['g_id'], $user_id);
				$ps = $db->run('SELECT t.id FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'posts AS p ON t.first_post_id=p.id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=t.forum_id AND fp.group_id=?) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.approved=1 AND t.deleted=0 AND p.poster_id=? ORDER BY t.last_post DESC', $data);
				$num_hits = $ps->rowCount();

				if (!$num_hits)
					message($lang_search['No user topics']);

				// Pass on the user ID so that we can later know whose topics we're searching for
				$search_type[2] = $user_id;
			}
			// If it's a search for subscribed topics
			else if ($action == 'show_subscriptions')
			{
				if ($panther_user['is_guest'])
					message($lang_common['Bad request'], false, '404 Not Found');
				
				$data = array($user_id, $panther_user['g_id']);
				$ps = $db->run('SELECT t.id FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'topic_subscriptions AS s ON (t.id=s.topic_id AND s.user_id=?) LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=t.forum_id AND fp.group_id=?) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.approved=1 AND t.deleted=0 ORDER BY t.last_post DESC', $data);
				$num_hits = $ps->rowCount();

				if (!$num_hits)
					message($lang_search['No subscriptions']);

				// Pass on user ID so that we can later know whose subscriptions we're searching for
				$search_type[2] = $user_id;
			}
			// If it's a search for unanswered posts
			else
			{
				$data = array($panther_user['g_id']);
				$ps = $db->run('SELECT t.id FROM '.$db->prefix.'topics AS t LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=t.forum_id AND fp.group_id=?) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.approved=1 AND t.deleted=0 AND t.num_replies=0 AND t.moved_to IS NULL ORDER BY t.last_post DESC', $data);
				$num_hits = $ps->rowCount();

				if (!$num_hits)
					message($lang_search['No unanswered']);
			}

			$search_ids = $markers = array();
			foreach ($ps as $row)
			{
				$markers[] = '?';
				$search_ids[] = $row['id'];
			}

			$db->free_result($ps);
		}
		else
			message($lang_common['Bad request'], false, '404 Not Found');

		// Prune "old" search results
		$old_searches = $placeholders = array();
		$ps = $db->select('online', 'ident');
		if ($ps->rowCount())
		{
			$ps->setFetchMode(PDO::FETCH_COLUMN, 0);
			foreach ($ps as $cur_ident)
			{
				$placeholders[] = '?';
				$old_searches[] = $cur_ident;
			}

			$db->delete('search_cache', 'ident NOT IN('.implode(',', $placeholders).')', $old_searches);
		}

		// Fill an array with our results and search properties
		$temp = serialize(array(
			'search_ids'		=> serialize($search_ids),
			'num_hits'			=> $num_hits,
			'sort_by'			=> $sort_by,
			'sort_dir'			=> $sort_dir,
			'show_as'			=> $show_as,
			'search_type'		=> $search_type
		));
		$search_id = mt_rand(1, 2147483647);

		$ident = ($panther_user['is_guest']) ? get_remote_address() : $panther_user['username'];
		$insert = array(
			'id'	=>	$search_id,
			'ident'	=>	$ident,
			'search_data'	=>	$temp,
		);

		$db->insert('search_cache', $insert);
		if ($search_type[0] != 'action')
		{
			$db->end_transaction();

			// Redirect the user to the cached result page
			header('Location: '.panther_link($panther_url['search_cache'], array($search_id)));
			exit;
		}
	}

	$forum_actions = array();
	if (!$panther_user['is_guest'] && $search_type[0] == 'action' && $search_type[1] == 'show_new')
		$forum_actions[] = array('href' => panther_link($panther_url['mark_read']), 'title' => $lang_common['Mark all as read']);

	// Fetch results to display
	if (!empty($search_ids))
	{
		switch ($sort_by)
		{
			case 1:
				$sort_by_sql = ($show_as == 'topics') ? 't.poster' : 'p.poster';
				break;

			case 2:
				$sort_by_sql = 't.subject';
				break;

			case 3:
				$sort_by_sql = 't.forum_id';
				break;

			default:
				$sort_by_sql = ($show_as == 'topics') ? 't.last_post' : 'p.posted';
				break;
		}

		// Determine the topic or post offset (based on $_GET['p'])
		$per_page = ($show_as == 'posts') ? $panther_user['disp_posts'] : $panther_user['disp_topics'];
		$num_pages = ceil($num_hits / $per_page);

		$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);
		$start_from = $per_page * ($p - 1);

		// throw away the first $start_from of $search_ids, only keep the top $per_page of $search_ids
		$search_ids = array_slice($search_ids, $start_from, $per_page);
		$markers = array();
		for ($i = 0; $i < count($search_ids); $i++)
			$markers[] = '?';

		// Run the query and fetch the results
		if ($show_as == 'posts')
			$ps = $db->run('SELECT u.group_id, p.id AS pid, p.poster AS pposter, p.posted AS pposted, p.poster_id, p.message, p.hide_smilies, t.id AS tid, t.poster, t.subject, t.question, t.first_post_id, t.last_post, t.last_post_id, t.last_poster, t.num_replies, t.forum_id, f.forum_name FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'users AS u ON (p.poster_id=u.id) WHERE p.id IN('.implode(',', $markers).') ORDER BY '.$sort_by_sql.' '.$sort_dir, $search_ids);
		else
			$ps = $db->run('SELECT u.id AS uid, u.group_id, u.use_gravatar, u.email, up.id AS up_id, up.group_id AS up_group_id, t.id AS tid, t.poster, t.subject, t.question, t.last_post, t.last_post_id, t.last_poster, t.num_replies, t.closed, t.sticky, t.forum_id, f.forum_name FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'users AS u ON (t.last_poster=u.username) LEFT JOIN '.$db->prefix.'users AS up ON (t.poster=up.username) WHERE t.id IN('.implode(',', $markers).') ORDER BY '.$sort_by_sql.' '.$sort_dir, $search_ids);

		$search_set = array();
		foreach($ps as $row)
			$search_set[] = $row;

		$crumbs_text = array();
		$crumbs_text['show_as'] = $lang_search['Search'];

		if ($search_type[0] == 'action')
		{
			if ($search_type[1] == 'show_user_topics')
				$crumbs_text['search_type'] = array('href' => panther_link($panther_url['search_user_topics'], array($search_type[2])), 'title' => sprintf($lang_search['Quick search show_user_topics'], $search_set[0]['poster']));
			else if ($search_type[1] == 'show_user_posts')
				$crumbs_text['search_type'] = array('href' => panther_link($panther_url['search_user_posts'], array($search_type[2])), 'title' => sprintf($lang_search['Quick search show_user_posts'], $search_set[0]['pposter']));
			else if ($search_type[1] == 'show_subscriptions')
			{
				// Fetch username of subscriber
				$subscriber_id = $search_type[2];
				$data = array(
					':id'	=>	$subscriber_id,
				);
				$ps = $db->select('users', 'username', $data, 'id=:id');
				if ($ps->rowCount())
					$subscriber_name = $ps->fetchColumn();
				else
					message($lang_common['Bad request'], false, '404 Not Found');

				$crumbs_text['search_type'] = array('href' => panther_link($panther_url['search_subscriptions'], array($subscriber_id)), 'title' => sprintf($lang_search['Quick search show_subscriptions'], $subscriber_name));
			}
			else
			{
				switch ($search_type[1])
				{
					case 'show_replies':
						$link = panther_link($panther_url['search_replies']);
					break;
					case 'show_new':
						$link = panther_link($panther_url['search_new']);
					break;
					case 'show_recent':
						$link = panther_link($panther_url['search_recent']);
					break;
					case 'show_unanswered':
						$link = panther_link($panther_url['search_unanswered']);
					break;
				}

				$crumbs_text['search_type'] = array('href' => $link, 'title' => $lang_search['Quick search '.$search_type[1]]);
			}
		}
		else
		{
			$keywords = $author = '';
			if ($search_type[0] == 'both')
			{
				list ($keywords, $author) = $search_type[1];
				$crumbs_text['search_type'] = sprintf($lang_search['By both show as '.$show_as], $keywords, $author);
			}
			else if ($search_type[0] == 'keywords')
			{
				$keywords = $search_type[1];
				$crumbs_text['search_type'] = sprintf($lang_search['By keywords show as '.$show_as], $keywords);
			}
			else if ($search_type[0] == 'author')
			{
				$author = $search_type[1];
				$crumbs_text['search_type'] = sprintf($lang_search['By user show as '.$show_as], $author);
			}

			$crumbs_text['search_type'] = array('href' => panther_link($panther_url['search_result'], array(urlencode($keywords), urlencode($author), $search_type[2], $search_type[3], $sort_by, $sort_dir, $show_as)), 'title' => $crumbs_text['search_type']);
		}

		($hook = get_extensions('search_before_header')) ? eval($hook) : null;

		require PANTHER_ROOT.'lang/'.$panther_user['language'].'/topic.php';
		$page_title = array($panther_config['o_board_title'], $lang_search['Search results']);
		define('PANTHER_ACTIVE_PAGE', 'search');
		require PANTHER_ROOT.'header.php';

		if ($show_as == 'topics')
			$topic_count = 0;
		else if ($show_as == 'posts')
		{
			require PANTHER_ROOT.'include/parser.php';
			$post_count = 0;
		}

		// Get topic/forum tracking data
		if (!$panther_user['is_guest'])
			$tracked_topics = get_tracked_topics();

		$results = array();
		foreach ($search_set as $cur_search)
		{
			if ($panther_config['o_censoring'] == '1')
				$cur_search['subject'] = censor_words($cur_search['subject']);

			if ($show_as == 'posts')
			{
				++$post_count;
				if ($panther_config['o_censoring'] == '1')
					$cur_search['message'] = censor_words($cur_search['message']);

				$results[] = array(
					'pid' => $cur_search,
					'message' => $parser->parse_message($cur_search['message'], $cur_search['hide_smilies']),
					'posted' => format_time($cur_search['pposted']),
					'topic_url' => panther_link($panther_url['topic'], array($cur_search['tid'], url_friendly($cur_search['subject']))),
					'post_url' => panther_link($panther_url['post'], array($cur_search['pid'])),
					'post_no' => ($start_from + $post_count),
					'post_count' => $post_count,
					'forum' => array('url' => panther_link($panther_url['forum'], array($cur_search['forum_id'], url_friendly($cur_search['forum_name']))), 'name' => $cur_search['forum_name']),
					'subject' => $cur_search['subject'],
					'poster' => ($cur_search['poster_id'] > 1) ? colourize_group($cur_search['pposter'], $cur_search['group_id'], $cur_search['poster_id']) : '',
					'post_id' => $cur_search['pid'],
					'first_post_id' => $cur_search['first_post_id'],
					'num_replies' => forum_number_format($cur_search['num_replies']),
					'viewed' => (!$panther_user['is_guest'] && $cur_search['last_post'] > $panther_user['last_visit'] && (!isset($tracked_topics['topics'][$cur_search['tid']]) || $tracked_topics['topics'][$cur_search['tid']] < $cur_search['last_post']) && (!isset($tracked_topics['forums'][$cur_search['forum_id']]) || $tracked_topics['forums'][$cur_search['forum_id']] < $cur_search['last_post'])) ? false : true,
				);
			}
			else
			{
				++$topic_count;
				$url_subject = url_friendly($cur_search['subject']);
				$num_pages_topic = ceil(($cur_search['num_replies'] + 1) / $panther_user['disp_posts']);

				$results[$cur_search['tid']] = array(
					'count' => ++$topic_count,
					'topic_count' => forum_number_format($topic_count + $start_from),
					'cur_search' => $cur_search,
					'topic_poster' => ($cur_search['up_id'] > 1) ? colourize_group($cur_search['poster'], $cur_search['up_group_id'], $cur_search['up_id']) : colourize_group($cur_search['poster'], PANTHER_GUEST),
					'subject' => $cur_search['subject'],
					'sticky' => $cur_search['sticky'],
					'closed' => $cur_search['closed'],
					'question' => $cur_search['question'],
					'topic_link' => panther_link($panther_url['topic'], array($cur_search['tid'], $url_subject)),
					'num_pages' => $num_pages_topic,
					'pagination' => paginate($num_pages_topic, -1, $panther_url['topic_paginate'], array($cur_search['tid'], $url_subject)),
					'new' => (!$panther_user['is_guest'] && $cur_search['last_post'] > $panther_user['last_visit'] && (!isset($tracked_topics['topics'][$cur_search['tid']]) || $tracked_topics['topics'][$cur_search['tid']] < $cur_search['last_post']) && (!isset($tracked_topics['forums'][$cur_search['forum_id']]) || $tracked_topics['forums'][$cur_search['forum_id']] < $cur_search['last_post'])) ? '1' : '0',
					'last_post_avatar' => generate_avatar_markup($cur_search['uid'], $cur_search['email'], $cur_search['use_gravatar'], array(32, 32)),
					'last_post_link' => panther_link($panther_url['post'], array($cur_search['last_post_id'])),
					'last_post' => format_time($cur_search['last_post']),
					'last_poster' => ($cur_search['uid'] > 1) ? colourize_group($cur_search['last_poster'], $cur_search['group_id'], $cur_search['uid']) : colourize_group($cur_search['last_poster'], PANTHER_GUEST),
					'num_replies' => forum_number_format($cur_search['num_replies']),
					'forum' => array('url' => panther_link($panther_url['forum'], array($cur_search['forum_id'], url_friendly($cur_search['forum_name']))), 'name' => $cur_search['forum_name']),
				);

				if ($results[$cur_search['tid']]['new'] == '1')
					$results[$cur_search['tid']]['new_link'] = panther_link($panther_url['topic_new_posts'], array($cur_search['tid'], $url_subject));
			}
		}

		$tpl = load_template('search_results.tpl');
		echo $tpl->render(
			array(
				'forum_actions' => $forum_actions,
				'index_link' => panther_link($panther_url['index']),
				'lang_common' => $lang_common,
				'search_link' => panther_link($panther_url['search']),
				'show_as' => $show_as,
				'lang_search' => $lang_search,
				'lang_topic' => $lang_topic,
				'lang_forum' => $lang_forum,
				'pagination' => paginate($num_pages, $p, $panther_url['search_pagination'], array($search_id)),
				'crumbs_text' => $crumbs_text,
				'results' => $results,
				'panther_config' => $panther_config,
			)
		);
		require PANTHER_ROOT.'footer.php';
	}
	else
		message($lang_search['No hits']);
}

($hook = get_extensions('search_form_before_header')) ? eval($hook) : null;

$page_title = array($panther_config['o_board_title'], $lang_search['Search']);
$focus_element = array('search', 'keywords');
define('PANTHER_ACTIVE_PAGE', 'search');
require PANTHER_ROOT.'header.php';

$data = array(
	':gid'	=>	$panther_user['g_id'],
);

$categories = $forums = array();
$ps = $db->run('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name, f.password, f.redirect_url, f.parent_forum FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.redirect_url IS NULL ORDER BY c.disp_position, c.id, f.disp_position', $data);
foreach ($ps as $cur_forum)
{
	if ($cur_forum['password'] != '')
		if (check_forum_login_cookie($cur_forum['fid'], $cur_forum['password'], true) === false)
			continue;

	if (!isset($catgeories[$cur_forum['cid']])) // A new category since last iteration?
		$categories[$cur_forum['cid']] = array(
			'name' => $cur_forum['cat_name'],
			'id' => $cur_forum['cid'],
		);
	
	$forums[] = array(
		'parent_forum' => $cur_forum['parent_forum'],
		'category_id' => $cur_forum['cid'],
		'id' => $cur_forum['fid'],
		'name' => $cur_forum['forum_name'],
	);
}

$tpl = load_template('search.tpl');
echo $tpl->render(
	array(
		'lang_search' => $lang_search,
		'lang_common' => $lang_common,
		'form_action' => panther_link($panther_url['search']),
		'panther_config' => $panther_config,
		'panther_user' => $panther_user,
		'search_all_forums' => ($panther_config['o_search_all_forums'] == '1' || $panther_user['is_admmod']) ? true : false,
		'categories' => $categories,
		'forums' => $forums,
	)
);

require PANTHER_ROOT.'footer.php';