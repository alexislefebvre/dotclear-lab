<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibFacebook, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}# Config for oauth 2 client managerclass oAuthClient20Facebook extends oAuthClient20{	# Set oauth config	public function __construct($core,$config)	{		$service = array(			'client_id' => 'facebook',			'client_name' => __('Facebook'),			'api_url' => 'https://graph.facebook.com/', // me?$access_token			'authorize' => 'https://graph.facebook.com/oauth/authorize', // ?client_id=YOUR_APP_ID&redirect_uri=YOUR_URL&scope= // return _GET['code'] = or $_GET['error']			'access_token' => 'https://graph.facebook.com/oauth/access_token', //?client_id=YOUR_APP_ID&redirect_uri=YOUR_URL&client_secret=YOUR_APP_SECRET&code=THE_CODE_FROM_ABOVE // return access_token=...&expires=...
			'query_type' => 'access_token'		);				$config = array_merge($service,$config);				parent::__construct($core,$config);
		
		//$this->oauth->setAuthType(0);	}		# Save user screen name after grant access	protected function onGrantAccess()	{		if ($this->state() == 2)		{			$user = $this->get('me');
						if ($user)			{				$cur = $this->store->open();				$this->record->name = $cur->name = $user->name; //todo: escape name
				$this->record->more = $cur->more = serialize(array('id'=>$user->id,'name'=>$user->name,'timezone'=>$user->timezone,'profil_url'=>$user->link));				$this->store->upd($this->record->uid,$cur,false);			}		}	}
	
	# get some additionnal info
	public function info($i)
	{
		$infos = @unserialize($this->record->more);
		
		return !is_array($infos) || !isset($infos[$i]) ? null : $infos[$i];
	}}?>