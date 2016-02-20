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

if ($panther_user['is_bot'])
	message($lang_common['No permission']);

if ($panther_user['g_robot_test'] == '1')
{
	if (file_exists(FORUM_CACHE_DIR.'cache_robots.php'))
		include FORUM_CACHE_DIR.'cache_robots.php';

	if (!defined('PANTHER_ROBOTS_LOADED'))
	{
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';

		generate_robots_cache();
		require FORUM_CACHE_DIR.'cache_robots.php';
	}
}

$tid = isset($_GET['tid']) ? intval($_GET['tid']) : 0;
$fid = isset($_GET['fid']) ? intval($_GET['fid']) : 0;
if ($tid < 1 && $fid < 1 || $tid > 0 && $fid > 0 || !$tid && !$fid)
	message($lang_common['Bad request'], false, '404 Not Found');

$data = array(
	':gid'	=>	$panther_user['g_id'],
);

// Fetch some info about the topic and/or the forum
if ($tid)
{
	$data[':id'] = $panther_user['id'];
	$data[':tid'] = $tid;
	$ps = $db->run('SELECT f.id, f.forum_name, f.moderators, f.increment_posts, f.password, f.redirect_url, f.force_approve, fp.post_replies, fp.post_polls, fp.post_topics, fp.upload, t.subject, t.archived, t.closed, s.user_id AS is_subscribed FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) LEFT JOIN '.$db->prefix.'topic_subscriptions AS s ON (t.id=s.topic_id AND s.user_id=:id) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id=:tid', $data);
}
else
{
	$data[':fid'] = $fid;	
	$ps = $db->run('SELECT f.id, f.forum_name, f.moderators, f.increment_posts, f.password, f.redirect_url, f.force_approve, fp.post_replies, fp.post_polls, fp.post_topics, fp.upload, 0 AS archived FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.id=:fid', $data);
}

if (!$ps->rowCount())
	message($lang_common['Bad request'], false, '404 Not Found');

$cur_posting = $ps->fetch();
$is_subscribed = $tid && $cur_posting['is_subscribed'];

// Is someone trying to post into a redirect forum?
if ($cur_posting['redirect_url'] != '')
	message($lang_common['Bad request'], false, '404 Not Found');

// Sort out who the moderators are and if we are currently a moderator (or an admin)
$mods_array = ($cur_posting['moderators'] != '') ? unserialize($cur_posting['moderators']) : array();
$is_admmod = ($panther_user['is_admin'] || ($panther_user['g_moderator'] == '1' && $panther_user['g_global_moderator'] ||  array_key_exists($panther_user['username'], $mods_array))) ? true : false;

if ($tid && $panther_config['o_censoring'] == '1')
	$cur_posting['subject'] = censor_words($cur_posting['subject']);

// Do we have permission to post?
if ((($tid && (($cur_posting['post_replies'] == '' && $panther_user['g_post_replies'] == '0') || $cur_posting['post_replies'] == '0')) ||
	($fid && (($cur_posting['post_topics'] == '' && $panther_user['g_post_topics'] == '0') || $cur_posting['post_topics'] == '0')) ||
	(isset($cur_posting['closed']) && $cur_posting['closed'] == '1')) &&
	!$is_admmod)
	message($lang_common['No permission'], false, '403 Forbidden');
	
if ($cur_posting['password'] != '')
{
	if ($fid)
		check_forum_login_cookie($fid, $cur_posting['password']);
	else
		check_forum_login_cookie($cur_posting['id'], $cur_posting['password']);
}

// Load the post.php language file
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/post.php';
check_posting_ban();

if ($cur_posting['archived'] == '1')
	message($lang_post['Topic archived']);

// Start with a clean slate
$errors = array();

