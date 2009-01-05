<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Pixearch
# Copyright (c) 2008 Hadrien Lanneau http://www.alti.info/
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
# ***** END LICENSE BLOCK *****
#

/**
* PictWebService
*/
abstract class Pixearch
{
	/**
	 * Webservice URI
	 *
	 * @var string
	 **/
	protected $wsuri;
	
	/**
	 * Api key
	 *
	 * @var string
	 **/
	protected $apikey;
	
	/**
	 * Number of search pages
	 *
	 * @var integer
	 **/
	public $nbElements = 0;
	
	/**
	 * Prefix for cached files
	 *
	 * @var string
	 **/
	protected $_cachePrefix = '';
	
	/**
	 * Singleton
	 *
	 * @var Pixearch object
	 **/
	protected static $singleton = null;
	
	/**
	 * Open a connection to Flickr
	 *
	 * @param {string} $apikey API key
	 * @return void
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public function __construct($apikey = null)
	{
		if (is_null(self::$singleton))
		{
			$this->apikey = $apikey;
			
			self::$singleton = $this;
		}
		else
		{
			$this->apikey = self::$singleton->_getApiKey();
		}
	}
	
	/**
	 * Get API Key
	 *
	 * @return string
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public function _getApiKey()
	{
		return $this->apikey;
	}
	
	/**
	 * Cache last search results to easily retrieve selected photo
	 *
	 * @return Boolean
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	protected function _setCache(
		$type = 'search', $xml = null)
	{
		if (!is_string($type))
		{
			throw new Exception('Type must be a string');
		}
		$cacheFolder = dirname(__FILE__) . '/../cache';
		
		if (!file_exists($cacheFolder))
		{
			mkdir($cacheFolder);
		}
		
		return file_put_contents(
			$cacheFolder . '/' . $this->_cachePrefix . $type,
			serialize($xml)
		);
	}
	
	/**
	 * Get last cached result
	 *
	 * @return simple_xml Object
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	protected function _getCache($type = 'search')
	{
		if (!is_string($type))
		{
			throw new Exception('Type must be a string');
		}
		$cacheFile = dirname(__FILE__) . '/../cache/' .
			$this->_cachePrefix . $type;
		
		return unserialize(
			@file_get_contents(
				$cacheFile
			)
		);
	}
}

class PxSFlickr extends Pixearch
{
	/**
	 * Flickr webservice URI
	 *
	 * @var string
	 **/
	protected $wsuri = 'http://api.flickr.com/services/rest/';
	
	/**
	 * Search pictures from keyword
	 *
	 * @param {string} $keyword Keywords to search
	 * @param {integer} $offset Page number
	 * @param {integer} $limit Number of items per page
	 * @return ArrayObject of PxSFlickrPicture objects
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public function search(
		$keyword = '',
		$offset = 1,
		$limit = 20)
	{
		if (!is_string($keyword))
		{
			throw new Exception('Keyword must be a string');
		}
		if (trim($keyword) == '')
		{
			return new ArrayObject();
		}
		
		// do request
		$picturesRaw = $this->callMethod(
			'flickr.photos.search',
			array(
				'text'				=> urlencode($keyword),
				'per_page'			=> $limit,
				'page'				=> $offset,
				'privacy_filter'	=> 1,
				'extras'			=> 'owner_name',
				'sort'				=> 'relevance'
			)
		);
		return $this->_createPictures(
			$picturesRaw
		);
	}
	
	/**
	 * Call a method
	 *
	 * @return xml
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public function callMethod(
		$method = null,
		$params = array())
	{
		$paramsstring = '';
		foreach ($params as $k => $p)
		{
			$paramsstring .= '&' . $k . '=' . $p;
		}
		
		$xml = HttpClient::quickGet(
			$this->wsuri . '?method=' . $method .
				'&api_key=' . $this->apikey . $paramsstring
		);
		if (!$xml)
		{
			throw new Exception('Connexion lost');
		}
		
		return simplexml_load_string(
			$xml
		);
	}
	
	/**
	 * Transform an xml from Flickr to an object that we can use
	 *
	 * @param {string} $xml XML response from Flickr webservice
	 * @return ArrayObject of PxSFlickrPicture objects
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	private function _createPictures($xml = null)
	{
		if (!$xml)
		{
			throw new Exception('Invalid XML');
		}
		if (isset($xml->err['msg']))
		{
			throw new Exception($xml->err['msg']);
		}
		if (!is_object($xml))
		{
			throw new Exception('Connexion lost');
		}
		
		$this->nbElements = intval(
			$xml->photos['total']
		);
		
		$pictures = new ArrayObject();
		
		foreach ($xml->photos->photo as $p)
		{
			$pictures[] = new PxSFlickrPicture(
				$p['id'],
				$p['title'],
				$p['farm'],
				$p['server'],
				$p['secret'],
				$p['owner'],
				$p['ownername']
			);
		}
		
		return $pictures;
	}
}

/**
* Photobucket webservice
*/
class PxSPhotoBucket extends Pixearch
{
	/**
	 * PhotoBucket webservice URI
	 *
	 * @var string
	 **/
	protected $wsuri = 'http://api.photobucket.com/';
	
