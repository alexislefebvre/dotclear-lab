<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of walouwalou, a theme for Dotclear 2.
# 
# Copyright (c) 2009 Osku
## Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class walouwalouConfig
{
	protected static $fonts = array(
		'sans-serif' => array(
			'ss1' => '"Helvetica Neue", Arial, Helvetica, sans-serif',
			'ss2' => '"DejaVu Sans", "Lucida Sans Unicode", "Lucida Grande"," Lucida Sans", sans-serif',
			'ss3' => '"Gill Sans", Calibri, "Trebuchet MS", sans-serif '
		),
		
		'serif' => array(
			's1' => 'Times, "Times New Roman", serif',
			's2' => 'Cambria, Georgia, serif' ,
			's3' => 'Palatino, "Palatino Linotype", serif' 
		)
	);
	
	protected static $fonts_combo = array();
	protected static $fonts_list = array();
	
	public static function fontsList()
	{
		if (empty(self::$fonts_combo))
		{
			self::$fonts_combo[__('default')] = '';
			foreach (self::$fonts as $family => $g)
			{
				$fonts = array();
				foreach ($g as $code => $font) {
					$fonts[str_replace('"','',$font)] = $code;
				}
				self::$fonts_combo[$family] = $fonts;
			}
		}
		
		return self::$fonts_combo;
	}
	
	public static function fontDef($c)
	{
		if (empty(self::$fonts_list))
		{
			foreach (self::$fonts as $family => $g)
			{
				foreach ($g as $code => $font) {
					self::$fonts_list[$code] = $font;
				}
			}
		}
		
		return isset(self::$fonts_list[$c]) ? self::$fonts_list[$c] : null;
	}
}
?>