// Did someone just hit "Submit" or "Preview"?
if (isset($_POST['form_sent']))
{
	($hook = get_extensions('post_before_validation')) ? eval($hook) : null;

	// Flood protection
	if (!isset($_POST['preview']) && $panther_user['last_post'] != '' && (time() - $panther_user['last_post']) < $panther_user['g_post_flood'])
		$errors[] = sprintf($lang_post['Flood start'], $panther_user['g_post_flood'], $panther_user['g_post_flood'] - (time() - $panther_user['last_post']));

	// Make sure they got here from the site
	confirm_referrer('post.php');

	// If it's a new topic
	if ($fid)
	{
		$subject = isset($_POST['req_subject']) ? panther_trim($_POST['req_subject']) : '';

		if ($panther_config['o_censoring'] == '1')
			$censored_subject = panther_trim(censor_words($subject));

		if ($subject == '')
			$errors[] = $lang_post['No subject'];
		else if ($panther_config['o_censoring'] == '1' && $censored_subject == '')
			$errors[] = $lang_post['No subject after censoring'];
		else if (panther_strlen($subject) > 70)
			$errors[] = $lang_post['Too long subject'];
		else if ($panther_config['p_subject_all_caps'] == '0' && is_all_uppercase($subject) && !$panther_user['is_admmod'])
			$errors[] = $lang_post['All caps subject'];
	}

	if (!empty($panther_robots) && $panther_user['g_robot_test'] == '1')
	{
		$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		$answer = isset($_POST['answer']) ? panther_trim($_POST['answer']) : '';

		if (!isset($panther_robots[$id]) || $answer != $panther_robots[$id]['answer'])
			$errors[] = $lang_common['Robot test fail'];
	}

	// If the user is logged in we get the username and email from $panther_user
	if (!$panther_user['is_guest'])
	{
		$username = $panther_user['username'];
		$email = $panther_user['email'];
	}
	// Otherwise it should be in $_POST
	else
	{
		$username = isset($_POST['req_username']) ? panther_trim($_POST['req_username']) : '';
		$email = strtolower(panther_trim(($panther_config['p_force_guest_email'] == '1') ? $_POST['req_email'] : $_POST['email']));
		$banned_email = false;

		// Load the register.php/prof_reg.php language files
		require PANTHER_ROOT.'lang/'.$panther_user['language'].'/prof_reg.php';
		require PANTHER_ROOT.'lang/'.$panther_user['language'].'/register.php';

		// It's a guest, so we have to validate the username
		check_username($username);

		if ($panther_config['p_force_guest_email'] == '1' || $email != '')
		{
			require PANTHER_ROOT.'include/email.php';
			if (!$mailer->is_valid_email($email))
				$errors[] = $lang_common['Invalid email'];

			// Check if it's a banned email address
			// we should only check guests because members' addresses are already verified
			if ($panther_user['is_guest'] && $mailer->is_banned_email($email))
			{
				if ($panther_config['p_allow_banned_email'] == '0')
					$errors[] = $lang_prof_reg['Banned email'];

				$banned_email = true; // Used later when we send an alert email
			}
		}
	}

	// Clean up message from POST
	$orig_message = $message = isset($_POST['req_message']) ? panther_linebreaks(panther_trim($_POST['req_message'])) : '';

	// Here we use strlen() not panther_strlen() as we want to limit the post to PANTHER_MAX_POSTSIZE bytes, not characters
	if (strlen($message) > PANTHER_MAX_POSTSIZE)
		$errors[] = sprintf($lang_post['Too long message'], forum_number_format(PANTHER_MAX_POSTSIZE));
	else if ($panther_config['p_message_all_caps'] == '0' && is_all_uppercase($message) && !$panther_user['is_admmod'])
		$errors[] = $lang_post['All caps message'];

	// Validate BBCode syntax
	if ($panther_config['p_message_bbcode'] == '1')
	{
		require PANTHER_ROOT.'include/parser.php';
		$message = $parser->preparse_bbcode($message, $errors);
	}

	if (empty($errors))
	{
		if ($message == '')
			$errors[] = $lang_post['No message'];
		else if ($panther_config['o_censoring'] == '1')
		{
			// Censor message to see if that causes problems
			$censored_message = panther_trim(censor_words($message));

			if ($censored_message == '')
				$errors[] = $lang_post['No message after censoring'];
		}
	}

	$hide_smilies = isset($_POST['hide_smilies']) ? '1' : '0';
	$subscribe = isset($_POST['subscribe']) ? '1' : '0';
	$stick_topic = isset($_POST['stick_topic']) && $is_admmod ? '1' : '0';
	$add_poll = isset($_POST['add_poll']) && $fid && $cur_posting['post_polls'] != '0' && $panther_user['g_post_polls'] == '1' && $panther_config['o_polls'] == '1' ? 1 : 0;
	$topic_approve = (!$is_admmod && ($cur_posting['force_approve'] == '1' || $cur_posting['force_approve'] == '3' || $panther_user['g_moderate_posts'] == '1')) ? 0 : 1;
	$post_approve = (!$is_admmod && ($cur_posting['force_approve'] == '2' || $cur_posting['force_approve'] == '3' || $panther_user['g_moderate_posts'] == '1')) ? 0 : 1;

	// Replace four-byte characters (MySQL cannot handle them)
	$message = strip_bad_multibyte_chars($message);

	$now = time();

	($hook = get_extensions('post_after_validation')) ? eval($hook) : null;

	// Did everything go according to plan?
	if (empty($errors) && !isset($_POST['preview']))
	{
		require PANTHER_ROOT.'include/search_idx.php';

		// If it's a reply
		if ($tid)
		{
			if (!$panther_user['is_guest'])
			{
				$new_tid = $tid;

				// Insert the new post
				$insert = array(
					'poster'	=>	$username,
					'poster_id'	=>	$panther_user['id'],
					'poster_ip'	=>	get_remote_address(),
					'message'	=>	$message,
					'hide_smilies'	=>	$hide_smilies,
					'posted'	=>	$now,
					'topic_id'	=>	$tid,
					'approved'	=>	$post_approve,
				);
		 
				$db->insert('posts', $insert);
				$new_pid = $db->lastInsertId($db->prefix.'posts');

				// To subscribe or not to subscribe, that ...
				if ($panther_config['o_topic_subscriptions'] == '1')
				{
					if ($subscribe && !$is_subscribed)
					{
						$data = array(
							'user_id'	=>	$panther_user['id'],
							'topic_id'	=>	$tid,
						);
						
						$db->insert('topic_subscriptions', $data);
					}
					else if (!$subscribe && $is_subscribed)
					{
						$data = array(
							':id'	=>	$panther_user['id'],
							':tid'	=>	$tid,
						);

						$db->delete('topic_subscriptions', 'user_id=:uid AND topic_id=:tid', $data);
					}
				}
			}
			else
			{
				// It's a guest. Insert the new post
				$insert_email = ($panther_config['p_force_guest_email'] == '1' || $email != '') ? $email : NULL;
				$insert = array(
					'poster'	=>	$username,
					'poster_ip'	=>	get_remote_address(),
					'poster_email'	=>	$insert_email,
					'message'	=>	$message,
					'hide_smilies'	=>	$hide_smilies,
					'posted'	=>	$now,
					'topic_id'	=>	$tid,
					'approved'	=>	$post_approve,
				);

				$db->insert('posts', $insert);
				$new_pid = $db->lastInsertId($db->prefix.'posts');
			}

			if ($post_approve == '1')
			{
				// Update topic
				$data = array(
					':now'	=>	$now,
					':last_post_id'	=>	$new_pid,
					':last_poster'	=>	$username,
					':id'	=>	$tid,
				);

				$db->run('UPDATE '.$db->prefix.'topics SET num_replies=num_replies+1, last_post=:now, last_post_id=:last_post_id, last_poster=:last_poster WHERE id=:id', $data);

				update_search_index('post', $new_pid, $message);
				update_forum($cur_posting['id']);

				require_once PANTHER_ROOT.'include/email.php';
				$cur_posting['message'] = ($panther_config['o_censoring'] == '1') ? $censored_message : $message;
				$mailer->handle_topic_subscriptions($tid, $cur_posting, $username, $new_pid);
			}
			else
			{
				require_once PANTHER_ROOT.'include/email.php';
				
				$info = array(
					'message' => array(
						'<username>' => $username,
						'<topic_title>' => $cur_posting['subject'],
						'<post_url>' => panther_link($panther_url['admin_posts']),
					)
				);

				$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/new_post.tpl', $info);
				$mailer->send($panther_config['o_mailing_list'], $mail_tpl['subject'], $mail_tpl['message']);
			}
		}
		// If it's a new topic
		else if ($fid)
		{
			// Create the topic
			$insert = array(
				'poster'	=>	$username,
				'subject'	=>	$subject,
				'posted'	=>	$now,
				'last_post'	=>	$now,
				'last_poster'	=>	$username,
				'sticky'	=>	$stick_topic,
				'forum_id'	=>	$fid,
				'approved'	=>	$topic_approve,
			);

			$db->insert('topics', $insert);
			$new_tid = $db->lastInsertId($db->prefix.'topics');

			if (!$panther_user['is_guest'])
			{
				// To subscribe or not to subscribe, that ...
				$data = array(
					'user_id'	=>	$panther_user['id'],
					'topic_id'	=>	$new_tid,
				);

				if ($panther_config['o_topic_subscriptions'] == '1' && $subscribe)
					$db->insert('topic_subscriptions', $data);

				// Create the post ("topic post")
				$insert = array(
					'poster'	=>	$username,
					'poster_id'	=>	$panther_user['id'],
					'poster_ip'	=>	get_remote_address(),
					'message'	=>	$message,
					'hide_smilies'	=>	$hide_smilies,
					'posted'	=>	$now,
					'topic_id'	=>	$new_tid,
					'approved'	=>	$topic_approve,
				);
			}
			else
			{
				$insert_email = ($panther_config['p_force_guest_email'] == '1' || $email != '') ? $email : NULL;
				// Create the post ("topic post")
				$insert = array(
					'poster'	=>	$username,
					'poster_ip'	=>	get_remote_address(),
					'poster_email'	=>	$insert_email,
					'message'	=>	$message,
					'hide_smilies'	=>	$hide_smilies,
					'posted'	=>	$now,
					'topic_id'	=>	$new_tid,
					'approved'	=>	$topic_approve,
				);
			}

			$db->insert('posts', $insert);
			$new_pid = $db->lastInsertId($db->prefix.'posts');

			// Update the topic with last_post_id
			$update = array(
				'last_post_id'	=>	$new_pid,
				'first_post_id'	=>	$new_pid,
			);

			$data = array(
				':id'	=>	$new_tid,
			);

			$db->update('topics', $update, 'id=:id', $data);

			if ($topic_approve)
			{
				update_search_index('post', $new_pid, $message, $subject);
				update_forum($fid);
				
				require_once PANTHER_ROOT.'include/email.php';

				$cur_posting['subject'] = ($panther_config['o_censoring'] == '1') ? $censored_subject : $subject;
				$cur_posting['message'] = ($panther_config['o_censoring'] == '1') ? $censored_message : $message;
				$mailer->handle_forum_subscriptions($cur_posting, $username, $new_tid);
			}
			else
			{
				require_once PANTHER_ROOT.'include/email.php';
				
				$info = array(
					'message' => array(
						'<username>' => $panther_user['username'],
						'<forum_name>' => $cur_posting['forum_name'],
						'<post_url>' => panther_link($panther_url['admin_posts']),
					)
				);

				// Load the "new post" template
				$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/new_post_topic.tpl', $info);
				$mailer->send($panther_config['o_mailing_list'], $mail_tpl['subject'], $mail_tpl['message']);
			}
		}

		// If we previously found out that the email was banned
		if ($panther_user['is_guest'] && $banned_email && $panther_config['o_mailing_list'] != '')
		{
			$info = array(
				'message' => array(
					'<username>' => $username,
					'<email>' => $email,
					'<post_url>' => panther_link($panther_url['post'], array($new_pid)),
				)
			);
			$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/banned_email_post.tpl', $info);
			$mailer->send($panther_config['o_mailing_list'], $mail_tpl['subject'], $mail_tpl['message']);
		}

		if (isset($_FILES['attached_file']))
		{
			if (isset($_FILES['attached_file']['error']) && $_FILES['attached_file']['error'] != 0 && $_FILES['attached_file']['error'] != 4)
				message(file_upload_error_message($_FILES['attached_file']['error']), __FILE__, __LINE__);

			if ($_FILES['attached_file']['size'] != 0 && is_uploaded_file($_FILES['attached_file']['tmp_name']))
			{
				$can_upload = false;
				if ($panther_user['is_admin'])
					$can_upload = true;
				else
				{
					$can_upload = ($panther_user['g_attach_files'] == '1' && ($cur_posting['upload'] == '1' || $cur_posting['upload'] == '')) ? true : false;

					$max_size = ($panther_user['g_max_size'] == '0' && $panther_user['g_attach_files'] == '1') ? $panther_config['o_max_upload_size'] : $panther_user['g_max_size'];
					if ($can_upload && $_FILES['attached_file']['size'] > $max_size)
						$can_upload = false;

					if (!check_file_extension($_FILES['attached_file']['name']))
						$can_upload = false;
				}

				if ($can_upload)
				{
					if (!create_attachment($_FILES['attached_file']['name'], $_FILES['attached_file']['type'], $_FILES['attached_file']['size'], $_FILES['attached_file']['tmp_name'], $new_pid, strlen($message)))
						message($lang_post['Attachment error']);
				}
				else // Remove file as it's either dangerous or they've attempted to URL hack. Either way, there's no need for it.
					unlink($_FILES['attached_file']['tmp_name']);
			}
		}

		// If the posting user is logged in, increment his/her post count
		if (!$panther_user['is_guest'])
		{
			if ($fid && $topic_approve == '1' || $tid && $post_approve == '1')
			{
				$data = array(
					':id'	=>	$panther_user['id'],
					':last_post'	=>	$now,
				);

				$update = ($cur_posting['increment_posts'] == '1') ? 'num_posts=num_posts+1, ' : '';
				$db->run('UPDATE '.$db->prefix.'users SET '.$update.'last_post=:last_post WHERE id=:id', $data);

				// Promote this user to a new group if enabled
				if ($panther_user['g_promote_next_group'] != 0 && $panther_user['num_posts'] + 1 >= $panther_user['g_promote_min_posts'] && $cur_posting['increment_posts'] == '1')
				{
					$update = array(
						'group_id'	=>	$panther_user['g_promote_next_group'],
					);
					
					$data = array(
						'id'	=>	$panther_user['id'],
					);

					$db->update('users', $update, 'id=:id', $data);
				}
			}
			else
			{
				$update = array(
					'last_post'	=>	$now,
				);
				
				$data = array(
					':id'	=>	$panther_user['id'],
				);
				
				$db->update('users', $update, 'id=:id', $data);
			}
				
			// Topic tracking stuff...
			$tracked_topics = get_tracked_topics();
			$tracked_topics['topics'][$new_tid] = time();
			set_tracked_topics($tracked_topics);
		}
		else
		{
			$update = array(
				'last_post'	=>	$now,
			);

			$data = array(
				':ident'	=>	get_remote_address(),
			);
			
			$db->update('online', $update, 'ident=:ident', $data);
		}

		($hook = get_extensions('post_after_posted')) ? eval($hook) : null;

		if ($add_poll)
			$redirect = panther_link($panther_url['poll_add'], array($new_tid));

		switch (true)
		{
			case $fid && $topic_approve == '0':
				$redirect_lang = $lang_post['Topic moderation redirect'];
				
				if (!isset($redirect))
					$redirect = panther_link($panther_url['forum'], array($cur_posting['id'], url_friendly($subject)));
			break;
			case $tid && $post_approve == '0':
				$redirect_lang = $lang_post['Post moderation redirect'];
				
				if (!isset($redirect))
					$redirect = panther_link($panther_url['topic'], array($tid, url_friendly($cur_posting['subject'])));
			break;
			default:
				$redirect_lang = $lang_post['Post redirect'];

				if (!isset($redirect))
					$redirect = panther_link($panther_url['post'], array($new_pid));
			break;
		}

		redirect($redirect, $redirect_lang);
	}
}

