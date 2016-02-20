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

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id < 1)
	message($lang_common['Bad request'], false, '404 Not Found');

if ($panther_user['is_bot'])
	message($lang_common['No permission']);

// Fetch some info about the post, the topic and the forum
$data = array(
	':gid'	=>	$panther_user['g_id'],
	':id'	=>	$id,
);
$ps = $db->run('SELECT f.id AS fid, f.forum_name, f.moderators, f.password, f.redirect_url, f.last_topic_id, fp.post_replies, fp.post_polls, fp.post_topics, fp.upload, fp.delete_files, t.id AS tid, t.subject, t.archived, t.posted, t.first_post_id, t.sticky, t.closed, p.poster, p.posted AS pposted, p.poster_id, p.message, p.edit_reason, p.hide_smilies FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=:gid) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND p.id=:id', $data);
if (!$ps->rowCount())
	message($lang_common['Bad request'], false, '404 Not Found');

$cur_post = $ps->fetch();

// Sort out who the moderators are and if we are currently a moderator (or an admin)
$mods_array = ($cur_post['moderators'] != '') ? unserialize($cur_post['moderators']) : array();
$is_admmod = ($panther_user['is_admin'] || ($panther_user['g_moderator'] == '1' && $panther_user['g_global_moderator'] ||  array_key_exists($panther_user['username'], $mods_array))) ? true : false;

$can_edit_subject = $id == $cur_post['first_post_id'] && $panther_user['g_edit_subject'] == '1';

if ($panther_config['o_censoring'] == '1')
{
	$cur_post['subject'] = censor_words($cur_post['subject']);
	$cur_post['message'] = censor_words($cur_post['message']);
}

// Do we have permission to edit this post?
if (($panther_user['g_edit_posts'] == '0' || $cur_post['poster_id'] != $panther_user['id'] || $cur_post['closed'] == '1' || $panther_user['g_deledit_interval'] != 0 && (time() - $cur_post['pposted']) > $panther_user['g_deledit_interval']) && !$is_admmod)
	message($lang_common['No permission'], false, '403 Forbidden');

if ($is_admmod && (!$panther_user['is_admin'] && (in_array($cur_post['poster_id'], get_admin_ids()) && $panther_user['g_mod_edit_admin_posts'] == '0')))
	message($lang_common['No permission'], false, '403 Forbidden');

// Load the post.php language file
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/post.php';
check_posting_ban();

if ($cur_post['archived'] == '1')
	message($lang_post['Topic archived']);

if ($cur_post['password'] != '')
	check_forum_login_cookie($cur_post['fid'], $cur_post['password']);

// Start with a clean slate
$errors = array();

