<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of odt, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) {return;}

# ajouter le plugin dans la liste des plugins du menu de l'administration
$_menu['Plugins']->addItem(
	# nom du lien (en anglais)
	__('ODT export'),
	# URL de base de la page d'administration
	'plugin.php?p=odt',
	# URL de l'image utilisée comme icône
	'index.php?pf=odt/icon.png',
	# expression régulière de l'URL de la page d'administration
	preg_match('/plugin.php\?p=odt(&.*)?$/',
		$_SERVER['REQUEST_URI']),
	# persmissions nécessaires pour afficher le lien
	$core->auth->check('admin',$core->blog->id));
?>