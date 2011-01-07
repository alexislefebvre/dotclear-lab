<?php 
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Hyphenator plugin for Dotclear 2.
#
# Copyright (c) 2009 kévin Lepeltier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

/* Add behavior callback to add the js scripts */
$core->addBehavior('publicHeadContent',array('hyphenator','publicHeadContent'));

class hyphenator {

	public static function publicHeadContent(&$core) {
	
		if ($core->blog->settings->hyphenate_active) {
		
			/* Add the js scripts for hyphenate */
			echo '<script type="text/javascript" '.
				'src="index.php?pf=hyphenator/js/hyphenator.js"></script>'."\r".
				'<script type="text/javascript">'."\r".
				'//<![CDATA['."\r".
				'Hyphenator.config({classname:"post-content"});'."\r".
				'Hyphenator.run();'."\r".
				'//]]>'."\r".
				'</script>'."\r";
		}
	}
}