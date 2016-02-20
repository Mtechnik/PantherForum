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

require PANTHER_ROOT.'lang/'.$panther_user['language'].'/index.php';
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/online.php';

if ($panther_user['g_read_board'] == '0')
	message($lang_common['No view'], false, '403 Forbidden');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id < 1)
	message($lang_common['Bad request'], false, '404 Not Found');

// Load the viewforum.php language file
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/forum.php';

$data = array(
	':gid'	=>	$panther_user['g_id'],
	':fid'	=>	$id
);

// Fetch some info about the forum
if (!$panther_user['is_guest'])
{
	$data[':id'] = $panther_user['id'];
	$ps = $db->run('SELECT pf.forum_name AS parent, f.parent_forum, f.protected, f.forum_name, f.redirect_url, f.moderators, f.password, f.num_topics, f.sort_by, fp.post_topics, s.user_id AS is_subscribed FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_subscriptions AS s ON (f.id=s.forum_id AND s.user_id=:id) LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) LEFT JOIN '.$db->prefix.'forums AS pf ON f.parent_forum=pf.id WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.id=:fid', $data);
}
else
	$ps = $db->run('SELECT pf.forum_name AS parent, f.parent_forum, f.protected, f.forum_name, f.redirect_url, f.moderators, f.password, f.num_topics, f.sort_by, fp.post_topics, 0 AS is_subscribed FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) LEFT JOIN '.$db->prefix.'forums AS pf ON f.parent_forum=pf.id WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.id=:fid', $data);

if (!$ps->rowCount())
	message($lang_common['Bad request'], false, '404 Not Found');

$cur_forum = $ps->fetch();

($hook = get_extensions('forum_before_redirect')) ? eval($hook) : null;

// Is this a redirect forum? In that case, redirect!
if ($cur_forum['redirect_url'] != '')
{
	header('Location: '.$cur_forum['redirect_url']);
	exit;
}

if ($cur_forum['password'] != '')
{
	if (isset($_POST['form_sent']))
		validate_login_attempt($id);
	else
		check_forum_login_cookie($id, $cur_forum['password']);
}

// Sort out who the moderators are and if we are currently a moderator (or an admin)
$mods_array = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();
$is_admmod = ($panther_user['is_admin'] || ($panther_user['g_moderator'] == '1' && $panther_user['g_global_moderator'] || array_key_exists($panther_user['username'], $mods_array))) ? true : false;

switch ($cur_forum['sort_by'])
{
	case 0:
		$sort_by = 'last_post DESC';
		break;
	case 1:
		$sort_by = 'posted DESC';
		break;
	case 2:
		$sort_by = 'subject ASC';
		break;
	default:
		$sort_by = 'last_post DESC';
		break;
}

// Get topic/forum tracking data
$new_topics = array();
if (!$panther_user['is_guest'])
{
	$data = array(
		':gid'	=> $panther_user['g_id'],
		':last_visit' => $panther_user['last_visit'],
	);

	$ps = $db->run('SELECT t.forum_id, t.id, t.last_post FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.last_post>:last_visit AND t.moved_to IS NULL', $data);
	if ($ps->rowCount())
	{
		foreach ($ps as $cur_topic)
			$new_topics[$cur_topic['forum_id']][$cur_topic['id']] = $cur_topic['last_post'];
	}

	$tracked_topics = get_tracked_topics();
}

// Determine the topic offset (based on $_GET['p'])
$num_pages = ceil($cur_forum['num_topics'] / $panther_user['disp_topics']);

// Preg replace is slow!
$url_forum = url_friendly($cur_forum['forum_name']);
$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);
$start_from = $panther_user['disp_topics'] * ($p - 1);

// Add relationship meta tags
$page_head = array();
$page_head['canonical'] = array('href' => panther_link($panther_url['forum'], array($id, $url_forum)), 'rel' => 'canonical');

if ($num_pages > 1)
{
	if ($p > 1)
		$page_head['prev'] = array('href' => panther_link($panther_url['forum_page'], array($id, ($p -1), $url_forum)), 'rel' => 'prev');
	if ($p < $num_pages)
		$page_head['next'] = array('href' => panther_link($panther_url['forum_page'], array($id, ($p +1), $url_forum)), 'rel' => 'next');
}

