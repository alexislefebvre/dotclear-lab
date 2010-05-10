<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of wikioWorld, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

global $__autoload;
$__autoload['wikioWorld'] = dirname(__FILE__).'/inc/class.wikioworld.php';

function wikioWorldSettings($core,$ns='wikioWorld') {
	if (!version_compare(DC_VERSION,'2.1.6','<=')) { 
		$core->blog->settings->addNamespace($ns); 
		return $core->blog->settings->{$ns}; 
	} else { 
		$core->blog->settings->setNamespace($ns); 
		return $core->blog->settings; 
	}
}
?>