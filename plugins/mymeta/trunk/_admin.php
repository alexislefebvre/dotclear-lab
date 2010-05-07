<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Mymeta plugin.
# Copyright (c) 2009 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# Mymeta plugin for DC2 is free software; you can redistribute it and/or modify
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
if (!defined('DC_CONTEXT_ADMIN')) { return; }

require (dirname(__FILE__).'/class.mymeta.php');

$_menu['Plugins']->addItem(__('My Metadata'),'plugin.php?p=mymeta','index.php?pf=mymeta/mymeta.png',
		preg_match('/plugin.php\?p=mymeta(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('usage,contentadmin',$core->blog->id));

$core->addBehavior('adminPostFormSidebar',array('mymetaBehaviors','mymetaSidebar'));
$core->addBehavior('adminPostForm',array('mymetaBehaviors','mymetaInForm'));
$core->addBehavior('adminPostForm',array('mymetaBehaviors','mymetaPostHeader'));

$core->addBehavior('adminAfterPostCreate',array('mymetaBehaviors','setMymeta'));
$core->addBehavior('adminAfterPostUpdate',array('mymetaBehaviors','setMymeta'));

$core->addBehavior('adminPageFormSidebar',array('mymetaBehaviors','mymetaSidebar'));
$core->addBehavior('adminPageForm',array('mymetaBehaviors','mymetaInForm'));

$core->addBehavior('adminPostsActionsCombo',array('mymetaBehaviors','adminPostsActionsCombo'));
$core->addBehavior('adminPostsActions',array('mymetaBehaviors','adminPostsActions'));
$core->addBehavior('adminPostsActionsContent',array('mymetaBehaviors','adminPostsActionsContent'));

$core->addBehavior('adminAfterPageCreate',array('mymetaBehaviors','setMymeta'));
$core->addBehavior('adminAfterPageUpdate',array('mymetaBehaviors','setMymeta'));
# BEHAVIORS
class mymetaBehaviors
{
	
	public static function mymetaPostHeader($post)
	{
		$mymeta = new myMeta($GLOBALS['core']);

		echo $mymeta->postShowHeader($post);
	}
	public static function mymetaSidebar($post)
	{
	}

	public static function mymetaInForm($post)
	{
		$mymeta = new myMeta($GLOBALS['core']);
		if ($mymeta->hasMeta()) {
			echo $mymeta->postShowForm($post);
		}

	}
	
	public static function setMymeta($cur,$post_id)
	{
		$mymeta = new myMeta($GLOBALS['core']);
		$mymeta->setMeta($post_id,$_POST);
	}


	public static function adminPostsActionsCombo($args)
	{
		$args[0][__('MyMeta')] = array(__('Set Metadata') => 'mymeta_set');
		
	}
	
	public static function adminPostsActions($core,$posts,$action,$redir)
	{
		if ($action == 'mymeta_set' && !empty($_POST['mymeta_ok']))
		{
			$mymeta = new myMeta($GLOBALS['core']);
			if ($mymeta->hasMeta()) {
				while ($posts->fetch())
				{
					$mymeta->setMeta($posts->post_id,$_POST);
				}
			}
			http::redirect($redir);
		}
	}
	
	public static function adminPostsActionsContent($core,$action,$hidden_fields)
	{
		if ($action == 'mymeta_set')
		{

			$mymeta = new myMeta($core);
			if ($mymeta->hasMeta()) {
				echo '<h2>'.__('Set Metadata').'</h2>'.
					'<form action="posts_actions.php" method="post">'.
					'<div><label class="area">'.__('Metadata to add:').'</label> '.
					$mymeta->postShowForm(array()).
					'</div>'.
					$hidden_fields.
					$core->formNonce().
					form::hidden(array('action'),'mymeta_set').
					form::hidden(array('mymeta_ok'),'1').
					'<p><input type="submit" value="'.__('save').'" ';
			}
		}
	}
	
}

# REST
class mymetaRest
{
}
?>