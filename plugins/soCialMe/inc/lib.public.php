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
	# Declare soCialMe Tpl (before document)
	public static function publicBeforeDocument($core)
	{
		# Load social part class once for all one page
		foreach(soCialMeUtils::getParts() as $part => $ns)
		{
			$core->{$ns} = new $ns($core);
		}
		# tpl path
		$core->tpl->setPath($core->tpl->getPath(),dirname(__FILE__).'/../default-templates');
		# Records tags
		$core->tpl->addBlock('SoCialMePreloadBox',array('soCialMeTemplate','SoCialMePreloadBox'));
		$core->tpl->addBlock('SoCialMeRecords',array('soCialMeTemplate','SoCialMeRecords'));
		$core->tpl->addBlock('SoCialMeRecordsOptionsIf',array('soCialMeTemplate','SoCialMeRecordsOptionsIf'));
		$core->tpl->addBlock('SoCialMeRecordsIf',array('soCialMeTemplate','SoCialMeRecordsIf'));
		$core->tpl->addValue('SoCialMeRecordsTitle',array('soCialMeTemplate','SoCialMeRecordsTitle'));
		$core->tpl->addValue('SoCialMeRecordsPart',array('soCialMeTemplate','SoCialMeRecordsPart'));
		$core->tpl->addValue('SoCialMeRecordsThing',array('soCialMeTemplate','SoCialMeRecordsThing'));
		$core->tpl->addValue('SoCialMeRecordsPlace',array('soCialMeTemplate','SoCialMeRecordsPlace'));
		$core->tpl->addBlock('SoCialMeRecordsHeader',array('soCialMeTemplate','SoCialMeRecordsHeader'));
		$core->tpl->addBlock('SoCialMeRecordsFooter',array('soCialMeTemplate','SoCialMeRecordsFooter'));
		# Record tags
		$core->tpl->addBlock('SoCialMeRecordIf',array('soCialMeTemplate','SoCialMeRecordIf'));
		$core->tpl->addBlock('SoCialMeRecordFieldIf',array('soCialMeTemplate','SoCialMeRecordFieldIf'));
		$core->tpl->addValue('SoCialMeRecordIfFirst',array('soCialMeTemplate','SoCialMeRecordIfFirst'));
		$core->tpl->addValue('SoCialMeRecordIfOdd',array('soCialMeTemplate','SoCialMeRecordIfOdd'));
		$core->tpl->addValue('SoCialMeRecordIfMe',array('soCialMeTemplate','SoCialMeRecordIfMe'));
		$core->tpl->addValue('SoCialMeRecordId',array('soCialMeTemplate','SoCialMeRecordId'));
		$core->tpl->addValue('SoCialMeRecordService',array('soCialMeTemplate','SoCialMeRecordService'));
		$core->tpl->addValue('SoCialMeRecordSourceName',array('soCialMeTemplate','SoCialMeRecordSourceName'));
		$core->tpl->addValue('SoCialMeRecordSourceURL',array('soCialMeTemplate','SoCialMeRecordSourceURL'));
		$core->tpl->addValue('SoCialMeRecordSourceIcon',array('soCialMeTemplate','SoCialMeRecordSourceIcon'));
		$core->tpl->addValue('SoCialMeRecordTitle',array('soCialMeTemplate','SoCialMeRecordTitle'));
		$core->tpl->addValue('SoCialMeRecordExcerpt',array('soCialMeTemplate','SoCialMeRecordExcerpt'));
		$core->tpl->addValue('SoCialMeRecordContent',array('soCialMeTemplate','SoCialMeRecordContent'));
		$core->tpl->addValue('SoCialMeRecordIcon',array('soCialMeTemplate','SoCialMeRecordIcon'));
		$core->tpl->addValue('SoCialMeRecordAvatar',array('soCialMeTemplate','SoCialMeRecordAvatar'));
		$core->tpl->addValue('SoCialMeRecordDate',array('soCialMeTemplate','SoCialMeRecordDate'));
		$core->tpl->addValue('SoCialMeRecordTime',array('soCialMeTemplate','SoCialMeRecordTime'));
		$core->tpl->addValue('SoCialMeRecordURL',array('soCialMeTemplate','SoCialMeRecordURL'));
		# Reader page tags
		$core->tpl->addValue('SoCialMeReaderPageTitle',array('soCialMeTemplate','SoCialMeReaderPageTitle'));
		$core->tpl->addValue('SoCialMeReaderPageContent',array('soCialMeTemplate','SoCialMeReaderPageContent'));
	}
	
	# Load soCialMe CSS (head content)
	public static function publicHeadContent($core)
	{
		foreach(soCialMeUtils::getParts() as $part => $ns)
		{
			$css = $core->blog->settings->{$ns}->css;
			if (!$core->{$ns}->active || !$css) continue;
			
			echo 
			"\n<!-- Style for plugin ".$ns." --> \n".
			'<style type="text/css">'."\n".
			html::escapeHTML($css)."\n".
			"</style>\n";
		}
	}
	
	# Execute services actions (top content)
	public static function publicTopAfterContent($core)
	{
		echo self::playContent($core,'ontop');
	}
	
	# Execute services actions (before post content)
	public static function publicEntryBeforeContent($core,$_ctx)
	{
		$params = self::getPostContext($core,$_ctx);
		if (!$params) return;
		
		echo self::playContent($core,'beforepost',$params);
	}
	
	# Execute services actions (after post content)
	public static function publicEntryAfterContent($core,$_ctx)
	{
		$params = self::getPostContext($core,$_ctx);
		if (!$params) return;
		
		echo self::playContent($core,'afterpost',$params);
	}
	
	# Execute services actions and script (footer content)
	public static function publicFooterContent($core)
	{
		echo self::playContent($core,'onfooter');
		echo self::playScript($core,'Public');
	}
	
	# Execute services script (after document)
	// This is used to put records in cache
	// todo: send this to an external ajax query or cron
	public static function publicAfterDocument($core)
	{
		self::playScript($core,'Server');
	}
	
	# Widget soCialMe Sharer post
	public static function widgetSharerPostPublic($w)
	{
		global $core, $_ctx;
		$params = self::getPostContext($core,$_ctx);
		if (!$params) return;
		
		echo  $core->soCialMeSharer->playContent('onwidget',$params);
	}
	
	# Widget soCialMe Profil badge
	public static function widgetProfilBadgePublic($w)
	{
		global $core;
		$params = array(
			'thing' => $w->thing
		);
		echo  $core->soCialMeProfil->playContent('onwidget',$params);
	}
	
	# Widget soCialMe Reader stream
	public static function widgetReaderStreamPublic($w)
	{
		global $core;
		
		if ($core->url->type == 'soCialMeReader') return;
		
		$params = array(
			'title' => $w->title,
			'size' => $w->size,
			'service'=>$w->service,
			'thing' => 'Widget',
			'limit' => (integer) $w->limit,
			'order' => 'date',
			'sort' => 'asc'
		);
		echo $core->soCialMeReader->playContent('onwidget',$params);
	}
	
	# Commons for playXxxContent
	private static function playContent($core,$place,$params=array())
	{
		$res = '';
		foreach(soCialMeUtils::getParts() as $part => $ns)
		{
			$res .= $core->{$ns}->playContent($place,$params);
		}
		return $res;
	}
	
	# Commons for playXxxScript
	private static function playScript($core,$type)
	{
		$res = '';
		foreach(soCialMeUtils::getParts() as $part => $ns)
		{
			$res .= $core->{$ns}->playScript($type);
		}
		return $res;
	}
	
	# Retrieve post info from context
	private static function getPostContext($core,$_ctx)
	{
		if (!$_ctx->exists('posts')) return array();
		
		return array(
			'more' => soCialMeUtils::fillPlayRecord(array(
				'url' => $_ctx->posts->getURL(),
				'title' => $_ctx->posts->post_title,
				'excerpt' => $_ctx->posts->post_excerpt_xhtml,
				'content' => $_ctx->posts->post_content_xhtml,
				'category' =>  $_ctx->posts->cat_title,
				'tags' => $_ctx->posts->post_meta,
				'author' => $_ctx->posts->user_displayname,
				'type' => 'text'
			)
		));
	}
}
?>