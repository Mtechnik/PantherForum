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

if ($panther_config['o_warnings'] == '0')
	message($lang_warnings['Warning system disabled']);

// Load the warnings.php/post.php language files
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/warnings.php';
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/post.php';

$action = isset($_GET['action']) ? panther_trim($_GET['action']) : '';
$page_title = array($panther_config['o_board_title'], $lang_warnings['Warning system']);

if (isset($_GET['warn']))
{
	$errors = array();
	if ($panther_user['g_mod_warn_users'] == '0' && !$panther_user['is_admin'])
		message($lang_common['No permission']);

	$user_id = isset($_GET['warn']) ? intval($_GET['warn']) : 0;
	$post_id = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
	if ($user_id < 1)
		message($lang_common['Bad request']);

	if ($post_id < 0)
		message($lang_common['Bad request']);

	if ($user_id == $panther_user['id'] || $user_id < 2 || (in_array($user_id, get_admin_ids())))
		message($lang_common['Bad request']);

	// Check whether user has been warned already for this post (users can only receive one warning per post)
	if ($post_id)
	{
		$data = array(
			':id'	=>	$post_id,
		);

		$ps = $db->select('warnings', 'id', $data, 'post_id=:id');
		if ($ps->rowCount())
		{
			$warning_id = $ps->fetchColumn();
			$warning_link = panther_link($panther_url['warning_details'], array($warning_id));

			message(sprintf($lang_warnings['Already warned'], '<a href="'.$warning_link.'">'.$warning_link.'</a>'));
		}
	}

	if (isset($_POST['form_sent']))
	{
		confirm_referrer('warnings.php');

		$data = array(
			':id'	=>	$user_id,
		);

		$ps = $db->select('users', 'username, pm_notify, email', $data, 'id=:id');
		if (!$ps->rowCount())
			message($lang_common['Bad request']);

		list($username, $pm_notify, $email) = $ps->fetch(PDO::FETCH_NUM);
		if ($post_id)
		{
			$data = array(
				':uid'	=>	$user_id,
				':id'	=>	$post_id,
			);

			$ps = $db->select('posts', 'poster_id, message', $data, 'id=:id AND poster_id=:uid AND approved=1 AND deleted=0');
			if (!$ps->rowCount())
				message($lang_common['Bad request']);

			list($poster_id, $message) = $ps->fetch(PDO::FETCH_NUM);
		}

		// Check warning type
		$warning_type = isset($_POST['warning_type']) ? intval($_POST['warning_type']) : -1;
		$now = time();

		// Make sure this warning type exists (and grab some data while we're at it)
		if ($warning_type > 0)
		{
			$data = array(
				':id'	=>	$warning_type,
			);

			$ps = $db->select('warning_types', 'title, points, expiration_time', $data, 'id=:id');
			if (!$ps->rowCount())
				$errors[] = $lang_warnings['No warning type'];

			list ($warning_title, $warning_points, $expiration_time) = $ps->fetch(PDO::FETCH_NUM);
		}
		else // ... Otherwise it's a custom warning
		{
			if ($panther_config['o_custom_warnings'] == '0')
				$errors[] = $lang_warnings['Custom warnings disabled'];

			if ($warning_type != 0)
				$errors[] = $lang_warnings['No warning type'];

			$warning_title = isset($_POST['custom_title']) ? panther_trim($_POST['custom_title']) : '';
			if ($warning_title == '')
				$errors[] = $lang_warnings['No warning reason'];
			else if (panther_strlen($warning_title) > 120)
				$errors[] = $lang_warnings['Too long warning reason'];

			$warning_points = isset($_POST['custom_points']) ? intval($_POST['custom_points']) : 0;
			if ($warning_points < 0)
				$errors[] = $lang_warnings['No points'];

			$expiration_time = isset($_POST['custom_expiration_time']) ? intval($_POST['custom_expiration_time']) : 0;
			$expiration_unit = isset($_POST['custom_expiration_unit']) ? panther_trim($_POST['custom_expiration_unit']) : '';

			if ($expiration_time < 1 && $expiration_unit != 'never')
				$errors[] = $lang_warnings['No expiration time'];

			$expiration = get_expiration_time($expiration_time, $expiration_unit);
		}

		$admin_note = panther_linebreaks(panther_trim($_POST['note_admin']));
		if (strlen($admin_note) > 65535)
			$errors[] = $lang_warnings['Too long admin note'];

		if ($panther_config['o_private_messaging'] == '1')
		{
			$link = '[url]'.panther_link($panther_url['warning_view'], array($user_id)).'[/url]';
			$pm_subject = isset($_POST['req_subject']) ? panther_trim($_POST['req_subject']) : '';

			if ($panther_config['o_censoring'] == '1')
				$censored_subject = panther_trim(censor_words($pm_subject));

			if ($pm_subject == '')
				$errors[] = $lang_warnings['No subject'];
			else if ($panther_config['o_censoring'] == '1' && $censored_subject == '')
				$errors[] = $lang_post['No subject after censoring'];
			else if (panther_strlen($pm_subject) > 70)
				$errors[] = $lang_post['Too long subject'];

			$pm_message = panther_linebreaks(panther_trim($_POST['req_message']));

			if ($pm_message == '')
				$errors[] = $lang_post['No message'];
			else if (strlen($pm_message) > PANTHER_MAX_POSTSIZE)
				$errors[] = sprintf($lang_post['Too long message'], forum_number_format(PANTHER_MAX_POSTSIZE));

			if ($panther_config['p_message_bbcode'] == '1')
			{
				require PANTHER_ROOT.'include/parser.php';
				$pm_message = $parser->preparse_bbcode($pm_message, $errors);
			}

			if (empty($errors))
			{
				if ($pm_message == '')
					$errors[] = $lang_post['No message'];
				else if ($panther_config['o_censoring'] == '1')
				{
					// Censor message to see if that causes problems
					$censored_message = panther_trim(censor_words($pm_message));

					if ($censored_message == '')
						$errors[] = $lang_post['No message after censoring'];
				}
			}

			$pm_subject = str_replace('<warning_type>', $warning_title, $pm_subject);
			$pm_subject = str_replace('<warnings_url>', $link, $pm_subject);
			$pm_message = str_replace('<warning_type>', $warning_title, $pm_message);
			$pm_message = str_replace('<warnings_url>', $link, $pm_message);

			// Check note_pm
			$note_pm = 'Subject: '.$pm_subject."\n\n".'Message:'."\n\n".$pm_message;
		}
		else
			$note_pm = '';
		
		($hook = get_extensions('warn_after_validation')) ? eval($hook) : null;

		if (empty($errors))
		{
			$expiration = ($expiration != '0') ? ($now + $expiration) : 0;
			$insert = array(
				'user_id'	=>	$user_id,
				'type_id'	=>	$warning_type,
				'post_id'	=>	$post_id,
				'title'		=>	($warning_type == 0) ? $warning_title : '', // Check if this is a custom warning
				'points'	=>	$warning_points,
				'date_issued'	=>	$now,
				'date_expire'	=>	$expiration,
				'issued_by'	=>	$panther_user['id'],
				'note_admin'	=>	$admin_note,
				'note_post'	=>	isset($message) ? $message : '',
				'note_pm'	=>	$note_pm,
			);

			$db->insert('warnings', $insert);

			// If private messaging system is enabled
			if ($panther_config['o_private_messaging'] == '1')
			{
				$insert = array(
					'subject'	=>	$pm_subject,
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
					'message'	=>	$pm_message,
					'hide_smilies'	=>	0,
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
				$insert = array(
					'topic_id'	=>	$new_tid,
					'user_id'	=>	$user_id,
				);

				$db->insert('pms_data', $insert);

				$insert = array(
					'topic_id'	=>	$new_tid,
					'user_id'	=>	$panther_user['id'],
					'viewed'	=>	1,
					'deleted'	=>	1,
				);

				$db->insert('pms_data', $insert);
				$data = array(
					':id'	=>	$user_id,
				);

				$db->run('UPDATE '.$db->prefix.'users SET num_pms=num_pms+1 WHERE id=:id', $data);
				if ($pm_notify == '1')
				{
					$info = array(
						'message' => array(
							'<username>' => $username,
							'<sender>' => $panther_user['username'],
							'<message>' => $pm_message,
							'<pm_title>' => $subject,
							'<message_url>' => panther_link($panther_url['pms_topic'], array($new_pid)),
						)
					);

					$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/new_pm.tpl', $info);
					$mailer->send($email, $mail_tpl['subject'], $mail_tpl['message']);
				}

				$update = array(
					'last_post'	=>	$now,
				);

				$data = array(
					':id'	=>	$panther_user['id'],
				);

				$db->update('users', $update, 'id=:id', $data);

				// Check whether user should be banned according to warning levels
				if ($warning_points > 0)
				{
					$data = array(
						':uid'	=>	$user_id,
						':now'	=>	$now,
					);

					$ps = $db->select('warnings', 'SUM(points)', $data, 'user_id=:uid AND (date_expire>:now OR date_expire=0)');
					$points_active = $ps->fetchColumn();

					$data = array(
						':active'	=>	$points_active,
					);

					$ps = $db->select('warning_levels', 'message, period', $data, 'points<=:active', 'points DESC LIMIT 1');
					if ($ps->rowCount())
					{
						list($ban_message, $ban_period) = $ps->fetch(PDO::FETCH_NUM);
						$data = array(
							':username'	=>	$username,
						);

						$ps = $db->select('bans', 'expire', $data, 'username=:username', 'expire IS NULL DESC, expire DESC LIMIT 1');
						if ($ps->rowCount())
						{
							$ban_expire = $ps->fetchColumn();

							// Only delete user's current bans if new ban is greater than curent ban and current ban is not a permanent ban
							if ((($now + $ban_period) > $ban_expire || $ban_period == '0') && $ban_expire != null)
							{
								$ps = $db->delete('bans', 'username=:username', $data);
								$insert = array(
									'username'	=>	$username,
									'message'	=>	$ban_message,
								);

								if ($ban_period != 0)
									$insert['expire'] = $now + $ban_period;

								$db->insert('bans', $insert);
							}
						}
						else
						{
							$insert = array(
								'username'	=>	$username,
								'message'	=>	$ban_message,
								'ban_creator'	=>	$panther_user['id'],
							);

							if ($ban_period != 0)
								$insert['expire'] = $now + $ban_period;

							$db->insert('bans', $insert);
						}

						// Regenerate the bans cache
						if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
							require PANTHER_ROOT.'include/cache.php';

						generate_bans_cache();
					}
				}

				$redirect_link = ($post_id) ? panther_link($panther_url['post'], array($post_id)) : panther_link($panther_url['profile'], array($user_id, url_friendly($username)));
				redirect($redirect_link, $lang_warnings['Warning added redirect']);
			}
		}
	}

	define('PANTHER_ACTIVE_PAGE', 'index');
	require PANTHER_ROOT.'header.php';
	$data = array(
		':id'	=>	$user_id,
	);

	$ps = $db->select('users', 'username, group_id', $data, 'id=:id');
	$cur_user = $ps->fetch();

	$now = time();
	$data = array(
		':id'	=>	$user_id,
		':time'	=>	$now
	);

	$ps = $db->select('warnings', 'COUNT(id)', $data, 'user_id=:id AND (date_expire>:time OR date_expire=0)');
	$num_active = $ps->fetchColumn();

	$ps = $db->select('warnings', 'COUNT(id)', $data, 'user_id=:id AND date_expire<=:time AND date_expire!=0');
	$num_expired = $ps->fetchColumn();

	$ps = $db->select('warnings', 'COALESCE(SUM(points), 0)', $data, 'user_id=:id AND (date_expire>:time OR date_expire=0)');
	$points_active = $ps->fetchColumn();

	$ps = $db->select('warnings', 'COALESCE(SUM(points), 0)', $data, 'user_id=:id AND date_expire<=:time AND date_expire!=0');
	$points_expired = $ps->fetchColumn();

	if ($panther_config['o_private_messaging'] == '1' && empty($errors))
	{
		$pm_tpl = trim(file_get_contents(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/warning_pm.tpl'));

		// Load the "warning pm" template
		$first_crlf = strpos($pm_tpl, "\n");
		$pm_subject = trim(substr($pm_tpl, 8, $first_crlf-8));
		$pm_message = trim(substr($pm_tpl, $first_crlf));
		$pm_message = str_replace('<username>', '[user]'.$cur_user['username'].'[/user]', $pm_message);
		$pm_message = str_replace('<board_title>', $panther_config['o_board_title'], $pm_message);
	}

	$types = array();
	$ps = $db->select('warning_types', 'id, title, points, expiration_time', array(), '', 'points');
	foreach ($ps as $cur_warning)
		$types[] = array('id' => $cur_warning['id'], 'title' => $cur_warning['title'], 'num_points' => forum_number_format($cur_warning['points']), 'expires' => sprintf($lang_warnings['Expires after period'], format_expiration_time($cur_warning['expiration_time'])));

	$tpl = load_template('warn_user.tpl');
	echo $tpl->render(
		array(
			'lang_post' => $lang_post,
			'lang_warnings' => $lang_warnings,
			'errors' => $errors,
			'types' => $types,
			'lang_common' => $lang_common,
			'form_action' => (!$post_id) ? panther_link($panther_url['warn_user'], array($user_id)) : panther_link($panther_url['warn_pid'], array($user_id, $post_id)),
			'username' => colourize_group($cur_user['username'], $cur_user['group_id'], $user_id),
			'num_active' => $num_active,
			'points_active' => $points_active,
			'num_expired' => $num_expired,
			'points_expired' => $points_expired,
			'csrf_token' => generate_csrf_token(),
			'panther_config' => $panther_config,
			'subject' => $pm_subject,
			'message' => $pm_message,
			'expiration_unit' => isset($expiration_unit) ? $expiration_unit : 'days',
			'warning_type' => isset($warning_type) ? $warning_type : -1,
			'warning_title' => isset($warning_title) ? $warning_title : '',
			'expiration_time' => isset($expiration_time) ? $expiration_time : 10,
			'warning_points' => isset($warning_points) ? $warning_points : '',
		)
	);
}
else if (isset($_GET['view']))
{
	$user_id = isset($_GET['view']) ? intval($_GET['view']) : 0;
	if ($user_id < 1)
		message($lang_common['Bad request']);

	// Normal users can only view their own warnings - and only if they have permission
	if ($panther_user['is_guest'] || (!$panther_user['is_admmod'] && $panther_user['id'] != $user_id) || (!$panther_user['is_admmod'] && $panther_config['o_warning_status'] == '2'))
		message($lang_common['No permission']);

	$now = time();
	$data = array(
		':id'	=>	$user_id,
	);

	$ps = $db->select('users', 'username', $data, 'id=:id');
	$username = $ps->fetchColumn();

	$url_username = url_friendly($username);
	$data = array(
		':id' => $user_id,
		':time' => $now,
	);

	$ps = $db->select('warnings', 'COUNT(id)', $data, 'user_id=:id AND (date_expire>:time OR date_expire=0)');
	$num_active = $ps->fetchColumn();

	$ps = $db->select('warnings', 'COUNT(id)', $data, 'user_id=:id AND date_expire<=:time OR date_expire!=0');
	$num_expired = $ps->fetchColumn();

	$ps = $db->select('warnings', 'SUM(points)', $data, 'user_id=:id AND (date_expire>:time OR date_expire=0)');
	$points_active = $ps->fetchColumn();

	$ps = $db->select('warnings', 'SUM(points)', $data, 'user_id=:id AND date_expire<=:time AND date_expire!=0');
	$points_expired = $ps->fetchColumn();

	$active_warnings = array();
	$ps = $db->run('SELECT w.id, w.type_id, w.post_id, w.title AS custom_title, w.points, w.date_issued, w.date_expire, w.issued_by, t.title, u.username AS issued_by_username, u.group_id AS issuer_gid FROM '.$db->prefix.'warnings as w LEFT JOIN '.$db->prefix.'warning_types AS t ON t.id=w.type_id LEFT JOIN '.$db->prefix.'users AS u ON u.id = w.issued_by WHERE w.user_id=:id AND (w.date_expire>:time OR w.date_expire=0) ORDER BY w.date_issued DESC', $data);
	foreach ($ps as $cur_warning)
	{
		if ($cur_warning['custom_title'] != '')
			$warning_title = sprintf($lang_warnings['Custom warning'], $cur_warning['custom_title']);
		else if ($cur_warning['title'] != '')
			$warning_title = $cur_warning['title'];
		else
			$warning_title = ''; // This warning type has been deleted

		$active_warnings[] = array(
			'title' => $warning_title,
			'issued' => format_time($cur_warning['date_issued']),
			'points' => $cur_warning['points'],
			'expires' => ($cur_warning['date_expire'] == '0') ? $lang_warnings['Never'] : format_time($cur_warning['date_expire']),
			'issuer' => ($cur_warning['issued_by_username'] != '') ? colourize_group($cur_warning['issued_by_username'], $cur_warning['issuer_gid'], $cur_warning['issued_by']) : '',
			'details_link' => panther_link($panther_url['warning_details'], array($cur_warning['id'])),
		);
	}

	$expired_warnings = array();
	$ps = $db->run('SELECT w.id, w.type_id, w.post_id, w.title AS custom_title, w.points, w.date_issued, w.date_expire, w.issued_by, t.title, u.username AS issued_by_username, u.group_id AS issuer_gid FROM '.$db->prefix.'warnings as w LEFT JOIN '.$db->prefix.'warning_types AS t ON t.id=w.type_id LEFT JOIN '.$db->prefix.'users AS u ON u.id=w.issued_by WHERE w.user_id=:id AND w.date_expire<=:time AND w.date_expire!=0 ORDER BY w.date_issued DESC', $data);
	foreach ($ps as $cur_warning)
	{
		// Determine warning type
		if ($cur_warning['custom_title'] != '')
			$warning_title = sprintf($lang_warnings['Custom warning'], $cur_warning['custom_title']);
		else if ($warnings_expired['title'] != '')
			$warning_title = $cur_warning['title'];
		else
			$warning_title = ''; // This warning type has been deleted

		$expired_warnings[] = array(
			'title' => $warning_title,
			'issued' => format_time($cur_warning['date_issued']),
			'points' => $cur_warning['points'],
			'expired' => format_time($cur_warning['date_expire']),
			'issuer' => ($cur_warning['issued_by_username'] != '') ? colourize_group($cur_warning['issued_by_username'], $cur_warning['issuer_gid'], $cur_warning['issued_by']) : '',
			'details_link' => panther_link($panther_url['warning_details'], array($cur_warning['id'])),
		);
	}

	define('PANTHER_ACTIVE_PAGE', 'index');
	require PANTHER_ROOT.'header.php';
	$tpl = load_template('view_warning.tpl');
	echo $tpl->render(
		array(
			'profile_link' => panther_link($panther_url['profile'], array($user_id, $url_username)),
			'username' => $username,
			'warning_link' => panther_link($panther_url['warning_view'], array($user_id)),
			'lang_warnings' => $lang_warnings,
			'points_active' => $points_active,
			'num_active' => $num_active,
			'expired_warnings' => $expired_warnings,
			'active_warnings' => $active_warnings,
			'points_expired' => $points_expired,
		)
	);
}
else if (isset($_GET['details']))
{
	$warning_id = isset($_GET['details']) ? intval($_GET['details']) : 0;
	if ($warning_id < 1)
		message($lang_common['Bad request']);

	$data = array(
		':id' => $warning_id,
	);

	$ps = $db->run('SELECT w.id, w.user_id, w.type_id, w.post_id, w.title AS custom_title, w.points, w.date_issued, w.date_expire, w.issued_by, w.note_admin, w.note_post, w.note_pm, t.title, u.username AS issued_by_username, u.group_id AS issuer_gid FROM '.$db->prefix.'warnings as w LEFT JOIN '.$db->prefix.'warning_types AS t ON t.id=w.type_id LEFT JOIN '.$db->prefix.'users AS u ON u.id=w.issued_by WHERE w.id=:id', $data);
	if (!$ps->rowCount())
		message($lang_common['Bad request']);

	$warning_details = $ps->fetch();

	// Normal users can only view their own warnings if they have permission
	if ($panther_user['is_guest'] || (!$panther_user['is_admmod'] && $panther_user['id'] != $warning_details['user_id']) || (!$panther_user['is_admmod'] && $panther_config['o_warning_status'] == '2'))
		message($lang_common['No permission']);

	if ($warning_details['custom_title'] != '')
		$warning_title = sprintf($lang_warnings['Custom warning'], $warning_details['custom_title']).' ('.sprintf($lang_warnings['No of points'], $warning_details['points']).')';
	else if ($warning_details['title'] != '')
		$warning_title = $warning_details['title'].' ('.sprintf($lang_warnings['No of points'], $warning_details['points']).')';
	else
		$warning_title = ''; // This warning type has been deleted

	$data = array(
		':id'	=>	$warning_details['user_id']
	);

	$ps = $db->select('users', 'username, group_id', $data, 'id=:id');
	list($username, $group_id) = $ps->fetch(PDO::FETCH_NUM);

	if ($warning_details['date_expire'] == '0')
		$warning_expires = sprintf($lang_warnings['Expires'], $lang_warnings['Never']);
	else if ($warning_details['date_expire'] > time())
		$warning_expires = sprintf($lang_warnings['Expires'], format_time($warning_details['date_expire']));
	else
		$warning_expires = sprintf($lang_warnings['Expired'], format_time($warning_details['date_expire']));
	
	$render = array(
		'lang_warnings' => $lang_warnings,
		'form_action' => panther_link($panther_url['warnings']),
		'issued_to' => colourize_group($username, $group_id, $warning_details['user_id']),
		'warning_title' => $warning_title,
		'issued' => format_time($warning_details['date_issued']),
		'warning_expires' => $warning_expires,
		'issued_by' => colourize_group($warning_details['issued_by_username'], $warning_details['issuer_gid'], $warning_details['issued_by']),
		'details_link' => panther_link($panther_url['warning_details'], array($warning_id)),
		'view_link' => panther_link($panther_url['warning_view'], array($warning_details['user_id'])),
		'profile_link' => panther_link($panther_url['profile'], array($warning_details['user_id'], url_friendly($username))),
		'username' => $username,
		'post_id' => $warning_details['post_id'],
		'panther_user' => $panther_user,
		'panther_config' => $panther_config,
		'csrf_token' => generate_csrf_token(),
		'user_id' => $warning_details['user_id'],
		'warning_id' => $warning_id,
	);

	require PANTHER_ROOT.'include/parser.php';
	if ($panther_user['is_admmod'])
	{
		$note_admin = $parser->parse_message($warning_details['note_admin'], 0);
		$render['admin_note'] = ($note_admin  == '') ? $lang_warnings['No admin note'] : $note_admin;
	}

	if ($panther_config['o_private_messaging'] == '1')
	{
		$note_pm = $parser->parse_message($warning_details['note_pm'], 0);
		$render['pm_note'] = ($note_pm == '') ? $lang_warnings['No message'] : $note_pm;
	}
	
	if ($warning_details['post_id'])
	{
		$render['message'] = $parser->parse_message($warning_details['note_post'], 0);
		$render['post_link'] = panther_link($panther_url['post'], array($warning_details['post_id']));
	}

	define('PANTHER_ACTIVE_PAGE', 'index');
	require PANTHER_ROOT.'header.php';
	$tpl = load_template('warning_details.tpl');
	echo $tpl->render($render);
}
else if (isset($_POST['delete_id']))
{
	confirm_referrer('warnings.php');

	// Are we allowed to delete warnings?
	if (!$panther_user['is_admin'] && (!$panther_user['is_admmod'] || $panther_user['g_mod_warn_users'] == '0'))
		message($lang_common['No permission']);

	$warning_id = isset($_POST['delete_id']) ? intval($_POST['delete_id']) : 0;
	$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

	if ($warning_id < 1)
		message($lang_common['Bad request']);

	$data = array(
		':id'	=>	$warning_id,
	);

	// Delete the warning
	$db->delete('warnings', 'id=:id', $data);
	redirect(panther_link($panther_url['warning_view'], array($user_id)), $lang_warnings['Warning deleted redirect']);
}
else if ($action == 'show_recent')
{
	if (!$panther_user['is_admmod'])
		message($lang_common['No permission']);

	// Fetch warnings count
	$ps = $db->select('warnings', 'COUNT(id)');
	$num_warnings = $ps->fetchColumn();

	// Determine the user offset (based on $_GET['p'])
	$num_pages = ceil($num_warnings / 50);

	$p = (!isset($_GET['p']) || !is_numeric($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
	$start_from = 50 * ($p - 1);

	$data = array(
		':start'	=>	$start_from,
	);

	$ps = $db->run('SELECT w.id, w.user_id, w.type_id, w.post_id, w.title AS custom_title, w.points, w.date_issued, w.date_expire, w.issued_by, t.title, u.username AS issued_by_username, u.group_id AS issuer_gid, v.username AS username, v.group_id AS user_gid FROM '.$db->prefix.'warnings as w LEFT JOIN '.$db->prefix.'warning_types AS t ON t.id=w.type_id LEFT JOIN '.$db->prefix.'users AS u ON u.id=w.issued_by LEFT JOIN '.$db->prefix.'users AS v ON v.id=w.user_id ORDER BY w.date_issued DESC LIMIT :start, 50', $data);

	$warnings = array();
	foreach ($ps as $active_warnings)
	{
		if ($active_warnings['custom_title'] != '')
			$warning_title = sprintf($lang_warnings['Custom warning'], $active_warnings['custom_title']);
		else if ($active_warnings['title'] != '')
			$warning_title = $active_warnings['title'];
		else
			$warning_title = '';
			
		$warnings[] = array(
			'title' => $warning_title,
			'issued' => format_time($active_warnings['date_issued']),
			'points' => $active_warnings['points'],
			'username' => ($active_warnings['username'] != '') ? colourize_group($active_warnings['username'], $active_warnings['user_gid'], $active_warnings['user_id']) : '',
			'issuer' => ($active_warnings['issued_by_username'] != '') ? colourize_group($active_warnings['issued_by_username'], $active_warnings['issuer_gid'], $active_warnings['issued_by']) : '',
			'details_link' => panther_link($panther_url['warning_details'], array($active_warnings['id'])),
		);		
	}

	define('PANTHER_ACTIVE_PAGE', 'index');
	require PANTHER_ROOT.'header.php';
	$tpl = load_template('recent_warnings.tpl');
	echo $tpl->render(
		array(
			'lang_warnings' => $lang_warnings,
			'lang_common' => $lang_common,
			'warnings' => $warnings,
			'num_pages' => $num_pages,
			'pagination' => paginate($num_pages, $p, $panther_url['warnings_recent']),
		)
	);
}
else
{
	$ps = $db->select('warning_types', 'id, title, description, points, expiration_time', array(), '', 'points, id');
	$ps1 = $db->select('warning_levels', 'id, points, period', array(), '', 'points, id');

	// If neither have been configured
	if (!$ps->rowCount() && !$ps1->rowCount())
		message($lang_common['Bad request']);

	$warning_types = array();
	foreach ($ps as $cur_type)
	{
		$warning_types[] = array(
			'title' => $cur_type['title'],
			'description' => $cur_type['description'],
			'points' => $cur_type['points'],
		);
	}

	$warning_levels = array();
	foreach ($ps1 as $cur_level)
	{
		$ban_title =  ($cur_level['period'] == '0') ? $lang_warnings['Permanent ban'] : format_expiration_time($cur_level['period']);
		$warning_levels[] = array(
			'title' => $ban_title,
			'points' => $cur_level['points'],
		);
	}
	
	($hook = get_extensions('view_warnings_before_header')) ? eval($hook) : null;

	define('PANTHER_ACTIVE_PAGE', 'index');
	require PANTHER_ROOT.'header.php';
	$tpl = load_template('warnings.tpl');
	echo $tpl->render(
		array(
			'lang_warnings' => $lang_warnings,
			'warning_levels' => $warning_levels,
			'warning_types' => $warning_types,
		)
	);
}

$footer_style = 'warnings';
require PANTHER_ROOT.'footer.php';