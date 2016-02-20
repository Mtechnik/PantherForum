<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

define('PANTHER_ROOT', __DIR__.'/../');
require PANTHER_ROOT.'include/common.php';
require PANTHER_ROOT.'lang/'.$panther_user['language'].'/reputation.php';

$action = isset($_GET['action']) ? $_GET['action'] : null;
$section = isset($_GET['section']) && in_array($_GET['section'], array('rep_received', 'rep_given')) ? $_GET['section'] : 'rep_received';
$vote = isset($_GET['vote']) ? intval($_GET['vote']) : '0';
$id = isset($_GET['id']) ? intval($_GET['id']) : '0';

if ($panther_config['o_reputation'] == '0')
	exit($lang_reputation['reputation disabled']);

if ($panther_user['g_rep_enabled'] == '0')
	exit($lang_reputation['Group disabled']);

if ($action == 'remove' && isset($_GET['p']) && isset($_GET['uid']))
{
	if (!$panther_user['is_admmod'] && !$panther_user['is_admin'] && $panther_user['g_access_admin_cp'] == '0')
		message($lang_common['No permission']);
		
	$data = array(
		':id' => $id,
	);

	$ps = $db->run('SELECT p.id, p.poster_id, r.vote FROM '.$db->prefix.'reputation AS r LEFT JOIN '.$db->prefix.'posts AS p ON r.post_id=p.id WHERE r.id=:id', $data);
	$cur_post = $ps->fetch();

	$vote = (($vote == '-1') ? '+1' : '-1');
	$data = array(
		':id'	=>	$cur_post['id'],
	);
	
	$db->run('UPDATE '.$db->prefix.'posts SET reputation=reputation'.$vote.' WHERE id=:id', $data);
	$data = array(
		':id' => $cur_post['poster_id'],
	);
	
	$db->run('UPDATE '.$db->prefix.'users SET reputation=reputation'.$vote.' WHERE id=:id', $data);
	$data = array(
		':id' => $id,
	);
	
	($hook = get_extensions('reputation_before_deletion')) ? eval($hook) : null;
	$db->delete('reputation', 'id=:id', $data);

	$db->end_transaction();
	header('Location: '.panther_link($panther_url['profile_'.$section], array(intval($_GET['uid']))));
	exit;
}
else
{
	if ($id < 1 || $vote == '0' || !defined('PANTHER_AJAX_REQUEST'))
	 	exit($lang_common['Bad request']);

	confirm_referrer('viewtopic.php');
	$data = array(
		':id'	=>	$id,
	);

	$ps = $db->run('SELECT f.use_reputation, t.closed, t.archived, f.id, p.poster_id, p.reputation, p.poster FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON p.topic_id = t.id INNER JOIN '.$db->prefix.'forums AS f ON t.forum_id=f.id WHERE p.id=:id', $data);
	if (!$ps->rowCount())
		exit($lang_common['Bad request']);
	else
			$cur_forum = $ps->fetch();
		
	if ($cur_forum['archived'] == '1')
		exit($lang_reputation['archived message']);

	if ($cur_forum['use_reputation'] == '0')
		exit($lang_reputation['reputation forum disabled']);
				
	if ($cur_forum['closed'] == '1' && !$panther_user['is_admmod'])
		exit($lang_reputation['topic closed']);

	if ($panther_user['id'] == $cur_forum['poster_id'])
		exit($lang_reputation['no own votes']);
	
	if ($cur_forum['poster_id'] == 1)
		exit($lang_reputation['no guest votes']);		

	if ($panther_user['g_rep_interval'] != '0')
	{
		$data = array(
			':id'	=>	$panther_user['id'],
			':time'	=>	(time() - $panther_user['g_rep_interval']),
		);
	
		$ps = $db->select('reputation', 'time_given', $data, 'given_by=:id AND time_given>:time');
		if ($ps->rowCount())
			exit(sprintf($lang_reputation['Rep interval'], $panther_user['g_rep_interval'] - (time() - $ps->fetchColumn())));
	}
	
	if ($vote != '-1' && $vote != '1')
		exit($lang_common['Bad request']);

	if ($vote == '-1')
	{
		if ($panther_config['o_rep_type'] == 2)
			exit($lang_reputation['invalid rep type']);
		
		$data = array(
			':uid'	=>	$panther_user['id'],
			':id'	=>	$id,
		);
		
		$ps = $db->select('reputation', 1, $data, 'post_id=:id AND given_by=:uid AND vote=-1');
		if ($ps->rowCount())
			exit($lang_reputation['duplicate entry minus']);
					
		if ($panther_user['g_rep_minus'] != '0')
		{
			$data = array(
				':id'	=>	$panther_user['id'],
				':time'	=>	(time() - 86400),
			);

			$ps = $db->select('reputation', 'COUNT(id)', $data, 'given_by=:id AND vote=-1 AND time_given>:time');
			if ($ps->fetchColumn() > $panther_user['g_rep_minus'])
				exit($lang_reputation['exceed negative reputation']);
		}
	}
	else
	{
		if ($panther_config['o_rep_type'] == 3)
			exit($lang_reputation['invalid rep type']);

		$data = array(
			':uid'	=>	$panther_user['id'],
			':id'	=>	$id,
		);
		
		$ps = $db->select('reputation', 1, $data, 'post_id=:id AND given_by=:uid AND vote=1');
		if ($ps->rowCount())
			exit($lang_reputation['duplicate entry positive']);

		if ($panther_user['g_rep_plus'] != '0')
		{
			$data = array(
				':id'	=>	$panther_user['id'],
				':time'	=>	(time() - 86400),
			);

			$ps = $db->select('reputation', 'COUNT(id)', $data, 'given_by=:id AND vote=1 AND time_given>:time');		
			if ($ps->fetchColumn() > $panther_user['g_rep_plus'])
				exit($lang_reputation['exceed positive reputation']);
		}
	}

	$data = array(
		':id'	=>	$panther_user['id'],
	);

	if ($panther_config['o_rep_abuse'] != 0)
	{
		$ps = $db->run('SELECT p.poster_id, r.vote, p.topic_id, r.id FROM '.$db->prefix.'reputation AS r LEFT JOIN '.$db->prefix.'posts AS p ON r.post_id = p.id WHERE r.given_by=:id ORDER BY r.id DESC LIMIT '.($panther_config['o_rep_abuse']), $data);
		if ($ps->rowCount())
		{
			$abuse = array('positive' => array(), 'negative' => array());
			foreach ($ps as $rep)
			{
				if ($rep['vote'] == '1')
				{
					if (array_key_exists($rep['poster_id'], $abuse['positive']))
						++$abuse['positive'][$rep['poster_id']];
					else
						$abuse['positive'][$rep['poster_id']] = '1';
				}
				else
				{
					if (array_key_exists($rep['poster_id'], $abuse['negative']))
						++$abuse['negative'][$rep['poster_id']];
					else
						$abuse['negative'][$rep['poster_id']] = '1';
				}
			}

			$positive = (!empty($abuse['positive'])) ? array_search(max(array_values($abuse['positive'])), $abuse['positive']) : '0';
			$negative = (!empty($abuse['negative'])) ? array_search(max(array_values($abuse['negative'])), $abuse['negative']) : '0';
			$rep_abuse = ($positive < $negative) ? array('user' => $negative, 'votes' => $abuse['negative'][$negative], 'type' => 'negative') : array('user' => $positive, 'votes' =>$abuse['positive'][$positive], 'type' => 'positive');

			if ($rep_abuse['votes'] >= $panther_config['o_rep_abuse'] && $panther_config['o_mailing_list'] != '')
			{ 
				require PANTHER_ROOT.'include/email.php';
				$info = array(
					'message' => array(
						'<abuser>' => $panther_user['username'],
						'<amount>' => $panther_config['o_rep_abuse'],
						'<type>' => $rep_abuse['type'],
						'<user>' => $cur_forum['poster'],
						'<profile_url>' => panther_link($panther_url['profile_rep_received'], array($rep_abuse['user'])),
					)
				);

				$mail_tpl = $mailer->parse(PANTHER_ROOT.'lang/'.$panther_user['language'].'/mail_templates/rep_abuse.tpl', $info);
				$mailer->send($panther_config['o_mailing_list'], $mail_tpl['subject'], $mail_tpl['message']);
			}
		}
	}
	
	($hook = get_extensions('reputation_after_rep_abuse')) ? eval($hook) : null;

	// Has the user issue issued the opposite vote? If so, remove it first ...
	$opposite_rep = false;
	
	$data = array(
		':uid'	=>	$panther_user['id'],
		':id'	=>	$id
	);

	$ps = $db->select('reputation', 1, $data, 'given_by=:uid AND post_id=:id');
	if ($ps->rowCount())
	{
			$opposite_rep = true;
			$vote_add = (($vote == '-1') ? '-1' : '+1');
			
			$data = array(
				':uid'	=>	$panther_user['id'],
				':id'	=>	$id,
			);

			$db->delete('reputation', 'given_by=:uid AND post_id=:id', $data);
			
			$data = array(
				':id'	=>	$id,
			);

			$db->run('UPDATE '.$db->prefix.'posts SET reputation=reputation'.$vote_add.' WHERE id=:id', $data);
			
			$data = array(
				':id'	=>	$cur_forum['poster_id'],
			);
			
			$db->run('UPDATE '.$db->prefix.'users SET reputation=reputation'.$vote_add.' WHERE id=:id', $data);
	}
	
	$insert = array(
		'post_id'	=>	$id,
		'given_by'	=>	$panther_user['id'], 
		'vote'		=>	(($vote == '-1') ? '-1' : '1'),
		'time_given'=>	time(),
	);

	$db->insert('reputation', $insert);

	$vote = (($vote == '-1') ? '-1' : '+1');
	
	$data = array(
		':id'	=>	$cur_forum['poster_id'],
	);

	$db->run('UPDATE '.$db->prefix.'users SET reputation=reputation'.$vote.' WHERE id=:id', $data);
	
	$data = array(
		':id'	=>	$id,
	);
	
	$db->run('UPDATE '.$db->prefix.'posts SET reputation=reputation'.$vote.' WHERE id=:id', $data);
	
	$db->end_transaction();
		
	if ($opposite_rep)
	{
		if ($vote == '-1')
			--$cur_forum['reputation'];
		else
			++$cur_forum['reputation'];		
	}

	if ($vote == '-1')
		--$cur_forum['reputation'];
	else
		++$cur_forum['reputation'];
		
		echo $cur_forum['reputation'];
}