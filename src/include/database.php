<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

if (!defined('PANTHER'))
	exit;

class db extends PDO
{
	private $saved_queries = array();
	private $num_queries = 0;
	public $prefix;

	public function __construct($config)
	{
		$this->prefix = panther_trim($config['prefix']);
		$opt = array(
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
			PDO::ATTR_EMULATE_PREPARES => false, // Prior to PHP 5.3.6, this was a security issue. We want actual prepared statements instead of "emulated" ones.
			PDO::ATTR_PERSISTENT => $config['p_connect'],
		);

		try
		{
			parent::__construct("mysql:host=".$config['host'].";dbname=".$config['db_name'].";charset=utf8", $config['username'], $config['password'], $opt);
		}
		catch (PDOException $e)
		{
			error($e->getMessage());
		}
	}

	public function select($table, $fields = '*', $parameters = array(), $where = '', $order_by = '')
	{
		$sql = "SELECT ".$fields." FROM ".$this->prefix.$table.((!empty($where)) ? " WHERE ".$where : '').((!empty($order_by)) ? " ORDER BY ".$order_by : '');
		return $this->run($sql, $parameters, 'select');
	}

	public function insert($table, $fields)
	{
		$parameters = array();
		$sql = "INSERT INTO ".$this->prefix.$table." (".implode(", ", array_keys($fields)).") VALUES (:".implode(", :", array_keys($fields)).")";
		foreach($fields as $column => $value)
			$parameters[':'.$column] = $value;

		return $this->run($sql, $parameters, 'insert');
	}

	public function update($table, $fields, $where = '', $parameters = array())
	{
		$i = 0;
		$sql = "UPDATE ".$this->prefix.$table." SET ";
		foreach ($fields as $column => $value)
		{
			$sql .= (($i > 0) ? ', ' : '').$column."=:".$column;
			$parameters[':'.$column] = $value;
			$i++;
		}

		$sql .= (!empty($where) ? ' WHERE '.$where : '');
		return $this->run($sql, $parameters, 'update');
	}

	public function delete($table, $where, $parameters = array())
	{
		$sql = "DELETE FROM ".$this->prefix.$table." WHERE ".$where;
		return $this->run($sql, $parameters, 'delete');
	}

	public function run($sql, $parameters = array(), $type = '')
	{
		global $panther_config;
		if ($panther_config['o_show_queries'] == '1')
			$q_start = microtime(true);

		$this->sql = panther_trim($sql);

		try
		{
			$ps = $this->prepare($this->sql);
			if ($ps->execute($parameters) !== false)
			{
				if ($panther_config['o_show_queries'] == '1')
					$this->saved_queries[] = array($this->sql, sprintf('%.5f', microtime(true) - $q_start));

				++$this->num_queries;
				if (in_array($type, array('update', 'delete', 'insert')))
					return $ps->rowCount();
				else
				{
					$ps->setFetchMode(PDO::FETCH_ASSOC);
					return $ps;
				}
			}
			else
				error('Unable to execute query', $this->sql, $parameters);
		}
		catch (PDOException $e)
		{
			error($e->getMessage(), $this->sql, $parameters);	
		}
	}
    
	public function start_transaction()
	{
		try
		{
			$this->beginTransaction();
		}
		catch(PDOException $e)
		{
			error($e->getMessage());
		}
	}
	
	public function end_transaction()
	{
		try
		{
			$this->commit();
		}
		catch(PDOException $e)
		{
			error($e->getMessage());
		}
	}
	public function free_result($ps)
	{
		try
		{
			$ps->closeCursor();
		}
		catch(PDOException $e)
		{
			error($e->getMessage());
		}

		return true;	// If we get this far, then there is no error
	}

	public function get_num_queries()
	{
		return $this->num_queries;
	}

	public function get_saved_queries()
	{
		return $this->saved_queries;
	}
	
	public function get_version()
	{
		$sql = "SELECT VERSION()";
		$ps = $this->run($sql);
		
		return array(
			'name'	=>	'MySQL',
			'version'	=>	preg_replace('%^([^-]+).*$%', '\\1', $ps->fetchColumn()),
		);
	}
	
