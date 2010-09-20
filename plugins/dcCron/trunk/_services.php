<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcCron, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class dcCronRestMethods
{
	/**
	Returns a readable interval
	
	@return	<b>xmlTag</b>		Response XML
	*/
	public static function getInterval()
	{
		if (isset($_GET['i'])) {
			$i = $_GET['i'];
		}
		else {
			throw new Exception(__('No interval'));
		}
		
		if (!is_numeric($i)) {
			throw new Exception(__('Interval must be a number'));
		}
		
		$rsp = new xmlTag();
		$rsp->interval(dcCron::getInterval($i));
		
		return $rsp;
	}
}

?>