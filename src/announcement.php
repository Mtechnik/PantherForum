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

require PANTHER_ROOT.'include/parser.php';

if ($panther_user['g_read_board'] == '0')
	message($lang_common['No view'], false, '403 Forbidden');

$fid = isset($_GET['fid']) && $_GET['fid'] > 0 ? intval($_GET['fid']) : key($panther_forums);
$id = isset($_GET['id']) ? intval($_GET['id']) : '0';
if ($id < 1)
	message($lang_common['Bad request'], false, '404 Not Found');

require PANTHER_ROOT.'lang/'.$panther_user['language'].'/topic.php';

if (!file_exists(FORUM_CACHE_DIR.'cache_perms.php'))
{
    if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
	    require PANTHER_ROOT.'include/cache.php';

	generate_perms_cache();
}

require FORUM_CACHE_DIR.'cache_perms.php';
if (!isset($perms[$panther_user['g_id'].'_'.$fid]))
	$perms[$panther_user['g_id'].'_'.$fid] = $perms['_'];

if ($perms[$panther_user['g_id'].'_'.$fid]['read_forum'] == '0')
	message($lang_common['No permission']);

($hook = get_extensions('announcement_before_query')) ? eval($hook) : null;
$data = array(
	':id'	=>	$id,
);

$ps = $db->select('announcements', 'forum_id', $data, 'id=:id');
$afid = $ps->fetchColumn();

$data = array(
	':id'	=>	$id,
);

if ($afid == 0)
{
	$data[':fid'] = $fid;
	$ps = $db->run('SELECT a.subject, a.forum_id, g.g_image, g.g_user_title, g.g_id, a.user_id, a.message, u.email_setting, u.email, u.use_gravatar, u.group_id, u.num_posts, u.username, u.title, u.url, u.location, u.registered, f.forum_name, f.parent_forum, u.reputation, f.id AS fid, f.password, pf.forum_name AS parent FROM '.$db->prefix.'announcements AS a INNER JOIN '.$db->prefix.'users AS u ON u.id=a.user_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=:fid INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id INNER JOIN '.$db->prefix.'posts AS p ON p.poster_id=a.user_id LEFT JOIN '.$db->prefix.'forums AS pf ON f.parent_forum=pf.id WHERE a.id=:id', $data);
}
else
	$ps = $db->run('SELECT a.subject, a.forum_id, g.g_image, g.g_user_title, g.g_id, a.user_id, a.message, u.email_setting, u.email, u.use_gravatar, u.group_id, u.num_posts, u.username, u.title, u.url, u.location, u.registered, f.forum_name, f.parent_forum, u.reputation, f.id AS fid, f.password, pf.forum_name AS parent FROM '.$db->prefix.'announcements AS a INNER JOIN '.$db->prefix.'users AS u ON u.id=a.user_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=a.forum_id INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id INNER JOIN '.$db->prefix.'posts AS p ON p.poster_id=a.user_id LEFT JOIN '.$db->prefix.'forums AS pf ON f.parent_forum=pf.id WHERE a.id=:id', $data);

if (!$ps->rowCount())
	message($lang_common['Bad request'], false, '404 Not Found');

$cur_announcement = $ps->fetch();

if ($cur_announcement['password'] != '')
	check_forum_login_cookie($cur_announcement['fid'], $cur_announcement['password']);

$user_avatar = '';
$user_info = $user_contacts = $post_actions = array();
if ($panther_user['is_admmod'] == '1' && $panther_user['g_mod_cp'] == '1' || $panther_user['is_admin'])
{
	$post_actions[] = array('class' => 'delete', 'href' => panther_link($panther_url['delete_announcement'], array($id)), 'title' => $lang_topic['Delete']);
	$post_actions[] = array('class' => 'edit', 'href' => panther_link($panther_url['edit_announcement'], array($id)), 'title' => $lang_topic['Edit']);
}

$cur_announcement['user_title'] = get_title($cur_announcement);
if ($panther_config['o_censoring'] == '1')
	$cur_announcement['user_title'] = censor_words($cur_announcement['user_title']);

if ($panther_config['o_avatars'] == '1' && $panther_user['show_avatars'] != '0')
	$user_avatar = generate_avatar_markup($cur_announcement['user_id'], $cur_announcement['email'], $cur_announcement['use_gravatar']);

