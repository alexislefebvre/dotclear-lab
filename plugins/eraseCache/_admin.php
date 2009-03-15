<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2007 gerits aurelien for clashdesign All rights
# reserved.
#
# Cette création est mise à disposition selon le Contrat Paternité-Pas 
# d'Utilisation Commerciale-Pas de Modification 2.0 Belgique disponible 
# en ligne http://creativecommons.org/licenses/by-nc-nd/2.0/be/ ou par 
# courrier postal à Creative Commons, 171 Second Street, Suite 300, San Francisco, 
# California 94105, USA.
# ***** END LICENSE BLOCK *****

# Ajout du menu plugin
$_menu['Plugins']->addItem('eraseCache','plugin.php?p=eraseCache','index.php?pf=eraseCache/icon.png',
		preg_match('/plugin.php\?p=eraseCache(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('usage,contentadmin',$core->blog->id));
$core->auth->setPermissionType('eraseCache',__('use eraseCache'));
?>