// If a topic ID was specified in the url (it's a reply)
if ($tid)
{
	$post_link = panther_link($panther_url['new_reply'], array($tid));
	$action = $lang_post['Post a reply'];

	// If a quote ID was specified in the url
	if (isset($_GET['qid']))
	{
		$qid = intval($_GET['qid']);
		if ($qid < 1)
			message($lang_common['Bad request'], false, '404 Not Found');

		$data = array(
			':id'	=>	$qid,
			':tid'	=>	$tid,
		);

		$ps = $db->select('posts', 'poster, message', $data, 'id=:id AND topic_id=:tid');
		if (!$ps->rowCount())
			message($lang_common['Bad request'], false, '404 Not Found');

		list($q_poster, $q_message) = $ps->fetch(PDO::FETCH_NUM);

		// If the message contains a code tag we have to split it up (text within [code][/code] shouldn't be touched)
		if (strpos($q_message, '[code]') !== false && strpos($q_message, '[/code]') !== false)
		{
			list($inside, $outside) = split_text($q_message, '[code]', '[/code]');

			$q_message = implode("\1", $outside);
		}

		// Remove [img] tags from quoted message
		$q_message = preg_replace('%\[img(?:=(?:[^\[]*?))?\]((ht|f)tps?://)([^\s<"]*?)\[/img\]%U', '\1\3', $q_message);

		// If we split up the message before we have to concatenate it together again (code tags)
		if (isset($inside))
		{
			$outside = explode("\1", $q_message);
			$q_message = '';

			$num_tokens = count($outside);
			for ($i = 0; $i < $num_tokens; ++$i)
			{
				$q_message .= $outside[$i];
				if (isset($inside[$i]))
					$q_message .= '[code]'.$inside[$i].'[/code]';
			}

			unset($inside);
		}

		if ($panther_config['o_censoring'] == '1')
			$q_message = censor_words($q_message);

		if ($panther_config['p_message_bbcode'] == '1')
		{
			// If username contains a square bracket, we add "" or '' around it (so we know when it starts and ends)
			if (strpos($q_poster, '[') !== false || strpos($q_poster, ']') !== false)
			{
				if (strpos($q_poster, '\'') !== false)
					$q_poster = '"'.$q_poster.'"';
				else
					$q_poster = '\''.$q_poster.'\'';
			}
			else
			{
				// Get the characters at the start and end of $q_poster
				$ends = substr($q_poster, 0, 1).substr($q_poster, -1, 1);

				// Deal with quoting "Username" or 'Username' (becomes '"Username"' or "'Username'")
				if ($ends == '\'\'')
					$q_poster = '"'.$q_poster.'"';
				else if ($ends == '""')
					$q_poster = '\''.$q_poster.'\'';
			}

			$quote = '[quote='.$q_poster.']'.$q_message.'[/quote]'."\n";
		}
		else
			$quote = '> '.$q_poster.' '.$lang_common['wrote']."\n\n".'> '.$q_message."\n";
	}
}
else if ($fid) // If a forum ID was specified in the url (new topic)
{
	$post_link = panther_link($panther_url['new_topic'], array($fid));
	$action = $lang_post['Post new topic'];
}