	/**
	 * Signature key
	 *
	 * @var string
	 **/
	protected $signature;
	
	/**
	 * Open a connection to Flickr
	 *
	 * @param {string} $apikey API key
	 * @return void
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public function __construct(
		$apikey = null,
		$signature = null)
	{
		if (is_null(self::$singleton))
		{
			$this->apikey = $apikey;
			
			$this->signature = $signature;
			
			self::$singleton = $this;
		}
		else
		{
			$this->apikey = self::$singleton->apikey;
			$this->signature = self::$singleton->signature;
		}
	}
	
	/**
	 * Search pictures from keyword
	 *
	 * @param {string} $keyword Keywords to search
	 * @param {integer} $offset Page number
	 * @param {integer} $limit Number of items per page
	 * @return ArrayObject of PxSPhotoBucketPicture objects
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public function search(
		$keyword = '',
		$offset = 1,
		$limit = 20)
	{
		if (!is_string($keyword))
		{
			throw new Exception('Keyword must be a string');
		}
		if (trim($keyword) == '')
		{
			return new ArrayObject();
		}
		
		// do request
		$xml = $this->callMethod(
			'search/' . urlencode($keyword) . '/image',
			array(
				'format'	=> 'xml',
				'perpage'	=> $limit,
				'page'		=> $offset
			)
		);
		
		return $this->_createPictures(
			$xml
		);
	}
	
	/**
	 * Call a method
	 *
	 * @return simple_xml object
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public function callMethod(
		$method = '',
		$params = array()
		)
	{
		$xml = HttpClient::quickGet(
			$this->_constructRequest(
				$method,
				$params
			)
		);
		if (!xml)
		{
			throw new Exception('Connexion lost');
		}
		return simplexml_load_string(
			$xml
		);
	}
	
	/**
	 * Construct an url with oauth signature
	 *
	 * @return string
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	private function _constructRequest($method, $params = '')
	{
		include_once(dirname(__FILE__) . '/OAuth.php');
		$token = new OAuthToken(
			$this->apikey,
			$this->signaturekey
		);
		$consumer = new OAuthConsumer(
			$this->apikey,
			$this->signature
		);
		$request = OAuthRequest::from_consumer_and_token(
			$consumer,
			null,
			'GET',
			$this->wsuri . $method,
			$params
		);
		$request->set_parameter(
			'oauth_signature_method',
			'HMAC-SHA1'
		);
		$request->sign_request(
			new OAuthSignatureMethod_HMAC_SHA1(),
			$consumer,
			null
		);
		
		return $request->to_url();
	}
	
	/**
	 * Transform an xml from PhotoBucket to an object that we can use
	 *
	 * @param {string} $xml XML response from PhotoBucket webservice
	 * @return ArrayObject of PxSPhotoBucketPicture objects
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	private function _createPictures($xml = null)
	{
		if (!is_object($xml))
		{
			throw new Exception('Connexion lost');
		}
		if (isset($xml->err['msg']))
		{
			throw new Exception($xml->err['msg']);
		}
		
		$this->nbElements = intval(
			$xml->content->result['totalresults']
		);
		
		$pictures = new ArrayObject();
		
		foreach ($xml->content->result->primary->media as $p)
		{
			$pictures[] = new PxSPhotoBucketPicture(
				strval($p->url),
				strval($p->title),
				strval($p->thumb),
				strval($p->browseurl),
				strval($p['username']),
				strval($p->albumurl)
			);
		}
		
		return $pictures;
	}
}

/**
* DeviantArt
*/
class PxSDeviantArt extends Pixearch
{
	/**
	 * Webservices uri
	 *
	 * @var string
	 **/
	protected $wsuri = 'http://backend.deviantart.com/rss.xml?type=deviation&q=';
	
