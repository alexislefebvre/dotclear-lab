<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of DotClear.
#
# Plugin Bloganniv by Francis Trautmann
# Contributor: Pierre Van Glabeke
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_RC_PATH')) { return; }
$this->registerModule(
	/* Name */				"Blog Anniv",
	/* Description*/		"Décompte du nombre de jours avant et après la date anniversaire du blog",
	/* Author */			"Fran6t, Pierre Van Glabeke",
	/* Version */			'1.4.1',
	/* Permissions */		'blogAnniv'
);
?>