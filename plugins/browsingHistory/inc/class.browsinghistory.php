<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of browsingHistory, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class browsingHistory
{
	private $core;
	private $sess;
	private $types;
	private $history;
	private $mem_time;
	
	public function __construct($core)
	{
		$this->core = $core;
		$this->core->blog->settings->addNamespace('browsingHistory');
		
		$mem_time = abs((integer) $this->core->blog->settings->browsingHistory->mem_time);
		if (!$mem_time) $mem_time = 604800;
		$this->mem_time = $mem_time;
		
		$types = new ArrayObject();
		
		# --BEHAVIOR-- publicBrowsingHistoryList
		$core->callbehavior('publicBrowsingHistoryList',$types);
		
		$types['post'] = 'browsingHistoryPost';
		$types['tag'] = 'browsingHistoryTag';
		
		$this->types = $types->getArrayCopy();
	}
	
	public function isType($type,$func='name')
	{
		return isset($this->types[$type]) 
		 && is_callable(array($this->types[$type],$func));
	}
	
	public function getTypes()
	{
		return $this->types;
	}
	
	# max len = 39
	private function getIP()
	{
		$ip = http::realIP();
		if (!$ip) {
			return md5(http::browserUID(DC_MASTER_KEY));
		}
		return $ip;
	}
	
	public function getHistory($reverse=false)
	{
		$params = array();
		$params['log_table'] = 'browsingHistory';
		$params['log_ip'] = $this->getIP();

		if ($reverse)
		{
			$params['order'] = 'log_dt ASC';
		}
		
		return $this->core->log->getLogs($params);
	}
	
	public function getHistoryRecords($params=array())
	{
		$reverse = false;
		if (isset($params['sort']) && preg_match('#^asc$#i',$params['sort'],$sort)) {
			$reverse = true;
		}
		
		$logs = $this->getHistory($reverse);
		
		$limit = 0;
		if (isset($params['limit'])) {
			$limit = abs((integer) $params['limit']);
		}
		
		$types = array();
		if (isset($params['type']))
		{
			if (is_array($params['type']) && !empty($params['type'])) {
				$types = $params['type'];
			} elseif ($params['type'] != '') {
				$types[] = $params['type'];
			}
		}
		
		$i = 1;
		$data = array();
		while($logs->fetch())
		{
			$log = @unserialize($logs->log_msg);
			if (!is_array($log)) continue;
			
			if (!empty($types) && !in_array($log['type'],$types)) continue;
			
			$record = $this->getRecord($log['type'],$log['id']);
			$record['log_dt'] = $logs->log_dt;
			$data[] = $record;
			
			$i++;
			if ($limit && $limit < $i) break;
		}
		
		$rs = staticRecord::newFromArray($data);
		$rs->core = $this->core;
		$rs->extend('rsExtBrowsingHistoryRecords');
		
		return $rs;
	}
	
	public function addHistory($type,$id)
	{
		$log_msg = serialize(array('type'=>$type,'id'=>$id));
		
		$this->core->con->execute(
			'DELETE FROM '.$this->core->prefix.'log '.
			"WHERE log_table='browsingHistory' ".
			"AND blog_id='".$this->core->con->escape($this->core->blog->id)."' ".
			"AND log_ip='".$this->core->con->escape($this->getIP())."' ".
			"AND log_msg='".$this->core->con->escape($log_msg)."' "
		);
		
		$this->cleanHistory();
		
		$cur = $this->core->con->openCursor($this->core->prefix.'log');
		$cur->log_table = 'browsingHistory';
		$cur->log_msg = $log_msg;
		$cur->log_ip = $this->getIP();
		$this->core->log->addLog($cur);
		
		$this->core->blog->triggerBlog();
	}
	
	protected function cleanHistory()
	{
		$now = date('Y-m-d H:i:s',time() - $this->mem_time);
		$this->core->con->execute(
			'DELETE FROM '.$this->core->prefix.'log '.
			"WHERE log_table = 'browsingHistory' ".
			"AND log_dt < TIMESTAMP '".$now."' "
		);
	}
	
	public function getTitle($type)
	{
		return $this->callType('title',$type);
	}
	
	public function getName($type)
	{
		return $this->callType('name',$type);
	}
	
	public function getRecord($type,$id)
	{
		return $this->callType('record',$type,$this->core,$id);
	}
	
	private function callType()
	{
		$args = func_get_args();
		$func = array_shift($args);
		$type = array_shift($args);
		
		if (!$this->isType($type)) return;
		
		return call_user_func_array(array($this->types[$type],$func),$args);
	}
}

class rsExtBrowsingHistoryRecords
{
	public static function getTS($rs)
	{
		return strtotime($rs->log_dt);
	}
	
	public static function getISO8601Date($rs)
	{
		$tz = $rs->core->blog->settings->system->blog_timezone;
		return dt::iso8601($rs->getTS()+dt::getTimeOffset($tz),$tz);
	}
	
	public static function getRFC822Date($rs)
	{
		$tz = $rs->core->blog->settings->system->blog_timezone;
		return dt::rfc822($rs->getTS()+dt::getTimeOffset($tz),$tz);
	}
	
	public static function getDate($rs,$format)
	{
		$tz = $rs->core->blog->settings->system->blog_timezone;
		if (!$format) {
			$format = $rs->core->blog->settings->system->date_format;
		}
		
		return dt::dt2str($format,$rs->log_dt,$tz);
	}
	
