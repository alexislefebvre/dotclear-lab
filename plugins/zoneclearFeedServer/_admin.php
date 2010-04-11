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

if (!defined('DC_CONTEXT_ADMIN')){return;}
if (!$core->plugins->moduleExists('metadata')){return;}

require_once dirname(__FILE__).'/_widgets.php';

# Admin menu
$_menu['Plugins']->addItem(
	__('Zoneclear feed server'),
	'plugin.php?p=zoneclearFeedServer','index.php?pf=zoneclearFeedServer/icon.png',
	preg_match('/plugin.php\?p=zoneclearFeedServer(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id)
);

# Add info about feed on post page sidebar
$core->addBehavior('adminPostFormSidebar',array('zoneclearFeedServerAdminBehaviors','adminPostFormSidebar'));
# Delete related info about feed post in meta table
$core->addBehavior('adminBeforePostDelete',array('zoneclearFeedServerAdminBehaviors','adminBeforePostDelete'));

class zoneclearFeedServerAdminBehaviors
{
	# Add info about feed on post page sidebar
	public static function adminPostFormSidebar(&$post)
	{
		if (null === $post || $post->post_type != 'post') return;

		$url = dcMeta::getMetaRecord($post->core,$post->post_meta,'zoneclearfeed_url');
		$url = $url->isEmpty() ? '' : $url->meta_id;
		$author = dcMeta::getMetaRecord($post->core,$post->post_meta,'zoneclearfeed_author');
		$author = $author->isEmpty() ? '' : $author->meta_id;
		$site = dcMeta::getMetaRecord($post->core,$post->post_meta,'zoneclearfeed_site');
		$site = $site->isEmpty() ? '' : $site->meta_id;
		$sitename = dcMeta::getMetaRecord($post->core,$post->post_meta,'zoneclearfeed_sitename');
		$sitename = $sitename->isEmpty() ? '' : $sitename->meta_id;

		if (!$url) return;

		echo
		'<div id="zoneclear-feed">'.
		'<h3>'.__('Feed source').'</h3>'.
		'<p>'.
		'<a href="'.$url.'" title="'.$author.' - '.$url.'">'.__('feed URL').'</a> - '.
		'<a href="'.$site.'" title="'.$sitename.' - '.$site.'">'.__('site URL').'</a>'.
		'</p>'.
		'</div>';
	}

	# Delete related info about feed post in meta table
	public static function adminBeforePostDelete($post_id)
	{
		global $core;
		$post_id = (integer) $post_id;
		$types = array(
			'zoneclearfeed_url',
			'zoneclearfeed_author',
			'zoneclearfeed_site',
			'zoneclearfeed_sitename',
			'zoneclearfeed_id'
		);

		$core->con->execute(
			'DELETE FROM '.$core->prefix.'meta '.
			'WHERE post_id = '.$post_id.' '.
			'AND meta_type '.$core->con->in($types).' '
		);
	}
}
?>