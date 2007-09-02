<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Spamplemousse2, a plugin for DotClear.  
# Copyright (c) 2007 Alain Vagner and contributors. All rights
# reserved.
#
# Spamplemousse2 is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# Spamplemousse2 is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Spamplemousse2; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

$core->rest->addFunction('postProgress',array('progressRest','postProgress'));

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
	@param	get			<b>array</b>		Get	parameters
	@param	post		<b>array</b>		Post parameters
	@return				<b>XmlTag</b>		XML message
	*/
	public static function postProgress(&$core,$get, $post)
	{
		$title = '';
		$urlprefix = '';
		$urlreturn = '1'; 
		$funcClass = !empty($post['funcClass']) ? $post['funcClass'] : null;
		$funcMethod = !empty($post['funcMethod']) ? $post['funcMethod'] : null;
		$func = array($funcClass, $funcMethod);
		$start = !empty($post['start']) ? $post['start'] : 0;
		$stop = !empty($post['stop']) ? $post['stop'] : 0;		
		$baseInc = !empty($post['baseInc']) ? $post['baseInc'] : 0;	
		
		$progress = new progress($title, $urlprefix, $urlreturn, $func, $start, $stop, $baseInc, $GLOBALS['core']->getNonce());
		$content = $progress->toXml();

		return $content;
	}
}
?>