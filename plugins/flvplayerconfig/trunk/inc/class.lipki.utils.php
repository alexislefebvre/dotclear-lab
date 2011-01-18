<?php if (!defined('DC_RC_PATH')) {return;}
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of lePluginDuJour, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 lipki and contributors
# kevin@lepeltier.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class LipkiUtils {

	public static $add = false;
	
	public static function adminEnabledPlugin($core,$settings) {
		if( !self::$add ) {
			echo '<fieldset><legend>'.__('Plugins Enable').'</legend>';
			$core->callBehavior('adminEnabledPlugin',$core,$settings);
			echo '</fieldset>';
			self::$add = true;
		}
	}
	
}
