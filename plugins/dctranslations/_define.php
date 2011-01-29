<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dctranslations, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Jean-Claude Dubacq, Franck Paul and contributors
# carnet.franck.paul@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
                      "Posts Translations",         // Name
                      "Allows to translate posts",  // Description
                      "Jean-Christophe Dubacq, Franck Paul",     // Author
                      '1.12',                        // Version
                      'admin,editor,usage',         // Permissions
                      500                           // Priority
                      );
?>