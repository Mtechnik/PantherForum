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

// Tell header we need some stuff
define('POSTING', 1);
define('REPUTATION', 1);

if ($panther_user['g_read_board'] == '0')
	message($lang_common['No view'], false, '403 Forbidden');

$action = isset($_GET['action']) ? $_GET['action'] : null;
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
if ($id < 1 && $pid < 1)
	message($lang_common['Bad request'], false, '404 Not Found');

// Load the viewtopic.php language file
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/topic.php';

// If a post ID is specified we determine topic ID and page number so we can redirect to the correct message
if ($pid)
{
	$data = array(
		':id'	=>	$pid,
	);
	$ps = $db->select('posts', 'topic_id, posted', $data, 'id=:id AND approved=1 AND deleted=0');
	if (!$ps->rowCount())
		message($lang_common['Bad request'], false, '404 Not Found');
	else
		list($id, $posted) = $ps->fetch(PDO::FETCH_NUM);
	
	$data = array(
		':id'	=>	$id,
		':posted'	=>	$posted,
	);

	// Determine on which page the post is located (depending on $panther_user['disp_posts'])
	$ps = $db->select('posts', 'COUNT(id)', $data, 'topic_id=:id AND posted<:posted AND approved=1 AND deleted=0');
	$num_posts = $ps->fetchColumn() + 1;

	$_GET['p'] = ceil($num_posts / $panther_user['disp_posts']);
}
else
{
	// If action=new, we redirect to the first new post (if any)
	if ($action == 'new')
	{
		if (!$panther_user['is_guest'])
		{
			// We need to check if this topic has been viewed recently by the user
			$tracked_topics = get_tracked_topics();
			$last_viewed = isset($tracked_topics['topics'][$id]) ? $tracked_topics['topics'][$id] : $panther_user['last_visit'];
			
			$data = array(
				':id'	=>	$id,
				':posted'	=>	$last_viewed,
			);

			$ps = $db->select('posts', 'MIN(id)', $data, 'topic_id=:id AND posted>:posted AND approved=1 AND deleted=0');
			$first_new_post_id = $ps->fetchColumn();

			if ($first_new_post_id)
			{
				header('Location: '.panther_link($panther_url['post'], array($first_new_post_id)));
				exit;
			}
		}

		// If there is no new post, we go to the last post
		$action = 'last';
	}

	// If action=last, we redirect to the last post
	if ($action == 'last')
	{
		$data = array(
			':id'	=>	$id,
		);

		$ps = $db->select('posts', 'MAX(id)', $data, 'topic_id=:id AND approved=1 AND deleted=0');
		$last_post_id = $ps->fetchColumn();

		if ($last_post_id)
		{
			header('Location: '.panther_link($panther_url['post'], array($last_post_id)));
			exit;
		}
	}
}

$data = array(
	':gid'	=>	$panther_user['g_id'],
	':tid'	=>	$id,
);

// Fetch some info about the topic
if (!$panther_user['is_guest'])
{
	$data[':id'] = $panther_user['id'];
	$ps = $db->run('SELECT pf.forum_name AS parent, f.parent_forum, f.protected, t.subject, t.poster, t.closed, t.archived, t.question, t.num_replies, t.sticky, t.first_post_id, t.last_post, p.type, p.options, p.votes, p.voters, p.posted, f.id AS forum_id, f.forum_name, f.use_reputation, f.moderators, f.password, fp.post_replies, fp.download, s.user_id AS is_subscribed FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'topic_subscriptions AS s ON (t.id=s.topic_id AND s.user_id=:id) LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) LEFT JOIN '.$db->prefix.'forums AS pf ON f.parent_forum=pf.id LEFT JOIN '.$db->prefix.'polls AS p ON t.id=p.topic_id WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id=:tid AND t.moved_to IS NULL AND t.approved=1 AND t.deleted=0', $data);
}
else
	$ps = $db->run('SELECT pf.forum_name AS parent, f.parent_forum, f.protected, t.subject, t.poster, t.closed, t.archived, t.question, t.num_replies, t.sticky, t.first_post_id, t.last_post, p.type, p.options, p.votes, p.voters, p.posted, f.id AS forum_id, f.forum_name, f.use_reputation, f.moderators, f.password, fp.post_replies, fp.download, 0 AS is_subscribed FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) LEFT JOIN '.$db->prefix.'forums AS pf ON f.parent_forum=pf.id LEFT JOIN '.$db->prefix.'polls AS p ON t.id=p.topic_id WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id=:tid AND t.moved_to IS NULL AND t.approved=1 AND t.deleted=0', $data);

