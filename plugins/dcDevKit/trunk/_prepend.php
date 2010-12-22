<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcDevKit, a plugin for Dotclear.
# 
# Copyright (c) 2010 Tomtom, Dsls and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$__autoload['dcDevKit'] = dirname(__FILE__).'/inc/class.dc.dev.kit.php';
$__autoload['dcDevModule'] = dirname(__FILE__).'/inc/class.dc.dev.module.php';
$__autoload['zipBuilder'] = dirname(__FILE__).'/inc/class.zipbuilder.php';
$__autoload['JSMinPlus'] = dirname(__FILE__).'/inc/class.jsmin.php';
$__autoload['cssmin'] = dirname(__FILE__).'/inc/class.cssmin.php';
$__autoload['moduleBootstrap'] = dirname(__FILE__).'/inc/class.module.bootstrap.php';
$__autoload['moduleTranslater'] = dirname(__FILE__).'/inc/class.module.translater.php';
# Load modules
$__autoload['dcDevModuleConfig'] = dirname(__FILE__).'/modules/class.dc.dev.module.config.php';
$__autoload['dcDevModuleBootstrap'] = dirname(__FILE__).'/modules/class.dc.dev.module.bootstrap.php';
$__autoload['dcDevModuleTranslater'] = dirname(__FILE__).'/modules/class.dc.dev.module.translater.php';
$__autoload['dcDevModulePackager'] = dirname(__FILE__).'/modules/class.dc.dev.module.packager.php';

$core->addBehavior('dcDevKitConstructor',array('dcDevKitBehaviors','dcDevKitConstructor'));
$core->addBehavior('dcDevKitPackagerContentFile',array('dcDevKitBehaviors','dcDevKitPackagerContentFile'));

class dcDevKitBehaviors
{
	public static function dcDevKitConstructor($devkit)
	{
		$devkit->addModule('dcDevModuleConfig');
		$devkit->addModule('dcDevModuleBootstrap');
		$devkit->addModule('dcDevModulePackager');
	}
	
	public static function dcDevKitPackagerContentFile($file)
	{
		global $core;
		
		# Minify *.css files
		if ($file['ext'] === 'css' && $core->blog->settings->dcDevKit->packager_minify_css) {
			$file['content'] = cssmin::minify($file['content']);
		}
		
		# Minify *.js files
		if ($file['ext'] === 'js' && $core->blog->settings->dcDevKit->packager_minify_js) {
			$file['content'] = JSMinPlus::minify($file['content']);
		}
	}
}

?>