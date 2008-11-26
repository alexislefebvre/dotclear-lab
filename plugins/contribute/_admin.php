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
	$core->auth->check('admin,contentadmin',$core->blog->id));

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
		$site = ($post) ? $meta->getMetaStr($post->post_meta,'contribute_site') : '';
		
		if (!empty($author))
		{
			if (!empty($site))
			{
				$author = '<a href="'.$site.'">'.$author.'</a>';
			}
			
			echo
			'<div id="planet-infos">'.'<h3>'.('Contribute').'</h3>'.
			'<p>'.sprintf(__('Post submitted by %s.'),$author).'</p>'.
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