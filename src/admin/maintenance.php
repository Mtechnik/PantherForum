<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

// Tell common.php that we don't want output buffering
define('PANTHER_DISABLE_BUFFERING', 1);

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
	if(!is_null($admins[$panther_user['id']]['admin_permissions']))
	{
		if ($admins[$panther_user['id']]['admin_permissions'] == '0')
			message($lang_common['No permission'], false, '403 Forbidden');
	}
}

check_authentication();

// Load the admin_maintenance.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_maintenance.php';

$action = isset($_REQUEST['action']) ? panther_trim($_REQUEST['action']) : '';
$uid_merge = isset($_POST['to_merge']) ? intval($_POST['to_merge']) : '0';
$uid_stay = isset($_POST['to_stay']) ? intval($_POST['to_stay']) : '0';

$errors = array();
if ($action == 'merge' || $action == 'confirm_merge')
{
	if ($uid_merge == $uid_stay || $uid_merge == '1' || $uid_stay == '1')
		message($lang_common['Bad request']);
	
	if ($action == 'merge')
	{
		@set_time_limit(0);
		$data = array(
			':id'	=>	$uid_merge,
		);
		
		$ps = $db->select('users', 'username, group_id, email, num_posts', $data, 'id=:id');
		if (!$ps->rowCount())
			message($lang_common['Bad request']);
		else
			$user_merge = $ps->fetch();
		
		$data = array(
			':id'	=>	$uid_stay,
		);
		
		$ps = $db->select('users', 'username, group_id, email, num_posts, num_pms', $data, 'id=:id');	
		if (!$ps->rowCount())
			message($lang_common['Bad request']);
		else
			$user_stay = $ps->fetch();
		
		$update = array(
			'owner'	=>	$uid_stay
		);
		
		$data = array(
			':uid'	=>	$uid_merge,
		);
		
		$db->update('attachments', $update, 'owner=:uid', $data);
		
		$update = array(
			'username'	=>	$user_stay['username'],
			'email'		=>	$user_stay['email'],
		);
		
		$data = array(
			':merge_username'	=>	$user_merge['username'],
			':merge_email'	=>	$user_merge['email'],
		);
		
		$db->update('bans', $update, 'username=:merge_username OR :merge_email', $data);
		
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';
		
		generate_bans_cache();
		
		$update = array(
			'user_id' => $uid_stay,
		);
		
		$data = array(
			':uid'	=>	$uid_merge,
		);
		
		$db->update('blocks', $update, 'user_id=:uid', $data);

		$update = array(
			'block_id'	=>	$uid_stay,
		);

		$data = array(
			':uid'	=>	$uid_merge,
		);
		
		$db->update('blocks', $update, 'block_id=:uid', $data);
		
		$update = array(
			'poster' => $user_stay['username'],
		);
		
		$data = array(
			':uid'	=>	$user_merge['username'],
		);
		
		$db->update('conversations', $update, 'poster=:uid', $data);
		
		$update = array(
			'poster_id' => $uid_stay,
		);

		$data = array(
			':uid'	=>	$uid_merge,
		);

		$db->update('conversations', $update, 'poster_id=:uid', $data);

		$update = array(
			'last_poster'	=>	$user_stay['username'],
		);

		$data = array(
			':uid'	=>	$user_merge['username'],
		);
		
		$db->update('conversations', $update, 'last_poster=:uid', $data);

		$update = array(
			'user_id'	=>	$uid_stay,
		);
		
		$data = array(
			':uid'	=>	$uid_merge,
		);
		
		$db->update('folders', $update, 'user_id=:uid', $data);
		
		$update = array(
			'user_id'	=>	$uid_stay,
		);
		
		$data = array(
			':uid'	=>	$uid_merge,
		);
		
		$db->update('folders', $update, 'user_id=:uid', $data);
		
		$update = array(
			'poster_id'	=>	$uid_stay,
			'poster'	=>	$user_stay['username'],
		);
		
		$where = array(
			':pid'	=>	$uid_merge,
		);
		
		$db->update('messages', $update, 'poster_id=:pid', $where);
		$update = array(
			'edited_by'	=>	$user_stay['username'],
		);
		
		$where = array(
			':edited'	=>	$user_merge['username']
		);

		$db->update('messages', $update, 'edited_by=:edited', $where);
		
		$data = array(
			'user_id'	=>	$uid_merge,
		);

		// This bit might take a while (depending on how many messages the user has)
		$num_pms = 0;
		$ps = $db->select('pms_data', 'topic_id', $data, 'user_id=:id');
		foreach ($ps as $pm)
		{
			$data = array(
				'user_id'	=>	$uid_stay,
				'topic_id'	=>	$pm['topic_id'],
			);

			// Check if this user has any PMs the same to avoid messing with the composite key of the table
			$ps1 = $db->select('pms_data', 1, $data, 'user_id=:uid AND topic_id=:tid');
			if (!$ps->roCount())
			{
				// Then we just replace the data as we're not in that conversation
				$update = array(
					'user_id'	=>	$uid_stay,
				);

				$data = array(
					':id'	=>	$pm['topic_id'],
					':uid'	=>	$uid_merge,
				);

				$db->update('pms_data', $update, 'user_id=:uid AND topic_id=:tid', $data);
				$data = array(
					':id'	=>	$pm['topic_id'],
				);

				$ps2 = $db->select('conversations', 'num_replies', $data, 'topic_id=:id');
				$num_pms = $num_pms + ($ps2->fetchColumn()+1); // Plus one for the topic post (which is not included in 'num_replies')
			}
			else
			{	// We are in that conversation, so we need to delete the old data row and leave ours
				$data = array(
					':id'	=>	$uid_merge,
					':tid'	=>	$pm['topic_id'],
				);

				$db->delete('pms_data', 'user_id=:id AND topic_id=:tid', $data);
			}
		}

		$update = array(
			'user_id'	=>	$user_stay['username'],
		);
		
		$where = array(
			':edited'	=>	$user_merge['username']
		);

		$db->update('messages', $update, 'edited_by=:edited', $where);

		$update = array(
			'edited_by'	=>	$user_stay['username'],
		);
		
		$where = array(
			':edited'	=>	$user_merge['username']
		);
		
		$db->update('posts', $update, 'edited_by=:edited', $where);
		
		$update = array(
			'user_id'	=>	$uid_stay,
		);
		
		$data = array(
			':id'	=>	$uid_merge,
		);
		
		$db->update('forum_subscriptions', $update, 'user_id=:id', $data);
		$update = array(
			'last_poster'	=>	$user_stay['username'],
		);
		
		$data = array(
			':user'	=>	$user_merge['username'],
		);
		
		$db->update('forums', $update, 'last_poster=:user', $data);
		$data = array(
			':username'	=>	$user_merge['username'],
		);
		
		$db->delete('login_queue', 'username=:username', $data);
		
		$update = array(
			'poster_id'	=>	$uid_stay,
			'poster'	=>	$user_stay['username'],
		);
		
		$where = array(
			':pid'	=>	$uid_merge,
		);
		
		$db->update('posts', $update, 'poster_id=:pid', $where);
		$update = array(
			'edited_by'	=>	$user_stay['username'],
		);
		
		$where = array(
			':edited'	=>	$user_merge['username']
		);
		
		$db->update('posts', $update, 'edited_by=:edited', $where);
		$update = array(
			'reported_by'	=>	$uid_stay,
		);
		
		$where = array(
			':by'	=>	$uid_merge,
		);
		
		$db->update('reports', $update, 'reported_by=:by', $where);
		$update = array(
			'given_by'	=>	$uid_stay,
		);
		
		$where = array(
			':by'	=>	$uid_merge,
		);
		
		$db->update('reputation', $update, 'given_by=:by', $where);
		$update = array(
			'ident'	=>	$user_stay['username'],
		);
		
		$where = array(
			':id'	=>	$user_merge['username'],
		);
		
		$db->update('search_cache', $update, 'ident=:id', $where);
		$update = array(
			'poster'	=>	$user_stay['username'],
		);
		
		$where = array(
			':user'	=>	$user_merge['username'],
		);
		
		$db->update('topics', $update, 'poster=:user', $where);
		$update = array(
			'last_poster'	=>	$user_stay['username'],
		);
		
		$where = array(
			':poster'	=>	$user_merge['username'],
		);
		
		$db->update('topics', $update, 'last_poster=:poster', $where);
		$update = array(
			'user_id'	=>	$uid_stay,
		);
		
		$where = array(
			':uid'	=>	$uid_merge,
		);
		
		$db->update('topic_subscriptions', $update, 'user_id=:uid', $where);
		
		$update = array(
			'user_id'	=>	$uid_stay,
		);

		$db->update('warnings', $update, 'user_id=:uid', $where);
		
		$update = array(
			'issued_by'	=>	$uid_stay,
		);

		$db->update('warnings', $update, 'issued_by=:uid', $where);
		
		$data = array(
			':uid'	=>	$uid_merge,
		);
		
		$db->delete('online', 'user_id=:uid', $data);
		
		// If the group IDs are different we go for the newer one
		if ($user_merge['group_id'] != $user_stay['group_id'])
			$user_merge['group_id'] = $user_stay['group_id'];
		
		$new_group_mod = $panther_groups[$user_merge['group_id']]['g_moderator'];
		
		$new_password = random_pass(12);
		$new_salt = random_pass(16);
		
		$update = array(
			'group_id'	=>	$user_merge['group_id'],
			'num_posts'	=>	$user_stay['num_posts']+$user_merge['num_posts'],
			'password'	=>	panther_hash($new_password.$new_salt),
			'salt'		=>	$new_salt,
			'num_pms'	=>	($user_stay['num_pms']+$num_pms) // Add all the new PMs we've just received to the total we already have
		);

		$data = array(
			':id'	=>	$uid_stay,
		);

		$db->update('users', $update, 'id=:id', $data);

		// Sort out admin restriction stuff
		if ($user_merge['group_id'] == PANTHER_ADMIN && $user_stay['group_id'] != PANTHER_ADMIN || $panther_groups[$user_merge['group_id']]['g_admin'] == '1' && $panther_groups[$user_stay['group_id']]['g_admin'] == '1')
		{
			$data = array(
				':id'	=>	$uid_merge,
			);

			$db->delete('restrictions', 'admin_id=:id', $data);
		}
		else
		{
			$data = array(
				':id'	=>	$uid_stay,
			);

			$ps = $db->select('restrictions', 1, $data, 'admin_id=:id');
			if (!$ps->rowCount())
			{
				$update = array(
					'admin_id'	=>	$uid_stay,
				);

				$data = array(
					':id'	=>	$uid_merge,
				);
				
				$db->update('restrictions', $update, 'admin_id=:id', $data);
			}
		}
		
		// So if we're not an admin, are we a moderator? If not, remove them from all the forums they (might) have moderated.
		if ($user_merge['group_id'] != PANTHER_ADMIN && $new_group_mod != '1' || $user_merge['group_id'] != $user_stay['group_id'])
		{
			$ps = $db->select('forums', 'id, moderators');
			foreach ($ps as $cur_forum)
			{
				$cur_moderators = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();
				if (in_array($uid_stay, $cur_moderators))
				{
					$data = array(
						':id'	=>	$cur_forum['id'],
					);
					if ($user_merge['group_id'] != PANTHER_ADMIN && $new_group_mod != '1') // Remove ability to moderate any forums they previously did
					{
						$username = array_search($uid_stay, $cur_moderators);
						unset($cur_moderators[$username]);
						unset($cur_moderators['groups'][$uid_stay]);
						
						if (empty($cur_moderators['groups']))
							unset($cur_moderators['groups']);
						
						$cur_moderators = (!empty($cur_moderators)) ? serialize($cur_moderators) : null;
						$update = array(
							'moderators'	=>	$cur_moderators,
						);
					}
					else // Just update the group id
					{
						$cur_moderators['groups'][$id] = $user_merge['group_id'];
						$update = array(
							'moderators' => serialize($cur_moderators),
						);
					}
					
					$db->update('forums', $update, 'id=:id', $data);
				}
			}
		}

		delete_avatar($uid_merge);
		require PANTHER_ROOT.'include/email.php';

		$info = array(
			'message' => array(
				'<username>' => $user_merge['username'],
				'<password>' => $new_password,
				'<admin>' => $panther_user['username'],
				'<merged_user>' => $user_stay['username'],
			)
		);
		
		$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/account_merged_full.tpl', $info);
		$mailer->send($user_merge['email'], $mail_subject, $mail_message);
		
		$info = array(
			'message' => array(
				'<username>' => $user_stay['username'],
				'<password>' => $new_password,
				'<admin>' => $panther_user['username'],
				'<merged_user>' => $user_merge['username'],
			)
		);
		
		$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/account_merged.tpl', $info);
		$mailer->send($user_stay['email'], $mail_subject, $mail_message);
		
		$data = array(
			':id'	=>	$uid_merge,
		);
		
		//Finally, the very last thing we do is delete the old user..
		$db->delete('users', 'id=:id', $data);

		generate_users_info_cache();
		redirect(panther_link($panther_url['admin_maintenance']), $lang_admin_maintenance['users merged redirect']);
	}
	
	$data = array(
		':id'	=>	$uid_merge, 
	);

	$ps = $db->select('users', 'username', $data, 'id=:id');
	if ($ps->rowCount())
		$merge_user = $ps->fetchColumn();
	else
		message($lang_common['Bad request']);
	
	$data = array(
		':id'	=>	$uid_stay,
	);

	$ps = $db->select('users', 'username', $data, 'id=:id');
	if ($ps->rowCount())
		$stay_user = $ps->fetchColumn();
	else
		message($lang_common['Bad request']);
	
	$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Maintenance']);
	define('PANTHER_ACTIVE_PAGE', 'admin');
	require PANTHER_ROOT.'header.php';

	generate_admin_menu('maintenance');	

	$tpl = load_template('confirm_merge.tpl');
	echo $tpl->render(
		array(
			'lang_admin_maintenance' => $lang_admin_maintenance,
			'form_action' => panther_link($panther_url['admin_maintenance']),
			'uid_merge' => $uid_merge,
			'uid_stay' => $uid_stay,
			'merge_user' => $merge_user,
			'stay_user' => $stay_user,
		)
	);
	
	require PANTHER_ROOT.'footer.php';
}

