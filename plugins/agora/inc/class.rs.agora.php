<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 Osku ,Tomtom and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class rsExtMember
{
	public static function wikiDesc($rs)
	{
		return agoraTools::wikiTransform($rs->user_desc);
	}
	
	public static function mediaDir($rs)
	{
		return mediaAgora::imagesURL().'/'.md5($rs->user_id).'/';
	}
	
	public static function hasAvatar($rs)
	{
		return file_exists(mediaAgora::imagesPath().'/'.md5($rs->user_id).'/avatar.jpg');
	}

	public static function getActivity($rs)
	{
		return (integer)$rs->getCountMessages() + (integer)$rs->getCountPosts();
		//return $rs->nb_message;
	}

	public static function getCountMessages($rs)
	{
		global $core, $_ctx;
		return $core->agora->getMessages(array('user_id' => $rs->user_id,'message_status'=>1),true)->f(0);
		//return $rs->nb_message;
	}

	public static function getCountPosts($rs)
	{
		global $core, $_ctx;
		return $core->blog->getPosts(array('user_id' => $rs->user_id,'post_status'=>1),true)->f(0);
		//return $rs->nb_post;
	}

	public static function getAuthorCN($rs)
	{
		return dcUtils::getUserCN($rs->user_id, $rs->user_name,
		$rs->user_firstname, $rs->user_displayname);
	}

	public static function getMemberProfile($rs)
	{
		global $core;
		$res = '<a href="'.$core->blog->url.$core->url->getURLFor("profile",$rs->user_id).'">%1$s</a>';
		
		return sprintf($res,html::escapeHTML($rs->getAuthorCN()));
	}

	public static function getMemberLink($rs)
	{
		$url = $rs->user_url;
		if ($url) {
			return sprintf('<a href="%1$s">%1$s</a>',html::escapeHTML($url));
		}
	}

	public static function isModerator($rs)
	{
		global $core, $_ctx;
		
		return $core->agora->checkPermission($rs->user_id,'contentadmin');
	}

	public static function getDate($rs,$format,$type='')
	{
		global $core, $_ctx;
		if (!$format) {
			$format = $core->blog->settings->system->date_format;
		}
		
		if ($type == 'upddt') {
			return dt::dt2str($format,$rs->user_upddt,$core->blog->settings->system->blog_timezone);
		} else {
			return dt::dt2str($format,$rs->user_creadt,$core->blog->settings->system->blog_timezone);
		}
	}
	
	public static function getTime($rs,$format,$type='')
	{
		global $core, $_ctx;
		if (!$format) {
			$format = $core->blog->settings->system->time_format;
		}
		
		if ($type == 'upddt') {
			return dt::dt2str($format,$rs->user_upddt,$core->blog->settings->system->blog_timezone);
		} else {
			return dt::dt2str($format,$rs->user_creadt,$core->blog->settings->system->blog_timezone);
		}
	}
}


class rsExtThread
{
	public static function mediaDir($rs)
	{
		return mediaAgora::imagesURL().'/'.md5($rs->user_id).'/';
	}

	public static function hasAvatar($rs)
	{
		return file_exists(mediaAgora::imagesPath().'/'.md5($rs->user_id).'/avatar.jpg');
	}

	public static function messagesActive($rs)
	{
		return
		$rs->core->blog->settings->system->allow_comments
		&& rsExtThread::checkIfMessagesOpen($rs)
		&& ($rs->core->blog->settings->system->comments_ttl == 0 ||
		time()-($rs->core->blog->settings->system->comments_ttl*86400) < $rs->getTS());
	}

	public static function authMe($rs)
	{
		return
		$rs->user_id == $rs->core->auth->userID();
	}

	public static function hasMessages($rs)
	{
		return ((string)$rs->getPostMetaInfo('nb_message') - 1) > 0;
	}
	
	public static function isStillinTime($rs)
	{
		# If user is admin or contentadmin, true
		if ($rs->core->auth->check('contentadmin',$rs->core->blog->id)) {
			return true;
		}
		$now = dt::toUTC(time());
		$now_tz = $now + dt::getTimeOffset($rs->post_tz,$now);
		# Post timestamp
		$post_ts = strtotime($rs->post_dt);
		# If now_tz >= post_ts, we publish the entry
		if ($now_tz >= $post_ts + 300) {
			return false;
		} else {
			return true;
		}
	}

	public static function isPublished($rs)
	{
		if ($rs->post_status == 1){
			return true;
		} else {
			return false;
		}
	}

	public static function getEditURL($rs)
	{
		return $rs->core->blog->url.$rs->core->url->getURLFor("editpost",$rs->post_id);
	}

	public static function getPublishURL($rs)
	{
		return $rs->core->blog->url.$rs->core->url->getURLFor("publishpost",$rs->post_id);
	}

	public static function getUnpublishURL($rs)
	{
		return $rs->core->blog->url.$rs->core->url->getURLFor("unpublishpost",$rs->post_id);
	}

	public static function getStatus($rs)
	{
		return __($rs->core->blog->getPostStatus($rs->post_status));
	}
	
	/**
	Returns whether post is editable.
	
	@param	rs	Invisible parameter
	@return	<b>boolean</b>
	*/
	public static function isEditable($rs)
	{
		global $_ctx;
		
		# If user is admin or contentadmin, true
		if ($rs->core->auth->check('contentadmin',$rs->core->blog->id)) {
			return true;
		}
		
		# No user id in result ? false
		if (!$rs->exists('user_id')) {
			return false;
		}
		
		# If user is member and owner of the post
		if ($rs->core->agora->checkPermission($rs->user_id,'member')
		&& $rs->user_id == $rs->core->auth->userID()) {
			return rsExtThread::isStillinTime($rs);
		}
		
		return false;
	}

