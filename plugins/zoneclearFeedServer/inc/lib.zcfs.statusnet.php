<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of zoneclearFeedServer, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis, BG and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class zcfsLibStatusNet
{
	const VESRION = '0.1';
	
	public static function getVersion()
	{
		return self::VERSION;
	}
	
	# Send message $str to API user status (timeline)
	public static function send($user,$pass,$str,$api_url='identi.ca',$api_path='/api/statuses/update.xml')
	{
		# User not set
		if (!$user || !$pass) {
			throw New Exception(__('User is not set.'));
		}
		
		# Clean message
		$str = (string) $str;
		$str = trim($str);
		//$str = urlencode($str);
		
		# Empty message
		if (!$str) {
			throw New Exception(__('Nothing to send.'));
		}
		
		# Split into smaller messages
		$msg = self::splitStr($str,140);
		
		# Loop throught lines of messages
		foreach($msg as $k => $line)
		{
			# Open connection
			$client = new netHttp($api_url);
			$client->setAuthorization($user,$pass);
			
			# Send Message
			if (!$client->post($api_path,array('status'=>$line))) {
				throw new Exception(sprintf(__('Failed to send message (%s)'),$k+1));
				return false;
			}
		}
		return true;
	}
	
	# Cut on word message $str into sub message less than $len chars long
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
	public static function shorten($url,$encode=true,$verbose=false)
	{
		$error = '';
		$url = $encode ? urlencode($url) : $url;
		$api = 'http://is.gd/api.php?';
		$path = '';
		$data = array('longurl'=>$url);
		
		# Send request
		$client = netHttp::initClient($api,$path);
		$client->setUserAgent('zcfsLibStatusNet');
		$client->setPersistReferers(false);
		$client->get($path,$data);
		
		# Receive short url
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