if (!$ps->rowCount())
	message($lang_common['Bad request'], false, '404 Not Found');
else
	$cur_topic = $ps->fetch();

// Sort out who the moderators are and if we are currently a moderator (or an admin)
$mods_array = ($cur_topic['moderators'] != '') ? unserialize($cur_topic['moderators']) : array();
$is_admmod = ($panther_user['is_admin'] || ($panther_user['g_moderator'] == '1' && $panther_user['g_global_moderator'] ||  isset($mods_array[$panther_user['username']]))) ? true : false;
if ($is_admmod)
	$admin_ids = get_admin_ids();

if ($cur_topic['password'] != '')
		check_forum_login_cookie($cur_topic['forum_id'], $cur_topic['password']);

if ($cur_topic['protected'] == '1' && $panther_user['username'] != $cur_topic['poster'] && !$is_admmod)
	message($lang_common['No permission']);

if ($panther_config['o_archiving'] == '1' && $cur_topic['archived'] == '0')
{
	if ($cur_topic['archived'] !== '2')
	{
		$archive_rules = unserialize($panther_config['o_archive_rules']);
		$cur_topic['archived'] = check_archive_rules($archive_rules, $id);
	}
}

// Add/update this topic in our list of tracked topics
if (!$panther_user['is_guest'])
{
	$tracked_topics = get_tracked_topics();
	$tracked_topics['topics'][$id] = time();
	set_tracked_topics($tracked_topics);
}

// Preg replace is slow!
$url_subject = url_friendly($cur_topic['subject']);
$url_forum = url_friendly($cur_topic['forum_name']);

// Determine the post offset (based on $_GET['p'])
$num_pages = ceil(($cur_topic['num_replies'] + 1) / $panther_user['disp_posts']);

$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);
$start_from = $panther_user['disp_posts'] * ($p - 1);

if ($panther_config['o_censoring'] == '1')
	$cur_topic['subject'] = censor_words($cur_topic['subject']);

$quickpost = false;
if ($panther_config['o_quickpost'] == '1' && $cur_topic['archived'] != '1' &&
	($cur_topic['post_replies'] == '1' || ($cur_topic['post_replies'] == '' && $panther_user['g_post_replies'] == '1')) &&
	($cur_topic['closed'] == '0' || $is_admmod))
{
	// Load the post.php language file
	require PANTHER_ROOT.'lang/'.$panther_user['language'].'/post.php';

	$required_fields = array('req_message' => $lang_common['Message']);
	if ($panther_user['is_guest'])
	{
		$required_fields['req_username'] = $lang_post['Guest name'];
		if ($panther_config['p_force_guest_email'] == '1')
			$required_fields['req_email'] = $lang_common['Email'];
	}
	$quickpost = true;
}

if (!$panther_user['is_guest'] && $panther_config['o_topic_subscriptions'] == '1')
{
	$token = generate_csrf_token('viewforum.php');
	if ($cur_topic['is_subscribed'])
		$subscription = panther_link($panther_url['topic_unsubscribe'], array($id, $token));
	else
		$subscription = panther_link($panther_url['topic_subscribe'], array($id, $token));
}
else
	$subscription = '';

// Add relationship meta tags
$page_head = array();
$page_head['canonical'] = array('href' => panther_link($panther_url['topic'], array($id, $url_subject)), 'rel' => 'canonical');

if ($num_pages > 1)
{
	if ($p > 1)
		$page_head['prev'] = array('href' => panther_link($panther_url['topic_page'], array($id, $url_subject, ($p - 1))), 'rel' => 'prev');
	if ($p < $num_pages)
		$page_head['next'] = array('href' => panther_link($panther_url['topic_page'], array($id, $url_subject, ($p + 1))), 'rel' => 'next');
}

if ($panther_config['o_feed_type'] == '1')
	$page_head['feed'] = array('href' => panther_link($panther_url['topic_rss'], array($id)), 'type' => 'application/rss+xml', 'rel' => 'alternate', 'title' => $lang_common['RSS topic feed']);
else if ($panther_config['o_feed_type'] == '2')
	$page_head['feed'] = array('href' => panther_link($panther_url['topic_atom'], array($id)), 'type' => 'application/atom+xml', 'rel' => 'alternate', 'title' => $lang_common['Atom topic feed']);

