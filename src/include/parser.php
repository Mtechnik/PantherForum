<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PANTHER'))
    exit;

class parser
{
	/* regular expression to match nested BBCode LIST tags
	'%
	\[list                # match opening bracket and tag name of outermost LIST tag
	(?:=([1a*]))?+        # optional attribute capture in group 1
	\]                    # closing bracket of outermost opening LIST tag
	(                     # capture contents of LIST tag in group 2
	  (?:                 # non capture group for either contents or whole nested LIST
		[^\[]*+           # unroll the loop! consume everything up to next [ (normal *)
		(?:               # (See "Mastering Regular Expressions" chapter 6 for details)
		  (?!             # negative lookahead ensures we are NOT on [LIST*] or [/LIST]
			\[list        # opening LIST tag
			(?:=[1a*])?+  # with optional attribute
			\]            # closing bracket of opening LIST tag
			|             # or...
			\[/list\]     # a closing LIST tag
		  )               # end negative lookahead assertion (we are not on a LIST tag)
		  \[              # match the [ which is NOT the start of LIST tag (special)
		  [^\[]*+         # consume everything up to next [ (normal *)
		)*+               # finish up "unrolling the loop" technique (special (normal*))*
	  |                   # or...
		(?R)              # recursively match a whole nested LIST element
	  )*                  # as many times as necessary until deepest nested LIST tag grabbed
	)                     # end capturing contents of LIST tag into group 2
	\[/list\]             # match outermost closing LIST tag
	%iex' */
	public $smilies = array();
	private $re_list = '%\[list(?:=([1a*]))?+\]((?:[^\[]*+(?:(?!\[list(?:=[1a*])?+\]|\[/list\])\[[^\[]*+)*+|(?R))*)\[/list\]%i';
	public function __construct($panther_config, $panther_user, $lang_common, $lang_post, $db, $lang_profile)
	{
		// Load the smilies
		if (file_exists(FORUM_CACHE_DIR.'cache_smilies.php'))
			include FORUM_CACHE_DIR.'cache_smilies.php';
		else
		{
			if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
				require PANTHER_ROOT.'include/cache.php';

			generate_smilies_cache();
			require FORUM_CACHE_DIR.'cache_smilies.php';
		}

		$this->smilies = $smilies;
		$this->user = $panther_user;
		$this->config = $panther_config;
		$this->lang_common = $lang_common;
		$this->lang_post = $lang_post;
		$this->lang_profile = $lang_profile;
		$this->db = $db;
	}

	//
	// Make sure all BBCodes are lower case and do a little cleanup
	//
	public function preparse_bbcode($text, &$errors, $is_signature = false)
	{
		// Remove empty tags
		while (($new_text = $this->strip_empty_bbcode($text)) !== false)
		{
			if ($new_text != $text)
			{
				$text = $new_text;
				if ($new_text == '')
				{
					$errors[] = $this->lang_post['Empty after strip'];
					return '';
				}
			}
			else
				break;
		}

		if ($is_signature)
		{
			if (preg_match('%\[/?(?:quote|code|list|h)\b[^\]]*\]%i', $text))
				$errors[] = $this->lang_profile['Signature quote/code/list/h'];
		}

		// If the message contains a code tag we have to split it up (text within [code][/code] shouldn't be touched)
		if (strpos($text, '[code]') !== false && strpos($text, '[/code]') !== false)
			list($inside, $text) = extract_blocks($text, '[code]', '[/code]');

		if (strpos($text, '[user]') !== false)
		{
			for ($i = 0; $i < $this->config['o_user_tags_max']; $i++)
			{
				preg_match('~\[user\](.*?)\[\/user\]~', $text, $match);
				if (isset($match[1]))
				{
					// If it's in the array, then we've already replaced this earlier so avoid the duplicate query
					if (isset($user_tags[strtolower($match[1])]))
						continue;

					$data = array(
						':username'	=>	$match[1],
					);

					$ps = $this->db->select('users', 1, $data, 'username=:username AND id!=1');
					if (!$ps->rowCount())
					{
						$errors[] = sprintf($this->lang_post['User x no exists'], panther_htmlspecialchars($match[1]));
						break;
					}
				}	
			}
		}

		// Tidy up lists
		$temp = preg_replace_callback($this->re_list, function ($matches)
		{
			return $this->preparse_list_tag($matches[2], $matches[1]);
		}, $text);

		// If the regex failed
		if (is_null($temp))
			$errors[] = $this->lang_common['BBCode list size error'];
		else
			$text = str_replace('*'."\0".']', '*]', $temp);

		if ($this->config['o_make_links'] == '1')
			$text = $this->do_clickable($text);

		$temp_text = false;
		if (empty($errors))
			$temp_text = $this->preparse_tags($text, $errors, $is_signature);

		if ($temp_text !== false)
			$text = $temp_text;

		// If we split up the message before we have to concatenate it together again (code tags)
		if (isset($inside))
		{
			$outside = explode("\1", $text);
			$text = '';

			$num_tokens = count($outside);
			for ($i = 0; $i < $num_tokens; ++$i)
			{
				$text .= $outside[$i];
				if (isset($inside[$i]))
					$text .= '[code]'.$inside[$i].'[/code]';
			}

			unset($inside);
		}

		// Remove empty tags
		while (($new_text = $this->strip_empty_bbcode($text)) !== false)
		{
			if ($new_text != $text)
			{
				$text = $new_text;
				if ($new_text == '')
				{
					$errors[] = $this->lang_post['Empty after strip'];
					break;
				}
			}
			else
				break;
		}

		return panther_trim($text);
	}

