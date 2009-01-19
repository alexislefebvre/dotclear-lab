<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcCron, a plugin for Dotclear.
# 
# Copyright (c) 2008 Tomtom
# http://blog.zesntyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class dcCron
{
	protected $tasks;

	/**
	 * Class constructor. Sets new dcCron object
	 *
	 * @param:	$core	dcCore
	 */
	public function __construct(&$core)
	{
		$this->core =& $core;
		$this->tasks = unserialize($core->blog->settings->dccron_tasks);
	}

	/**
	 * Checks if it need to run saved tasks
	 */
	public function check()
	{
		$time = time() + dt::getTimeOffset($this->core->blog->settings->blog_timezone);

		foreach ($this->tasks as $k => $v) {
			if ($time > $v['last_run'] + $v['interval']) {
				call_user_func($v['callback']);
				$v['last_run'] = $time;
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
	public function putTask($nid,$interval,$callback)
	{
		if (!preg_match('#^[a-zA-Z0-9]*$#',$nid)) {
			$this->core->error->add(__('[dcCron] Provide a valid id. Should be just letters and numbers'));
			return false;
		}
		if (!is_numeric($interval)) {
			$this->core->error->add(__('[dcCron] Provide a valid interval. Should be a number in second'));
			return false;
		}
		if (!is_array($callback) || !is_callable($callback)) {
			$this->core->error->add(sprintf(__('[dcCron] Provide a valid callback for task : %s'),$nid));
			return false;
		}

		$last_run = array_key_exists($nid,$this->tasks) ? $this->tasks[$nid]['last_run'] : time() + dt::getTimeOffset($this->core->blog->settings->blog_timezone);

		$this->tasks[$nid] = array(
			'id' => $nid,
			'interval' => $interval,
			'last_run' => $last_run,
			'callback' => $callback
		);

		$this->save();
		return true;
	}

	/**
	 * Deletes tasks by nid. Returns true if it's ok, false if not
	 *
	 * @param:	$nid	array
	 *
	 * @return:	boolean
	 */
	public function delTask($nid)
	{
		if (!is_array($nid)) {
			return false;
		}

		foreach ($nid as $k => $v) {
			if (array_key_exists($v,$this->tasks)) {
				unset($this->tasks[$v]);
				$this->save();
			}
			else {
				$this->core->error->add(sprintf(__('[dcCron] Impossible to delete task: %s. It does not exists'),$v));
			}
		}
		return true;	
	}

	/**
	 * Retrieves alla tasks
	 *
	 * @return:	array
	 */
	public function getTasks()
	{
		return $this->tasks;
	}

	/**
	 * Saves tasks array on blog settings
	 */
	private function save()
	{
		try {
			$this->core->blog->settings->setNamespace('dccron');
			$this->core->blog->settings->put('dccron_tasks',serialize($this->tasks),'string');
			$this->core->blog->triggerBlog();
		} catch (Exception $e) {
			$this->core->error->add($e->getMessage());
		}
	}
}

?>