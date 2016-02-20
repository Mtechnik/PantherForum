<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

// The contents of this file are very much inspired by the file functions_search.php
// from the phpBB Group forum software phpBB2 (http://www.phpbb.com)

// Make sure no one attempts to run this script "directly"
if (!defined('PANTHER'))
	exit;

// Make a regex that will match CJK or Hangul characters
define('PANTHER_CJK_HANGUL_REGEX', '['.
	'\x{1100}-\x{11FF}'.		// Hangul Jamo							1100-11FF		(http://www.fileformat.info/info/unicode/block/hangul_jamo/index.htm)
	'\x{3130}-\x{318F}'.		// Hangul Compatibility Jamo			3130-318F		(http://www.fileformat.info/info/unicode/block/hangul_compatibility_jamo/index.htm)
	'\x{AC00}-\x{D7AF}'.		// Hangul Syllables						AC00-D7AF		(http://www.fileformat.info/info/unicode/block/hangul_syllables/index.htm)

	// Hiragana
	'\x{3040}-\x{309F}'.		// Hiragana								3040-309F		(http://www.fileformat.info/info/unicode/block/hiragana/index.htm)

	// Katakana
	'\x{30A0}-\x{30FF}'.		// Katakana								30A0-30FF		(http://www.fileformat.info/info/unicode/block/katakana/index.htm)
	'\x{31F0}-\x{31FF}'.		// Katakana Phonetic Extensions			31F0-31FF		(http://www.fileformat.info/info/unicode/block/katakana_phonetic_extensions/index.htm)

	// CJK Unified Ideographs	(http://en.wikipedia.org/wiki/CJK_Unified_Ideographs)
	'\x{2E80}-\x{2EFF}'.		// CJK Radicals Supplement				2E80-2EFF		(http://www.fileformat.info/info/unicode/block/cjk_radicals_supplement/index.htm)
	'\x{2F00}-\x{2FDF}'.		// Kangxi Radicals						2F00-2FDF		(http://www.fileformat.info/info/unicode/block/kangxi_radicals/index.htm)
	'\x{2FF0}-\x{2FFF}'.		// Ideographic Description Characters	2FF0-2FFF		(http://www.fileformat.info/info/unicode/block/ideographic_description_characters/index.htm)
	'\x{3000}-\x{303F}'.		// CJK Symbols and Punctuation			3000-303F		(http://www.fileformat.info/info/unicode/block/cjk_symbols_and_pantherctuation/index.htm)
	'\x{31C0}-\x{31EF}'.		// CJK Strokes							31C0-31EF		(http://www.fileformat.info/info/unicode/block/cjk_strokes/index.htm)
	'\x{3200}-\x{32FF}'.		// Enclosed CJK Letters and Months		3200-32FF		(http://www.fileformat.info/info/unicode/block/enclosed_cjk_letters_and_months/index.htm)
	'\x{3400}-\x{4DBF}'.		// CJK Unified Ideographs Extension A	3400-4DBF		(http://www.fileformat.info/info/unicode/block/cjk_unified_ideographs_extension_a/index.htm)
	'\x{4E00}-\x{9FFF}'.		// CJK Unified Ideographs				4E00-9FFF		(http://www.fileformat.info/info/unicode/block/cjk_unified_ideographs/index.htm)
	'\x{20000}-\x{2A6DF}'.		// CJK Unified Ideographs Extension B	20000-2A6DF		(http://www.fileformat.info/info/unicode/block/cjk_unified_ideographs_extension_b/index.htm)
']');

//
// "Cleans up" a text string and returns an array of unique words
// This function depends on the current locale setting
//
function split_words($text, $idx)
{
	// Remove BBCode
	$text = preg_replace('%\[/?(b|u|s|ins|del|em|i|h|colou?r|quote|code|img|url|email|list|topic|post|forum|user)(?:\=[^\]]*)?\]%', ' ', $text);

	// Remove any apostrophes or dashes which aren't part of words
	$text = substr(ucp_preg_replace('%((?<=[^\p{L}\p{N}])[\'\-]|[\'\-](?=[^\p{L}\p{N}]))%u', '', ' '.$text.' '), 1, -1);

	// Remove pantherctuation and symbols (actually anything that isn't a letter or number), allow apostrophes and dashes (and % * if we aren't indexing)
	$text = ucp_preg_replace('%(?![\'\-'.($idx ? '' : '\%\*').'])[^\p{L}\p{N}]+%u', ' ', $text);

	// Replace multiple whitespace or dashes
	$text = preg_replace('%(\s){2,}%u', '\1', $text);

	// Fill an array with all the words
	$words = array_unique(explode(' ', $text));

	// Remove any words that should not be indexed
	foreach ($words as $key => $value)
	{
		// If the word shouldn't be indexed, remove it
		if (!validate_search_word($value, $idx))
			unset($words[$key]);
	}

	return $words;
}

