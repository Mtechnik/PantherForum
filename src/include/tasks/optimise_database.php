<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PANTHER'))
	exit;

class task_optimise_database extends task_scheduler
{
		public function __construct($db)
		{
			$ps = $db->run('SHOW TABLE STATUS');
			foreach ($ps as $cur_table)
				$db->run('OPTIMIZE TABLE '.$cur_table['Name']);
		}
}