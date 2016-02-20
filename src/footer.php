<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PANTHER'))
	exit;

$controls = $links = array();
if (isset($footer_style) && ($footer_style == 'viewforum' || $footer_style == 'viewtopic') && $is_admmod)
{
	if ($footer_style == 'viewforum')
		$controls[] = array('link' => panther_link($panther_url['moderate_forum_p'], array($forum_id, $p)), 'lang' => $lang_common['Moderate forum']);
	else if ($footer_style == 'viewtopic')
	{
		if ($cur_topic['archived'] != '1')
		{
			$controls[] = array('link' => panther_link($panther_url['moderate_topic_p'], array($forum_id, $id, $p)), 'lang' => $lang_common['Moderate topic'], 'num_pages' => $num_pages, 'moderate_all' => panther_link($panther_url['moderate_all'], array($forum_id, $id)), 'all' => $lang_common['All']);
			$controls[] = array('link' => panther_link($panther_url['move'], array($forum_id, $id, $csrf_token)), 'lang' => $lang_common['Move topic']);

			if ($cur_topic['closed'] == '1')
				$controls[] = array('link' => panther_link($panther_url['open'], array($forum_id, $id, $csrf_token)), 'lang' => $lang_common['Open topic']);
			else
				$controls[] = array('link' => panther_link($panther_url['close'], array($forum_id, $id, $csrf_token)), 'lang' => $lang_common['Close topic']);

			if ($cur_topic['sticky'] == '1')
				$controls[] = array('link' => panther_link($panther_url['unstick'], array($forum_id, $id, $csrf_token)), 'lang' => $lang_common['Unstick topic']);
			else
				$controls[] = array('link' => panther_link($panther_url['stick'], array($forum_id, $id, $csrf_token)), 'lang' => $lang_common['Stick topic']);

			$controls[] = array('link' => panther_link($panther_url['archive'], array($forum_id, $id, $csrf_token)), 'lang' => $lang_common['Archive topic']);
			$controls[] = array('link' => panther_link($panther_url['moderate_multi'], array($forum_id, $id, $csrf_token)), 'lang' => $lang_common['multi_moderate topic']);
		}
		else if ($panther_user['is_admin'])
			$controls[] = array('link' => panther_link($panther_url['unarchive'], array($forum_id, $id, $csrf_token)), 'lang' => $lang_common['Unarchive topic']);
	}
	
	($hook = get_extensions('footer_moderator_controls')) ? eval($hook) : null;
}

// Display the "Jump to" drop list
if ($panther_config['o_quickjump'] == '1')
{
	ob_start();
	// Load cached quick jump
	if (file_exists(FORUM_CACHE_DIR.'cache_quickjump_'.$panther_user['g_id'].'.php'))
		require FORUM_CACHE_DIR.'cache_quickjump_'.$panther_user['g_id'].'.php';

	if (!defined('PANTHER_QJ_LOADED'))
	{
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';

		generate_quickjump_cache($panther_user['g_id']);
		require FORUM_CACHE_DIR.'cache_quickjump_'.$panther_user['g_id'].'.php';
	}
	
	$quickjump_tpl = trim(ob_get_contents());
	ob_end_clean();
}
else
	$quickjump_tpl = '';

$feed = array();
if (isset($footer_style) && $footer_style == 'warnings')
{
	$links[] = array('url' => panther_link($panther_url['warnings']), 'lang' => $lang_warnings['Show warning types']);
	
	if ($panther_user['is_admmod'])
		$links[] = array('url' => panther_link($panther_url['warnings_recent']), 'lang' => $lang_warnings['Show recent warnings']);
	
	($hook = get_extensions('footer_warnings')) ? eval($hook) : null;
}
elseif (isset($footer_style) && ($footer_style == 'index' || $footer_style == 'viewforum' || $footer_style == 'viewtopic') && ($panther_config['o_feed_type'] == '1' || $panther_config['o_feed_type'] == '2'))
{
	switch ($footer_style)
	{
		case 'index':
			if ($panther_config['o_feed_type'] == '1')
			{
				$feed = array(
					'type' => 'rss',
					'link' => panther_link($panther_url['index_rss']),
					'lang' => $lang_common['RSS active topics feed']
				);
			}
			else if ($panther_config['o_feed_type'] == '2')
			{
				$feed = array(
					'type' => 'atom',
					'link' => panther_link($panther_url['index_atom']),
					'lang' => $lang_common['Atom active topics feed']
				);
			}
		break;
		case 'viewforum':
			if ($panther_config['o_feed_type'] == '1')
			{
				$feed = array(
					'type' => 'rss',
					'link' => panther_link($panther_url['forum_rss'], array($id)),
					'lang' => $lang_common['RSS forum feed']
				);
			}
			else if ($panther_config['o_feed_type'] == '2')
			{
				$feed = array(
					'type' => 'atom',
					'link' => panther_link($panther_url['forum_atom'], array($id)),
					'lang' => $lang_common['Atom forum feed']
				);
			}			
		break;
		case 'viewtopic':
			if ($panther_config['o_feed_type'] == '1')
			{
				$feed = array(
					'type' => 'rss',
					'link' => panther_link($panther_url['topic_rss'], array($id)),
					'lang' => $lang_common['RSS topic feed']
				);
			}
			else if ($panther_config['o_feed_type'] == '2')
			{
				$feed = array(
					'type' => 'atom',
					'link' => panther_link($panther_url['topic_atom'], array($id)),
					'lang' => $lang_common['Atom topic feed']
				);
			}			
		break;
	}
	
	($hook = get_extensions('footer_feedsr')) ? eval($hook) : null;
}

// Display debug info (if enabled/defined)
if ($panther_config['o_debug_mode'] == '1')
{
	// Calculate script generation time
	$time_diff = sprintf('%.3f', microtime(true) - $panther_start);
	$debug_info = sprintf($lang_common['Querytime'], $time_diff, $db->get_num_queries());

	if (function_exists('memory_get_usage'))
	{
		$debug_info .= ' - '.sprintf($lang_common['Memory usage'], file_size(memory_get_usage()));

		if (function_exists('memory_get_peak_usage'))
			$debug_info .= ' '.sprintf($lang_common['Peak usage'], file_size(memory_get_peak_usage()));
	}
}
else
	$debug_info = '';

$queries = ($panther_config['o_show_queries'] == '1') ? display_saved_queries() : '';

// End the transaction
$db->end_transaction();

$style_path = (($panther_config['o_style_path'] != 'style') ? $panther_config['o_style_path'] : PANTHER_ROOT.$panther_config['o_style_path']).'/'.$panther_user['style'].'/templates/';
$tpl = (defined('PANTHER_ADMIN_CONSOLE') && (file_exists($style_path.'admin_footer.tpl') || $panther_user['style'] == $panther_config['o_default_style'] && !file_exists($style_path)) ? 'admin_footer.tpl' : 'footer.tpl');
$tpl = load_template($tpl);
echo $tpl->render(
	array(
		'footer_style' => isset($footer_style) ? $footer_style : '',
		'controls' => $controls,
		'quickjump' => $quickjump_tpl,
		'lang_common' => $lang_common,
		'links' => $links,
		'panther_config' => $panther_config,
		'feed' => $feed,
		'debug_info' => $debug_info,
		'queries' => $queries,
	)
);

ob_flush();
exit;