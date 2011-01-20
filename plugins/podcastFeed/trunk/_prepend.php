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
 * Sert à charger les fichiers nécessaires au bon fonctionnement du plugin.
 * Sera lu en façade ainsi que dans l'administration, avant l'affichage.
 */

$__autoload['PodcastFeedUrlHandler']	= dirname(__FILE__).'/inc/PodcastFeedUrlHandler.php';
$__autoload['PodcastFeedTplTags']		= dirname(__FILE__).'/inc/PodcastFeedTplTags.php';