if ($action == 'prune_users')
{
	$days = isset($_POST['days']) ? intval($_POST['days']) : 0;
	$posts = isset($_POST['posts']) ? intval($_POST['posts']) : 0;
	
	$data = array($posts, (time() - ($days * 86400)));
	$sql = array();
	$prune = (isset($_POST['prune_by']) && $_POST['prune_by'] == '1') ? 'registered' : 'last_visit';
	
	if (isset($_POST['admmods_delete']) && $_POST['admmods_delete'] == '1')
	{
		$sql[] = 'group_id=?';
		$data[] = PANTHER_UNVERIFIED;
	}
	else
	{
		$groups = array();
		foreach ($panther_groups as $cur_group)
		{
			if ($cur_group['g_moderator'] == '1' || $cur_group['g_id'] == PANTHER_ADMIN)
			{
				$data[] = $cur_group['g_id'];
				$groups[] = '?';
			}
		}
		$sql[] = 'AND group_id NOT IN ('.implode(',', $groups).')';
	}
	
	if (isset($_POST['verified']) && $_POST['verified'] == '0')
	{
		$sql[] = 'group_id>?';
		$data[] = PANTHER_UNVERIFIED;
	}
	else
	{
		$sql[] = 'group_id=?';
		$data[] = PANTHER_UNVERIFIED;
	}
	
	$ps = $db->run('DELETE FROM '.$db->prefix.'users WHERE id>2 AND num_posts<? AND '.$prune.'<? '.implode(' AND ', $sql), $data);
	redirect(panther_link($panther_url['admin_maintenance']), sprintf($lang_admin_maintenance['Pruning complete message'], $ps->rowCount()));
}
if ($action == 'rebuild')
{
	$per_page = isset($_GET['i_per_page']) ? intval($_GET['i_per_page']) : 0;
	$start_at = isset($_GET['i_start_at']) ? intval($_GET['i_start_at']) : 0;

	// Check per page is > 0
	if ($per_page < 1)
		message($lang_admin_maintenance['Posts must be integer message']);

	@set_time_limit(0);

	// If this is the first cycle of posts we empty the search index before we proceed
	if (isset($_GET['i_empty_index']))
	{
		// This is the only potentially "dangerous" thing we can do here, so we check the referer
		confirm_referrer(PANTHER_ADMIN_DIR.'/maintenance.php');

		$db->truncate_table('search_matches');
		$db->truncate_table('search_words');

		// Reset the sequence for the search words
		$ps = $db->run('ALTER TABLE '.$db->prefix.'search_words auto_increment=1');
	}

	$query_str = '';
	require PANTHER_ROOT.'include/search_idx.php';

	$data = array(
		':start'	=>	$start_at,
		':limit'	=>	$per_page,
	);

	// Fetch posts to process this cycle
	$ps = $db->run('SELECT p.id, p.message, t.subject, t.first_post_id FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id WHERE p.id >= :start ORDER BY p.id ASC LIMIT :limit', $data);

	$end_at = 0;
	$posts = array();
	foreach ($ps as $cur_item)
	{
		if ($cur_item['id'] == $cur_item['first_post_id'])
			update_search_index('post', $cur_item['id'], $cur_item['message'], $cur_item['subject']);
		else
			update_search_index('post', $cur_item['id'], $cur_item['message']);

		$end_at = $cur_item['id'];
	}

	// Check if there is more work to do
	if ($end_at > 0)
	{
		$data = array(
			':id'	=>	$end_at,
		);

		$ps = $db->run('SELECT id FROM '.$db->prefix.'posts WHERE id>:id ORDER BY id ASC LIMIT 1', $data);
		if ($ps->rowCount())
			$query_str = '?action=rebuild&i_per_page='.$per_page.'&i_start_at='.$ps->fetchColumn();
	}

	redirect(panther_link($panther_url['admin_maintenance']).$query_str, sprintf($lang_admin_maintenance['Rebuilding search index'], $per_page, $end_at));
}