	public function create_table($table_name, $schema)
	{
		if ($this->table_exists($table_name))
			return true;

		$query = 'CREATE TABLE '.$this->prefix.$table_name." (\n";

		// Go through every schema element and add it to the query
		foreach ($schema['FIELDS'] as $field_name => $field_data)
		{
			$query .= $field_name.' '.$field_data['datatype'];

			if (isset($field_data['collation']))
				$query .= 'CHARACTER SET utf8 COLLATE utf8_'.$field_data['collation'];

			if (!$field_data['allow_null'])
				$query .= ' NOT NULL';

			if (isset($field_data['default']))
				$query .= ' DEFAULT '.$field_data['default'];

			$query .= ",\n";
		}

		// If we have a primary key, add it
		if (isset($schema['PRIMARY KEY']))
			$query .= 'PRIMARY KEY ('.implode(',', $schema['PRIMARY KEY']).'),'."\n";

		// Add unique keys
		if (isset($schema['UNIQUE KEYS']))
		{
			foreach ($schema['UNIQUE KEYS'] as $key_name => $key_fields)
				$query .= 'UNIQUE KEY '.$table_name.'_'.$key_name.'('.implode(',', $key_fields).'),'."\n";
		}

		// Add indexes
		if (isset($schema['INDEXES']))
		{
			foreach ($schema['INDEXES'] as $index_name => $index_fields)
				$query .= 'KEY '.$table_name.'_'.$index_name.'('.implode(',', $index_fields).'),'."\n";
		}

		// We remove the last two characters (a newline and a comma) and add on the ending
		$query = substr($query, 0, strlen($query) - 2)."\n".') ENGINE = '.(isset($schema['ENGINE']) ? $schema['ENGINE'] : 'InnoDB').' CHARACTER SET utf8';
		return $this->run($query);	
	}
	
	public function rename_table($old_table, $new_table)
	{
		// If the new table exists and the old one doesn't, then we're happy
		if ($this->table_exists($new_table) && !$this->table_exists($old_table))
			return true;

		return $this->run('ALTER TABLE '.$this->prefix.$old_table.' RENAME TO '.$this->prefix.$new_table) ? true : false;
	}
	
	public function table_exists($table)
	{
		$sql = "SHOW TABLES LIKE '".$this->prefix.$table."'";
		return (($this->run($sql, array(), 'update')) ? true : false);
	}
	
	public function drop_table($table)
	{
		if (!$this->table_exists($table))
			return true;

		return $this->run('DROP TABLE '.$this->prefix.$table_name) ? true : false;
	}

	public function alter_field($table_name, $field_name, $field_type, $allow_null, $default_value = null, $after_field = null)
	{
		if (!$this->field_exists($table_name, $field_name))
			return true;

		$field_type = preg_replace(array_keys($this->datatypes), array_values($this->datatypes), $field_type);
		return $this->run('ALTER TABLE '.$this->prefix.$table_name.' MODIFY '.$field_name.' '.$field_type.($allow_null ? '' : ' NOT NULL').(!is_null($default_value) ? ' DEFAULT '.$default_value : '').(!is_null($after_field) ? ' AFTER '.$after_field : '')) ? true : false;
	}
	
	public function add_field($table_name, $field_name, $field_type, $allow_null, $default_value = null, $after_field = null)
	{
		if ($this->field_exists($table_name, $field_name, $no_prefix))
			return true;

		$field_type = preg_replace(array_keys($this->datatypes), array_values($this->datatypes), $field_type);
		return $this->run('ALTER TABLE '.$this->prefix.$table_name.' ADD '.$field_name.' '.$field_type.($allow_null ? '' : ' NOT NULL').(!is_null($default_value) ? ' DEFAULT '.$default_value : '').(!is_null($after_field) ? ' AFTER '.$after_field : '')) ? true : false;
	}
	
	public function field_exists($table, $field)
	{
		$sql = "SHOW COLUMNS FROM ".$this->prefix.$table." LIKE '".$field."'";
		return (($this->run($sql, array(), 'update') > 0) ? true : false);		
	}
	
	public function drop_field($table_name, $field_name)
	{
		if (!$this->field_exists($table_name, $field_name))
			return true;

		return $this->run('ALTER TABLE '.$this->prefix.$table_name.' DROP '.$field_name) ? true : false;
	}
	
	function index_exists($table_name, $index_name)
	{
		$exists = false;
		$ps = $this->run('SHOW INDEX FROM '.$this->prefix.$table_name);
		foreach ($ps as $cur_index)
		{
			if (strtolower($cur_index['Key_name']) == strtolower($this->prefix.$table_name.'_'.$index_name))
			{
				$exists = true;
				break;
			}
		}

		return $exists;
	}
	
	public function add_index($table_name, $index_name, $index_fields, $unique = false)
	{
		if ($this->index_exists($table_name, $index_name))
			return true;
		
		return $this->run('ALTER TABLE '.$this->prefix.$table_name.' ADD '.($unique ? 'UNIQUE ' : '').'INDEX '.$this->prefix.$table_name.'_'.$index_name.' ('.implode(',', $index_fields).')') ? true : false;
	}
	
	public function drop_index($table_name, $index_name)
	{
		if (!$this->index_exists($table_name, $index_name))
			return true;

		return $this->run('ALTER TABLE '.$this->prefix.$table_name.' DROP INDEX '.$this->prefix.$table_name.'_'.$index_name) ? true : false;
	}
	
	public function truncate_table($table_name)
	{
		return $this->run('TRUNCATE TABLE '.$this->prefix.$table_name) ? true : false;
	}
}

$db = new db($config);