$page_title = array($panther_config['o_board_title'], $action);
$required_fields = array('req_email' => $lang_common['Email'], 'req_subject' => $lang_common['Subject'], 'req_message' => $lang_common['Message']);
$focus_element = array('post');

if (!$panther_user['is_guest'])
	$focus_element[] = ($fid) ? 'req_subject' : 'req_message';
else
{
	$required_fields['req_username'] = $lang_post['Guest name'];
	$focus_element[] = 'req_username';
}

if (!empty($panther_robots) && $panther_user['g_robot_test'] == '1')
	$required_fields['answer'] = $lang_common['Robot title'];

($hook = get_extensions('post_before_header')) ? eval($hook) : null;

$can_upload = false;

if ($panther_user['is_admin'])
	$can_upload = true;
else if ($panther_user['g_attach_files'] == '1' && ($cur_posting['upload'] == '1' || $cur_posting['upload'] == ''))
	$can_upload = true;

$max_size = ($panther_user['g_max_size'] == '0' && $panther_user['g_attach_files'] == '1') ? $panther_config['o_max_upload_size'] : $panther_user['g_max_size'];

define('PANTHER_ACTIVE_PAGE', 'index');
require PANTHER_ROOT.'header.php';

($hook = get_extensions('post_after_header')) ? eval($hook) : null;

