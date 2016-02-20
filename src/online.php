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

if ($panther_user['is_bot'])
	message($lang_common['No permission']);

// Load the userlist.php language file
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/online.php';

// Load the search.php language file
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/search.php';

if ($panther_user['g_view_users'] == '0')
	message($lang_common['No permission'], false, '403 Forbidden');

$ps = $db->select('online', 'COUNT(user_id)', array(), 'idle=0');
$num_online = $ps->fetchColumn();

// Determine the post offset (based on $_GET['p'])
$num_pages = ceil(($num_online) / $panther_user['disp_posts']);

$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);
$start_from = $panther_user['disp_posts'] * ($p - 1);

($hook = get_extensions('online_before_header')) ? eval($hook) : null;

$page_title = array($panther_config['o_board_title'], $lang_online['viewing online']);
define('PANTHER_ACTIVE_PAGE', 'online');
require PANTHER_ROOT.'header.php';

$bots = $online = array();
$ps = $db->run('SELECT o.user_id, o.ident, o.currently, o.logged, u.group_id FROM '.$db->prefix.'online AS o INNER JOIN '.$db->prefix.'users AS u ON o.user_id=u.id WHERE o.idle=0');
foreach ($ps as $panther_user_online)
{
	if (strpos($panther_user_online['ident'], '[Bot]') !== false)
	{
		$name = explode('[Bot]', $panther_user_online['ident']);
		if (empty($bots[$name[1]])) $bots[$name[1]] = 1;
			else ++$bots[$name[1]];

		foreach ($bots as $online_name => $online_id)
		   $ident = $online_name.' [Bot]';
	}
	else
	{
		if ($panther_user_online['user_id'] == 1)
			$ident = $lang_common['Guest'];
		else
			$ident = $panther_user_online['ident'];
	}

	$online[] = array(
		'username' => colourize_group($ident, $panther_user_online['group_id'], $panther_user_online['user_id']),
		'location' => generate_user_location($panther_user_online['currently']),
		'last_active' => format_time_difference($panther_user_online['logged'], $lang_online),
	);
}

$tpl = load_template('online.tpl');
echo $tpl->render(
	array(
		'pagination' => paginate($num_pages, $p, $panther_url['online']),
		'lang_online' => $lang_online,
		'lang_common' => $lang_common,
		'users_online' => $online,
		'num_pages' => $num_pages,
	)
);

($hook = get_extensions('online_after_display')) ? eval($hook) : null;

require PANTHER_ROOT.'footer.php';