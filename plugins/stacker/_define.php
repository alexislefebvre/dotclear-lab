<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of stacker, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Franck Paul and contributors
# carnet.franck.paul@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {return;}

$this->registerModule(
                      /* Name */        'Stacker',
                      /* Description*/  'Manages a bunch of transforming functions that can modify the entries text before display',
                      /* Author */      'Jean-Christophe Dubacq, Franck Paul',
                      /* Version */     '0.5',
                      /* Permissions */ 'usage,contentadmin',
                      /* Priority */    100001
                      );
?>