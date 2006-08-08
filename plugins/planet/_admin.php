<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
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

$_menu['Plugins']->addItem('Planet','plugin.php?p=planet','index.php?pf=planet/icon.png',
		preg_match('/plugin.php\?p=planet(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->isSuperAdmin());

if (!$core->blog->settings->planet_sources) {
	return;
}

$core->addBehavior('adminPostFormSidebar',array('planetBehaviors','displayInfos'));

class planetBehaviors
{
	public static function displayInfos(&$post)
	{
		$meta = new dcMeta($GLOBALS['core']);
		
		$sitename = ($post) ? $meta->getMetaStr($post->post_meta,'planet_sitename') : '';
		$author = ($post) ? $meta->getMetaStr($post->post_meta,'planet_author') : '';
		$site = ($post) ? $meta->getMetaStr($post->post_meta,'planet_site') : '';
		$url = ($post) ? $meta->getMetaStr($post->post_meta,'planet_url') : '';
		
		if ($url != '')
		{
			echo
			'<div id="planet-infos">'.
			'<h3>'.__('Planet').'</h3>'.
			sprintf(
			__('%s by %s on %s'),
			'<a href="'.$url.'">'.__('Original post').'</a>',
			$author,
			'<a href="'.$site.'">'.$sitename.'</a>'
			).
			'</div>';
		}
	}
}
?>