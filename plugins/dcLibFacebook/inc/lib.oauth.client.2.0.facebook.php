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

if (!defined('DC_RC_PATH')){return;}
			'query_type' => 'access_token'
		
		//$this->oauth->setAuthType(0);
			
				$this->record->more = $cur->more = serialize(array('id'=>$user->id,'name'=>$user->name,'timezone'=>$user->timezone,'profil_url'=>$user->link));
	
	# get some additionnal info
	public function info($i)
	{
		$infos = @unserialize($this->record->more);
		
		return !is_array($infos) || !isset($infos[$i]) ? null : $infos[$i];
	}