if (isset($_POST['form_sent']))
{
	// Make sure they got here from the site
	confirm_referrer('edit.php');

	// If it's a topic it must contain a subject
	if ($can_edit_subject)
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
	$stick_topic = isset($_POST['stick_topic']) ? '1' : '0';
	$add_poll = isset($_POST['add_poll']) && $cur_post['post_polls'] != '0' && $panther_user['g_post_polls'] == '1' && $panther_config['o_polls'] == '1' ? '1' : '0';

	if (!$is_admmod)
		$stick_topic = $cur_post['sticky'];

	// Replace four-byte characters (MySQL cannot handle them)
	$message = strip_bad_multibyte_chars($message);

	// Did everything go according to plan?
	if (empty($errors) && !isset($_POST['preview']))
	{
		$edit_reason = (isset($_POST['edit_reason']) && $is_admmod) ? panther_trim($_POST['edit_reason']): $cur_post['edit_reason'];
		require PANTHER_ROOT.'include/search_idx.php';

		if ($can_edit_subject)
		{
			$update = array(
				'subject'	=>	$subject,
				'sticky'	=>	$stick_topic,
			);

			$data = array(
				':id'	=>	$cur_post['tid'],
				':moved'	=>	$cur_post['tid'],
			);

			// Update the topic and any redirect topics
			$db->update('topics', $update, 'id=:id OR moved_to=:moved', $data);

			// We changed the subject, so we need to take that into account when we update the search words
			update_search_index('edit', $id, $message, $subject);

			// If this is the last topic in the forum, and we've changed the subject, we need to update that
			if ($cur_post['last_topic_id'] == $cur_post['tid'] && $subject != $cur_post['subject'])
				update_forum($cur_post['fid']);
		}
		else
			update_search_index('edit', $id, $message);

		$update = array(
			'message'	=>	$message,
			'edit_reason'	=>	$edit_reason,
			'hide_smilies'	=>	$hide_smilies,
		);
		
		if (!isset($_POST['silent']) || !$is_admmod)
		{
			$update['edited'] = time();
			$update['edited_by'] = $panther_user['username'];
		}
		
		$data = array(
			':id'	=>	$id,
		);

		// Update the post
		$db->update('posts', $update, 'id=:id', $data);
		$data = array(
			':id'	=>	$id,
		);

		$ps = $db->select('attachments', 'COUNT(id)', $data, 'post_id=:id');
		if ($ps->rowCount())
		{
			$num_attachments = $ps->fetchColumn();
			for ($i = 0; $i < $num_attachments; $i++)
			{
				if (isset($_POST['attach_delete'][$i]))
				{
					$attach_id = intval($_POST['attach_delete'][$i]);
					$data = array(
						':id'	=>	$attach_id,
					);

					$ps = $db->select('attachments', 'owner', $data, 'id=:id', 1);
					if ($ps->rowCount() || $is_admmod)
					{
						$owner = $ps->fetchColumn();
						$can_delete = false;

						if ($panther_user['is_admin'])
							$can_delete = true;
						else
							$can_delete = (($is_admmod || $panther_user['g_delete_posts'] == '1' && $owner == $panther_user['id']) && ($cur_post['delete_files'] == '1' || $cur_post['delete_files'] == '')) ? true : false;

						if($can_delete)
						{
							if (!delete_attachment($attach_id))
									message($lang_post['Can\'t delete']);
						}
						else
							message($lang_post['No delete']);
					}
					else
						message($lang_post['No attachments']);
				}
			}
		}

		if (isset($_FILES['attached_file']))
		{
			if (isset($_FILES['attached_file']['error']) && $_FILES['attached_file']['error'] != 0 && $_FILES['attached_file']['error'] != 4)
				error_handler(file_upload_error_message($_FILES['attached_file']['error']), __FILE__, __LINE__);

			if ($_FILES['attached_file']['size'] != 0 && is_uploaded_file($_FILES['attached_file']['tmp_name']))
			{
				$can_upload = false;
				if ($panther_user['is_admin'])
					$can_upload = true;
				else
				{
					$data = array(
						':id'	=>	$id,
					);

					$ps = $db->select('attachments', 'COUNT(id)', $data, 'post_id=:id GROUP BY post_id', 1);
					$num_attachments = $ps->fetchColumn();

					$can_upload = ($panther_user['g_attach_files'] == '1' && ($cur_post['upload'] == '1' || $cur_post['upload'] == '')) ? true : false;
					
					if ($can_upload && $num_attachments == $panther_user['g_max_attachments'])
						$can_upload = false;

					$max_size = ($panther_user['g_max_size'] == '0' && $panther_user['g_attach_files'] == '1') ? $panther_config['o_max_upload_size'] : $panther_user['g_max_size'];
					if ($can_upload && $_FILES['attached_file']['size'] > $max_size)
						$can_upload = false;

					if (!check_file_extension($_FILES['attached_file']['name']))
						$can_upload = false;
				}

				if($can_upload)
				{
					if (!create_attachment($_FILES['attached_file']['name'], $_FILES['attached_file']['type'], $_FILES['attached_file']['size'], $_FILES['attached_file']['tmp_name'], $id, strlen($message)))
						message($lang_post['Attachment error']);
				}
				else // Remove file as it's either dangerous or they've attempted to URL hack. Either way, there's no need for it.
					unlink($_FILES['attached_file']['tmp_name']);
			}
		}

	($hook = get_extensions('edit_after_edit')) ? eval($hook) : null;

	if ($add_poll)
		redirect(panther_link($panther_url['poll_add'], array($cur_post['tid'])), $lang_post['Edit redirect']);
	else
		redirect(panther_link($panther_url['post'], array($id)), $lang_post['Edit redirect']);
	}
}

$can_delete = false;
$can_upload = false;
if ($panther_user['is_admin'])
{
	$can_delete = true;
	$can_upload = true;
}
else
{
	$can_delete = (($is_admmod || $panther_user['g_delete_posts'] == '1') && ($cur_post['delete_files'] == '1' || $cur_post['delete_files'] == '')) ? true : false;
	$can_upload = ($panther_user['g_attach_files'] == '1' && ($cur_post['upload'] == '1' || $cur_post['upload'] == '')) ? true : false;
}