if ($panther_config['o_feed_type'] == '1')
	$page_head['feed'] = array('href' => panther_link($panther_url['forum_rss'], array($id)), 'rel' => 'alternate', 'type' => 'application/rss+xml', 'title' => $lang_common['RSS forum feed']);
else if ($panther_config['o_feed_type'] == '2')
	$page_head['feed'] = array('href' => panther_link($panther_url['forum_atom'], array($id)), 'rel' => 'alternate', 'type' => 'application/atom+xml', 'title' => $lang_common['Atom forum feed']);

$forum_actions = array();
if (!$panther_user['is_guest'])
{
	$token = generate_csrf_token('viewforum.php');
	if ($panther_config['o_forum_subscriptions'] == '1')
	{
		if ($cur_forum['is_subscribed'])
			$forum_actions[] = array('info' => $lang_forum['Is subscribed'], 'href' => panther_link($panther_url['forum_unsubscribe'], array($id, generate_csrf_token('viewforum.php', false))), 'title' => $lang_forum['Unsubscribe']);
		else
			$forum_actions[] = array('href' => panther_link($panther_url['forum_subscribe'], array($id, $token)), 'title' => $lang_forum['Subscribe']);
	}

	$forum_actions[] = array('href' => panther_link($panther_url['mark_forum_read'], array($id, $token)), 'title' => $lang_common['Mark forum read']);
}

// Load the cached announcements
if (file_exists(FORUM_CACHE_DIR.'cache_announcements.php'))
	require FORUM_CACHE_DIR.'cache_announcements.php';
else
{
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_announcements_cache();
	require FORUM_CACHE_DIR.'cache_announcements.php';
}

($hook = get_extensions('forum_before_header')) ? eval($hook) : null;

$page_title = array($panther_config['o_board_title'], $cur_forum['forum_name']);
define('PANTHER_ALLOW_INDEX', 1);
define('PANTHER_ACTIVE_PAGE', 'index');
require PANTHER_ROOT.'header.php';

$data = array(
	':gid'	=>	$panther_user['g_id'],
	':id'	=>	$id,
);

$forums = array();
$ps = $db->run('SELECT f.forum_desc, f.forum_name, f.id, f.last_post, f.last_post_id, f.last_poster, f.last_topic, f.last_topic_id, u.id AS uid, u.email, u.group_id, u.use_gravatar, f.moderators, f.num_posts, f.show_post_info, f.num_topics, f.redirect_url FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) LEFT JOIN '.$db->prefix.'users AS u ON (f.last_poster=u.username) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND parent_forum=:id ORDER BY disp_position', $data);
if ($ps->rowCount())
{
	$forum_count = 0;
	$forums = array();
	foreach ($ps as $cur_subforum)
	{
		$num_topics = $cur_subforum['num_topics'];
		$num_posts = $cur_subforum['num_posts'];
		if ($cur_subforum['moderators'] != '')
		{
			$cur_subforum['moderators'] = unserialize($cur_subforum['moderators']);
			$moderator_groups = array();
			if (isset($cur_subforum['moderators']['groups']))
			{
				$moderator_groups = $cur_subforum['moderators']['groups'];
				unset($cur_subforum['moderators']['groups']);
			}

			if (count($cur_subforum['moderators']))
			{
				$moderators = array();
				foreach ($cur_subforum['moderators'] as $mod_username => $mod_id)
					$moderators[] = colourize_group($mod_username, $moderator_groups[$mod_id], $mod_id);
			}
		}
		else
			$moderators = array();

		// Is this a redirect forum?
		if ($cur_subforum['redirect_url'] != '')
			$num_topics = $num_posts = '-';
		else
		{
			$num_topics = forum_number_format($num_topics);
			$num_posts = forum_number_format($num_posts);
		}
		
		$new = false;
		if (!$panther_user['is_guest'] && $cur_subforum['last_post'] > $panther_user['last_visit'] && (empty($tracked_topics['forums'][$cur_subforum['id']]) || $cur_subforum['last_post'] > $tracked_topics['forums'][$cur_subforum['id']]))
		{
			// There are new posts in this forum, but have we read all of them already?
			foreach ($new_topics[$cur_subforum['id']] as $check_topic_id => $check_last_post)
			{
				if ((empty($tracked_topics['topics'][$check_topic_id]) || $tracked_topics['topics'][$check_topic_id] < $check_last_post) && (empty($tracked_topics['forums'][$cur_subforum['id']]) || $tracked_topics['forums'][$cur_subforum['id']] < $check_last_post))
				{
					$new = true;
					break;
				}
			}
		}

		$forums[$cur_subforum['id']] = array(
			'moderators' => $moderators,
			'last_post' => ($cur_subforum['last_post']) ? format_time($cur_subforum['last_post']) : '',
			'num_topics' => $num_topics,
			'num_posts' => $num_posts,
			'forum_count' => forum_number_format($forum_count++),
			'search_link' => panther_link($panther_url['search_new_results'], array($cur_subforum['id'])),
			'link' => panther_link($panther_url['forum'], array($cur_subforum['id'], url_friendly($cur_subforum['forum_name']))),
			'forum_name' => $cur_subforum['forum_name'],
			'forum_desc' => $cur_subforum['forum_desc'],
			'redirect_url' => $cur_forum['redirect_url'],
			'show_post_info' => $cur_subforum['show_post_info'],
			'new' => $new,
		);

		if ($cur_subforum['last_post'])
		{
			$forums[$cur_subforum['id']]['last_post_avatar'] = generate_avatar_markup($cur_subforum['uid'], $cur_subforum['email'], $cur_subforum['use_gravatar'], array(32, 32));
			$forums[$cur_subforum['id']]['last_post_link'] = panther_link($panther_url['post'], array($cur_subforum['last_post_id']));
			$forums[$cur_subforum['id']]['last_topic_link'] = panther_link($panther_url['topic'], array($cur_subforum['last_topic_id'], url_friendly($cur_subforum['last_topic'])));
			$forums[$cur_subforum['id']]['last_topic'] = ((panther_strlen($cur_subforum['last_topic']) > 30) ? utf8_substr($cur_subforum['last_topic'], 0, 30).' …' : $cur_subforum['last_topic']);
			$forums[$cur_subforum['id']]['last_poster'] = (isset($cur_subforum['group_id'])) ? colourize_group($cur_subforum['last_poster'], $cur_subforum['group_id'], $cur_subforum['uid']) : colourize_group($cur_subforum['last_poster'], PANTHER_GUEST);
		}
	}
}

