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

// This particular function doesn't require forum-based moderator access. It can be used by all moderators and admins
if (isset($_GET['get_host']))
{
	if (!$panther_user['is_admmod'])
		message($lang_common['No permission'], false, '403 Forbidden');

	// Is get_host an IP address or a post ID?
	if (@preg_match('%^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$%', $_GET['get_host']) || @preg_match('%^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$%', $_GET['get_host']))
		$ip = $_GET['get_host'];
	else
	{
		$get_host = intval($_GET['get_host']);
		if ($get_host < 1)
			message($lang_common['Bad request'], false, '404 Not Found');

		$data = array(
			':id'	=>	$get_host,
		);
		
		$ps = $db->select('posts', 'poster_ip', $data, 'id=:id');
		if (!$ps->rowCount())
			message($lang_common['Bad request'], false, '404 Not Found');

		$ip = $ps->fetchColumn();
	}

	// Load the misc.php language file
	require PANTHER_ROOT.'lang/'.$panther_user['language'].'/misc.php';

	message(sprintf($lang_misc['Host info 1'], $ip).' | '.sprintf($lang_misc['Host info 2'], @gethostbyaddr($ip)));
}
// All other functions require moderator/admin access
$fid = isset($_GET['fid']) ? intval($_GET['fid']) : 0;
if ($fid < 1)
	message($lang_common['Bad request'], false, '404 Not Found');

$data = array(
	':id'	=>	$fid,
);

$ps = $db->select('forums', 'moderators, password', $data, 'id=:id');
$cur_forum = $ps->fetch();

$mods_array = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();

if (!$panther_user['is_admin'] && ($panther_user['g_moderator'] == '0' || !$panther_user['g_global_moderator'] && !array_key_exists($panther_user['username'], $mods_array)))
	message($lang_common['No permission'], false, '403 Forbidden');

	if ($cur_forum['password'] != '')
		check_forum_login_cookie($fid, $cur_forum['password']);

// Get topic/forum tracking data
$tracked_topics = get_tracked_topics();

// Load the misc.php language file
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/misc.php';

($hook = get_extensions('moderate_after_tracked_topics')) ? eval($hook) : null;

