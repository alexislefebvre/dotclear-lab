<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Arlequin, a plugin for Dotclear.
# 
# Copyright (c) 2007,2008,2011 Alex Pirine <alex pirine.fr>
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

require dirname(__FILE__).'/_widgets.php';

$_menu['Plugins']->addItem(__('Theme switcher'),'plugin.php?p=arlequin',
	'index.php?pf=arlequin/icon.png',
	preg_match('/plugin.php\?p=arlequin(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('contentadmin',$core->blog->id));

class adminArlequin
{
	public static function getDefaults()
	{
		return array(
			'e_html'=>'<li><a href="%1$s%2$s%3$s">%4$s</a></li>',
			'a_html'=>'<li><strong>%4$s</strong></li>',
			's_html'=>'<ul>%2$s</ul>',
			'homeonly'=>false);
	}
	
	public static function loadSettings(&$settings,&$initialized)
	{
		global $core;
		
		$initialized = false;
		$config = @unserialize($settings->config);
		$exclude = $settings->get('exclude');
	
		// Paramètres corrompus ou inexistants
		if ($config === false ||
			$exclude === null ||
			!(isset($config['e_html']) &&
			isset($config['a_html']) &&
			isset($config['s_html']) &&
			isset($config['homeonly'])))
		{
			$config = adminArlequin::getDefaults();
			$settings->put('config',serialize($config),'string','Arlequin configuration');
			$settings->put('exclude','customCSS','string','Excluded themes');
			$initialized = true;
			$core->blog->triggerBlog();
		}
		
		return array($config,$exclude);
	}
}
?>