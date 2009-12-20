<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dcHistory, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('adminBeforePostUpdate',array('dcHistoryBehaviors','adminBeforePostUpdate'));
$core->addBehavior('adminPostFormSidebar',array('dcHistoryBehaviors','adminPostFormSidebar'));

$_menu['Plugins']->addItem(__('dcHistory'),
        'plugin.php?p=dcHistory','index.php?pf=dcHistory/icon.png',
        preg_match('/plugin.php\?p=dcHistory(&.*)?$/',$_SERVER['REQUEST_URI']),
        $core->auth->check('usage,contentadmin',$core->blog->id));

class dcHistoryBehaviors
{
	public static function adminBeforePostUpdate($cur,$post_id)
	{
		global $core;
		
		$params = new ArrayObject();
		$params['post_id'] = $post_id;
		
		$post = $core->blog->getPosts($params);
		
		if ( $post->isEmpty() )
		{
			return;
		}
		
		$data = array();
		$data['post_content'] = $cur->post_content;
		$data['post_excerpt'] = $cur->post_excerpt;
		
		try 
		{
			$core->history->addRevision($post_id,$data,$post);
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}
	
	public static function adminPostFormSidebar($post)
	{
		global $core;
		
		//$params = array();
		$params['post_id'] = $post->post_id;
		$params['no_content'] = true;
		
		
		$revisions = $core->history->getAllRevisions($params);
		
		$nb_revisions = $core->history->getAllRevisions($params,true);
		
		if ($nb_revisions->f(0) > 0)
		{
			echo
			'<div id="revisions-infos">'.
			'<h3>'.__('History:').'</h3>'.
			 sprintf(
			__('<p> <strong>%s</strong> %s </p>'),
			$nb_revisions->f(0),
			'<a href="plugin.php?p=dcHistory&amp;post='.$post->post_id.'">revisions</a>'
			).
			'</div>';
		}

	
	}
}

?>