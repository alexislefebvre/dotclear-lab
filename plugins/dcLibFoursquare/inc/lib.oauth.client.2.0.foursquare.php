<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibFoursquare, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}# Config for oauth 1.0 client managerclass oAuthClient20Foursquare extends oAuthClient20{	# Set oauth config	public function __construct($core,$config)	{		$service = array(			'client_id' => 'foursquare',			'client_name' => __('Foursquare'),			'api_url' => 'https://api.foursquare.com/v2/',			'authorize' => 'https://foursquare.com/oauth2/authenticate',			'access_token' => 'https://foursquare.com/oauth2/access_token',
			'options' => array(
				'response_type' => 'code'
			)		);				$config = array_merge($service,$config);				parent::__construct($core,$config);	}		# Save user screen name after grant access	protected function onGrantAccess()	{		if ($this->state() == 2)		{
			$rsp = foursquareUtils::api($this,'users/self');
						if ($rsp)			{				$cur = $this->store->open();				$this->record->name = $cur->name = $rsp->user->firstName.' '.$rsp->user->lastName; //todo: escape name
				$this->record->more = $cur->more = serialize(array('id'=>$rsp->user->id,'name'=>$rsp->user->firstName.' '.$rsp->user->lastName));				$this->store->upd($this->record->uid,$cur,false);			}		}	}
	
	# get some additionnal info
	public function info($i)
	{
		$infos = @unserialize($this->record->more);
		
		return !is_array($infos) || !isset($infos[$i]) ? null : $infos[$i];
	}}?>