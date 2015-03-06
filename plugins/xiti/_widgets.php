<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of xiti, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2015 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}
$core->addBehavior('initWidgets',array('xitiWidget','xitiAdmin'));

class xitiWidget
{
	public static function xitiAdmin($w)
	{
		$w->create('xiti',__('XITI'),array('xitiPublic','xitiWidget'),
			null,
			__('Adds the button Xiti Service'));
		$w->xiti->setting('title',__('Title:'),__('XITI'),'text');
    $w->xiti->setting('content_only',__('Content only'),0,'check');
    $w->xiti->setting('class',__('CSS class:'),'');
		$w->xiti->setting('offline',__('Offline'),0,'check');
	}
}