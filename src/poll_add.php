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

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id < 1)
	message($lang_common['Bad request'], false, '404 Not found');
	
// Fetch some info about the topic and/or the forum
$data = array(
	':gid'	=>	$panther_user['g_id'],
	':tid'	=>	$id,
);

$ps = $db->run('SELECT f.id AS fid, f.forum_name, f.redirect_url, f.password, t.poster, t.subject, t.approved, f.moderators FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id=:tid', $data);
if (!$ps->rowCount())
	message($lang_common['Bad request'], false, '404 Not found');
	
$cur_posting = $ps->fetch();

$mods_array = ($cur_posting['moderators'] != '') ? unserialize($cur_posting['moderators']) : array();
$is_admmod = ($panther_user['is_admin'] || ($panther_user['g_moderator'] == '1' && $panther_user['g_global_moderator'] ||  array_key_exists($panther_user['username'], $mods_array))) ? true : false;

if ($panther_config['o_polls'] == '0' || (!$is_admmod && ($panther_user['g_post_polls'] == '0' || $cur_posting['poster'] != $panther_user['username'])))
	message($lang_common['No permission'], false, '403 Forbidden');

if ($cur_posting['redirect_url'] != '')
	message($lang_common['Bad request']);

if ($cur_posting['password'] != '')
		check_forum_login_cookie($cur_posting['fid'], $cur_posting['password']);

require PANTHER_ROOT.'lang/'.$panther_user['language'].'/poll.php';
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/post.php';

$errors = array();
if (isset($_POST['form_sent']))
{
	$question = isset($_POST['req_question']) ? panther_trim($_POST['req_question']) : '';
	$options = isset($_POST['options']) && is_array($_POST['options']) ? array_map('panther_trim', $_POST['options']) : array();
	$type = isset($_POST['type']) ? 2 : 1;

	if ($question == '')
		$errors[] = $lang_poll['No question'];
	else if (panther_strlen($question) > 70)
		$errors[] = $lang_poll['Too long question'];
	else if ($panther_config['p_subject_all_caps'] == '0' && is_all_uppercase($question) && !$panther_user['is_admmod'])
		$errors[] = $lang_poll['All caps question'];

	if (empty($options))
		$errors[] = $lang_poll['No options'];

	$option_data = array();
	for ($i = 0; $i <= $panther_config['o_max_poll_fields']; $i++)
	{
		if (!empty($errors))
			break;

		if (panther_strlen($options[$i]) > 55)
			$errors[] = $lang_poll['Too long option'];
		else if ($panther_config['p_subject_all_caps'] == '0' && is_all_uppercase($options[$i]) && !$panther_user['is_admmod'])
			$errors[] = $lang_poll['All caps option'];
		else if ($options[$i] != '')
			$option_data[] = $options[$i];
	}

	if (count($option_data) < 2)
		$errors[] = $lang_poll['Low options'];

	$now = time();

	// Did everything go according to plan?
	if (empty($errors) && !isset($_POST['preview']))
	{
		$update = array(
			'question'	=>	$question,
		);

		$data = array(
			':id'	=>	$id,
		);

		$db->update('topics', $update, 'id=:id', $data);
		$insert = array(
			'topic_id'	=>	$id,
			'options'	=>	serialize($option_data),
			'type'		=>	$type,
		);

		$db->insert('polls', $insert);
		$new_pid = $db->lastInsertId($db->prefix.'polls');

		($hook = get_extensions('add_poll_before_redirect')) ? eval($hook) : null;

		// Make sure we actually have a topic to go back to
		if ($cur_posting['approved'] == '0')
			redirect(panther_link($panther_url['forum'], array($cur_posting['fid'], url_friendly($cur_posting['forum_name']))), $lang_post['Topic moderation redirect']);
		else
			redirect(panther_link($panther_url['topic'], array($id, url_friendly($cur_posting['subject']))), $lang_post['Post redirect']);
	}
}

$cur_index = 1; 
$required_fields = array('req_question' => $lang_poll['Question'], 'req_subject' => $lang_common['Subject'], 'req_message' => $lang_common['Message']);
$focus_element = array('post');

if (!$panther_user['is_guest'])
	$focus_element[] = 'req_question';
else
{
	$required_fields['req_username'] = $lang_post['Guest name'];
	$focus_element[] = 'req_question';
}

$inputs = array();
for ($i = 0; $i <= $panther_config['o_max_poll_fields'] ; $i++)
{
	// Make sure this is indeed a valid option
	if (isset($option_data[$i]) && $option_data[$i] != '')
	{
		if (isset($_POST['preview']))
			$inputs[] = array('id' => $i, 'option' => $option_data[$i]);
	}
	else
		$inputs[] = array('id' => $i, 'option' => ((isset($options[$i])) ? $options[$i] : ''));
}

$page_title = array($panther_config['o_board_title'], $lang_post['Post new topic']);
define('PANTHER_ACTIVE_PAGE', 'index');
require PANTHER_ROOT.'header.php';

($hook = get_extensions('add_poll_before_display')) ? eval($hook) : null;

$tpl = load_template('add_poll.tpl');
echo $tpl->render(
	array(
		'lang_post' => $lang_post,
		'errors' => $errors,
		'preview' => (isset($_POST['preview'])) ? true : false,
		'lang_poll' => $lang_poll,
		'inputs' => $inputs,
		'question' => (isset($_POST['req_question'])) ? $question : '',
		'lang_common' => $lang_common,
		'index_link' => panther_link($panther_url['index']),
		'cur_posting' => $cur_posting,
		'forum_link' => panther_link($panther_url['forum'], array($cur_posting['fid'], url_friendly($cur_posting['forum_name']))),
		'form_action' => panther_link($panther_url['poll_add'], array($id)),
		'type' => isset($_POST['type']) ? true : false,
	)
);

require PANTHER_ROOT.'footer.php';