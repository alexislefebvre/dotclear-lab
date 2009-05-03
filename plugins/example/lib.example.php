<?php
/***** BEGIN LICENSE BLOCK *****
This program is free software. It comes without any warranty, to
the extent permitted by applicable law. You can redistribute it
and/or modify it under the terms of the Do What The Fuck You Want
To Public License, Version 2, as published by Sam Hocevar. See
http://sam.zoy.org/wtfpl/COPYING for more details.
	
Icon (icon.png) is from Silk Icons :
	http://www.famfamfam.com/lab/icons/silk/

***** END LICENSE BLOCK *****/

if (!defined('DC_RC_PATH')) {return;}

/**
@ingroup Example
@brief Generic functions
*/
class example
{
	/**
	return Hello World!
	@return	<b>string</b> Hello World!
	*/
	public static function HelloWorld()
	{
		return(__('Hello World!'));
	}
	
	/**
	return last post title
	@return	<b>string</b> Last post title
	*/
	public static function LastPostTitle()
	{
		global $core;
		
		$query = 'SELECT post_title FROM '.$core->prefix.'post '.
			'WHERE blog_id = \''.$core->con->escape($core->blog->id).'\' '.
			'ORDER BY post_dt DESC';
		
		$rs = $core->con->select($query);
		
		return($rs->post_title);
	}
}

?>