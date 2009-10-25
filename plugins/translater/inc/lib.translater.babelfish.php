<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of translater, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# This uses yahoo babelfish page to translate strings
class babelfishProposal
{
	public $core;
	public $from;
	public $to;

	public $api = 'http://babelfish.yahoo.com/translate_txt';
	public $agent = 'dcTranslater - http://dotclear.jcdenis.com/go/translater';

	public function __construct($core,$from_lang,$to_lang)
	{
		$this->core = $core;
		$this->from = $from_lang;
		$this->to = $to_lang;
	}

	public static function init($core,$from_lang,$to_lang)
	{
		return new self($core,$from_lang,$to_lang);
	}

	public function get($str)
	{
		$str = trim($str);
		if (empty($str)) return '';

		try
		{
			$args = array('lp'=>$this->from.'_'.$this->to,'ei'=>'UTF-8','trtext'=>$str);
			$path = '';
			$client = netHttp::initClient($this->api,$path);
			$client->setUserAgent($this->agent);
			$client->useGzip(false);
			$client->setPersistReferers(false);
			$client->get($path,$args);

			$rs = $client->getContent();
			return self::filter($rs);
		}
		catch (Exception $e) {}
		return '';
	}

	public static function filter($rs)
	{
		return preg_match('/<div id="result"><div style="padding:0.6em;">(.+?)<\/div><\/div>/',$rs,$m) ?
			str_replace('&quot ; ','"',$m[1]) :
			'';
	} 
}
?>