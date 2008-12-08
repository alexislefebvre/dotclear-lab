<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcScrobbler, a plugin for Dotclear.
# 
# Copyright (c) 2008 Boris de Laage
# bdelaage@free.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

# $Id: _define.php 24 2006-08-23 11:53:04Z bdelaage $
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
    /* Name */					"dcScrobbler",
    /* Description*/		"Displays recently played tracks with Last.fm",
    /* Author */				"Boris de Laage, Oum",
    /* Version */				'2.0',
    /* Permissions */		'usage,contentadmin'
);
?>
