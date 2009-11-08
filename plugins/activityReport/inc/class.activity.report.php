<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of activityReport, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class activityReport
{
	public $core;
	public $con;

	public $mailer = 'noreply@dotclearemailactivityreport.dc';

	private $ns = 'activityReport';
	private $_global = false;
	private $blog = null;
	private $table = '';
	private $groups = array();
	private $settings = array();

	public function __construct($core,$ns='activityReport')
	{
		$this->core =& $core;
		$this->con = $core->con;
		$this->table = $core->prefix.'activity';
		$this->blog = $core->con->escape($core->blog->id);
		$this->ns = $core->con->escape($ns);

		$this->getSettings();
	}

	public function setGlobal()
	{
		$this->_global = true;
	}

	public function unsetGlobal()
	{
		$this->_global = false;
	}

	public function getGroups($group=null,$action=null)
	{
		if ($action !== null)
		{
			return isset($this->groups[$group]['actions'][$action]) ? 
				$this->groups[$group]['actions'][$action] : null;
		}
		elseif ($group !== null)
		{
			return isset($this->groups[$group]) ? 
				$this->groups[$group] : null;
		}
		else
		{
			return $this->groups;
		}
	}

	public function addGroup($group,$title)
	{
		$this->groups[$group] = array(
			'title' => $title,
			'actions'=>array()
		);
		return true;
	}

	public function addAction($group,$action,$title,$msg,$behavior,$function)
	{
		if (!isset($this->groups[$group])) return false;

		$this->groups[$group]['actions'][$action] = array(
			'title' => $title,
			'msg' => $msg
		);
		$this->core->addBehavior($behavior,$function);
		return true;
	}

	private function getSettings()
	{
		$settings = array();

		$settings['active'] = false;
		$settings['dashboardItem'] = false;
		$settings['interval'] = 86400;
		$settings['lastreport'] = 0;
		$settings['mailinglist'] = array();
		$settings['requests'] = array();
		$settings['blogs'] = array();

		$this->settings = $this->_global_settings = $settings;

		$rs = $this->con->select(
			'SELECT setting_id, setting_value, blog_id '.
			'FROM '.$this->table.'_setting '.
			"WHERE setting_type='".$this->ns."' ".
			"AND (blog_id='".$this->blog."' OR blog_id IS NULL) ".
			'ORDER BY setting_id DESC '
		);

		while($rs->fetch())
		{
			$k = $rs->f('setting_id');
			$v = $rs->f('setting_value');
			$b = $rs->f('blog_id');

			if (isset($settings[$k]))
			{
				if ($b === null)
				{
					$this->_global_settings[$k] = self::decode($v);
				}
				else
				{
					$this->settings[$k] = self::decode($v);
				}
			}
		}
		# Force blog
		$this->settings['blogs'] = array(1=>$this->blog);
	}

	public function getSetting($n)
	{
		if ($this->_global && isset($this->_global_settings[$n]))
		{
			return $this->_global_settings[$n];
		}
		elseif (!$this->_global && isset($this->settings[$n]))
		{
			return $this->settings[$n];
		}

		return null;
	}

	public function setSetting($n,$v)
	{
		if ($this->_global && !isset($this->_global_settings[$n])
		|| !$this->_global && !isset($this->settings[$n])) return null;

		$c = $this->delSetting($n);

		$cur = $this->con->openCursor($this->table.'_setting');
		$this->con->writeLock($this->table.'_setting');

		$cur->blog_id = $this->_global ? null : $this->blog;
		$cur->setting_id = $this->con->escape($n);
		$cur->setting_type = $this->ns;
		$cur->setting_value = (string) self::encode($v);

		$cur->insert();
		$this->con->unlock();

		if ($this->_global)
		{
			$this->_global_settings[$n] = $v;
		}
		else
		{
			$this->settings[$n] = $v;
		}

		return true;
	}

	private function delSetting($n)
	{
		return $this->con->execute(
			'DELETE FROM '.$this->table.'_setting '.
			"WHERE blog_id".($this->_global ? ' IS NULL' : "='".$this->blog."'").' '.
			"AND setting_id='".$this->con->escape($n)."' ".
			"AND setting_type='".$this->ns."' "
		);
	}

	# Action params to put in params['sql']
	public static function requests2params($requests)
	{
		$r = array();
		foreach($requests as $group => $actions)
		{
			foreach($actions as $action => $is)
			{
				$r[] = "activity_group='".$group."' AND activity_action='".$action."' ";
			}
		}
		return empty($r) ? '' : 'AND ('.implode('OR ',$r).') ';
	}

	public function getLogs($p,$count_only=false)
	{
		if ($count_only)
		{
			$r = 'SELECT count(E.activity_id) ';
		}
		else
		{
			$content_r = empty($p['no_content']) ? 'activity_logs, ' : '';

			if (!empty($params['columns']) && is_array($params['columns']))
			{
				$content_r .= implode(', ',$params['columns']).', ';
			}
			
			$r =
			'SELECT E.activity_id, E.blog_id, '.$content_r.
			'E.activity_group, E.activity_action, E.activity_dt, '.
			'E.activity_blog_status, E.activity_super_status ';
		}

		$r .= 'FROM '.$this->table.' E  ';
		
		if (!empty($p['from']))
		{
			$r .= $p['from'].' ';
		}

		if ($this->_global)
		{
			$r .= "WHERE E.activity_super_status = 0 ";
		}
		else
		{
			$r .= "WHERE E.activity_blog_status = 0 ";
		}

		if (!empty($p['activity_type']))
		{
			$r .= "AND E.activity_type = '".$this->con->escape($p['activity_type'])."' ";
		}
		else
		{
			$r .= "AND E.activity_type = '".$this->ns."' ";
		}

		if (!empty($p['blog_id']))
		{
			if(is_array($p['blog_id']))
			{
				$r .= 'AND E.blog_id'.$this->con->in($p['blog_id']);
			}
			else
			{
				$r .= "AND E.blog_id = '".$this->con->escape($p['blog_id'])."' ";
			}
		}
		elseif($this->_global)
		{
			$r .= 'AND E.blog_id IS NOT NULL ';
		}
		else
		{
			$r .= "AND E.blog_id='".$this->blog."' ";
		}

		if (isset($p['activity_group']))
		{
			if (is_array($p['activity_group']) && !empty($p['activity_group']))
			{
				$r .= 'AND E.activity_group '.$this->con->in($p['activity_group']);
			}
			elseif ($p['activity_group'] != '')
			{
				$r .= "AND E.activity_group = '".$this->con->escape($p['activity_group'])."' ";
			}
		}

		if (isset($p['activity_action']))
		{
			if (is_array($p['activity_action']) && !empty($p['activity_action']))
			{
				$r .= 'AND E.activity_action '.$this->con->in($p['activity_action']);
			}
			elseif ($p['activity_action'] != '')
			{
				$r .= "AND E.activity_action = '".$this->con->escape($p['activity_action'])."' ";
			}
		}

		if (isset($p['activity_blog_status']))
		{
			$r .= "AND E.activity_blog_status = ".((integer) $p['activity_blog_status'])." ";
		}

		if (isset($p['activity_super_status']))
		{
			$r .= "AND E.activity_super_status = ".((integer) $p['activity_super_status'])." ";
		}

		if (isset($p['from_date_ts']))
		{
			$dt = date('Y-m-d H:i:s',$p['from_date_ts']);
			$r .= "AND E.activity_dt >= TIMESTAMP '".$dt."' ";
		}
		if (isset($p['to_date_ts']))
		{
			$dt = date('Y-m-d H:i:s',$p['to_date_ts']);
			$r .= "AND E.activity_dt < TIMESTAMP '".$dt."' ";
		}

		if (!empty($p['sql']))
		{
			$r .= $p['sql'].' ';
		}

		if (!$count_only)
		{
			if (!empty($p['order']))
			{
				$r .= 'ORDER BY '.$this->con->escape($p['order']).' ';
			} else {
				$r .= 'ORDER BY E.activity_dt DESC ';
			}
		}

		if (!$count_only && !empty($p['limit']))
		{
			$r .= $this->con->limit($p['limit']);
		}

		return $this->con->select($r);
	}

	public function addLog($group,$action,$logs)
	{
		try
		{
			$cur = $this->con->openCursor($this->table);
			$this->con->writeLock($this->table);

			$cur->activity_id = 	$this->getNextId();
			$cur->activity_type = 	$this->ns;
			$cur->blog_id = 		$this->blog;
			$cur->activity_group = 	$this->con->escape((string) $group);
			$cur->activity_action = $this->con->escape((string) $action);
			$cur->activity_logs = 	self::encode($logs);
			$cur->activity_dt = 	date('Y-m-d H:i:s');

			$cur->insert();
			$this->con->unlock();
		}
		catch (Exception $e) {
			$this->con->unlock();
			$this->core->error->add($e->getMessage());
		}

		# Test if email report is needed
		$this->needReport();
	}

	private function parseLogs($rs)
	{
		if ($rs->isEmpty()) return '';

		$from = time();
		$to = 0;
		$res = $blog = $group = '';

		while($rs->fetch())
		{
			# Blog
			if ($rs->blog_id != $blog && $this->_global)
			{
				$blog = $rs->blog_id;
				$group = '';
				$res .= "\n--- ".sprintf(__('On blog "%s"'),$blog)." ---\n";
			}

			# Type
			if ($rs->activity_group != $group)
			{
				$group = $rs->activity_group;
				$res .= "\n-- ".__($this->groups[$group]['title'])." --\n\n";
			}

			# Action
			$data = self::decode($rs->activity_logs);

			$res .= 
			'- '.$rs->activity_dt.' : '.
			vsprintf(
				__($this->groups[$group]['actions'][$rs->activity_action]['msg']),$data
			)."\n";

			# Period
			if (strtotime($rs->activity_dt) < $from)
			{
				$from = strtotime($rs->activity_dt);
			}
			if (strtotime($rs->activity_dt) > $to)
			{
				$to = strtotime($rs->activity_dt);
			}
		}

		# Top of msg
		if (!empty($res))
		{
			if ($this->_global)
			{
				$period = sprintf(
					__('Period: from %s to %s'),
					dt::str('%Y-%m-%d %H:%M:%S',$from),
					dt::str('%Y-%m-%d %H:%M:%S',$to)
				)."\n";
			}
			else
			{
				$period = 
				sprintf(__('Blog: %s'),$this->core->blog->name)."\n".
				sprintf(__('Website: %s'),$this->core->blog->url)."\n".
				sprintf(
					__('Period: from %s to %s'),
					dt::str('%Y-%m-%d %H:%M:%S',
						$from,
						$this->core->blog->settings->blog_timezone
					),
					dt::str(
						'%Y-%m-%d %H:%M:%S',
						$to,
						$this->core->blog->settings->blog_timezone
					)
				)."\n";
			}
			$res = $period.
			"\n-----------------------------------------------------------\n".
			$res;
		}
		return $res;
	}

	private function cleanLogs()
	{
		$this->con->execute(
			'DELETE FROM '.$this->table.' '.
			"WHERE activity_type='".$this->ns."' ".
			"AND activity_blog_status = 1 ".
			"AND activity_super_status = 1 "
		);
	}

	public function deleteLogs()
	{
		if (!$this->core->auth->isSuperAdmin()) return;

		return $this->con->execute(
			'DELETE FROM '.$this->table.' '.
			"WHERE activity_type='".$this->ns."' "
		);
	}

	private function updateStatus($from_date_ts,$to_date_ts)
	{
		$r = 
		'UPDATE '.$this->table.' ';

		if ($this->_global)
		{
			$r .= 
			"SET activity_super_status = 1 WHERE blog_id IS NOT NULL ";
		}
		else
		{
			$r .= 
			"SET activity_blog_status = 1 WHERE blog_id = '".$this->blog."' ";
		}

		$r .=
		"AND activity_type = '".$this->ns."' ".
		"AND activity_dt >= TIMESTAMP '".date('Y-m-d H:i:s',$from_date_ts)."' ".
		"AND activity_dt < TIMESTAMP '".date('Y-m-d H:i:s',$to_date_ts)."' ";

		$this->con->execute($r);
	}

	public function getNextId()
	{
		return $this->con->select(
			'SELECT MAX(activity_id) FROM '.$this->table
		)->f(0) + 1;
	}

	public function needReport($force=false)
	{
		$now = time();

		if ($this->_global)
		{
			$active = (boolean) $this->_global_settings['active'];
			$mailinglist = $this->_global_settings['mailinglist'];
			$requests = $this->_global_settings['requests'];
			$lastreport = (integer) $this->_global_settings['lastreport'];
			$interval = (integer) $this->_global_settings['interval'];
			$blogs = $this->_global_settings['blogs'];
		}
		else
		{
			$active = (boolean) $this->settings['active'];
			$mailinglist = $this->settings['mailinglist'];
			$requests = $this->settings['requests'];
			$lastreport = (integer) $this->settings['lastreport'];
			$interval = (integer) $this->settings['interval'];
			$blogs = $this->blog; // force local blog
		}

		if ($force)
		{
			$lastreport = 0;
		}

		# Check if report is needed
		if ($active && !empty($mailinglist) && !empty($requests) && !empty($blogs) 
		&& ($lastreport + $interval) < $now )
		{
			# Get datas
			$params = array();
			$params['from_date_ts'] = $lastreport;
			$params['to_date_ts'] = $now;
			$params['blog_id'] = $blogs;
			$params['sql'] = self::requests2params($requests);
			$params['order'] = 'blog_id ASC, activity_group ASC, activity_action ASC ';

			$send = false;
			$logs = $this->getLogs($params);
			if (!$logs->isEmpty())
			{
				# Datas to readable text
				$content = $this->parseLogs($logs);
				if (!empty($content))
				{
					# Send mails
					$send = $this->sendReport($mailinglist,$content);
				}
			}

			# Update db
			if ($send || $this->_global) // if global : delete all blog logs even if not selected
			{
				# Update log status
				$this->updateStatus($lastreport,$now);
				# Delete old logs
				$this->cleanLogs();
				# Then set update time
				$this->setSetting('lastreport',$now);
			}
		}

		# If this is on a blog, we need to test superAdmin report
		if (!$this->_global)
		{
			$this->_global = true;
			$this->needReport();
			$this->_global = false;
		}

		return true;
	}

	private function sendReport($recipients,$content)
	{
		if (!is_array($recipients) || empty($content) || !text::isEmail($this->mailer)) return;

		# Checks recipients addresses
		$rc2 = array();
		foreach ($recipients as $v)
		{
			$v = trim($v);
			if (!empty($v) && text::isEmail($v))
			{
				$rc2[] = $v;
			}
		}
		$recipients = $rc2;
		unset($rc2);

		if (empty($recipients)) return;

		# Sending mail
		$headers = array(
			'From: '.mail::B64Header(__('Activity report module')).' <'.$this->mailer.'>',
			'Content-Type: text/plain; charset=UTF-8;',
			'X-Originating-IP: '.http::realIP(),
			'X-Mailer: Dotclear',
			'X-Blog-Id: '.mail::B64Header($this->core->blog->id),
			'X-Blog-Name: '.mail::B64Header($this->core->blog->name),
			'X-Blog-Url: '.mail::B64Header($this->core->blog->url)
		);

		$subject = $this->_global ?
			mail::B64Header(__('Blog activity report')) :
			mail::B64Header('['.$this->core->blog->name.'] '.__('Blog activity report'));

		$msg = 
		__("You received a message from your blog's activity report module.").
		"\n\n".$content."\n\n";

		foreach ($recipients as $email)
		{
			try
			{
				mail::sendMail($email,$subject,$msg,$headers);
				return true;
			}
			catch (Exception $e) {
				// don't break other codes leave it silently
				//$core->error->add(__('Failed to send email notification'));
				return false;
			}
		}
	}
	
	public function getUserCode()
	{
		$code =
		pack('a32',$this->core->auth->userID()).
		pack('H*',crypt::hmac(DC_MASTER_KEY,$this->core->auth->getInfo('user_pwd')));
		return bin2hex($code);
	}
	
	public function checkUserCode($code)
	{
		$code = pack('H*',$code);
		
		$user_id = trim(@pack('a32',substr($code,0,32)));
		$pwd = @unpack('H40hex',substr($code,32,40));
		
		if ($user_id === false || $pwd === false) {
			return false;
		}
		
		$pwd = $pwd['hex'];
		
		$strReq = 'SELECT user_id, user_pwd '.
				'FROM '.$this->core->prefix.'user '.
				"WHERE user_id = '".$this->core->con->escape($user_id)."' ";
		
		$rs = $this->core->con->select($strReq);
		
		if ($rs->isEmpty()) {
			return false;
		}
		
		if (crypt::hmac(DC_MASTER_KEY,$rs->user_pwd) != $pwd) {
			return false;
		}
		
		return $rs->user_id;
	}

	public static function encode($a)
	{
		return base64_encode(serialize($a));
	}

	public static function decode($a)
	{
		return unserialize(base64_decode($a));
	}
}
?>