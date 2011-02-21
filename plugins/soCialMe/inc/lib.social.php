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

if (!defined('DC_RC_PATH')){return;}# All parts (sharer,reader,profil,writer) extend this classclass soCialMe{	public $core;	
	protected $part = null;	protected $ns = null;	public $active = false;	protected $services = array();	protected $available = array();	protected $actions = array();	protected $markers = array();	protected $things = array();		# Load all services class and part markers	public function __construct($core)	{		if (!$this->ns) {			throw new Exception('Behavior name must be specified');		}				$this->core = $core;				# Load services list from behavior		$list = $core->getBehaviors($this->ns.'Service');				if (!empty($list))		{			foreach($list as $k => $callback)			{				try				{					list($service_id,$service_class) = call_user_func($callback);										$service = new $service_class($core);										if (!$service->available()) continue;										$this->services[$service_id] = $service;					$this->available[] = $service_id;										foreach($service->actions() as $action => $value)					{						if (!$value) continue;												$this->actions[$action][] = $service_id;					}				}				catch (Exception $e) {}			}		}				$this->init();
		$this->active = $this->core->blog->settings->{$this->ns}->active;				# Load part markers		$rs = new ArrayObject($this->markers);				$this->core->callBehavior($this->ns.'Marker',$rs);				$markers = $rs->getArrayCopy();				if (!is_array($markers)) $markers = array();				# Clean up default values		foreach($markers as $key => $marker)		{			$markers[$key] = array_merge(				array(					'name' => 'unknow',					'description' => 'no description',					'action' => array(),					'title' => false,					'page' => false,
					'homeonly' => false,
					'order' => false				),				$marker			);		}		$this->markers = $markers;	}
	
	# Construction of admin pages for child class
	public static function adminNav()
	{
		return null;
	}		# Init for child class	protected function init()	{		return null;	}		# Return array of original markers	public function getMarkers()	{		return $this->markers;	}		# Return markers of a $type fill with original markers	public function getMarker($type='',$default='')	{		$partials = soCialMeUtils::decode($this->core->blog->settings->{$this->ns}->{$type});				$rs = array();		foreach($this->markers as $key => $marker)		{			if (!isset($marker[$type])) continue;						if (!isset($partials[$key])) $partials[$key] = array();						if (is_array($marker[$type]))			{				foreach($marker[$type] as $t)				{					$rs[$key][$t] = isset($partials[$key][$t]) ? $partials[$key][$t] : $default;				}			}			elseif (true === $marker[$type])			{				$rs[$key] = isset($partials[$key]) ? $partials[$key] : $default;			}		}		return $rs;	}		# Reorder things array from a partial ordered array and a full unordered array	public function fillOrder($availables,$limit=false)	{		$partials = soCialMeUtils::decode($this->core->blog->settings->{$this->ns}->order);				$rs = array();		foreach($this->things as $thing => $plop)		{			$rs[$thing] = array();						# Clean partial array			if (!isset($partials[$thing]) || !is_array($partials[$thing])) {				$partials[$thing] = array();			}			$partials[$thing] = array_values($partials[$thing]);						# Clean available array			if (!isset($availables[$thing]) || !is_array($availables[$thing])) {				$availables[$thing] = array();			}			$availables[$thing] = array_values($availables[$thing]);						if ($limit) {				# Limit array to usable things				$partials[$thing] = array_intersect($partials[$thing],$availables[$thing]);			}			# Merge arrays and keep ordered services at first			$rs[$thing] = array_merge($partials[$thing],$availables[$thing]);						# Removed duplicate services			$rs[$thing] = array_unique($rs[$thing]);						# Clean keys			$rs[$thing] = array_values($rs[$thing]);		}		return $rs;	}		# Return array of services objects or one service	public function services($id='')	{		if (empty($id)) {			return $this->services;		}		elseif (!empty($id) && isset($this->services[$id])) {			return $this->services[$id];		}		else {			return array();		}	}		# Return array of id of available services	public function available()	{		return $this->available;	}		# Return array of id/name of available things	public function things()	{		return $this->things;	}		# Return an array of id of available services for an action	public function can()	{		if (func_num_args() < 1) return array();				$actions = func_get_args();				$rs = array();		foreach($actions as $action)		{			$func = 'play'.$action;						if (!isset($this->actions[$func])) continue;						$rs = array_merge($rs,$this->actions[$func]);		}		return array_unique($rs);	}		# Execute action on service	public function play()	{		$n = func_num_args();		if ($n < 3) return false;				$args = func_get_args();		$service_id = array_shift($args);		$func = 'play'.array_shift($args).array_shift($args); // playThingType($args)				if (!in_array($service_id,$this->actions[$func])) return false;				try {			return call_user_func_array(array($this->services[$service_id],$func),$args);		}		catch(Exception $e) { }				return false;	}
	
	# Construct contents
	public function playContent($place,$params=array())
	{
		# Active
		if (!$this->active) 
		{
			return;
		}
		
		# Only on home page
		if (!empty($this->markers[$place]['homeonly']))
		{
			$s_homeonly = $this->getMarker('homeonly',false);
			if (!empty($s_homeonly) && is_array($s_homeonly) && isset($s_homeonly[$place]) 
			&& !empty($s_homeonly[$place]) && $this->core->url->type != 'default')
			{
				return;
			}
		}
		
		# Pages
		if (!empty($this->markers[$place]['page']))
		{
			$s_page = $this->getMarker('page',false);
			if ($place != 'onwidget' && (empty($s_page) || !is_array($s_page) 
			 || !isset($s_page[$place]) || empty($s_page[$place][$this->core->url->type])))
			{
				return;
			}
		}
		
		# Services
		$s_action = $this->getMarker('action',array());
		if (empty($s_action) || !is_array($s_action) || !isset($s_action[$place]))
		{
			return;
		}
		
		# Title
		$s_title = $this->getMarker('title','');
		
		# clean params
		$force_title = !empty($params['title']) ? $params['title'] : '';
		$service_limit = !empty($params['service']) ? $params['service'] : '';
		$avatar_size = !empty($params['size']) && in_array($params['size'],array('small','normal')) ? $params['size'] : '';
		$thing_limit = !empty($params['thing']) ? $params['thing'] : '';
		$count_limit = !empty($params['limit']) ? (integer) $params['limit'] : 100;
		$rec_order = !empty($params['order']) ? $params['order'] : 'date';
		$rec_sort = !empty($params['sort']) ? $params['sort'] : 'asc';
		$play_more = !empty($params['more']) ? $params['more'] : null;
		
		# Get services codes
		$usable = array();
		foreach($this->things() as $thing => $plop)
		{
			if (!empty($thing_limit) && $thing != $thing_limit) continue;
			
			$usable[$thing] = $this->can($thing.'Content');
		}
		
		# Reorder
		$s_order = $this->fillOrder($usable);
		
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
				$tmp = $this->play($service_id,$thing,'Content',$play_more);
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
			if (empty($rec) || !is_array($rec)) continue;
			
			# Convert to record
			$rec = soCialMeUtils::arrayToRecord($rec);
			# Sort by setting order or call order
			if (empty($this->markers[$place]['order'])) {
				$rec->sort($rec_order,$rec_sort);
			}
			if (!empty($force_title)) {
				$_ctx->soCialMeRecordsTitle = $force_title;
			}
			elseif (isset($s_title[$place]) && !empty($s_title[$place])) {
				$_ctx->soCialMeRecordsTitle = $s_title[$place];
			}
			else {
				$_ctx->soCialMeRecordsTitle = '';
			}
			$_ctx->soCialMeRecordsLimit = $count_limit;
			$_ctx->soCialMeRecordsIcon = $avatar_size;
			$_ctx->soCialMeRecordsOptions = array(
				'part' => $this->part,
				'thing' => $thing,
				'place' => $place
			);
			$_ctx->soCialMeRecords = $rec;
			
			$res .= $this->core->tpl->getData('socialme-records.html');
		}
		return $res;
	}
	
	# Construct scripts
	// type = Public or Server
	public function playScript($type)
	{
		# Active
		if (!$this->active) return;
		
		# get services that have hidden func
		$services = $this->can($type.'Script');
		if (empty($services)) return;
		
		# get list of func per service per thing
		$available = array();
		$things = $this->things();
		foreach($things as $thing => $plop)
		{
			$available[$thing] = $this->can($thing.'Content',$thing.'Script');
		}
		if (empty($available)) return;
		
		# loop through services to do their job
		$res = '';
		foreach($services as $service_id)
		{
			if (!soCialMeUtils::thingsHasService($available,$service_id)) continue;
			try {
				$tmp = $this->play($service_id,$type,'Script',$available);
				$res .= $tmp;
			}
			catch (Exception $e) { }
		}
		return $res;
	}}?>