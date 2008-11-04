<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of DL Manager.
# Copyright 2008 Moe (http://gniark.net/) and Tomtom (http://blog.zenstyle.fr)
#
# DL Manager is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# DL Manager is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Images are from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

$core->addBehavior('adminBeforeBlogSettingsUpdate',
	array('dlManagerAdmin','adminBeforeBlogSettingsUpdate'));
$core->addBehavior('adminBlogPreferencesForm',
	array('dlManagerAdmin','adminBlogPreferencesForm'));

$core->addBehavior('initWidgets',array('dlManagerAdmin','initWidgets'));

/**
@ingroup Download manager
@brief Admin
*/
class dlManagerAdmin
{
	/**
	adminBeforeBlogSettingsUpdate behavior
	@param	settings	<b>object</b>	Settings
	*/
	public static function adminBeforeBlogSettingsUpdate(&$settings)
	{
		global $core;

		$settings->setNameSpace('dlmanager');
		$settings->put('dlmanager_active',!empty($_POST['dlmanager_active']),
			'boolean','Enable DL Manager');
		$settings->put('dlmanager_counter',!empty($_POST['dlmanager_counter']),
			'boolean','Enable download counter');
		$settings->put('dlmanager_attachment_url',!empty($_POST['dlmanager_attachment_url']),
			'boolean','Redirect attachments links to DL Manager');
		$settings->put('dlmanager_enable_sort',!empty($_POST['dlmanager_enable_sort']),
			'boolean','Allow visitors to choose how to sort files');
		$settings->put('dlmanager_file_sort',
			(!empty($_POST['dlmanager_file_sort']) ? $_POST['dlmanager_file_sort'] : ''),
			'string','file sort');
		$settings->put('dlmanager_root',
			(!empty($_POST['dlmanager_root']) ? $_POST['dlmanager_root'] : ''),
			'string', 'root directory');
		# inspirated from lightbox/admin.php
		$settings->setNameSpace('system');
	}

	/**
	adminBlogPreferencesForm behavior
	@param	core	<b>object</b>	Core
	@return	<b>string</b> XHTML
	*/
	public static function adminBlogPreferencesForm(&$core)
	{
		echo '<fieldset>'.
		'<legend>'.__('Download manager').'</legend>'.
		'<p>'.
		form::checkbox('dlmanager_active',1,
			$core->blog->settings->dlmanager_active).
		'<label class="classic" for="dlmanager_active">'.
		sprintf(__('Enable the %s'),__('Download manager')).
		'</label>'.
		'</p>'.
		'<p class="form-note">'.
		sprintf(__('The %s display media on a public page.'),
			__('Download manager')).
		'</p>'.
		'<p>'.
		form::checkbox('dlmanager_counter',1,
			$core->blog->settings->dlmanager_counter).
		'<label class="classic" for="dlmanager_counter">'.
		__('Enable the download counter').
		'</label>'.
		'</p>'.
		'<p>'.
		form::checkbox('dlmanager_attachment_url',1,
			$core->blog->settings->dlmanager_attachment_url).
		'<label class="classic" for="dlmanager_attachment_url">'.
		sprintf(__('Redirect attachments links to %s'),
		__('Download manager')).
		'</label>'.
		'</p>'.
		'<p class="form-note">'.
		__('When downloading an attachment, the download counter will be increased.').' '.
		sprintf(__('This will redefine the %s tag.'),
			'<strong>{{tpl:AttachmentURL}}</strong>').
		'</p>'.
		'<p>'.
		form::checkbox('dlmanager_enable_sort',1,
				$core->blog->settings->dlmanager_enable_sort).
		'<label class="classic" for="dlmanager_enable_sort">'.
		__('Allow visitors to choose how to sort files').
		'</label> '.
		'</p>'.
		'<p>'.
		'<label for="dlmanager_file_sort">'.
		__('Sort files:').
		form::combo('dlmanager_file_sort',dlManager::getSortValues(true),
			$core->blog->settings->dlmanager_file_sort).
		'</label> '.
		'</p>'.
		'<p class="form-note">'.
		__('Leave empty to cancel this feature.').
		'</p>'.
		'<p>'.
		'<label for="dlmanager_root">'.
		sprintf(__('Change root of %s:'),__('Download manager')).
		form::combo('dlmanager_root',dlManager::listDirs(),
			$core->blog->settings->dlmanager_root).
		'</label> '.
		'</p>'.
		'<p class="form-note">'.
		__('Leave empty to cancel this feature.').' '.
		sprintf(__('This will change the root of the %s page and of the widget.'),
		__('Download manager')).' '.
		sprintf(__('If you change this setting, reconfigure the %s widget.'),
		__('Download manager')).
		'</p>'.
		# filemanager->$exclude_list is protected
		'<p>'.
			sprintf(
			__('Files can be excluded from %1$s by editing <strong>%2$s</strong> in <strong>%3$s</strong>.'),
			__('Download manager'),'media_exclusion',__('about:config')).' '.
			sprintf(__('For example, to exclude %1$s and %2$s files : <code>%3$s</code>'),
			__('PNG'),__('JPG'),'/\.(png|jpg)/i').
		'</p>'.
		'<p>'.
		sprintf(__('URL of the %s page :'),__('Download manager')).
		'<br />'.
		'<code>'.dlManager::pageURL().'</code>'.
		'<br />'.
		'<a href="'.dlManager::pageURL().'">'.sprintf(__('View the %s page'),
			__('Download manager')).'</a>'.	
		'</p>'.
		'</fieldset>';
	}

	/**
	widget
	@param	w	<b>object</b>	Widget
	*/
	public static function initWidgets(&$w)
	{
		# set timezone
		global $core;
		$tz = $core->blog->settings->blog_timezone;

		$w->create('dlManager',__('Download manager'),
			array('dlManagerWidget','show'));

		$w->dlManager->setting('title',__('Title:').' ('.__('optional').')',
			__('Download manager'),'text');

		$w->dlManager->setting('file_sort',__('Sort files:'),'','combo',
			dlManager::getSortValues(true));

		$w->dlManager->setting('root',__('root directory:'),'','combo',
			dlManager::listDirs(true));
		
		$w->dlManager->setting('display_dirs',__('Display subdirectories'),
			true,'check');
		
		$w->dlManager->setting('dirs_title',__('Subdirectories title:').
			' ('.__('optional').')',__('Directories'),'text');
		
		$w->dlManager->setting('display_files',__('Display files'),
			true,'check');
		
		$w->dlManager->setting('files_title',__('Files title:').
			' ('.__('optional').')',__('Files'),'text');

		$w->dlManager->setting('block',__('Block display:'),'<ul>%s</ul>','text');

		$w->dlManager->setting('item',__('Item display:'),
			'<li><a href="%1$s" title="%3$s">%2$s</a> %4$s</li>','textarea');

		$w->dlManager->setting('link',
			sprintf(__('Add a link to %s in the widget:'),__('Download manager')).
			' ('.__('optional').')',__('Download manager'),'text');

		$w->dlManager->setting('homeonly',__('Home page only'),false,'check');
	}
}

?>
