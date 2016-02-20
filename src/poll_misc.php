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

$id = ((isset($_GET['edit'])) ? intval($_GET['edit']) : (isset($_GET['delete']) ? intval($_GET['delete']) : 0));
$id = (($id != 0) ? $id : (isset($_GET['reset']) ? intval($_GET['reset']) : 0));

if ($id < 1)
	message($lang_common['Bad request']);
	
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/poll.php';

$data = array(
	':gid'	=>	$panther_user['g_id'],
	':tid'	=>	$id,
);

// Fetch some info about the topic and the forum
$ps = $db->run('SELECT f.moderators, f.password, f.redirect_url, f.id AS fid, t.archived, t.closed, t.subject, t.poster, t.question, p.type, p.options, p.id AS pid FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id INNER JOIN '.$db->prefix.'polls AS p ON t.id=p.topic_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.question!=\'\' AND t.id=:tid', $data);
if (!$ps->rowCount())
	message($lang_common['Bad request']);
	
$cur_topic = $ps->fetch();
	
// Is this a redirect forum? In that case, abort!
if ($cur_topic['redirect_url'] != '' || $cur_topic['question'] == '')
	message($lang_common['Bad request']);

if ($cur_topic['password'] != '')
		check_forum_login_cookie($id, $cur_topic['password']);

$mods_array = ($cur_topic['moderators'] != '') ? unserialize($cur_topic['moderators']) : array();
$is_admmod = ($panther_user['is_admin'] || ($panther_user['g_moderator'] == '1' && $panther_user['g_global_moderator'] ||  array_key_exists($panther_user['username'], $mods_array))) ? true : false;

$options = ($cur_topic['options'] != '') ? unserialize($cur_topic['options']) : array();

if ($cur_topic['archived'] == '1')
	message($lang_common['No permission']);
	
if (isset($_GET['edit']))
{
	// Do we have permission to edit this poll?
	if ($cur_topic['poster'] != $panther_user['username'] && $cur_topic['closed'] == '1' && !$is_admmod)
		message($lang_common['No permission']);
	
	$errors = array();
	if (isset($_POST['form_sent']))
	{
		confirm_referrer('poll_misc.php');

		$question = isset($_POST['req_question']) ? panther_trim($_POST['req_question']) : '';
		$options = isset($_POST['options']) && is_array($_POST['options']) ? array_map('panther_trim', $_POST['options']) : array();

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

		if (count($options) < 2)
			$errors[] = $lang_poll['Low options'];
		
		($hook = get_extensions('edit_poll_after_validation')) ? eval($hook) : null;

		$now = time();
		if (empty($errors))
		{
			$update = array(
				'question'	=>	$question,
			);

			$data = array(
				':id'	=>	$id,
			);

			$db->update('topics', $update, 'id=:id', $data);
			$update = array(
				'options'	=>	serialize($option_data),
			);

			$data = array(
				':id'	=>	$cur_topic['pid'],
			);
			
			$db->update('polls', $update, 'id=:id', $data);
			redirect(panther_link($panther_url['topic'], array($id, url_friendly($cur_topic['subject']))), $lang_poll['Poll updated redirect']);
		}
	}

	$fields = array();
	for ($i = 0; $i <= $panther_config['o_max_poll_fields']; $i++)
		$fields[] = ((isset($options[$i])) ? $options[$i] : '');
	
	require PANTHER_ROOT.'lang/'.$panther_user['language'].'/post.php';
	
	($hook = get_extensions('edit_poll_before_header')) ? eval($hook) : null;

	$page_title = array($panther_config['o_board_title'], $lang_poll['Edit poll']);
	define('PANTHER_ACTIVE_PAGE', 'index');
	require PANTHER_ROOT.'header.php';
	
	$tpl = load_template('edit_poll.tpl');
	echo $tpl->render(
		array(
			'lang_poll' => $lang_poll,
			'lang_post' => $lang_post,
			'lang_common' => $lang_common,
			'form_action' => panther_link($panther_url['poll_edit'], array($id)),
			'csrf_token' => generate_csrf_token(),
			'cur_topic' => $cur_topic,
			'options' => $fields,
			'errors' => $errors,
		)
	);
}
else if (isset($_GET['delete']))
{
	// Do we have permission to delete this poll?
	if ($cur_topic['poster'] != $panther_user['username'] && $cur_topic['closed'] == '1' && !$is_admmod)
		message($lang_common['No permission']);

	if (isset($_POST['form_sent']))
	{
		confirm_referrer('poll_misc.php');
		$data = array(
			':id'	=>	$cur_topic['pid'],
		);

		$db->delete('polls', 'id=:id', $data);
		$update = array(
			'question'	=>	'',
		);

		$data = array(
			':id'	=>	$id,
		);

		$db->update('topics', $update, 'id=:id', $data);
		
		($hook = get_extensions('delete_poll_after_deletion')) ? eval($hook) : null;
		redirect(panther_link($panther_url['topic'], array($id, url_friendly($cur_topic['subject']))), $lang_poll['Poll deleted redirect']);
	}
	
	($hook = get_extensions('delete_poll_before_header')) ? eval($hook) : null;

	$page_title = array($panther_config['o_board_title'], $lang_poll['Delete poll']);
	define('PANTHER_ACTIVE_PAGE', 'index');
	require PANTHER_ROOT.'header.php';
	
	$tpl = load_template('delete_poll.tpl');
	echo $tpl->render(
		array(
			'lang_poll' => $lang_poll,
			'lang_common' => $lang_common,
			'form_action' => panther_link($panther_url['poll_delete'], array($id)),
			'csrf_token' => generate_csrf_token(),
		)
	);
}
else if (isset($_GET['reset']))
{
	if (isset($_POST['form_sent']))
	{
		confirm_referrer('poll_misc.php');

		$update = array(
			'voters'	=>	'',
			'votes'		=>	'',
		);

		$data = array(
			':id'	=>	$cur_topic['pid'],
		);

		$db->update('polls', $update, 'id=:id', $data);
		redirect(panther_link($panther_url['topic'], array($id, url_friendly($cur_topic['subject']))), $lang_poll['Poll reset redirect']);
	}

	$page_title = array($panther_config['o_board_title'], $lang_poll['Reset poll']);
	define('PANTHER_ACTIVE_PAGE', 'index');
	require PANTHER_ROOT.'header.php';
	
	($hook = get_extensions('reset_poll_before_display')) ? eval($hook) : null;
	
	$tpl = load_template('reset_poll.tpl');
	echo $tpl->render(
		array(
			'lang_poll' => $lang_poll,
			'lang_common' => $lang_common,
			'form_action' => panther_link($panther_url['poll_reset'], array($id)),
			'csrf_token' => generate_csrf_token(),
		)
	);
}
else
	message($lang_common['Bad request']);

require PANTHER_ROOT.'footer.php';