if ($action == 'prune')
{
	$prune_from = isset($_POST['prune_from']) ? panther_trim($_POST['prune_from']) : '';
	$prune_sticky = isset($_POST['prne_sticky']) ? intval($_POST['prune_sticky']) : '';

	if (isset($_POST['prune_comply']))
	{
		confirm_referrer(PANTHER_ADMIN_DIR.'/maintenance.php');

		$prune_days = intval($_POST['prune_days']);
		$prune_date = ($prune_days) ? time() - ($prune_days * 86400) : -1;

		@set_time_limit(0);

		if ($prune_from == 'all')
		{
			$ps = $db->select('forums', 'id');
			$num_forums = $ps->rowCount();

			for ($i = 0; $i < $num_forums; ++$i)
			{
				$fid = $ps->fetchColumn();

				prune($fid, $prune_sticky, $prune_date);
				update_forum($fid);
			}
		}
		else
		{
			$prune_from = intval($prune_from);
			prune($prune_from, $prune_sticky, $prune_date);
			update_forum($prune_from);
		}

		// Locate any "orphaned redirect topics" and delete them
		$ps = $db->run('SELECT t1.id FROM '.$db->prefix.'topics AS t1 LEFT JOIN '.$db->prefix.'topics AS t2 ON t1.moved_to=t2.id WHERE t2.id IS NULL AND t1.moved_to IS NOT NULL');
		$num_orphans = $ps->rowCount();

		if ($num_orphans)
		{
			for ($i = 0; $i < $num_orphans; ++$i)
			{
				$orphans[] = $ps->fetchColumn();
				$markers[] = '?';
			}

			$db->run('DELETE FROM '.$db->prefix.'topics WHERE id IN('.implode(',', $markers).')', $orphans);
		}

		redirect(panther_link($panther_url['admin_maintenance']), $lang_admin_maintenance['Posts pruned redirect']);
	}

	$prune_days = panther_trim($_POST['req_prune_days']);
	if ($prune_days == '' || preg_match('%[^0-9]%', $prune_days))
		message($lang_admin_maintenance['Days must be integer message']);

	$prune_date = time() - ($prune_days * 86400);

	// Concatenate together the query for counting number of topics to prune
	$data = array(
		':prune'	=>	$prune_date,
	);

	$sql = 'SELECT COUNT(id) FROM '.$db->prefix.'topics WHERE last_post<:prune AND moved_to IS NULL';

	if ($prune_sticky == '0')
		$sql .= ' AND sticky=0';

	if ($prune_from != 'all')
	{
		$prune_from = intval($prune_from);
		$sql .= ' AND forum_id=:fid';
		$data[':fid'] = $prune_from;
		
		$select = array(
			':id'	=>	$prune_from,
		);

		// Fetch the forum name (just for cosmetic reasons)
		$ps = $db->select('forums', 'forum_name', $select, 'id=:id');
		$forum = $ps->fetchColumn();
	}
	else
		$forum = $lang_admin_maintenance['All forums'];

	$ps = $db->run($sql, $data);
	$num_topics = $ps->fetchColumn();

	if (!$num_topics)
		message(sprintf($lang_admin_maintenance['No old topics message'], $prune_days));

	$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Prune']);
	define('PANTHER_ACTIVE_PAGE', 'admin');
	require PANTHER_ROOT.'header.php';

	generate_admin_menu('maintenance');

	$tpl = load_template('confirm_prune.tpl');
	echo $tpl->render(
		array(
			'lang_admin_maintenance' => $lang_admin_maintenance,
			'lang_admin_common' => $lang_admin_common,
			'link' => panther_link($panther_url['admin_maintenance']),
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/maintenance.php'),
			'prune_days' => $prune_days,
			'prune_sticky' => $prune_sticky,
			'prune_from' => $prune_from,
			'forum' => $forum,
			'num_topics' => forum_number_format($num_topics),
		)
	);
	
	require PANTHER_ROOT.'footer.php';
}

