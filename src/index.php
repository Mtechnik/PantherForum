<?php

/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

if (!defined('PANTHER'))
{
	define('PANTHER_ROOT', __DIR__.'/');
	require PANTHER_ROOT.'include/common.php';
}

if ($panther_user['g_read_board'] == '0')
	message($lang_common['No view'], false, '403 Forbidden');

// Load the index.php language file
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/index.php';

// Get list of forums and topics with new posts since last visit
if (!$panther_user['is_guest'])
{
	$data = array(
		':id'	=> $panther_user['g_id'],
		':last_visit'	=> $panther_user['last_visit'],
	);

	$ps = $db->run('SELECT f.id, f.last_post FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:id) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.last_post>:last_visit', $data);
	if ($ps->rowCount())
	{
		$forums = $new_topics = array();
		$tracked_topics = get_tracked_topics();
		foreach ($ps as $cur_forum)
		{
			if (!isset($tracked_topics['forums'][$cur_forum['id']]) || $tracked_topics['forums'][$cur_forum['id']] < $cur_forum['last_post'])
				$forums[$cur_forum['id']] = $cur_forum['last_post'];
		}

		if (!empty($forums))
		{
			if (empty($tracked_topics['topics']))
				$new_topics = $forums;
			else
			{
				for ($i = 0; $i < count($forums); $i++)
					$placeholders[] = '?';

				$data = array_keys($forums);
				$data[] = $panther_user['last_visit'];

				$ps = $db->run('SELECT forum_id, id, last_post FROM '.$db->prefix.'topics WHERE forum_id IN('.implode(',', $placeholders).') AND last_post>? AND moved_to IS NULL', $data);
				foreach ($ps as $cur_topic)
				{
					if (!isset($new_topics[$cur_topic['forum_id']]) && (!isset($tracked_topics['forums'][$cur_topic['forum_id']]) || $tracked_topics['forums'][$cur_topic['forum_id']] < $forums[$cur_topic['forum_id']]) && (!isset($tracked_topics['topics'][$cur_topic['id']]) || $tracked_topics['topics'][$cur_topic['id']] < $cur_topic['last_post']))
						$new_topics[$cur_topic['forum_id']] = $forums[$cur_topic['forum_id']];
				}
			}
		}
	}
}

$page_head['canonical'] = array('href' => panther_link($panther_url['index']), 'rel' => 'canonical');

if ($panther_config['o_feed_type'] == '1')
    $page_head['feed'] = array('href' => panther_link($panther_url['index_rss']), 'rel' => 'alternate', 'type' => 'application/rss+xml', 'title' => $lang_common['RSS active topics feed']);
else if ($panther_config['o_feed_type'] == '2') 
    $page_head['feed'] = array('href' => panther_link($panther_url['index_atom']), 'rel' => 'alternate', 'type' => 'application/atom+xml', 'title' => $lang_common['Atom active topics feed']);

$sub_forums = array();
$data = array(
	':id'	=>	$panther_user['g_id'],
);

$ps = $db->run('SELECT u.id AS uid, u.email, u.use_gravatar, u.group_id, f.num_topics, f.num_posts, f.last_topic, f.last_topic_id,  f.parent_forum, f.last_post_id, f.show_post_info, f.last_poster, f.last_post, f.id, f.forum_name, f.last_topic FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:id) LEFT JOIN '.$db->prefix.'users AS u ON (f.last_poster=u.username) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.parent_forum <> 0 ORDER BY f.disp_position', $data);
foreach ($ps as $current)
{
	if (!isset($sub_forums[$current['parent_forum']]))
		$sub_forums[$current['parent_forum']] = array();

	$sub_forums[$current['parent_forum']][] = $current;
}

($hook = get_extensions('index_before_header')) ? eval($hook) : null;

$page_title = array($panther_config['o_board_title']);
define('PANTHER_ALLOW_INDEX', 1);
define('PANTHER_ACTIVE_PAGE', 'index');
require PANTHER_ROOT.'header.php';

