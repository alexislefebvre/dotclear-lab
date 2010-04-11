<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Contribute, a plugin for Dotclear 2
# Copyright (C) 2008,2009,2010 Moe (http://gniark.net/)
#
# Contribute is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Contribute is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons :
# <http://www.famfamfam.com/lab/icons/silk/>
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

$_menu['Plugins']->addItem(__('Contribute'),
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
	@param	cur	<b>cursor</b>	Cursor
	@param	post_id	<b>integer</b>	Post ID
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
	@param	post	<b>cursor</b>	Post
	*/
	public static function adminPostFormSidebar(&$post)
	{	
		$meta = new dcMeta($GLOBALS['core']);
		
		$author = ($post) ? $meta->getMetaStr($post->post_meta,'contribute_author') : '';
		$mail = ($post) ? $meta->getMetaStr($post->post_meta,'contribute_mail') : '';
		$site = ($post) ? $meta->getMetaStr($post->post_meta,'contribute_site') : '';
		
		$str = '';
		
		if (!empty($author) OR !empty($mail) OR !empty($site))
		{		
			$str .= '<p>'.
			'<label class="classic" for="contribute_author">'.
			__('Author:').
			form::field('contribute_author',10,255,html::escapeHTML($author),'maximal').
			'</label>'.
			'</p>';
			
			$str .= '<p>'.
			'<label class="classic" for="contribute_mail">'.
			__('Email:').
			form::field('contribute_mail',10,255,html::escapeHTML($mail),'maximal').
			'</label>';
			
			# mailto: link
			if ((!empty($mail)) && (text::isEmail($mail)))
			{
				$str .= '<br />'.'<a href="mailto:'.html::escapeHTML($mail).'">'.
					__('Send an e-mail').'</a>';
			}
			
			$str .= '</p>';
			
			$str .= '<p>'.
			'<label class="classic" for="contribute_site">'.
			__('Web site:').
			form::field('contribute_site',10,255,html::escapeHTML($site),'maximal').
			'</label>';
			
			# display a link to the site
			# prevent malformed URLs
			# inspired by /dotclear/inc/clearbricks/net.http/class.net.http.php
			if (!empty($site))
			{
				$parsed_url = @parse_url($site);
				if (($parsed_url !== false) && isset($parsed_url['host']))
				{
					$str .= '<br />'.'[<a href="'.html::escapeHTML($site).'"'.
						' title="'.html::escapeHTML($site).'">'.
						html::escapeHTML($parsed_url['host']).'</a>]';
				}
			}
			
			$str .= '</p>';
			
			echo
			'<div id="contribute-infos">'.'<h3>'.('Contribute').'</h3>'.
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