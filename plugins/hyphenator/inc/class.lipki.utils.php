<?php if (!defined('DC_RC_PATH')) {return;}
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of mettreEnOrdre,
# a plugin for DotClear2.
#
# Copyright (c) 2008 kévin Lepeltier [lipki] and contributors
#
# Licensed under the GPL version 3.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/gpl-3.0.txt
#
# -- END LICENSE BLOCK ------------------------------------

class LipkiUtils {
	
	public static function adminEnabledPlugin($core,$settings) {
		echo '<fieldset><legend>'.__('Plugins Enable').'</legend>';
		$core->callBehavior('adminEnabledPlugin',$core,$settings);
		echo '</fieldset>';
	}
	
}