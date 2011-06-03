<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of lePluginDuJour, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 lipki and contributors
# kevin@lepeltier.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			"lePLuginDuJour",
	/* Description*/		"Pour découvrir un nouveau plugin par jour. A activer dans Paramètres du blog. Ajouter le dans vos widgets, pour en faire profiter vos visiteurs.",
	/* Author */			"kévin Lepeltier [lipki]",
	/* Version */			"0.5",
	/* Permissions */		null
);