$announcements = array();
if (!empty($panther_announcements[$id]))
{
	$announce_count = 0;
	foreach ($panther_announcements[$id] as $cur_announce)
	{
		$data = array(
			':id'	=>	$cur_announce['user_id'],
		);

		$ps = $db->select('users', 'username, group_id', $data, 'id=:id');
		list($username, $group_id) = $ps->fetch(PDO::FETCH_NUM);

		$announcements[] = array(
			'count' => forum_number_format($announce_count++),
			'user' => colourize_group($username, $group_id, $cur_announce['user_id']),
			'link' => panther_link($panther_url['announcement_fid'], array($cur_announce['id'], $id, $cur_announce['url_subject'])),
			'subject' => $cur_announce['subject'],
		);	
	}
}

// Retrieve a list of topic IDs, LIMIT is (really) expensive so we only fetch the IDs here then later fetch the remaining data
$data = array(
	':id'	=>	$id,
	':start' => $start_from,
	':limit' => $panther_user['disp_topics'],
);

if ($cur_forum['protected'] == '1' && !$is_admmod)
{
	$where = ' AND poster=:username';
	$data[':username'] = $panther_user['username']; // This won't work with guests, but there's nothing that can be done
}
else
	$where = '';

$topics = array();
$forum_has_posts = false;
$ps = $db->select('topics', 'id', $data, 'forum_id=:id AND approved=1 AND deleted=0'.$where, 'sticky DESC, '.$sort_by.', id DESC LIMIT :start, :limit');
if ($ps->rowCount())
{
	$forum_has_posts = true;	// If there are topics in this forum
	$topic_ids = $placeholders = $data = array();
	$ps->setFetchMode(PDO::FETCH_COLUMN, 0);
	foreach ($ps as $cur_topic_id)
	{
		$topic_ids[] = $cur_topic_id;
		$placeholders[] = '?';
	}

	// Fetch list of topics to display on this page
	if ($panther_user['is_guest'] || $panther_config['o_show_dot'] == '0') // Without "the dot"
		$sql = 'SELECT u.id AS uid, u.group_id, u.email, u.use_gravatar, up.id AS up_id, up.group_id AS up_group_id, t.id, t.poster, t.subject, t.question, t.posted, t.last_post, t.last_post_id, t.last_poster, t.num_views, t.num_replies, t.closed, t.sticky, t.moved_to FROM '.$db->prefix.'topics AS t LEFT JOIN '.$db->prefix.'users AS u ON (t.last_poster=u.username) LEFT JOIN '.$db->prefix.'users AS up ON (t.poster=up.username) WHERE t.id IN('.implode(',', $placeholders).') AND t.approved=1 AND t.deleted=0 ORDER BY t.sticky DESC, t.'.$sort_by.', t.id DESC';
	else // With "the dot"
	{
		$data[] = $panther_user['id'];
		$sql = 'SELECT u.id AS uid, u.group_id, u.email, u.use_gravatar, up.id AS up_id, up.group_id AS up_group_id, p.poster_id AS has_posted, t.id, t.subject, t.question, t.poster, t.posted, t.last_post, t.last_post_id, t.last_poster, t.num_views, t.num_replies, t.closed, t.sticky, t.moved_to FROM '.$db->prefix.'topics AS t LEFT JOIN '.$db->prefix.'posts AS p ON t.id=p.topic_id AND p.poster_id=? LEFT JOIN '.$db->prefix.'users AS u ON (t.last_poster=u.username) LEFT JOIN '.$db->prefix.'users AS up ON (t.poster=up.username) WHERE t.id IN('.implode(',', $placeholders).') AND t.approved=1 AND t.deleted=0 GROUP BY t.id ORDER BY t.sticky DESC, t.'.$sort_by.', t.id DESC';
	}

	$data = array_merge($data, $topic_ids);
	$ps = $db->run($sql, $data);

	$topic_count = 0;
	foreach ($ps as $cur_topic)
	{
		$url_subject = url_friendly($cur_topic['subject']); // Preg match is slow!

		if ($panther_config['o_censoring'] == '1')
			$cur_topic['subject'] = censor_words($cur_topic['subject']);

		$num_pages_topic = ceil(($cur_topic['num_replies'] + 1) / $panther_user['disp_posts']);
		$topics[$cur_topic['id']] = array(
			'count' => ++$topic_count,
			'topic_count' => forum_number_format($topic_count + $start_from),
			'cur_topic' => $cur_topic,
			'topic_poster' => ($cur_topic['up_id'] > 1) ? colourize_group($cur_topic['poster'], $cur_topic['up_group_id'], $cur_topic['up_id']) : colourize_group($cur_topic['poster'], PANTHER_GUEST),
			'moved_to' => $cur_topic['moved_to'],
			'subject' => $cur_topic['subject'],
			'sticky' => $cur_topic['sticky'],
			'closed' => $cur_topic['closed'],
			'question' => $cur_topic['question'],
			'topic_link' => panther_link($panther_url['topic'], array($cur_topic['id'], $url_subject)),
			'num_pages' => $num_pages_topic,
			'pagination' => paginate($num_pages_topic, -1, $panther_url['topic_paginate'], array($cur_topic['id'], $url_subject)),
			'new' => (!$panther_user['is_guest'] && $cur_topic['last_post'] > $panther_user['last_visit'] && (!isset($tracked_topics['topics'][$cur_topic['id']]) || $tracked_topics['topics'][$cur_topic['id']] < $cur_topic['last_post']) && (!isset($tracked_topics['forums'][$id]) || $tracked_topics['forums'][$id] < $cur_topic['last_post']) && is_null($cur_topic['moved_to'])) ? '1' : '0',
		);

		if (is_null($cur_topic['moved_to']))
		{
			$topics[$cur_topic['id']]['last_post_avatar'] = generate_avatar_markup($cur_topic['uid'], $cur_topic['email'], $cur_topic['use_gravatar'], array(32, 32));
			$topics[$cur_topic['id']]['last_post_link'] = panther_link($panther_url['post'], array($cur_topic['last_post_id']));
			$topics[$cur_topic['id']]['last_post'] = format_time($cur_topic['last_post']);
			$topics[$cur_topic['id']]['last_poster'] = ($cur_topic['uid'] > 1) ? colourize_group($cur_topic['last_poster'], $cur_topic['group_id'], $cur_topic['uid']) : colourize_group($cur_topic['last_poster'], PANTHER_GUEST);
			$topics[$cur_topic['id']]['num_replies'] = forum_number_format($cur_topic['num_replies']);

			if ($panther_config['o_topic_views'] == '1')
				$topics[$cur_topic['id']]['num_views'] = forum_number_format($cur_topic['num_views']);
		}
		else
			$topics[$cur_topic['id']]['topic_link'] = panther_link($panther_url['topic'], array($cur_topic['moved_to'], $url_subject));

		if ($topics[$cur_topic['id']]['new'] == '1')
			$topics[$cur_topic['id']]['new_link'] = panther_link($panther_url['topic_new_posts'], array($cur_topic['id'], $url_subject));
	}
}

