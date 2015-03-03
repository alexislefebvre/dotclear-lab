<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Arlequin', a plugin for Dotclear 2                *
 *                                                             *
 *  Copyright (c) 2007,2015                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Arlequin' (see COPYING.txt);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

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