<?php

if (!defined('PANTHER'))
	exit;

class email
{
	public function __construct($panther_bans, $panther_url, $lang_common, $panther_config, $db, $panther_user, $panther_forums)
	{
		$this->bans = $panther_bans;
		$this->url = $panther_url;
		$this->lang = $lang_common;
		$this->config = $panther_config;
		$this->db = $db;
		$this->forums = $panther_forums;
		$this->user = $panther_user;

		if (!defined('FORUM_EOL')) // Define line breaks in mail headers; possible values can be PHP_EOL, "\r\n", "\n" or "\r"
			define('FORUM_EOL', PHP_EOL);
			
		require PANTHER_ROOT.'include/utf8/utils/ascii.php';
	}

	public function is_valid_email($email)
	{
		if (strlen($email) > 80)
			return false;

		return preg_match('%^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|("[^"]+"))@((\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\])|(([a-zA-Z\d\-]+\.)+[a-zA-Z]{2,}))$%', $email);
	}

	public function is_banned_email($email)
	{
		foreach ($this->bans as $cur_ban)
		{
			if ($cur_ban['email'] != '' &&
				($email == $cur_ban['email'] ||
				(strpos($cur_ban['email'], '@') === false && stristr($email, '@'.$cur_ban['email']))))
				return true;
		}

		return false;
	}
	
	function encode_mail_text($str)
	{
		if (utf8_is_ascii($str))
			return $str;

		return '=?UTF-8?B?'.base64_encode($str).'?=';
	}
	
