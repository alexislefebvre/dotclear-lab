<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of muppet, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

if (version_compare(str_replace("-r","-p",DC_VERSION),'2.2-beta','<')) { return; }

$__autoload['muppet'] = dirname(__FILE__).'/inc/class.setting.muppet.php';
$__autoload['toolsmuppet'] = dirname(__FILE__).'/inc/lib.behaviors.muppet.php';

$core->blog->settings->addNamespace('muppet'); 
require dirname(__FILE__).'/_widgets.php';

$post_types = muppet::getPostTypes();

if (!empty($post_types))
{
	foreach ($post_types as $k => $v)
	{
		$core->url->register($k,$k,sprintf('^%s/(.+)$',$k),array('urlMuppet','singlepost'));
		$core->url->register(sprintf('%spreview',$k),sprintf('%spreview',$k),sprintf('^%spreview/(.+)$',$k),array('urlMuppet','singlepreview'));
		$core->setPostType($k,'plugin.php?p=muppet&type='.$k.'&id=%d',$core->url->getBase($k).'/%s');
		$core->url->register($k.'s',$k.'s',sprintf('^%s(.*)$',$k.'s'),array('urlMuppet','listpost'));
		$core->url->register(sprintf('%s_feed',$k),sprintf('feed/%ss',$k),sprintf('^feed/%ss/(.+)$',$k),array('urlMuppet','mupFeed'));
	}
	// Waiting ticket http://dev.dotclear.org/2.0/ticket/1090
	$core->url->register('category','category','^category/(.+)$',array('urlMuppet','category'));
	$core->url->register('archive','archive','^archive(/.+)?$',array('urlMuppet','archive'));
}

$core->addBehavior('sitemapsDefineParts',array('muppetBehaviors','sitemapsDefineParts'));
$core->addBehavior('sitemapsURLsCollect',array('muppetBehaviors','sitemapsURLsCollect'));

class muppetBehaviors
{
	public static function sitemapsDefineParts($map)
	{
		$types = muppet::getPostTypes();
		if (!empty($types))
		{
			foreach ($types as $k => $v)
			{
				$map[ucfirst($v['plural'])] = $k;
			}
		}
	}

	public static function sitemapsURLsCollect($sitemaps)
	{
		global $core;
          $core->blog->settings->addNamespace('sitemaps');

		$types = muppet::getPostTypes();
		if (!empty($types))
		{
			foreach ($types as $k => $v)
			{
				if ($core->blog->settings->sitemaps->{'sitemaps_'.$k.'_url'})
				{
					$freq = $sitemaps->getFrequency($core->blog->settings->sitemaps->{'sitemaps_'.$k.'_fq'});
					$prio = $sitemaps->getPriority($core->blog->settings->sitemaps->{'sitemaps_'.$k.'_pr'});
					$base = $core->blog->url.$core->url->getBase($k);
					//$sitemaps->addEntry($base,$prio,$freq);
					$sitemaps->addPostType($k,$core->blog->url.$core->url->getBase($k).'/');
					$sitemaps->collectEntriesURLs($k);
				}
			}
		}
     }

	public static function adminPostsActionsCombo($args)
	{
		global $core;
		if ($core->auth->check('admin',$core->blog->id)) {
			$args[0][__('Move')] = array(__('Change post type') => 'settype');
		}
	}
	
	public static function adminPostsActions($core,$posts,$action,$redir)
	{
		if ($action == 'settype' && isset($_POST['posttype']))
		{
			$newposttype = $_POST['posttype'];
			try
			{
				while ($posts->fetch())
				{
					$cur = $core->con->openCursor($core->prefix.'post');
					$cur->post_type = $newposttype;
					$cur->update('WHERE post_id = '.(integer) $posts->post_id);
				}
			
				http::redirect($redir);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
	}
	
	public static function adminPostsActionsContent($core,$action,$hidden_fields)
	{
		if ($action == 'settype')
		{
			$default = array(__('Entry') => 'post');
			$types = array();
			
			if ($core->blog->settings->muppet->muppet_allow_page === true)
			{
				$types = array(__('Page') => 'page');
			}
		
			$ty = muppet::getPostTypes();
			foreach ($ty as $k =>$v) {
				$types= array_merge($types, array(ucfirst($v['name'])=> $k));
			}

			$types  = array_merge($default,$types);

			echo
			'<h2>'.__('Select post type for these entries').'</h2>'.
			'<form action="posts_actions.php" method="post">'.
			'<p><label class="classic">'.__('Choose post type:').' '.
			form::combo('posttype',$types).
			'</label> '.
		
			$hidden_fields.
			$core->formNonce().
			form::hidden(array('action'),'settype').
			'<input type="submit" value="'.__('save').'" /></p>'.
			'</form>';
		}
	}

	public static function adminPageHTMLHead()
	{
		echo '<script type="text/javascript" src="index.php?pf=muppet/js/menu.js"></script>';
	}
}
?>
