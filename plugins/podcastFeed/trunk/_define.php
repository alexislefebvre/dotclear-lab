<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of podcastFeed, a plugin for Dotclear.
# 
# Copyright (c) 2010 Arnaud Jacquemin <contact@arnaud-jacquemin.fr>
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

// Protection pour les fichiers lus côté public
if (!defined('DC_RC_PATH')) { return; }

/**
 * Fichier de définition (nom, auteur, numéro de version) du plugin.
 */
 
$this->registerModule(
	/* Name */			"Podcast Feed (iTunes support)",
	/* Description*/	"New feed dedicated to podcasting, with specific iTunes tags.",
	/* Author */		"Arnaud Jacquemin",
	/* Version */		'0.2.1-alpha',
	/* Permissions */	'admin' //restreindre ce plugin aux seuls administrateurs d'un blog donné
);