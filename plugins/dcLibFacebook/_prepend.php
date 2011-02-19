<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibFacebook, a plugin for Dotclear 2.
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

$__autoload['facebookUtils'] = dirname(__FILE__).'/inc/lib.facebook.utils.php';

# Add to plugin oAuthManager
$__autoload['oAuthClient20Facebook'] = dirname(__FILE__).'/inc/lib.oauth.client.2.0.facebook.php';
$core->addBehavior('oAuthManagerClientLoader',array('dcLibFacebookBehaviors','oAuthClient'));

# Add to plugin soCial
$__autoload['fblikeSoCialMeSharerService'] = dirname(__FILE__).'/inc/lib.social.sharer.srv.facebook.php';
$core->addBehavior('soCialMeSharerService',array('dcLibFacebookBehaviors','soCialMeSharer'));
$__autoload['facebookSoCialMeWriterService'] = dirname(__FILE__).'/inc/lib.social.writer.srv.facebook.php';
$core->addBehavior('soCialMeWriterService',array('dcLibFacebookBehaviors','soCialMeWriter'));
$__autoload['facebookSoCialMeProfilService'] = dirname(__FILE__).'/inc/lib.social.profil.srv.facebook.php';
$core->addBehavior('soCialMeProfilService',array('dcLibFacebookBehaviors','soCialMeProfil'));
$__autoload['facebookSoCialMeReaderService'] = dirname(__FILE__).'/inc/lib.social.reader.srv.facebook.php';
$core->addBehavior('soCialMeReaderService',array('dcLibFacebookBehaviors','soCialMeReader'));

class dcLibFacebookBehaviors
{
	public static function oAuthClient()
	{
		return array(
			'oauth_version' => '2.0',
			'loader' => 'oAuthClient20Facebook',
			'id' => 'facebook',
			'name' => 'Facebook',
			'url' => 'http://facebook.com'
		);
	}
	
	public static function soCialMeSharer()
	{
		return array('fblike','fblikeSoCialMeSharerService');
	}
	
	public static function soCialMeWriter()
	{
		return array('facebook','facebookSoCialMeWriterService');
	}
	
	public static function soCialMeProfil()
	{
		return array('facebook','facebookSoCialMeProfilService');
	}
	
	public static function soCialMeReader()
	{
		return array('facebook','facebookSoCialMeReaderService');
	}
}
?>