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

if ($panther_user['is_guest'])
	message($lang_common['No permission']);

if ($panther_config['o_private_messaging'] == '0')
	message($lang_common['No permission']);

require PANTHER_ROOT.'lang/'.$panther_user['language'].'/pms.php';

// Load the post.php language file
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/post.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

if ($action == 'edit')
{
	if ($pid < 1)
		message($lang_common['Bad request']);
	
	$data = array(
		':id'	=>	$pid,
		':uid'	=>	$panther_user['id']
	);

	$ps = $db->run('SELECT c.subject, c.first_post_id, c.id, c.num_replies+1 AS num_replies, cd.folder_id, m.message, m.hide_smilies, m.poster_id FROM '.$db->prefix.'messages AS m INNER JOIN '.$db->prefix.'conversations AS c ON m.topic_id=c.id INNER JOIN '.$db->prefix.'pms_data AS cd ON c.id=cd.topic_id WHERE m.id=:id AND cd.user_id=:uid AND cd.deleted=0', $data);
	if (!$ps->rowCount())
		message($lang_common['Bad request']);	// We've deleted it
	else
		$cur_topic = $ps->fetch();
	
	if (($panther_user['g_edit_posts'] == '0' || $cur_topic['poster_id'] != $panther_user['id']) && !$panther_user['is_admmod'])
		message($lang_common['No permission']);
	
	if ($cur_topic['folder_id'] == 3)	// Then we've archived it
		message($lang_common['Bad request']);
	
	$can_edit_subject = $pid == $cur_topic['first_post_id'] && $panther_user['g_edit_subject'] == '1';

	$errors = array();
	if (isset($_POST['form_sent']))
	{
		confirm_referrer('pms_misc.php');
		
		($hook = get_extensions('pms_edit_before_validation')) ? eval($hook) : null;

		// If it's a topic it must contain a subject
		if ($can_edit_subject)
		{
			$data = array(
				':tid'	=>	$cur_topic['id'],
				':uid' => $panther_user['id'],
			);

			$conversation_users = $conversation_data = array();
			$ps = $db->run('SELECT cd.user_id, cd.deleted, u.username FROM '.$db->prefix.'pms_data AS cd INNER JOIN '.$db->prefix.'users AS u ON cd.user_id=u.id WHERE cd.topic_id=:tid AND cd.user_id!=:uid', $data);
			foreach ($ps as $user_data)
			{
				$conversation_users[utf8_strtolower($user_data['username'])] = $user_data['user_id'];
				$conversation_data[$user_data['user_id']] = $user_data['deleted'];
			}

			$subject = isset($_POST['req_subject']) ? panther_trim($_POST['req_subject']) : '';
			$username = isset($_POST['req_username']) ? panther_trim($_POST['req_username']) : '';

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

			$users = array_map('utf8_strtolower', array_map('panther_trim', explode(',', $username)));

			if ((count($users) + 1) > $panther_config['o_max_pm_receivers']) // Add one for us as we shouldn't be included
				$errors[] = sprintf($lang_pm['Max receivers'], $panther_config['o_pm_max_receivers']);

			$removed_users = array_diff(array_keys($conversation_users), $users);
			if (!empty($removed_users))
			{
				foreach ($removed_users as $cur_user)
				{
					$uid = $conversation_users[utf8_strtolower($cur_user)];
					$data = array(
						':uid' => $uid,
						':tid' => $cur_topic['id'],
					);

					$db->delete('pms_data', 'user_id=:uid AND topic_id=:tid', $data);
					
					$data = array(
						':id' => $uid,
					);

					if ($conversation_data[$uid] == '1')
						continue; // If they're already deleted the message then the next part will knock their counter out

					$db->run('UPDATE '.$db->prefix.'users SET num_pms=num_pms-'.$cur_topic['num_replies'].' WHERE id=:id', $data);
				}
			}

			$receivers = array();
			foreach ($users as $user)
			{
				if (!empty($errors))
					break;

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

				if (!in_array($cur_user['id'], $conversation_users))
				{
					// Check if they have the PM enabled
					if ($cur_user['pm_enabled'] == '0' || $cur_user['g_use_pm'] == '0')
						$errors[] = sprintf($lang_pm['No PM access'], $cur_user['username']);

					// Check if they've reached their max limit
					if ($cur_user['num_pms'] + $cur_topic['num_replies'] >= $cur_user['g_pm_limit'] && $cur_user['g_pm_limit'] != 0)
						$errors[] = sprintf($lang_pm['Receiver inbox full'], $cur_user['username']);

					if (!$panther_user['is_admmod'])
					{
						$data = array(
							':uid'	=>	$panther_user['id'],
							':bid'	=>	$cur_user['id'],
							':bid2'	=>	$panther_user['id'],
							':uid2'	=>	$cur_user['id'],
						);

						$ps = $db->select('blocks', 1, $data, 'user_id=:uid AND block_id=:bid OR block_id=:bid2 AND user_id=:uid2');
						if ($ps->rowCount())
							$errors[] = sprintf($lang_pm['User x has blocked'], $cur_user['username']);
					}

					$receivers[$cur_user['id']] = $cur_user;
				}
			}
		}

		// Clean up message from POST
		$message = isset($_POST['req_message']) ? panther_linebreaks(panther_trim($_POST['req_message'])) : '';

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
		
		// Replace four-byte characters (MySQL cannot handle them)
		$message = strip_bad_multibyte_chars($message);
		
		if (empty($errors))
		{
			if ($can_edit_subject)
			{
				$update = array(
					'subject'	=>	$subject,
				);

				$data = array(
					':id'	=>	$cur_topic['id'],
				);

				// Update the topic and any redirect topics
				$db->update('conversations', $update, 'id=:id', $data);
				require PANTHER_ROOT.'include/email.php';

				foreach ($receivers as $uid => $udata)
				{
					$insert = array(
						'topic_id'	=>	$cur_topic['id'],
						'user_id'	=>	$uid,
					);

					$db->insert('pms_data', $insert);
					$data = array(
						':id'	=>	$uid,
					);

					$db->run('UPDATE '.$db->prefix.'users SET num_pms=num_pms+'.$cur_topic['num_replies'].' WHERE id=:id', $data);
					if ($udata['pm_notify'] == '1')
					{
						$info = array(
							'message' => array(
								'<username>' => $udata['username'],
								'<replier>' => $panther_user['username'],
								'<pm_title>' => $subject,
								'<message_url>' => panther_link($panther_url['pms_post'], array($pid)),
							)
						);

						$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/pm_addition.tpl', $info);
						$mailer->send($udata['email'], $mail_tpl['subject'], $mail_tpl['message']);
					}
				}
			}

			$update = array(
				'message'	=>	$message,
				'hide_smilies'	=>	$hide_smilies,
			);
			
			if (!isset($_POST['silent']) || !$panther_user['is_admmod'])
			{
				$update['edited'] = time();
				$update['edited_by'] = $panther_user['username'];
			}
			
			$data = array(
				':id'	=>	$pid,
			);

			// Update the post
			$db->update('messages', $update, 'id=:id', $data);
			redirect(panther_link($panther_url['pms_post'], array($pid)), $lang_post['Edit redirect']);
		}
	}
	else
	{
		$data = array(
			':uid'	=>	$panther_user['id'],
			':tid'	=>	$cur_topic['id'],
		);

		$users = array();
		$ps = $db->run('SELECT cd.user_id AS uid, u.username, u.email, u.pm_enabled, u.num_pms, u.pm_notify, g.g_use_pm, g.g_pm_limit, cd.deleted FROM '.$db->prefix.'pms_data AS cd INNER JOIN '.$db->prefix.'users AS u ON cd.user_id=u.id INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id WHERE cd.user_id!=:uid AND cd.topic_id=:tid', $data);
		foreach ($ps as $cur_user)
			$users[] = $cur_user['username'];

		$username = count($users) ? implode(', ', $users) : '';
	}

	($hook = get_extensions('pm_edit_before_header')) ? eval($hook) : null;
	
	// Tell header.php we should use the editor
	define('POSTING', 1);

	$page_title = array($panther_config['o_board_title'], $lang_common['PM'], $lang_pm['Edit message']);
	$required_fields = array('req_subject' => $lang_common['Subject'], 'req_message' => $lang_common['Message']);
	$focus_element = array('edit', 'req_message');
	define('PANTHER_ALLOW_INDEX', 1);
	define('PANTHER_ACTIVE_PAGE', 'pm');
	require PANTHER_ROOT.'header.php';
	
	$checkboxes = array();
	if ($panther_config['o_smilies'] == '1')
		$checkboxes[] = array('name' => 'hide_smilies', 'title' => $lang_post['Hide smilies'], 'checked' => ((isset($_POST['hide_smilies']) || $cur_topic['hide_smilies'] == '1') ? true : false));

	if ($panther_user['is_admmod'])
		$checkboxes[] = array('name' => 'silent', 'checked' => (((isset($_POST['form_sent']) && isset($_POST['silent'])) || !isset($_POST['form_sent'])) ? true : false), 'title' => $lang_post['Silent edit']);
	
	$tpl = load_template('edit_message.tpl');
	echo $tpl->render(
		array(
			'errors' => $errors,
			'lang_common' => $lang_common,
			'lang_post' => $lang_post,
			'lang_pm' => $lang_pm,
			'pm_menu' => generate_pm_menu('send'),
			'inbox_link' => panther_link($panther_url['inbox']),
			'index_link' => panther_link($panther_url['index']),
			'post_link' => panther_link($panther_url['pms_post'], array($pid)),
			'cur_topic' => $cur_topic,
			'form_action' => panther_link($panther_url['pms_edit'], array($pid)),
			'csrf_token' => generate_csrf_token(),
			'message' => isset($_POST['req_message']) ? $message : $cur_topic['message'],
			'subject' => isset($_POST['req_subject']) ? $_POST['req_subject'] : $cur_topic['subject'],
			'can_edit_subject' => $can_edit_subject,
			'panther_config' => $panther_config,
			'username' => $username,
			'checkboxes' => $checkboxes,
			'quickpost_links' => array(
				'bbcode' => panther_link($panther_url['help'], array('bbcode')),
				'url' => panther_link($panther_url['help'], array('url')),
				'img' => panther_link($panther_url['help'], array('img')),
				'smilies' => panther_link($panther_url['help'], array('smilies')),
			),
		)
	);

	require PANTHER_ROOT.'footer.php';
}
elseif ($action == 'delete')
{
	if ($pid < 1)
		message($lang_common['Bad request']);
	
	$data = array(
		':id'	=>	$pid,
		':uid'	=>	$panther_user['id']
	);

	$ps = $db->run('SELECT c.subject, c.id AS tid, cd.folder_id, m.poster, m.posted, c.first_post_id, c.num_replies, m.message, m.hide_smilies, m.poster_id FROM '.$db->prefix.'messages AS m INNER JOIN '.$db->prefix.'conversations AS c ON m.topic_id=c.id INNER JOIN '.$db->prefix.'pms_data AS cd ON c.id=cd.topic_id WHERE m.id=:id AND cd.user_id=:uid AND cd.deleted=0', $data);
	if (!$ps->rowCount())
		message($lang_common['Bad request']);	// We've deleted it
	else
		$cur_topic = $ps->fetch();
	
	$is_topic_post = ($pid == $cur_topic['first_post_id']) ? true : false;
	
	if ($cur_topic['poster_id'] != $panther_user['id'] && !$panther_user['is_admmod'])
		message($lang_common['No permission']);
	
	if ($cur_topic['folder_id'] == 3)	// Then we've archived it
		message($lang_common['Bad request']);

	($hook = get_extensions('pms_delete_before_validation')) ? eval($hook) : null;

	require PANTHER_ROOT.'lang/'.$panther_user['language'].'/delete.php';
	if (isset($_POST['form_sent']))
	{
		confirm_referrer('pms_misc.php');
		if ($is_topic_post)	// Then we delete the entire topic only for us (unless everyone else has already)
		{
			$data = array(
				':tid'	=>	$cur_topic['tid'],
				':uid'	=>	$panther_user['id'],
			);

			$ps = $db->run('SELECT 1 FROM '.$db->prefix.'conversations AS c INNER JOIN '.$db->prefix.'pms_data AS cd ON c.id=cd.topic_id WHERE cd.topic_id=:tid AND cd.deleted=0 AND cd.user_id!=:uid', $data);
			if (!$ps->rowCount())	// Then there is no one else in the conversation
			{
				$data = array(
					':id'	=>	$cur_topic['tid'],
				);

				$db->delete('conversations', 'id=:id', $data);
				
				$data = array(
					':id'	=>	$pid,
				);

				$db->delete('messages', 'id=:id', $data);
			}
			else
			{
				$update = array(
					'deleted'	=>	1,
				);

				$db->update('pms_data', $update, 'topic_id=:tid AND user_id=:uid', $data);
			}

			$data = array(
				':amount'	=>	($cur_topic['num_replies'] + 1),	// Make sure we include the topic post
				':id'	=>	$panther_user['id'],
			);

			$db->run('UPDATE '.$db->prefix.'users SET num_pms=num_pms-:amount WHERE id=:id', $data);
			$redirect_msg = $lang_delete['Topic del redirect'];
			$link = panther_link($panther_url['inbox']);
		}
		else	// Delete this post for all users
		{
			$data = array(
				':id'	=>	$pid,
			);

			$ps = $db->run('SELECT cd.user_id FROM '.$db->prefix.'messages AS m INNER JOIN '.$db->prefix.'conversations AS c ON m.topic_id=c.id INNER JOIN '.$db->prefix.'pms_data AS cd ON c.id=cd.topic_id WHERE m.id=:id AND cd.deleted=0', $data);
			if (!$ps->rowCount())	// Then we're the only person left
			{
				$data = array(
					':id'	=>	$pid,
				);

				$db->delete('messages', 'id=:id', $data);
				$data = array(
					':id'	=>	$panther_user['id'],
				);

				$db->run('UPDATE '.$db->prefix.'users SET num_pms=num_pms-1 WHERE id=:id', $data);
			}
			else
			{
				$ps->setFetchMode(PDO::FETCH_COLUMN, 0);
				foreach ($ps as $uid)
				{
					$data = array(
						':id'	=>	$uid,
					);
					
					$db->run('UPDATE '.$db->prefix.'users SET num_pms=num_pms-1 WHERE id=:id', $data);
				}
				
				$data = array(
					':id'	=>	$pid,
				);

				$db->delete('messages', 'id=:id', $data);
			}
			
			$data = array(
				':id'	=>	$cur_topic['tid'],
			);
			
			$ps = $db->select('messages', 'poster, posted, id', $data, 'topic_id=:id', 'id DESC LIMIT 1');
			$cur_post = $ps->fetch();
				
			$data = array(
				':poster'	=>	$cur_post['poster'],
				':posted'	=>	$cur_post['posted'],
				':last'		=>	$cur_post['id'],
				':id'		=>	$cur_topic['tid'],
			);
				
			$db->run('UPDATE '.$db->prefix.'conversations SET num_replies=num_replies-1, poster=:poster, last_post=:posted, last_post_id=:last WHERE id=:id', $data);

			$link = panther_link($panther_url['pms_post'], array($cur_post['id']));
			$redirect_msg = $lang_delete['Post del redirect'];
		}
		
		redirect($link, $redirect_msg);
	}

	($hook = get_extensions('pms_delete_before_header')) ? eval($hook) : null;

	$page_title = array($panther_config['o_board_title'], $lang_common['PM'], $lang_pm['Delete message']);
	define('PANTHER_ALLOW_INDEX', 1);
	define('PANTHER_ACTIVE_PAGE', 'pm');
	require PANTHER_ROOT.'header.php';

	require PANTHER_ROOT.'include/parser.php';
	$tpl = load_template('delete_message.tpl');
	echo $tpl->render(
		array(
			'lang_pm' => $lang_pm,
			'lang_delete' => $lang_delete,
			'lang_common' => $lang_common,
			'form_action' => panther_link($panther_url['pms_delete'], array($pid)),
			'message' => $parser->parse_message($cur_topic['message'], $cur_topic['hide_smilies']),
			'csrf_token' => generate_csrf_token(),
			'poster' => $cur_topic['poster'],
			'posted' => format_time($cur_topic['posted']),
			'is_topic_post' => $is_topic_post,
		)
	);

	require PANTHER_ROOT.'footer.php';
}
else if ($action == 'blocked')
{
	$errors = array();
	if (isset($_POST['add_block']))
	{
		$username = isset($_POST['req_username']) ? panther_trim($_POST['req_username']) : '';

		if ($username == $panther_user['username'])
			$errors[] = $lang_pm['No block self'];

		$data = array(
			':username'	=>	$username,
		);

		$ps = $db->select('users', 'group_id, id', $data, 'username=:username');
		if (!$ps->rowCount() || $username == $lang_common['Guest'])
			$errors[] = sprintf($lang_pm['No user x'], $username);
		else
			list($group_id, $uid) = $ps->fetch(PDO::FETCH_NUM);
		
		($hook = get_extensions('pms_blocked_after_validation')) ? eval($hook) : null;

		if (empty($errors))
		{
			if ($panther_groups[$group_id]['g_id'] == '1')
				$errors[] = sprintf($lang_pm['User is admin'], $username);
			elseif ($panther_groups[$group_id]['g_moderator'] == '1')
				$errors[] = sprintf($lang_pm['User is mod'], $username);
				
			$data = array(
				':id'	=>	$uid,
			);
				
			$ps = $db->select('blocks', 1, $data, 'block_id=:id');
			if ($ps->rowCount())
				$errors[] = $lang_pm['Already blocked'];
		}

		if (empty($errors))
		{
			$insert = array(
				'user_id'	=>	$panther_user['id'],
				'block_id'	=>	$uid,
			);
			
			$db->insert('blocks', $insert);
			redirect(panther_link($panther_url['pms_blocked']), $lang_pm['Block added redirect']);
		}
	}
	else if (isset($_POST['remove']))
	{
		$id = intval(key($_POST['remove']));
		$data = array(
			':id'	=>	$id,
			':uid'	=>	$panther_user['id'],
		);

		// Before we do anything, check we blocked this user
		$ps = $db->select('blocks', 1, $data, 'id=:id AND user_id=:uid');
		if (!$ps->rowCount())
			message($lang_common['No permission']);

		$db->delete('blocks', 'id=:id AND user_id=:uid', $data);
		redirect(panther_link($panther_url['pms_blocked']), $lang_pm['Block del redirect']);
	}

	$data = array(
		':uid'	=>	$panther_user['id'],
	);

	$ps = $db->run('SELECT b.id, b.block_id, u.username, u.group_id FROM '.$db->prefix.'blocks AS b INNER JOIN '.$db->prefix.'users AS u ON b.block_id=u.id WHERE b.user_id=:uid', $data);
	$users = array();
	foreach ($ps as $cur_block)
		$users[] = array('name' => colourize_group($cur_block['username'], $cur_block['group_id'], $cur_block['block_id']), 'id' => $cur_block['id']);

	$required_fields = array('req_username' => $lang_common['Username']);
	$focus_element = array('block', 'req_username');
	
	($hook = get_extensions('pms_blocked_before_header')) ? eval($hook) : null;
	
	$page_title = array($panther_config['o_board_title'], $lang_common['PM'], $lang_pm['My blocked']);
	define('PANTHER_ALLOW_INDEX', 1);
	define('PANTHER_ACTIVE_PAGE', 'pm');
	require PANTHER_ROOT.'header.php';

	$tpl = load_template('blocked_users.tpl');
	echo $tpl->render(
		array(
			'errors' => $errors,
			'lang_pm' => $lang_pm,
			'lang_common' => $lang_common,
			'pm_menu' => generate_pm_menu('blocked'),
			'form_action' => panther_link($panther_url['pms_blocked']),
			'username' => (isset($username)) ? $username : '',
			'users' => $users,
		)
	);

	require PANTHER_ROOT.'footer.php';	
}
else if ($action == 'folders')
{
	$errors = array();
	if (isset($_POST['add_folder']))
	{
		$folder = isset($_POST['req_folder']) ? panther_trim($_POST['req_folder']) : '';
		
		if ($panther_config['o_censoring'] == '1')
			$censored_folder = panther_trim(censor_words($folder));

		if ($folder == '')
			$errors[] = $lang_pm['No folder name'];
		else if (panther_strlen($folder) < 4)
			$errors[] = $lang_pm['Folder too short'];
		else if (panther_strlen($folder) > 30)
			$errors[] = $lang_pm['Folder too long'];
		else if ($panther_config['o_censoring'] == '1' && $folder == '')
			$errors[] = $lang_pm['No folder after censoring'];
		
		$data = array(
			':uid'	=>	$panther_user['id'],
		);

		if ($panther_user['g_pm_folder_limit'] != 0)
		{
			$ps = $db->select('folders', 'COUNT(id)', $data, 'user_id=:uid');
			$num_folders = $ps->fetchColumn();
			
			if ($num_folders >= $panther_user['g_pm_folder_limit'])
				$errors[] = sprintf($lang_pm['Folder limit'], $panther_user['g_pm_folder_limit']);
		}
		
		($hook = get_extensions('pms_folders_after_validation')) ? eval($hook) : null;

		if (empty($errors))
		{
			$insert = array(
				'user_id'	=>	$panther_user['id'],
				'name'		=>	$folder,
			);
			
			$db->insert('folders', $insert);
			redirect(panther_link($panther_url['pms_folders']), $lang_pm['Folder added']);
		}
	}
	else if (isset($_POST['update']))
	{
		$id = intval(key($_POST['update']));
		$folder = panther_trim($_POST['folder'][$id]);
		
		if ($panther_config['o_censoring'] == '1')
			$censored_folder = panther_trim(censor_words($folder));

		if ($folder == '')
			$errors[] = $lang_pm['No folder name'];
		else if (panther_strlen($folder) < 4)
			$errors[] = $lang_pm['Folder too short'];
		else if (panther_strlen($folder) > 30)
			$errors[] = $lang_pm['Folder too long'];
		else if ($panther_config['o_censoring'] == '1' && $folder == '')
			$errors[] = $lang_pm['No folder after censoring'];
		
		if (empty($errors))
		{
			$update = array(
				'name'	=>	$folder,
			);
			
			$data = array(
				':id'	=>	$id,
				':uid'	=>	$panther_user['id'],
			);

			$db->update('folders', $update, 'id=:id AND user_id=:uid', $data);
			redirect(panther_link($panther_url['pms_folders']), $lang_pm['Folder edit redirect']);
		}
	}
	else if (isset($_POST['remove']))
	{
		$id = intval(key($_POST['remove']));
		$data = array(
			':id'	=>	$id,
			':uid'	=>	$panther_user['id'],
		);

		// Before we do anything, check we own this box
		$ps = $db->select('folders', 1, $data, 'id=:id AND user_id=:uid');
		if (!$ps->rowCount())
			message($lang_common['No permission']);

		($hook = get_extensions('pms_delete_folder_before_deletion')) ? eval($hook) : null;

		$update = array(
			'folder_id'	=>	2,	// Send all potential conversations in this box back to the inbox upon deletion
		);

		$update_data = array(
			':id'	=>	$id,
		);

		$db->update('pms_data', $update, 'folder_id=:id', $update_data);
		$db->delete('folders', 'id=:id AND user_id=:uid', $data);
		redirect(panther_link($panther_url['pms_folders']), $lang_pm['Folder del redirect']);
	}

	$data = array(
		':uid'	=>	$panther_user['id'],
	);

	$folders = array();
	$ps = $db->select('folders', 'name, id', $data, 'user_id=:uid');
	foreach ($ps as $cur_folder)
		$folders[] = array('id' => $cur_folder['id'], 'name' => $cur_folder['name']);
	
	$required_fields = array('req_folder' => $lang_pm['Folder']);
	$focus_element = array('folder', 'req_folder');

	($hook = get_extensions('pms_message_folders_before_header')) ? eval($hook) : null;

	$page_title = array($panther_config['o_board_title'], $lang_common['PM'], $lang_pm['My folders 2']);
	define('PANTHER_ALLOW_INDEX', 1);
	define('PANTHER_ACTIVE_PAGE', 'pm');
	require PANTHER_ROOT.'header.php';

	$tpl = load_template('message_folders.tpl');
	echo $tpl->render(
		array(
			'errors' => $errors,
			'lang_pm' => $lang_pm,
			'lang_common' => $lang_common,
			'pm_menu' => generate_pm_menu('folders'),
			'form_action' => panther_link($panther_url['pms_folders']),
			'folder' => (isset($folder)) ? $folder : '',
			'folders' => $folders,
		)
	);

	require PANTHER_ROOT.'footer.php';
}
else
	message($lang_common['Bad request']);