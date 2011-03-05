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
 * Déclaration des ajouts pour la partie publique.
 * Ne sera lu qu'en façade du blog.
 */
 
// Ajouter une URL /feed/podcast à notre blog.
// La fonction register de l'objet $core→url prend 4 paramètres...
$core->url->register(
	// 1) un type, sorte d'identifiant unique de l'URL :
	'podcastFeed',
	// 2) la forme de base de l'URL que l'on peut obtenir par $core->url->getBase(<url>) :
	'feed/podcast',
	// 3) une expression rationnelle permettant à l'interface publique de savoir quand réagir :
	'^feed/podcast$',
	// 4) la fonction à appeler quand l'URL est reconnue (classe, méthode) :
	array('PodcastFeedUrlHandler', 'servePodcastFeed')
);

$core->tpl->addBlock('PodcastEntries', 
	array('PodcastFeedTplTags', 'getPodcastEntries')
);
$core->tpl->addValue('ItunesKeywords', 
	array('PodcastFeedTplTags', 'getItunesKeywordsForPost')
);
$core->tpl->addValue('PodcastTitle', 
	array('PodcastFeedTplTags', 'getPodcastTitle')
);
$core->tpl->addValue('PodcastLink', 
	array('PodcastFeedTplTags', 'getPodcastLink')
);
$core->tpl->addValue('PodcastSubTitle', 
	array('PodcastFeedTplTags', 'getPodcastSubTitle')
);
$core->tpl->addValue('PodcastLanguage', 
	array('PodcastFeedTplTags', 'getPodcastLanguage')
);
$core->tpl->addValue('PodcastAuthor', 
	array('PodcastFeedTplTags', 'getPodcastAuthor')
);
$core->tpl->addValue('PodcastDescription', 
	array('PodcastFeedTplTags', 'getPodcastDescription')
);
$core->tpl->addValue('PodcastItunesSummary', 
	array('PodcastFeedTplTags', 'getPodcastItunesSummary')
);
$core->tpl->addBlock('PodcastIfHasItunesSummary',
	array('PodcastFeedTplTags','ifPodcastHasItunesSummary')
);
$core->tpl->addBlock('PodcastIfHasOwner',
	array('PodcastFeedTplTags','ifPodcastHasOwner')
);
$core->tpl->addValue('PodcastOwnerName', 
	array('PodcastFeedTplTags', 'getPodcastOwnerName')
);
$core->tpl->addValue('PodcastOwnerEmail', 
	array('PodcastFeedTplTags', 'getPodcastOwnerEmail')
);
$core->tpl->addValue('PodcastImage', 
	array('PodcastFeedTplTags', 'getPodcastImage')
);
$core->tpl->addBlock('PodcastIfHasItunesImage',
	array('PodcastFeedTplTags','ifPodcastHasItunesImage')
);
$core->tpl->addValue('PodcastItunesImage', 
	array('PodcastFeedTplTags', 'getPodcastItunesImage')
);
$core->tpl->addValue('PodcastItunesCategory', 
	array('PodcastFeedTplTags', 'getPodcastItunesCategories')
);
$core->tpl->addValue('PodcastItunesExplicit', 
	array('PodcastFeedTplTags', 'getPodcastItunesExplicit')
);