if ($action == 'add_user')
{
	$errors = array();

	$username = isset($_POST['username']) ? panther_trim($_POST['username']) : '';
	$random_pass = isset($_POST['random_pass']) && $_POST['random_pass'] == '1' ? 1 : 0;
	$email = isset($_POST['email']) ? strtolower(panther_trim($_POST['email'])) : '';
	$password_salt = random_pass(16);
	
	if ($random_pass == '1')
	{
		$password1 = random_pass(12);
		$password2 = $password1;
	}
	else
	{
		$password1 = isset($_POST['password1']) ? panther_trim($_POST['password1']) : '';
		$password2 = isset($_POST['password2']) ? panther_trim($_POST['password2']) : '';
	}
	
	require PANTHER_ROOT.'lang/'.$panther_user['language'].'/prof_reg.php';
	
	// Validate username and passwords
	check_username($username);

	if (panther_strlen($password1) < 6)
		$errors[] = $lang_prof_reg['Pass too short'];
	else if ($password1 != $password2)
		$errors[] = $lang_prof_reg['Pass not match'];

	// Validate email
	require PANTHER_ROOT.'include/email.php';

	if (!$mailer->is_valid_email($email))
		$errors[] = $lang_common['Invalid email'];

	// Check if it's a banned email address
	if ($mailer->is_banned_email($email))
	{
		if ($panther_config['p_allow_banned_email'] == '0')
			$errors[] = $lang_prof_reg['Banned email'];
	}
	
	if ($panther_config['p_allow_dupe_email'] == '0')
	{
		$data = array(
			':email'	=>	$email,
		);

		$ps = $db->select('users', 1, $data, 'email=:email');
		if ($ps->rowCount())
			$errors[] = $lang_prof_reg['Dupe email'];
	}
	
	if (empty($errors))
	{
		// Insert the new user into the database. We do this now to get the last inserted ID for later use
		$now = time();

		$initial_group_id = ($random_pass == 0) ? $panther_config['o_default_user_group'] : PANTHER_UNVERIFIED;
		$password_hash = panther_hash($password1.$password_salt);
		
		// Add the user
		$insert = array(
			'username'	=>	$username,
			'group_id'	=>	$initial_group_id,
			'password'	=>	$password_hash,
			'salt'		=>	$password_salt,
			'email'		=>	$email,
			'email_setting'	=>	$panther_config['o_default_email_setting'],
			'timezone'	=>	$panther_config['o_default_timezone'],
			'dst'		=>	$panther_config['o_default_dst'],
			'language'	=>	$panther_config['o_default_lang'],
			'style'		=>	$panther_config['o_default_style'],
			'registered'	=>	$now,
			'registration_ip'	=>	get_remote_address(),
			'last_visit'	=>	$now,
		);

		$db->insert('users', $insert);
		$new_uid = $db->lastInsertId($db->prefix.'users');

		if ($random_pass == '1')
		{
			$info = array(
				'subject' => array(
					'<board_title>' => $panther_config['o_board_title'],
				),
				'message' => array(
					'<base_url>' => get_base_url(),
					'<username>' => $username,
					'<password>' => $password1,
					'<login_url>' => panther_link($panther_url['login']),
				)
			);

			$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/welcome.tpl', $info);
			$mailer->send($email, $mail_tpl['subject'], $mail_tpl['message']);
		}

		// Regenerate the users info cache
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';

		generate_users_info_cache();
		redirect(panther_link($panther_url['admin_maintenance']), $lang_admin_maintenance['User created message']);
	}
}

