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

# This uses google page to translate strings
class googleProposal
{
	public $core;
	public $from;
	public $to;

	public $api = 'http://translate.google.com/translate_t';
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
			$args = array('sl'=>$this->from,'tl'=>$this->to,'text'=>$str);
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
		return preg_match('/<div id=result_box dir="ltr">(.+?)<\/div>/',$rs,$m) ?
			strip_tags(str_replace('&nbsp;','',$m[0])) :
			'';
	} 
}
?>