<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcCron, a plugin for Dotclear.
# 
# Copyright (c) 2008 Tomtom
# http://blog.zesntyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$__autoload['dcCron'] = dirname(__FILE__).'/inc/class.dc.cron.php';
$__autoload['dcCronBehaviors'] = dirname(__FILE__).'/inc/class.dc.cron.behaviors.php';
$__autoload['dcCronList'] = dirname(__FILE__).'/inc/class.dc.cron.list.php';

$core->addBehavior('publicAfterDocument',array('dcCronBehaviors','run'));

$core->blog->dcCron = new dcCron($core);

?>