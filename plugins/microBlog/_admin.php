<?php
if (!defined('DC_CONTEXT_ADMIN')) { return; }

// Initialisation du Widget dans l'admin
require dirname(__FILE__).'/_widgets.php';

// ajouter le plugin dans le menu des Blogs du menu de l'administration
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