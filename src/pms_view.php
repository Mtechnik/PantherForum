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

// Tell header.php we should use the editor
define('POSTING', 1);

if ($panther_user['g_read_board'] == '0')
	message($lang_common['No view'], false, '403 Forbidden');

if ($panther_user['is_guest'])
	message($lang_common['No permission']);

if ($panther_config['o_private_messaging'] == '0')
	message($lang_common['No permission']);

require PANTHER_ROOT.'lang/'.$panther_user['language'].'/pms.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$tid = isset($_GET['tid']) ? intval($_GET['tid']) : 0;
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

if ($tid < 1 && $pid < 1)
	message($lang_common['Bad request'], false, '404 Not Found');

if ($pid)
{
	$data = array(
		':id'	=>	$pid,
	);

	$ps = $db->select('messages', 'topic_id, posted', $data, 'id=:id');
	if (!$ps->rowCount())
		message($lang_common['Bad request'], false, '404 Not Found');

	list($tid, $posted) = $ps->fetch(PDO::FETCH_NUM);

	$data = array(
		':id'	=>	$tid,
		':posted'	=>	$posted,
	);

	// Determine on which page the post is located (depending on $panther_user['disp_posts'])
	$ps = $db->select('messages', 'COUNT(id)', $data, 'topic_id=:id AND posted<:posted');
	$num_posts = $ps->fetchColumn() + 1;

	$_GET['p'] = ceil($num_posts / $panther_user['disp_posts']);
}
else
{
	// If action=new, we redirect to the first new post (if any)
	if ($action == 'new')
	{
		$data = array(
			':uid'	=>	$panther_user['id'],
			':tid'	=>	$tid,
			':last_visit'	=>	$panther_user['last_visit'],
		);

		$ps = $db->select('messages', 'MIN(id)', $data, 'poster_id!=:uid AND topic_id=:tid AND posted>:last_visit');
		$first_new_post_id = $ps->fetchColumn();

		
		if ($first_new_post_id)
		{
			header('Location: '.panther_link($panther_url['pms_post'], array($first_new_post_id)));
			exit;
		}

		$action = 'last';
	}
	
	// If action=last, we redirect to the last post
	if ($action == 'last')
	{
		$data = array(
			':id'	=>	$tid,
		);

		$ps = $db->select('messages', 'MAX(id)', $data, 'topic_id=:id');
		$last_post_id = $ps->fetchColumn();

		if ($last_post_id)
		{
			header('Location: '.panther_link($panther_url['pms_post'], array($last_post_id)));
			exit;
		}
	}
}

($hook = get_extensions('pms_topic_after_pagination')) ? eval($hook) : null;

$data = array(
	':uid'	=>	$panther_user['id'],
	':tid'	=>	$tid,
);

$ps = $db->run('SELECT c.subject, c.num_replies, f.name, f.id AS fid, cd.viewed FROM '.$db->prefix.'conversations AS c INNER JOIN '.$db->prefix.'pms_data AS cd ON c.id=cd.topic_id INNER JOIN '.$db->prefix.'folders AS f ON cd.folder_id=f.id WHERE c.id=:tid AND cd.user_id=:uid', $data);

if (!$ps->rowCount())
	message($lang_common['Bad request'], false, '404 Not Found');

$cur_topic = $ps->fetch();

$data = array(
	':tid'	=>	$tid,
	':uid'	=>	$panther_user['id'],
);

$ps = $db->select('pms_data', 1, $data, 'topic_id=:tid AND user_id=:uid AND deleted=1');
if ($ps->rowCount())	// Why are we still trying to view this if we've deleted it?
	message($lang_common['Bad request']);

$quickpost = false;
if ($panther_config['o_quickpost'] == '1' && $cur_topic['fid'] != '3')
{
	$quickpost = true;
	$required_fields = array('req_message' => $lang_common['Message']);
}

if ($cur_topic['viewed'] == '0')
{
	$update = array(
		'viewed'	=>	1,
	);

	$data = array(
		':tid'	=>	$tid,
		':uid'	=>	$panther_user['id'],
	);
	
	$db->update('pms_data', $update, 'topic_id=:tid AND user_id=:uid', $data);
}

require PANTHER_ROOT.'lang/'.$panther_user['language'].'/topic.php';

// Determine the post offset (based on $_GET['p'])
$num_pages = ceil(($cur_topic['num_replies'] + 1) / $panther_user['disp_posts']);

$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);
$start_from = $panther_user['disp_posts'] * ($p - 1);

if ($panther_config['o_censoring'] == '1')
	$cur_topic['subject'] = censor_words($cur_topic['subject']);

require PANTHER_ROOT.'include/parser.php';

($hook = get_extensions('pms_view_before_header')) ? eval($hook) : null;

$page_title = array($panther_config['o_board_title'], $lang_common['PM'], $cur_topic['subject']);
define('PANTHER_ALLOW_INDEX', 1);
define('PANTHER_ACTIVE_PAGE', 'pm');
require PANTHER_ROOT.'header.php';

$post_count = 0; // Keep track of post numbers
$data = array(
	':tid'	=>	$tid,
	':start'	=>	$start_from,
);

// Retrieve a list of post IDs, LIMIT is (really) expensive so we only fetch the IDs here then later fetch the remaining data
$ps = $db->select('messages', 'id', $data, 'topic_id=:tid ORDER BY id LIMIT :start,'.$panther_user['disp_posts']);

