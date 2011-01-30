<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of joliprint, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

global $__autoload, $core;

$__autoload['joliprint'] = dirname(__FILE__).'/inc/class.joliprint.php';

$core->url->register('joliprint','joliprint','^joliprint/(.+)$',array('joliprintUrl','joliprint'));

# Add to SocCial Sharer
$__autoload['joliprintSoCialMeSharerService'] = dirname(__FILE__).'/inc/lib.social.sharer.srv.joliprint.php';
$core->addBehavior('soCialMeSharerService',create_function(null,'return array("joliprint","joliprintSoCialMeSharerService");'));
?>