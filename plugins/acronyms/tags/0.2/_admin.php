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

class acronymsBehaviors
{
	public static function coreInitWikiPost(&$wiki2xhtml)
	{
		global $core;

		$acronyms = new dcAcronyms($core);

		$core->wiki2xhtml->setOpt('acronyms_file',$acronyms->file);
		$core->wiki2xhtml->acro_table = $acronyms->getList();
	}
}
