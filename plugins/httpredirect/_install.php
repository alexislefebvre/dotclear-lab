<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of HTTP Redirect, a plugin for Dotclear.
# 
# Copyright (c) 2007,2008,2011 Alex Pirine <alex pirine.fr>
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$label = 'httpredirect';
$m_version = $core->plugins->moduleInfo($label,'version');
$i_version = $core->getVersion($label);

if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# --INSTALL AND UPDATE PROCEDURES--

$s = new dbStruct($core->con,$core->prefix);

# Install
if ($i_version === null) {
	$s->post->redirect_url('varchar',255,true);
}

# --SCHEMA SYNC--

$si = new dbStruct($core->con,$core->prefix);
$si->synchronize($s); 

# --SETTING NEW VERSION--

$core->setVersion($label,$m_version);

unset($label,$i_version,$m_version);
return true;
?>