	public static function getPostMetaInfo($rs,$info)
	{
		return $rs->core->meta->getMetaRecordset($rs->post_meta,$info)->meta_id;
	}
	
	public static function getMessagesCount($rs)
	{
		return ($rs->getPostMetaInfo('nb_message') - 1);
	}
	
	public static function checkIfMessagesOpen($rs)
	{
		return ($rs->getPostMetaInfo('post_open_message') -1);
	}

	public static function getMemberCN($rs)
	{
		return dcUtils::getUserCN($rs->user_id, $rs->user_name,
		$rs->user_firstname, $rs->user_displayname);
	}

	public static function getMemberProfile($rs)
	{
		global $core;
		$res = '<a href="'.$core->blog->url.$core->url->getURLFor("profile",$rs->user_id).'">%1$s</a>';
		
		return sprintf($res,html::escapeHTML($rs->getMemberCN()));
	}

	public static function isModerator($rs)
	{
		global $core, $_ctx;
		
		return $core->agora->isModerator($rs->user_id);
	}
}

class rsExtMessage
{
	public static function mediaDir($rs)
	{
		return mediaAgora::imagesURL().'/'.md5($rs->user_id).'/';
	}

	public static function hasAvatar($rs)
	{
		return file_exists(mediaAgora::imagesPath().'/'.md5($rs->user_id).'/avatar.jpg');
	}

	public static function isStillinTime($rs)
	{
		# If user is admin or contentadmin, true
		if ($rs->core->auth->check('contentadmin',$rs->core->blog->id)) {
			return true;
		}
		
		$now = dt::toUTC(time());
		$now_tz = $now + dt::getTimeOffset($rs->message_tz,$now);
		# Post timestamp
		$message_ts = strtotime($rs->message_dt);
		# If now_tz >= post_ts, we publish the entry
		if ($now_tz >= $message_ts + 300) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	Returns whether post is editable.
	
	@param	rs	Invisible parameter
	@return	<b>boolean</b>
	*/
	public static function isEditable($rs)
	{
		# If user is admin or contentadmin, true
		if ($rs->core->auth->check('contentadmin',$rs->core->blog->id)) {
			return true;
		}
		
		# No user id in result ? false
		if (!$rs->exists('user_id')) {
			return false;
		}
		

		# If user is member and owner of the message
		if ($rs->core->agora->checkPermission($rs->user_id,'member')
		&& $rs->user_id == $rs->core->auth->userID()) {
			return rsExtMessage::isStillinTime($rs);
		}
		
		return false;
	}

	public static function isPublished($rs)
	{
		if ($rs->message_status == 1){
			return true;
		} else {
			return false;
		}
	}

	public static function getEditURL($rs)
	{
		return $rs->core->blog->url.$rs->core->url->getURLFor("editmessage",$rs->message_id);
	}

	public static function getPublishURL($rs)
	{
		return $rs->core->blog->url.$rs->core->url->getURLFor("publishmessage",$rs->message_id);
	}

	public static function getUnpublishURL($rs)
	{
		return $rs->core->blog->url.$rs->core->url->getURLFor("unpublishmessage",$rs->message_id);
	}

	public static function getStatus($rs)
	{
		return __($rs->core->agora->getMessageStatus($rs->message_status));
	}
	
	public static function getContent($rs,$absolute_urls=false)
	{
		if ($absolute_urls) {
			return html::absoluteURLs($rs->message_content_xhtml,$rs->getPostURL());
		} else {
			return $rs->message_content_xhtml;
		}
	}

	public static function getPostURL($rs)
	{
		return $rs->core->blog->url.$rs->core->getPostPublicURL(
				$rs->post_type,html::sanitizeURL($rs->post_url)
			);
	}

	public static function getDate($rs,$format,$type='')
	{
		if (!$format) {
			$format = $rs->core->blog->settings->system->date_format;
		}
		
		if ($type == 'upddt') {
			return dt::dt2str($format,$rs->message_upddt,$rs->message_tz);
		} elseif ($type == 'creadt') {
			return dt::dt2str($format,$rs->message_creadt,$rs->message_tz);
		} else {
			return dt::dt2str($format,$rs->message_dt);
		}
	}
	
	public static function getTime($rs,$format,$type='')
	{
		if (!$format) {
			$format = $rs->core->blog->settings->system->time_format;
		}
		
		if ($type == 'upddt') {
			return dt::dt2str($format,$rs->message_upddt,$rs->message_tz);
		} elseif ($type == 'creadt') {
			return dt::dt2str($format,$rs->message_creadt,$rs->message_tz);
		} else {
			return dt::dt2str($format,$rs->message_dt);
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
	
	public static function isMe($rs)
	{
		return
		$rs->user_id == $rs->post_user_id;
	}

	public static function authMe($rs)
	{
		return
		$rs->user_id == $rs->core->auth->userID();
	}

	public static function getAuthorCN($rs)
	{
		return dcUtils::getUserCN($rs->user_id, $rs->user_name,
		$rs->user_firstname, $rs->user_displayname);
	}
		
	public static function getAuthorLink($rs)
	{
		$res = '%1$s';
		$url = $rs->user_url;
		if ($url) {
			$res = '<a href="%2$s">%1$s</a>';
		}
		
		return sprintf($res,html::escapeHTML($rs->getAuthorCN()),html::escapeHTML($url));
	}

	public static function getMemberProfile($rs)
	{
		global $core;
		$res = '<a href="'.$core->blog->url.$core->url->getURLFor("profile",$rs->user_id).'">%1$s</a>';
		
		return sprintf($res,html::escapeHTML($rs->getMemberCN()));
	}

	public static function isModerator($rs)
	{
		global $core, $_ctx;
		
		return $core->agora->isModerator($rs->user_id);
	}
}
?>