	/**
	 * Prefix for cached files
	 *
	 * @var string
	 **/
	protected $_cachePrefix = 'dA_';
	
	function __construct()
	{
		
	}
	
	/**
	 * Search keyword
	 *
	 * @return PxSDeviantArtPictures Objects
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public function search(
		$keyword = '',
		$offset = 1,
		$limit = 20)
	{
		if (!is_string($keyword))
		{
			throw new Exception('Keyword must be a string');
		}
		if (trim($keyword) == '')
		{
			return new ArrayObject();
		}
		
		$rss = $this->callMethod(
			'search',
			array(
				'type'		=> 'deviation',
				'offset'	=> $offset,
				'limit'		=> $limit,
				'keyword'	=> $keyword
			)
		);
		
		if (!is_object($rss))
		{
			throw new Exception('Connexion lost');
		}
		
		$pictures = new ArrayObject();
		foreach ($rss->channel->item as $i)
		{
			$pictures[] = new PxSDeviantArtPicture(
				strval($i->guid), // ID
				strval($i->media_title), // title
				strval($i->media_thumbnail[1]['url']), // thumb
				strval($i->media_thumbnail[0]['url']), // medium
				strval($i->media_content[0]['url']), // original
				strval($i->media_content[1]['url']), // browserurl
				strval($i->media_credit[0]), // ownername
				strval($i->media_copyright['url']) // owneruri
			);
		}
		
		if ($pictures->count() == $limit)
		{
			$this->nbElements = floatval(($limit + 1) * $offset);
		}
		
		
		$this->_setCache(
			'search',
			$pictures
		);
		
		return $pictures;
	}
	
	/**
	 * Call a method
	 *
	 * @return simplexml_load_string
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public function callMethod(
		$method = null,
		$params = array())
	{
		switch ($method)
		{
			case 'search':
				$paramsstring = '';
				foreach ($params as $k => $p)
				{
					if ($k == 'keyword')
					{
						$keyword = $p;
					}
					else
					{
						$paramsstring .= '&' . $k . '=' . $p;
					}
				}
				
				return simplexml_load_string(
					str_replace(
						array(
							'media:title',
							'media:thumbnail',
							'media:content',
							'media:credit',
							'media:copyright'
						),
						array(
							'media_title',
							'media_thumbnail',
							'media_content',
							'media_credit',
							'media_copyright'
						),
						HttpClient::quickGet(
							$this->wsuri . 'boost%3Apopular%20' .
							$keyword .
							$paramsstring
						)
					)
				);
				break;
			case 'getFromId':
				if (!isset($params['id']))
				{
					throw new Exception('ID is needed');
				}
				$lastSelected = $this->_getCache(
					'selected'
				);
				if (urldecode($lastSelected->id) == $params['id'])
				{
					return $lastSelected;
				}
				$lastSearch = $this->_getCache(
					'search'
				);
				foreach ($lastSearch as $p)
				{
					if (urldecode($p->id) == $params['id'])
					{
						$this->_setCache(
							'selected',
							$p
						);
						return $p;
					}
				}
				throw new Exception('Bad image');
				break;
			default:
				throw new Exception('Unknown method');
		}
	}
}

/**
* PxSPicasa
*/
class PxSPicasa extends Pixearch
{
	/**
	 * Picasa webservice URI
	 *
	 * @var string
	 **/
	protected $wsuri =
		'http://picasaweb.google.com/data/feed/api/all?kind=photo';
	
	/**
	 * Prefix for cached files
	 *
	 * @var string
	 **/
	protected $_cachePrefix = 'Pcs_';
	
	function __construct()
	{
		
	}
	
