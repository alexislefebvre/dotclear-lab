<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of My Favicon, a plugin for Dotclear.
# 
# Copyright (c) 2008,2011 Alex Pirine <alex pirine.fr>
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('adminBlogPreferencesHeaders', array('myFavicon', 'adminBlogPreferencesHeaders'));
$core->addBehavior('adminBlogPreferencesForm',array('myFavicon','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('myFavicon','adminBeforeBlogSettingsUpdate'));

class myFavicon
{
	public static function adminBlogPreferencesHeaders()
	{
		return '<script type="text/javascript" src="index.php?pf=myfavicon/blog_pref.js"></script>';
	}
	
	public static function adminBlogPreferencesForm($core,$settings=false)
	{
		# Dotclear <=2.0-beta7 compatibility
		if ($settings === false) {
			$s = &$core->blog->settings->myfavicon;
		}
		else {
			$s = &$settings->myfavicon;
		}
		
		$favicon_url = $s->url;
		$favicon_ie6 = $s->ie6;
		
		echo
		'<fieldset><legend>Favicon</legend>'.
		'<p><label class="classic">'.
			form::checkbox('favicon_enable','1',!empty($favicon_url)).
			__('Enable favicon').'</label></p>'.
		'<div id="favicon_config">'.
		'<p><label class="classic">'.
			form::checkbox('favicon_ie6','1',$favicon_ie6).
			__('Enable Internet Explorer 6 compatibility').'</label></p>'.
		'<p><label>'.__('Favicon URL:').' '.
			form::field('favicon_url',40,255,html::escapeHTML($favicon_url)).'</label></p>'.
		'<p id="favicon_warn" class="form-note warn">'
			.__('Please note, IE6 compatibility works only with ".ico" format.').'</p>'.
		'</div></fieldset>';
	}
	
	public static function adminBeforeBlogSettingsUpdate($settings)
	{
		$favicon_url = empty($_POST['favicon_enable']) ? '' : $_POST['favicon_url'];
		$favicon_ie6 = !empty($_POST['favicon_ie6']);
		
		$s = &$settings->myfavicon;
		
		$s->put('url',$favicon_url);
		$s->put('ie6',$favicon_ie6);
	}
}
?>