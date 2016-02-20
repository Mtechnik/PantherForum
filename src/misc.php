<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

if (isset($_GET['action']))
	define('PANTHER_QUIET_VISIT', 1);

if (!defined('PANTHER_ROOT'))
{
	define('PANTHER_ROOT', __DIR__.'/');
	require PANTHER_ROOT.'include/common.php';
}

// Load the misc.php language file
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/misc.php';
$action = isset($_GET['action']) ? $_GET['action'] : null;

if ($action == 'rules')
{
	if ($panther_config['o_rules'] == '0' || ($panther_user['is_guest'] && $panther_user['g_read_board'] == '0' && $panther_config['o_regs_allow'] == '0'))
		message($lang_common['Bad request'], false, '404 Not Found');

	// Load the register.php language file
	require PANTHER_ROOT.'lang/'.$panther_user['language'].'/register.php';

	$page_title = array($panther_config['o_board_title'], $lang_register['Forum rules']);
	define('PANTHER_ACTIVE_PAGE', 'rules');
	require PANTHER_ROOT.'header.php';
	
	$tpl = load_template('forum_rules.tpl');
	echo $tpl->render(
		array(
			'lang_register' => $lang_register,
			'panther_config' => $panther_config,
		)
	);

	require PANTHER_ROOT.'footer.php';
}
else if ($action == 'markread')
{
    confirm_referrer('index.php');
	if ($panther_user['is_guest'])
		message($lang_common['No permission'], false, '403 Forbidden');

	$update = array(
		'last_visit' => $panther_user['logged'],
	);

	$data = array(
		':id' => $panther_user['id'],
	);

	$db->update('users', $update, 'id=:id', $data);

	// Reset tracked topics
	set_tracked_topics(null);
	redirect(panther_link($panther_url['index']), $lang_misc['Mark read redirect']);
}

