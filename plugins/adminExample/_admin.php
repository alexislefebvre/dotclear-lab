<?php
# ***** BEGIN LICENSE BLOCK *****
# 
# This program is free software. It comes without any warranty, to
# the extent permitted by applicable law. You can redistribute it
# and/or modify it under the terms of the Do What The Fuck You Want
# To Public License, Version 2, as published by Sam Hocevar. See
# http://sam.zoy.org/wtfpl/COPYING for more details.
# 
# Icon (icon.png) is from Silk Icons :
#  http://www.famfamfam.com/lab/icons/silk/
# 
# ***** END LICENSE BLOCK *****

# add the plugin in the plugins list on the backend
# ajouter le plugin dans la liste des plugins du menu de l'administration
$_menu['Plugins']->addItem(
	# link's name
	# nom du lien (en anglais)
	__('Admin example'),
	# base URL of the administration page
	# URL de base de la page d'administration
	'plugin.php?p=adminExample',
	# URL of the image used as icon
	# URL de l'image utilisée comme icône
	'index.php?pf=adminExample/icon.png',
	# regular expression of the URL of the administration page
	# expression régulière de l'URL de la page d'administration
	preg_match('/plugin.php\?p=adminExample(&.*)?$/',
		$_SERVER['REQUEST_URI']),
	# required permissions to show the link
	# permissions nécessaires pour afficher le lien
	$core->auth->check('usage,contentadmin',$core->blog->id));
?>