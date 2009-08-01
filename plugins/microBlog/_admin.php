<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Micro-Blogging, a plugin for Dotclear.
# 
# Copyright (c) 2009 Jeremie Patonnier
# jeremie.patonnier@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

// Initialisation du Widget dans l'admin
require dirname(__FILE__).'/_widgets.php';

// ajouter le plugin dans le menu des Blogs de l'administration
$_menu['Blog']->addItem(
	// nom du lien (en anglais)
	__('MicroBlog'),
	// URL de base de la page d'administration
	'plugin.php?p=microblog',
	// URL de l'image utilisée comme icône
	'index.php?pf=microblog/icon.png',
	// expression régulière de l'URL de la page d'administration
	preg_match('/plugin.php\?p=microblog(&.*)?$/',
		$_SERVER['REQUEST_URI']),
	// persmissions nécessaires pour afficher le lien
	$core->auth->check('usage,contentadmin',$core->blog->id));