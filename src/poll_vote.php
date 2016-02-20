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

$id = isset($_POST['poll_id']) ? intval($_POST['poll_id']) : 0;

if ($id < 1)
	message($lang_common['Bad request'], false, '404 Not Found');

$data = array(
	':id'	=>	$id,
	':gid'	=>	$panther_user['g_id'],
);

$ps = $db->run('SELECT f.id, f.moderators, f.password, f.redirect_url, fp.post_replies, fp.post_topics, t.subject, t.closed, t.archived, p.id AS pid, p.type, p.options, p.voters, p.votes FROM '.$db->prefix.'polls AS p INNER JOIN '.$db->prefix.'topics AS t ON p.topic_id=t.id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id=:id', $data);
if (!$ps->rowCount())
	message($lang_common['Bad request'], false, '404 Not Found');

$cur_poll = $ps->fetch();

if ($cur_poll['password'] != '')
		check_forum_login_cookie($cur_poll['id'], $cur_poll['password']);

$mods_array = ($cur_poll['moderators'] != '') ? unserialize($cur_poll['moderators']) : array();
$is_admmod = ($panther_user['is_admin'] || ($panther_user['g_moderator'] == '1' && $panther_user['g_global_moderator'] ||  array_key_exists($panther_user['username'], $mods_array))) ? true : false;

// Make sure we have permission to vote
if ((((($cur_poll['post_replies'] == '' && $panther_user['g_post_replies'] == '0') || $cur_poll['post_replies'] == '0') || $panther_user['is_guest']) || $cur_poll['closed'] == '1') && !$is_admmod || $cur_poll['archived'] == '1')
	message($lang_common['No permission'], false, '403 Forbidden');

require PANTHER_ROOT.'lang/'.$panther_user['language'].'/poll.php';

if (isset($_POST['form_sent']))
{
	confirm_referrer('viewtopic.php');

	$options = ($cur_poll['options'] != '') ? unserialize($cur_poll['options']) : array();
	$voters = ($cur_poll['voters'] != '') ? unserialize($cur_poll['voters']) : array();
	$votes = ($cur_poll['votes'] != '') ? unserialize($cur_poll['votes']) : array();
	
	($hook = get_extensions('poll_vote_before_validation')) ? eval($hook) : null;

	if (in_array($panther_user['id'], $voters))
		message($lang_poll['Already voted']);

	if ($cur_poll['type'] == '1')
	{
		$vote = isset($_POST['vote']) ? intval($_POST['vote']) : -1;
		if ($vote < 0)
			message($lang_common['Bad request'], false, '404 Not Found');

		// Increment the amount of votes for this option
		$votes[$vote] = isset($votes[$vote]) ? $votes[$vote]++ : 1;
	}
	else
	{
		$vote = isset($_POST['options']) && is_array($_POST['options']) ? array_map('intval', $_POST['options']) : array();

		if (empty($vote))
			message($lang_common['Bad request'], false, '404 Not Found');

		foreach ($vote as $key => $value)
		{
			// If the value isn't nothing, and it's a valid option, increment the votes
			if (!empty($value) && isset($options[$key]))
				$votes[$key] = isset($votes[$key]) ? $votes[$key]++ : 1;
		}
	}

	$voters[] = $panther_user['id'];
	$update = array(
		'votes'		=>	serialize($votes),
		'voters'	=>	serialize($voters),
	);

	$data = array(
		':id'	=>	$cur_poll['pid'],
	);
	
	$db->update('polls', $update, 'id=:id', $data);
	redirect(panther_link($panther_url['topic'], array($id, url_friendly($cur_poll['subject']))), $lang_poll['Vote success']);
}
else
	message($lang_common['Bad request'], false, '404 Not Found');