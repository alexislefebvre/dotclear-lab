<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2007 Olivier Meunier and contributors.
# All rights reserved.
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
# This file is part of TwitterComments
# Hadrien Lanneau http://www.alti.info/
#


$core->addBehavior('publicBeforeDocument',array('TwitterTrackback','publicBeforeDocument'));

if (!defined('DC_RC_PATH')) { return; }

/**
* TwitterTrackback
*/
class TwitterTrackback
{
	/**
	 * Check referer
	 *
	 * @return void
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	static public function publicBeforeDocument(&$core)
	{
		if (!isset($_SERVER['HTTP_REFERER']) or
			!$_SERVER['HTTP_REFERER'])
		{
			return;
		}
		// Look for same backlink on this post
		preg_match(
			'@^post/(.+)$@',
			$_SERVER['QUERY_STRING'],
			$m
		);
		
		$curPost = $core->blog->getPosts(
			array(
				'post_url'	=> $m[1]
			)
		);
		
		if ($curPost->count() == 1)
		{
			$createNewTrackback = true;
			
			$trackbacks = $core->blog->getComments(
				array(
					'post_id'			=> $curPost->post_id,
					'comment_trackback'	=> 1
				)
			);
			
			if ($trackbacks->count() > 0)
			{
				while ($trackbacks->fetch())
				{
					// testing existing trackbacks
					if ($trackbacks->comment_site == $_SERVER['HTTP_REFERER'])
					{
						$createNewTrackback = false;
					}
				}
			}
			
			if ($createNewTrackback)
			{
				$status = false;
				// Test Twitter
				if (preg_match(
						'@^http://(www\.)?twitter\.com/(.*?)/status/(.*?)$@',
						$_SERVER['HTTP_REFERER'],
						$m
					))
				{
					$status = netHttp::quickGet(
						'http://twitter.com/statuses/show/' .
							$m[3] . '.xml'
					);
					$service = 'twitter';
				}
				
				// Test identi.ca TODO
				if (!$status and
					preg_match(
						'@http://identi.ca/notice/(.*?)$@',
						$_SERVER['HTTP_REFERER'],
						$m
					))
				{
					$status = netHttp::quickGet(
						'http://identi.ca/api/statuses/show/' .
							$m[1] . '.xml'
					);
					$service = 'identi.ca';
				}
				
				if ($status)
				{
					$status = @simplexml_load_string(
						$status
					);
					
					if ($service == 'twitter')
					{
						$tweetUrl = html::clean(
							'http://twitter.com/' . strval($status->user->screen_name) . '/status/' . 
							$status->id
						);
					}
					else
					{
						$tweetUrl = html::clean(
							'http://identi.ca/notice/' . intval($status->id)
						);
					}
					
					// Create a new trackback from tweet
					$cur = $core->con->openCursor($core->prefix.'comment');
					$cur->comment_author = strval($status->user->name) .
						' ' . __('from') . ' ' . $service;
					$cur->comment_site = $tweetUrl;
					$cur->comment_content = "<!-- TB -->\n" .
						'<p><strong>' . $tweetUrl . "</strong></p>\n" .
						'<p>' . strval($status->text) . '</p>';
					$cur->post_id = intval($curPost->post_id);
					$cur->comment_trackback = 1;
					$cur->comment_status = $core->blog->settings->trackbacks_pub ? 1 : -1;
					$cur->comment_ip = http::realIP();
					
					$trackbackId = $core->blog->addComment(
						$cur
					);
				}
			}
		}
	}
}

