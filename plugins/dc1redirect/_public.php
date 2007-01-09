<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2006 Olivier Meunier and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

$core->url->register('redirect_post','','^(\d{4}/\d{2}/\d{2}/\d+.+)$',array('dcUrlRedirect','post'));
$core->url->register('redirect_category','','^([A-Z]+[A-Za-z0-9_-]*)$',array('dcUrlRedirect','category'));

class dcUrlRedirect {
	public static function post($args)
	{
		global $core;
		
		$url = $core->blog->url.$core->url->getBase('post').'/'.$args;
		http::head(301,'Moved Permanently');
		header('Location: '.$url);
		exit;
	}
		
	public static function category($args)
	{
		global $core;
		
		$url = $core->blog->url.$core->url->getBase('category').'/'.$args;
		http::head(301,'Moved Permanently');
		header('Location: '.$url);
		exit;
	}
}
?>