	public function bbcode2email($text, $wrap_length = 72)
	{
		$text = panther_trim($text, "\t\n ");
		$shortcut_urls = array(
			'topic' => $this->url['topic'],
			'post' => $this->url['post'],
			'forum' => $this->url['forum'],
			'user' => $this->url['profile'],
		);

		// Split code blocks and text so BBcode in codeblocks won't be touched
		list($code, $text) = extract_blocks($text, '[code]', '[/code]');

		// Strip all bbcodes, except the quote, url, img, email, code and list items bbcodes
		$text = preg_replace(array(
			'%\[/?(?!(?:quote|url|topic|post|user|forum|img|email|code|list|\*))[a-z]+(?:=[^\]]+)?\]%i',
			'%\n\[/?list(?:=[^\]]+)?\]%i' // A separate regex for the list tags to get rid of some whitespace
		), '', $text);

		// Match the deepest nested bbcode
		// An adapted example from Mastering Regular Expressions
		$match_quote_regex = '%
			\[(quote|\*|url|img|email|topic|post|user|forum)(?:=([^\]]+))?\]
			(
				(?>[^\[]*)
				(?>
					(?!\[/?\1(?:=[^\]]+)?\])
					\[
					[^\[]*
				)*
			)
			\[/\1\]
		%ix';

		$url_index = 1;
		$url_stack = array();
		while (preg_match($match_quote_regex, $text, $matches))
		{
			// Quotes
			if ($matches[1] == 'quote')
			{
				// Put '>' or '> ' at the start of a line
				$replacement = preg_replace(
					array('%^(?=\>)%m', '%^(?!\>)%m'),
					array('>', '> '),
					$matches[2].' '.$this->lang['wrote'].'\n'.$matches[3]);
			}

			// List items
			elseif ($matches[1] == '*')
				$replacement = ' * '.$matches[3];

			// URLs and emails
			elseif (in_array($matches[1], array('url', 'email')))
			{
				if (!empty($matches[2]))
				{
					$replacement = '['.$matches[3].']['.$url_index.']';
					$url_stack[$url_index] = $matches[2];
					$url_index++;
				}
				else
					$replacement = '['.$matches[3].']';
			}

			// Images
			elseif ($matches[1] == 'img')
			{
				if (!empty($matches[2]))
					$replacement = '['.$matches[2].']['.$url_index.']';
				else
					$replacement = '['.basename($matches[3]).']['.$url_index.']';

				$url_stack[$url_index] = $matches[3];
				$url_index++;
			}

			// Topic, post, forum and user URLs
			elseif (in_array($matches[1], array('topic', 'post', 'forum', 'user')))
			{
				$arg = '';
				if ($matches[1] == 'topic')
				{
					$data = array(
						':id' => $matches[3],
					);

					$ps = $this->db->select('topics', 'subject', $data, 'id=:id');
					$arg = url_friendly($ps->fetchColumn());
				}
				else if ($matches[1] == 'forum')
				{
					if (isset($this->forums[$matches[3]]))
						$arg = url_friendly($this->forums[$matches[3]]['forum_name']);
				}
				else if ($matches[1] == 'user')
				{
					$data = array(
						':username' => $matches[3],
					);

					$ps = $this->db->select('users', 'id', $data, 'username=:username');
					$id = $ps->fetchColumn();

					$arg = url_friendly($matches[3]);
					$matches[3] = $id;
				}

				if (!empty($matches[2]))
				{
					$replacement = '['.$matches[3].']['.$url_index.']';
					$url_stack[$url_index] = panther_link($shortcut_urls[$matches[1]], array($matches[2], $arg));
					$url_index++;
				}
				else
					$replacement = '['.panther_link($shortcut_urls[$matches[1]], array($matches[3], $arg)).']';
			}

			// Update the main text if there is a replacement
			if (!is_null($replacement))
			{
				$text = str_replace($matches[0], $replacement, $text);
				$replacement = null;
			}
		}

		// Put code blocks and text together
		if (isset($code))
		{
			$parts = explode("\1", $text);
			$text = '';
			foreach ($parts as $i => $part)
			{
				$text .= $part;
				if (isset($code[$i]))
					$text .= trim($code[$i], "\n\r");
			}
		}

		// Put URLs at the bottom
		if ($url_stack)
		{
			$text .= "\n\n";
			foreach ($url_stack as $i => $url)
				$text .= "\n".' ['.$i.']: '.$url;
		}

		// Wrap lines if $wrap_length is higher than -1
		if ($wrap_length > -1)
		{
			// Split all lines and wrap them individually
			$parts = explode("\n", $text);
			foreach ($parts as $k => $part)
			{
				preg_match('%^(>+ )?(.*)%', $part, $matches);
				$parts[$k] = wordwrap($matches[1].$matches[2], $wrap_length -
					strlen($matches[1]), "\n".$matches[1]);
			}

			return implode("\n", $parts);
		}
		else
			return $text;
	}
	
	function send($to, $subject, $message, $reply_to_email = '', $reply_to_name = '')
	{
		// Use \r\n for SMTP servers, the system's line ending for local mailers
		$smtp = $this->config['o_smtp_host'] != '';
		$EOL = $smtp ? "\r\n" : FORUM_EOL;

		// Do a little spring cleaning
		$to = panther_trim(preg_replace('%[\n\r]+%s', '', $to));
		$subject = panther_trim(preg_replace('%[\n\r]+%s', '', $subject));
		$from_email = panther_trim(preg_replace('%[\n\r:]+%s', '', $this->config['o_webmaster_email']));
		$from_name = panther_trim(preg_replace('%[\n\r:]+%s', '', str_replace('"', '', $this->config['o_email_name'])));
		$reply_to_email = panther_trim(preg_replace('%[\n\r:]+%s', '', $reply_to_email));
		$reply_to_name = panther_trim(preg_replace('%[\n\r:]+%s', '', str_replace('"', '', $reply_to_name)));

		// Set up some headers to take advantage of UTF-8
		$from = '"'.$this->encode_mail_text($from_name).'" <'.$from_email.'>';
		$subject = $this->encode_mail_text($subject);

		$headers = 'From: '.$from.$EOL.'Date: '.gmdate('r').$EOL.'MIME-Version: 1.0'.$EOL.'Content-transfer-encoding: 8bit'.$EOL.'Content-type: text/plain; charset=utf-8'.$EOL.'X-Mailer: Panther Forum Software';

		// If we specified a reply-to email, we deal with it here
		if (!empty($reply_to_email))
		{
			$reply_to = '"'.$this->encode_mail_text($reply_to_name).'" <'.$reply_to_email.'>';
			$headers .= $EOL.'Reply-To: '.$reply_to;
		}

		// Make sure all linebreaks are LF in message (and strip out any NULL bytes)
		$message = str_replace("\0", '', panther_linebreaks($message));
		$message = str_replace("\n", $EOL, $message);

		if ($smtp)
			$this->smtp_mail($to, $subject, $message, $headers);
		else
			mail($to, $subject, $message, $headers);
	}
	
	//
	// This function was originally a part of the phpBB Group forum software phpBB2 (http://www.phpbb.com)
	// They deserve all the credit for writing it. I made small modifications for it to suit PunBB and its coding standards
	//
	function server_parse($socket, $expected_response)
	{
		$server_response = '';
		while (substr($server_response, 3, 1) != ' ')
		{
			if (!($server_response = fgets($socket, 256)))
				error_handler('Couldn\'t get mail server response codes. Please contact the forum administrator.', __FILE__, __LINE__);
		}

		if (!(substr($server_response, 0, 3) == $expected_response))
			error_handler('Unable to send email. Please contact the forum administrator with the following error message reported by the SMTP server: "'.$server_response.'"', __FILE__, __LINE__);
	}
	
	//
	// This function was originally a part of the phpBB Group forum software phpBB2 (http://www.phpbb.com)
	// They deserve all the credit for writing it. I made small modifications for it to suit PunBB and its coding standards.
	//
	function smtp_mail($to, $subject, $message, $headers = '')
	{
		static $local_host;

		$recipients = explode(',', $to);

		// Sanitize the message
		$message = str_replace("\r\n.", "\r\n..", $message);
		$message = (substr($message, 0, 1) == '.' ? '.'.$message : $message);

		// Are we using port 25 or a custom port?
		if (strpos($this->config['o_smtp_host'], ':') !== false)
			list($smtp_host, $smtp_port) = explode(':', $this->config['o_smtp_host']);
		else
		{
			$smtp_host = $this->config['o_smtp_host'];
			$smtp_port = 25;
		}

		if ($this->config['o_smtp_ssl'] == '1')
			$smtp_host = 'ssl://'.$smtp_host;

		if (!($socket = fsockopen($smtp_host, $smtp_port, $errno, $errstr, 15)))
			error_handler('Could not connect to smtp host "'.$this->config['o_smtp_host'].'" ('.$errno.') ('.$errstr.')', __FILE__, __LINE__);

		$this->server_parse($socket, '220');

		if (!isset($local_host))
		{
			// Here we try to determine the *real* hostname (reverse DNS entry preferably)
			$local_host = php_uname('n');

			// Able to resolve name to IP
			if (($local_addr = @gethostbyname($local_host)) !== $local_host)
			{
				// Able to resolve IP back to name
				if (($local_name = @gethostbyaddr($local_addr)) !== $local_addr)
					$local_host = $local_name;
			}
		}

		if ($this->config['o_smtp_user'] != '' && $this->config['o_smtp_pass'] != '')
		{
			fwrite($socket, 'EHLO '.$local_host."\r\n");
			$this->server_parse($socket, '250');

			fwrite($socket, 'AUTH LOGIN'."\r\n");
			$this->server_parse($socket, '334');

			fwrite($socket, base64_encode($this->config['o_smtp_user'])."\r\n");
			$this->server_parse($socket, '334');

			fwrite($socket, base64_encode($this->config['o_smtp_pass'])."\r\n");
			$this->server_parse($socket, '235');
		}
		else
		{
			fwrite($socket, 'HELO '.$local_host."\r\n");
			$this->server_parse($socket, '250');
		}

		fwrite($socket, 'MAIL FROM: <'.$this->config['o_webmaster_email'].'>'."\r\n");
		$this->server_parse($socket, '250');;

		foreach ($recipients as $email)
		{
			fwrite($socket, 'RCPT TO: <'.$email.'>'."\r\n");
			$this->server_parse($socket, '250');
		}

		fwrite($socket, 'DATA'."\r\n");
		$this->server_parse($socket, '354');

		fwrite($socket, 'Subject: '.$subject."\r\n".'To: <'.implode('>, <', $recipients).'>'."\r\n".$headers."\r\n\r\n".$message."\r\n");

		fwrite($socket, '.'."\r\n");
		$this->server_parse($socket, '250');

		fwrite($socket, 'QUIT'."\r\n");
		fclose($socket);

		return true;
	}
	
	public function parse($tpl, $data)
	{
		$mail_tpl = trim(file_get_contents($tpl));
		$data['message']['<board_mailer>'] = $this->config['o_board_title'];

		// The first row contains the subject (it also starts with "Subject:")
		$first_crlf = strpos($mail_tpl, "\n");
		$mail_subject = trim(substr($mail_tpl, 8, $first_crlf-8));
		$mail_message = trim(substr($mail_tpl, $first_crlf));
		
		if (isset($data['subject']))
			$mail_subject = str_replace(array_keys($data['subject']), array_values($data['subject']), $mail_subject);
		
		$mail_message = str_replace(array_keys($data['message']), array_values($data['message']), $mail_message);
		return array('subject' => $mail_subject, 'message' => $mail_message);
	}
	
	public function handle_topic_subscriptions($tid, $post_data, $replier, $pid, $previous_post_time = 0)
	{
		if ($this->config['o_topic_subscriptions'] != '1')
			return;
		
		// Get the post time for the previous post in this topic
		if (!$previous_post_time)
		{
			$data = array(
				':tid'	=>	$tid,
			);

			$ps = $this->db->select('posts', 'posted', $data, 'topic_id=:tid', 'id DESC LIMIT 1,1');
			$previous_post_time = $ps->fetchColumn();
		}

		$data = array(
			':fid' => isset($post_data['forum_id']) ? $post_data['forum_id'] : $post_data['id'],
			':previous_post' => $previous_post_time,
			':tid' => $tid,
			':id' => $this->user['id'],
		);

		// Get any subscribed users that should be notified (banned users are excluded)
		$ps = $this->db->run('SELECT u.id, u.username, u.email, u.salt, u.login_key, u.notify_with_post, u.language FROM '.$this->db->prefix.'users AS u INNER JOIN '.$this->db->prefix.'topic_subscriptions AS s ON u.id=s.user_id LEFT JOIN '.$this->db->prefix.'groups AS g ON u.group_id=g.g_id LEFT JOIN '.$this->db->prefix.'forum_perms AS fp ON (fp.forum_id=:fid AND fp.group_id=u.group_id) LEFT JOIN '.$this->db->prefix.'online AS o ON u.id=o.user_id LEFT JOIN '.$this->db->prefix.'bans AS b ON u.username=b.username WHERE b.username IS NULL AND COALESCE(o.logged, u.last_visit)>:previous_post AND (fp.read_forum IS NULL OR fp.read_forum=1) AND s.topic_id=:tid AND u.id!=:id', $data);
		if ($ps->rowCount())
		{
			$cleaned_message = $this->bbcode2email($post_data['message'], -1);

			// Loop through subscribed users and send emails
			foreach ($ps as $cur_subscriber)
			{
				if (!file_exists(PANTHER_ROOT.'lang/'.$cur_subscriber['language'].'/mail_templates/new_reply.tpl'))
					continue;

				$token = panther_hash($cur_subscriber['id'].'viewforum.php'.$cur_subscriber['salt'].$cur_subscriber['login_key']);
				$info = array(
					'subject' => array(
						'<topic_subject>' => $post_data['subject'],
					),
					'message' => array(
						'<username>' => $cur_subscriber['username'],
						'<topic_subject>' => $post_data['subject'],
						'<replier>' => $replier,
						'<post_url>' => panther_link($this->url['post'], array($pid)),
						'<unsubscribe_url>' => panther_link($this->url['topic_unsubscribe'], array($tid, $token)),
						'<message>' => $cleaned_message,
					)
				);

				// Load the "new reply" template
				$mail_tpl = $this->parse(PANTHER_ROOT.'lang/'.$cur_subscriber['language'].'/mail_templates/'.(($cur_subscriber['notify_with_post'] == '0') ? 'new_reply' : 'new_reply_full').'.tpl', $info);
				$this->send($cur_subscriber['email'], $mail_tpl['subject'], $mail_tpl['message']);
			}
		}
	}

	public function handle_forum_subscriptions($post_data, $replier, $tid)
	{
		if ($this->config['o_forum_subscriptions'] != '1')
			return;

		$forum_id = isset($post_data['forum_id']) ? $post_data['forum_id'] : $post_data['id'];
		$poster_id = isset($post_data['poster_id']) ? $post_data['poster_id'] : $this->user['id'];

		// Get any subscribed users that should be notified (banned users are excluded)
		$data = array(
			':id'	=>	$this->user['id'],
			':post_id'	=>	$forum_id,
			':forum_id'	=>	$forum_id,
		);

		$ps = $this->db->run('SELECT u.id, u.username, u.email, u.salt, u.login_key, u.notify_with_post, u.language, u.group_id, g.g_global_moderator, g.g_admin FROM '.$this->db->prefix.'users AS u INNER JOIN '.$this->db->prefix.'forum_subscriptions AS s ON u.id=s.user_id LEFT JOIN '.$this->db->prefix.'groups AS g ON u.group_id=g.g_id LEFT JOIN '.$this->db->prefix.'forum_perms AS fp ON (fp.forum_id=:post_id AND fp.group_id=u.group_id) LEFT JOIN '.$this->db->prefix.'bans AS b ON u.username=b.username WHERE b.username IS NULL AND (fp.read_forum IS NULL OR fp.read_forum=1) AND s.forum_id=:forum_id AND u.id!=:id', $data);
		if ($ps->rowCount())
		{
			$cleaned_message = $this->bbcode2email($post_data['message'], -1);
			$moderators = ($this->forums[$forum_id]['moderators'] != '') ? unserialize($this->forums[$forum_id]['moderators']) : array();

			// Loop through subscribed users and send emails
			foreach ($ps as $cur_subscriber)
			{
				if ($this->forums[$forum_id]['protected'] == '1' && $cur_subscriber['g_global_moderator'] != '1' && $cur_subscriber['g_admin'] != '1' && $cur_subscriber['group_id'] != PANTHER_ADMIN && !in_array($cur_subscriber['username'], $moderators) && $cur_subscriber['id'] != $poster_id || !file_exists(PANTHER_ROOT.'lang/'.$cur_subscriber['language'].'/mail_templates/new_topic.tpl'))
					continue;

				$token = panther_hash($cur_subscriber['id'].'viewforum.php'.$cur_subscriber['salt'].$cur_subscriber['login_key']);
				$info = array(
					'subject' => array(
						'<forum_name>' => $post_data['forum_name'],
					),
					'message' => array(
						'<username>' => $cur_subscriber['username'],
						'<topic_subject>' => $post_data['subject'],
						'<forum_name>' => $post_data['forum_name'],
						'<poster>' => $replier,
						'<topic_url>' => panther_link($this->url['topic'], array($tid, url_friendly($post_data['subject']))),
						'<unsubscribe_url>' => panther_link($this->url['forum_unsubscribe'], array($forum_id, $token)),
						'<message>' => $cleaned_message,
					),
				);

				// Load the "new topic" template
				$mail_tpl = $this->parse(PANTHER_ROOT.'lang/'.$cur_subscriber['language'].'/mail_templates/'.(($cur_subscriber['notify_with_post'] == '0') ? 'new_topic' : 'new_topic_full').'.tpl', $info);
				$this->send($cur_subscriber['email'], $mail_tpl['subject'], $mail_tpl['message']);
			}
		}
	}	
}

$mailer = new email($panther_bans, $panther_url, $lang_common, $panther_config, $db, $panther_user, $panther_forums);