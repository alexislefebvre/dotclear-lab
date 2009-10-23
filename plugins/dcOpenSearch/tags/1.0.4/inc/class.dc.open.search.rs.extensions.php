<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcOpenSearch, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class dcOpenSearchRsExtensions
{
	public static function ifTypeChange($rs)
	{
		if ($rs->isStart()) {
			return true;
		}
		
		$ctype = $rs->search_type;
		$rs->movePrev();
		$ntype = $rs->search_type;
		$rs->moveNext();
		return $ntype != $ctype;	
	}
	
	public static function getAuthorLink($rs)
	{
		$res = '%1$s';
		$url = $rs->search_author_url;
		if ($url) {
			$res = '<a href="%2$s">%1$s</a>';
		}
		
		return sprintf($res,html::escapeHTML($rs->search_author_name),html::escapeHTML($url));
	}
	
	public static function getTS($rs)
	{
		return strtotime($rs->search_dt);
	}
	
	public static function getISO8601Date($rs)
	{
		return dt::iso8601($rs->getTS(),$rs->search_tz);
	}

	public static function getRFC822Date($rs)
	{
		return dt::rfc822($rs->getTS(),$rs->search_tz);
	}

	public static function getDate($rs,$format)
	{
		if ($format) {
			return dt::dt2str($format,$rs->search_dt);
		} else {
			return dt::dt2str($GLOBALS['core']->blog->settings->date_format,$rs->search_dt);
		}
	}
	
	public static function getTime($rs,$format)
	{
		if ($format) {
			return dt::dt2str($format,$rs->search_dt);
		} else {
			return dt::dt2str($GLOBALS['core']->blog->settings->time_format,$rs->search_dt);
		}
	}
}

?>