$max_size = 1;
$attachments = array();
if ($can_delete || $can_upload)
{
	$max_size = ($panther_user['g_max_size'] == '0' && $panther_user['g_attach_files'] == '1') ? $panther_config['o_max_upload_size'] : $panther_user['g_max_size'];
	$data = array(
			':id'	=>	$id,
	);

	$ps = $db->select('attachments', 'id, owner, filename, extension, size, downloads', $data, 'post_id=:id');
	foreach ($ps as $attachment)
			$attachments[] = array('id' => $attachment['id'], 'icon' => attach_icon($attachment['extension']), 'link' => panther_link($panther_url['attachment'], array($attachment['id'])), 'name' => $attachment['filename'], 'size' => sprintf($lang_post['Attachment size'], file_size($attachment['size'])), 'downloads' => sprintf($lang_post['Attachment downloads'], forum_number_format($attachment['downloads'])));
}

($hook = get_extensions('edit_before_header')) ? eval($hook) : null;

$page_title = array($panther_config['o_board_title'], $lang_post['Edit post']);
$required_fields = array('req_subject' => $lang_common['Subject'], 'req_message' => $lang_common['Message']);
$focus_element = array('edit', 'req_message');
define('PANTHER_ACTIVE_PAGE', 'index');
require PANTHER_ROOT.'header.php';

$checkboxes = array();
if ($can_edit_subject && $is_admmod)
	$checkboxes[] = array('name' => 'stick_topic', 'title' => $lang_common['Stick topic'], 'checked' => ((isset($_POST['form_sent']) && isset($_POST['stick_topic']) || !isset($_POST['form_sent']) && $cur_post['sticky'] == '1') ? true : false),);

if ($can_edit_subject && $cur_post['post_polls'] != '0' && $panther_user['g_post_polls'] == '1' && $panther_config['o_polls'] == '1')
	$checkboxes[] = array('name' => 'add_poll', 'title' => $lang_post['Add poll'], 'checked' => (isset($_POST['add_poll']) ? true : false));

if ($panther_config['o_smilies'] == '1')
	$checkboxes[] = array('name' => 'hide_smilies', 'title' => $lang_post['Hide smilies'], 'checked' => ((isset($_POST['form_sent']) && isset($_POST['hide_smilies']) || !isset($_POST['form_sent']) && $cur_post['hide_smilies'] == '1') ? true : false));

if ($is_admmod)
	$checkboxes[] = array('id' => 'silent_edit', 'name' => 'silent', 'title' => $lang_post['Silent edit'], 'checked' => ((isset($_POST['form_sent']) && isset($_POST['silent'])) || !isset($_POST['form_sent']) ? true : false));

$render = array(
	'errors' => $errors,
	'lang_post' => $lang_post,
	'lang_common' => $lang_common,
	'preview' => (isset($_POST['preview'])) ? true : false,
	'can_edit_subject' => $can_edit_subject,
	'subject' => isset($_POST['req_subject']) ? $_POST['req_subject'] : $cur_post['subject'],
	'can_upload' => $can_upload,
	'can_delete' => $can_delete,
	'panther_user' => $panther_user,
	'max_size' => $max_size,
	'attachments' => $attachments,
	'is_admmod' => $is_admmod,
	'edit_reason' => isset($_POST['edit_reason']) ? $_POST['edit_reason'] : $cur_post['edit_reason'],
	'checkboxes' => $checkboxes,
	'index_link' => panther_link($panther_url['index']),
	'forum_link' => panther_link($panther_url['forum'], array($cur_post['fid'], url_friendly($cur_post['forum_name']))),
	'cur_post' => $cur_post,
	'topic_link' => panther_link($panther_url['topic'], array($cur_post['tid'], url_friendly($cur_post['subject']))),
	'form_action' => panther_link($panther_url['edit_edit'], array($id)),
	'csrf_token' => generate_csrf_token(),
	'message' => isset($_POST['req_message']) ? $message : $cur_post['message'],
	'panther_config' => $panther_config,
	'quickpost_links' => array(
		'form_action' => panther_link($panther_url['new_reply'], array($id)),
		'csrf_token' => generate_csrf_token('post.php'),
		'bbcode' => panther_link($panther_url['help'], array('bbcode')),
		'url' => panther_link($panther_url['help'], array('url')),
		'img' => panther_link($panther_url['help'], array('img')),
		'smilies' => panther_link($panther_url['help'], array('smilies')),
	),
);

if (isset($_POST['preview']))
{
	require_once PANTHER_ROOT.'include/parser.php';
	$render['preview'] = $parser->parse_message($message, $hide_smilies);
}

$tpl = load_template('edit.tpl');
echo $tpl->render($render);
require PANTHER_ROOT.'footer.php';