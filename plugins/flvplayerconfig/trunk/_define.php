<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of flvplayerconfig, a plugin for Dotclear 2.
# 
# Copyright (c) 2011 lipki and contributors
# kevin@lepeltier.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

 if (!defined('DC_RC_PATH')) { return; }
/**
 * @author kévin lepeltier [lipki] (kevin@lepeltier.info)
 * @license http://creativecommons.org/licenses/by-sa/3.0/deed.fr
 */
 
$this->registerModule(
        /* Name */                    "flvplayerconfig",
        /* Description*/              "Configures flvplayer. A activer dans Paramètres du blog.",
        /* Author */                  "kévin Lepeltier [lipki]",
        /* Version */                 '1.7',
        /* Permissions */             'admin,usage,contentadmin'
);
