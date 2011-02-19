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

if (!defined('DC_RC_PATH')){return;}
			'options' => array(
				'response_type' => 'code'
			)
			$rsp = foursquareUtils::api($this,'users/self');
			
				$this->record->more = $cur->more = serialize(array('id'=>$rsp->user->id,'name'=>$rsp->user->firstName.' '.$rsp->user->lastName));
	
	# get some additionnal info
	public function info($i)
	{
		$infos = @unserialize($this->record->more);
		
		return !is_array($infos) || !isset($infos[$i]) ? null : $infos[$i];
	}