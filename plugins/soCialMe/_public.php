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

require_once dirname(__FILE__).'/_widgets.php';

# soCialMe Sharer
$core->addBehavior('publicHeadContent',array('soCialMeSharerPublic','publicHeadContent'));
$core->addBehavior('publicFooterContent',array('soCialMeSharerPublic','publicFooterContent'));
$core->addBehavior('publicEntryBeforeContent',array('soCialMeSharerPublic','publicEntryBeforeContent'));
$core->addBehavior('publicEntryAfterContent',array('soCialMeSharerPublic','publicEntryAfterContent'));

class soCialMeSharerPublic
{
	public static function publicHeadContent($core)
	{
		if (!$core->blog->settings->soCialMeSharer->active 
		 || !$core->blog->settings->soCialMeSharer->css)
		{
			return;
		}
		
		echo 
		"\n<!-- Style for plugin soCialMeSharer --> \n".
		'<style type="text/css">'."\n".
		html::escapeHTML($core->blog->settings->soCialMeSharer->css)."\n".
		"</style>\n";
	}
	
	public static function publicFooterContent($core)
	{
		echo soCialMeUtils::publicScripts($core,'soCialMeSharer');
	}
	
	public static function publicEntryBeforeContent($core,$_ctx)
	{
		echo soCialMeSharer::publicContent('beforepost',$core,$_ctx);
	}
	
	public static function publicEntryAfterContent($core,$_ctx)
	{
		echo soCialMeSharer::publicContent('afterpost',$core,$_ctx);
	}
}

# soCialMe Profil
$core->addBehavior('publicHeadContent',array('soCialMeProfilPublic','publicHeadContent'));
$core->addBehavior('publicTopAfterContent',array('soCialMeProfilPublic','publicTopAfterContent'));
$core->addBehavior('publicFooterContent',array('soCialMeProfilPublic','publicFooterContent'));

class soCialMeProfilPublic
{
	public static function publicHeadContent($core)
	{
		if (!$core->blog->settings->soCialMeProfil->active 
		 || !$core->blog->settings->soCialMeProfil->css)
		{
			return;
		}
		
		echo 
		"\n<!-- Style for plugin soCialMeProfil --> \n".
		'<style type="text/css">'."\n".
		html::escapeHTML($core->blog->settings->soCialMeProfil->css)."\n".
		"</style>\n";
	}
	
	public static function publicTopAfterContent($core)
	{
		echo soCialMeProfil::publicContent('ontop',$core);
	}
	
	public static function publicFooterContent($core)
	{
		echo soCialMeProfil::publicContent('onfooter',$core);
		echo soCialMeUtils::publicScripts($core,'soCialMeProfil');
	}
}

# soCialMe Reader
$core->addBehavior('publicHeadContent',array('soCialMeReaderPublic','publicHeadContent'));

class soCialMeReaderPublic
{
	public static function publicHeadContent($core)
	{
		if (!$core->blog->settings->soCialMeReader->active 
		 || !$core->blog->settings->soCialMeReader->css)
		{
			return;
		}
		
		echo 
		"\n<!-- Style for plugin soCialMeReader --> \n".
		'<style type="text/css">'."\n".
		html::escapeHTML($core->blog->settings->soCialMeReader->css)."\n".
		"</style>\n";
	}
}
?>