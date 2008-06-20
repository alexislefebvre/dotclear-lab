<?php
# -- BEGIN LICENSE BLOCK ---------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ---------------------------------- */

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