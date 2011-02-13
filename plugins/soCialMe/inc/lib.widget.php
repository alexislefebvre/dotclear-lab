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

class soCialMeWidget
{
	public static function soCialMeWidgetAdmin($w)
	{
		# soCialMe Sharer
		$w->create('soCialMeSharerPost',
			__('Social sharer'),array('soCialMeWidget','soCialMeSharerPostPublic')
		);
		
		# soCialMe Profil
		$w->create('soCialMeProfilBagde',
			__('Social profil'),array('soCialMeWidget','soCialMeProfilBadgePublic')
		);
		
		$so = new soCialMeProfil($GLOBALS['core']);

		$combo = array_flip($so->things());
		$combo[__('All')] = '';
		
		$w->soCialMeProfilBagde->setting('thing',__('Group:'),'','combo',$combo);
		
		# soCialMe Reader
		$w->create('soCialMeReaderStream',
			__('Social reader'),array('soCialMeWidget','soCialMeReaderStreamPublic')
		);
		
		$class = new soCialMeReader($GLOBALS['core']);
		
		foreach($class->things() as $thing => $plop)
		{
			if ($thing != 'Widget') continue;
			$usable[$thing] = $class->can($thing.'Content');
		}
		$s_action = $class->fillOrder($usable,true);
		
		if (!empty($s_action['Widget']))
		{
			$services = array_intersect_key($class->services(),array_flip($s_action['Widget']));
			
			foreach($services as $service_id => $service)
			{
				$combo_service[$service->name] = $service_id;
			}
		}
		$combo_service[__('All')] = '';
		
		$combo_avatar = array(
			__('Small') => 'small',
			__('Normal') => 'normal',
			__('None') => ''
		);
		
		$w->soCialMeReaderStream->setting('title',__('Title:'),'');
		$w->soCialMeReaderStream->setting('limit',__('Limit:'),10);
		$w->soCialMeReaderStream->setting('service',__('Stream:'),'','combo',$combo_service);
		$w->soCialMeReaderStream->setting('size',__('Size of icon:'),'small','combo',$combo_avatar);
	}
	
	# soCialMe Sharer post
	public static function soCialMeSharerPostPublic($w)
	{
		global $core, $_ctx;
		
		return soCialMeSharer::publicContent('onwidget',$core,$_ctx);
	}
	
	# soCialMe Profil badge
	public static function soCialMeProfilBadgePublic($w)
	{
		global $core;
		
		return soCialMeProfil::publicContent('onwidget',$core,$w->thing);
	}
	
	# soCialMe Reader stream
	public static function soCialMeReaderStreamPublic($w)
	{
		global $core;
		
		if ($core->url->type == 'soCialMeReader') return;
		
		$params = array(
			'title' => $w->title,
			'size' => $w->size,
			'service'=>$w->service,
			'thing' => 'Widget',
			'limit' => (integer) $w->limit
		);
		return soCialMeReader::publicContent('onwidget',$core,$params);
	}
}
?>