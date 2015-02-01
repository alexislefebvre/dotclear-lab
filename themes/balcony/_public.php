<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Balcony, a theme for Dotclear.
#
# Copyright (c) 2010
# annso contact@as-i-am.fr
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/public');

# appel css menu
$core->addBehavior('publicHeadContent','balconymenu_publicHeadContent');

function balconymenu_publicHeadContent($core)
{
	$style = $core->blog->settings->themes->balcony_menu;
	if (!preg_match('/^simplemenu|menu-no$/',$style)) {
		$style = 'simplemenu';
	}

	$url = $core->blog->settings->themes_url.'/'.$core->blog->settings->theme;
	echo '<link rel="stylesheet" type="text/css" media="projection, screen" href="'.$url."/".$style.".css\" />\n";
}