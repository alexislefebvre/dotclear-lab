<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# Copyright (c) 2010-2015 Arnaud Renevier
# published under the modified BSD license.
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

    $plugin_name = 'prvcat';
    $m_version = $core->plugins->moduleInfo($plugin_name,'version');
    $i_version = $core->getVersion($plugin_name);
    if (version_compare($i_version,$m_version,'>=')) {
        return;
    }
    $core->setVersion($plugin_name,$m_version);

    $perms = new prvCatPermMgr($core->con, $core->prefix);
    $perms->install();
