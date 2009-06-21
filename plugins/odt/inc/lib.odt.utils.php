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

class odtUtils
{

	public static function getLink()
	{
		global $core;
		$url = '$core->blog->url.$core->url->getBase("odt")';
		if ($core->url->type != 'default' and $core->url->type != 'default-page') {
			$url .= '."/".$core->url->type."/".$_ctx->posts->post_url';
		}
		return $url;
	}

	public static function getButton($attr=false, $addclass="")
	{
		global $core, $_ctx;
		$url = self::getLink();
		if ($attr === false) { // no arg, return directly
			$url = eval('return '.$url.";");
		} else {
			$f = $core->tpl->getFilters($attr);
			$url = sprintf($f,$url);
			$url = '<?php echo '.$url.'; ?'.'>';
		}
		$image_url = $core->blog->getQmarkURL().'pf=odt/img/odt.png';
		$button = '<p class="odt '.$addclass.'"><a href="'.$url.'" title="'.
		__("Export to ODT").'"><img alt="ODT" class="odt" src="'.$image_url.
		'" /></a></p>';
		return $button;
	}

	public static function checkConfig()
	{
		if (! class_exists("XSLTProcessor")) {
			return false;
		}
		return true;
	}
}
?>
