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

// Load the help.php language file
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/help.php';

$page_title = array($panther_config['o_board_title'], $lang_help['Help']);
define('PANTHER_ACTIVE_PAGE', 'help');
require PANTHER_ROOT.'header.php';

// Display the smiley set
require PANTHER_ROOT.'include/parser.php';

$smiley_groups = array();
foreach ($parser->smilies as $smiley_text => $smiley_img)
	$smiley_groups[$smiley_img][] = $smiley_text;

($hook = get_extensions('help_before_display')) ? eval($hook) : null;

$ps = $db->select('topics', 'subject, id', array(), '', 'id ASC LIMIT 1');
$cur_topic = $ps->fetch();

$ps = $db->select('posts', 'id', array(), '', 'id ASC LIMIT 1');
$cur_post = $ps->fetchColumn();

$ps = $db->select('users', 'id, username, group_id', array(), 'id>1', 'id ASC LIMIT 1');
$user = $ps->fetch();

$forum = $panther_forums[key($panther_forums)];

$tpl = load_template('help.tpl');
echo $tpl->render(
	array(
		'lang_help' => $lang_help,
		'panther_config' => $panther_config,
		'lang_common' => $lang_common,
		'base_url' => panther_link($panther_url['index']),
		'help_page' => panther_link($panther_url['help'], array('url')),
		'topic_link' => panther_link($panther_url['topic'], array($cur_topic['id'], url_friendly($cur_topic['subject']))),
		'topic_id' => $cur_topic['id'],
		'post_id' => $cur_post,
		'post_link' => panther_link($panther_url['post'], array($cur_post)),
		'forum_id' => $forum['id'],
		'forum_link' => panther_link($panther_url['forum'], array($forum['id'], url_friendly($forum['forum_name']))),
		'formatted_username' => colourize_group($user['username'], $user['group_id'], $user['id']),
		'username' => $user['username'],
		'smiley_path' => (($panther_config['o_smilies_dir'] != '') ? $panther_config['o_smilies_dir'] : get_base_url().'/'.$panther_config['o_smilies_path'].'/'),
		'smiley_groups' => $smiley_groups,
	)
);

$db->end_transaction();