// All other topic moderation features require a topic ID in GET
if (isset($_GET['tid']))
{
	$tid = intval($_GET['tid']);
	if ($tid < 1)
		message($lang_common['Bad request'], false, '404 Not Found');

	// Fetch some info about the topic
	$data = array(
		':gid'	=>	$panther_user['g_id'],
		':fid'	=>	$fid,
		':tid'	=>	$tid,
	);

	$ps = $db->run('SELECT t.subject, t.num_replies, t.first_post_id, f.id AS forum_id, f.forum_name FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.id=:fid AND t.id=:tid AND t.moved_to IS NULL', $data);
	if (!$ps->rowCount())
		message($lang_common['Bad request'], false, '404 Not Found');
	else
		$cur_topic = $ps->fetch();

	// Delete one or more posts
	if (isset($_POST['delete_posts']) || isset($_POST['delete_posts_comply']))
	{
		$posts = ((isset($_POST['posts']) && is_array($_POST['posts'])) ? array_map('intval', $_POST['posts']) : (isset($_POST['posts']) ? array_map('intval', explode(',', $_POST['posts'])) : array()));
		if (empty($posts))
			message($lang_misc['No posts selected']);

		if (isset($_POST['delete_posts_comply']))
		{
			confirm_referrer('moderate.php');

			$data = array();
			for ($i = 0; $i < count($posts); $i++)
			{
				$data[] = $posts[$i];
				$placeholders[] = '?';
			}

			$post_data = $data;
			$data[] = $tid;

			$sql = 'SELECT 1 FROM '.$db->prefix.'posts WHERE id IN('.implode(',', $placeholders).') AND topic_id=?';
			if (!$panther_user['is_admin'] && $panther_user['g_mod_edit_admin_posts'] == '0')
			{
				$admins = get_admin_ids();
				for($i = 0; $i < count($admins); $i++)
				{
					$data[] = $admins[$i];
					$markers[] = '?';
				}
				
				$sql .= ' AND poster_id NOT IN('.implode(',', $markers).')';
			}

			// Run the SQL
			$ps = $db->run($sql, $data);

			if (!$ps->rowCount())
				message($lang_common['Bad request'], false, '404 Not Found');
			else
				$deleted_posts = $ps->rowCount();

			foreach($posts as $id)
				attach_delete_post($id);

			// Delete the posts
			if ($panther_config['o_delete_full'] == '1')
				$db->run('DELETE FROM '.$db->prefix.'posts WHERE id IN('.implode(',', $placeholders).')', $post_data);
			else
				$db->run('UPDATE '.$db->prefix.'posts SET deleted=1 WHERE id IN('.implode(',', $placeholders).')', $post_data);

			require PANTHER_ROOT.'include/search_idx.php';
			strip_search_index($posts);
			
			$data = array(
				':id'	=>	$tid,
			);

			// Get last_post, last_post_id, and last_poster for the topic after deletion
			$ps = $db->select('posts', 'id, poster, posted', $data, 'topic_id=:id', 'id DESC LIMIT 1');
			$last_post = $ps->fetch();

			// How many posts did we just delete?
			$num_replies = $cur_topic['num_replies'] - $deleted_posts;

			// Update the topic
			$update = array(
				'last_post'	=>	$last_post['posted'],
				'last_post_id'	=>	$last_post['id'],
				'last_poster'	=>	$last_post['poster'],
				'num_replies'	=>	$num_replies,
			);
			
			$data = array(
				':id'	=>	$tid,
			);
			
			$db->update('topics', $update, 'id=:id', $data);

			update_forum($fid);
			redirect(panther_link($panther_url['topic'], array($tid, url_friendly($cur_topic['subject']))), $lang_misc['Delete posts redirect']);
		}
		
		($hook = get_extensions('moderate_delete_posts_before_header')) ? eval($hook) : null;

		$page_title = array($panther_config['o_board_title'], $lang_misc['Moderate']);
		define('PANTHER_ACTIVE_PAGE', 'index');
		require PANTHER_ROOT.'header.php';

		$tpl = load_template('delete_posts.tpl');
		echo $tpl->render(
			array(
				'lang_common' => $lang_common,
				'lang_misc' => $lang_misc,
				'csrf_token' => generate_csrf_token(),
				'form_action' => panther_link($panther_url['moderate_topic'], array($fid, $tid)),
				'posts' => implode(',', array_map('intval', array_keys($posts))),
			)
		);

		require PANTHER_ROOT.'footer.php';
	}
	else if (isset($_POST['split_posts']) || isset($_POST['split_posts_comply']))
	{
		$posts = ((isset($_POST['posts']) && is_array($_POST['posts'])) ? array_map('intval', $_POST['posts']) : (isset($_POST['posts']) ? array_map('intval', explode(',', $_POST['posts'])) : array()));
		if (empty($posts))
			message($lang_misc['No posts selected']);

		if (isset($_POST['split_posts_comply']))
		{
			confirm_referrer('moderate.php');

			if (@preg_match('%[^0-9,]%', implode(',', $posts)))
				message($lang_common['Bad request'], false, '404 Not Found');

			$move_to_forum = isset($_POST['move_to_forum']) ? intval($_POST['move_to_forum']) : 0;
			if ($move_to_forum < 1)
				message($lang_common['Bad request'], false, '404 Not Found');

			// How many posts did we just split off?
			$num_posts_splitted = count($posts);
			$num_replies = $cur_topic['num_replies'] - $num_posts_splitted;

			$data = array();
			$update_data = array($tid);	// We need the first value assigned to the new topic ID. So to avoid a second loop, just assign it to the current topic id then replace it later
			for ($i = 0; $i < $num_posts_splitted; $i++)
			{
				$markers[] = '?';
				$data[] = $update_data[] = $posts[$i];	// I know, this is not very nice assigning two variables to the same value. But it avoids a second foreach() loop later.
			}

			$data[] = $tid;
			// Verify that the post IDs are valid
			$ps = $db->select('posts', 'id', $data, 'id IN ('.implode(',', $markers).') AND topic_id=?');
			if ($ps->rowCount() != $num_posts_splitted)
				message($lang_common['Bad request'], false, '404 Not Found');

			// Verify that the move to forum ID is valid
			$data = array(
				':gid'	=>	$panther_user['g_id'],
				':fid'	=>	$move_to_forum,
			);

			$ps = $db->run('SELECT 1 FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.group_id=:gid AND fp.forum_id=:fid) WHERE f.redirect_url IS NULL AND (fp.post_topics IS NULL OR fp.post_topics=1)', $data);
			if (!$ps->rowCount())
				message($lang_common['Bad request'], false, '404 Not Found');

			// Load the post.php language file
			require PANTHER_ROOT.'lang/'.$panther_user['language'].'/post.php';

			// Check subject
			$new_subject = isset($_POST['new_subject']) ? panther_trim($_POST['new_subject']) : '';

			if ($new_subject == '')
				message($lang_post['No subject']);
			else if (panther_strlen($new_subject) > 70)
				message($lang_post['Too long subject']);
			
			($hook = get_extensions('moderate_split_posts')) ? eval($hook) : null;

			// Get data from the new first post
			$ps = $db->run('SELECT p.id, p.poster, p.posted FROM '.$db->prefix.'posts AS p WHERE id IN('.implode(',', $markers).') ORDER BY p.id ASC LIMIT 1', $posts);
			$first_post_data = $ps->fetch();

			// Create the new topic
			$insert = array(
				'poster'	=>	$first_post_data['poster'],
				'subject'	=>	$new_subject,
				'posted'	=>	$first_post_data['posted'],
				'first_post_id'	=>	$first_post_data['id'],
				'forum_id'	=>	$move_to_forum,
			);

			$db->insert('topics', $insert);
			$new_tid = $db->lastInsertId('topics');
			$update_data[0] = $new_tid;

			// Move the posts to the new topic
			$db->run('UPDATE '.$db->prefix.'posts SET topic_id=? WHERE id IN('.implode(',', $markers).')', $update_data);

			// Apply every subscription to both topics
			$data = array(
				':new_tid'	=>	$new_tid,
				':tid'	=>	$tid,
			);
	
			$db->run('INSERT INTO '.$db->prefix.'topic_subscriptions (user_id, topic_id) SELECT user_id, :new_tid FROM '.$db->prefix.'topic_subscriptions WHERE topic_id=:tid', $data);

			// Get last_post, last_post_id, and last_poster from the topic and update it
			$data = array(
				':id'	=>	$tid,
			);

			$ps = $db->select('posts', 'id, posted, poster', $data, 'topic_id=:id', 'id DESC LIMIT 1');
			$last_post_data = $ps->fetch();
			
			$update = array(
				'last_post'		=>	$last_post_data['posted'],
				'last_post_id'	=>	$last_post_data['id'],
				'last_poster'	=>	$last_post_data['poster'],
				'num_replies'	=>	$num_replies,
			);
			
			$data = array(
				':id'	=>	$tid,
			);
			
			$db->update('topics', $update, 'id=:id', $data);

			// Get last_post, last_post_id, and last_poster from the new topic and update it
			$data = array(
				':id'	=>	$new_tid,
			);

			$ps = $db->select('posts', 'id, poster, posted', $data, 'topic_id=:id', 'id DESC LIMIT 1');
			$last_post_data = $ps->fetch();
			
			$update = array(
				'last_post'		=>	$last_post_data['posted'],
				'last_post_id'	=>	$last_post_data['id'],
				'last_poster'	=>	$last_post_data['poster'],
				'num_replies'	=>	($num_posts_splitted-1),
			);
			
			$data = array(
				':id'	=>	$new_tid,
			);

			$db->update('topics', $update, 'id=:id', $data);

			update_forum($fid);
			update_forum($move_to_forum);

			redirect(panther_link($panther_url['topic'], array($new_tid, url_friendly($new_subject))), $lang_misc['Split posts redirect']);
		}
		
		$data = array(
			':gid'	=>	$panther_user['g_id'],
		);

		$forums = ''; // PHP does nag, so it does
		$categories = $forums = array();
		$ps = $db->run('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) WHERE (fp.post_topics IS NULL OR fp.post_topics=1) AND f.redirect_url IS NULL ORDER BY c.disp_position, c.id, f.disp_position', $data);
		$cur_category = 0;
		foreach ($ps as $cur_forum)
		{
			if (!isset($categories[$cur_forum['cid']]))
			{
				$catgeories[$cur_forum['cid']] = array(
					'name' => $cur_forum['cat_name'],
					'id' => $cur_forum['cid'],
				);
			}
			
			$forums[] = array(
				'category_id' => $cur_forum['cid'],
				'id' => $cur_forum['fid'],
				'name' => $cur_forum['forum_name'],
			);
		}
		
		($hook = get_extensions('moderate_split_posts')) ? eval($hook) : null;

		$page_title = array($panther_config['o_board_title'], $lang_misc['Moderate']);
		$focus_element = array('subject','new_subject');
		define('PANTHER_ACTIVE_PAGE', 'index');
		require PANTHER_ROOT.'header.php';
		
		$tpl = load_template('split_posts.tpl');
		echo $tpl->render(
			array(
				'lang_misc' => $lang_misc,
				'form_action' => panther_link($panther_url['moderate_topic'], array($fid, $tid)),
				'csrf_token' => generate_csrf_token(),
				'posts' => implode(',', array_map('intval', array_keys($posts))),
				'lang_common' => $lang_common,
				'forums' => $forums,
				'categories' => $catgeories,
				'fid' => $fid,
			)
		);

		require PANTHER_ROOT.'footer.php';
	}

	// Load the viewtopic.php language file
	require PANTHER_ROOT.'lang/'.$panther_user['language'].'/topic.php';

	if (isset($_GET['action']) && $_GET['action'] == 'all')
		$panther_user['disp_posts'] = $cur_topic['num_replies'] + 1;

	// Determine the post offset (based on $_GET['p'])
	$num_pages = ceil(($cur_topic['num_replies'] + 1) / $panther_user['disp_posts']);

	$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);
	$start_from = $panther_user['disp_posts'] * ($p - 1);
	$data = array(
		':id'	=>	$tid,
	);

	$ps = $db->run('SELECT t.subject, t.archived, f.forum_name FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON t.forum_id=f.id WHERE t.id=:id', $data);
	if (!$ps->rowCount())
		message($lang_common['Bad request']);
	else
		$info = $ps->fetch();

	if ($info['archived'] == '1')
		message($lang_topic['topic archived']);

	if ($panther_config['o_censoring'] == '1')
		$cur_topic['subject'] = censor_words($cur_topic['subject']);
	
	($hook = get_extensions('moderate_topic_before_header')) ? eval($hook) : null;

	$page_title = array($panther_config['o_board_title'], $cur_topic['forum_name'], $cur_topic['subject']);
	define('PANTHER_ACTIVE_PAGE', 'index');
	require PANTHER_ROOT.'header.php';

	require PANTHER_ROOT.'include/parser.php';
	$post_count = 0; // Keep track of post numbers

	// Retrieve a list of post IDs, LIMIT is (really) expensive so we only fetch the IDs here then later fetch the remaining data
	$data = array(
		':id'	=>	$tid,
	);

	$ps = $db->select('posts', 'id', $data, 'topic_id=:id', 'id LIMIT '.$start_from.','.$panther_user['disp_posts']);

	$post_ids = array();
	$ps->setFetchMode(PDO::FETCH_COLUMN, 0);
	foreach ($ps as $cur_post_id)
	{
		$post_ids[] = $cur_post_id;
		$markers[] = '?';
	}

	// Retrieve the posts (and their respective poster)
	$posts = array();
	$ps = $db->run('SELECT u.title, u.num_posts, g.g_id, g.g_user_title, p.id, p.poster, p.poster_id, p.message, p.hide_smilies, p.posted, p.edited, p.edited_by FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'users AS u ON u.id=p.poster_id INNER JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE p.id IN ('.implode(',', $markers).') ORDER BY p.id', $post_ids);
	foreach ($ps as $cur_post)
	{
		$post_count++;

		// If the poster is a registered user
		if ($cur_post['poster_id'] > 1)
		{
			$poster = colourize_group($cur_post['poster'], $cur_post['g_id'], $cur_post['poster_id']);

			// get_title() requires that an element 'username' be present in the array
			$cur_post['username'] = $cur_post['poster'];
			$user_title = get_title($cur_post);

			if ($panther_config['o_censoring'] == '1')
				$user_title = censor_words($user_title);
		}
		// If the poster is a guest (or a user that has been deleted)
		else
		{
			$poster = $cur_post['poster'];
			$user_title = $lang_topic['Guest'];
		}

		$posts[] = array(
			'message' => $parser->parse_message($cur_post['message'], $cur_post['hide_smilies']),
			'poster' => $poster,
			'user_title' => $user_title,
			'post_link' => panther_link($panther_url['post'], array($cur_post['id'])),
			'posted' => format_time($cur_post['posted']),
			'id' => $cur_post['id'],
			'first_post_id' => $cur_topic['first_post_id'],
			'count' => ($start_from + $post_count),
			'edited' => ($cur_post['edited'] != '') ? format_time($cur_post['edited']) : '',
			'edited_by' => $cur_post['edited_by'],
		);
	}
	
	$tpl = load_template('moderate_topic.tpl');
	echo $tpl->render(
		array(
			'lang_common' => $lang_common,
			'lang_topic' => $lang_topic,
			'index_link' => panther_link($panther_url['index']),
			'forum_name' => $cur_topic['forum_name'],
			'forum_link' => panther_link($panther_url['forum'], array($fid, url_friendly($info['forum_name']))),
			'topic_link' => panther_link($panther_url['topic'], array($tid, url_friendly($info['subject']))),
			'cur_topic' => $cur_topic,
			'lang_misc' => $lang_misc,
			'pagination' => paginate($num_pages, $p, $panther_url['moderate_topic'], array($fid, $tid)),
			'form_action' => panther_link($panther_url['moderate_topic'], array($fid, $tid)),
			'posts' => $posts,
		)
	);

	require PANTHER_ROOT.'footer.php';
}

