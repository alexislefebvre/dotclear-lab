<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of entryCSS, a plugin for Dotclear.
# 
# Copyright (c) 2009 - annso
# contact@as-i-am.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('adminPostForm', array('adminEntryCSS','cssForm'));
$core->addBehavior('adminPageForm', array('adminEntryCSS','cssForm'));

$core->addBehavior('adminAfterPageCreate',array('adminEntryCSS','setCSS'));
$core->addBehavior('adminAfterPageUpdate',array('adminEntryCSS','setCSS'));
$core->addBehavior('adminAfterPostCreate',array('adminEntryCSS','setCSS'));
$core->addBehavior('adminAfterPostUpdate',array('adminEntryCSS','setCSS'));


# BEHAVIORS
class adminEntryCSS
{
	//print_r($post);
	public static function cssForm(&$post)
	{
		global $core;
		
		$params = new ArrayObject();
		
		if (!empty($_REQUEST['id'])) {
			$params['post_id'] = $_REQUEST['id'];
			$params['columns'] = array('post_css');
			if (preg_match('/plugin.php\?p=pages(&.*)?$/',$_SERVER['REQUEST_URI'])) {
				$params['post_type'] = 'page';
			}
			$post = $core->blog->getPosts($params);
			$value = $post->post_css;
		} else {
			$value = '';
		}
		
		$res = '';
		$res .= '<p class="area"><label for="entry_css"><strong>'.__('CSS :').'</strong></label>';
		$res .= form::textarea('entry_css',50,5,$value);
		$res .= '</p>';
		
		if (preg_match('/plugin.php\?p=pages(&.*)?$/',$_SERVER['REQUEST_URI'])) {
			return $res;
		} else {
			echo $res;
		}
	}
	
	public static function setCSS(&$cur,&$post_id)
	{
		if (!empty($_POST['entry_css'])) {
			$cur = $GLOBALS['core']->con->openCursor($GLOBALS['core']->prefix.'post');
			$cur->post_css = $_POST['entry_css'];
			$cur->update('WHERE post_id = '.$post_id.';');
		}
	}
}
