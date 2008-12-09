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

$GLOBALS['__autoload']['dcAcronyms'] = dirname(__FILE__).'/class.dc.acronyms.php';

$_menu['Blog']->addItem(__('Acronyms Manager'),'plugin.php?p=acronyms','index.php?pf=acronyms/icon.png',
		preg_match('/plugin.php\?p=acronyms(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('acronyms',$core->blog->id));

$core->auth->setPermissionType('acronyms',__('manage acronyms'));

$core->addBehavior('coreInitWikiPost',array('acronymsBehaviors','coreInitWikiPost'));
$core->addBehavior('adminPostHeaders',array('acronymsBehaviors','jsLoad'));
$core->addBehavior('adminPageHeaders',array('acronymsBehaviors','jsLoad'));
$core->addBehavior('adminRelatedHeaders',array('acronymsBehaviors','jsLoad'));
$core->addBehavior('adminDashboardHeaders',array('acronymsBehaviors','jsLoad'));

class acronymsBehaviors
{
	public static function coreInitWikiPost(&$wiki2xhtml)
	{
		global $core;

		$acronyms = new dcAcronyms($core);

		$wiki2xhtml->setOpt('acronyms_file',$acronyms->file);
		$wiki2xhtml->acro_table = $acronyms->getList();
	}

	public static function jsLoad()
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