	//
	// Strip empty bbcode tags from some text
	//
	public function strip_empty_bbcode($text)
	{
		// If the message contains a code tag we have to split it up (empty tags within [code][/code] are fine)
		if (strpos($text, '[code]') !== false && strpos($text, '[/code]') !== false)
			list($inside, $text) = extract_blocks($text, '[code]', '[/code]');

		// Remove empty tags
		while (!is_null($new_text = preg_replace('%\[(spoiler|b|u|s|ins|del|em|i|h|colou?r|quote|img|url|email|list|topic|post|forum|user)(?:\=[^\]]*)?\]\s*\[/\1\]%', '', $text)))
		{
			if ($new_text != $text)
				$text = $new_text;
			else
				break;
		}

		// If we split up the message before we have to concatenate it together again (code tags)
		if (isset($inside))
		{
			$parts = explode("\1", $text);
			$text = '';
			foreach ($parts as $i => $part)
			{
				$text .= $part;
				if (isset($inside[$i]))
					$text .= '[code]'.$inside[$i].'[/code]';
			}
		}

		// Remove empty code tags
		while (!is_null($new_text = preg_replace('%\[(code)\]\s*\[/\1\]%', '', $text)))
		{
			if ($new_text != $text)
				$text = $new_text;
			else
				break;
		}

		return $text;
	}

