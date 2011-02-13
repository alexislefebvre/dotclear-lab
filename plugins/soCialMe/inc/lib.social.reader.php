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

class soCialMeReader extends soCialMe
{
	protected $ns = 'soCialMeReader';
	protected $things = array(
		'Feed' => 'Source',
		'Widget' => 'Source',
		'Page' => 'Source',
		'Comment' => 'Source'
	);
	protected $markers = null;
	
	# Load markers (all actions things)
	public function init()
	{
		# On comments and trackbacks
		$markers['oncomment'] = array(
			'name' => __('Comments and trackbacks'),
			'description' => __('Search comment or trackback from an external service.'),
			'action' => array('Comment')
		);
		
		# on widget
		$markers['onwidget'] = array(
			'name' => __('On a widget'),
			'description' => __('Show a stream on a widget from external services.'),
			'action' => array('Widget'),
			'title' => true,
			'homeonly' => true
		);
		
		$url = $this->core->blog->getQmarkURL().$this->core->url->getBase('soCialMeReader');
		$url = ' <a href="'.$url.'">'.$url.'</a>';
		
		# on widget
		$markers['onpage'] = array(
			'name' => __('On a page'),
			'description' => __('Show a stream on a page from external services.').$url,
			'action' => array('Page'),
			'title' => true
		);
		
		$this->markers = $markers;
	}
	
	# Public content (on page and widget)
	public static function publicContent($place,$core,$params=array())
	{
		# Active
		if (!$core->blog->settings->soCialMeReader->active) 
		{
			return;
		}
		
		# clean params
		$force_title = !empty($params['title']) ? $params['title'] : '';
		$service_limit = !empty($params['service']) ? $params['service'] : '';
		$avatar_size = !empty($params['size']) && in_array($params['size'],array('small','normal')) ? $params['size'] : '';
		$thing_limit = !empty($params['thing']) ? $params['thing'] : '';
		$count_limit = !empty($params['limit']) ? (integer) $params['limit'] : 100;
		
		# main class
		$class = new soCialMeReader($core);
		
		# Only on home page
		$s_homeonly = $class->getMarker('homeonly',false);
		if (!empty($s_homeonly) && is_array($s_homeonly) && isset($s_homeonly[$place]) 
		&& !empty($s_homeonly[$place]) && $core->url->type != 'default')
		{
			return;
		}
		
		# Services
		$s_action = $class->getMarker('action',array());
		if (empty($s_action) || !is_array($s_action) || !isset($s_action[$place]))
		{
			return;
		}
		
		# Title
		$s_title = $class->getMarker('title','');
		
		# Get services codes
		foreach($class->things() as $thing => $plop)
		{
			if (!empty($thing_limit) && $thing != $thing_limit) continue;
			
			$usable[$thing] = $class->can($thing.'Content');
		}
		
		# Reorder
		$s_order = $class->fillOrder($usable);
		
		# Get actions
		$rs = array();
		foreach($s_order as $thing => $services)
		{
			if (!isset($s_action[$place][$thing]) || empty($s_action[$place][$thing])) continue;
			$rs[$thing] = array();
			
			foreach($services as $service_id)
			{
				if (!empty($service_limit) && $service_limit != $service_id) continue;
				
				if (!in_array($service_id,$s_action[$place][$thing])) continue;
				
				# action must return formatted array of feeds content
				$tmp = $class->play($service_id,$thing,'Content',null);
				if (!is_array($tmp) || empty($tmp)) continue;
				
				$rs[$thing] = array_merge($rs[$thing],$tmp);
			}
		}
		
		# no stream
		if (empty($rs)) return;
		
		global $_ctx;
		
		# Loop through things
		$res = '';
		foreach($rs as $thing => $rec)
		{
			if (empty($rec)) continue;
			
			# Convert to record and sort result by date
			$rec = soCialMeUtils::arrayToRecord($rec);
			$rec->sort('date','desc');
			
			if (!empty($force_title)) {
				$_ctx->soCialMeReadersTitle = $force_title;
			}
			elseif (isset($s_title[$place]) && !empty($s_title[$place])) {
				$_ctx->soCialMeReadersTitle = $s_title[$place];
			}
			else {
				$_ctx->soCialMeReadersTitle = '';
			}
			$_ctx->soCialMeReadersLimit = $count_limit;
			$_ctx->soCialMeReadersIcon = $avatar_size;
			$_ctx->soCialMeReadersThing = $thing;
			$_ctx->soCialMeReadersPlace = $place;
			$_ctx->soCialMeReaders = $rec;
			
			$res .= $core->tpl->getData('socialme-reader-'.strtolower($place).'.html');
		}
		return $res;
	}
}
?>