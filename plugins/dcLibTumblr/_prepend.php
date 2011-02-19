<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibTumblr, a plugin for Dotclear 2.
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

# Add to SocCial Wrtier
$__autoload['tumblrSoCialMeWriterService'] = dirname(__FILE__).'/inc/lib.social.writer.srv.tumblr.php';
$core->addBehavior('soCialMeWriterService',create_function(null,'return array("tumblr","tumblrSoCialMeWriterService");'));
$__autoload['tumblrSoCialMeProfilService'] = dirname(__FILE__).'/inc/lib.social.profil.srv.tumblr.php';
$core->addBehavior('soCialMeProfilService',create_function(null,'return array("tumblr","tumblrSoCialMeProfilService");'));
?>