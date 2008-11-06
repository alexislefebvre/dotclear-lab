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

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(__('Download Manager'),
	'plugin.php?p=dlManager',
	'index.php?pf=dlManager/icon.png',
	preg_match('/plugin.php\?p=dlManager(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id));

$core->addBehavior('initWidgets',array('dlManagerAdmin','initWidgets'));

/**
@ingroup Download manager
@brief Admin
*/
class dlManagerAdmin
{
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