//
// Checks if a word is a valid searchable word
//
function validate_search_word($word, $idx)
{
	static $stopwords;

	// If the word is a keyword we don't want to index it, but we do want to be allowed to search it
	if (is_keyword($word))
		return !$idx;

	if (!isset($stopwords))
	{
		if (file_exists(FORUM_CACHE_DIR.'cache_stopwords.php'))
			include FORUM_CACHE_DIR.'cache_stopwords.php';

		if (!defined('PANTHER_STOPWORDS_LOADED'))
		{
			if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
				require PANTHER_ROOT.'include/cache.php';

			generate_stopwords_cache();
			require FORUM_CACHE_DIR.'cache_stopwords.php';
		}
	}

	// If it is a stopword it isn't valid
	if (in_array($word, $stopwords))
		return false;

	// If the word is CJK we don't want to index it, but we do want to be allowed to search it
	if (is_cjk($word))
		return !$idx;

	// Exclude % and * when checking whether current word is valid
	$word = str_replace(array('%', '*'), '', $word);

	// Check the word is within the min/max length
	$num_chars = panther_strlen($word);
	return $num_chars >= PANTHER_SEARCH_MIN_WORD && $num_chars <= PANTHER_SEARCH_MAX_WORD;
}

//
// Check a given word is a search keyword.
//
function is_keyword($word)
{
	return $word == 'and' || $word == 'or' || $word == 'not';
}

//
// Check if a given word is CJK or Hangul.
//
function is_cjk($word)
{
	return preg_match('%^'.PANTHER_CJK_HANGUL_REGEX.'+$%u', $word) ? true : false;
}

//
// Strip [img] [url] and [email] out of the message so we don't index their contents
//
function strip_bbcode($text)
{
	static $patterns;

	if (!isset($patterns))
	{
		$patterns = array(
			'%\[img=([^\]]*+)\]([^[]*+)\[/img\]%'									=>	'$2 $1',	// Keep the url and description
			'%\[(url|email)=([^\]]*+)\]([^[]*+(?:(?!\[/\1\])\[[^[]*+)*)\[/\1\]%'	=>	'$2 $3',	// Keep the url and text
			'%\[(img|url|email)\]([^[]*+(?:(?!\[/\1\])\[[^[]*+)*)\[/\1\]%'			=>	'$2',		// Keep the url
			'%\[(topic|post|forum|user)\][1-9]\d*\[/\1\]%'							=>	' ',		// Do not index topic/post/forum/user ID
		);
	}

	return preg_replace(array_keys($patterns), array_values($patterns), $text);
}

