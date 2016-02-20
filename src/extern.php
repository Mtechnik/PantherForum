<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher

-----------------------------------------------------------------------------

  INSTRUCTIONS

  This script is used to include information about your board from
  pages outside the forums and to syndicate news about recent
  discussions via RSS/Atom/XML. The script displays a list of topics
  in a forum, or posts from a single topic.

  The scripts behaviour is controlled via variables supplied in the
  URL to the script. The different variables are: show (how many items
  to display), fid (the ID or IDs of the forum(s) to poll for topics),
  nfid (the ID or IDs of forums that should be excluded), tid (the ID
  of the topic from which to display posts) and type (output as HTML or
  RSS). Possible/default values are:

	type:   rss - output as RSS 2.0
			atom - output as Atom 1.0
			xml - output as XML
			html - output as HTML (<li>'s)

	fid:    One or more forum IDs (comma-separated). If ignored,
			topics from all readable forums will be pulled.

	nfid:   One or more forum IDs (comma-separated) that are to be
			excluded, e.g. the ID of a test forum.

	tid:    A topic ID from which to show posts. If a tid is supplied,
			fid and nfid are ignored.

	show:   Any integer value between 1 and 50. The default is 15.

	order:  last_post - show topics ordered by when they were last
						posted in, giving information about the reply.
			posted - show topics ordered by when they were first
					 posted, giving information about the original post.

-----------------------------------------------------------------------------*/

if (!defined('PANTHER_ROOT'))
{
	define('PANTHER_QUIET_VISIT', 1);
	define('PANTHER_ROOT', __DIR__.'/');
	require PANTHER_ROOT.'include/common.php';
}

function authenticate_user($user, $password, $password_is_hash = false)
{
	global $db, $panther_user;
	$field = (is_int($user) ? 'u.id' : 'u.username');

	$ps = $db->run('SELECT u.*, g.*, o.logged, o.idle FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id LEFT JOIN '.$db->prefix.'online AS o ON o.user_id=u.id WHERE '.$field.'=?', array($user));
	// Check if there's a user matching $user and $password
	$panther_user = $ps->fetch();

	if (!isset($panther_user['id']) ||
		($password_is_hash && !panther_hash_equals($password, $panther_user['password'])) ||
		(!$password_is_hash && !panther_hash_equals($password, $panther_user['password'])))
		set_default_user();
	else
		$panther_user['is_guest'] = false;
}

function escape_cdata($str)
{
	return str_replace(']]>', ']]&gt;', $str);
}


 // The length at which topic subjects will be truncated (for HTML output)
if (!defined('FORUM_EXTERN_MAX_SUBJECT_LENGTH'))
	define('FORUM_EXTERN_MAX_SUBJECT_LENGTH', 30);

// If we're a guest and we've sent a username/pass, we can try to authenticate using those details
if ($panther_user['is_guest'] && isset($_SERVER['PHP_AUTH_USER']))
	authenticate_user($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

if ($panther_user['g_read_board'] == '0')
{
	http_authenticate_user();
	exit($lang_common['No view']);
}

//
// Sends the proper headers for Basic HTTP Authentication
//
function http_authenticate_user()
{
	global $panther_config, $panther_user;

	if (!$panther_user['is_guest'])
		return;

	header('WWW-Authenticate: Basic realm="'.$panther_config['o_board_title'].' External Syndication"');
	header('HTTP/1.0 401 Unauthorized');
}


//
// Output $feed as RSS 2.0
//
function output_rss($feed)
{
	global $lang_common, $panther_config;

	// Send XML/no cache headers
	header('Content-Type: application/xml; charset=utf-8');
	header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
	echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'."\n";
	echo "\t".'<channel>'."\n";
	echo "\t\t".'<atom:link href="'.panther_htmlspecialchars(get_current_url()).'" rel="self" type="application/rss+xml" />'."\n";
	echo "\t\t".'<title><![CDATA['.escape_cdata($feed['title']).']]></title>'."\n";
	echo "\t\t".'<link>'.panther_htmlspecialchars($feed['link']).'</link>'."\n";
	echo "\t\t".'<description><![CDATA['.escape_cdata($feed['description']).']]></description>'."\n";
	echo "\t\t".'<lastBuildDate>'.gmdate('r', count($feed['items']) ? $feed['items'][0]['pubdate'] : time()).'</lastBuildDate>'."\n";

	if ($panther_config['o_show_version'] == '1')
		echo "\t\t".'<generator>Panther '.$panther_config['o_cur_version'].'</generator>'."\n";
	else
		echo "\t\t".'<generator>Panther</generator>'."\n";

	foreach ($feed['items'] as $item)
	{
		echo "\t\t".'<item>'."\n";
		echo "\t\t\t".'<title><![CDATA['.escape_cdata($item['title']).']]></title>'."\n";
		echo "\t\t\t".'<link>'.panther_htmlspecialchars($item['link']).'</link>'."\n";
		echo "\t\t\t".'<description><![CDATA['.escape_cdata($item['description']).']]></description>'."\n";
		echo "\t\t\t".'<author><![CDATA['.(isset($item['author']['email']) ? escape_cdata($item['author']['email']) : 'dummy@example.com').' ('.escape_cdata($item['author']['name']).')]]></author>'."\n";
		echo "\t\t\t".'<pubDate>'.gmdate('r', $item['pubdate']).'</pubDate>'."\n";
		echo "\t\t\t".'<guid>'.panther_htmlspecialchars($item['link']).'</guid>'."\n";

		echo "\t\t".'</item>'."\n";
	}

	echo "\t".'</channel>'."\n";
	echo '</rss>'."\n";
}


//
// Output $feed as Atom 1.0
//
function output_atom($feed)
{
	global $lang_common, $panther_config;

	// Send XML/no cache headers
	header('Content-Type: application/atom+xml; charset=utf-8');
	header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
	echo '<feed xmlns="http://www.w3.org/2005/Atom">'."\n";

	echo "\t".'<title type="html"><![CDATA['.escape_cdata($feed['title']).']]></title>'."\n";
	echo "\t".'<link rel="self" href="'.panther_htmlspecialchars(get_current_url()).'"/>'."\n";
	echo "\t".'<link href="'.panther_htmlspecialchars($feed['link']).'"/>'."\n";
	echo "\t".'<updated>'.gmdate('Y-m-d\TH:i:s\Z', count($feed['items']) ? $feed['items'][0]['pubdate'] : time()).'</updated>'."\n";

	if ($panther_config['o_show_version'] == '1')
		echo "\t".'<generator version="'.$panther_config['o_cur_version'].'">Panther</generator>'."\n";
	else
		echo "\t".'<generator>Panther</generator>'."\n";

	echo "\t".'<id>'.panther_htmlspecialchars($feed['link']).'</id>'."\n";

	$content_tag = ($feed['type'] == 'posts') ? 'content' : 'summary';

	foreach ($feed['items'] as $item)
	{
		echo "\t".'<entry>'."\n";
		echo "\t\t".'<title type="html"><![CDATA['.escape_cdata($item['title']).']]></title>'."\n";
		echo "\t\t".'<link rel="alternate" href="'.panther_htmlspecialchars($item['link']).'"/>'."\n";
		echo "\t\t".'<'.$content_tag.' type="html"><![CDATA['.escape_cdata($item['description']).']]></'.$content_tag.'>'."\n";
		echo "\t\t".'<author>'."\n";
		echo "\t\t\t".'<name><![CDATA['.escape_cdata($item['author']['name']).']]></name>'."\n";

		if (isset($item['author']['email']))
			echo "\t\t\t".'<email><![CDATA['.escape_cdata($item['author']['email']).']]></email>'."\n";

		if (isset($item['author']['uri']))
			echo "\t\t\t".'<uri>'.panther_htmlspecialchars($item['author']['uri']).'</uri>'."\n";

		echo "\t\t".'</author>'."\n";
		echo "\t\t".'<updated>'.gmdate('Y-m-d\TH:i:s\Z', $item['pubdate']).'</updated>'."\n";

		echo "\t\t".'<id>'.panther_htmlspecialchars($item['link']).'</id>'."\n";
		echo "\t".'</entry>'."\n";
	}

	echo '</feed>'."\n";
}


//
// Output $feed as XML
//
function output_xml($feed)
{
	global $lang_common, $panther_config;

	// Send XML/no cache headers
	header('Content-Type: application/xml; charset=utf-8');
	header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
	echo '<source>'."\n";
	echo "\t".'<url>'.panther_htmlspecialchars($feed['link']).'</url>'."\n";

	$forum_tag = ($feed['type'] == 'posts') ? 'post' : 'topic';

	foreach ($feed['items'] as $item)
	{
		echo "\t".'<'.$forum_tag.' id="'.$item['id'].'">'."\n";

		echo "\t\t".'<title><![CDATA['.escape_cdata($item['title']).']]></title>'."\n";
		echo "\t\t".'<link>'.panther_htmlspecialchars($item['link']).'</link>'."\n";
		echo "\t\t".'<content><![CDATA['.escape_cdata($item['description']).']]></content>'."\n";
		echo "\t\t".'<author>'."\n";
		echo "\t\t\t".'<name><![CDATA['.escape_cdata($item['author']['name']).']]></name>'."\n";

		if (isset($item['author']['email']))
			echo "\t\t\t".'<email><![CDATA['.escape_cdata($item['author']['email']).']]></email>'."\n";

		if (isset($item['author']['uri']))
			echo "\t\t\t".'<uri>'.panther_htmlspecialchars($item['author']['uri']).'</uri>'."\n";

		echo "\t\t".'</author>'."\n";
		echo "\t\t".'<posted>'.gmdate('r', $item['pubdate']).'</posted>'."\n";

		echo "\t".'</'.$forum_tag.'>'."\n";
	}

	echo '</source>'."\n";
}


//
// Output $feed as HTML (using <li> tags)
//
function output_html($feed)
{

	// Send the Content-type header in case the web server is setup to send something else
	header('Content-type: text/html; charset=utf-8');
	header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	foreach ($feed['items'] as $item)
	{
		if (utf8_strlen($item['title']) > FORUM_EXTERN_MAX_SUBJECT_LENGTH)
			$subject_truncated = panther_htmlspecialchars(panther_trim(utf8_substr($item['title'], 0, (FORUM_EXTERN_MAX_SUBJECT_LENGTH - 5)))).' …';
		else
			$subject_truncated = panther_htmlspecialchars($item['title']);

		echo '<li><a href="'.panther_htmlspecialchars($item['link']).'" title="'.panther_htmlspecialchars($item['title']).'">'.$subject_truncated.'</a></li>'."\n";
	}
}

require PANTHER_ROOT.'include/parser.php';

// Determine what type of feed to output
$type = isset($_GET['type']) ? strtolower($_GET['type']) : 'html';
if (!in_array($type, array('html', 'rss', 'atom', 'xml')))
	$type = 'html';

$show = isset($_GET['show']) ? intval($_GET['show']) : 15;
if ($show < 1 || $show > 50)
	$show = 15;

// Was a topic ID supplied?
if (isset($_GET['tid']))
{
	$tid = intval($_GET['tid']);

	// Fetch topic subject
	$data = array(
		':gid'	=>	$panther_user['g_id'],
		':tid'	=>	$tid,
	);

	$ps = $db->run('SELECT t.subject, t.first_post_id, t.forum_id FROM '.$db->prefix.'topics AS t LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=t.forum_id AND fp.group_id=:gid) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.moved_to IS NULL AND t.id=:tid', $data);
	if (!$ps->rowCount())
	{
		http_authenticate_user();
		exit($lang_common['Bad request']);
	}

	$cur_topic = $ps->fetch();
	if (!isset($panther_forums[$cur_topic['forum_id']]) || $panther_forums[$cur_topic['forum_id']]['password'] != '' && check_forum_login_cookie($cur_topic['forum_id'], $panther_forums[$cur_topic['forum_id']]['password'], true) === false || $panther_forums[$cur_topic['forum_id']]['protected'] == '1' && !$panther_user['is_admmod'])
		exit($lang_common['Bad request']);

	if ($panther_config['o_censoring'] == '1')
		$cur_topic['subject'] = censor_words($cur_topic['subject']);

	// Setup the feed
	$feed = array(
		'title' 		=>	$panther_config['o_board_title'].$lang_common['Title separator'].$cur_topic['subject'],
		'link'			=>	panther_htmlspecialchars_decode(panther_link($panther_url['topic'], array($tid, url_friendly($cur_topic['subject'])))),
		'description'		=>	sprintf($lang_common['RSS description topic'], $cur_topic['subject']),
		'items'			=>	array(),
		'type'			=>	'posts'
	);

	// Fetch $show posts
	$data = array(
		':tid'	=>	$tid,
	);

	$ps = $db->run('SELECT p.id, p.poster, p.message, p.hide_smilies, p.posted, p.poster_id, u.email_setting, u.email, p.poster_email FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'users AS u ON u.id=p.poster_id WHERE p.topic_id=:tid ORDER BY p.posted DESC LIMIT '.$show, $data);
	foreach ($ps as $cur_post)
	{
		$cur_post['message'] = $parser->parse_message($cur_post['message'], $cur_post['hide_smilies']);
		$item = array(
			'id'			=>	$cur_post['id'],
			'title'			=>	$cur_topic['first_post_id'] == $cur_post['id'] ? $cur_topic['subject'] : $lang_common['RSS reply'].$cur_topic['subject'],
			'link'			=>	panther_link($panther_url['post'], array($cur_post['id'])),
			'description'		=>	$cur_post['message'],
			'author'		=>	array(
				'name'	=> $cur_post['poster'],
			),
			'pubdate'		=>	$cur_post['posted']
		);

		if ($cur_post['poster_id'] > 1)
		{
			if ($cur_post['email_setting'] == '0' && !$panther_user['is_guest'])
				$item['author']['email'] = $cur_post['email'];

			$item['author']['uri'] = panther_link($panther_url['profile'], array($cur_post['poster_id']));
		}
		else if ($cur_post['poster_email'] != '' && !$panther_user['is_guest'])
			$item['author']['email'] = $cur_post['poster_email'];

		$feed['items'][] = $item;
	}

	$output_func = 'output_'.$type;
	$output_func($feed);
}
else
{
	$order_posted = isset($_GET['order']) && strtolower($_GET['order']) == 'posted';
	$forum_name = '';
	$forum_sql = '';

	// Were any forum IDs supplied?
	$select = array($panther_user['g_id']);
	if (isset($_GET['fid']) && is_scalar($_GET['fid']) && $_GET['fid'] != '')
	{
		$fids = explode(',', panther_trim($_GET['fid']));
		$fids = array_map('intval', $fids);

		$markers = array();
		if (!empty($fids))
		{
			for ($i = 0; $i < count($fids); $i++)
			{
				if (!isset($panther_forums[$fids[$i]]))
				{
					$count = count($fids);
					unset($fids[$i]);
					if ($count == 1)
						exit($lang_common['Bad request']);
					else
						continue;
				}
				else if ($panther_forums[$fids[$i]]['password'] != '' && check_forum_login_cookie($fids[$i], $panther_forums[$fids[$i]]['password'], true) === false || $panther_forums[$fids[$i]]['protected'] == '1' && !$panther_user['is_admmod'])
				{
					// Artificially set the query string for no view forums
					if (!isset($_GET['nfid']))
						$_GET['nfid'] = '';

					$_GET['nfid'] = (empty($_GET['nfid']) ? $fids[$i] : ','.$fids[$i]);
					continue;
				}

				$markers[] = '?';
				$select[] = $fids[$i];
			}

			if (!empty($markers))
				$forum_sql .= ' AND t.forum_id IN('.implode(',', $markers).')';
		}

		if (count($fids) == 1)
		{
			// Fetch forum name
			$data = array(
				':gid'	=>	$panther_user['g_id'],
				':fid'	=>	$fids[0],
			);

			$ps = $db->run('SELECT f.forum_name FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.id=:fid', $data);
			if ($ps->rowCount())
				$forum_name = $lang_common['Title separator'].$ps->fetchColumn();
		}
	}

	// Any forum IDs to exclude?
	$data = array();
	if (isset($_GET['nfid']) && is_scalar($_GET['nfid']) && $_GET['nfid'] != '')
	{
		$nfids = (strpos($_GET['nfid'], ',') !== false) ? explode(',', panther_trim($_GET['nfid'])) : array($_GET['nfid']);
		$nfids = array_map('intval', $nfids);

		if (!empty($nfids))
		{
			$markers = array();
			for ($i = 0; $i < count($nfids); $i++)
			{
				$markers[] = '?';
				$data[] = $nfids[$i];
			}

			$forum_sql .= ' AND t.forum_id NOT IN('.implode(',', $markers).')';
		}
	}

	// Only attempt to cache if caching is enabled and we have all or a single forum
	if ($panther_config['o_feed_ttl'] > 0 && ($forum_sql == '' || ($forum_name != '' && !isset($_GET['nfid']))))
		$cache_id = 'feed'.sha1($panther_user['g_id'].'|'.$lang_common['lang_identifier'].'|'.($order_posted ? '1' : '0').($forum_name == '' ? '' : '|'.$fids[0]));

	// Load cached feed
	if (isset($cache_id) && file_exists(FORUM_CACHE_DIR.'cache_'.$cache_id.'.php'))
		include FORUM_CACHE_DIR.'cache_'.$cache_id.'.php';

	$now = time();
	if (!isset($feed) || $cache_expire < $now)
	{
		// Setup the feed
		$feed = array(
			'title' 		=>	$panther_config['o_board_title'].$forum_name,
			'link'			=>	panther_link($panther_url['index']),
			'description'	=>	sprintf($lang_common['RSS description'], $panther_config['o_board_title']),
			'items'			=>	array(),
			'type'			=>	'topics'
		);

		// Fetch $show topics
		$select = array_merge($select, $data);
		$ps = $db->run('SELECT t.id, t.poster, t.subject, t.forum_id, t.posted, t.last_post, t.last_poster, p.message, p.hide_smilies, u.email_setting, u.email, p.poster_id, p.poster_email FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'posts AS p ON p.id='.($order_posted ? 't.first_post_id' : 't.last_post_id').' INNER JOIN '.$db->prefix.'users AS u ON u.id=p.poster_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=t.forum_id AND fp.group_id=?) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.moved_to IS NULL'.$forum_sql.' ORDER BY '.($order_posted ? 't.posted' : 't.last_post').' DESC LIMIT '.(isset($cache_id) ? 50 : $show), $select);
		foreach ($ps as $cur_topic)
		{
			if ($panther_forums[$cur_topic['forum_id']]['password'] != '' && check_forum_login_cookie($cur_topic['forum_id'], $panther_forums[$cur_topic['forum_id']]['password'], true) === false || $panther_forums[$cur_topic['forum_id']]['protected'] == '1' && !$panther_user['is_admmod'])
				continue;

			if ($panther_config['o_censoring'] == '1')
				$cur_topic['subject'] = censor_words($cur_topic['subject']);

			$cur_topic['message'] = $parser->parse_message($cur_topic['message'], $cur_topic['hide_smilies']);
			$item = array(
				'id'			=>	$cur_topic['id'],
				'title'			=>	$cur_topic['subject'],
				'link'			=>	panther_htmlspecialchars_decode(panther_link($panther_url[(($order_posted) ? 'topic' : 'topic_new_posts')], array($cur_topic['id'], url_friendly($cur_topic['subject'])))),
				'description'	=>	$cur_topic['message'],
				'author'		=>	array(
					'name'	=> $order_posted ? $cur_topic['poster'] : $cur_topic['last_poster']
				),
				'pubdate'		=>	$order_posted ? $cur_topic['posted'] : $cur_topic['last_post']
			);

			if ($cur_topic['poster_id'] > 1)
			{
				if ($cur_topic['email_setting'] == '0' && !$panther_user['is_guest'])
					$item['author']['email'] = $cur_topic['email'];

				$item['author']['uri'] = panther_link($panther_url['profile'], array($cur_topic['poster_id']));
			}
			else if ($cur_topic['poster_email'] != '' && !$panther_user['is_guest'])
				$item['author']['email'] = $cur_topic['poster_email'];

			$feed['items'][] = $item;
		}

		// Output feed as PHP code
		if (isset($cache_id))
		{
			if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
				require PANTHER_ROOT.'include/cache.php';

			$content = '<?php'."\n\n".'$feed = '.var_export($feed, true).';'."\n\n".'$cache_expire = '.($now + ($panther_config['o_feed_ttl'] * 60)).';'."\n\n".'?>';
			panther_write_cache_file('cache_'.$cache_id.'.php', $content);
		}
	}

	// If we only want to show a few items but due to caching we have too many
	if (count($feed['items']) > $show)
		$feed['items'] = array_slice($feed['items'], 0, $show);

	// Prepend the current base URL onto some links. Done after caching to handle http/https correctly
	$feed['link'] = $feed['link'];

	foreach ($feed['items'] as $key => $item)
	{
		$feed['items'][$key]['link'] = panther_htmlspecialchars_decode($item['link']);

		if (isset($item['author']['uri']))
			$feed['items'][$key]['author']['uri'] = $item['author']['uri'];
	}

	$output_func = 'output_'.$type;
	$output_func($feed);
}

exit;