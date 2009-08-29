<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of eventdata, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class dcEventdata
{
	public $core;
	private $con;
	private $table;

	public function __construct($core)
	{
		$this->core =& $core;
		$this->con =& $this->core->con;
		$this->table = $this->core->prefix.'eventdata';
	}

	public static function serializeURL($type='',$post='',$start='',$end='',$location='')
	{
		return urlencode(serialize(array('type'=>$type,'post'=>$post,'start'=>$start,'end'=>$end,'location'=>$location)));
	}

	public static function unserializeURL($url='')
	{
		$rs = @unserialize(urldecode($url));
		return array(
			'type' => (isset($rs['type']) ? $rs['type'] : ''),
			'post' => (isset($rs['post']) ? $rs['post'] : null),
			'start' => (isset($rs['start']) ? $rs['start'] : ''),
			'end' => (isset($rs['end']) ? $rs['end'] : ''),
			'location' => (isset($rs['location']) ? $rs['location'] : '')
		);
	}

	public function getEventdata($type=null,$limit=null,$eventdata_start=null,$eventdata_end=null,$post_id=null,$period=null,$sort='desc')
	{
		$strReq = 'SELECT P.post_id AS post_id, eventdata_start, eventdata_end, eventdata_type, eventdata_location, COUNT(EV.post_id) as count '.
		'FROM '.$this->table.' EV LEFT JOIN '.$this->core->prefix.'post P '.
		'ON EV.post_id = P.post_id '.
		"WHERE P.blog_id = '".$this->con->escape($this->core->blog->id)."' ";

		if ($type !== null)
			$strReq .= "AND eventdata_type = '".$this->con->escape($type)."' ";

		switch ($period) {
			case 'ongoing':
				$op = array('<','>'); break;
			case 'outgoing':
				$op = array('>','<'); break;
			case 'notstarted':
				$op = array('>','!'); break;
			case 'scheduled':
				$op = array('>','!'); break;
			case 'started':
				$op = array('','!'); break;
			case 'notfinished':
				$op = array('!','>'); break;
			case 'finished':
				$op = array('!','<'); break;
			default:
				$op = array('=','='); break;
		}

		if ($eventdata_start !== null && $op[0] != '!')
			$strReq .= "AND eventdata_start ".$op[0]." TIMESTAMP '".$this->con->escape($eventdata_start)."' ";
		
		if ($eventdata_end !== null && $op[1] != '!')
			$strReq .= "AND eventdata_end ".$op[1]." TIMESTAMP '".$this->con->escape($eventdata_end)."' ";

		if ($post_id !== null)
			$strReq .= 'AND P.post_id = '.(integer) $post_id.' ';
		
		if (!$this->core->auth->check('contentadmin',$this->core->blog->id)) {
			$strReq .= 'AND ((post_status = 1 ';
			
			if ($this->core->blog->without_password)
				$strReq .= 'AND post_password IS NULL ';

			$strReq .= ') ';

			if ($this->core->auth->userID())
				$strReq .= "OR P.user_id = '".$this->con->escape($this->core->auth->userID())."' ";

				$strReq .= ') ';
		}
		$sort = strtoupper($sort) == 'ASC' ? 'ASC' : 'DESC';
		$strReq .= 'GROUP BY eventdata_start,eventdata_end,eventdata_type,eventdata_location,P.post_id,P.blog_id ORDER BY eventdata_start '.$sort.' ';

		if ($limit !== null)
			$strReq .= $this->con->limit($limit);

		$rs = $this->con->select($strReq);
		$rs = $rs->toStatic();
		
		while ($rs->fetch()) {
			$rs->set('start_ts',strtotime($rs->eventdata_start));
			$rs->set('end_ts',strtotime($rs->eventdata_end));
			$rs->set('start_ym',date('Ym',strtotime($rs->eventdata_start)));
			$rs->set('end_ym',date('Ym',strtotime($rs->eventdata_end)));
			$rs->set('duration_ts',(strtotime($rs->eventdata_end) - strtotime($rs->eventdata_start)));
		}

		return $rs;
	}

	public function getPostsByEventdata($params=array(),$count_only=false)
	{
		if (!isset($params['columns'])) $params['columns'] = array();
		$params['columns'][] = 'EV.eventdata_start';
		$params['columns'][] = 'EV.eventdata_end';
		$params['columns'][] = 'EV.eventdata_location';

		if (!isset($params['from'])) $params['from'] = '';
		$params['from'] .= ' LEFT OUTER JOIN '.$this->table.' EV ON P.post_id = EV.post_id ';

		if (!isset($params['sql'])) $params['sql'] = '';

		if (isset($params['period'])) {

			$ts_format = '%Y-%m-%d %H:%M:%S';
			$ts_start = $ts_end = "TIMESTAMP '".dt::str($ts_format)."'";

			if (!empty($params['eventdata_start']))
				$ts_start = "TIMESTAMP '".dt::str($ts_format,strtotime($params['eventdata_start']))."'";

			if (!empty($params['eventdata_end']))
				$ts_end = "TIMESTAMP '".dt::str($ts_format,strtotime($params['eventdata_end']))."'";

			$start = 'EV.eventdata_start';
			$end = 'EV.eventdata_end';

			switch($params['period']) {
				case 'ongoing':
				$params['sql'] .= 'AND '.$start.' < '.$ts_start.' '.
					' AND '.$end.' > '.$ts_end.' '; break;
				case 'outgoing':
				$params['sql'] .= 'AND '.$start.' > '.$ts_start.' '.
					' AND '.$end.' < '.$ts_end.' '; break;
				case 'notstarted':
				$params['sql'] .= 'AND '.$start.' > '.$ts_start.' '; break;
				case 'scheduled':
				$params['sql'] .= 'AND '.$start.' > '.$ts_start.' '; break;
				case 'started':
				$params['sql'] .= 'AND '.$start.' < '.$ts_start.' '; break;
				case 'notfinished':
				$params['sql'] .= 'AND '.$end.' > '.$ts_end.' '; break;
				case 'finished':
				$params['sql'] .= 'AND '.$end.' < '.$ts_end.' '; break;
			}
		}
		if (!empty($params['eventdata_type'])) {
			$params['sql'] .= "AND EV.eventdata_type = '".$this->con->escape($params['eventdata_type'])."' ";
			unset($params['eventdata_type']);
		}
		if (!isset($params['period']) && !empty($params['eventdata_start'])) {
			$params['sql'] .= "AND EV.eventdata_start = '".$this->con->escape($params['eventdata_start'])."' ";
			unset($params['eventdata_start']);
		}
		if (!isset($params['period']) && !empty($params['eventdata_end'])) {
			$params['sql'] .= "AND EV.eventdata_end = '".$this->con->escape($params['eventdata_end'])."' ";
			unset($params['eventdata_end']);
		}
		if (!empty($params['eventdata_location'])) {
			$params['sql'] .= "AND EV.eventdata_location = '".$this->con->escape($params['eventdata_location'])."' ";
			unset($params['eventdata_location']);
		}
		unset($params['period'],$start,$end,$ts_start,$ts_end,$ts_format,$ts_now);

		# Metadata
		if (isset($params['meta_id'])) {
			$params['from'] .= ', '.$this->core->prefix.'meta META ';
			$params['sql'] .= 'AND META.post_id = P.post_id ';
			$params['sql'] .= "AND META.meta_id = '".$this->con->escape($params['meta_id'])."' ";
			
			if (!empty($params['meta_type'])) {
				$params['sql'] .= "AND META.meta_type = '".$this->con->escape($params['meta_type'])."' ";
				unset($params['meta_type']);
			}
			unset($params['meta_id']);		
		}

		return $this->core->blog->getPosts($params,$count_only);
	}

	public function countEventOfDay($y,$m,$d)
	{
		$ts_start = sprintf('%4d-%02d-%02d 00:00:00',$y,$m,$d);
		$ts_end = sprintf('%4d-%02d-%02d 23:59:59',$y,$m,$d);

		$rs = $this->getEventdata('eventdata',null,$ts_start,$ts_end,null,'ongoing');

		if ($rs->isEmpty())
			return 0;

		$total = 0;
		while($rs->fetch()) {
			$total += $rs->count;
		}
		return $total;
	}

	public function setEventdata($type,$post_id,$start,$end,$location='')
	{
		$post_id = (integer) $post_id;

		$cur = $this->con->openCursor($this->table);

		$cur->post_id = (integer) $post_id;
		$cur->eventdata_type = (string) $type;
		$cur->eventdata_start = (string) $start;
		$cur->eventdata_end = (string) $end;
		$cur->eventdata_location = (string) $location;

		$cur->insert();
	}

	public function updEventdata($type,$post_id,$start,$end,$location,$new_start,$new_end,$new_location)
	{
		$post = null !== $post_id ? "post_id = '".$this->con->escape($post_id)."' AND " : '';

		$strReq = 'UPDATE '.$this->table.' SET '.
				"eventdata_start = '".$this->con->escape($new_start)."', ".
				"eventdata_end = '".$this->con->escape($new_end)."', ".
				"eventdata_location = '".$this->con->escape($new_location)."' ".
				'WHERE '.$post.
				"eventdata_start = '".$this->con->escape($start)."' AND ".
				"eventdata_end = '".$this->con->escape($end)."' AND ".
				"eventdata_location = '".$this->con->escape($location)."' ";

		$this->con->execute($strReq);
	}

	public function delEventdata($type,$post_id,$start=null,$end=null,$location=null)
	{
		$post_id = (integer) $post_id;

		$strReq = 'DELETE FROM '.$this->table.' '.
				'WHERE post_id = '.$post_id.' '.
				"AND eventdata_type = '".$this->con->escape($type)."' ";

		if ($start !== null) $strReq .= "AND eventdata_start = '".$this->con->escape($start)."' ";
		if ($end !== null) $strReq .= "AND eventdata_end = '".$this->con->escape($end)."' ";
		if ($location !== null) $strReq .= "AND eventdata_location = '".$this->con->escape($location)."' ";

		$this->con->execute($strReq);
	}
}	
?>