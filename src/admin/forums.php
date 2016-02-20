<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

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

// Load the admin_forums.php language file
require PANTHER_ROOT.'lang/'.$admin_language.'/admin_forums.php';

// Add a "default" forum
if (isset($_POST['add_forum']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/forums.php');

	$add_to_cat = isset($_POST['add_to_cat']) ? intval($_POST['add_to_cat']) : '';
	if ($add_to_cat < 1)
		message($lang_common['Bad request'], false, '404 Not Found');

	$insert = array(
		'forum_name' => $lang_admin_forums['New forum'],
		'cat_id' => $add_to_cat,
	);

	$db->insert('forums', $insert);
	$new_fid = $db->lastInsertId($db->prefix.'forums');

	// Regenerate the quick jump cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_forums_cache();
	generate_announcements_cache();
	generate_quickjump_cache();
	generate_perms_cache();

	redirect(panther_link($panther_url['edit_forum'], array($new_fid)), $lang_admin_forums['Forum added redirect']);
}
else if (isset($_GET['del_forum'])) // Delete a forum
{

	$forum_id = intval($_GET['del_forum']);
	if ($forum_id < 1)
		message($lang_common['Bad request'], false, '404 Not Found');

	if (isset($_POST['del_forum_comply'])) // Delete a forum with all posts
	{
		confirm_referrer(PANTHER_ADMIN_DIR.'/forums.php');
		@set_time_limit(0);

		// Prune all posts and topics
		prune($forum_id, 1, -1);

		// Locate any "orphaned redirect topics" and delete them
		$ps = $db->run('SELECT t1.id FROM '.$db->prefix.'topics AS t1 LEFT JOIN '.$db->prefix.'topics AS t2 ON t1.moved_to=t2.id WHERE t2.id IS NULL AND t1.moved_to IS NOT NULL');
		if ($ps->rowCount())
		{
			$markers = $orphans = array();
			$ps->setFetchMode(PDO::FETCH_COLUMN, 0);
			foreach ($ps as $orphan)
			{
				$orphans[] = $orphan;
				$markers[] = '?';
			}
				
			$db->run('DELETE FROM '.$db->prefix.'topics WHERE id IN ('.implode(',', $markers).')', $orphans);
		}

		// Delete the forum and any forum specific group permissions
		$data = array(
			':id' => $forum_id,
		);

		$db->delete('forums', 'id=:id', $data);
		$db->delete('forum_perms', 'forum_id=:id', $data);

		// Delete any subscriptions for this forum
		$db->delete('forum_subscriptions', 'forum_id=:id', $data);

		// Regenerate the quick jump cache
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';

		generate_forums_cache();
		generate_announcements_cache();
		generate_quickjump_cache();
		generate_perms_cache();

		redirect(panther_link($panther_url['admin_forums']), $lang_admin_forums['Forum deleted redirect']);
	}
	else // If the user hasn't confirmed the delete
	{
		$data = array(
			':id' => $forum_id,
		);

		$ps = $db->select('forums', 'forum_name', $data, 'id=:id');
		$forum_name = $ps->fetchColumn();

		$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Forums']);
		define('PANTHER_ACTIVE_PAGE', 'admin');
		require PANTHER_ROOT.'header.php';

		generate_admin_menu('forums');

		$tpl = load_template('delete_forum.tpl');
		echo $tpl->render(
			array(
				'lang_admin_forums' => $lang_admin_forums,
				'lang_admin_common' => $lang_admin_common,
				'form_action' => panther_link($panther_url['del_forum'], array($forum_id)),
				'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/forums.php'),
				'forum_name' => $forum_name,
			)
		);

		require PANTHER_ROOT.'footer.php';
	}
}

// Update forum positions
else if (isset($_POST['update_positions']) && isset($_POST['position']) && is_array($_POST['position']))
{
	confirm_referrer(PANTHER_ADMIN_DIR.'/forums.php');
	foreach ($_POST['position'] as $forum_id => $disp_position)
	{
		$disp_position = isset($disp_position) ? panther_trim($disp_position) : 0;
		if ($disp_position < 0)
			message($lang_admin_forums['Must be integer message']);
		
		$update = array(
			'disp_position'	=>	$disp_position,
		);

		$data = array(
			':id'	=>	intval($forum_id),
		);

		$db->update('forums', $update, 'id=:id', $data);
	}

	// Regenerate the quick jump cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PANTHER_ROOT.'include/cache.php';

	generate_forums_cache();
	generate_quickjump_cache();
	generate_perms_cache();

	redirect(panther_link($panther_url['admin_forums']), $lang_admin_forums['Forums updated redirect']);
}