($hook = get_extensions('topic_before_header')) ? eval($hook) : null;

$csrf_token = generate_csrf_token();
$page_title = array($panther_config['o_board_title'], $cur_topic['forum_name'], $cur_topic['subject']);
define('PANTHER_ALLOW_INDEX', 1);
define('PANTHER_ACTIVE_PAGE', 'index');
require PANTHER_ROOT.'header.php';

require PANTHER_ROOT.'include/parser.php';
$post_count = 0; // Keep track of post numbers

$data = array(
	':id'	=>	$id,
);

// Retrieve a list of post IDs, LIMIT is (really) expensive so we only fetch the IDs here then later fetch the remaining data
$ps = $db->select('posts', 'id', $data, 'topic_id=:id AND approved=1 AND deleted=0', 'id LIMIT '.$start_from.','.$panther_user['disp_posts']);

$post_ids = array();
$ps->setFetchMode(PDO::FETCH_COLUMN, 0);
foreach ($ps as $cur_post_id)
{
	$post_ids[] = $cur_post_id;
	$placeholders[] = '?';
}

$content = array();
if (empty($post_ids))
	error_handler(E_ERROR, 'The post table and topic table are out of sync', __FILE__, __LINE__);

($hook = get_extensions('viewtopic_before_display')) ? eval($hook) : null;

$render = array (
	'cur_topic' => $cur_topic,
	'lang_topic' => $lang_topic,
	'panther_user' => $panther_user,
	'is_admmod' => $is_admmod,
	'lang_post' => ($quickpost) ? $lang_post : array(),
	'reply_link' => panther_link($panther_url['new_reply'], array($id)),
	'csrf_token' => $csrf_token,
	'id' => $id,
	'index_link' => panther_link($panther_url['index']),
	'lang_common' => $lang_common,
	'forum_link' => panther_link($panther_url['forum'], array($cur_topic['forum_id'], $url_forum)),
	'topic_link' => panther_link($panther_url['topic'], array($id, $url_subject)),
	'pagination' => paginate($num_pages, $p, $panther_url['topic_paginate'], array($id, $url_subject)),
	'subscription_link' => $subscription,
	'panther_config' => $panther_config,
	'quickpost' => $quickpost,
);

// Retrieve the posts (and their respective poster/online status)
if ($cur_topic['question'] != '')
{
	require PANTHER_ROOT.'lang/'.$panther_user['language'].'/poll.php';	

	// Make sure stuff is declared properly
	$options = ($cur_topic['options'] != '') ? unserialize($cur_topic['options']) : array();
	$voters = ($cur_topic['voters'] != '') ? unserialize($cur_topic['voters']) : array();
	$votes = ($cur_topic['votes'] != '') ? unserialize($cur_topic['votes']) : array();
	$total_votes = count($voters);
	$total = $percent = 0;

	$poll_actions = array();
	if ($panther_user['username'] == $cur_topic['poster'] || $is_admmod)
	{
		$poll_actions[] = array('href' => panther_link($panther_url['poll_delete'], array($id)), 'class' => 'delete', 'lang' => $lang_topic['Delete']);
		$poll_actions[] = array('href' => panther_link($panther_url['poll_edit'], array($id)), 'class' => 'edit', 'lang' => $lang_topic['Edit']);

		if ($is_admmod)
			$poll_actions[] = array('href' => panther_link($panther_url['poll_reset'], array($id)), 'class' => 'edit', 'lang' => $lang_poll['Reset']);
	}

	// Check and make sure we can vote
	$can_vote = (!$panther_user['is_guest'] && !in_array($panther_user['id'], $voters)) ? true : false;

	// Grab the total amount of percent
	for ($i = 0; $i < count($options); $i++)
		$total += (isset($votes[$i])) ? $votes[$i] : 0;

	$render['can_vote'] = $can_vote;
	$render['poll_actions'] = $poll_actions;
	if ($can_vote)
	{
		$render['options'] = $options;
		$render['poll_action'] = panther_link($panther_url['poll_vote']);
	}
	else
	{
		foreach ($options as $key => $value)
		{
			// Prevent division by zero
			if (isset($votes[$key]) && $total != 0)
			{
				$percent =  ($votes[$key] * 100) / $total;
				$percent = floor($percent);
			}
			else
				$percent = 0;

			$percent_int = (isset($votes[$key]) && $percent != 0) ? $percent : 1;
			$percent_vote = (isset($votes[$key])) ? array($percent, $votes[$key]) : array(0, 0);
			$render['options'][$key] = array(
				'value' => $value,
				'percent' => $percent_int,
				'vote' => $percent_vote,
			);
		}

		$render['total_voters'] = $total_votes;
	}
}