// Move one or more topics
if (isset($_REQUEST['move_topics']) || isset($_POST['move_topics_to']))
{
	if (isset($_POST['move_topics_to']))
	{
		confirm_referrer('moderate.php');

		if (@preg_match('%[^0-9,]%', $_POST['topics']))
			message($lang_common['Bad request'], false, '404 Not Found');

		$topics = explode(',', $_POST['topics']);
		$move_to_forum = isset($_POST['move_to_forum']) ? intval($_POST['move_to_forum']) : 0;
		if (empty($topics) || $move_to_forum < 1)
			message($lang_common['Bad request'], false, '404 Not Found');

		$data = array($fid);
		for ($i = 0; $i < count($topics); $i++)
		{
			$markers[] = '?';
			$data[] = $topics[$i];
		}

		// Verify that the topic IDs are valid
		$ps = $db->select('topics', 1, $data, 'forum_id=? AND id IN ('.implode(',',$markers).')');

		if (!$ps->rowCount())
			message($lang_common['Bad request'], false, '404 Not Found');

		// Verify that the move to forum ID is valid
		$select = array(
			':gid'	=>	$panther_user['g_id'],
			':fid'	=>	$move_to_forum,
			':fid2' =>	$move_to_forum,
		);

		$ps = $db->run('SELECT forum_name FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.group_id=:gid AND fp.forum_id=:fid) WHERE f.redirect_url IS NULL AND f.id=:fid2 AND (fp.post_topics IS NULL OR fp.post_topics=1)', $select);
		if (!$ps->rowCount())
			message($lang_common['Bad request'], false, '404 Not Found');
		else
			$forum_name = url_friendly($ps->fetchColumn());

		foreach($topics as $id)
			attach_delete_thread($id);

		$data[0] = $move_to_forum;

		// Delete any redirect topics if there are any (only if we moved/copied the topic back to where it was once moved from)
		$db->run('DELETE FROM '.$db->prefix.'topics WHERE forum_id=? AND moved_to IN('.implode(',',$markers).')', $data);

		// Move the topic(s)
		$db->run('UPDATE '.$db->prefix.'topics SET forum_id=? WHERE id IN('.implode(',',$markers).')', $data);

		// Should we create redirect topics?
		if (isset($_POST['with_redirect']))
		{
			foreach ($topics as $cur_topic)
			{
				$data = array(
					':id'	=>	$cur_topic,
				);

				// Fetch info for the redirect topic
				$ps = $db->select('topics', 'poster, subject, posted, last_post', $data, 'id=:id');
				$moved_to = $ps->fetch();

				// Create the redirect topic
				$insert = array(
					'poster'	=>	$moved_to['poster'],
					'subject'	=>	$moved_to['subject'],
					'posted'	=>	$moved_to['posted'],
					'last_post'	=>	$moved_to['last_post'],
					'moved_to'	=>	$cur_topic,
					'forum_id'	=>	$fid,
				);

				$db->insert('topics', $insert);
			}
		}

		update_forum($fid); // Update the forum FROM which the topic was moved
		update_forum($move_to_forum); // Update the forum TO which the topic was moved

		$redirect_msg = (count($topics) > 1) ? $lang_misc['Move topics redirect'] : $lang_misc['Move topic redirect'];
		redirect(panther_link($panther_url['forum'], array($move_to_forum, $forum_name)), $redirect_msg);
	}

	if (isset($_POST['move_topics']))
	{
		$topics = isset($_POST['topics']) ? $_POST['topics'] : array();
		if (empty($topics))
			message($lang_misc['No topics selected']);

		$topics = implode(',', array_map('intval', array_keys($topics)));
		$action = 'multi';
	}
	else
	{
		// We only checked via REQUEST before
		$topics = isset($_GET['move_topics']) ? intval($_GET['move_topics']) : 0;
		if ($topics < 1)
			message($lang_common['Bad request'], false, '404 Not Found');

		$action = 'single';
	}
	
	$data = array(
		':gid'	=>	$panther_user['g_id'],
	);

	$ps = $db->run('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) WHERE (fp.post_topics IS NULL OR fp.post_topics=1) AND f.redirect_url IS NULL ORDER BY c.disp_position, c.id, f.disp_position', $data);
	if ($ps->rowCount() < 2)
		message($lang_misc['Nowhere to move']);
	
	($hook = get_extensions('moderate_forum_move_topics_before_header')) ? eval($hook) : null;

	$page_title = array($panther_config['o_board_title'], $lang_misc['Moderate']);
	define('PANTHER_ACTIVE_PAGE', 'index');
	require PANTHER_ROOT.'header.php';

	$cur_category = 0;
	$categories = $forums = array();
	foreach ($ps as $cur_forum)
	{
		if (!isset($categories[$cur_forum['cid']]))
		{
			$categories[$cur_forum['cid']] = array(
				'name' => $cur_forum['cat_name'],
				'id' => $cur_forum['cid'],
			);
		}

		$forums[] = array(
			'category_id' => $cur_forum['cid'],
			'id' => $cur_forum['fid'],
			'name' => $cur_forum['forum_name'],
		);
	}

	$tpl = load_template('move_topics.tpl');
	echo $tpl->render(
		array(
			'lang_common' => $lang_common,
			'lang_misc' => $lang_misc,
			'action' => $action,
			'form_action' => panther_link($panther_url['moderate_forum'], array($fid)),
			'csrf_token' => generate_csrf_token(),
			'topics' => $topics,
			'forums' => $forums,
			'categories' => $categories,
		)
	);

	require PANTHER_ROOT.'footer.php';
}
// Merge two or more topics
else if (isset($_POST['merge_topics']) || isset($_POST['merge_topics_comply']))
{
	if (isset($_POST['merge_topics_comply']))
	{
		confirm_referrer('moderate.php');

		if (@preg_match('%[^0-9,]%', $_POST['topics']))
			message($lang_common['Bad request'], false, '404 Not Found');

		$topics = explode(',', $_POST['topics']);
		if (count($topics) < 2)
			message($lang_misc['Not enough topics selected']);
		
		$data = array($fid);
		for ($i = 0; $i < count($topics); $i++)
		{
			$markers[] = '?';
			$data[] = $topics[$i];
		}

		// Verify that the topic IDs are valid (redirect links will point to the merged topic after the merge)
		$ps = $db->select('topics', 'id', $data, 'forum_id=? AND id IN ('.implode(',', $markers).')', 'id ASC');
		if ($ps->rowCount() != count($topics))
			message($lang_common['Bad request'], false, '404 Not Found');

		// The topic that we are merging into is the one with the smallest ID
		$merge_to_tid = $ps->fetchColumn();

		$data[0] = $merge_to_tid;
		// Make any redirect topics point to our new, merged topic
		$sql = 'UPDATE '.$db->prefix.'topics SET moved_to=? WHERE moved_to IN('.implode(',', $markers).')';

		// Should we create redirect topics?
		if (isset($_POST['with_redirect']))
		{
			$new_data = $data;
			$new_data[count($data)+1] = $data[0];	// Do whatever it takes to avoid a second loop =)
			unset($new_data[0]);

			$sql .= ' OR (id IN('.implode(',', $markers).') AND id !=?)';
		}
		else
			$new_data = array();

		$update = array_merge($new_data, $data);
		$db->run($sql, $update);

		$update = $data;

		// Merge the posts into the topic
		$db->run('UPDATE '.$db->prefix.'posts SET topic_id=? WHERE topic_id IN('.implode(',', $markers).')', $update);

		// Update any subscriptions
		unset($update[0]);
		$ps = $db->select('topic_subscriptions', 'DISTINCT user_id', array_values($update), 'topic_id IN('.implode(',', $markers).')');
		$ps->setFetchMode(PDO::FETCH_COLUMN, 0);
		foreach ($ps as $cur_user_id)
		{
			$insert = array(
				'topic_id'	=>	$merge_to_tid,
				'user_id'	=>	$cur_user_id,
			);
			
			$db->insert('topic_subscriptions', $insert);
		}

		$db->run('DELETE FROM '.$db->prefix.'topic_subscriptions WHERE topic_id IN('.implode(',', $markers).')', array_values($update));

		// Without redirection the old topics are removed
		if (!isset($_POST['with_redirect']))
		{
			$update[] = $merge_to_tid;
			$db->run('DELETE FROM '.$db->prefix.'topics WHERE id IN('.implode(',', $markers).') AND id !=?', array_values($update));
			$db->run('DELETE FROM '.$db->prefix.'polls WHERE topic_id IN('.implode(',', $markers).') AND id !=?', array_values($update));
		}

		// Count number of replies in the topic
		$data = array(
			':id'	=>	$merge_to_tid,
		);

		$ps = $db->select('posts', 'COUNT(id)', $data, 'topic_id=:id');
		$num_replies = $ps->fetchColumn() - 1;
		
		$data = array(
			':id'	=>	$fid,
		);

		$ps = $db->select('forums', 'forum_name', $data, 'id=:id');
		if (!$ps->rowCount())
			message($lang_common['Bad request']);
		else
			$forum_name = url_friendly($ps->fetchColumn());

		// Get last_post, last_post_id and last_poster
		$data = array(
			':id'	=>	$merge_to_tid,
		);

		$ps = $db->select('posts', 'posted, id, poster', $data, 'topic_id=:id', 'id DESC LIMIT 1');
		list($last_post, $last_post_id, $last_poster) = $ps->fetch(PDO::FETCH_NUM);

		// Update topic
		$update = array(
			'num_replies'	=>	$num_replies,
			'last_post'		=>	$last_post,
			'last_post_id'	=>	$last_post_id,
			'last_poster'	=>	$last_poster,
		);
		
		$data = array(
			':id'	=>	$merge_to_tid,
		);

		$db->update('topics', $update, 'id=:id', $data);
		// Update the forum FROM which the topic was moved and redirect
		update_forum($fid);
		redirect(panther_link($panther_url['forum'], array($fid, $forum_name)), $lang_misc['Merge topics redirect']);
	}

	$topics = isset($_POST['topics']) && is_array($_POST['topics']) ? $_POST['topics'] : array();
	if (count($topics) < 2)
		message($lang_misc['Not enough topics selected']);
	
	($hook = get_extensions('moderate_forum_merge_topics_before_header')) ? eval($hook) : null;

	$page_title = array($panther_config['o_board_title'], $lang_misc['Moderate']);
	define('PANTHER_ACTIVE_PAGE', 'index');
	require PANTHER_ROOT.'header.php';
	
	$tpl = load_template('merge_topics.tpl');
	echo $tpl->render(
		array(
			'lang_misc' => $lang_misc,
			'lang_common' => $lang_common,
			'form_action' => panther_link($panther_url['moderate_forum'], array($fid)),
			'csrf_token' => generate_csrf_token(),
			'topics' => implode(',', array_map('intval', array_keys($topics))),
		)
	);

	require PANTHER_ROOT.'footer.php';
}
else if (isset($_GET['multi_moderate']) || isset($_POST['multi_moderate_comply']))
{
	$tid = isset($_GET['multi_moderate']) ? intval($_GET['multi_moderate']) : '0';
	$action = isset($_POST['action']) ? intval($_POST['action']) : 0;

	if ($tid < 1)
		message($lang_misc['No topics selected']);
	
	if (isset($_POST['multi_moderate_comply']))
	{
		confirm_referrer('moderate.php');
	
		$data = array(
			':id'	=>	$tid,
		);

		$ps = $db->select('topics', 1, $data, 'id=:id AND approved=1 AND deleted=0');
		if (!$ps->rowCount())
			message($lang_common['Bad request']);

		//So, what rules are we applying here?
		$data = array(
			':id'	=>	$action
		);

		$ps = $db->select('multi_moderation', 'close, stick, move, archive, leave_redirect, reply_message, add_start, add_end, send_email, increment_posts', $data, 'id=:id');
		if (!$ps->rowCount())
			message($lang_common['Bad request']);
		else
			$moderation = $ps->fetch();

		$replace = array($panther_user['username'], get_title($panther_user), strip_tags($panther_config['o_board_title']), strip_tags($panther_config['o_board_desc']), '[email]'.$panther_config['o_admin_email'].'[/email]', '[email]'.$panther_config['o_webmaster_email'].'[/email]', '[email]'.$panther_user['email'].'[/email]', $panther_user['num_posts'], '[url]'.$panther_user['url'].'[/url]', $panther_user['realname']);
		$search = array('{username}', '{user_title}', '{board_title}', '{board_desc}', '{admin_email}', '{webmaster_email}', '{user_email}', '{user_posts}', '{website}', '{location}', '{real_name}');

		$data = $update = array();
		$moderation['reply_message'] = str_replace($search, $replace, $moderation['reply_message']);

		if ($moderation['close'] != '2')
			$update['closed'] = $moderation['close'];

		if ($moderation['stick'] != '2')
			$update['sticky'] = $moderation['stick'];
			
		if ($moderation['archive'] != '2')
			$update['archived'] = $moderation['archive'];

		if ($moderation['reply_message'] != '')
		{
			$insert = array(
				'poster'	=>	$panther_user['username'],
				'poster_id'	=>	$panther_user['id'],
				'poster_ip'	=>	get_remote_address(),
				'message'	=>	$moderation['reply_message'],
				'hide_smilies'	=>	0,
				'posted'	=>	time(),
				'topic_id'	=>	$tid,
			);

			$db->insert('posts', $insert);
			$new_pid = $db->lastInsertId($db->prefix.'posts');

			require PANTHER_ROOT.'include/search_idx.php';
			update_search_index('post', $new_pid, $moderation['reply_message']);
		}

		if ($moderation['move'] != '0')
		{
			$update['forum_id'] = $moderation['move'];
			if ($moderation['leave_redirect'] == '1')
			{						
				// Fetch info for the redirect topic
				$data = array(
					':id'	=>	$tid,
				);

				$ps = $db->select('topics', 'poster, subject, posted, last_post, forum_id', $data, 'id=:id');
				$moved_to = $ps->fetch();

					// Create the redirect topic
				$insert = array(
					'poster'	=>	$moved_to['poster'],
					'subject'	=>	$moderation['add_start'].$moved_to['subject'].$moderation['add_end'],
					'posted'	=>	$moved_to['posted'],
					'last_post'	=>	$moved_to['last_post'],
					'moved_to'	=>	$tid,
					'forum_id'	=>	$moved_to['forum_id'],
				);

				$db->insert('topics', $insert);
			}
		}

		//We may (not) need some of this, but we might as well get it all in one query regardless
		$data = array(
			':id'	=>	$tid,
		);

		$ps = $db->run('SELECT t.subject, p.message, p.poster_email, p.poster_id, u.email, u.id AS uid, u.language FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.first_post_id=p.id LEFT JOIN '.$db->prefix.'users AS u ON p.poster_id=u.id WHERE t.id=:id', $data);
		$topic = $ps->fetch();
				
		$email = ((!is_null($topic['poster_email'])) ? $topic['poster_email'] : $topic['email']);
					
		if ($moderation['add_start'] != '' || $moderation['add_end'] != '')
		{
			$update['subject'] = $moderation['add_start'].$topic['subject'].$moderation['add_end'];
			if (!defined('PANTHER_CJK_HANGUL_REGEX'))
				require PANTHER_ROOT.'include/search_idx.php';
						
			update_search_index('edit', $tid, $topic['message'], $moderation['add_start'].$topic['subject'].$moderation['add_end']);
		}

		if (!empty($update))
		{
			$data = array(
				':id'	=>	$tid,
			);

			$db->update('topics', $update, 'id=:id', $data);
		}
		
		$data = array(
			':id'	=>	$tid,
		);

		$ps = $db->select('posts', 'COUNT(id)', $data, 'topic_id=:id AND approved=1 AND deleted=0');
		$num_replies = $ps->fetchColumn() -1;
	
		// Get last_post, last_post_id and last_poster
		$data = array(
			':id'	=>	$tid,
		);

		$ps = $db->select('posts', 'posted, id, poster, poster_id', $data, 'topic_id=:id AND approved=1 AND deleted=0', 'id DESC LIMIT 1');
		$last_topic = $ps->fetch();
	
		// Update topic
		$update = array(
			'num_replies' => $num_replies,
			'last_post' => $last_topic['posted'],
			'last_post_id' => $last_topic['id'],
			'last_poster' => $last_topic['poster'],
		);

		$data = array(
			':id'	=>	$tid,
		);
		
		$db->update('topics', $update, 'id=:id', $data);
		update_forum($fid);

		if ($moderation['move'] !== '0')
			update_forum($moderation['move']);
		
		if ($moderation['increment_posts'] == '1')
		{
			$data = array(
				':time'	=>	time(),
				':id'	=>	$panther_user['id'],
			);

			$db->run('UPDATE '.$db->prefix.'users SET num_posts=num_posts+1, last_post=:time WHERE id=:id', $data);

			// Promote this user to a new group if enabled
			if ($panther_user['g_promote_next_group'] != 0 && $panther_user['num_posts'] + 1 >= $panther_user['g_promote_min_posts'])
			{
				$update = array(
					'group_id'	=>	$panther_user['g_promote_next_group'],
				);
				
				$data = array(
					':id'	=>	$panther_user['id'],
				);
				
				$db->update('users', $update, 'id=:id', $data);
			}
		}
		
		($hook = get_extensions('moderate_multi_moderate_before_email')) ? eval($hook) : null;

		if ($moderation['send_email'] == '1' && $moderation['reply_message'] != '' && $panther_user['id'] != $topic['poster_id'])
		{
			require PANTHER_ROOT.'include/email.php';
			
			$info = array(
				'subject' => array(
					'<topic_subject>' => $topic['subject'],
				),
				'message' => array(
					'<topic_subject>' => $topic['subject'],
					'<replier>' => $panther_user['username'],
					'<message>' => $moderation['reply_message'],
					'<post_url>' => panther_link($panther_url['post'], array($new_pid)),
				)
			);

			$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/new_action.tpl', $info);
			$mailer->send($email, $mail_tpl['subject'], $mail_tpl['message']);
		}

		redirect(panther_link($panther_url['topic'], array($tid, url_friendly($topic['subject']))), $lang_misc['multi_mod redirect']);
	}
	
	$ps = $db->select('multi_moderation', 'title, id');
	if (!$ps->rowCount())
		message($lang_misc['No multi moderation']);
	else
	{
		$actions = array();
		foreach ($ps as $action)
			$actions[] = array('title' => $action['title'], 'id' => $action['id']);
	}
	
	($hook = get_extensions('multi_moderate_before_header')) ? eval($hook) : null;

	$page_title = array($panther_config['o_board_title'], $lang_misc['Moderate']);
	define('PANTHER_ACTIVE_PAGE', 'index');
	require PANTHER_ROOT.'header.php';

	$csrf_token = generate_csrf_token();
	$tpl = load_template('multi_moderate.tpl');
	echo $tpl->render(
		array(
			'lang_misc' => $lang_misc,
			'actions' => $actions,
			'csrf_token' => $csrf_token,
			'form_action' => panther_link($panther_url['moderate_multi'], array($fid, $tid, $csrf_token)),
			'lang_common' => $lang_common,
		)
	);

	require PANTHER_ROOT.'footer.php';
}
else if (isset($_GET['unapprove']))
{
	// Why on earth do I do these things? =)
	$pid = ((isset($_POST['unapprove'])) ? intval($_POST['unapprove']) : (isset($_GET['unapprove']) ? intval($_GET['unapprove']) : 0));

	if ($pid < 1)
		message($lang_misc['No topics selected']);

	if (isset($_POST['unapprove_comply']))
	{
		confirm_referrer('moderate.php');

		if (!defined('PANTHER_CJK_HANGUL_REGEX'))
			require PANTHER_ROOT.'include/search_idx.php';

		//Is this a topic post?
		$data = array(
			':id'	=>	$pid,
			':fid'	=>	$fid,
		);

		$ps = $db->run('SELECT t.id AS tid, t.subject, t.first_post_id, t.num_replies, f.id AS fid, f.forum_name FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON p.topic_id=t.id INNER JOIN '.$db->prefix.'forums AS f ON t.forum_id=f.id WHERE p.id=:id AND f.id=:fid', $data);	
		if (!$ps->rowCount())
			message($lang_common['Bad request']);
		else
			$cur_post = $ps->fetch();
		
		($hook = get_extensions('unapprove_before_validation')) ? eval($hook) : null;

		$is_topic_post = ($pid == $cur_post['first_post_id']) ? true : false;
		if ($is_topic_post)
		{
			$update = array(
				'approved'	=>	0,
			);
			
			$data = array(
				':id'	=>	$cur_post['tid'],
			);
			
			$db->update('posts', $update, 'topic_id=:id', $data);
			$db->update('topics', $update, 'id=:id', $data);	// Make sure the topic isn't displayed in the forum without any posts!

			$posts = array();
			$ps = $db->select('posts', 'id', $data, 'topic_id=:id');
			foreach ($ps as $cur_row)
				$posts[] = $cur_row['id'];

			// Make sure we have a list of post IDs
			if (!empty($posts))
				strip_search_index($posts);
			
			update_forum($cur_post['fid']);
			redirect(panther_link($panther_url['forum'], array($cur_post['fid'], url_friendly($cur_post['forum_name']))), $lang_misc['unapproved topic redirect']);
		}
		else
		{
			$update = array(
				'approved'	=>	0,
			);
			
			$data = array(
				':id'	=>	$pid,
			);
			
			$db->update('posts', $update, 'id=:id', $data);
			strip_search_index(array($pid));
			
			$data = array(
				':id'	=>	$cur_post['tid'],
			);
		
			// Update the topic
			$ps = $db->select('posts', 'posted, id, poster', $data, 'topic_id=:id AND approved=1 AND deleted=0', 'id DESC LIMIT 1');
			$topic = $ps->fetch();
	
			$update = array(
				'num_replies'	=>	($cur_post['num_replies']-1),
				'last_post'	=>	$topic['posted'],
				'last_post_id'	=>	$topic['id'],
				'last_poster'	=>	$topic['poster'],
			);
		
			$db->update('topics', $update, 'id=:id', $data);
			
			update_forum($cur_post['fid']);
			redirect(panther_link($panther_url['topic'], array($cur_post['tid'], url_friendly($cur_post['subject']))), $lang_misc['unapproved post redirect']);
		}		
	}
	
	($hook = get_extensions('unapprove_before_header')) ? eval($hook) : null;
			
	$page_title = array($panther_config['o_board_title'], $lang_misc['Moderate']);
	define('PANTHER_ACTIVE_PAGE', 'index');
	require PANTHER_ROOT.'header.php';
	
	$tpl = load_template('unapprove_post.tpl');
	echo $tpl->render(
		array(
			'lang_misc' => $lang_misc,
			'form_action' => panther_link($panther_url['unapprove'], array($fid, $pid, generate_csrf_token())),
			'lang_common' => $lang_common,
		)
	);

	require PANTHER_ROOT.'footer.php';
}

