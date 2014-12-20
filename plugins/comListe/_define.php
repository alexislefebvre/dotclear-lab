<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of comListe, a plugin for Dotclear.
# 
# Copyright (c) 2008-2010 Benoit de Marne
# benoit.de.marne@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
        /* Name */                      "ComListe",
        /* Description*/                "Plugin for printing comments list",
        /* Author */                    "Benoit de Marne, Pierre Van Glabeke",
        /* Version */                   '0.2',
	/* Properties */
	array(
		'permissions' => 'admin',
		'type' => 'plugin',
		'dc_min' => '2.7',
		'support' => 'http://lab.dotclear.org/wiki/plugin/comListe/fr',
		'details' => 'http://plugins.dotaddict.org/dc2/details/comListe'
		)
);