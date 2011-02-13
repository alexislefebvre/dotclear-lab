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

# Public behaviors
class soCialMePublic
{
	# available social parts
	public static $nss = array('soCialMeSharer','soCialMeReader','soCialMeWriter','soCialMeProfil');
	
	# Declare soCialMe Tpl (before document)
	public static function publicBeforeDocument($core)
	{
		# soCialMe tpl path
		$core->tpl->setPath($core->tpl->getPath(),dirname(__FILE__).'/../default-templates');
		# soCialMe Reader tags
		$core->tpl->addBlock('SoCialMeReaders',array('soCialMeTemplate','SoCialMeReaders'));
		$core->tpl->addBlock('SoCialMeReadersIf',array('soCialMeTemplate','SoCialMeReadersIf'));
		$core->tpl->addValue('SoCialMeReadersTitle',array('soCialMeTemplate','SoCialMeReadersTitle'));
		$core->tpl->addBlock('SoCialMeReadersHeader',array('soCialMeTemplate','SoCialMeReadersHeader'));
		$core->tpl->addBlock('SoCialMeReadersFooter',array('soCialMeTemplate','SoCialMeReadersFooter'));
		$core->tpl->addBlock('SoCialMeReaderIf',array('soCialMeTemplate','SoCialMeReaderIf'));
		$core->tpl->addValue('SoCialMeReaderIfFirst',array('soCialMeTemplate','SoCialMeReaderIfFirst'));
		$core->tpl->addValue('SoCialMeReaderIfOdd',array('soCialMeTemplate','SoCialMeReaderIfOdd'));
		$core->tpl->addValue('SoCialMeReaderIfMe',array('soCialMeTemplate','SoCialMeReaderIfMe'));
		$core->tpl->addValue('SoCialMeReaderId',array('soCialMeTemplate','SoCialMeReaderId'));
		$core->tpl->addValue('SoCialMeReaderService',array('soCialMeTemplate','SoCialMeReaderService'));
		$core->tpl->addValue('SoCialMeReaderSourceName',array('soCialMeTemplate','SoCialMeReaderSourceName'));
		$core->tpl->addValue('SoCialMeReaderSourceURL',array('soCialMeTemplate','SoCialMeReaderSourceURL'));
		$core->tpl->addValue('SoCialMeReaderSourceIcon',array('soCialMeTemplate','SoCialMeReaderSourceIcon'));
		$core->tpl->addValue('SoCialMeReaderTitle',array('soCialMeTemplate','SoCialMeReaderTitle'));
		$core->tpl->addValue('SoCialMeReaderContent',array('soCialMeTemplate','SoCialMeReaderContent'));
		$core->tpl->addValue('SoCialMeReaderIcon',array('soCialMeTemplate','SoCialMeReaderIcon'));
		$core->tpl->addValue('SoCialMeReaderAvatar',array('soCialMeTemplate','SoCialMeReaderAvatar'));
		$core->tpl->addValue('SoCialMeReaderDate',array('soCialMeTemplate','SoCialMeReaderDate'));
		$core->tpl->addValue('SoCialMeReaderTime',array('soCialMeTemplate','SoCialMeReaderTime'));
		$core->tpl->addValue('SoCialMeReaderURL',array('soCialMeTemplate','SoCialMeReaderURL'));
	}
	
	# Load soCialMe CSS (head content)
	public static function publicHeadContent($core)
	{
		foreach(self::$nss as $ns)
		{
			if (!$core->blog->settings->{$ns}->active 
			 || !$core->blog->settings->{$ns}->css)
			{
				continue;
			}
			
			echo 
			"\n<!-- Style for plugin ".$ns." --> \n".
			'<style type="text/css">'."\n".
			html::escapeHTML($core->blog->settings->{$ns}->css)."\n".
			"</style>\n";
		}
	}
	
	# Execute services actions (top content)
	public static function publicTopAfterContent($core)
	{
		# soCialMe Profil
		echo soCialMeProfil::publicContent('ontop',$core);
	}
	
	# Execute services actions (before post content)
	public static function publicEntryBeforeContent($core,$_ctx)
	{
		# soCialMe Sharer
		echo soCialMeSharer::publicContent('beforepost',$core,$_ctx);
	}
	
	# Execute services actions (after post content)
	public static function publicEntryAfterContent($core,$_ctx)
	{
		# soCialMe Sharer
		echo soCialMeSharer::publicContent('afterpost',$core,$_ctx);
	}
	
	# Execute services actions like playPublicScript (footer content)
	public static function publicFooterContent($core)
	{
		# soCialMe Profil
		echo soCialMeProfil::publicContent('onfooter',$core);
		# Commons
		echo self::playScript($core,'Public');
	}
	
	# Execute services actions like playServerScript (after document)
	public static function publicAfterDocument($core)
	{
		self::playScript($core,'Server');
	}
	
	# Commons for playXxxScript
	private static function playScript($core,$type)
	{
		$res = '';
		foreach(self::$nss as $ns)
		{
			# social part not active
			if (!$core->blog->settings->{$ns}->active) continue;
			# social part class
			$class = new $ns($core);
			# get services that have hidden func
			$services = $class->can($type.'Script');
			# nothing to do
			if (empty($services)) continue;
			# get list of func per service per thing
			$available = array();
			$things = $class->things();
			foreach($things as $thing => $plop)
			{
				$available[$thing] = $class->can($thing.'Content',$thing.'Script');
			}
			# nothing to do
			if (empty($available)) continue;
			# loop through services to do their job
			foreach($services as $service_id)
			{
				if (!soCialMeUtils::thingsHasService($available,$service_id)) continue;
				try {
					$tmp = $class->play($service_id,$type,'Script',$available);
					$res .= $tmp;
				}
				catch (Exception $e) { }
			}
		}
		return $res;
	}
}
?>