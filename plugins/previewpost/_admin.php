<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Preview plugin.
# Copyright (c) 2008 Bruno Hondelatte,  and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# Preview plugin for DC2 is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

$core->addBehavior('adminPostHeaders',array('previewBehaviors','postHeaders'));


class previewBehaviors {
	public static function postHeaders()
	{
		global $post_url,$core;
		return '<script type="text/javascript" src="index.php?pf=previewpost/js/post.js"></script>'.
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		"dotclear.preview_url = '".$core->blog->url.'preview/'.html::sanitizeURL($post_url)."';\n".
		"dotclear.msg.preview = '".__('Preview post')."';".
		"\n//]]>\n".
		"</script>\n";
	}
}
?>
