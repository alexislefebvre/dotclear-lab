<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kezako, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Jean-Claude Dubacq, Franck Paul and contributors
# carnet.franck.paul@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {return;}

$this->registerModule(
                      /* Name */         'Kezako',
                      /* Description*/   'Allows the inclusion of a description in many languages of tags, categories, etc.',
                      /* Author */       'Jean-Christophe Dubacq, Franck Paul',
                      /* Version */      '0.9',
                      /* Permissions */  'editor'
                      );
?>