//
// Updates the search index with the contents of $post_id (and $subject)
//
function update_search_index($mode, $post_id, $message, $subject = null)
{
	global $db;

	$message = utf8_strtolower($message);
	$subject = utf8_strtolower($subject);

	// Remove any bbcode that we shouldn't index
	$message = strip_bbcode($message);

	// Split old and new post/subject to obtain array of 'words'
	$words_message = split_words($message, true);
	$words_subject = ($subject) ? split_words($subject, true) : array();

	if ($mode == 'edit')
	{
		$data = array(
			':id'	=>	$post_id,
		);

		$ps = $db->run('SELECT w.id, w.word, m.subject_match FROM '.$db->prefix.'search_words AS w INNER JOIN '.$db->prefix.'search_matches AS m ON w.id=m.word_id WHERE m.post_id=:id', $data);

		// Declare here to stop array_keys() and array_diff() from complaining if not set
		$cur_words['post'] = array();
		$cur_words['subject'] = array();

		foreach ($ps as $result)
		{
			$match_in = ($result['subject_match']) ? 'subject' : 'post';
			$cur_words[$match_in][$result['word']] = $result['id'];
		}

		$db->free_result($ps);

		$words['add']['post'] = array_diff($words_message, array_keys($cur_words['post']));
		$words['add']['subject'] = array_diff($words_subject, array_keys($cur_words['subject']));
		$words['del']['post'] = array_diff(array_keys($cur_words['post']), $words_message);
		$words['del']['subject'] = array_diff(array_keys($cur_words['subject']), $words_subject);
	}
	else
	{
		$words['add']['post'] = $words_message;
		$words['add']['subject'] = $words_subject;
		$words['del']['post'] = array();
		$words['del']['subject'] = array();
	}

	unset($words_message);
	unset($words_subject);

	// Get unique words from the above arrays
	$unique_words = array_unique(array_merge($words['add']['post'], $words['add']['subject']));
	$data = $placeholders = array();
	foreach ($unique_words as $word)
	{
		$placeholders[] = '?';
		$data[] = $word;
	}

	if (!empty($unique_words))
	{
		$ps = $db->run('SELECT id, word FROM '.$db->prefix.'search_words WHERE word IN('.implode(',', $placeholders).')', array_values($data));

		$word_ids = array();
		foreach ($ps as $cur_row)
			$word_ids[$cur_row['word']] = $cur_row['id'];

		$db->free_result($ps);

		$new_words = array_diff($unique_words, array_keys($word_ids));
		unset($unique_words);

		if (!empty($new_words))
		{
			foreach ($new_words as $word)
			{
				$data = array(
					'word'	=>	$word,
				);

				$db->insert('search_words', $data);
			}
		}

		unset($new_words);
	}

	// Delete matches (only if editing a post)
	foreach ($words['del'] as $match_in => $wordlist)
	{
		$placeholders = $data = array();

		if (!empty($wordlist))
		{
			foreach ($wordlist as $word)
			{
				$placeholders[] = '?';
				$data[] = $cur_words[$match_in][$word];
			}
			
			$data[] = $post_id;
			$data[] = ($match_in == 'subject') ? 1 : 0;

			$db->run('DELETE FROM '.$db->prefix.'search_matches WHERE word_id IN('.implode(',', $placeholders).') AND post_id=? AND subject_match=?', $data);
		}
	}

	// Add new matches
	foreach ($words['add'] as $match_in => $wordlist)
	{
		$placeholders = $data = array();
		$subject_match = ($match_in == 'subject') ? 1 : 0;
		foreach ($wordlist as $word)
		{
			$placeholders[] = '?';
			$data[] = $word;
		}

		if (!empty($wordlist))
			$db->run('INSERT INTO '.$db->prefix.'search_matches (post_id, word_id, subject_match) SELECT '.$post_id.', id, '.$subject_match.' FROM '.$db->prefix.'search_words WHERE word IN('.implode(',', $placeholders).')', $data);
	}

	unset($words);
}

//
// Strip search index of indexed words in $post_ids
//
function strip_search_index($post_ids)
{
	global $db;
	
	$placeholders = $data = array();
	if (!is_array($post_ids))
		$post_ids = explode(',', $post_ids);
	
	foreach ($post_ids as $post_id)
	{
		$placeholders[] = '?';
		$data[] = $post_id;
	}

	$ps = $db->run('SELECT word_id FROM '.$db->prefix.'search_matches WHERE post_id IN('.implode(',', $placeholders).') GROUP BY word_id', $post_ids);
	if ($ps->rowCount())
	{
		$markers = $word_ids =array();
		foreach ($ps as $cur_row)
		{
			$word_ids[] = $cur_row['word_id'];
			$markers[] = '?';
		}

		$ps = $db->run('SELECT word_id FROM '.$db->prefix.'search_matches WHERE word_id IN('.implode(',', $markers).') GROUP BY word_id HAVING COUNT(word_id)=1', $word_ids);
		if ($ps->rowCount())
		{
			$words = $new_markers = array();
			foreach ($ps as $cur_row)
			{
				$words[] = $cur_row['word_id'];
				$new_markers[] = '?';
			}

			$db->run('DELETE FROM '.$db->prefix.'search_words WHERE id IN('.implode(',', $new_markers).')', $words);
		}
	}

	$db->run('DELETE FROM '.$db->prefix.'search_matches WHERE post_id IN('.implode(',', $placeholders).')', $post_ids);
}