// Archive/unarchive multiple topics (admins only)
else if (isset($_POST['archive_topics']) || isset($_POST['unarchive_topics']))
{
	if (!$panther_user['is_admin'])
		message($lang_common['No permission']);

	confirm_referrer('moderate.php');

	$topics = ((isset($_POST['topics']) && is_array($_POST['topics'])) ? array_map('intval', array_keys($_POST['topics'])) : array());
	if (empty($topics))
		message($lang_misc['No topics selected']);

	$action = (isset($_POST['archive_topics'])) ? 1 : 2;
	
	($hook = get_extensions('archive_topics_before_validation')) ? eval($hook) : null;

	$data = array($action, $fid);
	$markers = array();
	for ($i = 0; $i < count($topics); $i++)
	{
		$data[] = $topics[$i];
		$markers[] = '?';
	}

	$db->run('UPDATE '.$db->prefix.'topics SET archived=? WHERE forum_id=? AND approved=1 AND deleted=0 AND id IN ('.implode(',', $markers).')', $data);

	$data = array(
		':id'	=>	$fid,
	);

	$ps = $db->select('forums', 'forum_name', $data, 'id=:id');
	$forum_name = url_friendly($ps->fetchColumn());

	$redirect_lang = ($action == '1') ? $lang_misc['Archive topics redirect'] : $lang_misc['Unarchive topics redirect'];
	redirect(panther_link($panther_url['forum'], array($fid, url_friendly($forum_name))), $redirect_lang);
}

