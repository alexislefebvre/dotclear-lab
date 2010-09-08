<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of TaC, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

/* Some usefull tools to use with twitter */
class tacTools
{
	# Cut on word message $str into sub messages less than $len chars long
	public static function splitStr($str,$len=140)
	{
		$split = array(0=>'');
		$j = 0;
		if (strlen($str) < $len)
		{
			$words = explode(' ',$str);
			for($i = 0; $i < count($words); $i++)
			{
				$s = empty($split[$j]) ? '' : ' ';

				$next_len = $split[$j].$s.$words[$i];
				if (strlen($next_len) < $len)
				{
					$split[$j] .= $s.$words[$i];
				}
				else
				{
					$j++;
					$split[$j] = $words[$i];
				}
			}
		}
		else
		{
			$split[0] = $str;
		}
		return $split;
	}
	
	# Trim a long url using http://is.gd API
	# Usefull for short message
	public static function shorten($url,$verbose=false)
	{
		$error = '';
		$api = 'http://is.dg/api.php?';
		$path = '';
		$data = array('longurl'=>urlencode($url));
		
		# Send request
		$client = netHttp::initClient($api,$path);
		$client->setUserAgent('libDcTwitterSender - '.self::$version);
		$client->setPersistReferers(false);
		$client->get($path,$data);
		
		# Recieve short url
		if ($client->getStatus() == 200) {
			return (string) $client->getContent();
		}
		# Error during shorten link
		elseif ($client->getStatus() == 500) {
			$str = html::escapeHTML((string) $client->getContent());
			$error = sprintf(__('Failed to get short url (%s)'),$str);
		}
		
		# Throw error
		if ($verbose) {
			if (empty($error)) {
				throw New Exception(__('Failed to get short url'));
			}
			else {
				throw New Exception($error);
			}
		}
		return false;
	}
}
?>