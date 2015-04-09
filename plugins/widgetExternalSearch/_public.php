<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of External Search, a plugin for Dotclear 2
# Copyright (C) 2009 Moe (http://gniark.net/)
#
# External Search is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# External Search is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

/**
@ingroup External Search
@brief Document
*/
class externalSearchDocument extends dcUrlHandlers
{
	/**
	serve the document
	@param	args	<b>string</b>	Arguments
	*/
	public static function page($args)
	{
		$url = preg_replace('/^(http([s]*)\:\/\/)/i','',
			$GLOBALS['core']->blog->url);
		
		$q = !empty($_POST['q']) ? $_POST['q'] : '';
		$q = urlencode($q);
		
		switch($_POST['engine'])
		{
			case 'bing' :
				$url = 'http://www.bing.com/search?q=site:'.$url.' '.$q;
				break;
			default :
				throw new Exception(__('invalid search engine'));
				break;
		}
		
		http::redirect($url);
	}
}