($hook = get_extensions('forum_before_users_online')) ? eval($hook) : null;

if ($panther_config['o_users_online'] == '1')
{
	$post_ids = $users = $guests_in_forum = array();
	$data = array(
		':id'	=>	$id,
	);

	$ps = $db->run('SELECT p.id FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id WHERE f.id=:id', $data);
	foreach ($ps as $cur_post)
		$post_ids[] = $cur_post['id'];

	$ps = $db->run('SELECT o.user_id, o.ident, o.currently, o.logged, u.group_id FROM '.$db->prefix.'online AS o INNER JOIN '.$db->prefix.'users AS u ON o.user_id=u.id WHERE o.currently LIKE \'%viewtopic.php%\' OR o.currently LIKE \'%viewforum%\' AND o.idle=0');
	foreach ($ps as $user_online)
	{
		if (strpos($user_online['currently'], '&p=') !== false)
		{
			preg_match('~&p=(.*)~', $user_online['currently'], $replace);
			$user_online['currently'] = str_replace($replace[0], '', $user_online['currently']);
		}

		$tid = filter_var($user_online['currently'], FILTER_SANITIZE_NUMBER_INT);
		if (strpos($user_online['currently'], 'viewforum.php?id='.$id) !== false)
		{
			if ($user_online['user_id'] == 1)
				$guests_in_forum[] = $user_online['ident'];
			else
				$users[] = colourize_group($user_online['ident'], $user_online['group_id'], $user_online['user_id']);
		}
		elseif (strpos($user_online['currently'], '?pid') !== false)
		{
			if ($forum_has_posts)
			{
				if (in_array($tid, $post_ids))
				{
					if ($user_online['user_id'] == 1)
						$guests_in_forum[] = $user_online['ident'];
					else
					{
						if ($panther_user['g_view_users'] == 0)
							$member = colourize_group($user_online['ident'], $user_online['group_id']);
						else
							$member = colourize_group($user_online['ident'], $user_online['group_id'], $user_online['user_id']);

						$users[] = $member;
					}
				}
			}
		}
		elseif (strpos($user_online['currently'], '?id') !== false)
		{
			if ($forum_has_posts)
			{
				if (in_array($tid, $topic_ids))
				{ 
					if ($user_online['user_id'] == 1)
						$guests_in_forum[] = $user_online['ident'];
					else
						$users[] = colourize_group($user_online['ident'], $user_online['group_id'], $user_online['user_id']);
				}
			}
		}
	}
}

