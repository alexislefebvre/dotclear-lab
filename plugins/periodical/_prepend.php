<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of periodical, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}
if (version_compare(str_replace("-r","-p",DC_VERSION),'2.2-alpha','<')){return;}

global $__autoload, $core;

# DB class
$__autoload['periodical'] = dirname(__FILE__).'/inc/class.periodical.php';
# Admin list and pagers
$__autoload['adminPeriodicalList'] = dirname(__FILE__).'/inc/lib.index.pager.php';

# Add to plugn soCialMe (writer part)
$__autoload['periodicalSoCialMeWriter'] = dirname(__FILE__).'/inc/lib.periodical.socialmewriter.php';
$core->addBehavior('soCialMeWriterMarker',array('periodicalSoCialMeWriter','soCialMeWriterMarker'));
$core->addBehavior('periodicalAfterPublishedPeriodicalEntry',array('periodicalSoCialMeWriter','periodicalAfterPublishedPeriodicalEntry'));

?>