// Print the categories and forums
$ps = $db->run('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name, f.forum_desc, f.last_topic, f.last_topic_id, f.show_post_info, f.redirect_url, f.moderators, f.num_topics, f.num_posts, f.last_post, f.last_post_id, f.last_poster, f.parent_forum, f.last_topic, u.group_id, u.id AS uid, u.email, u.use_gravatar FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:id) LEFT JOIN '.$db->prefix.'users AS u ON (f.last_poster=u.username) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND (f.parent_forum IS NULL OR f.parent_forum=0) ORDER BY c.disp_position, c.id, f.disp_position', $data);

$categories = $forums = array();
foreach ($ps as $cur_forum)
{
	if (!in_array($cur_forum['cid'], $categories))
		$categories[$cur_forum['cid']] = array(
			'cid' => $cur_forum['cid'],
			'name' => $cur_forum['cat_name'],
		);

	$num_topics = $cur_forum['num_topics'];
	$num_posts = $cur_forum['num_posts'];

	$cur_forum['search_link'] = panther_link($panther_url['search_new_results'], array($cur_forum['fid']));
	if ($cur_forum['moderators'] != '')
	{
		$cur_forum['moderators'] = unserialize($cur_forum['moderators']);
		$moderator_groups = array();
		if (isset($cur_forum['moderators']['groups']))
		{
			$moderator_groups = $cur_forum['moderators']['groups'];
			unset($cur_forum['moderators']['groups']);
		}

		if (count($cur_forum['moderators']))
		{
			$moderators = array();
			foreach ($cur_forum['moderators'] as $mod_username => $mod_id)
				$moderators[] = colourize_group($mod_username, $moderator_groups[$mod_id], $mod_id);
		}
	}
	else
		$moderators = array();

	$subforum_list = array();
	if (isset($sub_forums[$cur_forum['fid']]))
	{
		// There can be more than one sub forum per forum
		foreach ($sub_forums[$cur_forum['fid']] as $cur_subforum)
		{
			$num_topics += $cur_subforum['num_topics'];
			$num_posts += $cur_subforum['num_posts'];

			if ($cur_forum['last_post'] < $cur_subforum['last_post'])
			{
				$cur_forum['last_post_id'] = $cur_subforum['last_post_id'];
				$cur_forum['last_poster'] = $cur_subforum['last_poster'];
				$cur_forum['last_post'] = $cur_subforum['last_post'];
				$cur_forum['email'] = $cur_subforum['email'];
				$cur_forum['uid'] = $cur_subforum['uid'];
				$cur_forum['use_gravatar'] = $cur_subforum['use_gravatar'];
				$cur_forum['group_id'] = $cur_subforum['group_id'];
				$cur_forum['last_topic'] = $cur_subforum['last_topic'];
				$cur_forum['last_topic_id'] = $cur_subforum['last_topic_id'];

				// If there are no new topics in the actual forum but there are in the sub forum
				if (!isset($new_topics[$cur_forum['fid']]) && isset($new_topics[$cur_subforum['id']]))
				{
					$new_topics[$cur_forum['fid']] = $cur_subforum['last_post'];
					$cur_forum['search_link'] = panther_link($panther_url['search_new_results'], array($cur_subforum['id']));
				}
			}

			$subforum_list[] = array('fid' => $cur_subforum['id'], 'name' => $cur_subforum['forum_name'], 'link' => panther_link($panther_url['forum'], array($cur_subforum['id'], url_friendly($cur_subforum['forum_name']))));
		}
	}

	if ($cur_forum['redirect_url'] != '')
		$num_topics = $num_posts = '-';
	else
	{
		$num_topics = forum_number_format($num_topics);
		$num_posts = forum_number_format($num_posts);
	}

	$forums[$cur_forum['fid']] = array(
		'cid' => $cur_forum['cid'],
		'fid' => $cur_forum['fid'],
		'moderators' => $moderators,
		'last_post' => ($cur_forum['last_post']) ? format_time($cur_forum['last_post']) : '',
		'num_topics' => $num_topics,
		'num_posts' => $num_posts,
		'link' => panther_link($panther_url['forum'], array($cur_forum['fid'], url_friendly($cur_forum['forum_name']))),
		'forum_name' => $cur_forum['forum_name'],
		'forum_desc' => $cur_forum['forum_desc'],
		'search_forum' => $cur_forum['search_link'],
		'redirect_url' => $cur_forum['redirect_url'],
		'show_post_info' => $cur_forum['show_post_info'],
		'subforum_list' => $subforum_list,
	);
		
	if ($cur_forum['last_post'])
	{
		$forums[$cur_forum['fid']]['last_post_avatar'] = generate_avatar_markup($cur_forum['uid'], $cur_forum['email'], $cur_forum['use_gravatar'], array(32, 32));
		$forums[$cur_forum['fid']]['last_post_link'] = panther_link($panther_url['post'], array($cur_forum['last_post_id']));
		$forums[$cur_forum['fid']]['last_topic_link'] = panther_link($panther_url['topic'], array($cur_forum['last_topic_id'], url_friendly($cur_forum['last_topic'])));
		$forums[$cur_forum['fid']]['last_topic'] = ((panther_strlen($cur_forum['last_topic']) > 30) ? utf8_substr($cur_forum['last_topic'], 0, 30).' …' : $cur_forum['last_topic']);
		$forums[$cur_forum['fid']]['last_poster'] = (isset($cur_forum['group_id'])) ? colourize_group($cur_forum['last_poster'], $cur_forum['group_id'], $cur_forum['uid']) : colourize_group($cur_forum['last_poster'], PANTHER_GUEST);
	}
}

