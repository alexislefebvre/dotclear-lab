<?php if (!defined('DC_RC_PATH')) {return;}
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

class dcHyphenator extends dcTemplate {
	
	public static function adminEnabledPlugin($core, $settings) {
		echo '<p><label class="classic">'.
		form::checkbox('hyphenator_enabled','1',$settings->hyphenator->enabled).
		__('Enable hyphenator').'</label></p>'.
		'<p class="form-note">'.$core->plugins->moduleInfo('hyphenator','desc').'</p>';
	}
	
	public static function adminBeforeBlogSettingsUpdate($settings) {
		$settings->addNameSpace('hyphenator');
		$settings->hyphenator->put('enabled',!empty($_POST['hyphenator_enabled']),'boolean');
	}
	
}