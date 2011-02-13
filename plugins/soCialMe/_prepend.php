<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of soCialMe, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

# Set a URL shortener for quick get request
# This can be set in dotclear/inc/config.php or other plugin
if (!defined('SHORTEN_SERVICE_NAME')) {
	define('SHORTEN_SERVICE_NAME','Is.gd');
}
if (!defined('SHORTEN_SERVICE_API')) {
	define('SHORTEN_SERVICE_API','http://is.gd/api.php?');
}
if (!defined('SHORTEN_SERVICE_BASE')) {
	define('SHORTEN_SERVICE_BASE','http://is.gd/');
}
if (!defined('SHORTEN_SERVICE_PARAM')) {
	define('SHORTEN_SERVICE_PARAM','longurl');
}
if (!defined('SHORTEN_SERVICE_ENCODE')) {
	define('SHORTEN_SERVICE_ENCODE',false);
}

# SoCial admin home news
define('SOCIALME_RSS_NEWS','http://so.cial.me/feed/category/news/atom');

# Common libraries
$__autoload['soCialMe'] = dirname(__FILE__).'/inc/lib.social.php';
$__autoload['soCialMeAdmin'] = dirname(__FILE__).'/inc/lib.admin.php';
$__autoload['soCialMeCacheDB'] = dirname(__FILE__).'/inc/lib.cache.db.php';
$__autoload['soCialMeCacheFile'] = dirname(__FILE__).'/inc/lib.cache.file.php';
$__autoload['soCialMePublic'] = dirname(__FILE__).'/inc/lib.public.php';
$__autoload['soCialMeService'] = dirname(__FILE__).'/inc/lib.service.php';
$__autoload['soCialMeTemplate'] = dirname(__FILE__).'/inc/lib.template.php';
$__autoload['soCialMeURL'] = dirname(__FILE__).'/inc/lib.url.php';
$__autoload['soCialMeUtils'] = dirname(__FILE__).'/inc/lib.utils.php';
$__autoload['soCialMeWidget'] = dirname(__FILE__).'/inc/lib.widget.php';
# profil
$__autoload['soCialMeProfil'] = dirname(__FILE__).'/inc/lib.social.profil.php';
# reader
$__autoload['soCialMeReader'] = dirname(__FILE__).'/inc/lib.social.reader.php';
$core->url->register('soCialMeReader','reader','^reader(/(.*?)|)$',array('soCialMeURL','soCialMeReaderPage'));
# sharer
$__autoload['soCialMeSharer'] = dirname(__FILE__).'/inc/lib.social.sharer.php';
# writer
$__autoload['soCialMeWriter'] = dirname(__FILE__).'/inc/lib.social.writer.php';
?>