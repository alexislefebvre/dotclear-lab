<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of entryCSS, a plugin for Dotclear.
# 
# Copyright (c) 2009 - annso
# contact@as-i-am.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class entryCSS
{
	
	public static function getCSS($id)
	{
		global $core, $_ctx;
		
		$query = 'SELECT post_css FROM '.$core->prefix.'post WHERE post_id = '.$id.';';
		$rs = $core->con->select($query);
		$value = $rs->f('post_css');
		return $value;
	}	
	
}

?>