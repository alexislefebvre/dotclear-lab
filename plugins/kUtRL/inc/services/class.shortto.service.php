<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kUtRL, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class shorttoKutrlService extends kutrlServices
{
	public $core;

	public $id = 'shortto';
	public $name = 'short.to';
	public $home = 'http://short.to';

	private $url_api = 'http://short.to/s.txt';
	private $url_test = 'http://dotclear.jcdenis.com/go/kUtRL';

	public function __construct($core,$limit_to_blog=true)
	{
		parent::__construct($core,$limit_to_blog);

		$this->url_base = 'http://short.to/';
		$this->url_min_length = 25;
	}

	public function testService()
	{
		$arg = array('url' => urlencode($this->url_test));
		if (!self::post($this->url_api,$arg,true,true))
		{
			$this->error->add(__('Service is unavailable.'));
			return false;
		}
		return true;
	}

	public function createHash($url,$hash=null)
	{
		$arg = array('url' => $url);

		if (!($response = self::post($this->url_api,$arg,true,true)))
		{
			$this->error->add(__('Service is unavailable.'));
			return false;
		}

		$rs = new ArrayObject();
		$rs->hash = str_replace($this->url_base,'',$response);
		$rs->url = $url;
		$rs->type = $this->id;

		return $rs;
	}
}
?>