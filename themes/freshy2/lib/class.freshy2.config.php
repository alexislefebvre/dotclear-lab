<?php 
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Freshy2, a theme for Dotclear.
# Original WP Theme from Julien de Luca
# (http://www.jide.fr/francais/)
#
# Copyright (c) 2008-2009
# Bruno Hondelatte dsls@morefnu.org
# Pierre Van Glabeke contact@brol.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class freshy2Config
{
	private $core;

	public function __construct($core) {
		$this->core = $core;
	}

	public function getCustomThemes() {
		$themedir = dirname(__FILE__).'/..';
		$themes = array();
		$themes['default']=null;
		if ($dh = opendir($themedir)) {
			while (($file = readdir($dh)) !== false) { 
				if(preg_match('/^custom_.*\.css$/i',$file)) {
					$custom = preg_replace('/^custom_(.*)\.css$/i','\\1',$file);
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
				if(substr($file,0,1) != '.' && preg_match('/^.*\.(jpg|png|gif)$/i',$file)) {
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
