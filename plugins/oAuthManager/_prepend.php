<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of oAuthManager, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')){return;}# main class$__autoload['oAuthManager'] = dirname(__FILE__).'/inc/class.oauth.manager.php';
$__autoload['oAuthClient'] = dirname(__FILE__).'/inc/class.oauth.client.php';
# oAuth 1.0a libraries$__autoload['oAuthClient10'] = dirname(__FILE__).'/inc/lib.oauth.client.1.0.php';$__autoload['oAuthClient10Store'] = dirname(__FILE__).'/inc/lib.oauth.client.1.0.store.php';
# Use a generic script to deal with oAuth 1.0a# Taken from http://code.google.com/p/oauth/
# Use oAuthManager libraries rather than directly this one
# as this script can be removed/changed at any timerequire_once dirname(__FILE__).'/inc/OAuth/OAuth.php';?>