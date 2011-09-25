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

// Protection pour les fichiers lus seulement côté administration
if (!defined('DC_CONTEXT_ADMIN')) {return;}

/**
 * Appelé dès qu'un utilisateur se rend sur le tableau de bord
 * via l'interface d'administration.
 */
 
// On lit la version du plugin
$m_version = $core->plugins->moduleInfo('podcastFeed','version');
// On lit la version du plugin dans la table des versions
$i_version = $core->getVersion('podcastFeed');
 
// La version dans la table est supérieure ou égale à celle du module,
// on ne fait rien puisque celui-ci est installé
if (version_compare($i_version, $m_version, '>=')) {
	return;
}
 
// La procédure d'installation commence vraiment là
// Création du setting (s'il existe, il ne sera pas écrasé)
$settings =& $core->blog->settings;

// Le paramètre de setNamespace() ne doit contenir que des chiffres ou des lettres sans accent.
$settings->addNameSpace('podcastFeed');

$settings->podcastFeed->put('podcastCategoryFilter', '', 'string', 'Catégories à prendre en comptre', false);
$settings->podcastFeed->put('podcastTitle', $core->blog->name, 'string', 'Titre du podcast', false);
$settings->podcastFeed->put('podcastSubTitle', '', 'string', 'Sous-titre du podcast', false);
$settings->podcastFeed->put('podcastLink', $core->blog->url, 'string', 'URL du podcast', false);
$settings->podcastFeed->put('podcastLanguage', $core->blog->settings->lang, 'string', 'Langue du podcast', false);
$settings->podcastFeed->put('podcastAuthor', $core->blog->settings->editor, 'string', 'Auteur du podcast', false);
$settings->podcastFeed->put('podcastDescription', $core->blog->desc, 'string', 'Description du podcast', false);
$settings->podcastFeed->put('podcastImage', '', 'string', 'Logo du podcast', false);
$settings->podcastFeed->put('podcastCategories', '', 'string', 'Categories du podcast (iTunes)', false);
$settings->podcastFeed->put('podcastExplicit', 'no', 'string', 'Langage "tout public" ? (iTunes)', false);
$settings->podcastFeed->put('podcastItunesSummary', '', 'string', 'Description spécifique iTunes', false);
$settings->podcastFeed->put('podcastItunesImage', '', 'string', 'Logo spécifique iTunes', false);
$settings->podcastFeed->put('podcastOwnerName', '', 'string', 'Nom du propriétaire du podcast', false);
$settings->podcastFeed->put('podcastOwnerEmail', '', 'string', 'E-mail du propriétaire du podcast', false);
 
$core->setVersion('podcastFeed', $m_version);