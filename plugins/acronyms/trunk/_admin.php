<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of acronyms, a plugin for DotClear2.
#
# Copyright (c) 2008 Vincent Garnier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

require_once dirname(__FILE__).'/_widgets.php';

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/main');

$_menu['Blog']->addItem(__('Acronyms Manager'),'plugin.php?p=acronyms','index.php?pf=acronyms/icon.png',
		preg_match('/plugin.php\?p=acronyms(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('acronyms',$core->blog->id));

$core->auth->setPermissionType('acronyms',__('manage acronyms'));

$core->addBehavior('coreInitWikiPost',array('acronymsAdminBehaviors','coreInitWikiPost'));

$core->addBehavior('adminPostHeaders',array('acronymsAdminBehaviors','jsLoad'));
$core->addBehavior('adminPageHeaders',array('acronymsAdminBehaviors','jsLoad'));
$core->addBehavior('adminRelatedHeaders',array('acronymsAdminBehaviors','jsLoad'));
$core->addBehavior('adminDashboardHeaders',array('acronymsAdminBehaviors','jsLoad'));

$core->addBehavior('adminBlogPreferencesForm',array('acronymsAdminBehaviors','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('acronymsAdminBehaviors','adminBeforeBlogSettingsUpdate'));

class acronymsAdminBehaviors
{
	public static function coreInitWikiPost($wiki2xhtml)
	{
		$acronyms = new dcAcronyms($GLOBALS['core']);

		$wiki2xhtml->setOpt('acronyms_file',$acronyms->file);
		$wiki2xhtml->acro_table = $acronyms->getList();
	}

	public static function jsLoad()
	{
		if ($GLOBALS['core']->blog->settings->acronyms_button_enabled)
		{
			return
			'<script type="text/javascript" src="index.php?pf=acronyms/post.js"></script>'.
			'<script type="text/javascript">'."\n".
			"//<![CDATA[\n".
			dcPage::jsVar('jsToolBar.prototype.elements.acronyms.title',__('Acronym'))."\n".
			dcPage::jsVar('jsToolBar.prototype.elements.acronyms.msg_title',__('Title?'))."\n".
			dcPage::jsVar('jsToolBar.prototype.elements.acronyms.msg_lang',__('Lang?')).
			"\n//]]>\n".
			"</script>\n";
		}
	}

	public static function adminBlogPreferencesForm($core,$settings)
	{
		echo
		'<fieldset><legend>'.__('Acronyms Manager').'</legend>'.
		'<p><label class="classic">'.
		form::checkbox('acronyms_button_enabled','1',$settings->acronyms_button_enabled).
		__('Enable acronyms button on toolbar').'</label></p>'.
		'<p><label class="classic">'.
		form::checkbox('acronyms_public_enabled','1',$settings->acronyms_public_enabled).
		__('Enable acronyms public page').'</label></p>'.
		'</fieldset>';
	}

	public static function adminBeforeBlogSettingsUpdate($settings)
	{
		$settings->addNameSpace('acronyms');
		$settings->acronyms->put('acronyms_button_enabled',!empty($_POST['acronyms_button_enabled']),'boolean');
		$settings->acronyms->put('acronyms_public_enabled',!empty($_POST['acronyms_public_enabled']),'boolean');
		$settings->addNameSpace('system');
	}

} # class acronymsAdminBehaviors


$core->addBehavior('adminDashboardFavs',array('acronymsfavBehaviors','dashboardFavs'));

class acronymsfavBehaviors
{
    public static function dashboardFavs($core,$favs)
    {
        $favs['Acronyms'] = new ArrayObject(array(
            'Acronyms',
            __('Acronyms'),
            'plugin.php?p=acronyms',
            'index.php?pf=acronyms/icon.png',
            'index.php?pf=acronyms/icon-big.png',
            'usage,contentadmin',
            null,
            null));
    }
}
?>
