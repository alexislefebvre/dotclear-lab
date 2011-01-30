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

# Sharer
$core->addBehavior('initWidgets',array('soCialMeSharerWidget','soCialMeSharerPostAdmin'));

class soCialMeSharerWidget
{
	public static function soCialMeSharerPostAdmin($w)
	{
		$w->create('soCialMeSharerPost',
			__('SoCialMe sharer'),array('soCialMeSharerWidget','soCialMeSharerPostPublic')
		);
	}
	
	public static function soCialMeSharerPostPublic($w)
	{
		global $core, $_ctx;
		
		return soCialMeSharer::publicContent('onwidget',$core,$_ctx);
	}
}

# Profil
$core->addBehavior('initWidgets',array('soCialMeProfilWidget','soCialMeProfilBadgeAdmin'));

class soCialMeProfilWidget
{
	public static function soCialMeProfilBadgeAdmin($w)
	{
		$w->create('soCialMeProfilBagde',
			__('SoCialMe profil'),array('soCialMeProfilWidget','soCialMeProfilBadgePublic')
		);
		
		$so = new soCialMeProfil($GLOBALS['core']);

		$combo = array_flip($so->things());
		$combo[__('All')] = '';
		
		$w->soCialMeProfilBagde->setting('thing',__('Group'),'','combo',$combo);
	}
	
	public static function soCialMeProfilBadgePublic($w)
	{
		global $core;
		
		return soCialMeProfil::publicContent('onwidget',$core,$w->thing);
	}
}
?>