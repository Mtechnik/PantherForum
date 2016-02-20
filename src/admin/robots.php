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
require PANTHER_ROOT.'include/common_admin.php';

if (!$panther_user['is_admin'])
	message($lang_common['No permission'], false, '403 Forbidden');

if ($panther_user['id'] != '2')
{
	if(!is_null($admins[$panther_user['id']]['admin_robots']))
	{
		if ($admins[$panther_user['id']]['admin_robots'] == '0')
			message($lang_common['No permission'], false, '403 Forbidden');
	}
}

check_authentication();

// Load the admin_robots.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_robots.php';

// Add a robot test
if (isset($_POST['add_test']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/robots.php');

	$question = isset($_POST['new_question']) ? panther_trim($_POST['new_question']) : '';
	$answer = isset($_POST['new_answer']) ? panther_trim($_POST['new_answer']) : '';

	if ($question == '' || $answer == '')
		message($lang_admin_robots['Must enter question message']);

	$insert = array(
		'question' => $question,
		'answer' => $answer,
	);

	$db->insert('robots', $insert);

	// Regenerate the robots cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_robots_cache();
	redirect(panther_link($panther_url['admin_robots']), $lang_admin_robots['Question added redirect']);
}
else if (isset($_POST['update'])) // Update a robot question
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/robots.php');

	$id = intval(key($_POST['update']));

	$question = isset($_POST['question'][$id]) ? panther_trim($_POST['question'][$id]) : '';
	$answer = isset($_POST['answer'][$id]) ? panther_trim($_POST['answer'][$id]) : '';

	if ($question == '' || $answer == '')
		message($lang_admin_robots['Must enter question message']);

	$update = array(
		'question' => $question,
		'answer' => $answer,
	);
	
	$data = array(
		':id'	=>	$id,
	);

	$db->update('robots', $update, 'id=:id', $data);

	// Regenerate the robots cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_robots_cache();
	redirect(panther_link($panther_url['admin_robots']), $lang_admin_robots['Question updated redirect']);
}

// Remove a robot test
else if (isset($_POST['remove']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/robots.php');
	$id = intval(key($_POST['remove']));
	$data = array(
		':id'	=>	$id,
	);

	$db->delete('robots', 'id=:id', $data);

	// Regenerate the robots cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_robots_cache();
	redirect(panther_link($panther_url['admin_robots']),  $lang_admin_robots['Question removed redirect']);
}

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Robots']);
$focus_element = array('robots', 'new_question');
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

generate_admin_menu('robots');

$robots = array();
$ps = $db->select('robots', 'id, question, answer', array(), '', 'id');
foreach ($ps as $cur_test)
	$robots[] = array(
		'id' => $cur_test['id'],
		'question' => $cur_test['question'],
		'answer' => $cur_test['answer'],
	);

$tpl = load_template('admin_robots.tpl');
echo $tpl->render(
	array(
		'lang_admin_robots' => $lang_admin_robots,
		'lang_admin_common' => $lang_admin_common,
		'form_action' => panther_link($panther_url['admin_robots']),
		'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/robots.php'),
		'robots' => $robots,
	)
);

require PANTHER_ROOT.'footer.php';