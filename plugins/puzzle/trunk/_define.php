<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Puzzle plugin.
# Copyright (c) 2009 Kévin Lepeltier [lipki],  and contributors. 
# Many, many thanks to the Dotclear Team.
# All rights reserved.
#
# Puzzle plugin for DC2 is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			"Puzzle",
	/* Description*/		"Créer une mise en page magazine avec n'importe quel thème, pour la page d'accueil ou pour les catégories. ",
	/* Author */			"kévin Lepeltier",
	/* Version */			'0.2',
	/* Permissions */		'usage,contentadmin',
	/* Priority */			1001
);