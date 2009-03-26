<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of community, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$__autoload['community']				= dirname(__FILE__).'/inc/class.community.php';
$__autoload['communityBehaviros']		= dirname(__FILE__).'/inc/class.community.behaviors.php';
$__autoload['communityStandbyList']	= dirname(__FILE__).'/inc/lib.community.ui.php';
$__autoload['communityUserList']		= dirname(__FILE__).'/inc/lib.community.ui.php';
$__autoload['communityGroupList']		= dirname(__FILE__).'/inc/lib.community.ui.php';
$__autoload['mail']					= CLEARBRICKS_PATH.'/mail/class.mail.php';

?>