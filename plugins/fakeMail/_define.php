<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of fakeMail a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */		'fakeMail',
	/* Description*/	'Fake mails and write them to a public file',
	/* Author */		'Osku and contributors',
	/* Version */		'0.1',
	/* Permissions */	'usage'
);

class mail
{
	/*
	*/
	public static function sendMail($to,$subject,$message,$headers=null,$p=null)
	{
		global $core;
		$out = path::real($core->blog->public_path)."/mails.txt";
		if (!($fp = fopen($out, 'a'))) {
			return;
		}
		fprintf($fp,"###########\n%s\n-----\n To: %s\n Subject: %s\n-----\n Message:\n%s\n###########\n\n",implode($headers,"\n\t"),$to,$subject,$message);
		return true;
	}
	
	public static function getMX($host)
	{
		if (!getmxrr($host,$mx_h,$mx_w) || count($mx_h) == 0) {
			return false;
		}
		
		$res = array();
		
		for ($i=0; $i<count($mx_h); $i++) {
			$res[$mx_h[$i]] = $mx_w[$i];
		}
		
		asort($res);
		
		return $res;
	}
	
	public static function QPHeader($str,$charset='UTF-8')
	{
		if (!preg_match('/[^\x00-\x3C\x3E-\x7E]/',$str)) {
			return $str;
		}
		
		return '=?'.$charset.'?Q?'.text::QPEncode($str).'?=';
	}
	
	public static function B64Header($str,$charset='UTF-8')
	{
		if (!preg_match('/[^\x00-\x3C\x3E-\x7E]/',$str)) {
			return $str;
		}
		
		return '=?'.$charset.'?B?'.base64_encode($str).'?=';
	}
}
?>
