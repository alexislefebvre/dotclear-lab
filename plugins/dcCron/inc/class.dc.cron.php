<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcCron, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zesntyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class dcCron
{
	protected $tasks;
	protected $errors;

	/**
	 * Class constructor. Sets new dcCron object
	 *
	 * @param:	$core	dcCore
	 */
	public function __construct(&$core)
	{
		$this->core =& $core;
		$this->tasks = $core->blog->settings->dccron_tasks != '' ? unserialize($core->blog->settings->dccron_tasks) : array();
		$this->errors = $core->blog->settings->dccron_errors != '' ? unserialize($core->blog->settings->dccron_errors) : array();
	}

	/**
	 * Checks if it need to run saved tasks
	 */
	public function check()
	{
		$time = time() + dt::getTimeOffset($this->core->blog->settings->blog_timezone);
		$format = $this->core->blog->settings->date_format.' - %H:%M:%S';

		foreach ($this->tasks as $k => $v) {
			if ($this->checkInterval($v,$time)) {
				if (call_user_func($v['callback']) === false) {
					$this->errors[$k] = sprintf(__('[%s] Impossible to execute task : %s'),dt::str($format,$time),$k); 
				}
				else {
					$this->tasks[$k]['last_run'] = $time;
				}
			}
		}

		$this->save();
	}

	/**
	 * Adds or edits task set by nid, time interval and callback function. Returns true if it's ok, false if not
	 *
	 * @param:	$nid		string
	 * @param:	$interval	string
	 * @param:	$callback	array
	 *
	 * @return:	boolean
	 */
	public function put($nid,$interval,$callback)
	{
		if (!preg_match('#^[a-zA-Z0-9\_\-]*$#',$nid)) {
			$this->core->error->add(__('[dcCron] Provide a valid id. Should be just letters and numbers'));
			return false;
		}
		if (!$this->isValidInterval($interval)) {
			$this->core->error->add(__('[dcCron] Provide a valid interval. Should be a number in second or a special string (see help)'));
			return false;
		}
		if (!is_array($callback) || !is_callable($callback) || is_object($callback[0])) {
			$this->core->error->add(sprintf(__('[dcCron] Provide a valid callback for task : %s'),$nid));
			return false;
		}

		$cond_interval = array_key_exists($nid,$this->tasks) && $this->tasks[$nid]['interval'] != $interval;
		$cond_callback = array_key_exists($nid,$this->tasks) ? serialize($this->tasks[$nid]['callback']) !== serialize($callback) : false;
		$cond_exists = !array_key_exists($nid,$this->tasks);

		if ($cond_interval || $cond_callback || $cond_exists) {
			call_user_func($callback);

			$last_run = array_key_exists($nid,$this->tasks) ? $this->tasks[$nid]['last_run'] : time() + dt::getTimeOffset($this->core->blog->settings->blog_timezone);

			$this->tasks[$nid] = array(
				'id' => $nid,
				'interval' => $interval,
				'last_run' => $last_run,
				'callback' => $callback,
				'enabled' => true
			);

			$this->save();

			return true;
		}

		return false;
	}

	/**
	 * Deletes tasks by nid. Returns true if it's ok, false if not
	 *
	 * @param:	$nid	array
	 *
	 * @return:	boolean
	 */
	public function del($nid)
	{
		$res = true;

		if (!is_array($nid)) {
			$this->core->error->add(__('[dcCron] Invalid format to delete task'));
			$res = false;
		}

		if (count($nid) == 0) {
			$this->core->error->add(__('[dcCron] No task specified to delete'));
			$res = false;
		}

		foreach ($nid as $k => $v) {
			if (array_key_exists($v,$this->tasks)) {
				unset($this->tasks[$v]);
				if (array_key_exists($v,$this->errors)) {
					unset($this->errors[$v]);
				}
				$this->save();
			}
			else {
				$this->core->error->add(sprintf(__('[dcCron] Impossible to delete task: %s. It does not exists'),$v));
				$res = false;
			}
		}

		return $res;	
	}

	/**
	 * Enables task
	 *
	 * @param:	nid	string
	 */
	public function enable($nid)
	{
		if (array_key_exists($nid,$this->tasks)) {
			$this->tasks[$nid]['enabled'] = true;
			$this->save();
			return true;
		}
		else {
			$this->core->error->add(sprintf(__('[dcCron] Impossible to enable task: %s. It does not exists'),$nid));
			return false;
		}
	}

	/**
	 * Disables task
	 *
	 * @param:	nid	string
	 */
	public function disable($nid)
	{
		if (array_key_exists($nid,$this->tasks)) {
			$this->tasks[$nid]['enabled'] = false;
			$this->save();
			return true;
		}
		else {
			$this->core->error->add(sprintf(__('[dcCron] Impossible to disable task: %s. It does not exists'),$nid));
			return false;
		}
	}

	/**
	 * Retrieves alls enabled tasks
	 *
	 * @return:	array
	 */
	public function getEnabledTasks()
	{
		$res = array();

		foreach ($this->tasks as $k => $v) {
			if ($v['enabled']) {
				$res[$k] = $v; 
			}
		}

		return $res;
	}

	/**
	 * Retrieves alls disabled tasks
	 *
	 * @return:	array
	 */
	public function getDisabledTasks()
	{
		$res = array();

		foreach ($this->tasks as $k => $v) {
			if (!$v['enabled']) {
				$res[$k] = $v; 
			}
		}

		return $res;
	}

	/**
	 * Retrieves alls tasks
	 *
	 * @return:	array
	 */
	public function getTasks()
	{
		return $this->tasks;
	}

	/**
	 * Retrieves alls errors
	 *
	 * @return:	array
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Returns true if task called by nid exists. if not, returns false
	 *
	 * @param:	nid	string
	 *
	 * @return:	boolean
	 */
	public function taskExists($nid)
	{
		return array_key_exists($nid,$this->tasks) ? true : false;
	}

	/**
	 * Saves tasks array on blog settings
	 */
	private function save()
	{
		$this->core->blog->settings->setNamespace('dccron');
		$this->core->blog->settings->put('dccron_tasks',serialize($this->tasks),'string');
		$this->core->blog->settings->put('dccron_errors',serialize($this->errors),'string');
		$this->core->blog->triggerBlog();
	}

	/**
	 * Returns if interval is in a valid format
	 *
	 * @param:	inteval	string
	 *
	 * @return:	boolean
	 */
	private function isValidInterval($interval)
	{
		if (preg_match('#^(([0-9]{1,2}|\*)\s?){4}$#',$interval)) {
			return true;
		}
		elseif (is_numeric($interval)) {
			return true;
		}
		return false;
	}

	/**
	 * Checks if interval is reach or not
	 *
	 * @param:	task	array
	 * @param:	time	string
	 *
	 * @return:	boolean
	 */
	private function checkInterval($task,$time)
	{
		if (is_numeric($task['interval'])) {
			if ($time > $task['last_run'] + $task['interval'] && $task['enabled']) {
				return true;
			}
		}
		else {
			list($hour,$min,$day,$month) = explode(' ',$task['interval']);
			$hour = $hour == '*' ? date('H',$time) : $hour;
			$min = $min == '*' ? date('i',$time) : $min;
			$day = $day == '*' ? date('j',$time) : $day;
			$month = $month == '*' ? date('n',$time) : $month;
			if ($time > mktime($hour,$min,date('s'),$month,$day,date('Y')) && $task['enabled']) {
				return true;
			}
		}
		return false;
	}
}

?>