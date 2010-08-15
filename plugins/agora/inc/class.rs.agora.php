<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku , Tomtom and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class rsExtMessage
{
	public static function getContent($rs,$absolute_urls=false)
	{
		if ($absolute_urls) {
			return html::absoluteURLs($rs->message_content_xhtml,$rs->getThreadURL());
		} else {
			return $rs->message_content_xhtml;
		}
	}

	public static function getAuthorCN($rs)
	{
		return dcUtils::getUserCN($rs->user_id, $rs->user_name,
		$rs->user_firstname, $rs->user_displayname);
	}

	public static function getThreadURL($rs)
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
			return dt::dt2str($rs->core->blog->settings->system->date_format,$rs->message_dt);
		}
	}
	
	public static function getTime($rs,$format)
	{
		if ($format) {
			return dt::dt2str($format,$rs->messsage_dt);
		} else {
			return dt::dt2str($rs->core->blog->settings->system->time_format,$rs->message_dt);
		}
	}

	public static function getTS($rs)
	{
		return strtotime($rs->message_dt);
	}
	
	public static function getISO8601Date($rs)
	{
		return dt::iso8601($rs->getTS(),$rs->message_tz);
	}
	
	public static function getRFC822Date($rs)
	{
		return dt::rfc822($rs->getTS(),$rs->message_tz);
	}

	public static function getFeedID($rs)
	{
		return 'urn:md5:'.md5($rs->core->blog->uid.$rs->message_id);
		
		$url = parse_url($rs->core->blog->url);
		$date_part = date('Y-m-d',strtotime($rs->message_dt));
		
		return 'tag:'.$url['host'].','.$date_part.':'.$rs->message_id;
	}
}
?>
