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