<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Dotwit, a plugin for Dotclear.
# 
# Copyright (c) 2007 Valentin VAN MEEUWEN
# <adresse email>
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('initWidgets',array('doTwitBehaviors','initWidgets'));

class doTwitBehaviors
{
	public static function initWidgets(&$widgets)
    {
		$widgets->create('dotwit',__('DoTwit'),array('DoTwit','dotwitWidget'));
		$widgets->dotwit->setting('title',__('Titre (facultatif):'),'');
		$widgets->dotwit->setting('idTwitter',__('Identifiant Twitter :'),'');
		$widgets->dotwit->setting('pwdTwitter',__('Mot de passe Twitter :'),'');
		$widgets->dotwit->setting('limit',__('Nombre de twit à afficher :'),1);
		$widgets->dotwit->setting('homeonly',__('Home page only'),1,'check');
		$widgets->dotwit->setting('timeline_friends',__('Timeline amis (Timeline perso par défaut)'),1,'check');
		$widgets->dotwit->setting('display_timeout',__('Affichage du temps écoulé'),1,'check');
		$widgets->dotwit->setting('display_profil_image',__('Affichage des profils image'),1,'check');
	}
}

?>