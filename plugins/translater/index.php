<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of translater, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Check user perms
dcPage::check('admin,translater');

# Load translations of alert messages
$_lang = $core->auth->getInfo('user_lang');
$_lang = preg_match('/^[a-z]{2}(-[a-z]{2})?$/',$_lang) ? $_lang : 'en';
l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/error');
l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/admin');

# Load main class
$O = new dcTranslater($core);

# Init some vars
$p_url 	= 'plugin.php?p=translater';
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
$start_part = 'setting';
$default_part = isset($_REQUEST['part']) ? $_REQUEST['part'] : $start_part;
$section = isset($_REQUEST['section']) ? $_REQUEST['section'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$module = isset($_REQUEST['module']) ? $_REQUEST['module'] : '';
$from = isset($_POST['from']) && $_POST['from'] != '-' ? $_POST['from'] : '';
$lang = isset($_REQUEST['lang']) && $_REQUEST['lang'] != '-' ? $_REQUEST['lang'] : '';
if ($type == '-' || $module == '-')
{
	$type = $module = '';
}

# Common page header
$header = 
dcPage::jsLoad('js/_posts_list.js').
dcPage::jsLoad('index.php?pf=translater/js/main.js').
dcPage::jsLoad('index.php?pf=translater/js/jquery.translater.js').
'<script type="text/javascript">'."\n//<![CDATA[\n".
dcPage::jsVar('jcToolsBox.prototype.text_wait',__('Please wait')).
dcPage::jsVar('jcToolsBox.prototype.section',$section).
"\n//]]>\n</script>\n";

if ('' != $O->proposal_tool)
{
	$header .= 
	'<style type="text/css">'.
	' .addfield, .togglelist { border: none; }'.
	"</style>\n".
	"<script type=\"text/javascript\"> \n".
	"//<![CDATA[\n".
	" \$(function(){if(!document.getElementById){return;} \n".
	"  \$.fn.translater.defaults.url = '".html::escapeJS('services.php')."'; \n".
	"  \$.fn.translater.defaults.func = '".html::escapeJS('getProposal')."'; \n".
	"  \$.fn.translater.defaults.from = '".html::escapeJS($O->proposal_lang)."'; \n".
	"  \$.fn.translater.defaults.to = '".html::escapeJS($lang)."'; \n".
	"  \$.fn.translater.defaults.tool = '".html::escapeJS($O->proposal_tool)."'; \n".
	"  \$.fn.translater.defaults.title = '".html::escapeJS(sprintf(__('Use this %s translation:'),$O->proposal_tool))."'; \n".
	"  \$.fn.translater.defaults.title_go = '".html::escapeJS(sprintf(__('Translate this text with %s'),$O->proposal_tool))."'; \n".
	"  \$.fn.translater.defaults.title_add = '".html::escapeJS(__('Use this text'))."'; \n".
	"  \$('.translaterline').translater(); \n".
	"})\n".
	"//]]>\n".
	"</script>\n";
}

# Common menu
$menu = 
'<h2>'.__('Translater').
' - <a class="button" href="'.$p_url.'&amp;part=modules&amp;type=plugin">'.__('Plugins').'</a>'.
' - <a class="button" href="'.$p_url.'&amp;part=modules&amp;type=theme">'.__('Themes').'</a>'.
' - <a class="button" href="'.$p_url.'&amp;part=pack">'.__('Import/Export').'</a>'.
'</h2><hr class="clear" />';

# Common page footer
$footer = '<hr class="clear"/><p class="right">';
if ($core->auth->check('admin',$core->blog->id))
{
	$footer .= '<a class="button" href="'.$p_url.'&amp;part=setting">'.__('Settings').'</a> - ';
}
$footer .= '
translater - '.$core->plugins->moduleInfo('translater','version').'&nbsp;
<img alt="'.__('Translater').'" src="index.php?pf=translater/icon.png" />
</p>';

# Combos of translation tools
$combo_proposal_tool = array('-' => '');
foreach($O->proposal->getTools() AS $id => $tool)
{
	$combo_proposal_tool[$tool['name']] = $id;
}

# Combos of backup folders
$combo_backup_folder = array(
	'module' => __('locales folders of each module'),
	'plugin' => __('plugins folder root'),
	'public' => __('public folder root'),
	'cache' => __('cache folder of Dotclear'),
	'translater' =>__('locales folder of translater')
);

# succes_codes
$succes = array(
	'save_setting' => __('Configuration successfully updated'),
	'update_lang' => __('Translation successfully updated'),
	'add_lang' => __('Translation successfully created'),
	'delete_lang' => __('Translation successfully deleted'),
	'create_backup' => __('Backups successfully create'),
	'restore_backup' => __('Backups successfully restored'),
	'delete_backup' => __('Backups successfully deleted'),
	'import_pack' => __('Package successfully imported'),
	'export_pack' => __('Package successfully exported')
);

# errors_codes
$errors = array(
	'save_setting' => __('Failed to update settings: %s'),
	'update_lang' => __('Failed to update translation: %s'),
	'add_lang' => __('Failed to create translation: %s'),
	'delete_lang' => __('Failed to delete translation: %s'),
	'create_backup' => __('Failed to create backups: %s'),
	'restore_backup' => __('Failed to restore backups: %s'),
	'delete_backup' => __('Failed to delete backups: %s'),
	'import_pack' => __('Failed to import package: %s'),
	'export_pack' => __('Failed to export package: %s')
);

# Messages
if (isset($succes[$msg]))
{
	$msg = sprintf('<p class="message">%s</p>',$succes[$msg]);
}

# Pages
if (!file_exists(dirname(__FILE__).'/inc/index.'.$default_part.'.php'))
{
	$default_part = 'setting';
}
define('DC_CONTEXT_TRANSLATER',$default_part);
include dirname(__FILE__).'/inc/index.'.$default_part.'.php';
?>