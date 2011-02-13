<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibTwitter, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

# Utils
$__autoload['twitterUtils'] = dirname(__FILE__).'/inc/lib.twitter.utils.php';

# Add to plugin oAuthManager
$__autoload['oAuthClient10Twitter'] = dirname(__FILE__).'/inc/lib.oauth.client.1.0.twitter.php';
$core->addBehavior('oAuthManagerClientLoader',array('dcLibTwitterBehaviors','oAuthClient'));

# Add to plugin soCial
$__autoload['twitterSoCialMeSharerService'] = dirname(__FILE__).'/inc/lib.social.sharer.srv.twitter.php';
$core->addBehavior('soCialMeSharerService',array('dcLibTwitterBehaviors','soCialMeSharer'));
$__autoload['twitterSoCialMeProfilService'] = dirname(__FILE__).'/inc/lib.social.profil.srv.twitter.php';
$core->addBehavior('soCialMeProfilService',array('dcLibTwitterBehaviors','soCialMeProfil'));
$__autoload['twitterSoCialMeWriterService'] = dirname(__FILE__).'/inc/lib.social.writer.srv.twitter.php';
$core->addBehavior('soCialMeWriterService',array('dcLibTwitterBehaviors','soCialMeWriter'));
$__autoload['twitterSoCialMeReaderService'] = dirname(__FILE__).'/inc/lib.social.reader.srv.twitter.php';
$core->addBehavior('soCialMeReaderService',array('dcLibTwitterBehaviors','soCialMeReader'));

class dcLibTwitterBehaviors
{
	public static function oAuthClient()
	{
		return array(
			'oauth_version' => '1.0',
			'loader' => 'oAuthClient10Twitter',
			'id' => 'twitter',
			'name' => 'Twitter',
			'url' => 'http://twitter.com'
		);
	}
	
	public static function soCialMeSharer()
	{
		return array('twitter','twitterSoCialMeSharerService');
	}
	
	public static function soCialMeProfil()
	{
		return array('twitter','twitterSoCialMeProfilService');
	}
	
	public static function soCialMeWriter()
	{
		return array('twitter','twitterSoCialMeWriterService');
	}
	
	public static function soCialMeReader()
	{
		return array('twitter','twitterSoCialMeReaderService');
	}
}

# Add to optionsForComment
$__autoload['ofcTwitter'] = dirname(__FILE__).'/inc/lib.ofc.twitter.php';
$__autoload['libOfcTwitter'] = dirname(__FILE__).'/inc/lib.ofc.twitter.php';
$__autoload['urlOfcTwitter'] = dirname(__FILE__).'/inc/lib.ofc.twitter.php';
$core->url->register('ofcTwitter','ofctl','^ofctl$',array('urlOfcTwitter','login'));
$core->addBehavior('optionsForCommentAdminPrepend',array('ofcTwitter','optionsForCommentAdminPrepend'));
$core->addBehavior('optionsForCommentAdminHeader',array('ofcTwitter','optionsForCommentAdminHeader'));
$core->addBehavior('optionsForCommentPublicPrepend',array('ofcTwitter','optionsForCommentPublicPrepend'));
$core->addBehavior('optionsForCommentPublicCreate',array('ofcTwitter','optionsForCommentPublicCreate'));
$core->addBehavior('optionsForCommentPublicHead',array('ofcTwitter','optionsForCommentPublicHead'));
$core->addBehavior('optionsForCommentAdminForm',array('ofcTwitter','optionsForCommentAdminForm'));
$core->addBehavior('optionsForCommentPublicForm',array('ofcTwitter','optionsForCommentPublicForm'));
$core->addBehavior('noodlesNoodleImageInfo',array('libOfcTwitter','noodlesNoodleImageInfo'));
?>