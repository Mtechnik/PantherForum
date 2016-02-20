<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PANTHER'))
	exit;

class task_forum_updater extends task_scheduler
{
		public function __construct($db, $panther_config, $updater)
		{
			if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
				require PANTHER_ROOT.'include/cache.php';

			$panther_updates = generate_update_cache();
			if ($panther_config['o_update_type'] == '0')
				return;

			if (version_compare($panther_config['o_cur_version'], $updater->panther_updates['version'], '<'))	
			{
				if ($panther_config['o_update_type'] == '2' || $panther_config['o_update_type'] == '3')
					$updater->download();

				$file_name = 'panther-update-patch-'.$updater->version_friendly($updater->panther_updates['version']).'.zip';
				if (file_exists(PANTHER_ROOT.'include/updates/'.$file_name) && ($panther_config['o_update_type'] == '1' || $panther_config['o_update_type'] == '3'))
					$updater->install();
			}
		}
}