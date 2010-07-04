<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of doTwit, a plugin for Dotclear.
#
# Copyright (c) 2007 Valentin VAN MEEUWEN
# <adresse email>
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) { return; }

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/admin');

$core->addBehavior('initWidgets',array('doTwitBehaviors','initWidgets'));

class doTwitBehaviors
{
	public static function initWidgets($w)
    {
		global $core;
		$w->create('dotwit',__('doTwit'),array('doTwit','dotwitWidget'));
		$w->dotwit->setting('title',__('Title (optional):'),'');
		$w->dotwit->setting('idTwitter',__('Twitter Id:'),'');
		$w->dotwit->setting('pwdTwitter',__('Twitter password:'),'');
		$w->dotwit->setting('limit',__('Twits limit:'),1);
		$w->dotwit->setting('homeonly',__('Home page only'),1,'check');
		$w->dotwit->setting('timeline_friends',__('Timeline friends'),1,'check');
		$w->dotwit->setting('display_timeout',__('Display timeout'),1,'check');
		$w->dotwit->setting('display_profil_image',__('Display avatars'),1,'check');
		$w->dotwit->setting('css',__('Copy the file dotwit.css in the folder of your theme if you want to modify it.'),1,'check');
	}
}

$core->addBehavior('publicHeadContent',array('doTwitPublic','publicHeadContent'));

class doTwitPublic
{
	public static function publicHeadContent($core)
    {

    $url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));

		echo
		'<style type="text/css">'."\n".
		'@import url('.$url.'/css/dotwit.css);'."\n".
		"</style>\n"."\n";
		
		$theme_url=$core->blog->settings->system->themes_url."/".$core->blog->settings->system->theme;
		
		echo
		'<style type="text/css">'."\n".
		'@import url('.$theme_url.'/dotwit.css);'."\n".
		"</style>\n"."\n";
		}
}
		
?>
