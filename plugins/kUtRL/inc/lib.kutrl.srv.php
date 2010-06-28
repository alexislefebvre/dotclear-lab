<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kUtRL, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

# This file contents parent class of shorten link services 

class kutrlServices
{
	public $core;
	public $error;
	public $s;
	public $log;
	public $limit_to_blog;

	public $id = 'undefined';
	public $name = 'undefined';

	public $allow_customized_hash = false;
	public $allowed_protocols = array('http://');
	public $url_base = '';
	public $url_min_length = 0;

	public function __construct($core,$limit_to_blog=true)
	{
		$this->core = $core;
		$this->s = $core->blog->settings->kUtRL;
		$this->log = new kutrlLog($core);
		$this->limit_to_blog = (boolean) $limit_to_blog;
		$this->error = new dcError();
		$this->error->setHTMLFormat('%s',"%s\n");
	}

	# Save settings from admin page
	public function saveSettings()
	{
		return null;
	}

	# Settings form for admin page
	public function settingsForm()
	{
		echo 
		'<p class="form-note">'.
		__('There is nothing to configure for this service.').
		'</p>';
	}

	# Test if service is well configured
	public function testService()
	{
		return null;
	}

	# Test if an url is valid
	public function isValidUrl($url)
	{
		return (boolean) filter_var($url,FILTER_VALIDATE_URL);
	}

	# Test if an url contents know prefix
	public function isServiceUrl($url)
	{
		return strpos($url,$this->url_base) === 0;
	}

	# Test if an url is long enoutgh
	public function isLongerUrl($url)
	{
		return ($this->url_min_length >= $url);
	}

	# Test if an url protocol (eg: http://) is allowed
	public function isProtocolUrl($url)
	{
		foreach($this->allowed_protocols as $protocol)
		{
			if (empty($protocol)) continue;

			if (strpos($url,$protocol) === 0) return true;
		}
		return false;
	}

	# Test if an url is from current blog
	public function isBlogUrl($url)
	{
		$base = $this->core->blog->url;
		$url = substr($url,0,strlen($base));

		return $url == $base;
	}

	# Test if an url is know
	public function isKnowUrl($url)
	{
		return $this->log->select($url,null,$this->id,'kutrl');
	}

	# Test if an custom short url is know
	public function isKnowHash($hash)
	{
		return $this->log->select(null,$hash,$this->id,'kutrl');
	}

	# Create hash from url
	public function hash($url,$hash=null)
	{
		$url = trim($this->core->con->escape($url));
		if ('undefined' === $this->id) 
		{
			return false;
		}
		if ($hash && !$this->allow_customized_hash)
		{
			return false;
		}
		if ($this->isServiceUrl($url))
		{
			return false;
		}
		if (!$this->isLongerUrl($url))
		{
			return false;
		}
		if ($this->limit_to_blog && !$this->isBlogUrl($url))
		{
			return false;
		}
		if ($hash && false !== ($rs = $this->isKnowHash($hash)))
		{
			return false;
		}
		if (false === ($rs = $this->isKnowUrl($url)))
		{
			if (false === ($rs = $this->createHash($url,$hash)))
			{
				return false;
			}

			$this->log->insert($rs->url,$rs->hash,$rs->type,'kutrl');
			$this->core->blog->triggerBlog();


			# --BEHAVIOR-- kutrlAfterCreateShortUrl
			$this->core->callBehavior('kutrlAfterCreateShortUrl',$rs);


		}
		return $rs;
	}

	# Create a hash for a given url (and its custom hash) 
	public function createHash($url,$hash=null)
	{
		return false;
	}

	# Remove an url from list of know urls
	public function remove($url)
	{
		if (!($rs = $this->isKnowUrl($url))) return false;
		$this->deleteUrl($url);
		$this->log->delete($rs->id);
		return true;
	}

	# Delete url on service (second argument really delete urls)
	public function deleteUrl($url,$delete=false)
	{
		return null;
	}

	# Retrieve long url from hash
	public function getUrl($hash)
	{
		return false;
	}

	# Post request
	public static function post($server,$data,$verbose=true,$get=false)
	{
		$url = (string) $server;
		$client = netHttp::initClient($url,$url);
		$client->setUserAgent('kUtRL - http://kutrl.fr');
		$client->setPersistReferers(false);

		if ($get)
		{
			$client->get($url,$data);
		}
		else
		{
			$client->post($url,$data);
		}

		if (!$verbose && $client->getStatus() != 200)
		{
			return false;
		}

		if ($verbose)
		{
			return $client->getContent();
		}
		return true;
	}
}
?>