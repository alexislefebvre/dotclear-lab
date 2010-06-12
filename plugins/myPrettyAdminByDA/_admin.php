<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of myPrettyAdminByDA, a plugin for Dotclear 2.
#
# Copyright (c) 2009 Philippe Amalgame and DotAddict contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('adminPageHTMLHead',array('customAdmin','adminCssLink'));

class customAdmin
{
	public static function adminCssLink()
	{
		global $core;
		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		echo
		'<style type="text/css">'."\n".
		'@import url('.$url.'/css/admin.css);'."\n".
		"</style>\n";
	}
}
?>