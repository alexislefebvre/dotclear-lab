<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibIdentica, a plugin for Dotclear 2.
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

# Add to plugin oAuthManager
$__autoload['oAuthClient10Identica'] = dirname(__FILE__).'/inc/lib.oauth.client.1.0.identica.php';
$core->addBehavior('oAuthManagerClientLoader',array('dcLibIdenticaBehaviors','oAuthClient'));

# Add to plugin soCial
$__autoload['identicaSoCialMeSharerService'] = dirname(__FILE__).'/inc/lib.social.sharer.srv.identica.php';
$core->addBehavior('soCialMeSharerService',array('dcLibIdenticaBehaviors','soCialMeSharer'));
$__autoload['identicaSoCialMeWriterService'] = dirname(__FILE__).'/inc/lib.social.writer.srv.identica.php';
$core->addBehavior('soCialMeWriterService',array('dcLibIdenticaBehaviors','soCialMeWriter'));

class dcLibIdenticaBehaviors
{
	public static function oAuthClient()
	{
		return array(
			'oauth_version' => '1.0',
			'loader' => 'oAuthClient10Identica',
			'id' => 'identica',
			'name' => 'Identi.ca',
			'url' => 'http://identi.ca'
		);
	}
	
	public static function soCialMeSharer()
	{
		return array('identica','identicaSoCialMeSharerService');
	}
	
	public static function soCialMeWriter()
	{
		return array('identica','identicaSoCialMeWriterService');
	}
}
?>