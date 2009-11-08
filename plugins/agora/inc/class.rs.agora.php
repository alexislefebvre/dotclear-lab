<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku , Tomtom and contributors
## Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class rsExtagora
{
	public static function getURL($rs)
	{
		return $rs->core->blog->url.$rs->core->url->getBase('forum').'/'.
		html::sanitizeURL($rs->post_url);
	}

	public static function NewPosts($rs)
	{
		$now = dt::toUTC(time());
		
		// to be continued
		
	}

	public static function getThreadURL($rs)
	{


	}
}
class rsExtMessage
{
	public static function getContent($rs,$absolute_urls=false)
	{
		if ($absolute_urls) {
			return html::absoluteURLs($rs->message_content_xhtml,$rs->getURL());
		} else {
			return $rs->message_content_xhtml;
		}
	}

	public static function getURL($rs)
	{
		return $rs->core->blog->url.$rs->core->getPostPublicURL(
				$rs->post_type,html::sanitizeURL($rs->post_url)
			);
	}

	public static function getDate($rs,$format)
	{
		if ($format) {
			return dt::dt2str($format,$rs->message_dt);
		} else {
			return dt::dt2str($rs->core->blog->settings->date_format,$rs->message_dt);
		}
	}
	
	public static function getTime($rs,$format)
	{
		if ($format) {
			return dt::dt2str($format,$rs->messsage_dt);
		} else {
			return dt::dt2str($rs->core->blog->settings->time_format,$rs->message_dt);
		}
	}

	public static function getTS($rs)
	{
		return strtotime($rs->messsage_dt);
	}
	
	public static function getISO8601Date($rs)
	{
		return dt::iso8601($rs->getTS(),$rs->message_tz);
	}
	
	public static function getRFC822Date($rs)
	{
		return dt::rfc822($rs->getTS(),$rs->message_tz);
	}
}
?>