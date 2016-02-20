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

if ($panther_user['is_bot'])
	message($lang_common['No permission']);

if ($panther_user['g_read_board'] == '0')
	message($lang_common['No view'], false, '403 Forbidden');
else if ($panther_user['g_view_users'] == '0')
	message($lang_common['No permission'], false, '403 Forbidden');

// Load language files
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/userlist.php';
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/search.php';
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/online.php';

// Determine if we are allowed to view post counts
$show_post_count = ($panther_config['o_show_post_count'] == '1' || $panther_user['is_admmod']) ? true : false;

$username = isset($_GET['username']) && $panther_user['g_search_users'] == '1' ? panther_trim($_GET['username']) : '';
$show_group = isset($_GET['show_group']) ? intval($_GET['show_group']) : -1;
$sort_by = isset($_GET['sort_by']) && (in_array($_GET['sort_by'], array('username', 'registered')) || ($_GET['sort_by'] == 'num_posts' && $show_post_count)) ? $_GET['sort_by'] : 'username';
$sort_dir = isset($_GET['sort_dir']) && $_GET['sort_dir'] == 'DESC' ? 'DESC' : 'ASC';

// Create any applicable SQL generated from the GET array
$data = array(
	':unverified'	=>	PANTHER_UNVERIFIED,
);

$fields = array();
$sql = 'SELECT COUNT(id) FROM '.$db->prefix.'users AS u WHERE u.id > 1 AND u.group_id != :unverified';
$sql1 = 'SELECT u.id, u.username, u.title, u.num_posts, u.registered, u.email, u.use_gravatar, u.group_id AS g_id, g.g_user_title, o.user_id AS is_online FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id LEFT JOIN '.$db->prefix.'online AS o ON (o.user_id=u.id AND o.user_id!=1) WHERE u.id>1 AND u.group_id!=:unverified';

if ($username != '')
{
	$fields['username'] = ' AND u.username LIKE :username';
	$data[':username'] = str_replace('*', '%', $username);
}

if ($show_group > -1)
{
	$fields['gid'] = ' AND u.group_id = :gid';
	$data[':gid'] = $show_group;
}

foreach($fields as $field => $where_cond)
{
	$sql .= $where_cond;
	$sql1 .= $where_cond;
}

// Fetch user count
$ps = $db->run($sql, $data);
$num_users = $ps->fetchColumn();

// Determine the user offset (based on $_GET['p'])
$num_pages = ceil($num_users / 50);

$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);
$start_from = 50 * ($p - 1);

$data[':start'] = $start_from;
$sql1 .= " ORDER BY ".$sort_by." ".$sort_dir.", u.id ASC LIMIT :start, 50";

$page_title = array($panther_config['o_board_title'], $lang_common['User list']);
if ($panther_user['g_search_users'] == '1')
	$focus_element = array('userlist', 'username');

($hook = get_extensions('userlist_before_header')) ? eval($hook) : null;

define('PANTHER_ALLOW_INDEX', 1);
define('PANTHER_ACTIVE_PAGE', 'userlist');
require PANTHER_ROOT.'header.php';

$users = array();
$ps = $db->run($sql1, $data);
if ($ps->rowCount())
{
	foreach ($ps as $user_data)
	{
		$users[] = array(
			'avatar' => generate_avatar_markup($user_data['id'], $user_data['email'], $user_data['use_gravatar'], array(32, 32)),
			'is_online' => ($user_data['is_online'] == $user_data['id']) ? true : false,
			'username' => colourize_group($user_data['username'], $user_data['g_id'], $user_data['id']),
			'title' => get_title($user_data),
			'num_posts' => forum_number_format($user_data['num_posts']),
			'registered' => format_time($user_data['registered'], true),
		);
	}
}

$tpl = load_template('userlist.tpl');
echo $tpl->render(
	array(
		'lang_search' => $lang_search,
		'lang_ul' => $lang_ul,
		'lang_common' => $lang_common,
		'lang_online' => $lang_online,
		'panther_groups' => $panther_groups,
		'show_post_count' => $show_post_count,
		'userlist_link' => panther_link($panther_url['userlist']),
		'panther_user' => $panther_user,
		'username' => $username,
		'show_group' => $show_group,
		'sort_by' => $sort_by,
		'sort_dir' => $sort_dir,
		'pagination' => paginate($num_pages, $p, $panther_url['userlist_result'], array(urlencode($username), $show_group, $sort_by, $sort_dir)),
		'users' => $users,
		'panther_config' => $panther_config,
	)
);

($hook = get_extensions('userlist_after_output')) ? eval($hook) : null;

require PANTHER_ROOT.'footer.php';