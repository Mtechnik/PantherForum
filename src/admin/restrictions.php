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

if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
	require PANTHER_ROOT.'include/cache.php';

require PANTHER_ROOT.'include/common_admin.php';

if (!$panther_user['is_admin'])
	message($lang_common['No permission']);

// Load the admin_restrictions.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_restrictions.php';

if ($panther_user['id'] != '2')
{
	if(!is_null($admins[$panther_user['id']]['admin_restrictions']))
	{
		if ($admins[$panther_user['id']]['admin_restrictions'] == '0')
			message($lang_common['No permission'], false, '403 Forbidden');
	}
}

check_authentication();

$action = isset($_GET['action']) ? $_GET['action'] : null;
$stage = isset($_GET['stage']) ? intval($_GET['stage']) : null;
$csrf_token = generate_csrf_token(PANTHER_ADMIN_DIR.'/restrictions.php');

if (($action == 'add' || $action == 'edit') && isset($_POST['form_sent']) && $stage == '3')
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/restrictions.php');

	//Stage 3: Add/edit restrictions
	$user = isset($_POST['admin_id']) ? intval($_POST['admin_id']) : 0;
	if ($user < 1)
		message($lang_common['Bad request']);

	$board_config = isset($_POST['board_config']) ? intval($_POST['board_config']) : '1';
	$board_perms = isset($_POST['board_perms']) ? intval($_POST['board_perms']) : '1';	
	$board_cats = isset($_POST['board_cats']) ? intval($_POST['board_cats']) : '1';
	$board_forums = isset($_POST['board_forums']) ? intval($_POST['board_forums']) : '1';	
	$board_groups = isset($_POST['board_groups']) ? intval($_POST['board_groups']) : '1';
	$board_users = isset($_POST['board_users']) ? intval($_POST['board_users']) : '1';
	$board_censoring = isset($_POST['board_censoring']) ? intval($_POST['board_censoring']) : '1';
	$board_ranks = isset($_POST['board_ranks']) ? intval($_POST['board_ranks']) : '1';
	$board_moderate = isset($_POST['board_moderate']) ? intval($_POST['board_moderate']) : '1';
	$board_maintenance = isset($_POST['board_maintenance']) ? intval($_POST['board_maintenance']) : '0';
	$board_plugins = isset($_POST['board_plugins']) ? intval($_POST['board_plugins']) : '1';
	$board_restrictions = isset($_POST['board_restrictions']) ? intval($_POST['board_restrictions']) : '0';
	$board_updates = isset($_POST['board_updates']) ? intval($_POST['board_updates']) : '0';
	$board_archive = isset($_POST['board_archive']) ? intval($_POST['board_archive']) : '1';
	$board_smilies = isset($_POST['board_smilies']) ? intval($_POST['board_smilies']) : '1';
	$board_warnings = isset($_POST['board_warnings']) ? intval($_POST['board_warnings']) : '1';
	$board_attachments = isset($_POST['board_attachments']) ? intval($_POST['board_attachments']) : '1';
	$board_robots = isset($_POST['board_robots']) ? intval($_POST['board_robots']) : '1';
	$board_addons = isset($_POST['board_addons']) ? intval($_POST['board_addons']) : '1';
	$board_tasks = isset($_POST['board_tasks']) ? intval($_POST['board_tasks']) : '1';

	$data = array(
		':id'	=>	$user,
		':admin' => PANTHER_ADMIN,
	);

	$ps = $db->run('SELECT 1 FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id WHERE u.group_id=:id OR g.g_admin=1 OR g.g_id=:admin', $data);
	if (!$ps->rowCount())
		message($lang_admin_restrictions['no user']);

	$data = array(
		':id'	=>	$user,
	);

	$restrictions = array(
		'admin_options'		=>	$board_config,
		'admin_permissions'	=>	$board_perms,
		'admin_categories'	=>	$board_cats,
		'admin_forums'		=>	$board_forums,
		'admin_groups'		=>	$board_groups,
		'admin_censoring'	=>	$board_censoring,
		'admin_maintenance'	=>	$board_maintenance,
		'admin_plugins'		=>	$board_plugins,
		'admin_restrictions'=>	$board_restrictions,
		'admin_users'		=>	$board_users,
		'admin_moderate'	=>	$board_moderate,
		'admin_ranks'		=>	$board_ranks,
		'admin_updates'		=>	$board_updates,
		'admin_archive'		=>	$board_archive,
		'admin_smilies'		=>	$board_smilies,
		'admin_warnings'	=>	$board_warnings,
		'admin_attachments'	=>	$board_attachments,
		'admin_robots'		=>	$board_robots,
		'admin_addons'		=>	$board_addons,
		'admin_tasks'		=>	$board_tasks,
	);

	$insert = array(
		'admin_id'	=>	$user,
		'restrictions'	=>	serialize($restrictions),
	);

	if ($action == 'add')
	{
		$db->insert('restrictions', $insert);
		$redirect_lang = $lang_admin_restrictions['added redirect'];
	}
	else
	{
		$data = array(
			':id'	=>	$user,
		);

		$db->update('restrictions', $insert, 'admin_id=:id', $data);
		$redirect_lang = $lang_admin_restrictions['edited redirect'];
	}

	generate_admin_restrictions_cache();
	redirect(panther_link($panther_url['admin_restrictions']), $redirect_lang);
}
else if ($action == 'delete' && isset($_POST['form_sent']) && $stage == '3')
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/restrictions.php');

	//Stage 3: Remove restrictions
	$user = isset($_POST['admin_id']) ? intval($_POST['admin_id']) : 0;
	$data = array(
		':id'	=>	$user,
	);

	$ps = $db->select('restrictions', 1, $data, 'admin_id=:id');

	if (!$ps->rowCount())
		message($lang_admin_restrictions['no restrictions']);

	$db->delete('restrictions', 'admin_id=:id', $data);

	generate_admin_restrictions_cache();
	redirect(panther_link($panther_url['admin_restrictions']), $lang_admin_restrictions['removed redirect']);
}

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Restrictions']);
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

