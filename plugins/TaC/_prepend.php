<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of TaC, a plugin for Dotclear 2.
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

# TaC main class
$__autoload['tac'] = dirname(__FILE__).'/inc/class.tac.php';
$__autoload['tacQuick'] = dirname(__FILE__).'/inc/lib.tac.quick.php';
$__autoload['tacTools'] = dirname(__FILE__).'/inc/lib.tac.tools.php';

#OAuth class
$__autoload['OAuthException'] = dirname(__FILE__).'/inc/OAuth.php';
$__autoload['OAuthConsumer'] = dirname(__FILE__).'/inc/OAuth.php';
$__autoload['OAuthToken'] = dirname(__FILE__).'/inc/OAuth.php';
$__autoload['OAuthSignatureMethod'] = dirname(__FILE__).'/inc/OAuth.php';
$__autoload['OAuthSignatureMethod_HMAC_SHA1'] = dirname(__FILE__).'/inc/OAuth.php';
$__autoload['OAuthSignatureMethod_PLAINTEXT'] = dirname(__FILE__).'/inc/OAuth.php';
$__autoload['OAuthSignatureMethod_RSA_SHA1'] = dirname(__FILE__).'/inc/OAuth.php';
$__autoload['OAuthRequest'] = dirname(__FILE__).'/inc/OAuth.php';
$__autoload['OAuthServer'] = dirname(__FILE__).'/inc/OAuth.php';
$__autoload['OAuthDataStore'] = dirname(__FILE__).'/inc/OAuth.php';
$__autoload['OAuthUtil'] = dirname(__FILE__).'/inc/OAuth.php';

?>