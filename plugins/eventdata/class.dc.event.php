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

class dcEvent
{
	private $core;
	private $con;
	private $table;
	
	public function __construct(&$core)
	{
		$this->core =& $core;
		$this->con =& $this->core->con;
		$this->table = $this->core->prefix.'event';
	}

	public function getEvent($type=null,$limit=null,$event_start=null,$event_end=null,$post_id=null,$period=null)
	{
		$strReq = 'SELECT event_start,event_end, event_type, COUNT(EV.post_id) as count '.
		'FROM '.$this->table.' EV LEFT JOIN '.$this->core->prefix.'post P '.
		'ON EV.post_id = P.post_id '.
		"WHERE P.blog_id = '".$this->con->escape($this->core->blog->id)."' ";

		if ($type !== null)
			$strReq .= " AND event_type = '".$this->con->escape($type)."' ";

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

		if ($event_start !== null && $op[0] != '!')
			$strReq .= " AND event_start = '".$this->con->escape($event_start)."' ";
		
		if ($event_end !== null && $op[1] != '!')
			$strReq .= " AND event_end = '".$this->con->escape($event_end)."' ";
		
		if ($post_id !== null)
			$strReq .= ' AND P.post_id = '.(integer) $post_id.' ';
		
		if (!$this->core->auth->check('contentadmin',$this->core->blog->id)) {
			$strReq .= 'AND ((post_status = 1 ';
			
			if ($this->core->blog->without_password) {
				$strReq .= 'AND post_password IS NULL ';
			}
			$strReq .= ') ';

			if ($this->core->auth->userID()) {
				$strReq .= "OR P.user_id = '".$this->con->escape($this->core->auth->userID())."')";
			} else {
				$strReq .= ') ';
			}
		}
		$strReq .= 'GROUP BY event_start,event_end,event_type,P.blog_id ORDER BY event_start DESC ';

		if ($limit)
			$strReq .= $this->con->limit($limit);

		$rs = $this->con->select($strReq);
		$rs = $rs->toStatic();
		
		while ($rs->fetch()) {
			$rs->set('start_ts',strtotime($rs->event_start));
			$rs->set('end_ts',strtotime($rs->event_end));
			$rs->set('start_ym',date('Ym',strtotime($rs->event_start)));
			$rs->set('end_ym',date('Ym',strtotime($rs->event_end)));
		}

		return $rs;
	}

	public function getPostsByEvent($params=array(),$count_only=false)
	{
		if (!isset($params['columns'])) $params['columns'] = array();
		$params['columns'][] = 'EV.event_start';
		$params['columns'][] = 'EV.event_end';

		if (!isset($params['from'])) $params['from'] = '';
		$params['from'] .= ', '.$this->table.' EV ';

		if (!isset($params['sql'])) $params['sql'] = '';
		$params['sql'] .= "AND EV.post_id = P.post_id ";	

		if (isset($params['period'])) {
			switch($params['period']) {
				case 'ongoing':
				$params['sql'] .= " AND TIMESTAMP(EV.event_start) < NOW() ".
					" AND TIMESTAMP(EV.event_end) > NOW() "; break;
				case 'outgoing':
				$params['sql'] .= " AND (TIMESTAMP(EV.event_start) > NOW() ".
					" OR TIMESTAMP(EV.event_end) < NOW()) "; break;
				case 'notstarted':
				$params['sql'] .= " AND TIMESTAMP(EV.event_start) > NOW() "; break;
				case 'scheduled':
				$params['sql'] .= " AND TIMESTAMP(EV.event_start) > NOW() "; break;
				case 'started':
				$params['sql'] .= " AND TIMESTAMP(EV.event_start) < NOW() "; break;
				case 'notfinished':
				$params['sql'] .= " AND TIMESTAMP(EV.event_end) > NOW() "; break;
				case 'finished':
				$params['sql'] .= " AND TIMESTAMP(EV.event_end) < NOW() "; break;
			}
			unset($params['period']);
		}
		if (!empty($params['event_type'])) {
			$params['sql'] .= "AND EV.event_type = '".$this->con->escape($params['event_type'])."' ";
			unset($params['event_type']);
		}
		if (!empty($params['event_start'])) {
			$params['sql'] .= "AND EV.event_start = '".$this->con->escape($params['event_start'])."' ";
			unset($params['event_start']);
		}
		if (!empty($params['event_end'])) {
			$params['sql'] .= "AND EV.event_end = '".$this->con->escape($params['event_end'])."' ";
			unset($params['event_end']);
		}

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

	public function setEvent($type,$post_id,$start,$end)
	{
		$post_id = (integer) $post_id;

		$cur = $this->con->openCursor($this->table);

		$cur->post_id = (integer) $post_id;
		$cur->event_type = (string) $type;
		$cur->event_start = (string) $start;
		$cur->event_end = (string) $end;

		$cur->insert();
	}

	public function delEvent($type,$post_id,$start=null,$end=null)
	{
		$post_id = (integer) $post_id;

		$strReq = 'DELETE FROM '.$this->table.' '.
				'WHERE post_id = '.$post_id.' '.
				"AND event_type = '".$this->con->escape($type)."' ";

		if ($start !== null) $strReq .= "AND event_start = '".$this->con->escape($start)."' ";
		if ($end !== null) $strReq .= "AND event_end = '".$this->con->escape($end)."' ";

		$this->con->execute($strReq);
	}
}	
?>