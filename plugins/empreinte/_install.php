<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Empreinte, a plugin for Dotclear.
# 
# Copyright (c) 2007,2008,2011 Alex Pirine <alex pirine.fr>
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$label = 'empreinte';
$m_version = $core->plugins->moduleInfo($label,'version');
$i_version = $core->getVersion($label);

if (version_compare($i_version,$m_version,'>=')) {
	return;
}


# --SCHEMA SYNC--

$s = new dbStruct($core->con,$core->prefix);

# We do NOT erase database contents if a previous version is installed
if ($i_version === null) {
	$s->comment
		->comment_browser('varchar',	65,true,null)
		->comment_system('varchar',	65,true,null)
		;
}

$si = new dbStruct($core->con,$core->prefix);
$si->synchronize($s);


# --SETTINGS--

$core->blog->settings->addNamespace(strtolower($label));
$s = &$core->blog->settings->empreinte;

$s->put('authorlink_mask','%1$s',
	'string','AuthorLink mask',false);
$s->put('allow_disable',true,
	'boolean','Allow visitors disable Empreinte',false);
$s->put('checkbox_style','margin:0pt 5px 0pt 140px;width:auto;',
	'string','Set a style attribute to the checkbox that disables Empreinte',false);


# --SETTING NEW VERSION--

$core->setVersion($label,$m_version);

if (!file_exists(DC_TPL_CACHE.DIRECTORY_SEPARATOR.'cbtpl')) {
	return true;
}
if (!files::deltree(DC_TPL_CACHE.DIRECTORY_SEPARATOR.'cbtpl')) {
	throw new Exception(__('To finish installation, please delete the whole cache/cbtpl directory.'));
}

return true;
?>