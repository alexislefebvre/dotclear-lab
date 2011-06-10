<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of postWidgetText, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}$core->addBehavior('initWidgets',	array('postWidgetTextWidgetBehaviors','init'));class postWidgetTextWidgetBehaviors{	public static function init($w)	{		global $core;		$w->create('postwidgettext',__('Post widget text'),			array('postWidgetTextPublicBehaviors','widget'));		$w->postwidgettext->setting('title',__('Title:'),			__('More about this entry'),'text');		$w->postwidgettext->setting('excerpt',__('Use excerpt if no content'),			0,'check');		$w->postwidgettext->setting('show',__('Show widget even if empty'),			0,'check');	}}?>