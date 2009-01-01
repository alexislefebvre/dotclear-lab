<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2008 Olivier Meunier and contributors. All rights
# reserved.
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

class freshy2Config
{
	private $core;

	public function __construct(&$core) {
		$this->core = $core;
	}

	public function getCustomThemes() {
		$themedir = dirname(__FILE__).'/..';
		$themes = array();
		$themes['default']=null;
		if ($dh = opendir($themedir)) {
			while (($file = readdir($dh)) !== false) { 
				if(eregi('^custom_.*\.css$',$file)) {
					$custom = eregi_replace('^custom_(.*)\.css$','\\1',$file);
					$themes[$custom]=$file;
				}
			}
		}
		return $themes;
	}

	public function getHeaderImages() {
		$headerdir = dirname(__FILE__).'/../images/headers';
		$images = array();
		$prefix = 'blog_theme.php?shot=freshy2&amp;src=images/headers/';
		if ($dh = opendir($headerdir)) {
			while (($file = readdir($dh)) !== false) { 
				if(substr($file,0,1) != '.' && eregi('^.*\.(jpg|png|gif)$',$file)) {
					$images[$file]=array();
					$images[$file]['img']=$prefix.$file;
					$thumb = preg_replace('/^(.*).(jpg|gif|png)$/','.$1_s.$2',$file);
					if (file_exists($headerdir.'/'.$thumb))
						$images[$file]['thumb']=$prefix.$thumb;
					else
						$images[$file]['thumb']=$prefix.$file;

				}
			}
		}
		return $images;

	}

}
?>
