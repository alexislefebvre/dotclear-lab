<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of TwitterPost.
# Copyright (c) 2009 Hadrien Lanneau.
# All rights reserved.
#
# Pixearch is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# Pixearch is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with Pixearch; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# http://www.alti.info/pages/TwitterPost
#
# ***** END LICENSE BLOCK *****
/**
* TwitterPost
*/
class TwitterPost
{
	/**
	 * Insert checkbox for twitter into post form sidebar
	 *
	 * @return void
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public static function initPostFormSidebar(&$post)
	{
		echo '<h3>';
		echo '<label for="twitterpost_twit">';
		echo __('Twitter Post :');
		echo '</label';
		echo '</h3>';
		
		echo '<p class="label"><label class="classic">';
		echo form::checkbox(
			'twitterpost_twit',
			'1',
			false
		);
		echo __('Twit post');
		echo '</label></p>';
	}
	
	/**
	 * Update twitter status if asked before publishing
	 *
	 * @return void
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public static function adminBeforePostUpdate(&$cur, &$post_id)
	{
		global $core;
		
		$username = $core->blog->settings->get(
			'twitterpost_username'
		);
		$password = $core->blog->settings->get(
			'twitterpost_password'
		);
		
		$status = $core->blog->settings->get(
			'twitterpost_status'
		);
		
		if (!empty($_POST['twitterpost_twit']) and
			$_POST['twitterpost_twit'] and
			$username and $password and $status)
		{
			$post = $core->blog->getPosts(
				array(
					'post_id'	=> $post_id
				)
			);
			
			if ($post->post_status != 1)
			{
				throw new Exception(
					__('Twitter Post : Post must be published')
				);
			}
			$c = new netHttp(
				'twitter.com'
			);
			$c->setAuthorization(
				$username,
				$password
			);
			
			$status = str_replace(
				array(
					'%title%',
					'%url%'
				),
				array(
					$post->post_title,
					$post->getURL()
				),
				$status
			);
			
			$twit = $c->post(
				'/statuses/update.xml',
				array(
					'status'	=> $status
				)
			);
			if (!$twit)
			{
				throw new Exception(
					'Error'
				);
			}
		}
	}
}
