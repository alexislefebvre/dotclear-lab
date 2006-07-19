<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2003-2006 Olivier Meunier and contributors. All rights
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

if (!$core->blog->settings->planet_sources) {
	return;
}

$core->addBehavior('coreBlogGetPosts',array('planetBehaviors','coreBlogGetPosts'));
$core->url->unregister('post');

class planetBehaviors
{
	public static function coreBlogGetPosts(&$rs)
	{
		$rs->extend('rsExtPlanetPosts');
	}
}

class rsExtPlanetPosts extends rsExtPost
{
	public static function planetInfo(&$rs,$info)
	{
		return dcMeta::getMetaRecord($rs->core,$rs->post_meta,'planet_'.$info)->meta_id;
	}
	
	public static function getAuthorLink(&$rs)
	{
		return
		$rs->planetInfo('author').
		' (<a href="'.$rs->planetInfo('site').'">'.
		$rs->planetInfo('sitename').'</a>)';
	}
	
	public static function getAuthorCN(&$rs)
	{
		return $rs->planetInfo('author');
	}
	
	public static function getURL(&$rs)
	{
		return $rs->planetInfo('url');
	}
	
	public static function getContent(&$rs,$absolute_urls=false)
	{
		return
		parent::getContent($rs,$absolute_urls).
		'<p class="planet-original"><em>'.
		sprintf(__('Original post on <a href="%s">%s</a>'),
		$rs->planetInfo('url'),$rs->planetInfo('sitename')).
		'</em></p>';
	}
}
?>