$checkboxes = array();
if ($fid && $is_admmod)
	$checkboxes[] = array('name' => 'stick_topic', 'checked' => (isset($_POST['stick_topic']) ? true : false), 'title' => $lang_common['Stick topic']);

if ($fid && $cur_posting['post_polls'] != '0' && $panther_user['g_post_polls'] == '1' && $panther_config['o_polls'] == '1')
	$checkboxes[] = array('name' => 'add_poll', 'checked' => (isset($_POST['add_poll']) ? true : false), 'title' => $lang_post['Add poll']);

if (!$panther_user['is_guest'])
{
	if ($panther_config['o_smilies'] == '1')
		$checkboxes[] = array('name' => 'hide_smilies', 'checked' => (isset($_POST['hide_smilies']) ? true : false), 'title' => $lang_post['Hide smilies']);

	if ($panther_config['o_topic_subscriptions'] == '1')
	{
		$subscr_checked = false;

		// If it's a preview
		if (isset($_POST['preview']))
			$subscr_checked = isset($_POST['subscribe']) ? true : false;
		// If auto subscribed
		else if ($panther_user['auto_notify'])
			$subscr_checked = true;
		// If already subscribed to the topic
		else if ($is_subscribed)
			$subscr_checked = true;

		$checkboxes[] = array('name' => 'subscribe', 'checked' => (($subscr_checked) ? true : false), 'title' => (($is_subscribed ? $lang_post['Stay subscribed'] : $lang_post['Subscribe'])));
	}
}
else if ($panther_config['o_smilies'] == '1')
	$checkboxes[] = array('name' => 'hide_smilies', 'checked' => (isset($_POST['hide_smilies']) ? true : false), 'title' => $lang_post['Hide smilies']);

