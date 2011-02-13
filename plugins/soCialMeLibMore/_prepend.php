<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of soCialMeLibMore, a plugin for Dotclear 2.
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

# Add to soCial Sharer
//b
$__autoload['beboMoreSoCialMeSharerService'] = dirname(__FILE__).'/inc/lib.social.sharer.srv.more.php';
$core->addBehavior('soCialMeSharerService',create_function(null,'return array("bebo","beboMoreSoCialMeSharerService");'));
$__autoload['blinklistMoreSoCialMeSharerService'] = dirname(__FILE__).'/inc/lib.social.sharer.srv.more.php';
$core->addBehavior('soCialMeSharerService',create_function(null,'return array("blinklist","blinklistMoreSoCialMeSharerService");'));
$__autoload['blogmarksMoreSoCialMeSharerService'] = dirname(__FILE__).'/inc/lib.social.sharer.srv.more.php';
$core->addBehavior('soCialMeSharerService',create_function(null,'return array("blogmarks","blogmarksMoreSoCialMeSharerService");'));
$__autoload['bloglinesMoreSoCialMeSharerService'] = dirname(__FILE__).'/inc/lib.social.sharer.srv.more.php';
$core->addBehavior('soCialMeSharerService',create_function(null,'return array("bloglines","bloglinesMoreSoCialMeSharerService");'));
//d
$__autoload['deliciousMoreSoCialMeSharerService'] = dirname(__FILE__).'/inc/lib.social.sharer.srv.more.php';
$core->addBehavior('soCialMeSharerService',create_function(null,'return array("delicious","deliciousMoreSoCialMeSharerService");'));
$__autoload['diggMoreSoCialMeSharerService'] = dirname(__FILE__).'/inc/lib.social.sharer.srv.more.php';
$core->addBehavior('soCialMeSharerService',create_function(null,'return array("digg","diggMoreSoCialMeSharerService");'));
$__autoload['diigoMoreSoCialMeSharerService'] = dirname(__FILE__).'/inc/lib.social.sharer.srv.more.php';
$core->addBehavior('soCialMeSharerService',create_function(null,'return array("diigo","diigoMoreSoCialMeSharerService");'));
$__autoload['dzoneMoreSoCialMeSharerService'] = dirname(__FILE__).'/inc/lib.social.sharer.srv.more.php';
$core->addBehavior('soCialMeSharerService',create_function(null,'return array("dzone","dzoneMoreSoCialMeSharerService");'));
//e
$__autoload['emailMoreSoCialMeSharerService'] = dirname(__FILE__).'/inc/lib.social.sharer.srv.more.php';
$core->addBehavior('soCialMeSharerService',create_function(null,'return array("email","emailMoreSoCialMeSharerService");'));
//f
$__autoload['favoritesMoreSoCialMeSharerService'] = dirname(__FILE__).'/inc/lib.social.sharer.srv.more.php';
$core->addBehavior('soCialMeSharerService',create_function(null,'return array("favorites","favoritesMoreSoCialMeSharerService");'));
$__autoload['farkMoreSoCialMeSharerService'] = dirname(__FILE__).'/inc/lib.social.sharer.srv.more.php';
$core->addBehavior('soCialMeSharerService',create_function(null,'return array("fark","farkMoreSoCialMeSharerService");'));
$__autoload['flattrMoreSoCialMeSharerService'] = dirname(__FILE__).'/inc/lib.social.sharer.srv.more.php';
$core->addBehavior('soCialMeSharerService',create_function(null,'return array("flattr","flattrMoreSoCialMeSharerService");'));
$__autoload['friendfeedMoreSoCialMeSharerService'] = dirname(__FILE__).'/inc/lib.social.sharer.srv.more.php';
$core->addBehavior('soCialMeSharerService',create_function(null,'return array("friendfeed","friendfeedMoreSoCialMeSharerService");'));
//g
$__autoload['googlebookmarksMoreSoCialMeSharerService'] = dirname(__FILE__).'/inc/lib.social.sharer.srv.more.php';
$core->addBehavior('soCialMeSharerService',create_function(null,'return array("googlebookmarks","googlebookmarksMoreSoCialMeSharerService");'));
$__autoload['googlebuzzMoreSoCialMeSharerService'] = dirname(__FILE__).'/inc/lib.social.sharer.srv.more.php';
$core->addBehavior('soCialMeSharerService',create_function(null,'return array("googlebuzz","googlebuzzMoreSoCialMeSharerService");'));
//r
$__autoload['redditMoreSoCialMeSharerService'] = dirname(__FILE__).'/inc/lib.social.sharer.srv.more.php';
$core->addBehavior('soCialMeSharerService',create_function(null,'return array("reddit","redditMoreSoCialMeSharerService");'));

# Add to soCial Profil
//d
$__autoload['deliciousMoreSoCialMeProfilService'] = dirname(__FILE__).'/inc/lib.social.profil.srv.more.php';
$core->addBehavior('soCialMeProfilService',create_function(null,'return array("delicious","deliciousMoreSoCialMeProfilService");'));
//f
$__autoload['feedburnerMoreSoCialMeProfilService'] = dirname(__FILE__).'/inc/lib.social.profil.srv.more.php';
$core->addBehavior('soCialMeProfilService',create_function(null,'return array("feedburner","feedburnerMoreSoCialMeProfilService");'));
$__autoload['flickrMoreSoCialMeProfilService'] = dirname(__FILE__).'/inc/lib.social.profil.srv.more.php';
$core->addBehavior('soCialMeProfilService',create_function(null,'return array("flickr","flickrMoreSoCialMeProfilService");'));
//n
$__autoload['netvibesMoreSoCialMeProfilService'] = dirname(__FILE__).'/inc/lib.social.profil.srv.more.php';
$core->addBehavior('soCialMeProfilService',create_function(null,'return array("netvibes","netvibesMoreSoCialMeProfilService");'));

# Add to soCial Reader
//f
$__autoload['feedMoreSoCialMeReaderService'] = dirname(__FILE__).'/inc/lib.social.reader.srv.more.php';
$core->addBehavior('soCialMeReaderService',create_function(null,'return array("feed","feedMoreSoCialMeReaderService");'));


//ajouter RSS, flattr, sharethis...
?>