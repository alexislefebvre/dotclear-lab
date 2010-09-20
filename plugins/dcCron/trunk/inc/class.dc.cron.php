<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcCron, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class dcCron
{
	protected $tasks;
	protected $lock_dir = null;
	protected $lock_file_prefix = 'dccron';

	/**
	Class constructor. Sets new dcCron object
	
	@param	core		dcCore
	*/
	public function __construct($core)
	{
		$this->core =& $core;
		$this->tasks = $core->blog->settings->dccron->dccron_tasks != '' ? unserialize($core->blog->settings->dccron->dccron_tasks) : array();
		
		# --BEHAVIOR-- coreCronConstruct
		$this->core->callBehavior('coreCronConstruct',$this);
	}

	/**
	Checks if it need to run saved tasks
	*/
	public function check()
	{
		$now = time();
		
		foreach ($this->tasks as $k => $v) {
			if (
				(($v['last_run'] !== null && $now >= $v['last_run'] + $v['interval']) ||
				($v['last_run'] === null && $now >= $v['first_run'])) &&
				(integer) $v['status'] === 1
			) {
				try {
					# --BEHAVIOR-- coreCronBeforeExecute
					$this->core->callBehavior('coreCronBeforeExecute',$k,$v,$now);
					
					if (!is_callable($v['callback'])) {
						$cur = $this->core->con->openCursor($this->core->prefix.'log');
						$cur->log_table = 'dcCron';
						$cur->log_msg = sprintf(__('[%s] Impossible to execute task: Callback not available'),$k);
						$this->core->log->addLog($cur);
					}
					else {
						$this->getLock($k);
						
						if (($e = call_user_func($v['callback'],$k)) === false) {
							$cur = $this->core->con->openCursor($this->core->prefix.'log');
							$cur->log_table = 'dcCron';
							$cur->log_msg = sprintf(__('[%s] Impossible to execute task: %s'),$k,$e);
							$this->core->log->addLog($cur);
						}
						else {
							if ((integer) $v['interval'] === 0) {
								$this->del($k);
							}
							else {
								$this->tasks[$k]['last_run'] = $now;
							}
							$this->delLock($k);
							$this->save();
							
							# --BEHAVIOR-- coreCronAfterExecute
							$this->core->callBehavior('coreCronAfterExecute',$k,$e,$now);
						}
					}
				} catch (Exception $e) {
					$this->tasks[$k]['status'] = (integer) -1;
					$cur = $this->core->con->openCursor($this->core->prefix.'log');
					$cur->log_table = 'dcCron';
					$cur->log_msg = sprintf($e->getMessage());
					$this->core->log->addLog($cur);
				}
			}
		}
	}

	/**
	Adds or edits task set by nid, time interval and callback function.
	
	@param	nid			<b>string</b>		Task id
	@param	interval		<b>int</b>		Interval between two execution
	@param	callback		<b>array</b>		Valid callback
	@param	first_run		<b>int</b>		Timestamp of first run
	*/
	public function put($nid,$interval,$callback,$first_run = null)
	{
		$now = time();
		$tz = dt::getTimeOffset($this->core->blog->settings->system->blog_timezone);
		
		if (!preg_match('#^[a-zA-Z0-9\_\-]*$#',$nid)) {
			throw new Exception(__('[dcCron] Provide a valid id. Should be composed by [a-zA-Z0-9_-] characters'));
		}
		if (!is_numeric($interval)) {
			throw new Exception(__('[dcCron] Provide a valid interval. Should be a number'));
		}
		if (!is_array($callback) || !is_callable($callback) || is_object($callback[0])) {
			throw new Exception(__('[dcCron] Provide a valid callback. Should be a static method'));
		}
		if ($first_run === false) {
			throw new Exception(__('[dcCron] Provide a valid date for the first execution'));
		}
		
		if ($first_run === null) {
			$first_run = array_key_exists($nid,$this->tasks) ? $this->tasks[$nid]['first_run'] : $now + $tz;
		}
		
		$first_run = array_key_exists($nid,$this->tasks) ? $first_run : $first_run - $tz;
		
		if (!array_key_exists($nid,$this->tasks) && $first_run < $now) {
			throw new Exception(__('[dcCron] Date of the first execution must be higher than now'));
		}
		
		$last_run = array_key_exists($nid,$this->tasks) ? $this->tasks[$nid]['last_run'] : null;
		
		$status = array_key_exists($nid,$this->tasks) ? $this->tasks[$nid]['status'] : 1;
		
		$this->tasks[$nid] = array(
			'id' => $nid,
			'interval' => $interval,
			'first_run' => $first_run,
			'last_run' => $last_run,
			'callback' => $callback,
			'status' => $status
		);
		
		$this->save();
	}

	/**
	Deletes tasks by nid. Returns true if it's ok, false if not
	
	@param	nid		<b>string|array</b>		Tasks id
	*/
	public function del($nid)
	{
		if (is_string($nid)) {
			$nid = array($nid);
		}
		elseif (!is_array($nid)) {
			throw new Exception(__('[dcCron] Impossible to delete task: Invalid id format'));
		}
		elseif (count($nid) === 0) {
			throw new Exception(__('[dcCron] No task specified to delete'));
		}

		foreach ($nid as $k => $v) {
			if (array_key_exists($v,$this->tasks)) {
				unset($this->tasks[$v]);
			}
			else {
				throw new Exception(sprintf(__('[dcCron] Impossible to delete task: %s. It does not exist'),$v));
			}
		}
		
		$this->save();
	}

	/**
	Enables task
	
	@param	nid		<b>string|array</b>		Tasks id
	*/
	public function enable($nid)
	{
		if (is_string($nid)) {
			$nid = array($nid);
		}
		elseif (!is_array($nid)) {
			throw new Exception(__('[dcCron] Impossible to enable task: Invalid id format'));
		}
		elseif (count($nid) === 0) {
			throw new Exception(__('[dcCron] No task specified to delete'));
		}
		
		foreach ($nid as $v) {
			if (array_key_exists($v,$this->tasks)) {
				$this->tasks[$v]['status'] = 1;
			}
			else {
				throw new Exception(sprintf(__('[dcCron] Impossible to enable task: %s. It does not exist'),$v));
			}
		}
		
		$this->save();
	}

	/**
	Disables task
	
	@param	nid		<b>string|array</b>		Tasks id
	*/
	public function disable($nid)
	{
		if (is_string($nid)) {
			$nid = array($nid);
		}
		elseif (!is_array($nid)) {
			throw new Exception(__('[dcCron] Impossible to disable task: Invalid id format'));
		}
		elseif (count($nid) === 0) {
			throw new Exception(__('[dcCron] No task specified to delete'));
		}
		
		foreach ($nid as $v) {
			if (array_key_exists($v,$this->tasks)) {
				$this->tasks[$v]['status'] = 0;
			}
			else {
				throw new Exception(sprintf(__('[dcCron] Impossible to disable task: %s. It does not exist'),$v));
			}
		}
		
		$this->save();
	}

	/**
	Retrieves tasks
	
	@param	params		<b>array</b>		Parameters
	
	@return	array
	*/
	public function getTasks($params = array())
	{
		$res = array();
		
		if (isset($params['status'])) {
			foreach ($this->tasks as $k => $v) {
				if ($v['status'] === (integer) $params['status']) {
					$res[$k] = $v;
				}
			}
		}
		else {
			$res = $this->tasks;
		}
		
		return $res;
	}
	
	/**
	Returns true if task called by nid exists. if not, returns false
	
	@param	nid		<b>string</b>		Task id
	
	@return	boolean
	*/
	public function taskExists($nid)
	{
		return array_key_exists($nid,$this->tasks) ? true : false;
	}

	/**
	Returns task interval called by nid. if not, returns false
	
	@param	nid		<b>string</b>		Task id
	
	@return	mixed
	*/
	public function getTaskInterval($nid)
	{
		return array_key_exists($nid,$this->tasks) ? $this->tasks[$nid]['interval'] : false;
	}

	/**
	Returns next run date called by nid. if not, returns false
	
	@param	nid		<b>string</b>		Task id
	
	@return	mixed
	*/
	public function getNextRunDate($nid)
	{
		return array_key_exists($nid,$this->tasks) ? $this->tasks[$nid]['last_run'] + $this->tasks[$nid]['interval'] : false;
	}

	/**
	Returns released time called by nid. if not, returns false
	
	@param	nid		<b>string</b>		Task id
	
	@return	mixed
	*/
	public function getRemainingTime($nid)
	{
		$now = time();
		$tz = dt::getTimeOffset($this->core->blog->settings->system->blog_timezone);
		
		$remaining = $this->getNextRunDate($nid) - $now + $tz;
		
		return array_key_exists($nid,$this->tasks) ? $remaining : false;
	}

	/**
	Saves tasks array on blog settings
	*/
	private function save()
	{
		$this->core->blog->settings->dccron->put('dccron_tasks',serialize($this->tasks),'string');
	}
	
	/**
	Returns a readable interval
	
	@param	interval		<b>int</b>		Interval between two execution
	
	@return	string
	*/
	public static function getInterval($interval)
	{
		if ((integer) $interval === 0) {
			return __('None');
		}
		
		$res = array();
		
		$weeks = ($interval/(3600*24*7))%(3600*24*7);
		if ($weeks > 0) {
			$res[] = sprintf('%s %s',$weeks,($weeks == 1 ? __('week') : __('weeks')));
			$interval = $interval - $weeks*3600*24*7;
		}
		$days = ($interval/(3600*24))%(3600*24);
		if ($days > 0) {
			$res[] = sprintf('%s %s',$days,($days == 1 ? __('day') : __('days')));
			$interval = $interval - $days*3600*24;
		}
		$hours = ($interval/3600)%3600;
		if ($hours > 0) {
			$res[] = sprintf('%s %s',$hours,($hours == 1 ? __('hour') : __('hours')));
			$interval = $interval - $hours*3600;
		}
		$minutes = ($interval/60)%60;
		if ($minutes > 0) {
			$res[] = sprintf('%s %s',$minutes,($minutes == 1 ? __('minute') : __('minutes')));
			$interval = $interval - $minutes*60;
		}
		if ($interval > 0) {
			$res[] = sprintf('%s %s',$interval,($interval == 1 ? __('seconde') : __('secondes')));
		}
		
		return implode(' - ',$res);
	}
	
	/**
	Sets lock directory
	
	@param	dir		<b>strong</b>		Lock directory
	*/
	public function setLockDir($dir)
	{
		if (!is_dir($dir)) {
			throw new Exception($dir.' is not a valid directory.');
		}
		
		if (!is_writable($dir)) {
			throw new Exception($dir.' is not writable.');
		}
		
		$this->lock_dir = path::real($dir).'/';
	}
	
	public function unlockTasks($task)
	{
		if (is_array($task)) {
			foreach ($task as $v) {
				$this->delLock($v);
			}
		}
		if (is_string($task)) {
			$this->delLock($task);
		}
	}
	
	/**
	Tries to acquire a lockfile.
	
	Attempts to create a new lock file for a given time, or to renew
	an expired one.
	
	Returns true when a fresh lock has been acquired, false otherwise.
	
	@param	lock		<b>string</b>		Filename
	
	@return	boolean
	*/
	private function getLock($task)
	{
		$lock = $this->getLockFileName($task);
		
		if (file_exists($lock)) {
			throw new Exception(sprintf(__('[%s] Task already in progress or locked'),$task));
		}
		
		files::makeDir(dirname($lock),true);
		if (!@file_put_contents($lock,'LOCK',LOCK_EX)) {
			throw new Exception(sprintf(__('[%s] Impossible to get lock'),$task));
		}
		
		return true;
	}
	
	private function delLock($task)
	{
		return (boolean)@unlink($this->getLockFileName($task));
	}
	
	private function getLockFileName($task)
	{
		if ($this->lock_dir === null) {
			$this->setLockDir(DC_TPL_CACHE);
		}
		
		$task_md5 = md5($task);
		
		return sprintf('%s/%s/%s/%s/%s.lock',
			$this->lock_dir,
			$this->lock_file_prefix,
			substr($task_md5,0,2),
			substr($task_md5,2,2),
			$task_md5
		);
	}
}

?>