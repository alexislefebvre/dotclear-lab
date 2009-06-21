<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of threadedComments, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------

# This is a wrapper class to access the protected "blocks"
# and "values" variables

class odtTemplate extends dcTemplate
{
	function __construct()
	{
	}

	public function getBlock($name, $args=array())
	{
		global $core;
		return call_user_func($core->tpl->blocks[$name],$args);
	}
	
	public function getValue($name, $args=array())
	{
		global $core;
		return call_user_func($core->tpl->values[$name],$args);
	}
	
	
}
?>
