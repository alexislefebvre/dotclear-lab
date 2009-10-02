<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of "translater", a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$new_version = $core->plugins->moduleInfo('translater','version');
$old_version = $core->getVersion('translater');

if (version_compare($old_version,$new_version,'>=')) return;

try
{
	if (!version_compare(DC_VERSION,'2.1.5','>=')) {

		throw new Exception('translater plugin requires Dotclear 2.1.5');
	}
	$s =& $core->blog->settings;

	$s->setNameSpace('translater');
	$s->put('translater_light_face',0,'boolean','Use easy and light interface',false,true);
	$s->put('translater_two_cols',0,'boolean','Use two columns in admin options',false,true);
	$s->put('translater_plugin_menu',0,'boolean','Put an link in plugins page',false,true);
	$s->put('translater_theme_menu',0,'boolean','Put a link in themes page',false,true);
	$s->put('translater_backup_auto',1,'boolean','Make a backup of languages old files when there are modified',false,true);
	$s->put('translater_backup_limit',20,'string','Maximum backups per module',false,true);
	$s->put('translater_backup_folder','module','string','In which folder to store backups',false,true);
	$s->put('translater_write_po',1,'boolean','Write .po languages files',false,true);
	$s->put('translater_write_langphp',1,'boolean','Write .lang.php languages files',false,true);
	$s->put('translater_scan_tpl',0,'boolean','Translate strings of templates files',false,true);
	$s->put('translater_parse_nodc',1,'boolean','Translate only untranslated strings of Dotclear',false,true);
	$s->put('translater_parse_comment',1,'boolean','Write comments and strings informations in lang files',false,true);
	$s->put('translater_parse_user',1,'boolean','Write inforamtions about author in lang files',false,true);
	$s->put('translater_parse_userinfo','displayname, email','string','Type of informations about user to write',false,true);
	$s->put('translater_import_overwrite',0,'boolean','Overwrite existing languages when import packages',false,true);
	$s->put('translater_export_filename','type-module-l10n-timestamp','string','Name of files of exported package',false,true);
	$s->put('translater_proposal_tool','google','string','Id of default tool for proposed translation',false,true);
	$s->put('translater_proposal_lang','en','string','Default source language for proposed translation',false,true);
	$s->setNameSpace('system');

	$core->setVersion('translater',$core->plugins->moduleInfo('translater','version'));

	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}
return false;
?>