// Delete one or more topics
else if (isset($_POST['delete_topics']) || isset($_POST['delete_topics_comply']))
{
	$topics = ((isset($_POST['topics']) && is_array($_POST['topics'])) ? array_map('intval', $_POST['topics']) : (isset($_POST['topics']) ? array_map('intval', explode(',', $_POST['topics'])) : array()));

	if (empty($topics))
		message($lang_misc['No topics selected']);

	if (isset($_POST['delete_topics_comply']))
	{
		confirm_referrer('moderate.php');
		require PANTHER_ROOT.'include/search_idx.php';

		$data = array($fid);
		$markers = array();
		for ($i = 0; $i < count($topics); $i++)
		{
			$data[] = $topics[$i];
			$markers[] = '?';
		}

		// Verify that the topic IDs are valid
		$markers = implode(',', $markers);
		$ps = $db->select('topics', 1, $data, 'forum_id=? AND id IN('.$markers.')');

		if ($ps->rowCount() != count($topics))
			message($lang_common['Bad request'], false, '404 Not Found');
		
		unset($data[0]);

		// Verify that the posts are not by admins
		if (!$panther_user['is_admin'] && $panther_user['g_mod_edit_admin_posts'] == '0')
		{
			$admins = get_admin_ids();
			for ($i = 0; $i < count($admins); $i++)
			{
				$markers_2[] = '?';
				$data_2[] = $admins[$i];
			}

			$select = array_merge($data_2, $data);
			$ps = $db->select('posts', 1, array_values($select), 'topic_id IN('.$markers.') AND poster_id IN('.implode(',', $markers_2).')');
			if ($ps->rowCount())
				message($lang_common['No permission'], false, '403 Forbidden');
		}
		
		foreach (array_values($data) as $tid)
			attach_delete_thread($tid);

		// Delete the topics and any redirect topics
		$delete = array_merge($data, $data);
		if ($panther_config['o_delete_full'] == '1')
			$db->run('DELETE FROM '.$db->prefix.'topics WHERE id IN('.$markers.') OR moved_to IN('.$markers.')', array_values($delete));
		else
			$db->run('UPDATE '.$db->prefix.'topics SET deleted=1 WHERE id IN('.$markers.') OR moved_to IN('.$markers.')', array_values($delete));

		// Delete any subscriptions
		$db->run('DELETE FROM '.$db->prefix.'topic_subscriptions WHERE topic_id IN('.$markers.')', array_values($data));

		// Create a list of the post IDs in this topic and then strip the search index
		$ps = $db->select('posts', 'id', array_values($data), 'topic_id IN('.$markers.')');

		$post_ids = array();
		foreach ($ps as $row)
			$post_ids[] = $row['id'];

		// We have to check that we actually have a list of post IDs since we could be deleting just a redirect topic
		if (!empty($post_ids))
			strip_search_index($post_ids);

		// Delete posts
		$db->run('DELETE FROM '.$db->prefix.'posts WHERE topic_id IN('.$markers.')', array_values($data));
		
		// Delete polls
		$db->run('DELETE FROM '.$db->prefix.'polls WHERE topic_id IN('.$markers.')', array_values($data));

		update_forum($fid);
		$data = array(
			':id'	=>	$fid,
		);
		
		$ps = $db->select('forums', 'forum_name', $data, 'id=:id');
		$forum_name = url_friendly($ps->fetchColumn());
		redirect(panther_link($panther_url['forum'], array($fid, $forum_name)), $lang_misc['Delete topics redirect']);
	}
	
	($hook = get_extensions('delete_topics_before_header')) ? eval($hook) : null;

	$page_title = array($panther_config['o_board_title'], $lang_misc['Moderate']);
	define('PANTHER_ACTIVE_PAGE', 'index');
	require PANTHER_ROOT.'header.php';
	
	$tpl = load_template('delete_topics.tpl');
	echo $tpl->render(
		array(
			'lang_misc' => $lang_misc,
			'lang_common' => $lang_common,
			'form_action' => panther_link($panther_url['moderate_forum'], array($fid)),
			'csrf_token' => generate_csrf_token(),
			'topics' => implode(',', array_map('intval', array_keys($topics))),
		)
	);

	require PANTHER_ROOT.'footer.php';
}

