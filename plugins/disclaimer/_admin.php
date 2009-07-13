<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of disclaimer, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) return;

$core->addBehavior('adminBeforeBlogSettingsUpdate',
	array('adminDisclaimer','adminBeforeBlogSettingsUpdate'));

$core->addBehavior('adminBlogPreferencesForm',
	array('adminDisclaimer','adminBlogPreferencesForm'));

class adminDisclaimer
{
	public static function adminBeforeBlogSettingsUpdate(&$blog_settings)
	{
		$blog_settings->setNameSpace('disclaimer');
		try {
			$blog_settings->put('disclaimer_active',abs((integer) $_POST['disclaimer_active']));
			$blog_settings->put('disclaimer_remember',abs((integer) $_POST['disclaimer_remember']));
			$blog_settings->put('disclaimer_redir',$_POST['disclaimer_redir']);
			$blog_settings->put('disclaimer_title',$_POST['disclaimer_title']);
			$blog_settings->put('disclaimer_text',$_POST['disclaimer_text']);
		}
		catch (Exception $e) {
			$blog_settings->drop('disclaimer_active');
			$blog_settings->put('disclaimer_active',0);
		}
		$blog_settings->setNameSpace('system');
	}

	public static function adminBlogPreferencesForm(&$core,$blog_settings)
	{
		echo
		'<fieldset><legend>'.__('Disclaimer').'</legend>'.
		'<div class="two-cols">'.
		'<div class="col">'.
		'<p><label class="classic">'.
		form::checkbox('disclaimer_active','1',$blog_settings->disclaimer_active).
		__('Enable disclaimer').'</label></p>'.
		'<p><label>'.__('Title:').
		form::field('disclaimer_title',30,255,html::escapeHTML($blog_settings->disclaimer_title)).
		'</label></p>'.
		'</div><div class="col">'.
		'<p><label class="classic">'.
		form::checkbox('disclaimer_remember','1',$blog_settings->disclaimer_remember).
		__('Remember user').'</label></p>'.
		'<p><label>'.__('Link output:').
		form::field('disclaimer_redir',30,255,html::escapeHTML($blog_settings->disclaimer_redir)).
		'</label></p>'.
		'</div></div>'.
		'<p class="area"><label for="disclaimer_text">'.__('Disclaimer:').'</label>'.
		form::textarea('disclaimer_text',60,5,html::escapeHTML($blog_settings->disclaimer_text)).'</p>'.
		'</fieldset>';
	}
}
?>