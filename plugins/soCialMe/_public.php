<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of soCialMe, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

require_once dirname(__FILE__).'/_widgets.php';

# Public behaviors
$core->addBehavior('publicBeforeDocument',array('soCialMePublic','publicBeforeDocument'));
$core->addBehavior('publicHeadContent',array('soCialMePublic','publicHeadContent'));
$core->addBehavior('publicTopAfterContent',array('soCialMePublic','publicTopAfterContent'));
$core->addBehavior('publicEntryBeforeContent',array('soCialMePublic','publicEntryBeforeContent'));
$core->addBehavior('publicEntryAfterContent',array('soCialMePublic','publicEntryAfterContent'));
$core->addBehavior('publicFooterContent',array('soCialMePublic','publicFooterContent'));
$core->addBehavior('publicAfterDocument',array('soCialMePublic','publicAfterDocument'));
?>