if ($panther_config['o_users_online'] == '1')
{
	// Collect some statistics from the database
	if (file_exists(FORUM_CACHE_DIR.'cache_users_info.php'))
		include FORUM_CACHE_DIR.'cache_users_info.php';

	if (!defined('PANTHER_USERS_INFO_LOADED'))
	{
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';

		generate_users_info_cache();
		require FORUM_CACHE_DIR.'cache_users_info.php';
	}

	$ps = $db->run('SELECT SUM(num_topics), SUM(num_posts) FROM '.$db->prefix.'forums');
	list($stats['total_topics'], $stats['total_posts']) = array_map('intval', $ps->fetch(PDO::FETCH_NUM));

	$stats['newest_user'] = colourize_group($stats['last_user']['username'], $stats['last_user']['group_id'], $stats['last_user']['id']);

	// Fetch users online info and generate strings for output
	$num_guests = count($online['guests']);
	$num_users = count($online['users']);
	$num_bots = 0;
	$users = $bots = $bots_online = array();
	
	foreach ($online['users'] as $online_id => $details)
		$users[] = colourize_group($details['username'], $details['group_id'], $details['id'], $online_id);

	foreach ($online['guests'] as $details)
	{
		if (strpos($details['ident'], '[Bot]') !== false)
		{
			++$num_bots;
			$name = explode('[Bot]', $details['ident']);
			if (empty($bots[$name[1]]))
				$bots[$name[1]] = 1;
			else
				++$bots[$name[1]];
		}
	}
	foreach ($bots as $online_name => $online_id)
		   $bots_online[] = $online_name.' [Bot]'.($online_id > 1 ? ' ('.$online_id.')' : '');

	$num_guests = $num_guests - $num_bots;
}
else
{
	$num_guests = $num_bots = $num_users = 0;
	$users = $bots_online = array();
}

$groups = array();
foreach ($panther_groups as $g_id => $details)
{
	if (!in_array($g_id, array(PANTHER_GUEST, PANTHER_MEMBER)) && $details['g_colour'] !== '')
		$groups[] = array('link' => panther_link($panther_url['userlist_group'], array($g_id)), 'title' => colourize_group($details['g_title'], $g_id));
}

$tpl = load_template('index.tpl');
echo $tpl->render(
	array(
		'categories' => $categories,
		'forums' => $forums,
		'lang_common' => $lang_common,
		'lang_index' => $lang_index,
		'new_posts' => !empty($new_topics) ? $new_topics : array(),
		'panther_user' => $panther_user,
		'panther_config' => $panther_config,
		'mark_read' => panther_link($panther_url['mark_read'], array(generate_csrf_token('index.php'))),
		'num_users' => forum_number_format($num_users),
		'num_guests' => forum_number_format($num_guests),
		'num_bots' => forum_number_format($num_bots),
		'users' => $users,
		'bots' => $bots_online,
		'groups' => $groups,
		'stats' => $stats,
	)
);

($hook = get_extensions('index_after_display')) ? eval($hook) : null;

$footer_style = 'index';
require PANTHER_ROOT.'footer.php';