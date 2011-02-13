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
if (!defined('DC_RC_PATH')){return;}# URL handlerclass soCialMeURL extends dcUrlHandlers{	public static function soCialMeReaderPage($args)	{		global $core;				$params = array(			'size' => 'normal',			'service'=> '',			'thing' => 'Page',			'limit' => 30		);				$res = soCialMeReader::publicContent('onpage',$core,$params);				if (empty($res)) {			self::p404();		}		echo $res;		exit;	}}?>