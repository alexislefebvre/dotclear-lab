<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# Copyright (c) 2008 Steven Tlucek
#
# This work is licensed under the Creative Commons
# Attribution-Share Alike 3.0 Unported License.
# To view a copy of this license, visit
# http://creativecommons.org/licenses/by-sa/3.0/ or send a
# letter to Creative Commons, 171 Second Street, Suite 300,
# San Francisco, California, 94105, USA.
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('publicHeadContent','flavin_publicHeadContent');

function flavin_publicHeadContent($core)
{
	$style = $core->blog->settings->themes->flavin_color;
	if (!preg_match('/^pink|blue|green$/',$style)) {
		$style = 'pink';
	}
	
	$url = $core->blog->settings->system->themes_url.'/'.$core->blog->settings->system->theme;
	echo '<link rel="stylesheet" type="text/css" media="screen" href="'.$url."/".$style.".css\" />\n";
}