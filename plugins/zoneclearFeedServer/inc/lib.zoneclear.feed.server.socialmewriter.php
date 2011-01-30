<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of zoneclearFeedServer, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis, BG and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

# Add ability to send social messages when a feed is update
class zcfsSoCialMeWriter
{
	public static function soCialMeWriterMarker($rs)
	{
		$rs['zcfscreate'] = array(
			'name' => __('New Zoneclear post'),
			'description' => __('When a feed has new entry'),
			'action' => array('Message','Link'),
			'format' => array('Message'),
			'wildcards' => array('Message' => array('%posttitle%','%postlink%','%postauthor%','%posttweeter%','%sitetitle%','%sitelink%','%tags'))
		);
	}
	
	public static function zoneclearFeedServerAfterFeedUpdate($core,$is_new_published_entry,$post,$meta)
	{
		// for now only new post
		if(!$is_new_published_entry) return;
		$key = 'zcfscreate';
		
		# Is install
		if (!$core->moduleExists('soCialMe')) return;
		
		# Is active
		if (!$core->blog->settings->soCialMeWriter->active) return;
		
		# Load services
		$soCialMeWriter = new soCialMeWriter($core);
		
		# List of service per action
		$actions = $soCialMeWriter->getMarker('action');
		
		# List of format per type
		$formats = $soCialMeWriter->getMarker('format');
		
		# prepare data
		$shortposturl = soCialMeWriter::reduceURL($meta->url);
		$shortposturl = $shortposturl ? $shortposturl : $meta->url;
		
		$shortsiteurl = soCialMeWriter::reduceURL($meta->site);
		$shortsiteurl = $shortsiteurl ? $shortsiteurl : $meta->site;
		
		foreach($tags as $k => $tag) { $tags[$k] = '#'.$tag; } // need this?
		
		# sendMessage
		if (!empty($formats[$key]['message']) && !empty($actions[$key]['message']))
		{
			// parse message
			$message_txt = str_replace(
				array('%posttitle%','%postlink%','%postauthor%','%posttweeter%','%sitetitle%','%sitelink%','%tags'),
				array($post->post_title,$shortposturl,$meta->author,$meta->feed_tweeter,$meta->sitename,$shortsiteurl,implode(',',$meta->tags)),
				$formats[$key]['message']
			);
			
			// send message
			if (!empty($message_txt))
			{
				foreach($actions[$key]['message'] as $service_id)
				{
					$soCialMeWriter->send($service_id,'message',$message_txt);
				}
			}
		}
		
		# sendLink
		if (!empty($actions[$key]['link']))
		{
			foreach($actions[$key]['link'] as $service_id)
			{
				$soCialMeWriter->send($service_id,'link',$cur->post_title,$shortposturl);
			}
		}
		
		# sendData
		// not yet implemented
		
		#sendArticle
		// not yet implemented
	}
}
?>