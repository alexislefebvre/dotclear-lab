<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of footnotesToolbar, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------


$core->addBehavior('publicHeadContent',
	array('footnotesToolbarBehaviors','publicHeadContent'));

class footnotesToolbarBehaviors
{
	public static function publicHeadContent(&$core,$_ctx)
	{
		echo "\n<!-- Better footnotes -->\n";
		echo (
			'<script type="text/javascript" src="'.$core->blog->getQmarkURL().
			'pf=footnotesToolbar/betterfootnotes.js'.'"></script>'."\n".
			'<style type="text/css" media="screen">'.
			'@import url('.$core->blog->getQmarkURL().
			'pf=footnotesToolbar/betterfootnotes.css);</style>'."\n"
			);
	}

}

?>
