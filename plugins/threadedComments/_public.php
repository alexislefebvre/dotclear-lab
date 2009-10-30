<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of threadedComments, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------


$core->addBehavior('publicHeadContent',
	array('threadedCommentsBehaviors','publicHeadContent'));

class threadedCommentsBehaviors
{
	public static function publicHeadContent(&$core,$_ctx)
	{
		if (!$core->blog->settings->threading_active) { return; }
		// print the headers
		echo "\n<!-- Threaded comments -->\n";
		echo (
			'<script type="text/javascript">'.
			'//<![CDATA['."\n".
			'var threading_indent = '.
			html::escapeHTML($core->blog->settings->threading_indent).
			';'."\n".
			'var threading_max_levels = '.
			html::escapeHTML($core->blog->settings->threading_max_levels).
			';'."\n".
			'var threading_switch_text = "'.
			html::escapeHTML($core->blog->settings->threading_switch_text).
			'";'."\n".
			'//]]>'.
			'</script>'."\n".
			'<script type="text/javascript" src="'.$core->blog->getQmarkURL().
			'pf=threadedComments/threadedComments.js'.'"></script>'."\n".
			'<style type="text/css" media="screen">'.
			'@import url('.$core->blog->getQmarkURL().
			'pf=threadedComments/threadedComments.css);</style>'."\n"
			);
	}

}

?>