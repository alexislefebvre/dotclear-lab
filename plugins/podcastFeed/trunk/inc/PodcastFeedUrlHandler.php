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
 * Classe de contrôleur des URL spécifiques au module podcastFeed
 */
class PodcastFeedUrlHandler extends dcUrlHandlers {

	/** Servir le flux du podcast */
	public static function servePodcastFeed($args) {
		global $core;
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/../default-templates/');
		self::serveDocument('podcast.xml', 'text/xml');
	}

}