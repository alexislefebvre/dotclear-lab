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
# This file is part of TwitterTrackback
# Hadrien Lanneau http://www.alti.info/pages/TwitterTrackback-extension-pour-trackbacker-les-tweets-retrolien-plugin-dotclear
#
/**
* TwitterTrackback
*/
class TwitterTrackback
{
	/*dtd
	<!ELEMENT tpl:CommentIsTweet - - -- Test if current comment is a twitter trackback -->
	>
	*/
	public function commentIsTweet($attr, $content)
	{
		return
			'<?php if ($_ctx->comments->comment_trackback == 1 and ' .
			'preg_match(' .
				'\'@^https?://(www\.)?twitter.com/(.*?)/status/(.*?)$@\', ' .
				'$_ctx->comments->comment_site' .
			')) { ?>' . $content . '<?php } ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:TwitterAvatar - O -- Twitter avatar from a tweet trackback -->
	<!ATTLIST tpl:EntryIf
	classname	CDATA	#IMPLIED	-- Class name to add to img tag
	size		CDATA	#IMPLIED	-- Image size
	>
	*/
	public function twitterAvatar($attr)
	{
		return
			'<?php if ($_ctx->comments->comment_trackback == 1 and ' .
			'preg_match(' .
				'\'@^https?://(www\.)?twitter.com/(.*?)/status/(.*?)$@\', ' .
				'$_ctx->comments->comment_site,' .
				'$m' .
			')) { echo \'<img ' .
				'src="\' . $core->blog->url . \'twitter_avatar_\' . $m[2] . \'.png" ' .
				'alt="\' . $m[2] . \'" ' .
				(isset($attr['classname']) ? 'class="' . $attr['classname'] . '"' : '') . 
				(isset($attr['size']) ? 'width="' . $attr['size'] . '"' : '') . 
				'/>\'; } ?>';
	}
	
	//--------------------------------------------------------------------------
	// Public methods
	//--------------------------------------------------------------------------
	
	/**
	 * Check referer
	 *
	 * @return void
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	static public function publicBeforeDocument(&$core)
	{
		if (!$core->blog->settings->get(
				'twittertrackback_apikey'
			) or
			$core->blog->settings->get(
				'twittertrackback_apikey'
			) == '')
		{
			return;
		}
		
		preg_match(
			'@^post/(.+)$@',
			$_SERVER['QUERY_STRING'],
			$m
		);
		if (!isset($m[1]) or !$m[1])
		{
			return;
		}
		
		$post_url = $m[1];
		
		// Check lock time
		$lockpath = DC_TPL_CACHE . '/twitter_trackback_locks/' .
		 	substr(md5($post_url), 0, 2) . '/' . substr(md5($post_url), 2);
		
		if (!file_exists(
				$lockpath
			) or
			(time() > filemtime(
				$lockpath
			) + 10 * 60))
		{
			// We can check new comments
			
			// Create lock dir if needed
			if (!file_exists(
					DC_TPL_CACHE . '/twitter_trackback_locks/'
				))
			{
				mkdir(
					DC_TPL_CACHE . '/twitter_trackback_locks/'
				);
			}
			if (!file_exists(
					DC_TPL_CACHE . '/twitter_trackback_locks/' . substr(md5($post_url), 0, 2)
				))
			{
				mkdir(
					DC_TPL_CACHE . '/twitter_trackback_locks/' . substr(md5($post_url), 0, 2)
				);
			}
			
			// Lock this post
			touch($lockpath);
			
			// Ask BackTypeConnect for last comments
			include_once(
				dirname(__FILE__) . '/BackTypeConnect.class.php'
			);
			$btc = new BackTypeConnect(
				$core->blog->settings->get(
					'twittertrackback_apikey'
				)
			);
			$backTypeComments = $btc->getCommentsFromUrl(
				$core->blog->url . '/post/' . $post_url
			);
			
			if (!$backTypeComments or
				$backTypeComments->count() == 0)
			{
				return;
			}
			
			// Check which trackback already exists
			$curPost = $core->blog->getPosts(
				array(
					'post_url'	=> $post_url
				)
			);
			
			if ($curPost->count() == 1)
			{
				$strReq =
				'SELECT comment_site, comment_status
				FROM '.$core->blog->prefix.'comment 
				WHERE post_id = \''.$core->blog->con->escape($curPost->post_id).'\';';
				
				$trackbacks = $core->blog->con->select($strReq);
				$trackbacks->core = $core;
				$trackbacks->extend('rsExtComment');
				
				foreach ($backTypeComments as $btc)
				{
					$createNewTrackback = true;
					
					// If trackback already exists
					while ($trackbacks->fetch())
					{
						// testing existing trackbacks
						if ($trackbacks->comment_site ==
								'http://twitter.com/' .
									$btc->user_name .
									'/status/' .
									$btc->id)
						{
							$createNewTrackback = false;
						}
					}
					
					if ($createNewTrackback)
					{
						$tweetUrl = html::clean(
							'http://twitter.com/' . $btc->user_name . '/status/' . 
							$btc->id
						);

						// Create a new trackback from tweet
						$cur = $core->con->openCursor($core->prefix.'comment');
						$cur->comment_author = ucfirst($btc->user_name) .
							' ' . __('from Twitter');
						$cur->comment_site = $tweetUrl;
						$cur->comment_content = "<!-- TB -->\n" .
							'<p>' . $btc->text . '</p>';
						$cur->post_id = intval($curPost->post_id);
						$cur->comment_trackback = 1;
						$cur->comment_status = $core->blog->settings->trackbacks_pub ? 1 : -1;
						$cur->comment_ip = $_SERVER['SERVER_ADDR'];
						
						// If tweet is yours, made from twitterPost
						// and you don't want to get it
						// We set it as 
						if ($core->blog->settings->get(
								'twittertrackback_preventmytweets'
							) and
							$btc->user_name == $core->blog->settings->get(
									'twitterpost_username'
								) and
							preg_match(
								'@' .
								str_replace(
									array(
										'%title%',
										'%url%'
									),
									array(
										'(.*?)',
										'(.*?)'
									),
									$core->blog->settings->get(
										'twitterpost_status'
									)
								) . '@',
								$btc->text
							))
						{
							$cur->comment_status = 0;
						}
						
						$trackbackId = $core->blog->addComment(
							$cur
						);
						
						$offset = dt::getTimeOffset($core->blog->settings->blog_timezone);
						$cur->comment_upddt = date('Y-m-d H:i:s', $btc->date + $offset);
						$cur->comment_dt = date('Y-m-d H:i:s', $btc->date + $offset);
						$cur->update('WHERE comment_id = '.$trackbackId.' ');
					}
				}
			}
		}
	}
}

/**
* TwitterTrackbackAvatar
*/
class TwitterTrackbackAvatar extends dcUrlHandlers
{
	
	
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public function displayAvatar()
	{
		preg_match(
			'@/twitter_avatar_(.*?)\.png@',
			$_SERVER['REQUEST_URI'],
			$m
		);
		if (isset($m[1]))
		{
			$screenName = $m[1];
			
			$cachedir = DC_TPL_CACHE . '/twitter_avatars/';
			
			// Look in cache
			if (!file_exists(
					$cachedir . substr(md5($screenName), 0, 2) . '/' .
						substr(md5($screenName), 2)
				) or
				(time() > filemtime(
					$cachedir . substr(md5($screenName), 0, 2) . '/' .
						substr(md5($screenName), 2)
				) + 30 * 24 * 60 * 60))
			{
				try
				{
					$user = netHttp::quickGet(
						'http://twitter.com/users/show.xml?screen_name=' . $screenName
					);
					$user = simplexml_load_string(
						$user
					);
					$avatar = strval(
						$user->profile_image_url
					);
				}
				catch (Exception $e)
				{
					$avatar = 'http://s.twimg.com/a/1250203207/images/default_profile_bigger.png';
				}
				
				// Cache avatar
				if (!file_exists($cachedir))
				{
					mkdir($cachedir);
				}
				if (!file_exists($cachedir . substr(md5($screenName), 0, 2)))
				{
					mkdir($cachedir . substr(md5($screenName), 0, 2));
				}
				files::putContent(
					$cachedir . substr(md5($screenName), 0, 2) . '/' .
						substr(md5($screenName), 2),
					netHttp::quickGet(
						$avatar
					)
				);
			}
			
			// Return image
			header('Content-type: image/png');
			echo file_get_contents(
				$cachedir . substr(md5($screenName), 0, 2) . '/' .
					substr(md5($screenName), 2)
			);
		}
	}
}