	/**
	 * Search pictures from keyword
	 *
	 * @param {string} $keyword Keywords to search
	 * @param {integer} $offset Page number
	 * @param {integer} $limit Number of items per page
	 * @return ArrayObject of PxSPicasaPicture objects
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public function search(
		$keyword = '',
		$offset = 1,
		$limit = 20)
	{
		if (!is_string($keyword))
		{
			throw new Exception('Keyword must be a string');
		}
		if (trim($keyword) == '')
		{
			return new ArrayObject();
		}
		
		// do request
		$rss = $this->callMethod(
			'search',
			array(
				'q'					=> urlencode($keyword),
				'max-results'		=> $limit,
				'start-index'		=> $offset,
				'thumbsize'			=> 64,
				'imgmax'			=> 640
				
			)
		);
		
		if (!is_object($rss))
		{
			throw new Exception('Connexion lost');
		}
		
		$pictures = new ArrayObject();
		foreach ($rss->entry as $p)
		{
			$pictures[] = new PxSPicasaPicture(
				strval($p->id), // ID
				strval($p->title), // title
				strval($p->media_group->media_thumbnail['url']), // thumb
				strval($p->media_group->media_thumbnail['url']), // medium
				strval($p->media_group->media_content['url']), // original
				strval($p->link[1]['href']), // browserurl
				strval($p->author->name), // ownername
				strval($p->author->uri) // owneruri
			);
		}
		
		$this->nbElements = intval($rss->openSearch_totalResults);
		
		return $pictures;
	}
	
	/**
	 * Call a method
	 *
	 * @return xml
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public function callMethod(
		$method = null,
		$params = array())
	{
		switch ($method)
		{
			case 'search':
				$paramsstring = '';
				foreach ($params as $k => $p)
				{
					$paramsstring .= '&' . $k . '=' . urlencode($p);
				}
				return simplexml_load_string(
					str_replace(
						array(
							'openSearch:totalResults',
							'media:group',
							'media:content',
							'media:thumbnail'
						),
						array(
							'openSearch_totalResults',
							'media_group',
							'media_content',
							'media_thumbnail'
						),
						HttpClient::quickGet(
							$this->wsuri .
							$paramsstring
						)
					)
				);
				break;
			case 'getFromId':
				if (!isset($params['id']))
				{
					throw new Exception('ID is needed');
				}
				return simplexml_load_string(
					str_replace(
						array(
							'media:title',
							'media:thumbnail',
							'media:group',
							'media:content',
							'media:credit',
							'media:copyright'
						),
						array(
							'media_title',
							'media_thumbnail',
							'media_group',
							'media_content',
							'media_credit',
							'media_copyright'
						),
						HttpClient::quickGet(
							$params['id']
						)
					)
				);
				break;
			default:
				throw new Exception('Unknown method');
		}
	}
	
	/**
	 * Transform an xml from Flickr to an object that we can use
	 *
	 * @param {string} $xml XML response from Flickr webservice
	 * @return ArrayObject of PxSFlickrPicture objects
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	private function _createPictures($xml = null)
	{
		if (!$xml)
		{
			throw new Exception('Invalid XML');
		}
		if (isset($xml->err['msg']))
		{
			throw new Exception($xml->err['msg']);
		}
		if (!is_object($xml))
		{
			throw new Exception('Connexion lost');
		}
		
		$this->nbElements = intval(
			$xml->photos['total']
		);
		
		$pictures = new ArrayObject();
		
		foreach ($xml->photos->photo as $p)
		{
			$pictures[] = new PxSFlickrPicture(
				$p['id'],
				$p['title'],
				$p['farm'],
				$p['server'],
				$p['secret'],
				$p['owner'],
				$p['ownername']
			);
		}
		
		return $pictures;
	}
}


/**
* 
*/
abstract class PixearchPicture
{
	/**
	 * Picture title
	 *
	 * @var string
	 **/
	public $title;
	
	/**
	 * Pictures' owner name
	 *
	 * @var string
	 **/
	public $ownerName;
	
	/**
	 * Pictures' owner url
	 *
	 * @var string
	 **/
	public $ownerUri;
	
	/**
	 * Differents images sizes urls
	 *
	 * @var ArrayObject
	 **/
	public $sizes;
	
	/**
	 * URI to the photo original page
	 *
	 * @var string
	 **/
	public $page;
	
