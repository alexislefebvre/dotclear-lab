<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of pluginBootstrap,
# a plugin for DotClear2.
#
# Copyright (c) 2008 Vincent Garnier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class bsText extends text
{
	static public function strToCamelCase($str)
	{
		$str = parent::str2URL($str,false);

		$str = implode('',array_map('ucfirst',explode('-',$str)));

		return (string)(strtolower(substr($str,0,1)).substr($str,1));
	}
}
