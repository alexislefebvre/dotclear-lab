<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of plugin randomComment for Dotclear 2.
# Copyright (c) 2008 Thomas Bouron.
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
global $core;

$w = null;

$types = array(
	'nav',
	'extra'
);

foreach($types as $type)
{
	$widgets = dcWidgets::load($core->blog->settings->{'widgets_'.$type});

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
		'<p>'.$rd->getWidgetContent().'</p>'.
		'<p>'.$rd->getWidgetInfo().'</p>';
}

?>
