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

if (!defined('DC_RC_PATH')) {return;}

$core->addBehavior('adminBeforeBlogSettingsUpdate',
	array('contributeAdmin','adminBeforeBlogSettingsUpdate'));
$core->addBehavior('adminBlogPreferencesForm',
	array('contributeAdmin','adminBlogPreferencesForm'));

$core->addBehavior('adminPostFormSidebar',
	array('contributeAdmin','adminPostFormSidebar'));

$core->addBehavior('initWidgets',array('contributeAdmin','initWidgets'));

/**
@ingroup Contribute
@brief Admin
*/
class contributeAdmin
{
	/**
	adminBeforeBlogSettingsUpdate behavior
	@param	settings	<b>object</b>	Settings
	*/
	public static function adminBeforeBlogSettingsUpdate(&$settings)
	{
		global $core;

		$settings->setNameSpace('contribute');
		$settings->put('contribute_active',!empty($_POST['contribute_active']),
			'boolean','Enable DL Manager');
		$settings->put('contribute_user',
			(!empty($_POST['contribute_user']) ? $_POST['contribute_user'] : ''),
			'string', 'user');
		# inspirated by lightbox/admin.php
		$settings->setNameSpace('system');
	}

	/**
	adminBlogPreferencesForm behavior
	@param	core	<b>object</b>	Core
	@return	<b>string</b> XHTML
	*/
	public static function adminBlogPreferencesForm(&$core)
	{
		$users= array();
		
		$rs = $core->getUsers();
		
		while ($rs->fetch())
		{
			$name = $rs->user_id.' '.
				((strlen($rs->user_displayname) > 1) ?
				'('.$rs->user_displayname.') ' : '').
				$rs->user_firstname.' '.$rs->user_name;
			
			$users[$name.(($rs->user_id == $core->auth->userID())
				? ' ('.__('me').')' : '')] = $rs->user_id;
		}
		
		$user = ((!empty($core->blog->settings->contribute_user))
			? $core->blog->settings->contribute_user
			: $core->auth->userID());
		
		$posts = array();
		$rs = $core->blog->getPosts();
		
		while ($rs->fetch())
		{
			$posts[html::escapeHTML($rs->post_title)] = $rs->post_id;
		}
		
		$post = $core->blog->settings->contribute_default_post;
		
		echo '<fieldset>'.
		'<legend>'.('Contribute').'</legend>'.
		'<p>'.
		form::checkbox('contribute_active',1,
			$core->blog->settings->contribute_active).
		'<label class="classic" for="contribute_active">'.
		sprintf(__('Enable %s'),('Contribute')).
		'</label>'.
		'</p>'.
		'<p class="form-note">'.
		sprintf(__('%s allow visitors to contribute to your blog.'),
			('Contribute')).
		'</p>'.
		'<p>'.
		'<label for="contribute_user">'.
		__('Owner of the posts:').
		form::combo('contribute_user',$users,$user).
		'</label> '.
		'</p>'.
		'<p>'.
		'<label for="contribute_default_post">'.
		__('Default post:').
		form::combo('contribute_default_post',$posts,$post).
		'</label> '.
		'</p>'.
		'<p class="form-note">'.
		__('Create a new post and select it here.').' '.
		sprintf(__('The post can be %s or %s.'),__('pending'),__('unpublished')).
		'</p>'.
		'<p>'.
		sprintf(__('URL of the %s page:'),('Contribute')).
		'<br />'.
		'<code>'.$core->blog->url.$core->url->getBase('contribute').'</code>'.
		'<br />'.
		'<a href="'.$core->blog->url.$core->url->getBase('contribute').'">'.sprintf(__('View the %s page'),
			('Contribute')).'</a>'.	
		'</p>'.
		'</fieldset>';
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
			echo
			'<div id="planet-infos">'.'<h3>'.('Contribute').'</h3>'.
			'<p>'.sprintf(__('Post submitted by %s.'),'<a href="'.$site.'">'.$author.'</a>').'</p>'.
			'</div>';
		}
	}
	
	/**
	widget
	@param	w	<b>object</b>	Widget
	*/
	public static function initWidgets(&$w)
	{
		$w->create('contribute',__('Contribute'),array('contributeWidget','show'));

		$w->contribute->setting('title',__('Title:').' ('.__('optional').')',
			__('Contribute'),'text');
		
		$w->contribute->setting('text',__('Text:').' ('.__('optional').')',
			__('Write a post for this blog'),'text');
		
		$w->contribute->setting('homeonly',__('Home page only'),false,'check');
	}
}

?>