$render = array(
	'cur_forum' => $cur_forum,
	'panther_user' => $panther_user,
	'is_admmod' => $is_admmod,
	'post_link' => panther_link($panther_url['new_topic'], array($id)),
	'lang_common' => $lang_common,
	'lang_forum' => $lang_forum,
	'index_link' => panther_link($panther_url['index']),
	'forum_link' => panther_link($panther_url['forum'], array($id, $url_forum)),
	'pagination' => paginate($num_pages, $p, $panther_url['forum_paginate'], array($id, $url_forum)),
	'forums' => $forums,
	'topics' => $topics,
	'new_topics' => $new_topics,
	'announcements' => $announcements,
	'panther_config' => $panther_config,
	'forum_actions' => $forum_actions,
	'lang_common' => $lang_common,
	'lang_online' => $lang_online,
	'guests' => count($guests_in_forum),
	'users' => (count($users) > 0) ? implode(', ', $users) : $lang_online['no users'],
);

if ($cur_forum['parent'])
	$render['parent_link'] = panther_link($panther_url['forum'], array($cur_forum['parent_forum'], url_friendly($cur_forum['parent'])));

$tpl = load_template('forum.tpl');
echo $tpl->render($render);

($hook = get_extensions('forum_after_display')) ? eval($hook) : null;

$forum_id = $id;
$footer_style = 'viewforum';
require PANTHER_ROOT.'footer.php';