// Mark the topics/posts in a forum as read?
else if ($action == 'markforumread')
{
    confirm_referrer('viewforum.php');
	if ($panther_user['is_guest'])
		message($lang_common['No permission'], false, '403 Forbidden');

	$fid = isset($_GET['fid']) ? intval($_GET['fid']) : 0;
	if ($fid < 1)
		message($lang_common['Bad request'], false, '404 Not Found');
	
	$data = array(
		':id'	=>	$fid,
	);
	
	$ps = $db->select('forums', 'forum_name', $data, 'id=:id');
	$forum_name = url_friendly($ps->fetchColumn());

	$tracked_topics = get_tracked_topics();
	$tracked_topics['forums'][$fid] = time();
	set_tracked_topics($tracked_topics);
	redirect(panther_link($panther_url['forum'], array($fid, $forum_name)), $lang_misc['Mark forum read redirect']);
}
else if (isset($_GET['email']))
{
	if ($panther_user['is_guest'] || $panther_user['g_send_email'] == '0')
		message($lang_common['No permission'], false, '403 Forbidden');

	$recipient_id = intval($_GET['email']);
	if ($recipient_id < 2)
		message($lang_common['Bad request'], false, '404 Not Found');

	$data = array(
		':id'	=>	$recipient_id,
	);

	$ps = $db->select('users', 'username, email, email_setting', $data, 'id=:id');
	if (!$ps->rowCount())
		message($lang_common['Bad request'], false, '404 Not Found');

	list($recipient, $recipient_email, $email_setting) = $ps->fetch(PDO::FETCH_NUM);

	if ($email_setting == 2 && !$panther_user['is_admmod'])
		message($lang_misc['Form email disabled']);

	$errors = array();
	if (isset($_POST['form_sent']))
	{
		confirm_referrer('misc.php');

		// Clean up message and subject from POST
		$subject = isset($_POST['req_subject']) ? panther_trim($_POST['req_subject']) : '';
		$message = isset($_POST['req_message']) ? panther_trim($_POST['req_message']) : '';

		if ($subject == '')
			$errors[] = $lang_misc['No email subject'];
		else if ($message == '')
			$errors[] = $lang_misc['No email message'];
		// Here we use strlen() not panther_strlen() as we want to limit the post to PANTHER_MAX_POSTSIZE bytes, not characters
		else if (strlen($message) > PANTHER_MAX_POSTSIZE)
			$errors[] = $lang_misc['Too long email message'];

		if ($panther_user['last_email_sent'] != '' && (time() - $panther_user['last_email_sent']) < $panther_user['g_email_flood'] && (time() - $panther_user['last_email_sent']) >= 0)
			$errors[] = sprintf($lang_misc['Email flood'], $panther_user['g_email_flood'], $panther_user['g_email_flood'] - (time() - $panther_user['last_email_sent']));
		
		($hook = get_extensions('send_email_after_validation')) ? eval($hook) : null;

		if (empty($errors))
		{
			require PANTHER_ROOT.'include/email.php';
			
			$info = array(
				'subject' => array(
					'<mail_subject>' => $subject,
				),
				'message' => array(
					'<sender>' => $panther_user['username'],
					'<board_title>' => $panther_config['o_board_title'],
					'<mail_message>' => $message,
				)
			);

			$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/form_email.tpl', $info);
			$mailer->send($recipient_email, $mail_tpl['subject'], $mail_tpl['message'], $panther_user['email'], $panther_user['username']);

			$update = array(
				'last_email_sent'	=>	time(),
			);

			$data = array(
				':id'	=>	$panther_user['id'],
			);

			$db->update('users', $update, 'id=:id', $data);

			// Try to determine if the data in redirect_url is valid (if not, we redirect to index.php after the email is sent)
			$redirect_url = validate_redirect($_POST['redirect_url'], panther_link($panther_url['index']));
			redirect($redirect_url, $lang_misc['Email sent redirect']);
		}
	}

	// Try to determine if the data in HTTP_REFERER is valid (if not, we redirect to the user's profile after the email is sent)
	if (!empty($_SERVER['HTTP_REFERER']))
		$redirect_url = validate_redirect($_SERVER['HTTP_REFERER'], null);

	if (!isset($redirect_url))
		$redirect_url = panther_link($panther_url['profile'], array($recipient_id));
	else if (preg_match('%viewtopic\.php\?pid=(\d+)$%', $redirect_url, $matches))
		$redirect_url .= '#p'.$matches[1];

	$page_title = array($panther_config['o_board_title'], sprintf($lang_misc['Send email to'], $recipient));
	$required_fields = array('req_subject' => $lang_misc['Email subject'], 'req_message' => $lang_misc['Email message']);
	$focus_element = array('email', 'req_subject');
	define('PANTHER_ACTIVE_PAGE', 'index');
	require PANTHER_ROOT.'header.php';

	$tpl = load_template('send_email.tpl');
	echo $tpl->render(
		array(
			'lang_common' => $lang_common,
			'lang_misc' => $lang_misc,
			'recipient' => $recipient,
			'form_action' => panther_link($panther_url['email'], array($recipient_id)),
			'csrf_token' => generate_csrf_token(),
			'redirect_url' => $redirect_url,
			'errors' => $errors,
			'subject' => isset($subject) ? $subject : '',
			'message' => isset($message) ? $message : ''
		)
	);

	require PANTHER_ROOT.'footer.php';
}
else if (isset($_GET['report']))
{
	if ($panther_user['is_guest'])
		message($lang_common['No permission'], false, '403 Forbidden');

	$post_id = intval($_GET['report']);
	if ($post_id < 1)
		message($lang_common['Bad request'], false, '404 Not Found');

	$errors = array();
	if (isset($_POST['form_sent']))
	{
		// Make sure they got here from the site
		confirm_referrer('misc.php');
		
		// Clean up reason from POST
		$reason = isset($_POST['req_reason']) ? panther_linebreaks(panther_trim($_POST['req_reason'])) : '';
		if ($reason == '')
			$errors[] = $lang_misc['No reason'];
		else if (strlen($reason) > 65535) // TEXT field can only hold 65535 bytes
			$errors[] = $lang_misc['Reason too long'];

		if ($panther_user['last_report_sent'] != '' && (time() - $panther_user['last_report_sent']) < $panther_user['g_report_flood'] && (time() - $panther_user['last_report_sent']) >= 0)
			$errors[] = sprintf($lang_misc['Report flood'], $panther_user['g_report_flood'], $panther_user['g_report_flood'] - (time() - $panther_user['last_report_sent']));
		
		($hook = get_extensions('report_after_validation')) ? eval($hook) : null;

		if (empty($errors))
		{
			// Get the topic ID
			$data = array(
				':id'	=>	$post_id,
			);

			$ps = $db->select('posts', 'topic_id', $data, 'id=:id');
			if (!$ps->rowCount())
				message($lang_common['Bad request'], false, '404 Not Found');

			$topic_id = $ps->fetchColumn();
			$data = array(
				':id'	=>	$topic_id,
			);

			// Get the subject and forum ID
			$ps = $db->run('SELECT t.subject, t.forum_id, f.forum_name, f.password FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON t.forum_id=f.id WHERE t.id=:id', $data);
			if (!$ps->rowCount())
				message($lang_common['Bad request'], false, '404 Not Found');

			list($subject, $forum_id, $forum_name, $forum_password) = $ps->fetch(PDO::FETCH_NUM);

			if ($forum_password != '')
				check_forum_login_cookie($forum_id, $forum_password);

			// Should we use the internal report handling?
			if ($panther_config['o_report_method'] == '0' || $panther_config['o_report_method'] == '2')
			{
				$insert = array(
					'post_id'	=>	$post_id,
					'topic_id'	=>	$topic_id,
					'forum_id'	=>	$forum_id,
					'reported_by'	=>	$panther_user['id'],
					'created'	=>	time(),
					'message'	=>	$reason,
				);
				
				$db->insert('reports', $insert);
			}

			// Should we email the report?
			if ($panther_config['o_report_method'] == '1' || $panther_config['o_report_method'] == '2')
			{
				// We send it to the complete mailing-list in one swoop
				if ($panther_config['o_mailing_list'] != '')
				{
					require PANTHER_ROOT.'include/email.php';
					
					$info = array(
						'subject' => array(
							'<forum_id>' => $forum_id,
							'<topic_subject>' => $subject,
						),
						'message' => array(
							'<username>' => $panther_user['username'],
							'<post_url>' => panther_link($panther_url['post'], array($post_id)),
							'<reason>' => $reason,
						)
					);

					$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/new_report.tpl', $info);
					$mailer->send($panther_config['o_mailing_list'], $mail_tpl['subject'], $mail_tpl['message']);
				}
			}
			
			$update = array(
				'last_report_sent'	=>	time(),
			);
			
			$data = array(
				':id'	=>	$panther_user['id'],
			);

			$db->update('users', $update, 'id=:id', $data);
			redirect(panther_link($panther_url['forum'], array($forum_id, url_friendly($forum_name))), $lang_misc['Report redirect']);
		}
	}

	// Fetch some info about the post, the topic and the forum
	$data = array(
		':gid'	=>	$panther_user['g_id'],
		':pid'	=>	$post_id,
	);

	$ps = $db->run('SELECT f.id AS fid, f.forum_name, f.password, f.protected, f.moderators, t.id AS tid, t.subject, t.archived, p.poster_id FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND p.id=:pid', $data);
	if (!$ps->rowCount())
		message($lang_common['Bad request'], false, '404 Not Found');

	$cur_post = $ps->fetch();
	
	if ($cur_post['password'] != '')
		check_forum_login_cookie($cur_post['fid'], $cur_post['password']);

	$moderators = ($cur_post['moderators'] != '') ? unserialize($cur_post['moderators']) : array();
	if ($cur_post['protected'] == '1' && $panther_user['id'] != $cur_post['poster_id'] && $panther_user['g_global_moderator'] != 1 && !$panther_user['is_admin'] && !in_array($panther_user['username'], $moderators))
		message($lang_common['Bad request'], false, '404 Not Found');

	if ($panther_config['o_censoring'] == '1')
		$cur_post['subject'] = censor_words($cur_post['subject']);
	
	if ($cur_post['archived'] == '1')
		message($lang_misc['Topic archived']);

	$page_title = array($panther_config['o_board_title'], $lang_misc['Report post']);
	$required_fields = array('req_reason' => $lang_misc['Reason']);
	$focus_element = array('report', 'req_reason');
	define('PANTHER_ACTIVE_PAGE', 'index');
	require PANTHER_ROOT.'header.php';
	
	$tpl = load_template('report.tpl');
	echo $tpl->render(
		array(
			'lang_common' => $lang_common,
			'index_link' => panther_link($panther_url['index']),
			'cur_post' => $cur_post,
			'forum_link' => panther_link($panther_url['forum'], array($cur_post['fid'], url_friendly($cur_post['forum_name']))),
			'post_link' => panther_link($panther_url['post'], array($post_id)),
			'lang_misc' => $lang_misc,
			'form_action' => panther_link($panther_url['report'], array($post_id)),
			'csrf_token' => generate_csrf_token(),
			'errors' => $errors,
			'message' => isset($_POST['req_reason']) ? $_POST['req_reason'] : '',
		)
	);

	require PANTHER_ROOT.'footer.php';
}
else if ($action == 'subscribe')
{
	confirm_referrer('viewforum.php');
	if ($panther_user['is_guest'])
		message($lang_common['No permission'], false, '403 Forbidden');

	$topic_id = isset($_GET['tid']) ? intval($_GET['tid']) : 0;
	$forum_id = isset($_GET['fid']) ? intval($_GET['fid']) : 0;
	if ($topic_id < 1 && $forum_id < 1)
		message($lang_common['Bad request'], false, '404 Not Found');

	if ($topic_id)
	{
		if ($panther_config['o_topic_subscriptions'] != '1')
			message($lang_common['No permission'], false, '403 Forbidden');

		// Make sure the user can view the topic
		$data = array(
			':gid'	=>	$panther_user['g_id'],
			':tid'	=>	$topic_id,
		);

		$ps = $db->run('SELECT t.subject, t.poster, f.password, f.protected, f.moderators, f.id AS fid FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON t.forum_id=f.id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=t.forum_id AND fp.group_id=:gid) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id=:tid AND t.moved_to IS NULL', $data);
		if (!$ps->rowCount())
			message($lang_common['Bad request'], false, '404 Not Found');
		else
			$cur_topic = $ps->fetch();

		$moderators = ($cur_topic['moderators'] != '') ? unserialize($cur_topic['moderators']) : array();
		if ($cur_topic['password'] != '')
			check_forum_login_cookie($cur_topic['fid'], $cur_topic['password']);

		if ($cur_topic['protected'] == '1' && $panther_user['username'] != $cur_topic['poster'] && $panther_user['g_global_moderator'] != 1 && !$panther_user['is_admin'] && !in_array($panther_user['username'], $moderators))
			message($lang_common['Bad request'], false, '404 Not Found');

		$data = array(
			':id'	=>	$panther_user['id'],
			':tid'	=>	$topic_id,
		);

		$ps = $db->select('topic_subscriptions', 1, $data, 'user_id=:id AND topic_id=:tid');
		if ($ps->rowCount())
			message($lang_misc['Already subscribed topic']);

		$insert = array(
			'user_id'	=>	$panther_user['id'],
			'topic_id'	=>	$topic_id,
		);

		$db->insert('topic_subscriptions', $insert);
		redirect(panther_link($panther_url['topic'], array($topic_id, url_friendly($cur_topic['subject']))), $lang_misc['Subscribe redirect']);
	}

	if ($forum_id)
	{
		if ($panther_config['o_forum_subscriptions'] != '1')
			message($lang_common['No permission'], false, '403 Forbidden');

		// Make sure the user can view the forum (and if a password is present, they know that too)
		$data = array(
			':gid'	=>	$panther_user['g_id'],
			':fid'	=>	$forum_id,
		);

		$ps = $db->run('SELECT f.forum_name, f.password FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.id=:fid', $data);
		if (!$ps->rowCount())
			message($lang_common['Bad request'], false, '404 Not Found');
		else
			$cur_forum = $ps->fetch();

		if ($cur_forum['password'] != '')
			check_forum_login_cookie($forum_id, $cur_forum['password']);

		$data = array(
			':id'	=>	$panther_user['id'],
			':fid'	=>	$forum_id,
		);

		$ps = $db->select('forum_subscriptions', 1, $data, 'user_id=:id AND forum_id=:fid');
		if ($ps->rowCount())
			message($lang_misc['Already subscribed forum']);

		$insert = array(
			'user_id'	=>	$panther_user['id'],
			'forum_id'	=>	$forum_id,
		);

		$db->insert('forum_subscriptions', $insert);
		redirect(panther_link($panther_url['forum'], array($forum_id, url_friendly($cur_forum['forum_name']))), $lang_misc['Subscribe redirect']);
	}
}
else if ($action == 'unsubscribe')
{
	confirm_referrer('viewforum.php', false);
	if ($panther_user['is_guest'])
		message($lang_common['No permission'], false, '403 Forbidden');

	$topic_id = isset($_GET['tid']) ? intval($_GET['tid']) : 0;
	$forum_id = isset($_GET['fid']) ? intval($_GET['fid']) : 0;
	if ($topic_id < 1 && $forum_id < 1)
		message($lang_common['Bad request'], false, '404 Not Found');

	if ($topic_id)
	{
		if ($panther_config['o_topic_subscriptions'] != '1')
			message($lang_common['No permission'], false, '403 Forbidden');

		$data = array(
			':id'	=>	$topic_id,
		);
		
		$ps = $db->run('SELECT t.subject, f.password, f.id AS fid FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON t.forum_id=f.id WHERE t.id=:id', $data);
		if (!$ps->rowCount())
			message($lang_common['Bad request']);
		else
			$cur_topic = $ps->fetch();
		
		if ($cur_topic['password'] != '')
			check_forum_login_cookie($cur_topic['fid'], $cur_topic['password']);
		
		$data = array(
			':id'	=>	$panther_user['id'],
			':tid'	=>	$topic_id,
		);

		$ps = $db->select('topic_subscriptions', 1, $data, 'user_id=:id AND topic_id=:tid');
		if (!$ps->rowCount())
			message($lang_misc['Not subscribed topic']);
		
		$data = array(
			':id'	=>	$panther_user['id'],
			':tid'	=>	$topic_id,
		);

		$db->delete('topic_subscriptions', 'user_id=:id AND topic_id=:tid', $data);
		redirect(panther_link($panther_url['topic'], array($topic_id, url_friendly($cur_topic['subject']))), $lang_misc['Unsubscribe redirect']);
	}

	if ($forum_id)
	{
		if ($panther_config['o_forum_subscriptions'] != '1')
			message($lang_common['No permission'], false, '403 Forbidden');
		
		$data = array(
			':id'	=>	$forum_id
		);

		$ps = $db->select('forums', 'forum_name, password', $data, 'id=:id');
		if (!$ps->rowCount())
			message($lang_common['Bad request']);
		else
			$cur_forum = $ps->fetch();

		if ($cur_forum['password'] != '')
			check_forum_login_cookie($forum_id, $cur_forum['password']);

		$data = array(
			':id'	=>	$panther_user['id'],
			':fid'	=>	$forum_id,
		);

		$ps = $db->select('forum_subscriptions', 1, $data, 'user_id=:id AND forum_id=:fid');
		if (!$ps->rowCount())
			message($lang_misc['Not subscribed forum']);
		
		$data = array(
			':id'	=>	$panther_user['id'],
			':fid'	=>	$forum_id,
		);

		$db->delete('forum_subscriptions', 'user_id=:id AND forum_id=:fid', $data);
		redirect(panther_link($panther_url['forum'], array($forum_id, url_friendly($cur_forum['forum_name']))), $lang_misc['Unsubscribe redirect']);
	}
}
else if ($action == 'leaders')
{
	if ($panther_user['g_read_board'] == '0')
		message($lang_common['No view'], false, '403 Forbidden');
	
	if ($panther_user['g_view_users'] == '0')
		message($lang_common['No permission'], false, '403 Forbidden');
	
	if (!defined('PANTHER_FP_LOADED'))
	{
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';
		
		generate_perms_cache();
		require FORUM_CACHE_DIR.'cache_perms.php';
	}

	// Load the userlist.php language file
	require PANTHER_ROOT.'lang/'.$panther_user['language'].'/online.php';
	
	$data = array(
		':admin' => PANTHER_ADMIN,
	);

	define('PANTHER_ACTIVE_PAGE', 'leaders');
	$page_title = array($panther_config['o_board_title'], $lang_common['User list'], $lang_online['the team']);
	require PANTHER_ROOT.'header.php';

	$administrators = array();
	$ps = $db->run('SELECT u.id AS id, u.username, u.group_id, o.currently FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id LEFT JOIN '.$db->prefix.'online AS o ON o.user_id=u.id WHERE u.group_id=:admin OR g.g_admin=1', $data);
	foreach ($ps as $user_data)
	{
		$administrators[$user_data['id']] = array(
			'username' => colourize_group($user_data['username'], $user_data['group_id'], $user_data['id']),
		);

		if ($panther_config['o_users_online'] == '1')
			$administrators[$user_data['id']]['location'] = generate_user_location((($user_data['currently'] == '' ? '-' : $user_data['currently'])), $lang_online, $user_data['username']);
	}

	$global_moderators = array();
	$ps = $db->run('SELECT u.id AS id, u.username, u.group_id, o.currently FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id LEFT JOIN '.$db->prefix.'online AS o ON o.user_id=u.id WHERE g.g_moderator=1 AND g.g_global_moderator=1 AND g.g_admin=0');
	foreach ($ps as $user_data)
	{
		$global_moderators[$user_data['id']] = array(
			'username' => colourize_group($user_data['username'], $user_data['group_id'], $user_data['id']),
		);

		if ($panther_config['o_users_online'] == '1')
			$global_moderators[$user_data['id']]['location'] = generate_user_location((($user_data['currently'] == '' ? '-' : $user_data['currently'])), $lang_online, $user_data['username']);
	}
	
	$data = array(
		':id'	=>	$panther_user['g_id'],
	);

	$moderators = array();

	$ps = $db->run('SELECT u.id AS id, u.username, u.group_id, o.currently FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id LEFT JOIN '.$db->prefix.'online AS o ON o.user_id=u.id WHERE g.g_moderator=1 AND g.g_global_moderator=0 AND g.g_admin=0');
	foreach ($ps as $user_data)
	{
		$total = 0;
		$forums = array();
		foreach ($panther_forums as $cur_forum)
		{
			$forum_moderators = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();
			if (!isset($perms[$panther_user['g_id'].'_'.$cur_forum['id']]))
				$perms[$panther_user['g_id'].'_'.$cur_forum['id']] = $perms['_'];

			if (in_array($user_data['id'], $forum_moderators) && ($perms[$panther_user['g_id'].'_'.$cur_forum['id']]['read_forum'] == '1' || is_null($perms[$panther_user['g_id'].'_'.$cur_forum['id']]['read_forum'])))
			{
				$forums[] = array('forum_id' => $cur_forum['id'], 'forum_name' => $cur_forum['forum_name']);
				++$total;
			}
		}

		$moderators[$user_data['id']] = array(
			'username' => colourize_group($user_data['username'], $user_data['group_id'], $user_data['id']),
			'total' => $total,
			'forums' => $forums,
		);
		
		if ($panther_config['o_users_online'] == '1')
			$moderators[$user_data['id']]['location'] = generate_user_location($user_data['currently'], $lang_online, $user_data['username']);
	}
	
	$tpl = load_template('leaders.tpl');
	echo $tpl->render(
		array(
			'lang_online' => $lang_online,
			'lang_common' => $lang_common,
			'global_moderators' => $global_moderators,
			'administrators' => $administrators,
			'moderators' => $moderators,
			'action' => panther_link($panther_url['forum_noid']),
			'panther_config' => $panther_config,
			'location' => panther_link($panther_url['forum'], array("'+this.options[this.selectedIndex].value)+'", 'forum-name')),
		)
	);

	require PANTHER_ROOT.'footer.php';
}
else
	message($lang_common['Bad request'], false, '404 Not Found');