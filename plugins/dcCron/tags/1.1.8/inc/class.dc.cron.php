<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcCron, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
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
	public function __construct($core)
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
			if (
				$time > $v['last_run'] + $v['interval'] &&
				($v['first_run'] === null || $v['first_run'] < $time) &&
				$v['enabled']
			) {
				if (call_user_func($v['callback'],$k) === false) {
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
	 * @param:	$nid			string
	 * @param:	$interval		int
	 * @param:	$callback		array
	 * @param:	$first_run	int
	 *
	 * @return:	boolean
	 */
	public function put($nid,$interval,$callback,$first_run = null)
	{
		if (!preg_match('#^[a-zA-Z0-9\_\-]*$#',$nid)) {
			$this->core->error->add(__('[dcCron] Provide a valid id. Should be just letters and numbers'));
			return false;
		}
		if (!is_numeric($interval)) {
			$this->core->error->add(__('[dcCron] Provide a valid interval. Should be a number in second'));
			return false;
		}
		if (!is_array($callback) || !is_callable($callback) || is_object($callback[0])) {
			$this->core->error->add(sprintf(__('[dcCron] Provide a valid callback for task : %s'),$nid));
			return false;
		}
		if ($first_run === false) {
			$this->core->error->add(__('[dcCron] Provide a valid date for the first execution'));
			return false;
		}
		if ($first_run !== null && $first_run < time() + dt::getTimeOffset($this->core->blog->settings->blog_timezone)) {
			$this->core->error->add(__('[dcCron] Date of the first execution must be higher than now'));
			return false;
		}

		$cond_interval = array_key_exists($nid,$this->tasks) && $this->tasks[$nid]['interval'] != $interval;
		$cond_callback = array_key_exists($nid,$this->tasks) ? serialize($this->tasks[$nid]['callback']) !== serialize($callback) : false;
		$cond_exists = !array_key_exists($nid,$this->tasks);
		$cond_first_run = array_key_exists($nid,$this->tasks) && $this->tasks[$nid]['first_run'] != $first_run;

		if ($cond_interval || $cond_callback || $cond_exists || $cond_first_run) {
			if ($first_run === null) {
				call_user_func($callback);
			}

			$last_run = array_key_exists($nid,$this->tasks) ? $this->tasks[$nid]['last_run'] : time() + dt::getTimeOffset($this->core->blog->settings->blog_timezone);
			$last_run = $first_run !== null && !array_key_exists($nid,$this->tasks) ? 0 : $last_run;

			$this->tasks[$nid] = array(
				'id' => $nid,
				'interval' => $interval,
				'first_run' => $first_run,
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
				$this->core->error->add(sprintf(__('[dcCron] Impossible to delete task: %s. It does not exist'),$v));
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
			$this->core->error->add(sprintf(__('[dcCron] Impossible to enable task: %s. It does not exist'),$nid));
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
			$this->core->error->add(sprintf(__('[dcCron] Impossible to disable task: %s. It does not exist'),$nid));
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
	 * Returns task interval called by nid. if not, returns false
	 *
	 * @param:	nid	string
	 *
	 * @return:	mixed
	 */
	public function getTaskInterval($nid)
	{
		return array_key_exists($nid,$this->tasks) ? $this->tasks[$nid]['interval'] : false;
	}

	/**
	 * Returns next run date called by nid. if not, returns false
	 *
	 * @param:	nid	string
	 *
	 * @return:	mixed
	 */
	public function getNextRunDate($nid)
	{
		return array_key_exists($nid,$this->tasks) ? $this->tasks[$nid]['last_run'] + $this->tasks[$nid]['interval'] : false;
	}

	/**
	 * Returns released time called by nid. if not, returns false
	 *
	 * @param:	nid	string
	 *
	 * @return:	mixed
	 */
	public function getRemainingTime($nid)
	{
		$time = time() + dt::getTimeOffset($this->core->blog->settings->blog_timezone);

		$remaining = $this->getNextRunDate($nid) - $time;

		return array_key_exists($nid,$this->tasks) ? $remaining : false;
	}

	/**
	 * Saves tasks array on blog settings
	 */
	private function save()
	{
		try {
			$this->core->blog->settings->setNamespace('dccron');
			$this->core->blog->settings->put('dccron_tasks',serialize($this->tasks),'string');
			$this->core->blog->settings->put('dccron_errors',serialize($this->errors),'string');
			$this->core->blog->triggerBlog();
		}
		catch (Exception $e) {}
	}
}

?>