<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of randomComment, a plugin for Dotclear.
# 
# Copyright (c) 2009-2015 Tomtom and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

global $core;

$w = null;

$types = array(
	'nav',
	'extra',
	'custom'
);

foreach($types as $type)
{
	$widgets = dcWidgets::load($core->blog->settings->widgets->{'widgets_'.$type});

	foreach($widgets->elements() as $k => $v)
	{
		if ($v->id() == 'randomcomment') {
			$w = $v;
			break 2;
		}
	}
}

if (!empty($w)) {
	$rd = new randomComment($core,$w);
	$rd->getRandomComment();

	echo
		'<li id="rd_text">'.$rd->getWidgetContent().'</li>'.
		'<li id="rd_info">'.$rd->getWidgetInfo().'</li>';
}