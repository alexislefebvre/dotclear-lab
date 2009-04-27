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

if (!defined('DC_CONTEXT_ADMIN')) {return;}

$_menu['Plugins']->addItem(__('Download Manager'),
	'plugin.php?p=dlManager',
	'index.php?pf=dlManager/icon.png',
	preg_match('/plugin.php\?p=dlManager(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id));

$core->addBehavior('adminMediaItem',array('dlManagerAdmin','adminMediaItem'));

$core->addBehavior('adminMediaListItem',array('dlManagerAdmin','adminMediaListItem'));

/**
@ingroup Download manager
@brief Admin
*/
class dlManagerAdmin
{
	/**
	adminMediaItem behavior
	@param	file	<b>fileItem</b>	File item
	*/
	public static function adminMediaItem($file)
	{
		$count_dl = @unserialize($GLOBALS['core']->blog->settings->dlmanager_count_dl);
		if (!is_array($count_dl))
		{
			$count_dl = array();
		}
		
		$dl = '0';
		
		if ((isset($file->media_id))
			&& (array_key_exists($file->media_id,$count_dl)))
		{
				$dl = $count_dl[$file->media_id];
		}
		
		echo '<li><strong>'.__('Downloads').' :</strong> '.$dl.'</li>';
	}
	
	/**
	adminMediaListItem behavior
	@param	res	<b>string</b>	Result
	@param	file	<b>fileItem</b>	File item
	*/
	public static function adminMediaListItem($file)
	{
		$count_dl = @unserialize($GLOBALS['core']->blog->settings->dlmanager_count_dl);
		if (!is_array($count_dl))
		{
			$count_dl = array();
		}
		
		$dl = '0';
		
		if ((isset($file->media_id))
			&& (array_key_exists($file->media_id,$count_dl)))
		{
				$dl = $count_dl[$file->media_id];
		}
		
		return '<li><strong>'.__('Downloads').' :</strong> '.$dl.'</li>';
	}
}
?>