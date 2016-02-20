<?php
/**
 * Copyright (C) 2015 Panther (https://www.pantherforum.org)
 * based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

if (!defined('PANTHER'))
    exit;

class task_scheduler
{
	public function __construct($db, $panther_config, $updater)
	{
		if (!defined('PANTHER_TASKS_LOADED'))
		{
			if (file_exists(FORUM_CACHE_DIR.'cache_tasks.php'))
				require FORUM_CACHE_DIR.'cache_tasks.php';
			else
			{
				if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
					require PANTHER_ROOT.'include/cache.php';

				generate_task_cache();
				require FORUM_CACHE_DIR.'cache_tasks.php';
			}
		}

		$this->db = $db;
		$this->panther_config = $panther_config;
		$this->updater = $updater;
		if ($this->panther_config['o_task_type'] == '0' || defined('IN_CRON'))
		{
			$now = time();
			foreach ($this->panther_tasks as $task_id => $cur_task)
			{
				if ($cur_task['next_run'] < $now)
					$this->run($task_id);
			}
		}
	}

	public function run($task_id)
	{
		$this->task = $this->panther_tasks[$task_id];
		if (!preg_match('/^[a-z-_0-9]+$/i', $this->task['script']) || !file_exists(PANTHER_ROOT.'include/tasks/'.$this->task['script'].'.php'))
			error_handler(E_ERROR, 'Invalid task name or task does not exist', __FILE__, __LINE__);

		if ($this->task['locked'])
			return;

		// Lock the task so it can't be ran twice
		$this->lock();

		$task_name = 'task_'.$this->task['script'];
		if (!class_exists($task_name)) // If there are duplicate tasks, only perform it once
		{
			require PANTHER_ROOT.'include/tasks/'.$this->task['script'].'.php';
			$task = new $task_name($this->db, $this->panther_config, $this->updater);
		}

		$this->lock(0);
	}

	private function lock($lock = 1)
	{
		$update = array(
			'locked' => $lock,
		);

		if (!$lock) // If we're unlocking the task, we need the next run time
			$update['next_run'] = $this->get_next_run($this->task['minute'], $this->task['hour'], $this->task['day'], $this->task['month'], $this->task['week_day']);

		$data = array(
			':id' => $this->task['id'],
		);

		$this->db->update('tasks', $update, 'id=:id', $data);

		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PANTHER_ROOT.'include/cache.php';

		generate_task_cache();
	}

	public function get_next_run($minute, $hour, $day, $month, $week_day)
	{
		$this->now = time();
		$this->current = array(
			'minute' => gmdate('i', $this->now),
			'hour' => gmdate('H', $this->now),
			'day' => gmdate('j',$this->now),
			'month' => gmdate('m', $this->now),
			'week_day' => gmdate('w', $this->now),
			'year' => gmdate('Y', $this->now),
		);

		$this->run = $this->current;

		// Determine whether it should be ran today
		$next_day = ($week_day == '*' && $month == '*') ? 1 : 0;
		$next_minute = ($minute == '*') ? 1 : 0;

		// Sort out the hours
		if ((string) $hour == '*')
		{
			if ($minute !== '*')
				$this->add_hour();
			else
				$this->run['hour'] = $this->current['hour'];
		}
		else
			$this->run['hour'] = $hour;

		// Sort out the days
		if ($day == '*')
		{
			if ($this->run['hour'] < $this->current['hour'])
				$this->add_day();
			else
				$this->run['day'] = $this->current['day'];
		}
		else
			$this->run['day'] = $day;

		// Sort out the months
		if ($month == '*')
		{
			if ($this->run['day'] < $this->current['day'])
				$this->add_month();
			else
				$this->run['month'] = $this->current['month'];
		}
		else
			$this->run['month'] = $month;

		if ($this->run['day'] < $this->current['day'] && $this->run['month'] <= $this->current['month'])
			$this->add_month();

		// Sort out the week days
		if ($week_day !== '*')
		{
			if ($month == '*')
			{
				if ($day == '*')
				{
					$this->run['day'] = $this->current['day'] + ($week_day - $this->current['week_day']);
					if ($this->run['day'] > gmdate('t', $this->now))
						$this->run['day'] = ($this->run['day'] - gmdate('t', $this->now));

					if ($this->run['day'] < $this->current['day'])
						$this->add_day(7);
				}
				else
					$this->run['day'] = $day + ($week_day - ($this->current['week_day']));
			}
			else
			{
				if ($day == '*')
				{
					$this->run['day'] = $this->current['day'] + ($week_day - $this->current['week_day']);
					if ($this->run['day'] > gmdate('t', $this->now))
						$this->run['day'] = ($this->run['day'] - gmdate('t', $this->now));

					$next_year = false;
					if ($this->run['day'] < $this->current['day'] && $this->run['month'] <= $this->current['month'])
					{
						$next_year = true;
						$this->run['year']++;
						
						$week_days = array('Sunday', 'Monday', ''
						);
						
						$month_name = date('F', mktime(0, 0, 0, $this->run['month'], $this->run['year']));
						$days = array(
							'Sunday',
							'Monday',
							'Tuesday',
							'Wednesday',
							'Thursday',
							'Friday',
							'Saturday'
						);

						$this->run['day'] = date('t', strtotime('first '.$days[$week_day].' of '.$month_name.' '.$this->run['year']));
					}

					if ($this->run['day'] < $this->current['day'] && !$next_year)
						$this->add_day(7);
				}
				else
				{
					// Make sure using a day already past in this year isn't possible
					if ($this->run['day'] < $day && $this->run['month'] < $this->current['month'] && $this->run['year'] == $this->current['year'])
						$this->run['year']++;

					$this->found = false;
					while (!$this->found)
					{
						if (date('w', mktime(0, 0, 0, $month, $day, $this->run['year'])) == $week_day) // We have a match!
						{
							$this->found = true;
							break;
						}
						else
							$this->run['year']++;
					}
				}
			}
		}

		// Sort out the minutes
		if ((string) $minute == '*')
		{
			if ((string) $hour == '*')
			{
				if ($this->run['month'] != $this->current['month'])
					$this->run['minute'] = 0;
				else
					$this->add_minute();
			}
			else
				$this->run['minute'] = 0;
		}
		else
		{
			if ($hour == '*' && !$next_day)
				$this->add_minute($minute);
			else
				$this->run['minute'] = $minute;
		}

		// Check things to make sure the time actually ended up in the future
		if ($this->run['month'] < $this->current['month'] && $this->run['year'] <= $this->current['year'])
			$this->run['year']++;

		if ($this->run['hour'] <= $this->current['hour'] && $this->run['day'] == $this->current['day'] && $this->run['month'] <= $this->current['month'] && $this->run['year'] <= $this->current['year'])
		{
			if ($hour == '*')
			{
				if ($this->run['hour'] == $this->current['hour'] && $this->run['minute'] <= $this->current['minute'])
					$this->add_hour();
			}
 			else
				$this->add_day();
		}

		return gmmktime($this->run['hour'], $this->run['minute'], 0, $this->run['month'], $this->run['day'], $this->run['year']);
	}

	private function add_month()
	{
		if ($this->current['month'] == 12)
		{
			$this->run['month'] = 1;
			$this->run['year']++;
		}
		else
			$this->run['month']++;
	}

	private function add_day($days = 1)
	{
		if ($this->current['week_day'] >= (gmdate('t', $this->now) - $days))
		{
			$this->run['day'] = ($this->current['month'] + $days) - date('t', $this->now);
			$this->add_month();
		}
		else
			$this->run['day'] += $days;
	}

	private function add_hour($hour = 1)
	{
		if ($this->current['hour'] >= (24 - $hour))
		{
			$this->run['hour'] = ($this->current['hour'] + $hour) - 24;
			$this->add_day();
		}
		else
			$this->run['hour'] += $hour;
	}

	private function add_minute($minutes = 1)
	{
		if ($this->current['minute'] >= (60 - $minutes))
		{
			$this->run['minute'] = ($this->current['minute'] + $minutes) - 60;
			$this->add_hour();
		}
		else
			$this->run['minute'] += $minutes;
	}
}