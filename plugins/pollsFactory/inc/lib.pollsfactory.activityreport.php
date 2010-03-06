<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of pollsFactory, a plugin for Dotclear 2.
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
$core->activityReport->addGroup('pollsfactory',__('Plugin polls factory'));

# from BEHAVIOR publicAfterAddResponse in pollsFactory/_public.php
$core->activityReport->addAction(
	'pollsfactory',
	'set',
	__('new vote'),
	__('New vote on poll called "%s" was set by user "%s"'),
	'publicAfterAddResponse',
	array('pollsFactoryActivityReportBehaviors','addResponse')
);
# from BEHAVIOR adminAfterCompletePoll in pollsFactory/inc/index.addpoll.php
$core->activityReport->addAction(
	'pollsfactory',
	'create',
	__('complete poll'),
	__('Poll called "%s" was completed by user "%s"'),
	'adminAfterCompletePoll',
	array('pollsFactoryActivityReportBehaviors','completePoll')
);

class pollsFactoryActivityReportBehaviors
{
	public static function addResponse($poll,$user_id)
	{
		$logs = array(
			$poll->post_title,
			$user_id
		);

		$GLOBALS['core']->activityReport->addLog('pollsfactory','set',$logs);
	}
	public static function completePoll($poll)
	{
		global $core;

		$logs = array(
			$poll->post_title,
			$core->auth->getInfo('user_cn')
		);

		$core->activityReport->addLog('pollsfactory','create',$logs);
	}
}
?>