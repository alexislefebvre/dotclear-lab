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

$__autoload['dcCron'] = dirname(__FILE__).'/inc/class.dc.cron.php';
$__autoload['dcCronList'] = dirname(__FILE__).'/inc/class.dc.cron.list.php';
$__autoload['dcCronRestMethods'] = dirname(__FILE__).'/_services.php';

# Initialization of dcCron object
$core->cron = new dcCron($core);
# dcCron trigger behavior
$core->addBehavior('urlHandlerServeDocument',array('dcCronBehaviors','urlHandlerServeDocument'));
# Rest function
$core->rest->addFunction('getInterval',array('dcCronRestMethods','getInterval'));

class dcCronBehaviors
{
	/**
	Calls dcCron's check function when RSS/Atom feed is requested
	
	@param	args		<b>array</b>		Parameters
	*/
	public static function urlHandlerServeDocument($args)
	{
		if (preg_match('/(atom|rss2)/',$args['tpl'])) {
			$GLOBALS['core']->cron->check();
		}
	}
}

?>