<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PANTHER'))
	exit;

class panther_updater
{
	public $panther_updates;
	public function __construct($db, $panther_config, $lang_common)
	{
		$this->db = $db;
		$this->panther_config = $panther_config;
		$this->lang = $lang_common;

		if (file_exists(FORUM_CACHE_DIR.'cache_updates.php'))
			require FORUM_CACHE_DIR.'cache_updates.php';
		else
		{
			if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
				require PANTHER_ROOT.'include/cache.php';

			generate_update_cache();
			require FORUM_CACHE_DIR.'cache_updates.php';
		}
	}

	public function download()
	{
		$this->file_name = 'panther-update-patch-'.$this->version_friendly($this->panther_updates['version']).'.zip';
		if (file_exists(PANTHER_ROOT.'include/updates/'.$this->file_name))
			return;

		if (version_compare($this->panther_config['o_cur_version'], $this->panther_updates['version'], '<') && !file_exists(PANTHER_ROOT.'include/updates/'.$this->file_name))
		{
			if (is_callable('curl_init'))
			{
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://www.pantherforum.org/get_patch.php');
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, array(
					'version' => $this->panther_updates['version'],
				));

				$result = curl_exec($ch);
				curl_close($ch);

				file_put_contents(PANTHER_ROOT.'include/updates/'.$this->file_name, $result);
				if (!file_exists(PANTHER_ROOT.'include/updates/'.$this->file_name))
					exit($this->lang['update failed']);
			}
			else
				exit($this->lang['curl disabled']);
		}
	}

	public function install()
	{
		if (class_exists('ZipArchive'))
		{
			if (!file_exists(PANTHER_ROOT.'include/updates/'.$this->file_name))
				exit($this->lang['update failed']);

			$zip = new ZipArchive();
			if ($zip->open(PANTHER_ROOT.'include/updates/'.$this->file_name))
			{
				if (!is_dir(PANTHER_ROOT.'include/updates/'.$this->version_friendly($this->panther_updates['version'])))
					mkdir(PANTHER_ROOT.'include/updates/'.$this->version_friendly($this->panther_updates['version']));

				$zip->extractTo(PANTHER_ROOT.'include/updates/'.$this->version_friendly($this->panther_updates['version']));
				$zip->close();

				unlink(PANTHER_ROOT.'include/updates/'.$this->file_name);
				if (file_exists(PANTHER_ROOT.'include/updates/'.$this->version_friendly($this->panther_updates['version']).'/panther_database.php'))
					require PANTHER_ROOT.'include/updates/'.$this->version_friendly($this->panther_updates['version']).'/panther_database.php';

				if (file_exists(PANTHER_ROOT.'include/updates/'.$this->version_friendly($this->panther_updates['version']).'/panther_updates.php'))
					require PANTHER_ROOT.'include/updates/'.$this->version_friendly($this->panther_updates['version']).'/panther_updates.php';

				if (!defined('PANTHER_REQUIRED_MYSQL') || !defined('PANTHER_REQUIRED_PHP') || !defined('PANTHER_NEW_VERSION'))
					exit($this->lang['Invalid update patch']);

				if (!preg_match('/^[0-9]{1}\.[0-9]{1}\.[0-9]{1}$/', PANTHER_NEW_VERSION) || !preg_match('/^[0-9]{1}\.[0-9]{1}\.[0-9]{2}(|[-a-z]+)$/', PANTHER_REQUIRED_MYSQL) || !preg_match('/^[0-9]{1}\.[0-9]{1}\.[0-9]{1,2}$/', PANTHER_REQUIRED_PHP))
					exit($this->lang['Invalid update patch']);

				$mysql = $this->db->get_version();
				$php_version = phpversion();

				if (PANTHER_REQUIRED_MYSQL > $mysql['version'] || PANTHER_REQUIRED_PHP > $php_version)
					exit(sprintf($this->lang['Hosting environment does not support Panther x'], PANTHER_NEW_VERSION, PANTHER_REQUIRED_MYSQL, PANTHER_REQUIRED_PHP, $mysql['version'], $php_version));

				if (isset($this->database)) // There are updates to perform for the Panther database
				{
					if (!empty($this->database['alter']))
					{
						foreach ($this->database['alter'] as $table => $query)
							$this->db->run('ALTER TABLE '.$this->db->prefix.$table.$query);
					}

					if (!empty($this->database['drop']))
					{
						foreach ($this->database['drop'] as $table => $query)
							$this->db->drop_table($table);
					}

					if (!empty($this->database['create']))
					{
						foreach ($this->database['create'] as $table => $schema)
							$this->db->create_table($table, $schema);
					}

					if (!empty($this->database['rename']))
					{
						foreach ($this->database['rename'] as $old_name => $new_name)
							$this->db->rename_table($old_name, $new_name);
					}

					if (!empty($this->database['drop_field']))
					{
						foreach ($this->database['drop_field'] as $table => $field)
							$this->db->drop_field($table, $field);
					}

					if (!empty($this->database['query']))
					{
						foreach ($this->database['query'] as $table => $query)
						{
							$query = str_replace($table, $this->db->prefix.$table, $query); // Make sure a valid database prefix is present.
							$this->db->run($query);
						}
					}
				}

				$update = array(
					'conf_value' => PANTHER_NEW_VERSION,
				);

				$data = array(
					':conf_name' => 'o_cur_version',
				);

				$this->db->update('config', $update, 'conf_name=:conf_name', $data);

				$this->db->end_transaction();
				$this->db->close();

				if (isset($this->updates['replace']))
				{
					foreach ($this->updates['replace'] as $file => $replace)
					{
						if (file_exists(PANTHER_ROOT.$file))
							unlink(PANTHER_ROOT.$file);

						rename(PANTHER_ROOT.'include/updates/'.$this->version_friendly($this->panther_updates['version']).'/'.$replace, PANTHER_ROOT.$file);
					}
				}

				if (isset($this->updates['add']))
				{
					foreach ($this->updates['add'] as $file => $replace)
						rename(PANTHER_ROOT.'include/updates/'.$this->version_friendly($this->panther_updates['version']).'/'.$replace, PANTHER_ROOT.$file);
				}

				if (isset($this->updates['remove']))
				{
					foreach ($this->updates['remove'] as $file)
						unlink(PANTHER_ROOT.$file);
				}

				$files = array_diff(scandir(PANTHER_ROOT.'include/updates/'.$this->version_friendly($this->panther_updates['version'])), array('.', '..'));
				foreach ($files as $file)
					unlink(PANTHER_ROOT.'include/updates/'.$this->version_friendly($this->panther_updates['version']).'/'.$file);

				rmdir(PANTHER_ROOT.'include/updates/'.$this->version_friendly($this->panther_updates['version']));
				forum_clear_cache();
			}
			else
				exit(sprintf($this->lang['Unable to open archive'], $this->file_name));
		}
		else
			exit(sprintf($this->lang['ZipArchive not supported'], $this->panther_updates['version']));	
	}

	public function version_friendly($str)
	{
		$str = strtolower(utf8_decode($str));
		$str = panther_trim(preg_replace(array('/[^a-z0-9\s.]/', '/[\s]+/'), array('', '-'), $str), '-');

		return $str;
	}
}