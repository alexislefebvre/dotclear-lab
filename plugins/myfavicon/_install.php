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

$label = 'myfavicon';
$m_version = $core->plugins->moduleInfo($label,'version');
$i_version = $core->getVersion($label);

if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# --INSTALL AND UPDATE PROCEDURES--

$core->blog->settings->addNamespace('myfavicon');
$s = &$core->blog->settings->myfavicon;

# New install / update
$s->put('url','','string','Favicon URL',false);
$s->put('iOS_url','','string','iOS icon URL');
$s->put('ie6',false,'boolean','Internet Explorer 6 compatibility',false);

if (version_compare(DC_VERSION,'2.0-rc1','<')
&& file_exists(DC_TPL_CACHE.'/cbtpl')
&& !files::deltree(DC_TPL_CACHE.'/cbtpl')) {
	throw new Exception(__('To finish installation, please delete the whole cache/cbtpl directory.'));
}

# --SETTING NEW VERSION--

$core->setVersion($label,$m_version);
return true;
?>