// We only show location, register date, post count and the contact links if "Show user info" is enabled
if ($panther_config['o_show_user_info'] == '1')
{
	if ($cur_announcement['location'] != '')
	{
		if ($panther_config['o_censoring'] == '1')
			$cur_announcement['location'] = censor_words($cur_announcement['location']);

		$user_info[] = array('title' => $lang_topic['From'], 'value' => $cur_announcement['location']);
	}

	$user_info[] = array('title' => $lang_topic['Registered'], 'value' => format_time($cur_announcement['registered'], true));

	if ($panther_config['o_show_post_count'] == '1' || $panther_user['is_admmod'])
		$user_info[] = array('title' => $lang_topic['Posts'], 'value' => forum_number_format($cur_announcement['num_posts']));

	// Now let's deal with the contact links (Email and URL)
	if ((($cur_announcement['email_setting'] == '0' && !$panther_user['is_guest']) || $panther_user['is_admmod']) && $panther_user['g_send_email'] == '1')
		$user_contacts[] = array('class' => 'email', 'href' => 'mailto:'.$cur_announcement['email'], 'title' => $lang_common['Email']);
	else if ($cur_announcement['email_setting'] == '1' && !$panther_user['is_guest'] && $panther_user['g_send_email'] == '1')
		$user_contacts[] = array('class' => 'email', 'href' => panther_link($panther_url['email'], array($cur_announcement['user_id'])), 'title' => $lang_common['Email']);

	if ($cur_announcement['url'] != '')
	{
		if ($panther_config['o_censoring'] == '1')
			$cur_announcement['url'] = censor_words($cur_announcement['url']);

		$user_contacts[] = array('class' => 'website', 'href' => $cur_announcement['url'], 'rel' => 'nofollow', 'title' => $lang_topic['Website']);
	}
}

if ($panther_config['o_reputation'] == '1')
{
	switch(true)
	{
		case $cur_announcement['reputation'] > '0':
			$type = 'positive';
		break;
		case $cur_announcement['reputation'] < '0':
			$type = 'negative';
		break;
		default:
			$type = 'zero';
		break;
	}

	$cur_announcement['reputation'] = array('type' => $type, 'title' => sprintf($lang_topic['reputation'], forum_number_format($cur_announcement['reputation'])));
}

if ($cur_announcement['g_image'] != '')
{
	$image_dir = ($panther_config['o_image_group_dir'] != '') ? $panther_config['o_image_group_dir'] : get_base_url().'/'.$panther_config['o_image_group_path'].'/';
	$img_size = @getimagesize($panther_config['o_image_group_path'].'/'.$cur_announcement['group_id'].'.'.$cur_announcement['g_image']);
	$group_image = array('src' => $image_dir.$cur_announcement['group_id'].'.'.$cur_announcement['g_image'], 'size' => $img_size[3], 'alt' => $cur_announcement['g_user_title']);
}
else
	$group_image = array();

$announcement_type = (($afid != '0') ? 'announcement_fid' : 'announcement');
($hook = get_extensions('announcement_before_header')) ? eval($hook) : null;

$page_title = array($panther_config['o_board_title'], $cur_announcement['forum_name'], $cur_announcement['subject']);
define('PANTHER_ACTIVE_PAGE', 'index');
require PANTHER_ROOT.'header.php';

$render = array(
	'index_link' => panther_link($panther_url['index']),
	'lang_common' => $lang_common,
	'forum_link' => panther_link($panther_url['forum'], array($cur_announcement['fid'], url_friendly($cur_announcement['forum_name']))),
	'announce_link' => panther_link($panther_url[$announcement_type], array($id, $cur_announcement['fid'], url_friendly($cur_announcement['subject']))),
	'cur_announcement' => $cur_announcement,
	'username' => colourize_group($cur_announcement['username'], $cur_announcement['group_id'], $cur_announcement['user_id']),
	'user_title' => get_title($cur_announcement),
	'user_avatar' => $user_avatar,
	'message' => $parser->parse_message($cur_announcement['message'], 0),
	'panther_config' => $panther_config,
	'post_actions' => $post_actions,
	'user_info' => $user_info,
	'user_contacts' => $user_contacts,
	'group_image' => $group_image,
);

if ($cur_announcement['parent'])
	$render['parent_link'] = panther_link($panther_url['forum'], array($cur_announcement['parent_forum'], url_friendly($cur_announcement['parent'])));

$tpl = load_template('announcement.tpl');
echo $tpl->render ($render);

($hook = get_extensions('announcement_after_display')) ? eval($hook) : null;

require PANTHER_ROOT.'footer.php';