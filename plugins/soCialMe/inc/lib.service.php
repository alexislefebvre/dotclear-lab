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
	
	# Local URL to get images
	protected $url = null;
		
		$this->url = $core->blog->getQmarkURL();
		if ($n == 'icon' && !empty($this->define['icon']) && substr($this->define['icon'],0,3) == 'pf=') {
			return $this->url.$this->define['icon'];
		}