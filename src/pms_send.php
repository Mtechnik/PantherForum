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

if ($panther_user['is_guest'])
	message($lang_common['No permission']);

if ($panther_config['o_private_messaging'] == '0')
	message($lang_common['No permission']);

if ($panther_user['g_post_replies'] == '0')
	message($lang_common['No permission']);

require PANTHER_ROOT.'lang/'.$panther_user['language'].'/pms.php';

// Load the post.php language file
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/post.php';
check_posting_ban();

$action = isset($_GET['action']) ? $_GET['action'] : '';
$tid = isset($_GET['tid']) ? intval($_GET['tid']) : '';
$qid = isset($_GET['qid']) ? intval($_GET['qid']) : '';

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

if ($tid != '')
{
	if ($tid < 1)
		message($lang_common['Bad request']);
		
	$data = array(
		':tid'	=>	$tid,
		':uid'	=>	$panther_user['id']
	);

	$ps = $db->run('SELECT 1 FROM '.$db->prefix.'conversations AS c INNER JOIN '.$db->prefix.'pms_data AS cd ON c.id=cd.topic_id WHERE cd.topic_id=:tid AND cd.user_id=:uid AND cd.deleted=0', $data);
	if (!$ps->rowCount())
		message($lang_common['Bad request']);	// We've deleted it
	
	if ($qid)
	{
		if ($qid < 1)
			message($lang_common['Bad request'], false, '404 Not Found');
		
		$data = array(
			':id'	=>	$qid,
			':tid'	=>	$tid,
		);

		$ps = $db->select('messages', 'poster, message', $data, 'id=:id AND topic_id=:tid');
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

if (isset($_GET['uid']))
{
	$uid = isset($_GET['uid']) ? intval($_GET['uid']) : 0;
	$data = array(
		':id'	=>	$uid,
	);

	$ps = $db->select('users', 'username', $data, 'id=:id');
	if (!$ps->rowCount())
		message($lang_common['Bad request']);
	else
		$username = $ps->fetchColumn();
}
else
	$username = '';

$errors = array();
if (isset($_POST['form_sent']))
{
	($hook = get_extensions('pms_before_validation')) ? eval($hook) : null;

	$message = isset($_POST['req_message']) ? panther_trim($_POST['req_message']) : '';
	$subject = isset($_POST['req_subject']) ? panther_trim($_POST['req_subject']) : '';
	$username = isset($_POST['req_username']) ? panther_trim($_POST['req_username']) : '';
	$hide_smilies = isset($_POST['hide_smilies']) ? '1' : '0';
	
	if (!empty($panther_robots) && $panther_user['g_robot_test'] == '1')
	{
		$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		$answer = isset($_POST['answer']) ? panther_trim($_POST['answer']) : '';

		if (!isset($panther_robots[$id]) || $answer != $panther_robots[$id]['answer'])
			$errors[] = $lang_common['Robot test fail'];
	}

	if ($tid)
	{
		if ($message == '')
			$errors[] = $lang_post['No message'];
		elseif (strlen($message) > PANTHER_MAX_POSTSIZE)
			$errors[] = sprintf($lang_post['Too long message'], forum_number_format(PANTHER_MAX_POSTSIZE));
		else if ($panther_config['p_message_all_caps'] == '0' && is_all_uppercase($message) && !$panther_user['is_admmod'])
			$errors[] = $lang_post['All caps message'];

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
		
		$data = array(
			':uid'	=>	$panther_user['id'],
			':tid'	=>	$tid,
		);
		
		$ps = $db->run('SELECT cd.user_id AS uid, u.username, u.email, u.pm_enabled, u.num_pms, u.pm_notify, g.g_use_pm, g.g_pm_limit, cd.deleted FROM '.$db->prefix.'pms_data AS cd INNER JOIN '.$db->prefix.'users AS u ON cd.user_id=u.id INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id WHERE cd.user_id!=:uid AND cd.topic_id=:tid', $data);
		if (!$ps->rowCount())
			$errors[] = $lang_pm['No receivers'];
		else
		{
			foreach ($ps as $cur_user)
			{
				if (!empty($errors))
					break;
			
				if ($cur_user['deleted'] == '1')
					$errors[] = sprintf($lang_pm['User x has left'], $cur_user['username']);
				
				// Check if they have the PM enabled
				if ($cur_user['pm_enabled'] == '0' && $cur_user['g_use_pm'] == '0')
					$errors[] = sprintf($lang_pm['No PM access'], $cur_user['username']);

				// Check if they've reached their max limit
				if ($cur_user['num_pms'] + 1 >= $cur_user['g_pm_limit'] && $cur_user['g_pm_limit'] != 0)
					$errors[] = sprintf($lang_pm['Receiver inbox full'], $cur_user['username']);

				if (!$panther_user['is_admmod'])
				{
					$data = array(
						':uid'	=>	$panther_user['id'],
						':bid'	=>	$cur_user['uid'],
						':bid2'	=>	$panther_user['id'],
						':uid2'	=>	$cur_user['uid'],
					);

					$ps1 = $db->select('blocks', 1, $data, 'user_id=:uid AND block_id=:bid OR block_id=:bid2 AND user_id=:uid2');
					if ($ps1->rowCount())
						$errors[] = sprintf($lang_pm['User x has blocked'], $cur_user['username']);
				}
				
				$receivers[$cur_user['uid']] = $cur_user;
			}
		}

		$data = array(
			':id'	=>	$tid,
		);

		$ps = $db->select('conversations', 'subject', $data, 'id=:id');
		if (!$ps->rowCount())
			message($lang_common['Bad request']);
		else
			$subject = $ps->fetchColumn();

		if (empty($errors) && !isset($_POST['preview']))
		{
			$now = time();
			$insert = array(
				'poster'	=>	$panther_user['username'],
				'poster_id'	=>	$panther_user['id'],
				'poster_ip'	=>	get_remote_address(),
				'message'	=>	$message,
				'hide_smilies'	=>	$hide_smilies,
				'posted'	=>	$now,
				'topic_id'	=>	$tid,
			);
			
			$db->insert('messages', $insert);
			
			$new_pid = $db->lastInsertId($db->prefix.'messages');
			$data = array(
				':tid'	=>	$tid,
				':last_post'	=>	$now,
				':last_poster'	=>	$panther_user['username'],
				':last_post_id'	=>	$new_pid,
			);

			$db->run('UPDATE '.$db->prefix.'conversations SET last_post=:last_post, last_poster=:last_poster, num_replies=num_replies+1, last_post_id=:last_post_id WHERE id=:tid', $data);

			$update = array(
				'viewed'	=>	0,
			);
			
			$data = array(
				':tid'	=>	$tid,
				':uid'	=>	$panther_user['id'],
			);
			
			$db->update('pms_data', $update, 'topic_id=:tid AND user_id!=:uid', $data);	// Reset the topics as unread for all users but us
			require_once PANTHER_ROOT.'include/email.php';

			$message = $mailer->bbcode2email($message, -1);
			foreach ($receivers as $uid => $udata)
			{
				if ($udata['pm_notify'] == '1' && $uid != $panther_user['id'])
				{
					$info = array(
						'message' => array(
							'<username>' => $udata['username'],
							'<replier>' => $panther_user['username'],
							'<message>' => $message,
							'<pm_title>' => $subject,
							'<message_url>' => panther_link($panther_url['pms_post'], array($new_pid))
						)
					);

					$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/pm_reply.tpl', $info);
					$mailer->send($udata['email'], $mail_tpl['subject'], $mail_tpl['message']);
				}
				
				$data = array(
					':id'	=>	$uid,
				);
				
				$db->run('UPDATE '.$db->prefix.'users SET num_pms=num_pms+1 WHERE id=:id', $data);
			}
			
			$data = array(
				':id'	=>	$panther_user['id'],
			);
			
			$db->run('UPDATE '.$db->prefix.'users SET num_pms=num_pms+1 WHERE id=:id', $data);
			redirect(panther_link($panther_url['pms_post'], array($new_pid)), $lang_pm['PM sent redirect']);
		}
	}
	else
	{
		$users = array_map('panther_trim', explode(',', $username));

		if ((count($users) + 1) > $panther_config['o_max_pm_receivers']) // Add one for us as we shouldn't be included
			$errors[] = sprintf($lang_pm['Max receivers'], $panther_config['o_pm_max_receivers']);

		$receivers = array();
		foreach ($users as $user)
		{
			$data = array(
				':username'	=>	$user,
			);
				
			$ps = $db->run('SELECT u.id, u.username, u.email, u.pm_enabled, u.num_pms, u.pm_notify, g.g_use_pm, g.g_pm_limit FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id WHERE u.username=:username AND u.id>1', $data);
			if (!$ps->rowCount())
			{
				$errors[] = sprintf($lang_pm['No user x'], $user);
				continue;
			}
			else
				$cur_user = $ps->fetch();

			$receivers[$cur_user['id']] = $cur_user;
		}

		if (!isset($_POST['preview']) && $panther_user['last_post'] != '' && (time() - $panther_user['last_post']) < $panther_user['g_post_flood'])
			$errors[] = sprintf($lang_post['Flood start'], $panther_user['g_post_flood'], $panther_user['g_post_flood'] - (time() - $panther_user['last_post']));
		
		if (isset($receivers[$panther_user['id']]))	// Stop us from sending messages completely to ourselves
			$errors[] = $lang_pm['No self messages'];
		
		if ($panther_user['num_pms'] + 1 >= $panther_user['g_pm_limit'] && $panther_user['g_pm_limit'] != 0)
				$errors[] = $lang_pm['Inbox full'];

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

		if ($message == '')
			$errors[] = $lang_post['No message'];
		elseif (strlen($message) > PANTHER_MAX_POSTSIZE)
			$errors[] = sprintf($lang_post['Too long message'], forum_number_format(PANTHER_MAX_POSTSIZE));
		else if ($panther_config['p_message_all_caps'] == '0' && is_all_uppercase($message) && !$panther_user['is_admmod'])
			$errors[] = $lang_post['All caps message'];

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

		foreach ($receivers as $uid => $udata)
		{
			if (!empty($errors))
				break;

			// Check if they have the PM enabled
			if ($udata['pm_enabled'] == '0' && $udata['g_use_pm'] == '0')
				$errors[] = sprintf($lang_pm['No PM access'], $udata['username']);

			// Check if they've reached their max limit
			if ($udata['num_pms'] + 1 >= $udata['g_pm_limit'] && $udata['g_pm_limit'] != 0)
				$errors[] = sprintf($lang_pm['Receiver inbox full'], $udata['username']);

			if (!$panther_user['is_admmod'])
			{
				$data = array(
					':uid'	=>	$panther_user['id'],
					':bid'	=>	$uid,
					':bid2'	=>	$panther_user['id'],
					':uid2'	=>	$uid,
				);

				$ps = $db->select('blocks', 1, $data, 'user_id=:uid AND block_id=:bid OR block_id=:bid2 AND user_id=:uid2');
				if ($ps->rowCount())
					$errors[] = sprintf($lang_pm['User x has blocked'], $udata['username']);
			}
		}

		($hook = get_extensions('pms_after_validation')) ? eval($hook) : null;

		if (empty($errors) && !isset($_POST['preview']))
		{
			$now = time();
			$insert = array(
				'subject'	=>	$subject,
				'poster'	=>	$panther_user['username'],
				'poster_id'	=>	$panther_user['id'],
				'num_replies'	=>	0,
				'last_post'	=>	$now,
				'last_poster'	=>	$panther_user['username'],
			);

			$db->insert('conversations', $insert);
			$new_tid = $db->lastInsertId($db->prefix.'conversations');

			$insert = array(
				'poster'	=>	$panther_user['username'],
				'poster_id'	=>	$panther_user['id'],
				'poster_ip'	=>	get_remote_address(),
				'message'	=>	$message,
				'hide_smilies'	=>	$hide_smilies,
				'posted'	=>	$now,
				'topic_id'	=>	$new_tid,
			);

			$db->insert('messages', $insert);
			$new_pid = $db->lastInsertId($db->prefix.'messages');
			
			$update = array(
				'first_post_id'	=>	$new_pid,
				'last_post_id'	=>	$new_pid,
			);
			
			$data = array(
				':tid'	=>	$new_tid,
			);
			
			$db->update('conversations', $update, 'id=:tid', $data);

			// Now add us to the receivers array to create our row in the pms_data table
			$receivers[$panther_user['id']] = $panther_user;
			require_once PANTHER_ROOT.'include/email.php';

			$message = $mailer->bbcode2email($message, -1);

			// Yes, unfortunately we need a second loop here =(
			foreach ($receivers as $uid => $udata)
			{
				$insert = array(
					'topic_id'	=>	$new_tid,
					'user_id'	=>	$uid,
					'viewed'	=>	(($uid == $panther_user['id']) ? 1 : 0),	// If we're the current receiver, then we've seen the message
				);

				$db->insert('pms_data', $insert);

				$data = array(
					':id'	=>	$uid,
				);

				$db->run('UPDATE '.$db->prefix.'users SET num_pms=num_pms+1 WHERE id=:id', $data);
				
				if ($udata['pm_notify'] == '1' && $uid != $panther_user['id'])
				{
					$info = array(
						'message' => array(
							'<username>' => $udata['username'],
							'<sender>' => $panther_user['username'],
							'<message>' => $message,
							'<pm_title>' => $subject,
							'<message_url>' => panther_link($panther_url['pms_post'], array($new_pid)),
						)
					);

					$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/new_pm.tpl', $info);
					$mailer->send($udata['email'], $mail_tpl['subject'], $mail_tpl['message']);
				}
			}

			redirect(panther_link($panther_url['pms_view'], array($new_tid)), $lang_pm['PM sent redirect']);
		}
	}
}

$required_fields = array('req_message' => $lang_common['Message']);
$focus_element = array('post');

if (!$tid)
{
	$required_fields['req_subject'] = $lang_common['Subject'];
	$required_fields['req_username'] = $lang_common['Username'];
}
else
{
	$data = array(
		':tid'	=>	$tid,
		':uid'	=>	$panther_user['id'],
	);
	
	$ps = $db->select('pms_data', 'folder_id', $data, 'topic_id=:tid AND user_id=:uid AND deleted=0');
	if (!$ps->rowCount())	// Then we're not a part of this conversation (or have deleted it)
		message($lang_common['Bad request']);
	else
		$folder_id = $ps->fetchColumn();

	if ($folder_id == 3)	// If we've archived this folder
		message($lang_common['Bad request']);
}

if (!empty($panther_robots) && $panther_user['g_robot_test'] == '1')
	$required_fields['answer'] = $lang_common['Robot title'];

$focus_element[] = ($tid) ? 'req_message' : 'req_subject';

$page_title = array($panther_config['o_board_title'], $lang_common['PM'], $lang_pm['Send message']);
define('PANTHER_ALLOW_INDEX', 1);
define('PANTHER_ACTIVE_PAGE', 'pm');
require PANTHER_ROOT.'header.php';

$render = array(
	'tid' => $tid,
	'errors' => $errors,
	'preview' => (isset($_POST['preview'])) ? true : false,
	'index_link' => panther_link($panther_url['index']),
	'inbox_link' => panther_link($panther_url['inbox']),
	'pm_menu' => generate_pm_menu('send'),
	'form_action' => (!$tid) ? panther_link($panther_url['send_message']) : panther_link($panther_url['pms_reply'], array($tid)),
	'subject' => (isset($subject)) ? $subject : '',
	'message' => isset($_POST['req_message']) ? $message : (isset($quote) ? $quote : ''),
	'lang_pm' => $lang_pm,
	'lang_common' => $lang_common,
	'panther_config' => $panther_config,
	'panther_user' => $panther_user,
	'lang_post' => $lang_post,
	'csrf_token' => generate_csrf_token('post.php'),
	'quickpost_links' => array(
		'bbcode' => panther_link($panther_url['help'], array('bbcode')),
		'url' => panther_link($panther_url['help'], array('url')),
		'img' => panther_link($panther_url['help'], array('img')),
		'smilies' => panther_link($panther_url['help'], array('smilies')),
	),
);

($hook = get_extensions('pms_before_submit')) ? eval($hook) : null;

if (isset($_POST['preview']))
{
	require_once PANTHER_ROOT.'include/parser.php';
	$render['preview'] = $parser->parse_message($message, $hide_smilies);
}

if (!empty($panther_robots) && $panther_user['g_robot_test'] == '1')
{
	$id = array_rand($panther_robots);
	$render['robot_id'] = $id;
	$render['test'] = $panther_robots[$id];
}

$tpl = load_template('send_message.tpl');
echo $tpl->render($render);
require PANTHER_ROOT.'footer.php';