// Open or close one or more topics
else if (isset($_REQUEST['open']) || isset($_REQUEST['close']))
{
	$action = (isset($_REQUEST['open'])) ? 0 : 1;

	// There could be an array of topic IDs in $_POST
	if (isset($_POST['open']) || isset($_POST['close']))
	{
		confirm_referrer('moderate.php');

		$topics = isset($_POST['topics']) && is_array($_POST['topics']) ? array_map('intval', array_keys($_POST['topics'])) : array();
		if (empty($topics))
			message($lang_misc['No topics selected']);
		
		$data = array(
			':id'	=>	$fid,
		);
		
		$ps = $db->select('forums', 'forum_name', $data, 'id=:id');
		if (!$ps->rowCount())
			message($lang_common['Bad request']);
		else
			$forum_name = url_friendly($ps->fetchColumn());
		
		$data = array($action, $fid);
		for ($i = 0; $i < count($topics); $i++)
		{
			$markers[] = '?';
			$data[] = $topics[$i];
		}

		$db->run('UPDATE '.$db->prefix.'topics SET closed=? WHERE forum_id=? AND id IN('.implode(',', $markers).')', $data);

		$redirect_msg = ($action) ? $lang_misc['Close topics redirect'] : $lang_misc['Open topics redirect'];
		redirect(panther_link($panther_url['moderate_forum'], array($fid, $forum_name)), $redirect_msg);
	}
	else // Or just one in $_GET
	{
		confirm_referrer('viewtopic.php');

		$topic_id = ($action) ? intval($_GET['close']) : intval($_GET['open']);
		if ($topic_id < 1)
			message($lang_common['Bad request'], false, '404 Not Found');

		$data = array(
			':id'	=>	$topic_id,
		);

		$ps = $db->select('topics', 'subject', $data, 'id=:id');
		if (!$ps->rowCount())
			message($lang_common['Bad request']);
		else
			$subject = url_friendly($ps->fetchColumn());
		
		$update = array(
			'closed'	=>	$action,
		);

		$data = array(
			':id'	=>	$topic_id,
			':fid'	=>	$fid,
		);
		
		$db->update('topics', $update, 'id=:id AND forum_id=:fid', $data);

		$redirect_msg = ($action) ? $lang_misc['Close topic redirect'] : $lang_misc['Open topic redirect'];
		redirect(panther_link($panther_url['topic'], array($topic_id, $subject)), $redirect_msg);
	}
}
// Stick or Unstick a topic
else if (isset($_GET['stick']) || isset($_GET['unstick']))
{
	confirm_referrer('viewtopic.php');
	$action = (isset($_GET['unstick'])) ? 0 : 1;
	$topic_id = ($action) ? intval($_GET['stick']) : intval($_GET['unstick']);

	if ($topic_id < 1)
		message($lang_common['Bad request'], false, '404 Not Found');
	
	($hook = get_extensions('stick_unstick_topic_before_update')) ? eval($hook) : null;

	$data = array(
		':id'	=>	$topic_id,
	);

	$ps = $db->select('topics', 'subject', $data, 'id=:id');
	if (!$ps->rowCount())
		message($lang_common['Bad request']);
	else
		$subject = url_friendly($ps->fetchColumn());
	
	$data = array(
		':id'	=>	$topic_id,
		':fid'	=>	$fid,
	);

	$update = array(
		'sticky'	=>	$action,
	);
		
	$db->update('topics', $update, 'id=:id AND forum_id=:fid', $data);
	$redirect_msg = ($action) ? $lang_misc['Stick topic redirect'] : $lang_misc['Unstick topic redirect'];
	redirect(panther_link($panther_url['topic'], array($topic_id, $subject)), $redirect_msg);
}
else if (isset($_GET['unarchive']) || isset($_GET['archive']))
{
	confirm_referrer('viewtopic.php');
	$action = (isset($_GET['unarchive'])) ? 2 : 1;
	$topic_id = ($action == '1') ? intval($_GET['archive']) : intval($_GET['unarchive']);

	if ($topic_id < 1)
		message($lang_common['Bad request'], false, '404 Not Found');
	
	($hook = get_extensions('unarchive_topics_before_update')) ? eval($hook) : null;

	$data = array(
		':id'	=>	$topic_id,
	);

	$ps = $db->select('topics', 'subject', $data, 'id=:id');
	if (!$ps->rowCount())
		message($lang_common['Bad request']);
	else
		$subject = url_friendly($ps->fetchColumn());
	
	$data = array(
		':id'	=>	$topic_id,
		':fid'	=>	$fid,
	);

	$update = array(
		'archived'	=>	$action,
	);
		
	$db->update('topics', $update, 'id=:id AND forum_id=:fid', $data);
	$redirect_msg = ($action == '1') ? $lang_misc['Archived topic redirect'] : $lang_misc['Unarchived topic redirect'];
	redirect(panther_link($panther_url['topic'], array($topic_id, $subject)), $redirect_msg);
}

