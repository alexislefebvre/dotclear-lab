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
		if (isset($_POST['contribute_delete_author'])
			&& ($_POST['contribute_delete_author'] == '1'))
		{
			$meta = new dcMeta($GLOBALS['core']);
			
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
		
		$infos = array();
		
		if (!empty($author))
		{
			// fixme : display fields
			if (!empty($author))
			{
				$infos[] = sprintf(__('Post submitted by %s'),$author);
			}
			if (!empty($mail))
			{
				$infos[] = sprintf(__('Email address : %s'),'<a href="mailto:'.$mail.'">'.$mail.'</a>');
			}
			if (!empty($site))
			{
				# prevent malformed URLs
				# inspirated by /dotclear/inc/clearbricks/net.http/class.net.http.php
				$parsed_url = @parse_url($site);
				if ($parsed_url != false)
				{
					$host = '['.$parsed_url['host'].']';
				}
				else
				{
					$host = '';
				}
				$infos[] = sprintf(__('Website : %s'),'<a href="'.$site.'">'.$host.'</a>');
			}
			if (!empty($infos))
			{
				$infos = '<ul><li>'.implode('</li><li>',$infos).'</li></ul>';
			}
			else
			{
				$infos = '';
			}			
			
			echo
			'<div id="planet-infos">'.'<h3>'.('Contribute').'</h3>'.
			$infos.
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