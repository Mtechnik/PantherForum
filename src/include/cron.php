#!/usr/bin/php -q
<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */
if (substr(PHP_SAPI, 0, 3) != 'cli')
{
	// Output transparent gif
	header('Expires: Thu, 21 Jul 1977 07:30:00 GMT');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('X-Frame-Options: deny');
	header('Cache-Control: no-cache');
	header('Content-type: image/gif');
	header('Content-length: 43');

	echo base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');

	flush();
	exit;
}

define('IN_CRON', true);
define('PANTHER_DISABLE_BUFFERING', true);
define('PANTHER_QUIET_VISIT', true);
define('PANTHER_ROOT', __DIR__.'/../');
require PANTHER_ROOT.'include/common.php';

($hook = get_extensions('task_after_run')) ? eval($hook) : null;

$db->end_transaction();
$db->close();
exit;