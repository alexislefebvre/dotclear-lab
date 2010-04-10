<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of periodical, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

global $__autoload, $core;

# DB class
$__autoload['periodical'] = dirname(__FILE__).'/inc/class.periodical.php';
# Admin list and pagers
$__autoload['adminPeriodicalList'] = dirname(__FILE__).'/inc/lib.index.pager.php';
# DC 2.1.6 vs 2.2 settings
function periodicalSettings($core) {
	if (!version_compare(DC_VERSION,'2.1.6','<=')) { 
		$core->blog->settings->addNamespace('periodical'); 
		$s =& $core->blog->settings->periodical; 
	} else { 
		$core->blog->settings->setNamespace('periodical'); 
		$s =& $core->blog->settings; 
	}
	return $s;
}
?>