else if (isset($_GET['edit_forum']))
{
	$forum_id = intval($_GET['edit_forum']);
	if ($forum_id < 1)
		message($lang_common['Bad request'], false, '404 Not Found');

	// Update group permissions for $forum_id
	if (isset($_POST['save']))
	{
		confirm_referrer(PANTHER_ADMIN_DIR.'/forums.php');

		// Start with the forum details
		$forum_name = isset($_POST['forum_name']) ? panther_trim($_POST['forum_name']) : '';
		$forum_desc = isset($_POST['forum_desc']) ? panther_linebreaks(panther_trim($_POST['forum_desc'])) : '';
		$cat_id = isset($_POST['cat_id']) ? intval($_POST['cat_id']) : '';
		$sort_by = isset($_POST['sort_by']) ? intval($_POST['sort_by']) : '';
		$redirect_url = isset($_POST['redirect_url']) ? panther_trim($_POST['redirect_url']) : null;
		$use_reputation = isset($_POST['use_reputation']) && $_POST['use_reputation'] == '1' ? '1' : '0';
		$force_approve = isset($_POST['moderator_approve']) ? intval($_POST['moderator_approve']) : '0';
		$parent_forum = isset($_POST['parent_forum']) ? intval($_POST['parent_forum']) : '0';
		$show_post_info = isset($_POST['show_post_info']) ? intval($_POST['show_post_info']) :'1';
		$forum_password1 = isset($_POST['forum_password1']) ? panther_trim($_POST['forum_password1']) : '';
		$forum_password2 = isset($_POST['forum_password2']) ? panther_trim($_POST['forum_password2']) : '';
		$change_password = isset($_POST['change_forum_pass']) ? intval($_POST['change_forum_pass']) : '0';
		$quickjump = isset($_POST['quickjump']) ? intval($_POST['quickjump']) : '1';
		$protected = isset($_POST['protected']) ? intval($_POST['protected']) : '0';
		$increment_posts = isset($_POST['increment_posts']) ? intval($_POST['increment_posts']) : 1;

		if ($forum_name == '')
			message($lang_admin_forums['Must enter name message']);

		if ($cat_id < 1)
			message($lang_common['Bad request'], false, '404 Not Found');
		
		$data = array(
			':id'	=>	$forum_id,
		);
		
		if ($change_password == '1')
		{
			if ($forum_password1 == $forum_password2)
			{
				if ($forum_password1 == '')
				{
					$update = array(
						'password'	=>	'',
						'salt' => '',
					);
					
					$db->update('forums', $update, 'id=:id', $data);
				}
				else
				{
					$salt = random_key(12, true);
					$update = array(
						'password'	=>	panther_hash($forum_password1.panther_hash($salt)),
						'salt' => $salt,
					);
					
					$db->update('forums', $update, 'id=:id', $data);					
				}
			}
			else
				message($lang_admin_forums['passwords do not match']);
		}

		$forum_desc = ($forum_desc != '') ? $forum_desc : null;
		$redirect_url = ($redirect_url != '') ? $redirect_url : null;

		$update = array(
			'forum_name'	=>	$forum_name,
			'forum_desc'	=>	$forum_desc,
			'use_reputation'	=>	$use_reputation,
			'parent_forum'	=>	$parent_forum,
			'redirect_url'	=>	$redirect_url,
			'force_approve'	=>	$force_approve,
			'sort_by'		=>	$sort_by,
			'cat_id'		=>	$cat_id,
			'show_post_info'	=>	$show_post_info,
			'quickjump'		=>	$quickjump,
			'protected'		=>	$protected,
			'increment_posts'	=>	$increment_posts,
		);

		$db->update('forums', $update, 'id=:id', $data);

		// Now let's deal with the permissions
		if (isset($_POST['read_forum_old']))
		{
			foreach ($panther_groups as $cur_group)
			{
				if ($cur_group['g_id'] != PANTHER_ADMIN)
				{
					$read_forum_new = ($cur_group['g_read_board'] == '1') ? isset($_POST['read_forum_new'][$cur_group['g_id']]) ? '1' : '0' : intval($_POST['read_forum_old'][$cur_group['g_id']]);
					$post_replies_new = isset($_POST['post_replies_new'][$cur_group['g_id']]) ? '1' : '0';
					$post_topics_new = isset($_POST['post_topics_new'][$cur_group['g_id']]) ? '1' : '0';
					$post_polls_new = isset($_POST['post_polls_new'][$cur_group['g_id']]) ? '1' : '0';
					$upload_new = isset($_POST['upload_new'][$cur_group['g_id']]) ? '1' : '0';
					$download_new = isset($_POST['download_new'][$cur_group['g_id']]) ? '1' : '0';
					$delete_new = isset($_POST['delete_new'][$cur_group['g_id']]) ? '1' : '0';

					// Check if the new settings differ from the old
					if ($read_forum_new != $_POST['read_forum_old'][$cur_group['g_id']] || $post_replies_new != $_POST['post_replies_old'][$cur_group['g_id']] || $post_polls_new != $_POST['post_polls_old'][$cur_group['g_id']] || $post_topics_new != $_POST['post_topics_old'][$cur_group['g_id']] || $upload_new != $_POST['upload_old'][$cur_group['g_id']] || $download_new != $_POST['download_old'][$cur_group['g_id']] || $delete_new != $_POST['delete_old'][$cur_group['g_id']])
					{
						// If the new settings are identical to the default settings for this group, delete its row in forum_perms
						if ($read_forum_new == '1' && $post_replies_new == $cur_group['g_post_replies'] && $post_topics_new == $cur_group['g_post_topics'] && $post_polls_new == $cur_group['g_post_polls'] && $upload_new == $cur_group['g_attach_files'] && $delete_new == $cur_group['g_delete_posts'] && $download_new == '1')
						{
							$data = array(
								':gid'	=>	$cur_group['g_id'],
								':fid'	=>	$forum_id,
							);

							$db->delete('forum_perms', 'group_id=:gid AND forum_id=:fid', $data);
						}
						else
						{
							$data = array(
								':group_id'	=>	$cur_group['g_id'],
								':forum_id'	=>	$forum_id,
								':read_forum'	=>	$read_forum_new,
								':post_replies'	=>	$post_replies_new,
								':post_polls'	=>	$post_polls_new,
								':post_topics'	=>	$post_topics_new,
								':upload'		=>	$upload_new,
								':download'		=>	$download_new,
								':delete'		=>	$delete_new,
							);

							$db->run('REPLACE INTO '.$db->prefix.'forum_perms (group_id, forum_id, read_forum, post_replies, post_topics, post_polls, upload, download, delete_files) VALUES(:group_id, :forum_id, :read_forum, :post_replies, :post_topics, :post_polls, :upload, :download, :delete)', $data);
						}
					}
				}
			}
		}

		// Regenerate the quick jump cache
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';

		generate_forums_cache();
		generate_quickjump_cache();
		generate_perms_cache();

		redirect(panther_link($panther_url['admin_forums']), $lang_admin_forums['Forum updated redirect']);
	}
	else if (isset($_POST['revert_perms']))
	{
		confirm_referrer(PANTHER_ADMIN_DIR.'/forums.php');
		$data = array(
			':id'	=>	$forum_id,
		);

		$db->delete('forum_perms', 'forum_id=:id', $data);

		// Regenerate the quick jump cache
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';

		generate_forums_cache();
		generate_quickjump_cache();
		generate_perms_cache();

		redirect(panther_link($panther_url['edit_forum'], array($forum_id)), $lang_admin_forums['Perms reverted redirect']);
	}

	// Fetch forum info
	$data = array(
		':id'	=>	$forum_id,
	);

	if (!isset($panther_forums[$forum_id]))
		message($lang_common['Bad request'], false, '404 Not Found');
	else
		$cur_forum = $panther_forums[$forum_id];

	$password = ($cur_forum['password'] != '' && $cur_forum['redirect_url'] == '') ? random_key(12, true) : '';

	$parent_forums = array();
	$ps = $db->select('forums', 'DISTINCT parent_forum', array(), 'parent_forum!=0');
	$ps->setFetchMode(PDO::FETCH_COLUMN, 0);
	foreach ($ps as $fid)
		$parent_forums[] = $fid;

	$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Forums']);
	define('PANTHER_ACTIVE_PAGE', 'admin');
	require PANTHER_ROOT.'header.php';

	generate_admin_menu('forums');

	$categories = array();
	$ps = $db->select('categories', 'id, cat_name', array(), '', 'disp_position');
	foreach ($ps as $cur_cat)
		$categories[] = array('id' => $cur_cat['id'], 'name' => $cur_cat['cat_name']);

	$forums = $category_list = array();
	if (!in_array($cur_forum['id'], $parent_forums))
	{
		$ps = $db->run('SELECT c.id AS cid, c.cat_name, f.id, f.forum_name, f.redirect_url, f.parent_forum FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id ORDER BY c.disp_position, c.id, f.disp_position');
		foreach ($ps as $forum_list)
		{
			if (!isset($category_list[$forum_list['cid']]))
				$category_list[$forum_list['cid']] = array(
					'cat_name' => $forum_list['cat_name'],
					'id' => $forum_list['cid'],
				);

			if (!$forum_list['parent_forum'] && $forum_list['id'] != $cur_forum['id'])
				$forums[] = array('id' => $forum_list['id'], 'name' => $forum_list['forum_name'], 'category_id' => $forum_list['cid']);
		}
	}

	$data = array(
		':gid'	=>	PANTHER_ADMIN,
		':fid'	=>	$forum_id,
	);

	$groups = array();
	$ps = $db->run('SELECT g.g_id, g.g_title, g.g_read_board, g.g_post_replies, g.g_post_topics, g.g_delete_posts, g.g_attach_files, g.g_post_polls, fp.read_forum, fp.post_replies, fp.post_topics, fp.post_polls, fp.upload, fp.download, fp.delete_files FROM '.$db->prefix.'groups AS g LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (g.g_id=fp.group_id AND fp.forum_id=:fid) WHERE g.g_id!=:gid ORDER BY g.g_id', $data);
	foreach ($ps as $cur_perm)
	{
		$read_forum = ($cur_perm['read_forum'] != '0') ? true : false;
		$post_replies = (($cur_perm['g_post_replies'] == '0' && $cur_perm['post_replies'] == '1') || ($cur_perm['g_post_replies'] == '1' && $cur_perm['post_replies'] != '0')) ? true : false;
		$post_topics = (($cur_perm['g_post_topics'] == '0' && $cur_perm['post_topics'] == '1') || ($cur_perm['g_post_topics'] == '1' && $cur_perm['post_topics'] != '0')) ? true : false;
		$post_polls = (($cur_perm['g_post_polls'] == '0' && $cur_perm['post_polls'] == '1') || ($cur_perm['g_post_polls'] == '1' && $cur_perm['post_polls'] != '0')) ? true : false;
		$upload = (($cur_perm['g_attach_files'] == '0' && $cur_perm['upload'] == '1') || ($cur_perm['g_attach_files'] == '1' && $cur_perm['upload'] != '0')) ? true : false;
		$download = (($cur_perm['read_forum'] != '0' && $cur_perm['download'] != '0') || ($cur_perm['read_forum'] == '1' && $cur_perm['download'] != '0')) ? true : false;
		$delete = (($cur_perm['g_delete_posts'] == '0' && $cur_perm['delete_files'] == '1') || ($cur_perm['g_delete_posts'] == '1' && $cur_perm['delete_files'] != '0')) ? true : false;

		// Determine if the current settings differ from the default or not
		$read_forum_def = ($cur_perm['read_forum'] == '0') ? false : true;
		$post_replies_def = (($post_replies && $cur_perm['g_post_replies'] == '0') || (!$post_replies && ($cur_perm['g_post_replies'] == '' || $cur_perm['g_post_replies'] == '1'))) ? false : true;
		$post_topics_def = (($post_topics && $cur_perm['g_post_topics'] == '0') || (!$post_topics && ($cur_perm['g_post_topics'] == '' || $cur_perm['g_post_topics'] == '1'))) ? false : true;
		$post_polls_def = (($post_polls && $cur_perm['g_post_polls'] == '0') || (!$post_polls && ($cur_perm['g_post_polls'] == '' || $cur_perm['g_post_polls'] == '1'))) ? false : true;
		$upload_def = (($upload && $cur_perm['g_attach_files'] == '0') || (!$upload && ($cur_perm['g_attach_files'] == '' || $cur_perm['g_attach_files'] == '1'))) ? false : true;
		$download_def = (($download && $cur_perm['read_forum'] == '0') || (!$download && ($cur_perm['read_forum'] == '' || $cur_perm['read_forum'] == '1'))) ? false : true;
		$delete_def = (($delete && $cur_perm['g_delete_posts'] == '0') || (!$delete && ($cur_perm['g_delete_posts'] == '' || $cur_perm['g_delete_posts'] == '1'))) ? false : true;

		$groups[] = array(
			'perm' => $cur_perm,
			'read_forum_def' => $read_forum_def,
			'read_forum' => $read_forum,
			'post_replies_def' => $post_replies_def,
			'post_replies' => $post_replies,
			'post_topics_def' => $post_topics_def,
			'post_topics' => $post_topics,
			'post_polls_def' => $post_polls_def,
			'post_polls' => $post_polls,
			'upload_def' => $upload_def,
			'upload' => $upload,
			'download_def' => $download_def,
			'download' => $download,
			'delete_def' => $delete_def,
			'delete' => $delete,
		);
	}

	$tpl = load_template('edit_forum.tpl');
	echo $tpl->render(
		array(
			'lang_admin_forums' => $lang_admin_forums,
			'lang_admin_common' => $lang_admin_common,
			'forum' => $cur_forum,
			'password' => ($cur_forum['password'] != '') ? random_key(12, true) : '',
			'form_action' => panther_link($panther_url['edit_forum'], array($forum_id)),
			'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/forums.php'),
			'categories' => $categories,
			'category_list' => $category_list,
			'forums' => $forums,
			'groups_link' => panther_link($panther_url['admin_groups']),
			'groups' => $groups,
		)
	);

	require PANTHER_ROOT.'footer.php';
}

