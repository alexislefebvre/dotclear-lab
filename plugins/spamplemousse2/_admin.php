<?php
# ***** BEGIN LICENSE BLOCK *****
# This is progress, a plugin for DotClear. 
# Copyright (c) 2007 Alain Vagner and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
$core->rest->addFunction('getProgress',array('progressRest','getProgress'));

/**
@ingroup PROGRESS
@brief Progress rest interface

This class calls the toXml method of the progress class with the GET parameters
*/
class progressRest
{
	/**
	Call the toXml method

	@param	core		<b>DcCore</b>		Dotclear core object
	@param	get			<b>array</b>		Get parameters
	@return				<b>XmlTag</b>		XML message
	*/
	public static function getProgress(&$core,$get)
	{
		$title = '';
		$urlprefix = '';
		$urlreturn = '1'; 
		$funcClass = !empty($get['funcClass']) ? $get['funcClass'] : null;
		$funcMethod = !empty($get['funcMethod']) ? $get['funcMethod'] : null;
		$func = array($funcClass, $funcMethod);
		$start = !empty($get['start']) ? $get['start'] : 0;
		$stop = !empty($get['stop']) ? $get['stop'] : 0;		
		$baseInc = !empty($get['baseInc']) ? $get['baseInc'] : 0;	
		
		$progress = new progress($title, $urlprefix, $urlreturn, $func, $start, $stop, $baseInc);
		$content = $progress->toXml();

		return $content;
	}
}
?>