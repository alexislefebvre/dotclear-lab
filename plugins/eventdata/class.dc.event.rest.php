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

class dcEventRest
{
	public static function getEvent(&$core,$get)
	{
		$postId = !empty($get['postId']) ? $get['postId'] : null;
		$eventType = !empty($get['eventType']) ? $get['eventType'] : null;
		$eventStart = !empty($get['eventStart']) ? $get['eventStart'] : null;
		$eventEnd = !empty($get['eventEnd']) ? $get['eventEnd'] : null;
		$eventPeriod = !empty($get['eventPeriod']) ? $get['eventPeriod'] : null;

		$limit = !empty($get['limit']) ? $get['limit'] : null;
		$sortby = !empty($get['sortby']) ? $get['sortby'] : 'event_start,desc';

		$event = new dcEvent($core);
		$rs = $event->getEvent($eventType,$limit,$eventStart,$eventEnd,$postId,$eventPeriod);

		$sortby = explode(',',$sortby);
		$sort = $sortby[0];
		$order = isset($sortby[1]) ? $sortby[1] : 'desc';

		switch ($sort) {
			case 'event_start':
				$sort = 'start_ts'; break;
			case 'event_end':
				$sort = 'end_ts'; break;
			default:
				$sort = 'event_start';
		}

		$rs->sort($sort,$order);
		
		$rsp = new xmlTag();
		
		while ($rs->fetch())
		{
			$xe = new xmlTag('event');
			$xe->post = $rs->post_id;
			$xe->type = $rs->event_type;
			$xe->count = $rs->count;
			$xe->start = $rs->event_start;
			$xe->end = $rs->event_end;
			$rsp->insertNode($xe);
		}

		return $rsp;
	}
	
	public static function setEvent(&$core,$get,$post)
	{
		if (empty($post['postId'])) throw new Exception('No post ID');
		if (empty($post['eventType'])) throw new Exception('No event type');
		if (empty($post['eventStart'])) throw new Exception('No event start date');
		if (empty($post['eventEnd'])) throw new Exception('No event end date');

		$event = new dcEvent($core);
		$event->setEvent($post['eventType'],$post['postId'],$post['eventStart'],$post['eventEnd'],);

		return true;
	}
	
	public static function delEvent(&$core,$get,$post)
	{
		if (empty($post['postId'])) throw new Exception('No post ID');
		if (empty($post['eventType'])) throw new Exception('No event type');

		$start = empty($post['eventStart']) ? null : $post['eventStart'];
		$end = empty($post['eventEnd']) ? null : $post['eventEnd'];

		$event = new dcEvent($core);
		$event->delEvent($post['eventType'],$post['postId'],$start,$end);

		return true;
	}
}
?>