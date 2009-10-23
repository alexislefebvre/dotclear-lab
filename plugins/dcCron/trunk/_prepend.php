<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcCron, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$__autoload['dcCron'] = dirname(__FILE__).'/inc/class.dc.cron.php';
$__autoload['dcCronBehaviors'] = dirname(__FILE__).'/inc/class.dc.cron.behaviors.php';
$__autoload['dcCronEnableList'] = dirname(__FILE__).'/inc/class.dc.cron.list.php';
$__autoload['dcCronDisableList'] = dirname(__FILE__).'/inc/class.dc.cron.list.php';

$core->addBehavior('publicBeforeDocument',array('dcCronBehaviors','run'));
$core->addBehavior('adminPageHTMLHead',array('dcCronBehaviors','run'));

$core->blog->dcCron = new dcCron($core);

?>