// Get the first post ID from the db
$ps = $db->select('posts', 'id', array(), '', 'id ASC LIMIT 1');
$first_id = ($ps->rowCount()) ? $ps->fetchColumn() : 0;

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Maintenance']);
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

$options = array();
$ps = $db->run('SELECT u.id, u.username, g.g_title FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id WHERE u.id!=1 ORDER BY u.id ASC');	
foreach ($ps as $result)
	$options[] = array('id' => $result['id'], 'username' => $result['username'], 'group_title' => $result['g_title']);

$forums = $catgeories = array();
$ps = $db->run('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id WHERE f.redirect_url IS NULL ORDER BY c.disp_position, c.id, f.disp_position');
foreach ($ps as $cur_forum)
{
	if (!isset($categories[$cur_forum['cid']]))
		$categories[$cur_forum['cid']] = array(
			'id' => $cur_forum['cid'],
			'name' => $cur_forum['cat_name'],
		);

	$forums[] = array(
		'category_id' => $cur_forum['cid'],
		'name' => $cur_forum['forum_name'],
		'id' => $cur_forum['fid'],
	);
}

generate_admin_menu('maintenance');

$tpl = load_template('admin_maintenance.tpl');
echo $tpl->render(
	array(
		'lang_admin_maintenance' => $lang_admin_maintenance,
		'lang_admin_common' => $lang_admin_common,
		'lang_common' => $lang_common,
		'form_action' => panther_link($panther_url['admin_maintenance']),
		'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/maintenance.php'),
		'options_link' => panther_link($panther_url['admin_options']),
		'first_id' => $first_id,
		'POST' => $_POST,
		'panther_config' => $panther_config,
		'errors' => $errors,
		'options' => $options,
		'forums' => $forums,
		'categories' => $categories,
	)
);

require PANTHER_ROOT.'footer.php';