($hook = get_extensions('topic_after_poll')) ? eval($hook) : null;

$results = array();
$download = false;
if ($panther_user['is_admin'])
	$download = true;
else if ($cur_topic['download'] != 0)
	$download = true;

if ($download)
{
	$ps = $db->run('SELECT id, filename, post_id, size, downloads FROM '.$db->prefix.'attachments WHERE post_id IN ('.implode(',', $placeholders).') ORDER BY post_id', $post_ids);
	foreach ($ps as $cur_attach)
	{
		if (!isset($results[$cur_attach['post_id']]))
			$results[$cur_attach['post_id']] = array();

		$results[$cur_attach['post_id']][$cur_attach['id']] = $cur_attach;
	}
}

$ps = $db->run('SELECT u.email, u.use_gravatar, u.title, u.url, u.location, u.signature, u.email_setting, u.num_posts, u.registered, u.admin_note, u.reputation AS poster_reputation, p.id, p.reputation, p.poster AS username, p.poster_id, p.poster_ip, p.poster_email, p.message, p.hide_smilies, p.posted, p.edited, p.edit_reason, p.edited_by, g.g_id, g.g_user_title, g_image, g.g_promote_next_group, o.user_id AS is_online FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'users AS u ON u.id=p.poster_id INNER JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id LEFT JOIN '.$db->prefix.'online AS o ON (o.user_id=u.id AND o.user_id!=1 AND o.idle=0) WHERE p.id IN ('.implode(',', $placeholders).') ORDER BY p.id', $post_ids);
foreach ($ps as $cur_post)
{
	$user_avatar = '';
	$user_info = array();
	$user_contacts = array();
	$post_actions = array();
	$actions = array();
	$signature = '';

	// If the poster is a registered user
	if ($cur_post['poster_id'] > 1)
	{
		$username = colourize_group($cur_post['username'], $cur_post['g_id'], $cur_post['poster_id']);
		$user_title = get_title($cur_post);

		if ($panther_config['o_censoring'] == '1')
			$user_title = censor_words($user_title);

		if ($panther_config['o_avatars'] == '1' && $panther_user['show_avatars'] != '0')
			$user_avatar = generate_avatar_markup($cur_post['poster_id'], $cur_post['email'], $cur_post['use_gravatar']);

		// We only show location, register date, post count and the contact links if "Show user info" is enabled
		if ($panther_config['o_show_user_info'] == '1')
		{
			if ($cur_post['location'] != '')
			{
				if ($panther_config['o_censoring'] == '1')
					$cur_post['location'] = censor_words($cur_post['location']);

				$user_info[] = array('title' => $lang_topic['From'], 'value' => $cur_post['location']);
			}

			$user_info[] = array('title' => $lang_topic['Registered'], 'value' => format_time($cur_post['registered'], true));

			if ($panther_config['o_show_post_count'] == '1' || $panther_user['is_admmod'])
				$user_info[] = array('title' => $lang_topic['Posts'], 'value' => forum_number_format($cur_post['num_posts']));

			// Now let's deal with the contact links (Email and URL)
			if ((($cur_post['email_setting'] == '0' && !$panther_user['is_guest']) || $panther_user['is_admmod']) && $panther_user['g_send_email'] == '1')
				$user_contacts[] = array('class' => 'email', 'href' => 'mailto:'.$cur_post['email'], 'title' => $lang_common['Email']);
			else if ($cur_post['email_setting'] == '1' && !$panther_user['is_guest'] && $panther_user['g_send_email'] == '1')
				$user_contacts[] = array('class' => 'email', 'href' => panther_link($panther_url['email'], array($cur_post['poster_id'])), 'title' => $lang_common['Email']);

			if ($cur_post['url'] != '')
			{
				if ($panther_config['o_censoring'] == '1')
					$cur_post['url'] = censor_words($cur_post['url']);

				$user_contacts[] = array('class' => 'website', 'href' => $cur_post['url'], 'rel' => 'nofollow', 'title' => $lang_topic['Website']);
			}
		}

		if ($panther_user['is_admin'] || ($panther_user['g_moderator'] == '1' && $panther_user['g_mod_promote_users'] == '1'))
		{
			if ($cur_post['g_promote_next_group'])
				$user_info[] = array('title' => $lang_topic['Promote user'], 'href' => panther_link($panther_url['profile_promote'], array($cur_post['poster_id'], $cur_post['id'], $csrf_token)));
		}

		if ($panther_user['is_admmod'])
		{
			$user_info[] = array('title' => $lang_topic['IP address logged'], 'href' => panther_link($panther_url['get_host'], array($cur_post['id'])), 'label' => $cur_post['poster_ip']);

			if ($cur_post['admin_note'] != '')
				$user_info[] = array('title' => $lang_topic['Note'], 'value' => $cur_post['admin_note']);
		}
		
		if ($panther_config['o_reputation'] == '1')
		{
			switch(true)
			{
				case $cur_post['poster_id'] == 1:
					$type = 'zero';
				break;
				case $cur_post['poster_reputation'] > '0':
					$type = 'positive';
				break;
				case $cur_post['poster_reputation'] < '0':
					$type = 'negative';
				break;
				default:
					$type = 'zero';
				break;
			}

			$cur_post['poster_reputation'] = array('type' => $type, 'title' => sprintf($lang_topic['reputation'], forum_number_format($cur_post['poster_reputation'])));
			if ($cur_topic['use_reputation'] == '1')
			{
				switch(true)
				{
					case $cur_post['reputation'] > '0':
						$type = 'positive';
					break;
					case $cur_post['reputation'] < '0':
						$type = 'negative';
					break;
					default:
						$type = 'zero';
					break;
				}

				if ($cur_post['poster_id'] != 1)
				{
					if ($cur_post['poster_id'] != $panther_user['id'] && $panther_user['g_rep_enabled'] == '1' && $cur_topic['archived'] !== '1' && ($cur_topic['closed'] == '0' || $is_admmod) && $cur_topic['archived'] != '1')
					{
						$actions = array();
						if ($panther_config['o_rep_type'] != 3)
							$actions[] = array('class' => 'voterep votemore', 'src' => 'plus.png', 'onclick' => 1);

						if ($panther_config['o_rep_type'] != 2)
							$actions[] = array('class' => 'voterep voteless', 'src' => 'minus.png', 'onclick' => -1);

						if (count($actions) > 0)
							$post_actions[] = array('class' => 'helpful', 'actions' => true, 'title' => (($cur_topic['first_post_id'] == $cur_post['id']) ? $lang_topic['topic helpful'] : $lang_topic['post helpful']));
					}
					$post_actions[] = array('class' => 'rep', 'span_id' => 'post_rep_'.$cur_post['id'], 'span_class' => 'reputation '.$type, 'title' => $cur_post['reputation']);
				}
			}
		}
	}
	// If the poster is a guest (or a user that has been deleted)
	else
	{
		$username = colourize_group($cur_post['username'], $cur_post['g_id']);
		$user_title = get_title($cur_post);

		if ($panther_user['is_admmod'])
			$user_info[] = array('title' => $lang_topic['IP address logged'], 'href' => panther_link($panther_url['get_host'], array($cur_post['id'])), 'label' => $cur_post['poster_ip']);

		if ($panther_config['o_show_user_info'] == '1' && $cur_post['poster_email'] != '' && !$panther_user['is_guest'] && $panther_user['g_send_email'] == '1')
			$user_contacts[] = array('class' => 'email', 'href' => 'mailto:'.$cur_post['poster_email'], 'title' => $lang_common['Email']);
	}
	
	if ($cur_post['g_image'] != '')
	{
		$image_path = ($panther_config['o_image_group_dir'] != '') ? $panther_config['o_image_group_path'] : PANTHER_ROOT.$panther_config['o_image_group_path'].'/';
		$image_dir = ($panther_config['o_image_group_dir'] != '') ? $panther_config['o_image_group_dir'] : get_base_url().'/'.$panther_config['o_image_group_path'].'/';
		$img_size = getimagesize($image_path.$cur_post['g_id'].'.'.$cur_post['g_image']);
		$group_image = array('src' => $image_dir.$cur_post['g_id'].'.'.$cur_post['g_image'], 'size' => $img_size[3], 'alt' => $cur_post['g_user_title']);
	}
	else
		$group_image = array();

	// Generation post action array (quote, edit, delete etc.)
	if ($cur_topic['archived'] != '1')
	{
		if (!$is_admmod)
		{
			if (!$panther_user['is_guest'] && $cur_topic['archived'] == '0')
				$post_actions[] = array('class' => 'report', 'href' => panther_link($panther_url['report'], array($cur_post['id'])), 'title' => $lang_topic['Report']);

			if ($cur_topic['closed'] == '0' && $cur_topic['archived'] == '0')
			{
				if ($cur_post['poster_id'] == $panther_user['id'] && ($panther_user['g_deledit_interval'] == '0' || time() - $cur_post['posted'] < $panther_user['g_deledit_interval']))
				{
					if ((($start_from + $post_count) == 1 && $panther_user['g_delete_topics'] == '1') || (($start_from + $post_count) > 1 && $panther_user['g_delete_posts'] == '1'))
						$post_actions[] = array('class' => 'delete', 'href' => panther_link($panther_url['delete'], array($cur_post['id'])), 'title' => $lang_topic['Delete']);
					if ($panther_user['g_edit_posts'] == '1')
						$post_actions[] = array('class' => 'edit', 'href' => panther_link($panther_url['edit'], array($cur_post['id'])), 'title' => $lang_topic['Edit']);
				}

				if (($cur_topic['post_replies'] == '' && $panther_user['g_post_replies'] == '1') || $cur_topic['post_replies'] == '1')
					$post_actions[] = array('class' => 'quote', 'href' => panther_link($panther_url['quote'], array($id, $cur_post['id'])), 'title' => $lang_topic['Quote']);
			}
		}
		else
		{
			$post_actions[] = array('class' => 'report', 'href' => panther_link($panther_url['report'], array($cur_post['id'])), 'title' => $lang_topic['Report']);
			if ($panther_user['is_admin'] || $panther_user['g_mod_edit_admin_posts'] == '1' || !in_array($cur_post['poster_id'], $admin_ids))
			{
				if ($panther_config['o_warnings'] == '1' && (!in_array($cur_post['poster_id'], $admin_ids)) && $panther_user['id'] != $cur_post['poster_id'] && $cur_post['poster_id'] > 1 && ($panther_user['is_admin'] || $panther_user['g_mod_warn_users'] == '1'))
					$post_actions[] = array('class' => 'delete', 'href' => panther_link($panther_url['warn_pid'], array($cur_post['poster_id'], $cur_post['id'])), 'title' => $lang_topic['Warn']);

				$post_actions[] = array('class' => 'delete', 'href' => panther_link($panther_url['unapprove'], array($cur_topic['forum_id'], $cur_post['id'], $csrf_token)), 'title' => $lang_topic['Unapprove']); 
				$post_actions[] = array('class' => 'delete', 'href' => panther_link($panther_url['delete'], array($cur_post['id'])), 'title' => $lang_topic['Delete']);
				$post_actions[] = array('class' => 'edit', 'href' => panther_link($panther_url['edit'], array($cur_post['id'])), 'title' => $lang_topic['Edit']);
			}
			$post_actions[] = array('class' => 'quote', 'href' => panther_link($panther_url['quote'], array($id, $cur_post['id'])), 'title' => $lang_topic['Quote']);
		}
	}

	// Perform the main parsing of the message (BBCode, smilies, censor words etc)
	$cur_post['message'] = $parser->parse_message($cur_post['message'], $cur_post['hide_smilies']);

	// Do signature parsing/caching
	if ($panther_config['o_signatures'] == '1' && $cur_post['signature'] != '' && $panther_user['show_sig'] != '0')
	{
		if (isset($signature_cache[$cur_post['poster_id']]))
			$signature = $signature_cache[$cur_post['poster_id']];
		else
		{
			$signature = $parser->parse_signature($cur_post['signature']);
			$signature_cache[$cur_post['poster_id']] = $signature;
		}
	}
	else
		$signature = '';

	$attachments = array();
	if ($download && isset($results[$cur_post['id']]) && count($results[$cur_post['id']]) > 0)
	{
		foreach ($results[$cur_post['id']] as $cur_attach)
			$attachments[] = array('icon' => attach_icon(attach_get_extension($cur_attach['filename'])), 'link' => panther_link($panther_url['attachment'], array($cur_attach['id'])), 'name' => $cur_attach['filename'], 'size' => sprintf($lang_topic['Attachment size'], file_size($cur_attach['size'])), 'downloads' => sprintf($lang_topic['Attachment downloads'], forum_number_format($cur_attach['downloads'])));
	}

	$posts[] = array(
		'id' => $cur_post['id'],
		'count' => $post_count++,
		'number' => ($start_from + $post_count),
		'link' => panther_link($panther_url['post'], array($cur_post['id'])),
		'posted' => format_time($cur_post['posted']),
		'username' => $username,
		'user_title' => $user_title,
		'poster_id' => $cur_post['poster_id'],
		'poster_reputation' => $cur_post['poster_reputation'],
		'user_avatar' => $user_avatar,
		'group_image' => $group_image,
		'edited' => $cur_post['edited'] ? format_time($cur_post['edited']) : '',
		'edited_by' => $cur_post['edited_by'],
		'edit_reason' => $cur_post['edit_reason'],
		'attachments' => $attachments,
		'message' => $cur_post['message'],
		'signature' => $signature,
		'is_online' => $cur_post['is_online'],
		'user_info' => $user_info,
		'user_contacts' => $user_contacts,
		'group_image' => $group_image,
		'post_actions' => $post_actions,
		'actions' => $actions,
	);
}

$render['posts'] = $posts;
if ($cur_topic['parent'])
	$render['parent_link'] = panther_link($panther_url['forum'], array($cur_topic['parent_forum'], url_friendly($cur_topic['parent'])));

($hook = get_extensions('topic_before_users_online')) ? eval($hook) : null;

if ($panther_config['o_users_online'] == '1')
{
	require PANTHER_ROOT.'lang/'.$panther_user['language'].'/online.php';
	$guests_in_topic = $users = array();

	$online = $db->run('SELECT o.user_id, o.ident, o.currently, o.logged, u.group_id FROM '.$db->prefix.'online AS o INNER JOIN '.$db->prefix.'users AS u ON u.id=o.user_id WHERE o.currently LIKE \'%viewtopic.php%\' AND o.idle = 0');
	foreach ($online as $user_online)
	{
		if (strpos($user_online['currently'], '&p=')!== false)
		{
			preg_match('~&p=(.*)~', $user_online['currently'], $replace);
			$user_online['currently'] = str_replace($replace[0], '', $user_online['currently']);
		}
		$tid = filter_var($user_online['currently'], FILTER_SANITIZE_NUMBER_INT);
		if (strpos($user_online['currently'], '?pid') !== false)
		{
			if (in_array($tid, $post_ids))
			{
				if ($user_online['user_id'] == 1)
					$guests_in_topic[] = $user_online['ident'];
				else
					$users[] = colourize_group($user_online['ident'], $user_online['group_id'], $user_online['user_id']);
			}
		}
		elseif (strpos($user_online['currently'], '?id') !== false)
		{
			if ($tid == $id)
			{
				if ($user_online['user_id'] == 1)
					$guests_in_topic[] = $user_online['ident'];
				else
					$users[] = colourize_group($user_online['ident'], $user_online['group_id'], $user_online['user_id']);
			}
		}	 
	}

	$render['guests'] = count($guests_in_topic);
	$render['users'] = (count($users) > 0) ? implode(', ', $users) : $lang_online['no users'];
	$render['lang_online'] = $lang_online;
}

// Display quick post if enabled
if ($quickpost)
{
	$render['quickpost_links'] = array(
		'form_action' => panther_link($panther_url['new_reply'], array($id)),
		'csrf_token' => generate_csrf_token('post.php'),
		'bbcode' => panther_link($panther_url['help'], array('bbcode')),
		'url' => panther_link($panther_url['help'], array('url')),
		'img' => panther_link($panther_url['help'], array('img')),
		'smilies' => panther_link($panther_url['help'], array('smilies')),
	);
}

// Increment "num_views" for topic
if ($panther_config['o_topic_views'] == '1')
	$db->run('UPDATE '.$db->prefix.'topics SET num_views=num_views+1 WHERE id=:id', array($id));

$tpl = load_template('topic.tpl');
echo $tpl->render($render);

($hook = get_extensions('topic_after_display')) ? eval($hook) : null;

$forum_id = $cur_topic['forum_id'];
$footer_style = 'viewtopic';
require PANTHER_ROOT.'footer.php';