$markers = $post_ids = array();
$ps->setFetchMode(PDO::FETCH_COLUMN, 0);
foreach ($ps as $cur_post_id)
{
	$post_ids[] = $cur_post_id;
	$markers[] = '?';
}

$posts = array();
$ps = $db->run('SELECT u.email, u.use_gravatar, u.title, u.url, u.location, u.signature, u.reputation, u.email_setting, u.num_posts, u.registered, u.admin_note, p.id, p.poster AS username, p.poster_id, p.message, p.hide_smilies, p.posted, p.poster_ip, p.edited, p.edited_by, g.g_id, g.g_user_title, g.g_image, o.user_id AS is_online FROM '.$db->prefix.'messages AS p LEFT JOIN '.$db->prefix.'users AS u ON u.id=p.poster_id LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id LEFT JOIN '.$db->prefix.'online AS o ON (o.user_id=u.id AND o.user_id!=1 AND o.idle=0) WHERE p.id IN ('.implode(',', $markers).') ORDER BY p.id', $post_ids);
foreach ($ps as $cur_post)
{
	$user_avatar = '';
	$user_info = array();
	$user_contacts = array();
	$post_actions = array();
	$is_online = '';
	$signature = '';
	
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

	if ($panther_user['is_admmod'])
	{
		$user_info[] = array('title' => $lang_topic['IP address logged'], 'href' => panther_link($panther_url['get_host'], array($cur_post['id'])), 'label' => $cur_post['poster_ip']);

		if ($cur_post['admin_note'] != '')
			$user_info[] = array('title' => $lang_topic['Note'], 'value' => $cur_post['admin_note']);
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

	if ($panther_config['o_reputation'] == '1')
	{
		switch(true)
		{
			case $cur_post['poster_id'] == 1:
				$type = 'zero';
			break;
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
		
		$reputation = array('type' => $type, 'title' => sprintf($lang_topic['reputation'], forum_number_format($cur_post['reputation'])));
	}
	
	
	
	if ($cur_topic['fid'] != '3') // If it's not archived then we can do stuff
	{
		if ($cur_post['poster_id'] == $panther_user['id'] || $panther_user['is_admmod'])
		{
			$post_actions[] = array('class' => 'delete', 'href' => panther_link($panther_url['pms_delete'], array($cur_post['id'])), 'title' => $lang_topic['Delete']);

			if ($panther_user['g_edit_posts'] == '1')
				$post_actions[] = array('class' => 'edit', 'href' => panther_link($panther_url['pms_edit'], array($cur_post['id'])), 'title' => $lang_topic['Edit']);

			if ($panther_user['g_post_replies'] == '1')
				$post_actions[] = array('class' => 'quote', 'href' => panther_link($panther_url['pms_quote'], array($tid, $cur_post['id'])), 'title' => $lang_topic['Quote']);
		}
	}

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

	$posts[] = array(
		'id' => $cur_post['id'],
		'link' => panther_link($panther_url['pms_post'], array($cur_post['id'])),
		'posted' => format_time($cur_post['posted']),
		'username' => colourize_group($cur_post['username'], $cur_post['g_id'], $cur_post['poster_id']),
		'user_title' => $user_title,
		'number' => ($start_from + (++$post_count)),
		'avatar' => $user_avatar,
		'poster_reputation' => $reputation,
		'message' => $parser->parse_message($cur_post['message'], $cur_post['hide_smilies']),
		'signature' => $signature,
		'edited' => $cur_post['edited'] ? format_time($cur_post['edited']) : '',
		'edited_by' => $cur_post['edited_by'],
		'post_actions' => $post_actions,
		'user_info' => $user_info,
		'group_image' => $group_image,
		'user_contacts' => $user_contacts,
		'is_online' => $cur_post['is_online'],
		'poster_id' => $cur_post['poster_id'],
	);
}

$render = array(
	'lang_common' => $lang_common,
	'lang_topic' => $lang_topic,
	'lang_pm' => $lang_pm,
	'index_link' => panther_link($panther_url['index']),
	'inbox_link' => panther_link($panther_url['inbox']),
	'cur_topic' => $cur_topic,
	'panther_config' => $panther_config,
	'panther_user' => $panther_user,
	'reply_link' => panther_link($panther_url['pms_reply'], array($tid)),
	'pm_menu' => generate_pm_menu($cur_topic['fid']),
	'pagination' => paginate($num_pages, $p, $panther_url['pms_paginate'], array($tid)),
	'posts' => $posts,
	'quickpost' => $quickpost,
	'csrf_token' => generate_csrf_token(),
);

if ($quickpost)
{
	$render['quickpost_links'] = array(
		'form_action' => panther_link($panther_url['pms_reply'], array($tid)),
		'bbcode' => panther_link($panther_url['help'], array('bbcode')),
		'url' => panther_link($panther_url['help'], array('url')),
		'img' => panther_link($panther_url['help'], array('img')),
		'smilies' => panther_link($panther_url['help'], array('smilies')),
	);
}

$tpl = load_template('pm_topic.tpl');
echo $tpl->render($render);

($hook = get_extensions('pms_topic_after_display')) ? eval($hook) : null;

require PANTHER_ROOT.'footer.php';