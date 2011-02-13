<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibTwitter, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}# Config for oauth 1.0 client managerclass oAuthClient10Twitter extends oAuthClient10{	# Set oauth config	public function __construct($core,$config)	{		$service = array(			'client_id' => 'twitter',			'client_name' => __('Twitter'),			'api_url' => 'https://api.twitter.com/1/',			'request_token' => 'https://api.twitter.com/oauth/request_token',			'authorize' => 'https://twitter.com/oauth/authenticate',			'authenticate' => 'https://twitter.com/oauth/authenticate',			'access_token' => 'https://api.twitter.com/oauth/access_token'		);				$config = array_merge($service,$config);				parent::__construct($core,$config);	}		# Save user screen name after grant access	protected function onGrantAccess()	{		if ($this->state() == 2)		{			$user = $this->get('account/verify_credentials');			if ($user)			{				$cur = $this->store->open();				$this->record->name = $cur->name = $user->screen_name; //todo: escape name				$this->store->upd($this->record->uid,$cur,false);			}		}	}		# get user screen name	public function getScreenName()	{		return $this->state() == 2 ? $this->record->name : null;	}}?>