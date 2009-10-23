<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of randomComment, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
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