<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku , Tomtom and contributors
## Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class dcLog
{
	public function __construct($core)
	{
		$this->con =& $core->con;
		$this->prefix =& $core->prefix;
		$this->core =& $core;
	}
	
	public function getLogs($params=array(),$count_only=false)
	{
		if ($count_only)
		{
			$strReq =
			'SELECT count(log_id) '.
			'FROM '.$this->prefix.'log '.
			'WHERE NULL IS NULL ';
		}
		else
		{
			$strReq =
			'SELECT log_id , user_id , log_table ,log_dt ,log_ip,'.
			'log_msg ,blog_id '.
			'FROM '.$this->prefix.'log '.
			'WHERE NULL IS NULL ';
		}
		
		if (!empty($params['user_id'])) {
			$strReq .= "AND user_id = '".$this->con->escape($params['user_id'])."' ";
		}
		if (!empty($params['log_msg'])) {
			$strReq .= "AND log_msg = '".$this->con->escape($params['log_msg'])."' ";
		}
		
		if (!$count_only) {
			$strReq .= 'GROUP BY user_id ';
			
			if (!empty($params['order']) && !$count_only) {
				$strReq .= 'ORDER BY '.$this->con->escape($params['order']).' ';
			} else {
				$strReq .= 'ORDER BY log_id ASC ';
			}
		}
		
		$rs = $this->con->select($strReq);
		return $rs;
	}
	
	public function addLog($cur)
	{
		$this->con->writeLock($this->prefix.'log');
		try
		{
			# Get ID
			$rs = $this->con->select(
				'SELECT MAX(log_id) '.
				'FROM '.$this->prefix.'log ' 
				);
			
			$cur->log_id = (integer) $rs->f(0) + 1;
			$cur->blog_id = (string) $this->blog->core->id;
			$cur->log_dt = date('Y-m-d H:i:s');
			
			$this->getLogCursor($cur,$cur->log_id);
			
			if ($cur->log_ip === null) {
				$cur->log_ip = http::realIP();
			}
			
			# --BEHAVIOR-- coreBeforeLogCreate
			$this->core->callBehavior('coreBeforeLogCreate',$this,$cur);
			
			$cur->insert();
			$this->con->unlock();
		}
		catch (Exception $e)
		{
			$this->con->unlock();
			throw $e;
		}
	
		# --BEHAVIOR-- coreAfterLogCreate
		$this->core->callBehavior('coreAfterLogCreate',$this,$cur);
		
		return $cur->log_id;
	}
	
	public function updLog($id,$cur)
	{
		$id = (integer) $id;
		
		if (empty($id)) {
			throw new Exception(__('No such log ID'));
		}
		
		$rs = $this->getLogs(array('log_id' => $id));
		
		if ($rs->isEmpty()) {
			throw new Exception(__('No such log ID'));
		}
		
		$this->getLogCursor($cur);
		
		$offset = dt::getTimeOffset($this->core->settings->blog_timezone);
		$cur->log_dt = date('Y-m-d H:i:00',time() + $offset);
		
		# --BEHAVIOR-- coreBeforeLogUpdate
		$this->core->callBehavior('coreBeforeLogUpdate',$this,$cur,$rs);
		
		$cur->update('WHERE log_id = '.$id.' ');
		
		# --BEHAVIOR-- coreAfterLogUpdate
		$this->core->callBehavior('coreAfterLogUpdate',$this,$cur,$rs);
	}
	
	public function delLog($id)
	{
		$id = (integer) $id;
		
		if (empty($id)) {
			throw new Exception(__('No such log ID'));
		}
		
		$strReq = 'DELETE FROM '.$this->prefix.'log '.
				'WHERE log_id = '.$id.' ';
		
		$this->con->execute($strReq);
	}
	
	private function getLogCursor($cur,$log_id=null)
	{
		if ($cur->log_msg == '') {
			throw new Exception(__('No log message'));
		}
	
		if ($cur->log_table === null) {
			$cur->log_table = 'none';
		}
		
		if ($cur->log_dt == '') {
			$offset = dt::getTimeOffset($this->core->settings->blog_timezone);
			$cur->log_dt = date('Y-m-d H:i:00',time() + $offset);
		}
	}
}
?>