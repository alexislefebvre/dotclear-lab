<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of zoneclearFeedServer, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis, BG and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Namespace for settings
$core->blog->settings->addNamespace('zoneclearFeedServer');

# Widgets
require_once dirname(__FILE__).'/_widgets.php';

# Admin menu
$_menu['Plugins']->addItem(
	__('Feeds server'),
	'plugin.php?p=zoneclearFeedServer','index.php?pf=zoneclearFeedServer/icon.png',
	preg_match('/plugin.php\?p=zoneclearFeedServer(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id)
);

if ($core->auth->check('admin',$core->blog->id))
{
	# Dashboard icon
	$core->addBehavior('adminDashboardIcons',array('zoneclearFeedServerAdminBehaviors','adminDashboardIcons'));
	# Add info about feed on post page sidebar
	$core->addBehavior('adminPostHeaders',array('zoneclearFeedServerAdminBehaviors','adminPostHeaders'));
	$core->addBehavior('adminPostFormSidebar',array('zoneclearFeedServerAdminBehaviors','adminPostFormSidebar'));
}
# Delete related info about feed post in meta table
$core->addBehavior('adminBeforePostDelete',array('zoneclearFeedServerAdminBehaviors','adminBeforePostDelete'));

# Take care about tweakurls (thanks Mathieu M.)
if (version_compare($core->plugins->moduleInfo('tweakurls','version'),'0.8','>=')) {
	$core->addbehavior('zoneclearFeedServerAfterPostCreate',array('zoneclearFeedServer','tweakurlsAfterPostCreate'));
}

class zoneclearFeedServerAdminBehaviors
{
	# Add icon on dashboard if there are disabled feeds
	public static function adminDashboardIcons($core,$icons)
	{
		$zcfs = new zoneclearFeedServer($core);
		$count = $zcfs->getFeeds(array('feed_status'=>'0'),true)->f(0);
		if (!$count) return;

		$str = ($count > 1) ? __('%s disabled feeds') : __('one disable feed');

		$icons['zcfs'] = new ArrayObject(array(
			sprintf($str,$count),
			'plugin.php?p=zoneclearFeedServer&part=feeds&sortby=feed_status&order=asc',
			'index.php?pf=zoneclearFeedServer/icon-b.png'
		));
	}
	
	# Load javascript for toggle menu
	public static function adminPostHeaders()
	{
		return 
		'<script type="text/javascript">$(function() { '.
		"$('#zcfs-form-title').toggleWithLegend($('#zcfs-form-content'),{cookie:'dcx_zcfs_admin_form_sidebar'}); ".
		'});</script>';
	}
	
	# Add info about feed on post page sidebar
	public static function adminPostFormSidebar($post)
	{
		global $core;
		
		if (null === $post || $post->post_type != 'post') return;
		
		$url = $core->meta->getMetadata(array('post_id'=>$post->post_id,'meta_type'=>'zoneclearfeed_url','limit'=>1));
		$url = $url->isEmpty() ? '' : $url->meta_id;
		
		if (!$url) return;
		
		$author = $core->meta->getMetadata(array('post_id'=>$post->post_id,'meta_type'=>'zoneclearfeed_author','limit'=>1));
		$author = $author->isEmpty() ? '' : $author->meta_id;
		
		$site = $core->meta->getMetadata(array('post_id'=>$post->post_id,'meta_type'=>'zoneclearfeed_site','limit'=>1));
		$site = $site->isEmpty() ? '' : $site->meta_id;
		
		$sitename = $core->meta->getMetadata(array('post_id'=>$post->post_id,'meta_type'=>'zoneclearfeed_sitename','limit'=>1));
		$sitename = $sitename->isEmpty() ? '' : $sitename->meta_id;
		
		echo
		'<div id="zoneclear-feed">'.
		'<h3 id="zcfs-form-title" class="clear">'.__('Feed source').'</h3>'.
		'<div id="zcfs-form-content">'.
		'<p>'.
		'<a href="'.$url.'" title="'.$author.' - '.$url.'">'.__('feed URL').'</a> - '.
		'<a href="'.$site.'" title="'.$sitename.' - '.$site.'">'.__('site URL').'</a>'.
		'</p>';
		
		if ($core->auth->check('admin',$core->blog->id))
		{
			$fid = $core->meta->getMetadata(array('post_id'=>$post->post_id,'meta_type'=>'zoneclearfeed_id','limit'=>1));
			if (!$fid->isEmpty())
			{
				echo '<p><a class="button" href="plugin.php?p=zoneclearFeedServer&amp;part=feed&amp;feed_id='.$fid->meta_id.'">'.__('Edit this feed').'</a></p>';
			}
		}
		
		echo 
		'</div></div>';
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