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
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$my_types = muppet::getPostTypes();

if (!empty($my_types))
{
	$_menu['Muppet'] = new dcMenu('muppet-menu',__('Content'));
	foreach ($my_types as $k => $v)
	{
		$plural = empty($v['plural']) ? $v['name'].'s' : $v['plural'];
		$pattern = '/p=muppet&type='.$k.'.*?$/';
		$_menu['Muppet']->addItem(ucfirst($plural),
				'plugin.php?p=muppet&amp;type='.$k.'&amp;list=all',
				'index.php?pf=muppet/img/'.$v['icon'],
				preg_match($pattern,$_SERVER['REQUEST_URI']),
				$core->auth->check('contentadmin,'.$v['perm'],$core->blog->id));
		$core->auth->setPermissionType($v['perm'],sprintf(__('manage the %s'),$plural));
	}

	// JS for Menu Content
	$core->addBehavior('adminPageHTMLHead',array('muppetBehaviors','adminPageHTMLHead'));
}

$core->addBehavior('sitemapsDefineParts',array('muppetBehaviors','sitemapsDefineParts'));
$core->addBehavior('sitemapsURLsCollect',array('muppetBehaviors','sitemapsURLsCollect'));

$core->addBehavior('adminPostsActionsCombo',array('muppetBehaviors','adminPostsActionsCombo'));
$core->addBehavior('adminPostsActions',array('muppetBehaviors','adminPostsActions'));
$core->addBehavior('adminPostsActionsContent',array('muppetBehaviors','adminPostsActionsContent'));
$core->addBehavior('adminPagesActionsCombo',array('muppetBehaviors','adminPostsActionsCombo'));
$core->addBehavior('adminPagesActions',array('muppetBehaviors','adminPostsActions'));
$core->addBehavior('adminPagesActionsContent',array('muppetBehaviors','adminPostsActionsContent'));

$_menu['Plugins']->addItem(__('My types'),'plugin.php?p=muppet','index.php?pf=muppet/icon.png',
	(preg_match('/plugin.php\?p=muppet(&.*)?/',$_SERVER['REQUEST_URI']))
	&& (!preg_match('/type/',$_SERVER['REQUEST_URI'])),
	$core->auth->check('contentadmin',$core->blog->id));

class muppetBehaviors
{
	public static function sitemapsDefineParts($map)
	{
		$types = muppet::getPostTypes();
		if (!empty($types))
		{
			foreach ($types as $k => $v)
			{
				//$map[ucfirst($v['plural'])] = $k;
				$map->offsetSet(ucfirst($v['plural']),$k);
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
				//if ((!muppet::typeExists($newposttype)) && ($newposttype != 'post')) {
				//	throw new Exception(__('Something wrong happened...'));
				//}
			
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