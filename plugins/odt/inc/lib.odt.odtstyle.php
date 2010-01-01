<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of odt, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class odtStyle
{

	protected static function _build_style_lib($tpldir)
	{
		$style_lib = array();
		if (!is_dir($tpldir)) {
			return false;
		}
		if ($dh = opendir($tpldir)) {
			while (($file = readdir($dh)) !== false) {
				if (! fnmatch("*.txt", $file)) {
					continue;
				}
				$style_xml = file_get_contents($tpldir."/".$file);
				$style = odtStyle::_build_style($style_xml);
				if (! $style) {
					continue;
				}
				$style_lib[$style["name"]] = $style;
			}
		} else {
			return false;
		}
		return $style_lib;
	}

	protected static function _build_style($style_xml)
	{
		if (preg_match('/.*style:name="([^"]+)".*/', $style_xml, $style_name_mo) == 0) {
			return false;
		}
		$style = array(
			"name" => $style_name_mo[1],
			"xml" => $style_xml,
		);
		$style["mainstyle"] = (strpos($style_xml, "style:display-name=") !== false);
		if (preg_match('/.*font-name="([^"]+)".*/', $style_xml, $need_font) > 0) {
			$style["need_font"] = $need_font[1];
		}
		return $style;
	}
	
	public static function add_styles($tpldir, $content_xml, $import_style_callback, $import_font_callback)
	{
		$style_lib = odtStyle::_build_style_lib($tpldir);
		foreach ($style_lib as $stylename => $styledata) {
			if (strpos($content_xml, 'style-name="'.$stylename.'"') === false) {
				continue; // style is not used
			}
			call_user_func($import_style_callback, $styledata["xml"], $styledata["mainstyle"]);
			if (array_key_exists("need_font", $styledata)) {
				$font_name = $styledata["need_font"];
				$font_xml = $style_lib[$font_name]["xml"];
				call_user_func($import_font_callback, $font_xml);
			}
		}
	}
}
?>