	public static function getTime($rs,$format)
	{
		$tz = $rs->core->blog->settings->system->blog_timezone;
		if (!$format) {
			$format = $rs->core->blog->settings->system->time_format;
		}
		
		return dt::dt2str($format,$rs->log_dt,$tz);
	}
}

__('Event');
__('Poll');
__('Page');
__('Entry');
__('Gallery');
__('Image');

class browsingHistoryPost
{
	public static $named_types = array(
		'eventhandler' => 'Event',
		'pollsfactory' => 'Poll',
		'pages' => 'Page',
		'post' => 'Entry',
		'gal' => 'Gallery',
		'galitem' => 'Image'
	);
	
	public static function title()
	{
		return __('Show entries');
	}
	
	public static function name()
	{
		return __('Entry');
	}
	
	public static function record($core,$id)
	{
		$params = array('post_id'=>$id,'post_type'=>'','limit'=>1);
		$rs = $core->blog->getPosts($params);
		
		if ($rs->isEmpty()) return '';
		
		return array(
			'type' => self::namedPostType($rs->post_type),
			'title' => html::escapeHTML($rs->post_title),
			'text' => $rs->isExtended() ? $rs->getExcerpt() : $rs->getContent(),
			'url' => $rs->getURL(),
			'link' => '<a href="'.$rs->getURL().'">'.html::escapeHTML($rs->post_title).'</a>',
			'firstimage' => self::firstImage($rs->post_id)
		);
	}
	
	# First post image helpers
	public static function firstImage($post_id,$size='sq',$class="")
	{
		if (!preg_match('/^sq|t|s|m|o$/',$size)) {
			$size = 's';
		}
		
		global $core;
		
		$p_url = $core->blog->settings->system->public_url;
		$p_site = preg_replace('#^(.+?//.+?)/(.*)$#','$1',$core->blog->url);
		$p_root = $core->blog->public_path;
		
		$pattern = '(?:'.preg_quote($p_site,'/').')?'.preg_quote($p_url,'/');
		$pattern = sprintf('/<img.+?src="%s(.*?\.(?:jpg|gif|png))"[^>]+/msu',$pattern);
		
		$src = '';
		$alt = '';
		
		$post_id = abs((integer) $post_id);
		$posts = $core->blog->getPosts(array('post_id'=>$post_id,'post_type'=>'','limit'=>1));
		
		# We first look in post content
		if (!$posts->isEmpty())
		{
			$subject = $posts->post_excerpt_xhtml.$posts->post_content_xhtml.$posts->cat_desc;
			if (preg_match_all($pattern,$subject,$m) > 0)
			{
				foreach ($m[1] as $i => $img) {
					if (($src = self::ContentFirstImageLookup($p_root,$img,$size)) !== false) {
						$src = $p_url.(dirname($img) != '/' ? dirname($img) : '').'/'.$src;
						if (preg_match('/alt="([^"]+)"/',$m[0][$i],$malt)) {
							$alt = $malt[1];
						}
						break;
					}
				}
			}
		}
		
		if ($src) {
			return '<img alt="'.$alt.'" src="'.$src.'" class="'.$class.'" />';
		}
		return '';
	}
	
	private static function ContentFirstImageLookup($root,$img,$size)
	{
		# Get base name and extension
		$info = path::info($img);
		$base = $info['base'];
		
		if (preg_match('/^\.(.+)_(sq|t|s|m)$/',$base,$m)) {
			$base = $m[1];
		}
		
		$res = false;
		if ($size != 'o' && file_exists($root.'/'.$info['dirname'].'/.'.$base.'_'.$size.'.jpg'))
		{
			$res = '.'.$base.'_'.$size.'.jpg';
		}
		else
		{
			$f = $root.'/'.$info['dirname'].'/'.$base;
			if (file_exists($f.'.'.$info['extension'])) {
				$res = $base.'.'.$info['extension'];
			} elseif (file_exists($f.'.jpg')) {
				$res = $base.'.jpg';
			} elseif (file_exists($f.'.png')) {
				$res = $base.'.png';
			} elseif (file_exists($f.'.gif')) {
				$res = $base.'.gif';
			}
		}
		
		if ($res) {
			return $res;
		}
		return false;
	}
	
	protected static function namedPostType($type)
	{
		global $core;
		
		# default know types
		$types = self::$named_types;
		foreach($types as $k => $v) {
			$types[$k] = __($v);
		}
		
		# plugin muppet types
		if ($core->plugins->moduleExists('muppet')) {
			$muppet_types = muppet::getPostTypes();
			if(is_array($muppet_types) && !empty($muppet_types)) {
			
				foreach($muppet_types as $k => $v) {
					$types[$k] = $v['name'];
				}
			}
		}
		
		return isset($types[$type]) ? $types[$type] : __('Unknow');
	}
}

class browsingHistoryTag
{
	public static function title()
	{
		return __('Show tags');
	}
	
	public static function name()
	{
		return __('Tag');
	}
	
	public static function record($core,$id)
	{
		$params = array('meta_id'=>$id,'meta_type'=>'tag','limit'=>1);
		$rs = $core->meta->getMetadata($params);
		
		if ($rs->isEmpty()) return '';
		
		$url = $core->blog->url.$core->url->getBase('tag').'/'.$rs->meta_id;
		
		return array(
			'type' => self::name(),
			'title' => html::escapeHTML($rs->meta_id),
			'text' => '',
			'url' => $url,
			'link' => '<a href="'.$url.'">'.html::escapeHTML($rs->meta_id).'</a>',
			'firstimage' => ''
		);
	}
}
?>