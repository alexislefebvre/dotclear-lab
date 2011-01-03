<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of joliprint, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class joliprint
{
	# URL path of joliprint API
	public static $api_path = 'api/rest/url/';
	# URL path of joliprint buttons
	public static $api_res = 'buttons/';
	
	# Available button pictures
	public static function buttons()
	{
		return array(
			__('Big') => 'joliprint-button-big.png',
			__('Button') => 'joliprint-button.png',
			__('Icon') => 'joliprint-icon.png',
			__('Share button') => 'joliprint-share-button.png',
			__('Share style') => 'joliprint-share-style.png',
			__('Colored icon') => 'joliprint-button-both.png',
			__('Black icon') => 'joliprint-icon-small.png',
			__('PDF icon') => 'pdf-icone.gif'
		);
	}
	
	# Available API continent
	public static function servers()
	{
		return array(
			__('Europe') => 'eu.joliprint.com',
			__('US') => 'api.joliprint.com'
		);
	}
	
	# Available formats
	public static function formats()
	{
		return array(
			__('PDF') => 'print',
			__('XML') => 'xml',
			__('JSON') => 'json',
			__('JPEG') => 'jpeg',
			__('Text') => 'text'
		);
	}
	
	# Create a HTML button
	public static function toHTML($params)
	{
		# (required) url: url of the page to parse
		if (!is_array($params) || !isset($params['url'])) {
			throw new Exception ('Missing parameters for joliprint button');
		}
		
		# (optional) server: default = api.joliprint.com
		$server = 'http://'.
			(isset($params['server']) && in_array($params['server'],self::servers()) ?
				$params['server'] : 'api.joliprint.com'
			).'/';
		
		# (optional) format: default = print
		$format = (isset($params['format']) && in_array($params['format'],self::formats()) ?
				$params['format'] : 'print'
			).'/';
		
		# (optional) button : default = joliprint-button.png
		$button = $server.self::$api_res.
			(isset($params['button']) && in_array($params['button'],self::buttons()) ?
				$params['button'] : 'joliprint-button.png'
			);
		
		# (optional) text : default = empty
		$title = !empty($params['text']) ? $params['text'] : __('print with Joliprint');
		$text = isset($params['text']) ? ' '.$params['text'] : '';
		
		$url = $server.self::$api_path.$format.'?url='.urlencode($params['url']);
		
		return 
		'<a class="joliprint" href="'.$url.'" title="'.$title.'">'.
		'<img class="joliprint" src="'.$button.'" alt="'.$title.'" />'.
		$text.
		'</a>';
	}
/* JS not yet implemented
	# Create a JS button
	public static function toJS($params)
	{
		# (optional) url: default = null (current page)
		$url = !empty($params['url']) ?
			'"'.$params['url'].'"' : 'null';
		
		# (optional) server: default = api.joliprint.com
		$server = 'http://'.
			(isset($params['server']) && in_array($params['server'],self::servers()) ?
				$params['server'] : 'api.joliprint.com'
			).'/';
		
		# (optional) format: default = print
		$format = (isset($params['format']) && in_array($params['format'],self::formats()) ?
				$params['format'] : 'print'
			).'/';
		
		# (optional) button : default = joliprint-button.png
		$button = $server.self::$api_res.
			(isset($params['button']) && in_array($params['button'],self::buttons()) ?
				$params['button'] : 'joliprint-button.png'
			);
		
		# (optional) text : default = null
		$title = isset($params['text']) ? $params['text'] : __('print with Joliprint');
		$text = !empty($params['text']) ? '"'.$params['text'].'"' : 'null';
		$pos = !empty($params['text']) ? '"after"' : 'null';
		
		return 
		"<script type='text/javascript'> ".
		'$joliprint()'.
		'.set("type","url")'.
		'.set("url",'.$url.')'.
		'.set("buttonUrl", "'.$button.'")'.
		'.set("label", '.$text.')'.
		'.set("labelposition", '.$pos.')'.
		'.set("service", "DotClear")'.
		'.set("title","'.$title.'")'.
		'.write();</script>'.
		"\n";
	}
//*/
}
?>