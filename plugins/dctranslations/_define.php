<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dctranslations, a plugin for Dotclear.
# 
# Copyright (c) 2009 Jean-Christophe Dubacq
# jcdubacq1@free.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
                      "Posts Translations",         // Name
                      "Allows to translate posts",  // Description
                      "Jean-Christophe Dubacq",     // Author
                      '1.8',                      // Version
                      'admin,editor,usage',         // Permissions
                      500                           // Priority
                      );
?>