	//
	// Check the structure of bbcode tags and fix simple mistakes where possible
	//
	public function preparse_tags($text, &$errors, $is_signature = false)
	{
		// Start off by making some arrays of bbcode tags and what we need to do with each one

		// List of all the tags
		$tags = array('spoiler', 'quote', 'code', 'b', 'i', 'u', 's', 'ins', 'del', 'em', 'color', 'colour', 'url', 'email', 'img', 'list', '*', 'h', 'topic', 'post', 'forum', 'user');
		// List of tags that we need to check are open (You could not put b,i,u in here then illegal nesting like [b][i][/b][/i] would be allowed)
		$tags_opened = $tags;
		// and tags we need to check are closed (the same as above, added it just in case)
		$tags_closed = $tags;
		// Tags we can nest and the depth they can be nested to
		$tags_nested = array('quote' => $this->config['o_quote_depth'], 'list' => 5, '*' => 5, 'spoiler' => 5);
		// Tags to ignore the contents of completely (just code)
		$tags_ignore = array('code');
		// Tags not allowed
		$tags_forbidden = array();
		// Block tags, block tags can only go within another block tag, they cannot be in a normal tag
		$tags_block = array('quote', 'code', 'list', 'h', '*', 'spoiler');
		// Inline tags, we do not allow new lines in these
		$tags_inline = array('b', 'i', 'u', 's', 'ins', 'del', 'em', 'color', 'colour', 'h', 'topic', 'post', 'forum', 'user');
		// Tags we trim interior space
		$tags_trim = array('img');
		// Tags we remove quotes from the argument
		$tags_quotes = array('url', 'email', 'img', 'topic', 'post', 'forum', 'user');
		// Tags we limit bbcode in
		$tags_limit_bbcode = array(
			'*' 	=> array('b', 'i', 'u', 's', 'ins', 'del', 'em', 'color', 'colour', 'url', 'email', 'list', 'img', 'code', 'topic', 'post', 'forum', 'user'),
			'list' 	=> array('*'),
			'url' 	=> array('img'),
			'email' => array('img'),
			'topic' => array('img'),
			'post'  => array('img'),
			'forum' => array('img'),
			'user'  => array('img'),
			'img' 	=> array(),
			'h'		=> array('b', 'i', 'u', 's', 'ins', 'del', 'em', 'color', 'colour', 'url', 'email', 'topic', 'post', 'forum', 'user'),
		);
		// Tags we can automatically fix bad nesting
		$tags_fix = array('quote', 'b', 'i', 'u', 's', 'ins', 'del', 'em', 'color', 'colour', 'url', 'email', 'h', 'topic', 'post', 'forum', 'user');

		// Disallow URL tags
		if ($this->user['g_post_links'] != '1')
			$tags_forbidden[] = 'url';

		$split_text = preg_split('%(\[[\*a-zA-Z0-9-/]*?(?:=.*?)?\])%', $text, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);

		$open_tags = array('panther-bbcode');
		$open_args = array('');
		$opened_tag = 0;
		$new_text = '';
		$current_ignore = '';
		$current_nest = '';
		$current_depth = array();
		$limit_bbcode = $tags;
		$count_ignored = array();

		foreach ($split_text as $current)
		{
			if ($current == '')
				continue;

			// Are we dealing with a tag?
			if (substr($current, 0, 1) != '[' || substr($current, -1, 1) != ']')
			{
				// It's not a bbcode tag so we put it on the end and continue
				// If we are nested too deeply don't add to the end
				if ($current_nest)
					continue;

				$current = str_replace("\r\n", "\n", $current);
				$current = str_replace("\r", "\n", $current);
				if (in_array($open_tags[$opened_tag], $tags_inline) && strpos($current, "\n") !== false)
				{
					// Deal with new lines
					$split_current = preg_split('%(\n\n+)%', $current, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
					$current = '';

					if (!panther_trim($split_current[0], "\n")) // The first part is a linebreak so we need to handle any open tags first
						array_unshift($split_current, '');

					for ($i = 1; $i < count($split_current); $i += 2)
					{
						$temp_opened = array();
						$temp_opened_arg = array();
						$temp = $split_current[$i - 1];
						while (!empty($open_tags))
						{
							$temp_tag = array_pop($open_tags);
							$temp_arg = array_pop($open_args);

							if (in_array($temp_tag , $tags_inline))
							{
								array_push($temp_opened, $temp_tag);
								array_push($temp_opened_arg, $temp_arg);
								$temp .= '[/'.$temp_tag.']';
							}
							else
							{
								array_push($open_tags, $temp_tag);
								array_push($open_args, $temp_arg);
								break;
							}
						}
						$current .= $temp.$split_current[$i];
						$temp = '';
						while (!empty($temp_opened))
						{
							$temp_tag = array_pop($temp_opened);
							$temp_arg = array_pop($temp_opened_arg);
							if (empty($temp_arg))
								$temp .= '['.$temp_tag.']';
							else
								$temp .= '['.$temp_tag.'='.$temp_arg.']';
							array_push($open_tags, $temp_tag);
							array_push($open_args, $temp_arg);
						}
						$current .= $temp;
					}

					if (isset($split_content[$i-1]))
						$current .= $split_current[$i-1];
				}

				if (in_array($open_tags[$opened_tag], $tags_trim))
					$new_text .= panther_trim($current);
				else
					$new_text .= $current;

				continue;
			}

			// Get the name of the tag
			$current_arg = '';
			if (strpos($current, '/') === 1)
			{
				$current_tag = substr($current, 2, -1);
			}
			else if (strpos($current, '=') === false)
			{
				$current_tag = substr($current, 1, -1);
			}
			else
			{
				$current_tag = substr($current, 1, strpos($current, '=')-1);
				$current_arg = substr($current, strpos($current, '=')+1, -1);
			}
			$current_tag = strtolower($current_tag);

			// Is the tag defined?
			if (!in_array($current_tag, $tags))
			{
				// It's not a bbcode tag so we put it on the end and continue
				if (!$current_nest)
					$new_text .= $current;

				continue;
			}

			// We definitely have a bbcode tag

			// Make the tag string lower case
			if ($equalpos = strpos($current,'='))
			{
				// We have an argument for the tag which we don't want to make lowercase
				if (strlen(substr($current, $equalpos)) == 2)
				{
					// Empty tag argument
					$errors[] = sprintf($this->lang_common['BBCode error empty attribute'], $current_tag);
					return false;
				}
				$current = strtolower(substr($current, 0, $equalpos)).substr($current, $equalpos);
			}
			else
				$current = strtolower($current);

			// This is if we are currently in a tag which escapes other bbcode such as code
			// We keep a count of ignored bbcodes (code tags) so we can nest them, but
			// only balanced sets of tags can be nested
			if ($current_ignore)
			{
				// Increase the current ignored tags counter
				if ('['.$current_ignore.']' == $current)
					$count_ignored[$current_tag]++;

				// Decrease the current ignored tags counter
				if ('[/'.$current_ignore.']' == $current)
					$count_ignored[$current_tag]--;

				if ('[/'.$current_ignore.']' == $current && $count_ignored[$current_tag] == 0)
				{
					// We've finished the ignored section
					$current = '[/'.$current_tag.']';
					$current_ignore = '';
					$count_ignored = array();
				}

				$new_text .= $current;
				continue;
			}

			// Is the tag forbidden?
			if (in_array($current_tag, $tags_forbidden))
			{
				if (isset($this->lang_common['BBCode error tag '.$current_tag.' not allowed']))
					$errors[] = sprintf($this->lang_common['BBCode error tag '.$current_tag.' not allowed']);
				else
					$errors[] = sprintf($this->lang_common['BBCode error tag not allowed'], $current_tag);

				return false;
			}

			if ($current_nest)
			{
				// We are currently too deeply nested so lets see if we are closing the tag or not
				if ($current_tag != $current_nest)
					continue;

				if (substr($current, 1, 1) == '/')
					$current_depth[$current_nest]--;
				else
					$current_depth[$current_nest]++;

				if ($current_depth[$current_nest] <= $tags_nested[$current_nest])
					$current_nest = '';

				continue;
			}

			// Check the current tag is allowed here
			if (!in_array($current_tag, $limit_bbcode) && $current_tag != $open_tags[$opened_tag])
			{
				$errors[] = sprintf($this->lang_common['BBCode error invalid nesting'], $current_tag, $open_tags[$opened_tag]);
				return false;
			}

			if (substr($current, 1, 1) == '/')
			{
				// This is if we are closing a tag
				if ($opened_tag == 0 || !in_array($current_tag, $open_tags))
				{
					// We tried to close a tag which is not open
					if (in_array($current_tag, $tags_opened))
					{
						$errors[] = sprintf($this->lang_common['BBCode error no opening tag'], $current_tag);
						return false;
					}
				}
				else
				{
					// Check nesting
					while (true)
					{
						// Nesting is ok
						if ($open_tags[$opened_tag] == $current_tag)
						{
							array_pop($open_tags);
							array_pop($open_args);
							$opened_tag--;
							break;
						}

						// Nesting isn't ok, try to fix it
						if (in_array($open_tags[$opened_tag], $tags_closed) && in_array($current_tag, $tags_closed))
						{
							if (in_array($current_tag, $open_tags))
							{
								$temp_opened = array();
								$temp_opened_arg = array();
								$temp = '';
								while (!empty($open_tags))
								{
									$temp_tag = array_pop($open_tags);
									$temp_arg = array_pop($open_args);

									if (!in_array($temp_tag, $tags_fix))
									{
										// We couldn't fix nesting
										$errors[] = sprintf($this->lang_common['BBCode error no closing tag'], $temp_opened);
										return false;
									}
									array_push($temp_opened, $temp_tag);
									array_push($temp_opened_arg, $temp_arg);

									if ($temp_tag == $current_tag)
										break;
									else
										$temp .= '[/'.$temp_tag.']';
								}
								$current = $temp.$current;
								$temp = '';
								array_pop($temp_opened);
								array_pop($temp_opened_arg);

								while (!empty($temp_opened))
								{
									$temp_tag = array_pop($temp_opened);
									$temp_arg = array_pop($temp_opened_arg);
									if (empty($temp_arg))
										$temp .= '['.$temp_tag.']';
									else
										$temp .= '['.$temp_tag.'='.$temp_arg.']';
									array_push($open_tags, $temp_tag);
									array_push($open_args, $temp_arg);
								}
								$current .= $temp;
								$opened_tag--;
								break;
							}
							else
							{
								// We couldn't fix nesting
								$errors[] = sprintf($this->lang_common['BBCode error no opening tag'], $current_tag);
								return false;
							}
						}
						else if (in_array($open_tags[$opened_tag], $tags_closed))
							break;
						else
						{
							array_pop($open_tags);
							array_pop($open_args);
							$opened_tag--;
						}
					}
				}

				if (in_array($current_tag, array_keys($tags_nested)))
				{
					if (isset($current_depth[$current_tag]))
						$current_depth[$current_tag]--;
				}

				if (in_array($open_tags[$opened_tag], array_keys($tags_limit_bbcode)))
					$limit_bbcode = $tags_limit_bbcode[$open_tags[$opened_tag]];
				else
					$limit_bbcode = $tags;

				$new_text .= $current;

				continue;
			}
			else
			{
				// We are opening a tag
				if (in_array($current_tag, array_keys($tags_limit_bbcode)))
					$limit_bbcode = $tags_limit_bbcode[$current_tag];
				else
					$limit_bbcode = $tags;

				if (in_array($current_tag, $tags_block) && !in_array($open_tags[$opened_tag], $tags_block) && $opened_tag != 0)
				{
					// We tried to open a block tag within a non-block tag
					$this->errors[] = sprintf($this->lang_common['BBCode error invalid nesting'], $current_tag, $open_tags[$opened_tag]);
					return false;
				}

				if (in_array($current_tag, $tags_ignore))
				{
					// It's an ignore tag so we don't need to worry about what's inside it
					$current_ignore = $current_tag;
					$count_ignored[$current_tag] = 1;
					$new_text .= $current;
					continue;
				}

				// Deal with nested tags
				if (in_array($current_tag, $open_tags) && !in_array($current_tag, array_keys($tags_nested)))
				{
					// We nested a tag we shouldn't
					$errors[] = sprintf($this->lang_common['BBCode error invalid self-nesting'], $current_tag);
					return false;
				}
				else if (in_array($current_tag, array_keys($tags_nested)))
				{
					// We are allowed to nest this tag
					if (isset($current_depth[$current_tag]))
						$current_depth[$current_tag]++;
					else
						$current_depth[$current_tag] = 1;

					// See if we are nested too deep
					if ($current_depth[$current_tag] > $tags_nested[$current_tag])
					{
						$current_nest = $current_tag;
						continue;
					}
				}

				// Remove quotes from arguments for certain tags
				if (strpos($current, '=') !== false && in_array($current_tag, $tags_quotes))
					$current = preg_replace('%\['.$current_tag.'=("|\'|)(.*?)\\1\]\s*%i', '['.$current_tag.'=$2]', $current);

				if (in_array($current_tag, array_keys($tags_limit_bbcode)))
					$limit_bbcode = $tags_limit_bbcode[$current_tag];

				$open_tags[] = $current_tag;
				$open_args[] = $current_arg;
				$opened_tag++;
				$new_text .= $current;
				continue;
			}
		}

		// Check we closed all the tags we needed to
		foreach ($tags_closed as $check)
		{
			if (in_array($check, $open_tags))
			{
				// We left an important tag open
				$errors[] = sprintf($this->lang_common['BBCode error no closing tag'], $check);
				return false;
			}
		}

		if ($current_ignore)
		{
			// We left an ignore tag open
			$errors[] = sprintf($this->lang_common['BBCode error no closing tag'], $current_ignore);
			return false;
		}

		return $new_text;
	}

	//
	// Preparse the contents of [list] bbcode
	//
	private function preparse_list_tag($content, $type = '*')
	{
		if (strlen($type) != 1)
			$type = '*';

		if (strpos($content,'[list') !== false)
			$content = preg_replace_callback($this->re_list, function ($matches)
			{
				return $this->preparse_list_tag($matches[2], $matches[1]);
			}, $content);

		$items = explode('[*]', str_replace('\"', '"', $content));

		$content = '';
		foreach ($items as $item)
		{
			if (panther_trim($item) != '')
				$content .= '[*'."\0".']'.str_replace('[/*]', '', panther_trim($item)).'[/*'."\0".']'."\n";
		}

		return '[list='.$type.']'."\n".$content.'[/list]';
	}

	//
	// Truncate URL if longer than 55 characters (add http:// or ftp:// if missing)
	//
	private function handle_url_tag($url, $link = '', $bbcode = false)
	{
		$url = panther_trim($url);

		// Deal with [url][img]http://example.com/test.png[/img][/url]
		if (preg_match('%<img src=\"(.*?)\"%', $url, $matches))
			return $this->handle_url_tag($matches[1], $url, $bbcode);

		$full_url = str_replace(array(' ', '\'', '`', '"'), array('%20', '', '', ''), $url);
		if (strpos($url, 'www.') === 0) // If it starts with www, we add http://
			$full_url = 'http://'.$full_url;
		else if (strpos($url, 'ftp.') === 0) // Else if it starts with ftp, we add ftp://
			$full_url = 'ftp://'.$full_url;
		else if (strpos($url, '/') === 0) // Allow for relative URLs that start with a slash
			$full_url = get_base_url().$full_url;
		else if (!preg_match('#^([a-z0-9]{3,6})://#', $url)) // Else if it doesn't start with abcdef://, we add http://
			$full_url = 'http://'.$full_url;

		// Ok, not very pretty :-)
		if ($bbcode)
		{
			if ($full_url == $link)
				return '[url]'.$link.'[/url]';
			else
				return '[url='.$full_url.']'.$link.'[/url]';
		}
		else
		{
			if ($link == '' || $link == $url)
			{
				$url = panther_htmlspecialchars_decode($url);
				$link = utf8_strlen($url) > 55 ? utf8_substr($url, 0 , 39).' … '.utf8_substr($url, -10) : $url;
				$link = panther_htmlspecialchars($link);
			}
			else
				$link = stripslashes($link);

			return '<a href="'.$full_url.'" rel="nofollow">'.$link.'</a>';
		}
	}

	//
	// Turns an URL from the [img] tag into an <img> tag or a <a href...> tag
	//
	private function handle_img_tag($url, $is_signature = false, $alt = null)
	{
		if (is_null($alt))
			$alt = basename($url);

		$img_tag = '<a href="'.$url.'" rel="nofollow">&lt;'.$this->lang_common['Image link'].' - '.$alt.'&gt;</a>';

		if ($is_signature && $this->user['show_img_sig'] != '0')
			$img_tag = '<img class="sigimage" src="'.$url.'" alt="'.$alt.'" />';
		else if (!$is_signature && $this->user['show_img'] != '0')
			$img_tag = '<span class="postimg"><img src="'.$url.'" alt="'.$alt.'" /></span>';

		return $img_tag;
	}

	//
	// Parse the contents of [list] bbcode
	//
	private function handle_list_tag($content, $type = '*')
	{
		if (strlen($type) != 1)
			$type = '*';

		if (strpos($content,'[list') !== false)
		{
			$content = preg_replace_callback($this->re_list, function ($matches)
			{
				return $this->preparse_list_tag($matches[2], $matches[1]);
			}, $content);
		}

		$content = preg_replace('#\s*\[\*\](.*?)\[/\*\]\s*#s', '<li><p>$1</p></li>', panther_trim($content));

		if ($type == '*')
			$content = '<ul>'.$content.'</ul>';
		else
			if ($type == 'a')
				$content = '<ol class="alpha">'.$content.'</ol>';
			else
				$content = '<ol class="decimal">'.$content.'</ol>';

		return '</p>'.$content.'<p>';
	}

	//
	// Convert BBCodes to their HTML equivalent
	//
	private function do_bbcode($text, $is_signature = false)
	{
		static $user_tags = array();
		if (strpos($text, '[quote') !== false)
		{
			$text = preg_replace('%\[quote\]\s*%', '</p><div class="quotebox"><blockquote><div><p>', $text);
			$text = preg_replace_callback('%\[quote=(&quot;|&\#039;|"|\'|)([^\r\n]*?)\\1\]%s', function ($matches)
			{
				return '</p><div class="quotebox"><cite>'.str_replace(array('[', '"'), array('&#91;', '"'), $matches[2]).' '.$this->lang_common['wrote'].'</cite><blockquote><div><p>';
			}, $text);
			$text = preg_replace('%\s*\[\/quote\]%S', '</p></div></blockquote></div><p>', $text);
		}

		if (strpos($text, '[user]') !== false)
		{
			for ($i = 0; $i < $this->config['o_user_tags_max']; $i++)
			{
				preg_match('~\[user\](.*?)\[\/user\]~', $text, $match);
				if (isset($match[1]))
				{
					// If it's in the array, then we've already replaced this using in an earlier so avoid the database query
					if (!isset($user_tags[strtolower($match[1])]))
					{
						$data = array(
							':username'	=>	$match[1],
						);

						$ps = $this->db->select('users', 'username, id, group_id', $data, 'username=:username');
						if ($ps->rowCount())
						{
							$cur_user = $ps->fetch();

							$user_tags[strtolower($cur_user['username'])] = colourize_group($cur_user['username'], $cur_user['group_id'], $cur_user['id']);
							$text = preg_replace('%\[user\]'.$match[1].'\s*\[\/user\]%S', $user_tags[strtolower($cur_user['username'])], $text);
						}
					}
					else
					{
						$text = preg_replace('%\[user\]'.$match[1].'\s*\[\/user\]%S', $user_tags[strtolower($match[1])], $text);
						$i--;
					}
				}	
			}
		}

		if (strpos($text, '[spoiler') !== false)
		{
			$text = str_replace('[spoiler]', "</p><div class=\"quotebox\" style=\"padding: 0px;\"><div onclick=\"var e,d,c=this.parentNode,a=c.getElementsByTagName('div')[1],b=this.getElementsByTagName('span')[0];if(a.style.display!=''){while(c.parentNode&&(!d||!e||d==e)){e=d;d=(window.getComputedStyle?getComputedStyle(c, null):c.currentStyle)['backgroundColor'];if(d=='transparent'||d=='rgba(0, 0, 0, 0)')d=e;c=c.parentNode;}a.style.display='';a.style.backgroundColor=d;b.innerHTML='&#9650;';}else{a.style.display='none';b.innerHTML='&#9660;';}\" style=\"font-weight: bold; cursor: pointer; font-size: 0.9em;\"><span style=\"padding: 0 5px;\">&#9660;</span>".$this->lang_common['Spoiler']."</div><div style=\"padding: 6px; margin: 0; display: none;\"><p>", $text);
			$text = preg_replace('#\[spoiler=(.*?)\]#s', '</p><div class="quotebox" style="padding: 0px;"><div onclick="var e,d,c=this.parentNode,a=c.getElementsByTagName(\'div\')[1],b=this.getElementsByTagName(\'span\')[0];if(a.style.display!=\'\'){while(c.parentNode&&(!d||!e||d==e)){e=d;d=(window.getComputedStyle?getComputedStyle(c, null):c.currentStyle)[\'backgroundColor\'];if(d==\'transparent\'||d==\'rgba(0, 0, 0, 0)\')d=e;c=c.parentNode;}a.style.display=\'\';a.style.backgroundColor=d;b.innerHTML=\'&#9650;\';}else{a.style.display=\'none\';b.innerHTML=\'&#9660;\';}" style="font-weight: bold; cursor: pointer; font-size: 0.9em;"><span style="padding: 0 5px;">&#9660;</span>$1</div><div style="padding: 6px; margin: 0; display: none;"><p>', $text);
			$text = str_replace('[/spoiler]', '</p></div></div><p>', $text);
		}

		if (!$is_signature)
		{
			$pattern_callback[] = $this->re_list;
			$replace_callback[] = '$this->handle_list_tag($matches[2], $matches[1])';
		}

		$pattern[] = '%\[b\](.*?)\[/b\]%ms';
		$pattern[] = '%\[i\](.*?)\[/i\]%ms';
		$pattern[] = '%\[u\](.*?)\[/u\]%ms';
		$pattern[] = '%\[s\](.*?)\[/s\]%ms';
		$pattern[] = '%\[del\](.*?)\[/del\]%ms';
		$pattern[] = '%\[ins\](.*?)\[/ins\]%ms';
		$pattern[] = '%\[em\](.*?)\[/em\]%ms';
		$pattern[] = '%\[colou?r=([a-zA-Z]{3,20}|\#[0-9a-fA-F]{6}|\#[0-9a-fA-F]{3})](.*?)\[/colou?r\]%ms';
		$pattern[] = '%\[h\](.*?)\[/h\]%ms';

		$replace[] = '<strong>$1</strong>';
		$replace[] = '<em>$1</em>';
		$replace[] = '<span class="bbu">$1</span>';
		$replace[] = '<span class="bbs">$1</span>';
		$replace[] = '<del>$1</del>';
		$replace[] = '<ins>$1</ins>';
		$replace[] = '<em>$1</em>';
		$replace[] = '<span style="color: $1">$2</span>';
		$replace[] = '</p><h5>$1</h5><p>';

		if (($is_signature && $this->config['p_sig_img_tag'] == '1') || (!$is_signature && $this->config['p_message_img_tag'] == '1'))
		{
			$pattern_callback[] = '%\[img\]((ht|f)tps?://)([^\s<"]*?)\[/img\]%';
			$pattern_callback[] = '%\[img=([^\[]*?)\]((ht|f)tps?://)([^\s<"]*?)\[/img\]%';
			if ($is_signature)
			{
				$replace_callback[] = '$this->handle_img_tag($matches[1].$matches[3], true)';
				$replace_callback[] = '$this->handle_img_tag($matches[2].$matches[4], true, $matches[1])';
			}
			else
			{
				$replace_callback[] = '$this->handle_img_tag($matches[1].$matches[3], false)';
				$replace_callback[] = '$this->handle_img_tag($matches[2].$matches[4], false, $matches[1])';
			}
		}

		$pattern_callback[] = '%\[url\]([^\[]*?)\[/url\]%';
		$pattern_callback[] = '%\[url=([^\[]+?)\](.*?)\[/url\]%';
		$pattern[] = '%\[email\]([^\[]*?)\[/email\]%';
		$pattern[] = '%\[email=([^\[]+?)\](.*?)\[/email\]%';
		$pattern_callback[] = '%\[topic\]([1-9]\d*)\[/topic\]%';
		$pattern_callback[] = '%\[topic=([1-9]\d*)\](.*?)\[/topic\]%';
		$pattern_callback[] = '%\[post\]([1-9]\d*)\[/post\]%';
		$pattern_callback[] = '%\[post=([1-9]\d*)\](.*?)\[/post\]%';
		$pattern_callback[] = '%\[forum\]([1-9]\d*)\[/forum\]%';
		$pattern_callback[] = '%\[forum=([1-9]\d*)\](.*?)\[/forum\]%';
		$pattern_callback[] = '%\[user\]([a-zA-Z1-9]\d*)\[/user\]%';

		$replace_callback[] = '$this->handle_url_tag($matches[1])';
		$replace_callback[] = '$this->handle_url_tag($matches[1], $matches[2])';
		$replace[] = '<a href="mailto:$1">$1</a>';
		$replace[] = '<a href="mailto:$1">$2</a>';
		$replace_callback[] = '$this->handle_url_tag(\''.get_base_url().'/viewtopic.php?id=\'.$matches[1])';
		$replace_callback[] = '$this->handle_url_tag(\''.get_base_url().'/viewtopic.php?id=\'.$matches[1], $matches[2])';
		$replace_callback[] = '$this->handle_url_tag(\''.get_base_url().'/viewtopic.php?pid=\'.$matches[1].\'#p\'.$matches[1])';
		$replace_callback[] = '$this->handle_url_tag(\''.get_base_url().'/viewtopic.php?pid=\'.$matches[1].\'#p\'.$matches[1], $matches[2])';
		$replace_callback[] = '$this->handle_url_tag(\''.get_base_url().'/viewforum.php?id=\'.$matches[1])';
		$replace_callback[] = '$this->handle_url_tag(\''.get_base_url().'/viewforum.php?id=\'.$matches[1], $matches[2])';

		$parser = $this; // Support for PHP 5.3

		// This thing takes a while! :)
		$text = preg_replace($pattern, $replace, $text);
		$count = count($pattern_callback);
		for($i = 0 ; $i < $count ; $i++)
			$text = preg_replace_callback($pattern_callback[$i], function ($matches) use ($parser, $i, $replace_callback)
			{
				return eval('return '.$replace_callback[$i].';');
			}, $text);

		return $text;
	}

	//
	// Make hyperlinks clickable
	//
	function do_clickable($text)
	{
		$text = ' '.$text;
		$text = $this->ucp_preg_replace_callback('%(?<=[\s\]\)])(<)?(\[)?(\()?([\'"]?)(https?|ftp|news){1}://([\p{L}\p{N}\-]+\.([\p{L}\p{N}\-]+\.)*[\p{L}\p{N}]+(:[0-9]+)?(/(?:[^\s\[]*[^\s.,?!\[;:-])?)?)\4(?(3)(\)))(?(2)(\]))(?(1)(>))(?![^\s]*\[/(?:url|img)\])%ui', 'stripslashes($matches[1].$matches[2].$matches[3].$matches[4]).$this->handle_url_tag($matches[5]."://".$matches[6], $matches[5]."://".$matches[6], true).stripslashes($matches[4].$this->forum_array_key($matches, 10).$this->forum_array_key($matches, 11).$this->forum_array_key($matches, 12))', $text);
		$text = $this->ucp_preg_replace_callback('%(?<=[\s\]\)])(<)?(\[)?(\()?([\'"]?)(www|ftp)\.(([\p{L}\p{N}\-]+\.)+[\p{L}\p{N}]+(:[0-9]+)?(/(?:[^\s\[]*[^\s.,?!\[;:-])?)?)\4(?(3)(\)))(?(2)(\]))(?(1)(>))(?![^\s]*\[/(?:url|img)\])%ui','stripslashes($matches[1].$matches[2].$matches[3].$matches[4]).$this->handle_url_tag($matches[5].".".$matches[6], $matches[5].".".$matches[6], true).stripslashes($matches[4].$this->forum_array_key($matches, 10).$this->forum_array_key($matches, 11).$this->forum_array_key($matches, 12))', $text);

		return substr($text, 1);
	}

	//
	// Return an array key, if it exists, otherwise return an empty string
	//
	private function forum_array_key($arr, $key)
	{
		return isset($arr[$key]) ? $arr[$key] : '';
	}
	
	//
	// Unfortunately, this had to be duplicated and added into this class to avoid a global variable in functions.php ...
	//
	private function ucp_preg_replace_callback($pattern, $replace, $subject)
	{
		$parser = $this; // PHP 5.3 support
		$replaced = preg_replace_callback($pattern, function ($matches) use($parser, $replace)
		{
			return eval('return '.$replace.';');
		}, $subject);
		if ($replaced === false)
		{
			if (is_array($pattern))
			{
				foreach ($pattern as $cur_key => $cur_pattern)
					$pattern[$cur_key] = str_replace('\p{L}\p{N}', '\w', $cur_pattern);

				$replaced = preg_replace($pattern, $replace, $subject);
			}
			else
				$replaced = preg_replace(str_replace('\p{L}\p{N}', '\w', $pattern), $replace, $subject);
		}

		return $replaced;
	}

	//
	// Convert a series of smilies to images
	//
	private function do_smilies($text)
	{
		$this->config['o_smilies_dir'] = ($this->config['o_smilies_dir'] != '') ? $this->config['o_smilies_dir'] : panther_htmlspecialchars(get_base_url().'/'.$this->config['o_smilies_path'].'/');

		$text = ' '.$text.' ';
		foreach ($this->smilies as $smiley_text => $smiley_img)
		{
			if (strpos($text, $smiley_text) !== false)
				$text = ucp_preg_replace('%(?<=[>\s])'.preg_quote($smiley_text, '%').'(?=[^\p{L}\p{N}])%um', '<img src="'.$this->config['o_smilies_dir'].$smiley_img.'" width="15" height="15" alt="'.substr($smiley_img, 0, strrpos($smiley_img, '.')).'" />', $text);
		}

		return substr($text, 1, -1);
	}

	//
	// Parse message text
	//
	public function parse_message($text, $hide_smilies)
	{
		if ($this->config['o_censoring'] == '1')
			$text = censor_words($text);

		// Convert applicable characters to HTML entities
		$text = panther_htmlspecialchars($text);

		// If the message contains a code tag we have to split it up (text within [code][/code] shouldn't be touched)
		if (strpos($text, '[code]') !== false && strpos($text, '[/code]') !== false)
			list($inside, $text) = extract_blocks($text, '[code]', '[/code]');

		if ($this->config['p_message_bbcode'] == '1' && strpos($text, '[') !== false && strpos($text, ']') !== false)
			$text = $this->do_bbcode($text);

		if ($this->config['o_smilies'] == '1' && $this->user['show_smilies'] == '1' && $hide_smilies == '0')
			$text = $this->do_smilies($text);

		// Deal with newlines, tabs and multiple spaces
		$pattern = array("\n", "\t", '  ', '  ');
		$replace = array('<br />', '&#160; &#160; ', '&#160; ', ' &#160;');
		$text = str_replace($pattern, $replace, $text);

		// If we split up the message before we have to concatenate it together again (code tags)
		if (isset($inside))
		{
			$parts = explode("\1", $text);
			$text = '';
			foreach ($parts as $i => $part)
			{
				$text .= $part;
				if (isset($inside[$i]))
				{
					$num_lines = (substr_count($inside[$i], "\n"));
					$text .= '</p><div class="codebox"><pre'.(($num_lines > 28) ? ' class="vscroll"' : '').'><code>'.panther_trim($inside[$i], "\n\r").'</code></pre></div><p>';
				}
			}
		}

		return $this->clean_paragraphs($text);
	}

	//
	// Clean up paragraphs and line breaks
	//
	private function clean_paragraphs($text)
	{
		// Add paragraph tag around post, but make sure there are no empty paragraphs
		$text = '<p>'.$text.'</p>';

		// Replace any breaks next to paragraphs so our replace below catches them
		$text = preg_replace('%(</?p>)(?:\s*?<br />){1,2}%i', '$1', $text);
		$text = preg_replace('%(?:<br />\s*?){1,2}(</?p>)%i', '$1', $text);

		// Remove any empty paragraph tags (inserted via quotes/lists/code/etc) which should be stripped
		$text = str_replace('<p></p>', '', $text);

		$text = preg_replace('%<br />\s*?<br />%i', '</p><p>', $text);

		$text = str_replace('<p><br />', '<br /><p>', $text);
		$text = str_replace('<br /></p>', '</p><br />', $text);
		$text = str_replace('<p></p>', '<br /><br />', $text);

		return $text;
	}

	//
	// Parse signature text
	//
	public function parse_signature($text)
	{
		if ($this->config['o_censoring'] == '1')
			$text = censor_words($text);

		// Convert applicable characters to HTML entities
		$text = panther_htmlspecialchars($text);

		if ($this->config['p_sig_bbcode'] == '1' && strpos($text, '[') !== false && strpos($text, ']') !== false)
			$text = $this->do_bbcode($text, true);

		if ($this->config['o_smilies_sig'] == '1' && $this->user['show_smilies'] == '1')
			$text = $this->do_smilies($text);

		// Deal with newlines, tabs and multiple spaces
		$pattern = array("\n", "\t", '  ', '  ');
		$replace = array('<br />', '&#160; &#160; ', '&#160; ', ' &#160;');
		$text = str_replace($pattern, $replace, $text);

		return $this->clean_paragraphs($text);
	}
}

$parser = new parser($panther_config, $panther_user, $lang_common, (isset($lang_post) ? $lang_post : array()), $db, (isset($lang_profile) ? $lang_profile : array()));