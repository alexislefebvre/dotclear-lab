<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Arlequin, a plugin for Dotclear.
# 
# Copyright (c) 2007,2008,2011 Alex Pirine <alex pirine.fr>
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$mt_models = array();

/** Syntaxe pour ajouter vos propres modèles prédéfinis :

$mt_models[] = array(
	'name'=>__('Model name'),	// Nom du modèle prédéfini, éventuellement
							// traduit dans un fichier de langue
	's_html'=>'[HTML code]',		// Code HTML du sélecteur de thème
	'e_html'=>'[HTML code]',		// Code HTML d'un item pouvant être sélectionné
	'a_html'=>'[HTML code]'		// Code HTML d'un item actif (thème sélectionné)
);

//*/

$mt_models[] = array(
	'name'=>__('Bullets list'),
	's_html'=>'<ul>%2$s</ul>',
	'e_html'=>'<li><a href="%1$s%2$s%3$s">%4$s</a></li>',
	'a_html'=>'<li><strong>%4$s</strong></li>'
);

$mt_models[] = array(
	'name'=>__('Scrolled list'),
	's_html'=>
		'<form action="%1$s" method="post">'."\n".
		'<p><select name="theme">'."\n".
		'%2$s'."\n".
		'</select>'."\n".
		'<input type="submit" value="'.__('ok').'"/></p>'."\n".
		'</form>',
	'e_html'=>'<option value="%3$s">%4$s</option>',
	'a_html'=>'<option value="%3$s" selected="selected" disabled="disabled">%4$s ('.__('active theme').')</option>'
);
?>