// No specific forum moderation action was specified in the query string, so we'll display the moderator forum

// Load the viewforum.php language file
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/forum.php';

// Fetch some info about the forum
$data = array(
	':gid'	=>	$panther_user['g_id'],
	':fid'	=>	$fid,
);
$ps = $db->run('SELECT f.forum_name, f.redirect_url, f.num_topics, f.sort_by FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.id=:fid', $data);
if (!$ps->rowCount())
	message($lang_common['Bad request'], false, '404 Not Found');

$cur_forum = $ps->fetch();

// Is this a redirect forum? In that case, abort!
if ($cur_forum['redirect_url'] != '')
	message($lang_common['Bad request'], false, '404 Not Found');

switch ($cur_forum['sort_by'])
{
	case 0:
		$sort_by = 'last_post DESC';
		break;
	case 1:
		$sort_by = 'posted DESC';
		break;
	case 2:
		$sort_by = 'subject ASC';
		break;
	default:
		$sort_by = 'last_post DESC';
		break;
}

($hook = get_extensions('moderate_forum_before_header')) ? eval($hook) : null;

// Determine the topic offset (based on $_GET['p'])
$num_pages = ceil($cur_forum['num_topics'] / $panther_user['disp_topics']);

$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);
$start_from = $panther_user['disp_topics'] * ($p - 1);