// Check to see if the topic review is to be displayed
$posts = array();
if ($tid && $panther_config['o_topic_review'] != '0')
{
	require_once PANTHER_ROOT.'include/parser.php';
	$data = array(
		':id'	=>	$tid,
	);

	$ps = $db->run('SELECT p.poster, p.message, p.hide_smilies, p.posted, u.group_id FROM '.$db->prefix.'posts AS p LEFT JOIN '.$db->prefix.'users AS u ON (p.poster=u.username) WHERE p.topic_id=:id ORDER BY p.id DESC LIMIT '.$panther_config['o_topic_review'], $data);
	foreach ($ps as $cur_post)
		$posts[] = array('username' => colourize_group($cur_post['poster'], $cur_post['group_id']), 'posted' => format_time($cur_post['posted']), 'message' => $parser->parse_message($cur_post['message'], $cur_post['hide_smilies']));
}

$render = array(
	'lang_common' => $lang_common,
	'lang_post' => $lang_post,
	'posts' => $posts,
	'errors' => $errors,
	'index_link' => panther_link($panther_url['index']),
	'forum_link' => panther_link($panther_url['forum'], array($cur_posting['id'], url_friendly($cur_posting['forum_name']))),
	'cur_posting' => $cur_posting,
	'POST' => $_POST,
	'action' => $action,
	'fid' => $fid,
	'tid' => $tid,
	'csrf_token' => generate_csrf_token(),
	'panther_config' => $panther_config,
	'message' => isset($_POST['req_message']) ? $orig_message : (isset($quote) ? $quote : ''),
	'panther_user' => $panther_user,
	'can_upload' => $can_upload,
	'checkboxes' => $checkboxes,
	'quickpost_links' => array(
		'bbcode' => panther_link($panther_url['help'], array('bbcode')),
		'url' => panther_link($panther_url['help'], array('url')),
		'img' => panther_link($panther_url['help'], array('img')),
		'smilies' => panther_link($panther_url['help'], array('smilies')),
	),
);

if (isset($cur_posting['subject']))
	$render['topic_link'] = panther_link($panther_url['topic'], array($tid, url_friendly($cur_posting['subject'])));

if (isset($_POST['preview']))
{
	require_once PANTHER_ROOT.'include/parser.php';
	$render['preview'] = $parser->parse_message($message, $hide_smilies);
}

if ($panther_user['is_guest'])
{
	$email_form_name = ($panther_config['p_force_guest_email'] == '1') ? 'req_email' : 'email';

	$render['username'] = (isset($username)) ? $username : '';
	$render['email'] = (isset($_POST[$email_form_name])) ? $email : '';
	$render['email_form_name'] = $email_form_name;
}

if ($can_upload)
	$render['max_size'] = $max_size;

if (!empty($panther_robots) && $panther_user['g_robot_test'] == '1')
{
	$id = array_rand($panther_robots);
	$render['robot_id'] = $id;
	$render['test'] = $panther_robots[$id];
}

($hook = get_extensions('post_before_submit')) ? eval($hook) : null;

$tpl = load_template('post.tpl');
echo $tpl->render($render);

require PANTHER_ROOT.'footer.php';