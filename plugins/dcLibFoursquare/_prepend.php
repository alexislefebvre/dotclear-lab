<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibFoursquare, a plugin for Dotclear 2.
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

$__autoload['foursquareUtils'] = dirname(__FILE__).'/inc/lib.foursquare.utils.php';

# Add to plugin oAuthManager
$__autoload['oAuthClient20Foursquare'] = dirname(__FILE__).'/inc/lib.oauth.client.2.0.foursquare.php';
$core->addBehavior('oAuthManagerClientLoader',array('dcLibFoursquareBehaviors','oAuthClient'));

# Add to plugin soCial
$__autoload['foursquareSoCialMeProfilService'] = dirname(__FILE__).'/inc/lib.social.profil.srv.foursquare.php';
$core->addBehavior('soCialMeProfilService',array('dcLibFoursquareBehaviors','soCialMeProfil'));
$__autoload['foursquareSoCialMeReaderService'] = dirname(__FILE__).'/inc/lib.social.reader.srv.foursquare.php';
$core->addBehavior('soCialMeReaderService',array('dcLibFoursquareBehaviors','soCialMeReader'));

class dcLibFoursquareBehaviors
{
	public static function oAuthClient()
	{
		return array(
			'oauth_version' => '2.0',
			'loader' => 'oAuthClient20Foursquare',
			'id' => 'foursquare',
			'name' => 'Foursquare',
			'url' => 'http://foursquare.com'
		);
	}
	
	public static function soCialMeProfil()
	{
		return array('foursquare','foursquareSoCialMeProfilService');
	}
	
	public static function soCialMeReader()
	{
		return array('foursquare','foursquareSoCialMeReaderService');
	}
}
?>