$page_title = array($panther_config['o_board_title'], $cur_forum['forum_name']);
define('PANTHER_ACTIVE_PAGE', 'index');
require PANTHER_ROOT.'header.php';

// Retrieve a list of topic IDs, LIMIT is (really) expensive so we only fetch the IDs here then later fetch the remaining data
$data = array(
	':id'	=>	$fid,
);

$ps = $db->run('SELECT id FROM '.$db->prefix.'topics WHERE forum_id=:id AND deleted=0 AND approved=1 ORDER BY sticky DESC, '.$sort_by.', id DESC LIMIT '.$start_from.', '.$panther_user['disp_topics'], $data);

// If there are topics in this forum
if ($ps->rowCount())
{
	$topic_ids = $topics = array();
	$ps->setFetchMode(PDO::FETCH_COLUMN, 0);
	foreach ($ps as $cur_topic_id)
	{
		$topic_ids[] = $cur_topic_id;
		$markers[] = '?';
	}

	// Select topics
	$ps = $db->run('SELECT u.id AS uid, u.group_id, up.id AS up_id, up.group_id AS up_group_id, u.use_gravatar, u.email, t.id, t.poster, t.subject, t.question, t.posted, t.last_post, t.last_post_id, t.last_poster, t.num_views, t.num_replies, t.closed, t.sticky, t.moved_to FROM '.$db->prefix.'topics AS t LEFT JOIN '.$db->prefix.'users AS u ON (t.last_poster=u.username) LEFT JOIN '.$db->prefix.'users AS up ON (t.poster=up.username) WHERE t.id IN ('.implode(',', $markers).')'.' ORDER BY t.sticky DESC, t.'.$sort_by.', t.id DESC', $topic_ids);
	$topic_count = 0;
	foreach ($ps as $cur_topic)
	{
		$url_subject = url_friendly($cur_topic['subject']); // Preg match is slow!

		if ($panther_config['o_censoring'] == '1')
			$cur_topic['subject'] = censor_words($cur_topic['subject']);

		$ghost_topic = (!is_null($cur_topic['moved_to'])) ? true : false;
		$num_pages_topic = ceil(($cur_topic['num_replies'] + 1) / $panther_user['disp_posts']);
		$topics[$cur_topic['id']] = array(
			'count' => ++$topic_count,
			'topic_count' => forum_number_format($topic_count + $start_from),
			'cur_topic' => $cur_topic,
			'topic_poster' => ($cur_topic['up_id'] > 1) ? colourize_group($cur_topic['poster'], $cur_topic['up_group_id'], $cur_topic['up_id']) : colourize_group($cur_topic['poster'], PANTHER_GUEST),
			'moved_to' => $cur_topic['moved_to'],
			'subject' => $cur_topic['subject'],
			'sticky' => $cur_topic['sticky'],
			'closed' => $cur_topic['closed'],
			'topic_link' => panther_link($panther_url['topic'], array($cur_topic['id'], $url_subject)),
			'num_pages' => $num_pages_topic,
			'pagination' => paginate($num_pages_topic, -1, $panther_url['topic_paginate'], array($cur_topic['id'], $url_subject)),
			'new' => (!$ghost_topic && $cur_topic['last_post'] > $panther_user['last_visit'] && (!isset($tracked_topics['topics'][$cur_topic['id']]) || $tracked_topics['topics'][$cur_topic['id']] < $cur_topic['last_post']) && (!isset($tracked_topics['forums'][$fid]) || $tracked_topics['forums'][$fid] < $cur_topic['last_post'])) ? '1' : '0',
		);

		if (is_null($cur_topic['moved_to']))
		{
			$topics[$cur_topic['id']]['last_post_avatar'] = generate_avatar_markup($cur_topic['uid'], $cur_topic['email'], $cur_topic['use_gravatar'], array(32, 32));
			$topics[$cur_topic['id']]['last_post_link'] = panther_link($panther_url['post'], array($cur_topic['last_post_id']));
			$topics[$cur_topic['id']]['last_post'] = format_time($cur_topic['last_post']);
			$topics[$cur_topic['id']]['last_poster'] = ($cur_topic['uid'] > 1) ? colourize_group($cur_topic['last_poster'], $cur_topic['group_id'], $cur_topic['uid']) : colourize_group($cur_topic['last_poster'], PANTHER_GUEST);
			$topics[$cur_topic['id']]['num_replies'] = forum_number_format($cur_topic['num_replies']);

			if ($panther_config['o_topic_views'] == '1')
				$topics[$cur_topic['id']]['num_views'] = forum_number_format($cur_topic['num_views']);
		}
		else
			$topics[$cur_topic['id']]['topic_link'] = panther_link($panther_url['topic'], array($cur_topic['moved_to'], $url_subject));

		if ($topics[$cur_topic['id']]['new'] == '1')
			$topics[$cur_topic['id']]['new_link'] = panther_link($panther_url['topic_new_posts'], array($cur_topic['id'], $url_subject));
	}
}

$tpl = load_template('moderate_forum.tpl');
echo $tpl->render(
	array(
		'lang_common' => $lang_common,
		'lang_misc' => $lang_misc,
		'lang_forum' => $lang_forum,
		'index_link' => panther_link($panther_url['index']),
		'forum_link' => panther_link($panther_url['moderate_forum'], array($fid, url_friendly($cur_forum['forum_name']))),
		'pagination' => paginate($num_pages, $p, $panther_url['moderate_forum'], array($fid)),
		'forum' => $cur_forum,
		'form_action' => panther_link($panther_url['moderate_forum'], array($fid)),
		'panther_config' => $panther_config,
		'csrf_token' => generate_csrf_token(),
		'panther_user' => $panther_user,
		'topics' => $topics,
	)
);

($hook = get_extensions('moderate_forum_after_output')) ? eval($hook) : null;

require PANTHER_ROOT.'footer.php';