if ($action == 'delete' && isset($_POST['form_sent']) && $stage == '2')
{
	//Stage 2: Confirm Removal of existing restrictions
	$user = isset($_POST['user']) ? intval($_POST['user']) : 0;
	$data = array(
		':id'	=>	$user,
	);

	$ps = $db->select('restrictions', 1, $data, 'admin_id=:id');

	if (!$ps->rowCount())
		message($lang_admin_restrictions['no restrictions']);

	generate_admin_menu('restrictions');
	
	$tpl = load_template('delete_restriction.tpl');
	echo $tpl->render(
		array(
			'lang_admin_restrictions' => $lang_admin_restrictions,
			'lang_common' => $lang_common,
			'form_action' => panther_link($panther_url['admin_restrictions_query'], array('action=delete&stage=3')),
			'csrf_token' => $csrf_token,
			'user' => $user,
		)
	);
}
else if (($action == 'edit' || $action == 'add') && isset($_POST['form_sent']) && $stage == '2')
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/restrictions.php');

	//Stage 2: Edit existing restrictions
	$user = isset($_POST['user']) ? intval($_POST['user']) : 0;
	$data = array(
		':id'	=>	$user,
	);
	
	$ps = $db->select('users', 'username, group_id', $data, 'id=:id');
	if (!$ps->rowCount())
		message($lang_common['Bad request']);
	
	list($username, $group_id) = $ps->fetch(PDO::FETCH_NUM);
	
	if ($panther_groups[$group_id]['g_admin'] != '1' && $group_id != PANTHER_ADMIN)
		message($lang_common['Bad request']);

	// Then we're adding restrictions
	if (!isset($admins[$user]))
	{
		$admins[$user] = array(
			'admin_options'		=>	1,
			'admin_permissions'	=>	1,
			'admin_categories'	=>	1,
			'admin_forums'		=>	1,
			'admin_groups'		=>	1,
			'admin_censoring'	=>	1,
			'admin_maintenance'	=>	1,
			'admin_plugins'		=>	1,
			'admin_restrictions'=>	1,
			'admin_users'		=>	1,
			'admin_moderate'	=>	1,
			'admin_ranks'		=>	1,
			'admin_updates'		=>	1,
			'admin_archive'		=>	1,
			'admin_smilies'		=>	1,
			'admin_warnings'	=>	1,
			'admin_attachments'	=>	1,
			'admin_robots'		=>	1,
			'admin_addons'		=>	1,
			'admin_tasks'		=>	1,
		);
	}

	generate_admin_menu('restrictions');

	$tpl = load_template('edit_restriction.tpl');
	echo $tpl->render(
		array(
			'lang_admin_restrictions' => $lang_admin_restrictions,
			'admin' => $admins[$user],
			'user' => $user,
			'csrf_token' => $csrf_token,
			'lang_common' => $lang_common,
			'lang_admin_common' => $lang_admin_common,
			'form_action' => panther_link($panther_url['admin_restrictions_query'], array('action='.$action.'&stage=3')),
			'username' => $username,
		)
	);
}
else
{
	if (count(get_admin_ids()) < 2)
		message($lang_admin_restrictions['no admins available']);

	$data = array(
		':admin' => PANTHER_ADMIN,
	);

	$administrators = $restrictions = array();
	$ps = $db->run('SELECT u.username, u.id FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'restrictions AS ar ON u.id=ar.admin_id INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id WHERE (u.group_id=:admin OR g.g_admin=1) AND u.id!=2 AND ar.admin_id IS NULL ORDER BY u.id ASC', $data);
	foreach ($ps as $admin)
		$administrators[] = array('id' => $admin['id'], 'username' => $admin['username']);

	$ps = $db->run('SELECT u.username, u.id FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'restrictions AS ar ON u.id=ar.admin_id INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id WHERE (u.group_id=:admin OR g.g_admin=1) AND u.id!=2 AND ar.admin_id IS NOT NULL ORDER BY u.id ASC', $data);
	foreach ($ps as $admin)
		$restrictions[] = array('id' => $admin['id'], 'username' => $admin['username']);

	generate_admin_menu('restrictions');
	
	$tpl = load_template('admin_restrictions.tpl');
	echo $tpl->render(
		array(
			'lang_admin_common' => $lang_admin_common,
			'lang_admin_restrictions' => $lang_admin_restrictions,
			'csrf_token' => $csrf_token,
			'add_action' => panther_link($panther_url['admin_restrictions_query'], array('action=add&stage=2')),
			'edit_action' => panther_link($panther_url['admin_restrictions_query'], array('action=edit&stage=2')),
			'delete_action' => panther_link($panther_url['admin_restrictions_query'], array('action=delete&stage=2')),
			'lang_common' => $lang_common,
			'restrictions' => $restrictions,
			'administrators' => $administrators,
		)
	);
}
require PANTHER_ROOT.'footer.php';