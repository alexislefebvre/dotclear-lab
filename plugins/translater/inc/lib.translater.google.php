<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of translater, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
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

	public $api = 'http://ajax.googleapis.com/ajax/services/language/translate';
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
			$data = array(
				'v' => '1.0',
				'q' => $str,
				'langpair' => $this->from.'|'.$this->to
			);
			
			$userip = http::realIP();
			if ($userip)
			{
				$data['userip'] = $userip;
			}
			
			$path = '';
			$client = netHttp::initClient($this->api,$path);
			$client->setUserAgent($this->agent);
			$client->useGzip(false);
			$client->setPersistReferers(false);
			$client->get($path,$data);

			$rs = $client->getContent();
			if (null === ($dec = json_decode($rs)))
			{
				throw new Exception('Failed to decode result');
			}
			if ($dec->responseStatus != 200)
			{
				$detail = $dec->responseDetails != '' ? $dec->responseDetails : 'no detail';
				throw new Exception($detail);
			}
			if ('' == $dec->responseData)
			{
				throw new Exception('No data response');
			}

			return $dec->responseData->translatedText;
		}
		catch (Exception $e) {}
		return '';
	}
}
?>