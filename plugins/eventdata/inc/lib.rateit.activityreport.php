<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of eventdata, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

# This file is used with plugin activityReport
$core->activityReport->addGroup('eventdata',__('Plugin eventdata'));

# from BEHAVIOR eventdataAfterSet in rateit/inc/class.dc.eventdata.php
$core->activityReport->addAction(
	'eventdata',
	'set',
	__('event creation'),
	__('New event of type "%s" from "%s" to "%s" at "%s" was set on post "%s"'),
	'eventdataAfterSet',
	array('eventdataActivityReportBehaviors','eventdataSet')
);

# from BEHAVIOR eventdataAfterDelete in rateit/inc/class.dc.eventdata.php
$core->activityReport->addAction(
	'eventdata',
	'delete',
	__('event deletion'),
	__('Some events have been deleted from post "%s"'),
	'eventdataAfterDelete',
	array('eventdataActivityReportBehaviors','eventdataDelete')
);

# from BEHAVIOR eventdataAfterUpdate in rateit/inc/class.dc.eventdata.php
$core->activityReport->addAction(
	'eventdata',
	'update',
	__('updating event'),
	__('Event of type "%s" from "%s" to "%s" at "%s" has been updated'),
	'eventdataAfterUpdate',
	array('eventdataActivityReportBehaviors','eventdataUpdate')
);

class eventdataActivityReportBehaviors
{
	public static function eventdataSet($cur)
	{
		$logs = array(
			$cur->eventdata_type,
			$cur->eventdata_start,
			$cur->eventdata_end,
			$cur->eventdata_location,
			$cur->post_id
		);
		$GLOBALS['core']->activityReport->addLog('eventdata','set',$logs);
	}
	public static function eventdataDelete($type,$post_id,$start,$end,$location)
	{
		$logs = array(
			$post_id
		);
		$GLOBALS['core']->activityReport->addLog('eventdata','delete',$logs);
	}
	public static function eventdataUpdate($type,$post_id,$start,$end,$location,$new_start,$new_end,$new_location)
	{
		$logs = array(
			$type,
			$start,
			$end,
			$location
		);
		$GLOBALS['core']->activityReport->addLog('eventdata','update',$logs);
	}
}
?>