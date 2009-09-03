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
* BackTypeConnect
* Help connects to Backtype.com services
*/
class BackTypeConnect
{
	/**
	 * undocumented class variable
	 *
	 * @var string
	 **/
	private $_apiUrl = 'api.backtype.com';
	
	/**
	 * Api key
	 *
	 * @var string
	 **/
	private $_apiKey = null;
	
	/**
	 * Construct with apikey
	 *
	 * @return void
	 * @author Hadrien Lanneau (hadrien at over-blog dot com)
	 **/
	public function __construct($apikey = null)
	{
		if (is_null($apikey))
		{
			throw new Exception('Api key is mandatory');
		}
		
		$this->_apiKey = $apikey;
	}
	
	/**
	 * Get comments from an url
	 *
	 * @return ArrayObject
	 * @author Hadrien Lanneau (hadrien at over-blog dot com)
	 **/
	public function getCommentsFromUrl($url = null)
	{
		$c = new netHttp(
			$this->_apiUrl
		);
		$c->get(
			'/comments/connect.json',
			array(
				'url'			=> $url,
				'key'			=> $this->_apiKey,
				'sort'			=> 1
			)
		);
		
		if ($c->getStatus() == 200)
		{
			$query = json_decode(
				$c->getContent()
			);
			
			$retArr = new ArrayObject();
			foreach (array_reverse($query->comments) as $q)
			{
				if ($q->entry_type == 'tweet')
				{
					$retArr[] = new BackTypeTweet(
						$q->tweet_id,
						$q->tweet_from_user_id,
						$q->tweet_from_user,
						$q->tweet_profile_image_url,
						$q->tweet_created_at,
						$q->tweet_text
					);
				}
				// TODO : traiter les autres types de commentaires
			}
			
			return $retArr;
		}
		
		return false;
	}
}

/**
* BackTypeComment
*/
class BackTypeTweet
{
	/**
	 * Tweet ID
	 *
	 * @var string
	 **/
	private $_id = null;
	
	/**
	 * User ID
	 *
	 * @var string
	 **/
	private $_user_id = null;
	
	/**
	 * User name
	 *
	 * @var string
	 **/
	private $_user_name = null;
	
	/**
	 * user_avatar
	 *
	 * @var string
	 **/
	private $_user_avatar = null;
	
	/**
	 * Tweet publishing date
	 *
	 * @var date
	 **/
	private $_date = null;
	
	/**
	 * Tweet text
	 *
	 * @var string
	 **/
	private $_text = null;
	
	
	function __construct(
		$id = null,
		$user_id = null,
		$user_name = null,
		$user_avatar = null,
		$date = null,
		$text = null)
	{
		if (is_null($id) or
			is_null($user_id) or
			is_null($user_name) or
			is_null($user_avatar) or
			is_null($date) or
			is_null($text))
		{
			throw new Exception('Parameters are missing');
		}
		
		$this->_id = strval($id);
		$this->_user_id = strval($id);
		$this->_user_name = strval($user_name);
		$this->_user_avatar = strval($user_avatar);
		$this->_date = strtotime($date);
		$this->_text = strval($text);
	}
	
	/**
	 * magick get
	 *
	 * @return void
	 * @author Hadrien Lanneau (hadrien at over-blog dot com)
	 **/
	public function __get($key)
	{
		if (isset($this->{'_' . $key}))
		{
			return $this->{'_' . $key};
		}
		
		return null;
	}
}


