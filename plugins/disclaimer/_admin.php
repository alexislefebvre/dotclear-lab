<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of disclaimer, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) {

	return null;
}

$core->addBehavior(
	'adminBeforeBlogSettingsUpdate',
	array('adminDisclaimer', 'adminBeforeBlogSettingsUpdate')
);
$core->addBehavior(
	'adminBlogPreferencesHeaders',
	array('adminDisclaimer', 'adminDisclaimerHeaders')
);
$core->addBehavior(
	'adminBlogPreferencesForm',
	array('adminDisclaimer', 'adminBlogPreferencesForm')
);

/**
 * @ingroup DC_PLUGIN_DISCLAIMER
 * @brief Public disclaimer - Admin methods.
 * @since 2.6
 */
class adminDisclaimer
{

	/**
	 * Save settings
	 * 
	 * @param  dcSettings $blog_settings dcSettings instance
	 */
	public static function adminBeforeBlogSettingsUpdate(dcSettings $blog_settings)
	{
		$blog_settings->addNamespace('disclaimer');
		$s = $blog_settings->disclaimer;
		try {
			$disclaimer_active =
				isset($_POST['disclaimer_active']);
			$disclaimer_remember =
				isset($_POST['disclaimer_remember']);
			$disclaimer_redir =
				isset($_POST['disclaimer_redir']) ?
					$_POST['disclaimer_redir'] : '';
			$disclaimer_title =
				isset($_POST['disclaimer_title']) ?
					$_POST['disclaimer_title'] : '';
			$disclaimer_text =
				isset($_POST['disclaimer_text']) ?
					$_POST['disclaimer_text'] : '';
			$disclaimer_bots_unactive =
				isset($_POST['disclaimer_bots_unactive']);
			$disclaimer_bots_agents =
				isset($_POST['disclaimer_bots_agents']) ?
					$_POST['disclaimer_bots_agents'] : '';
			
			$s->put('disclaimer_active',		$disclaimer_active);
			$s->put('disclaimer_remember',	$disclaimer_remember);
			$s->put('disclaimer_redir',		$disclaimer_redir);
			$s->put('disclaimer_title',		$disclaimer_title);
			$s->put('disclaimer_text',		$disclaimer_text);
			$s->put('disclaimer_bots_unactive', $disclaimer_bots_unactive);
			$s->put('disclaimer_bots_agents',	$disclaimer_bots_agents);
		}
		catch (Exception $e) {
			$s->drop('disclaimer_active');
			$s->put('disclaimer_active', 0);
		}
	}

	public static function adminDisclaimerHeaders()
{
        global $core;

        $post_format = $core->auth->getOption('post_format');
        $post_editor = $core->auth->getOption('editor');

        $admin_post_behavior = '';
        if ($post_editor && !empty($post_editor[$post_format])) {
            $admin_post_behavior = $core->callBehavior('adminPostEditor', $post_editor[$post_format],
                                                       'disclaimer_text', array('#disclaimer_text')
            );
        }

	return
		dcPage::jsToolBar().
    $admin_post_behavior.
    dcPage::jsConfirmClose('opts-forms').
    dcPage::jsLoad('index.php?pf=disclaimer/js/config.js');
}
	/**
	 * Form
	 * 
	 * @param  dcCore     $core          dcCore instance
	 * @param  dcSettings $blog_settings dcSettings instance
	 */
	public static function adminBlogPreferencesForm(dcCore $core, dcSettings $blog_settings)
	{
		$blog_settings->addNamespace('disclaimer');
		$s = $blog_settings->disclaimer;
		$disclaimer_active		= (boolean) $s->disclaimer_active;
		$disclaimer_remember	= (boolean) $s->disclaimer_remember;
		$disclaimer_redir		= (string) $s->disclaimer_redir;
		$disclaimer_title		= (string) $s->disclaimer_title;
		$disclaimer_text		= (string) $s->disclaimer_text;
		$disclaimer_bots_unactive = (boolean) $s->disclaimer_bots_unactive;
		$disclaimer_bots_agents	= $s->disclaimer_bots_agents;
		if (!$disclaimer_bots_agents) {
			$disclaimer_bots_agents = 
			'bot;Scooter;Slurp;Voila;WiseNut;Fast;Index;Teoma;'.
			'Mirago;search;find;loader;archive;Spider;Crawler';
		}

		echo
		'<div class="fieldset">'.
		'<h4>'.__('Disclaimer').'</h4>'.

		'<div class="two-boxes">'.

		'<p><label class="classic" for="disclaimer_active">'.
		form::checkbox(
			'disclaimer_active',
			'1',
			$disclaimer_active
		).
		__('Enable disclaimer').'</label></p>'.

		'<p><label for="disclaimer_title">'.
		__('Title:').
		'</label>'.
		form::field(
			'disclaimer_title',
			30,
			255,
			html::escapeHTML($disclaimer_title)
		).
		'</p>'.

		'</div><div class="two-boxes">'.

		'<p><label class="classic">'.
		form::checkbox(
			'disclaimer_remember',
			'1',
			$disclaimer_remember
		).
		__('Remember the visitor').'</label></p>'.

		'<p><label for="disclaimer_redir">'.
		__('Link output:').
		'</label>'.
		form::field(
			'disclaimer_redir',
			30,
			255,
			html::escapeHTML($disclaimer_redir)
		).'</p>'.
    '<p class="form-note info">'.__('Leave blank to redirect to the site Dotclear').'</p>'.

		'</div><div class="clear">'.

		'<p class="area"><label for="disclaimer_text">'.
		__('Disclaimer:').
		'</label>'.
		form::textarea(
			'disclaimer_text',
			60,
			5,
			html::escapeHTML($disclaimer_text)
		).'</p>'.

		'<p><label for="disclaimer_bots_agents">'.
		__('List of robots allowed to index the site pages (separated by semicolons):').
		'</label>'.
		form::field(
			'disclaimer_bots_agents',
			120,
			255,
			html::escapeHTML($disclaimer_bots_agents)
		).'</p>'.

		'<p><label for="disclaimer_bots_unactive">'.
		form::checkbox(
			'disclaimer_bots_unactive',
			'1',
			$disclaimer_bots_unactive
		).
		__('Disable the authorization of indexing by search engines').
		'</label></p>'.

		'</div>'.

		'</div>';
	}
}