<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Coal Dust, a Dotclear 2 theme.
#
# Copyright (c) 2003-2009 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

if ($core->blog->settings->themes->coaldust_hreflang)
{
	$core->addBehavior('publicHeadContent',
		array('tplCoalDustTheme','publicHeadContent'));
}

class tplCoalDustTheme
{
	public static function publicHeadContent($core)
	{
		echo '<style type="text/css">'."\n".
			'a[hreflang]:after {'.
				'content: "\0000a0(" attr(hreflang) ")";'.
				'font-size:smaller;'.
			'}'.
		"\n".
		'</style>'."\n";
	}
}