$page_title = array($panther_config['o_board_title'], $lang_admin_common['Admin'], $lang_admin_common['Forums']);
define('PANTHER_ACTIVE_PAGE', 'admin');
require PANTHER_ROOT.'header.php';

generate_admin_menu('forums');

// Display all the categories and forums
$category_list = $forums = array();
$ps = $db->run('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name, f.disp_position, f.parent_forum FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id ORDER BY c.disp_position, c.id, f.disp_position');
foreach ($ps as $cur_forum)
{
	if (!isset($catgeory_list[$cur_forum['cid']]))
		$category_list[$cur_forum['cid']] = array(
			'cat_name' => $cur_forum['cat_name'],
			'id' => $cur_forum['cid'],
		);
			
	$forums[] = array(
		'edit_link' => panther_link($panther_url['edit_forum'], array($cur_forum['fid'])),
		'delete_link' => panther_link($panther_url['del_forum'], array($cur_forum['fid'])),
		'id' => $cur_forum['fid'],
		'name' => $cur_forum['forum_name'],
		'disp_position' => $cur_forum['disp_position'],
		'parent_forum' => $cur_forum['parent_forum'],
		'category_id' => $cur_forum['cid'],
	);
}

$categories = array();
$ps = $db->select('categories', 'id, cat_name', array(), '', 'disp_position');
foreach ($ps as $cur_cat)
	$categories[] = array('id' => $cur_cat['id'], 'cat_name' => $cur_cat['cat_name']);

$tpl = load_template('admin_forums.tpl');
echo $tpl->render(
	array(
		'lang_admin_forums' => $lang_admin_forums,
		'lang_admin_common' => $lang_admin_common,
		'form_action' => panther_link($panther_url['admin_forums_action'], array('addel')),
		'action' => panther_link($panther_url['admin_forums_action'], array('edit')),
		'csrf_token' => generate_csrf_token(PANTHER_ADMIN_DIR.'/forums.php'),
		'categories' => $categories,
		'category_list' => $category_list,
		'forums' => $forums,
	)
);

require PANTHER_ROOT.'footer.php';