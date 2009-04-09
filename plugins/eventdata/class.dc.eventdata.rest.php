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

class dcEventdataRest
{
	public static function getEventdata(&$core,$get)
	{
		$postId = !empty($get['postId']) ? $get['postId'] : null;
		$eventdataType = !empty($get['eventdataType']) ? $get['eventdataType'] : null;
		$eventdataStart = !empty($get['eventdataStart']) ? $get['eventdataStart'] : null;
		$eventdataEnd = !empty($get['eventdataEnd']) ? $get['eventdataEnd'] : null;
		$eventdataPeriod = !empty($get['eventdataPeriod']) ? $get['eventdataPeriod'] : null;

		$limit = !empty($get['limit']) ? $get['limit'] : null;
		$sortby = !empty($get['sortby']) ? $get['sortby'] : 'eventdata_start,desc';

		$eventdata = new dcEventdata($core);
		$rs = $eventdata->getEventdata($eventdataType,$limit,$eventdataStart,$eventdataEnd,$postId,$eventdataPeriod);

		$sortby = explode(',',$sortby);
		$sort = $sortby[0];
		$order = isset($sortby[1]) ? $sortby[1] : 'desc';

		switch ($sort) {
			case 'eventdata_start':
				$sort = 'start_ts'; break;
			case 'eventdata_end':
				$sort = 'end_ts'; break;
			default:
				$sort = 'eventdata_start';
		}

		$rs->sort($sort,$order);
		
		$rsp = new xmlTag();
		
		while ($rs->fetch())
		{
			$xe = new xmlTag('eventdata');
			$xe->post = $rs->post_id;
			$xe->type = $rs->eventdata_type;
			$xe->count = $rs->count;
			$xe->start = $rs->eventdata_start;
			$xe->end = $rs->eventdata_end;
			$rsp->insertNode($xe);
		}

		return $rsp;
	}
	
	public static function setEventdata(&$core,$get,$post)
	{
		if (empty($post['postId'])) throw new Exception('No post ID');
		if (empty($post['eventdataType'])) throw new Exception('No event type');
		if (empty($post['eventdataStart'])) throw new Exception('No event start date');
		if (empty($post['eventdataEnd'])) throw new Exception('No event end date');

		$eventdata = new dcEventdata($core);
		$eventdata->setEventdata($post['eventdataType'],$post['postId'],$post['eventdataStart'],$post['eventdataEnd'],);

		return true;
	}
	
	public static function delEventdata(&$core,$get,$post)
	{
		if (empty($post['postId'])) throw new Exception('No post ID');
		if (empty($post['eventdataType'])) throw new Exception('No event type');

		$start = empty($post['eventdataStart']) ? null : $post['eventdataStart'];
		$end = empty($post['eventdataEnd']) ? null : $post['eventdataEnd'];

		$eventdata = new dcEventdata($core);
		$eventdata->delEventdata($post['eventdataType'],$post['postId'],$start,$end);

		return true;
	}
}
?>