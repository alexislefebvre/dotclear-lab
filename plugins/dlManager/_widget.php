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

$core->addBehavior('initWidgets',array('dlManagerWidget','initWidgets'));


/**
@ingroup Download manager
@brief Widget
*/
class dlManagerWidget
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
			dlManager::listDirs(true,false));
		
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
	
	/**
	show widget
	@param	w	<b>object</b>	Widget
	@return	<b>string</b> XHTML
	*/
	public static function show(&$w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		if (!dlManager::inJail($w->root)) {return;}
		
		# from /dotclear/admin/media.php
		if ($w->file_sort) {
			$core->media->setFileSort($w->file_sort);
		}
		# /from /dotclear/admin/media.php

		$core->media->chdir($w->root);
		$core->media->getDir();
		
		$items_str = $str = '';

		$block_format = $w->block;
		if (empty($block_format)) {$block_format = '%s';}
		
		$item_format = $w->item;
		if (empty($item_format)) {$item_format = '%s';}
		
		if ($w->display_dirs)
		{
			$items = $core->media->dir['dirs'];
			
			# define root of DL Manager
			$page_root = $core->blog->settings->dlmanager_root;

			# used to remove root from path
			$page_root_len = strlen($page_root);
			
			# remove slash at the beginning of the string
			if ($page_root_len > 0) {$page_root_len += 1;}
			
			$parent_dir_full_path = path::real(
				dirname($core->media->root.'/'.$w->root));
			
			foreach ($items as $item) {
				if (!empty($item->relname))
				{
					$item->relname = substr($item->relname,$page_root_len);
					
					# display only subdirectories
					if ($item->file != $parent_dir_full_path)
					{
						$items_str .= sprintf($item_format,$core->blog->url.
							$core->url->getBase('media').'/'.$item->relname,
							$item->basename,$item->basename,'');
					}
				}
			}
			
			if (!empty($items_str))
			{
				if ($w->dirs_title)
				{
					$str .= '<h3>'.html::escapeHTML($w->dirs_title).'</h3>';
				}
				$str .= sprintf($block_format,$items_str);
			}
		}
		
		if ($w->display_files)
		{
			$items_str = '';
			$items = $core->media->dir['files'];
			
			foreach ($items as $item) {
				if ($item->media_type == 'image')
				{
					$icon = 'image.png';
				} elseif ($item->type == 'audio/mpeg3' )
				{
					$icon = 'music.png';
				} elseif ($item->type == 'video/x-flv'
					|| $item->type == 'video/mp4'
					|| $item->type == 'video/x-m4v')
				{
					$icon = 'film.png';
				} elseif ($item->type == 'application/zip')
				{
					$icon = 'briefcase.png';
				} elseif ($item->media_type == 'text')
				{
					$icon = 'page_white_text.png';
				} else
				{
					$icon = 'information.png';
				}
				
				$mediaplayer =
					'<a href="'.$core->blog->url.$core->url->getBase('mediaplayer').'/'.
					$item->media_id.'" title="'.__('Preview:').' '.$item->media_title.'">'.
					'<img src="'.$core->blog->getQmarkURL().
					'pf=dlManager/images/'.$icon.'" alt="'.__('Preview').'" />'.
					'</a>';
				
				$items_str .= sprintf($item_format,$core->blog->url.
					$core->url->getBase('download').'/'.$item->media_id,
					$item->media_title,$item->basename,$mediaplayer);
			}
			
			if (!empty($items_str))
			{
				if ($w->files_title)
				{
					$str .= '<h3>'.html::escapeHTML($w->files_title).'</h3>';
				}
				$str .= sprintf($block_format,$items_str);
			}
		}
		unset($items);

		# output
		$header = (strlen($w->title) > 0)
			? '<h2>'.html::escapeHTML($w->title).'</h2>' : null;

		$link = (strlen($w->link) > 0) ? '<p class="text"><a href="'.
			dlManager::pageURL().'">'.html::escapeHTML($w->link).'</a></p>' : null;

		return '<div class="dlmanager">'.$header.$str.$link.'</div>';
	}
}

?>