	/**
	 * Default size
	 *
	 * @var string
	 **/
	public $defaultSize = 'small';
	
	/**
	 * thumb size
	 *
	 * @var string
	 **/
	public $thumbSize = 'thumb';
	
	/**
	 * coverflow size
	 *
	 * @var string
	 **/
	public $coverflowSize = 'medium';
}


/**
* FlickR Image
*/
class PxSFlickrPicture extends PixearchPicture
{
	public $defaultSize = 'small';
	
	public $thumbSize = 'square';
	
	public $coverflowSize = 'small';
	
	/**
	 * Construct a PxSFlickrPicture object
	 *
	 * @param {string} $id Photo id
	 * @param {string} $title Photo title
	 * @param {string} $farm Flickr farm id
	 * @param {string} $server Flickr server id
	 * @param {string} $secret Flickr secret
	 * @param {string} $nsid Photo owner id
	 * @param {string} $owner Photo owner name
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	function __construct(
		$id = null,
		$title = null,
		$farm = null,
		$server = null,
		$secret = null,
		$nsid = null,
		$owner = null)
	{
		if (!$farm)
		{
			if ($id)
			{
				$flickr = new PxSFlickr();
				
				// Infos
				$pictureInfos = $flickr->callMethod(
					'flickr.photos.getInfo',
					array(
						'photo_id'	=> $id
					)
				);
				
				if (isset($pictureInfos->err['msg']))
				{
					throw new Exception($pictureInfos->err['msg']);
				}
				
				$title = strval($pictureInfos->photo->title);
				$farm = strval($pictureInfos->photo['farm']);
				$server = strval($pictureInfos->photo['server']);
				$secret = strval($pictureInfos->photo['secret']);
				$nsid = strval($pictureInfos->photo->owner['nsid']);
			}
			else
			{
				throw new Exception(
					'Need more params'
				);
			}
		}
		$this->id = $id;
		$this->title = $title;
		$this->sizes = new ArrayObject();
		$this->sizes['square'] = 'http://farm' .
			$farm .
			'.static.flickr.com/' .
			$server .
			'/' .
			$id .
			'_' .
			$secret .
			'_s.jpg';
		$this->sizes['thumbnail'] = 'http://farm' .
			$farm .
			'.static.flickr.com/' .
			$server .
			'/' .
			$id .
			'_' .
			$secret .
			'_t.jpg';
		$this->sizes['small'] = 'http://farm' .
			$farm .
			'.static.flickr.com/' .
			$server .
			'/' .
			$id .
			'_' .
			$secret .
			'_m.jpg';
		$this->sizes['medium'] = 'http://farm' .
			$farm .
			'.static.flickr.com/' .
			$server .
			'/' .
			$id .
			'_' .
			$secret .
			'.jpg';
		$this->sizes['original'] = 'http://farm' .
			$farm .
			'.static.flickr.com/' .
			$server .
			'/' .
			$id .
			'_' .
			$secret .
			'_b.jpg';
		$this->page = 'http://www.flickr.com/photos/' . $nsid .
			'/' . $this->id;
		$this->ownerUri = 'http://www.flickr.com/photos/' . $nsid;
		$this->ownerName = $owner;
	}
}

/**
* 
*/
class PxSPhotoBucketPicture extends PixearchPicture
{
	public $defaultSize = 'thumbnail';
	
	public $thumbSize = 'thumbnail';
	
	public $coverflowSize = 'original';
	
	/**
	 * Construct a new PxSPhotoBucketPicture
	 *
	 * @return void
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public function __construct(
		$id = null,
		$title = null,
		$thumb = null,
		$browseurl = null,
		$owner = null,
		$ownerUri = null
		)
	{
		if (is_null($title))
		{
			// Get from photobucket
			// TODO
			$photobucket = new PxSPhotoBucket();
			$xml = $photobucket->callMethod(
				'media/' . urlencode($id),
				array(
					'format'	=> 'xml'
				)
			);
			
			$title = strval($xml->content->media->title);
			$thumb = strval($xml->content->media->thumb);
			$browseurl = strval($xml->content->media->browseurl);
			$owner = strval($xml->content->media['username']);
			$ownerUri = strval($xml->content->media->browseurl);
		}
		
		$this->id = urlencode($id);
		$this->title = $title;
		$this->sizes = new ArrayObject();
		$this->sizes['thumbnail'] = $thumb;
		$this->sizes['original'] = $id;
		$this->page = $browseurl;
		$this->ownerUri = $ownerUri;
		$this->ownerName = $owner;
	}
}

/**
* PxSDeviantArtPicture
*/
class PxSDeviantArtPicture extends PixearchPicture
{
	public $defaultSize = 'thumbnail';
	
