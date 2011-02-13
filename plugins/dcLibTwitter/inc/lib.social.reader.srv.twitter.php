<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibTwitter, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}# Add twitter to plugin soCialMe (reader part)class twitterSoCialMeReaderService extends soCialMeService{	protected $part = 'reader';		protected $define = array(		'id' => 'twitter',		'name' => 'Twitter',		'home' => 'http://twitter.com',		'icon' => 'pf=dcLibTwitter/icon.png'	);		protected $actions = array(
		'playServerScript' => true,
		'playWidgetContent' => true,
		'playPageContent' => true,
		'playCommentContent' => true	);		private $oauth = false;
	private $cache_timeout = 300; //5 minutes
	private $tweets_returned = 10;
	private $retweets_show = 1;		protected function init()	{		# Required plugin oAuthManager		# Used name of parent plugin		if (soCialMeUtils::checkPlugin('oAuthManager','0.1'))		{			$this->oauth = oAuthClient::load($this->core,'twitter',				array(					'user_id' => null,					'plugin_id' => 'soCialMeReader',					'plugin_name' => __('SoCialMe Reader'),					'token' => 'fjWBGGA5qkR009ZikITvQ',					'secret' => '1Une37GYVs3Xn0zMHAcX5kq1KFfos2uMrwXd5aJ9U'				)			);		}				if (false === $this->oauth)		{			$this->available = false;			return false;		}				$this->available = true;		return true;	}		public function adminSave($service_id,$admin_url)	{		if (!$this->available || $service_id != $this->id) return;				$request_step = !empty($_REQUEST['step']) ? $_REQUEST['step'] : null;				if (!$request_step)		{			return;		}		elseif ($request_step == 'request')		{			$this->oauth->getRequestToken($admin_url.'&step=callback');		}		elseif ($request_step == 'callback')		{			$this->oauth->getAccessToken();		}		elseif ($request_step == 'clean')		{			$this->oauth->removeToken();		}	}		public function adminForm($service_id,$admin_url)	{		if (!$this->available) return;		$admin_url = str_replace('&','&amp;',$admin_url);				$res = '<p>';		if ($this->oauth->state() == 1)		{			$res .= '<a class="button" href="'.$admin_url.'&amp;step=clean">'.sprintf(__('Something went wrong, clean acces of %s from %s'),$this->oauth->config('plugin_name'),$this->oauth->config('client_name')).'</a>';		}		elseif ($this->oauth->state() == 2)		{
			$user = $this->oauth->getScreenName();
			if ($user)
			{
				$res .= '<p>'.sprintf(__('Your are connected as "%s"'),$user).'</p>';
			}			$res .= '<a class="button" href="'.$admin_url.'&amp;step=clean">'.sprintf(__('Disconnet %s from %s'),$this->oauth->config('plugin_name'),$this->oauth->config('client_name')).'</a>';		}		elseif ($this->oauth->state() == 0)		{			$res .= '<a class="button" href="'.$admin_url.'&amp;step=request">'.sprintf(__('Connect %s to %s'),$this->oauth->config('plugin_name'),$this->oauth->config('client_name')).'</a>';		}		$res .= '</p>';				return $res;	}
	
	# Put user timeline into cache file
	public function playServerScript($available)
	{
		if (!$this->available || $this->oauth->state() != 2) return;
		
		#
		# Cache for user timeline
		#
		
		# cache filename
		$file_user_timeline = $this->core->blog->id.$this->id.'user_timeline';
		
		# check cache expiry
		if((isset($available['Widget']) && in_array($this->id,$available['Widget']) 
		 || isset($available['Page']) && in_array($this->id,$available['Page'])) 
		&& soCialMeCacheFile::expired($file_user_timeline,'enc',$this->cache_timeout))
		{
			# call API
			$params = array(
				'count' => $this->tweets_returned,
				'include_rts' => (integer) $this->retweets_show
			);
			$rs = $this->oauth->get('statuses/user_timeline',$params);
			
			if ($rs)
			{
				# Parse response
				$records = null;
				$i = 0;
				foreach($rs as $record)
				{
					$records[$i]['service'] = $this->id;
					$records[$i]['author'] = $record->user->screen_name;
					$records[$i]['source_name'] = $this->name;
					$records[$i]['source_url'] = $this->home;
					$records[$i]['source_icon'] = $this->icon;
					
					if (!empty($record->retweeted_status))
					{
						$records[$i]['me'] = false;
						$records[$i]['title'] = sprintf(__('%s retweets what %s says'),$record->user->screen_name,$record->retweeted_status->user->screen_name);
						$records[$i]['date'] = twitterUtils::dateToTime($record->retweeted_status->created_at,$record->retweeted_status->user->time_zone);
						$records[$i]['content'] = twitterUtils::textToHTML($record->retweeted_status->text);
						$records[$i]['avatar'] = twitterUtils::profileImgURL($record->retweeted_status->user->profile_image_url);
						$records[$i]['icon'] = twitterUtils::profileImgURL($record->retweeted_status->user->profile_image_url,true);
						$records[$i]['url'] = 'http://twitter.com/'.$record->retweeted_status->user->screen_name.'/status/'.$record->retweeted_status->id_str;
					}
					else
					{
						$records[$i]['me'] = true;
						$records[$i]['title'] = $record->in_reply_to_screen_name ?
							sprintf(__('%s says in reply to %s'),$record->user->screen_name,$record->in_reply_to_screen_name) :
							sprintf(__('%s says'),$record->user->screen_name);
						$records[$i]['date'] = twitterUtils::dateToTime($record->created_at,$record->user->time_zone);
						$records[$i]['content'] = twitterUtils::textToHTML($record->text);
						$records[$i]['avatar'] = twitterUtils::profileImgURL($record->user->profile_image_url);
						$records[$i]['icon'] = twitterUtils::profileImgURL($record->user->profile_image_url,true);
						$records[$i]['url'] = 'http://twitter.com/'.$record->user->screen_name.'/status/'.$record->id_str;
					}
					$i++;
				}
				# Create cache file
				if (!empty($records)) {
					soCialMeCacheFile::write($file_user_timeline,'enc',soCialMeUtils::encode($records));
				}
			}
		}
		
		#
		# Comment and trackback
		#
		
		global $_ctx;
		
		# check post context
		if (isset($available['Comment']) && in_array($this->id,$available['Comment']) 
		&& $_ctx->exists('posts'))
		{
			# cache filename
			$file_post_trackback = $this->core->blog->id.$this->id.$_ctx->posts->post_id.'post_trackback';
			
			# check cache expiry
			if(soCialMeCacheFile::expired($file_post_trackback,'enc',$this->cache_timeout))
			{
				# Search URL of this post
				$url = $_ctx->posts->getURL();
				$shorturl = soCialMeUtils::reduceURL($url);
				$searches = twitterUtils::search($url,$shorturl);
				$results = $searches->results;
				
				if (!$results) {
					
				}
				else
				{
					# Get trackbacks of this post
					$params = array(
						'post_id' => $_ctx->posts->post_id,
						'comment_trackback' => 1
					);
					$trackbacks = $this->core->blog->getComments($params);
				
					# Compare search results and post trackbacks
					foreach($results as $k => $result)
					{
						$site = 'http://twitter.com/'.$result->from_user_id_str.'/status/'.$result->id_str;
						$is_new = true;
						while($trackbacks->fetch())
						{
							if ($trackbacks->comment_site == $site) $is_new = false;
						}
						# create new trackbacks
						if ($is_new)
						{
							# preprare record
							$cur = $this->core->con->openCursor($this->core->prefix.'comment');
							$cur->comment_author = sprintf(__('%s from %s'),$result->from_user,$this->name);
							$cur->comment_email = $result->from_user.'@twitter'; //for noodles
							$cur->comment_site = $site;
							$cur->comment_content = "<!-- TB -->\n<p>".$result->text."</p>\n";
							$cur->post_id = $_ctx->posts->post_id;
							$cur->comment_trackback = 1;
							$cur->comment_status = $this->core->blog->settings->system->trackbacks_pub ? 1 : -1;
							$cur->comment_ip = $_SERVER['SERVER_ADDR'];
							# add trackback
							try
							{
								$id = $this->core->auth->sudo(array($this->core->blog,'addComment'),$cur);
								
								# update trackback date
								$time = strtotime($result->created_at);
								$offset = dt::getTimeOffset($this->core->blog->settings->blog_timezone);
								$cur->comment_upddt = date('Y-m-d H:i:s', $time + $offset);
								$cur->comment_dt = date('Y-m-d H:i:s', $time + $offset);
								$cur->update('WHERE comment_id = '.$id);
							}
							catch (Exception $e) { }
						}
					}
				}
				soCialMeCacheFile::write($file_post_trackback,'enc',' ');
			}
		}
	}
	
	public function playWidgetContent()
	{
		return self::parseContent();
	}
	
	public function playPageContent()
	{
		return self::parseContent();
	}
	
	private function parseContent()
	{
		if (!$this->available) return;
		# cache filename
		$file = $this->core->blog->id.$this->id.'user_timeline';
		# Read cache content
		$content = soCialMeCacheFile::read($file,'enc');
		if (empty($content)) return;
		# Parse content
		return soCialMeUtils::decode($content);
	}
	
	public static function playCommentContent($post_id)
	{
		// nothing to do here. All is done in playServerScript
		// but this func must exist
	}
}?>