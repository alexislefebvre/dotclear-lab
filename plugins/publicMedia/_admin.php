<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Public Media.
# Copyright 2008 Moe (http://gniark.net/)
#
# Public Media is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Public Media is distributed in the hope that it will be useful,
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
	array('publicMediaAdmin','adminBeforeBlogSettingsUpdate'));
$core->addBehavior('adminBlogPreferencesForm',
	array('publicMediaAdmin','adminBlogPreferencesForm'));

$core->addBehavior('initWidgets',array('publicMediaAdmin','initWidgets'));

/**
@ingroup Public Media
@brief Admin
*/
class publicMediaAdmin
{
	/**
	adminBeforeBlogSettingsUpdate behavior
	@param	settings	<b>object</b>	Settings
	*/
	public static function adminBeforeBlogSettingsUpdate(&$settings)
	{
		global $core;

		$settings->setNameSpace('publicmedia');
		$settings->put('publicmedia_page_active',!empty($_POST['publicmedia_page_active']),
			'boolean','Activate Media Page');
		$settings->put('publicmedia_page_enable_sort',!empty($_POST['publicmedia_page_enable_sort']),
			'boolean','Allow visitors to choose how to sort files');
		$settings->put('publicmedia_page_file_sort',
			(!empty($_POST['publicmedia_page_file_sort']) ? $_POST['publicmedia_page_file_sort'] : ''),
			'string','file sort');
		$settings->put('publicmedia_page_root',
			(!empty($_POST['publicmedia_page_root']) ? $_POST['publicmedia_page_root'] : ''),
			'string', 'root directory');
		/*$settings->put('publicmedia_count_dl',
			(!empty($_POST['publicmedia_count_dl']) ? $_POST['publicmedia_count_dl'] : serialize(array())),
			'string', 'Download counter');*/
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
		'<legend>'.__('Media Page').'</legend>'.
		'<p>'.
		form::checkbox('publicmedia_page_active',1,$core->blog->settings->publicmedia_page_active).
		'<label class="classic" for="publicmedia_page_active">'.
		sprintf(__('Activate %s'),__('Media Page')).
		'</label>'.
		'</p>'.
		'<p class="form-note">'.
		sprintf(__('%s display media on a public page.'),__('Media Page')).
		'</p>'.
		'<p>'.
		form::checkbox('publicmedia_page_enable_sort',1,
				$core->blog->settings->publicmedia_page_enable_sort).
		'<label class="classic" for="publicmedia_page_enable_sort">'.
		__('Allow visitors to choose how to sort files').
		'</label> '.
		'</p>'.
		'<p>'.
		'<label for="publicmedia_page_file_sort">'.
		__('Sort files:').
		form::combo('publicmedia_page_file_sort',publicMedia::getSortValues(true),
			$core->blog->settings->publicmedia_page_file_sort).
		'</label> '.
		'</p>'.
		'<p class="form-note">'.
		__('Leave empty to cancel this feature.').
		'</p>'.
		'<p>'.
		'<label for="publicmedia_page_root">'.
		__('Limit display to a subdirectory :').
		form::combo('publicmedia_page_root',publicMedia::listDirs(),
			$core->blog->settings->publicmedia_page_root).
		'</label> '.
		'</p>'.
		'<p class="form-note">'.
		__('Leave empty to cancel this feature.').' '.
		__('The public directory will be used.').
		'</p>'.
		# filemanager->$exclude_list is protected
		'<p>'.
			sprintf(__('Files can be excluded from %1$s by editing <strong>%2$s</strong> in <strong>%3$s</strong>.'),
			__('Media Page'),'media_exclusion',__('about:config')).' '.
			sprintf(__('For example, to exclude %1$s and %2$s files : <code>%3$s</code>'),
			__('PNG'),__('JPG'),'/\.(png|jpg)/i').
		'</p>'.
		'<p>'.
		sprintf(__('URL of the %s :'),__('Media Page')).
		'<br />'.
		'<code>'.publicMedia::pageURL().'</code>'.
		'<br />'.
		'<a href="'.publicMedia::pageURL().'">'.sprintf(__('View %s'),__('Media Page')).'</a>'.	
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

		$w->create('Media',__('Media'),array('publicMediaWidget','show'));

		$w->Media->setting('title',__('Title:').' ('.__('optional').')',__('Media'),'text');

		$w->Media->setting('file_sort',__('Sort files:'),'','combo',
			publicMedia::getSortValues(true));

		$w->Media->setting('root',__('root directory:'),'','combo',
			publicMedia::listDirs());

		$w->Media->setting('block',__('Block display:'),'<ul>%s</ul>','text');

		$w->Media->setting('item',__('Item display:'),
			'<li><a href="%1$s" title="%3$s">%2$s</a></li>','text');

		$w->Media->setting('link',
			sprintf(__('Add a link to %s in the widget:'),__('Media Page')).
			' ('.__('optional').')',__('Media'),'text');

		$w->Media->setting('homeonly',__('Home page only'),false,'check');
	}
}

?>