	public $thumbSize = 'thumbnail';
	
	public $coverflowSize = 'medium';
	
	/**
	 * Construct a new PxSDeviantArtPicture
	 *
	 * @return void
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public function __construct(
		$id = null,
		$title = null,
		$thumb = null,
		$medium = null,
		$original = null,
		$browseurl = null,
		$ownerName = null,
		$ownerUri = null
		)
	{
		if (is_null($title))
		{
			// Get from deviantart
			$deviantArt = new PxSDeviantArt();
			$p = $deviantArt->callMethod(
				'getFromId',
				array(
					'id'	=> $id
				)
			);
			$this->id = $p->id;
			$this->title = $p->title;
			$this->sizes = $p->sizes;
			$this->page = $p->page;
			$this->ownerUri = $p->ownerUri;
			$this->ownerName = $p->ownerName;
		}
		else
		{
			$this->id = urlencode($id);
			$this->title = $title;
			$this->sizes = new ArrayObject();
			$this->sizes['thumbnail'] = $thumb;
			$this->sizes['medium'] = $medium;
			$this->sizes['original'] = $original;
			$this->page = $id;
			$this->ownerUri = $ownerUri . '/gallery/';
			$this->ownerName = $ownerName;
		}
	}
}

/**
* PxSPicasaPicture
*/
class PxSPicasaPicture extends PixearchPicture
{
	public $defaultSize = 'thumbnail';
	
	public $thumbSize = 'thumbnail';
	
	public $coverflowSize = 'original';
	
	/**
	 * Construct a new PxSDeviantArtPicture
	 *
	 * @return void
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public function __construct(
		$id = null,
		$title = null,
		$thumb = null,
		$medium = null,
		$original = null,
		$browseurl = null,
		$ownerName = null,
		$ownerUri = null
		)
	{
		if (is_null($title))
		{
			// Get from deviantart
			$deviantArt = new PxSPicasa();
			$rss = $deviantArt->callMethod(
				'getFromId',
				array(
					'id'	=> $id
				)
			);
			if (!is_object($rss))
			{
				throw new Exception('Wrong ID');
			}
			
			$this->id = urlencode($id);
			$this->title = strval($rss->title);
			$this->sizes = new ArrayObject();
			$this->sizes['square'] = strval(
				$rss->media_group->media_thumbnail[0]['url']
			);
			$this->sizes['thumbnail'] = strval(
				$rss->media_group->media_thumbnail[1]['url']
			);
			$this->sizes['small'] = strval(
				$rss->media_group->media_thumbnail[2]['url']
			);
			$this->sizes['medium'] = str_replace(
				array(
					's' . strval(
						$rss->media_group->media_thumbnail[0]['width']
					),
					's' . strval(
						$rss->media_group->media_thumbnail[0]['height']
					)
				),
				array(
					's512',
					's512'
				),
				strval(
					$rss->media_group->media_thumbnail[0]['url']
				)
			);
			$this->sizes['original'] = str_replace(
				array(
					's' . strval(
						$rss->media_group->media_thumbnail[0]['width']
					),
					's' . strval(
						$rss->media_group->media_thumbnail[0]['height']
					)
				),
				array(
					's800',
					's800'
				),
				strval(
					$rss->media_group->media_thumbnail[0]['url']
				)
			);
			$this->page = $rss->link[1]['href'];
			$this->ownerUri = '';
			$this->ownerName = strval($rss->media_credit);
		}
		else
		{
			$this->id = urlencode($id);
			$this->title = $title;
			$this->sizes = new ArrayObject();
			$this->sizes['thumbnail'] = $thumb;
			$this->sizes['medium'] = $medium;
			$this->sizes['original'] = $original;
			$this->page = $id;
			$this->ownerUri = $ownerUri;
			$this->ownerName = $ownerName;
		}
	}
}
