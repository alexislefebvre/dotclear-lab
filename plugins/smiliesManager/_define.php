<?php
# ***** BEGIN LICENSE BLOCK *****
# This is Contact, a plugin for DotClear. 
# Copyright (c) 2005 k-net. All rights reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */					"Smilies Manager",
	/* Description*/		"Manage and display your smilies",
	/* Author */				"k-net",
	/* Version */				'2.1.3',
	/* Permissions */		'usage,contentadmin'
);

class smiliesManager
{
	public static function getSmilies($theme = '') {
		global $core;
		
		if (empty($theme)) {
			$theme = $core->blog->settings->theme;
			if (!file_exists($core->blog->themes_path.'/'.$theme.'/smilies/smilies.txt') || filesize($core->blog->themes_path.'/'.$theme.'/smilies/smilies.txt') < 1) {
				$theme = 'default';
			}
		}
		
		$config = unserialize($core->blog->settings->smiliesmanager_smiliesontoolbar);
		
		$definition = $core->blog->themes_path.'/'.$theme.'/smilies/smilies.txt';
		$base_url = $core->blog->settings->themes_url.'/'.$theme.'/smilies/';
		
		$res = array();
		
		if (file_exists($definition)) {
			
			$def = file($definition);
			
			foreach ($def as $v) {
				$v = trim($v);
				if (preg_match('|^([^\t]*)[\t]+(.*)$|', $v, $matches)) {
					$res[] = array('code' => $matches[1], 'url' => $base_url.$matches[2], 'onToolbar' => !is_array($config) || in_array($matches[1], $config));
				}
			}
		}
		
		return $res;
	}
}

?>