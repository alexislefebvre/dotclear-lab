<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Contribute.
# Copyright 2008 Moe (http://gniark.net/)
#
# Contribute is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Contribute is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

$_menu['Plugins']->addItem(('Contribute'),
	'plugin.php?p=contribute',
	'index.php?pf=contribute/icon.png',
	preg_match('/plugin.php\?p=contribute(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id));

$core->addBehavior('adminPostFormSidebar',
	array('contributeAdmin','adminPostFormSidebar'));

$core->addBehavior('adminBeforePostUpdate',
	array('contributeAdmin','adminBeforePostUpdate'));

/**
@ingroup Contribute
@brief Admin
*/
class contributeAdmin
{
	/**
	adminBeforePostUpdate behavior
	@param	settings	<b>object</b>	Settings
	*/
	public static function adminBeforePostUpdate($cur,$post_id)
	{
		$meta = new dcMeta($GLOBALS['core']);
		
		try
		{
			if (isset($_POST['contribute_author']))
			{
				$meta->delPostMeta($post_id,'contribute_author');
				$meta->setPostMeta($post_id,'contribute_author',
					$_POST['contribute_author']);
			}
			
			if (isset($_POST['contribute_mail']))
			{
				$meta->delPostMeta($post_id,'contribute_mail');
				$meta->setPostMeta($post_id,'contribute_mail',
					$_POST['contribute_mail']);
			}
			
			if (isset($_POST['contribute_site']))
			{
				$meta->delPostMeta($post_id,'contribute_site');
				$meta->setPostMeta($post_id,'contribute_site',
					$_POST['contribute_site']);
			}	
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
		
		if (isset($_POST['contribute_delete_author'])
			&& ($_POST['contribute_delete_author'] == '1'))
		{
			try
			{
				$meta->delPostMeta($post_id,'contribute_author');
				$meta->delPostMeta($post_id,'contribute_mail');
				$meta->delPostMeta($post_id,'contribute_site');
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
	}
	
	/**
	adminPostFormSidebar behavior
	@param	settings	<b>object</b>	Settings
	*/
	public static function adminPostFormSidebar(&$post)
	{	
		$meta = new dcMeta($GLOBALS['core']);
		
		$author = ($post) ? $meta->getMetaStr($post->post_meta,'contribute_author') : '';
		$mail = ($post) ? $meta->getMetaStr($post->post_meta,'contribute_mail') : '';
		$site = ($post) ? $meta->getMetaStr($post->post_meta,'contribute_site') : '';
		
		$str = '';
		
		if (!empty($author))
		{		
			$str .=  '<p>'.
			'<label class="classic" for="contribute_author">'.
			__('Author:').
			form::field('contribute_author',10,255,html::escapeHTML($author),'maximal').
			'</label>'.
			'</p>';
			
			if (!empty($site))
			{
				$link = '<br />'.'<a href="mailto:'.html::escapeHTML($mail).'">'.
					__('send email').'</a>';
			}
			else
			{
				$link = '';
			}
			
			$str .=  '<p>'.
			'<label class="classic" for="contribute_mail">'.
			__('Email:').
			form::field('contribute_mail',10,255,html::escapeHTML($mail),'maximal').
			'</label>'.
			$link.
			'</p>';
			
			# prevent malformed URLs
			# inspirated by /dotclear/inc/clearbricks/net.http/class.net.http.php
			if (!empty($site))
			{
				$parsed_url = @parse_url($site);
				$host = (($parsed_url != false) ? '['.$parsed_url['host'].']' : '');
				$link = '<br />'.
					'<a href="'.html::escapeHTML($site).'">'.$host.'</a>';
			}
			else
			{
				$link = '';
			}
			
			$str .=  '<p>'.
			'<label class="classic" for="contribute_site">'.
			__('Web site:').
			form::field('contribute_site',10,255,html::escapeHTML($site),'maximal').
			'</label>'.
			$link.
			'</p>';
			
			echo
			'<div id="planet-infos">'.'<h3>'.('Contribute').'</h3>'.
			$str.
			'<p>'.
			'<label class="classic" for="contribute_delete_author">'.
			form::checkbox('contribute_delete_author',1).
			__('Delete this author').
			'</label>'.
			'</p>'.
			'</div>';
		}
	}
}

?>