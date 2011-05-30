<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2006-2008 Pep and contributors. All rights
# reserved. Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('adminBlogPreferencesForm',array('dc1redirectBehaviors','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('dc1redirectBehaviors','adminBeforeBlogSettingsUpdate'));

class dc1redirectBehaviors
{
	public static function adminBlogPreferencesForm($core,$settings)
	{
		if ($core->auth->isSuperAdmin())
		{
			$old_url = $settings->dc1redirect->dc1_old_url;
			$dc2_full_url = $core->blog->url;
			preg_match('|^[a-z]{3,}://[^/]*(/.*?)/?$|',$dc2_full_url,$matches);
			$old_url_default = $matches[1];

			$old_dc_url = $settings->dc1redirect->dc1_old_dc_url;
			$old_dc_url_default = dirname($settings->system->public_url);

			echo
			'<fieldset><legend>'.__('Dotclear 1 URLs').'</legend>'.
			'<p><label class="classic">'.
			form::checkbox('dc1_redirect','1',$settings->dc1redirect->dc1_redirect).
			__('Redirect Dotclear 1.x old URLs').'</label></p>'.
			'<p><label class="classic">'.__('Old blog URL path:').' '.
			form::field('dc1_old_url',50,0,html::escapeHTML($old_url)).
			'</label> '.__('Default: ').html::escapeHTML($old_url_default).'</p>'.
			'<p><label class="classic">'.__('Old DotClear URL path:').' '.
			form::field('dc1_old_dc_url',50,0,html::escapeHTML($old_dc_url)).
			'</label> '.__('Default: ').html::escapeHTML($old_dc_url_default).'</p>'.
			/*
			'<p><label class="classic">'.__('Migration date:').' '.
			form::field('dc1_mig_date',50,0,$settings->dc1redirect->dc1_mig_date).
			'</label></p>'.
			*/
			'<p><a href="plugin.php?p=dc1redirect">'.__('Redirect your Atom and RSS feeds').'</a></p>'.
			'</fieldset>';
		}
	}
	
	public static function adminBeforeBlogSettingsUpdate($settings)
	{
		if ($GLOBALS['core']->auth->isSuperAdmin())
		{
			$settings->addNameSpace('dc1redirect');
			try {
				$settings->dc1redirect->put('dc1_redirect',!empty($_POST['dc1_redirect']),'boolean','Redirect enabled');
			} catch (Exception $e) {
				$settings->dc1redirect->drop('dc1_redirect');
				$settings->dc1redirect->put('dc1_redirect',!empty($_POST['dc1_redirect']),'boolean','Redirect enabled');
			}
			try {
				$settings->dc1redirect->put('dc1_old_url',$_POST['dc1_old_url'],'string','Old blog URL',!empty($_POST['dc1_old_url']));
			} catch (Exception $e) {
				$settings->dc1redirect->drop('dc1_old_url');
				$settings->dc1redirect->put('dc1_old_url',$_POST['dc1_old_url'],'string','Old blog URL',!empty($_POST['dc1_old_url']));
			}
			try {
				$settings->dc1redirect->put('dc1_old_dc_url',$_POST['dc1_old_dc_url'],'string','Old DotClear URL path',!empty($_POST['dc1_old_dc_url']));
			} catch (Exception $e) {
				$settings->dc1redirect->drop('dc1_old_dc_url');
				$settings->dc1redirect->put('dc1_old_dc_url',$_POST['dc1_old_dc_url'],'string','Old DotClear URL path',!empty($_POST['dc1_old_dc_url']));
			}
			/*
			try {
				$settings->dc1redirect->put('dc1_mig_date',$_POST['dc1_mig_date'],'string','Migration date (yyyy-mm-dd)');
			} catch (Exception $e) {
				$settings->dc1redirect->drop('dc1_mig_date');
				$settings->dc1redirect->put('dc1_mig_date',$_POST['dc1_mig_date'],'string','Migration date (yyyy-mm-dd)');
			}
			*/
		}
	}
}
?>
