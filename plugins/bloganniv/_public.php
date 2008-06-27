<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
#
if (!defined('DC_RC_PATH')) { return; }

$core->tpl->addValue('blogAnniv',array('tplBlogAnniv','blogAnniv'));

class tplBlogAnniv
{

	public static function blogAnniv($attr)
	{
		$output = '';
		
		if (isset($attr['text']))
		{
			$author = isset($attr['author']) ? ' <cite>'.$attr['author'].'</cite>' : '';
			
			$output = '<blockquote>'.$attr['text'].$author.'</blockquote>';
		}
		
		return $output;
	}
	
	public static function BlogAnnivWidget(&$w)
	{
		global $core;
		
		// Si nous sommes pas en page accueil et que c'est coché page accueil uniquement on fait rien
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		$title = $w->title ? html::escapeHTML($w->title) : __('Subscribe');
		$ftdatecrea = $w->ftdatecrea;
		// Test si la date est valide 
		
		$date = $ftdatecrea;
		$nbrejours=0;
		$nbreannee=0;
		list($jour, $mois, $annee) = explode('/', $date);
		if(checkdate($mois,$jour,$annee)){
			// Ok nous pouvons calculer la date anniversaire et le nombre de jours restant avant
			
			//Extraction des données
			list($jour2, $mois2, $annee2) = explode('-', date('d-m-Y'));

			//Calcul des timestamp
			$timestamp1 = mktime(0,0,0,$mois,$jour,$annee2); // La date anniversaire cette année
			$timestamp2 = mktime(0,0,0,$mois2,$jour2,$annee2); 
			if (($timestamp2 - $timestamp1)/86400 > 0){  // Si c'est négatif il faut recalculer l'anniversaire avec l'année prochaine
				$timestamp2 = mktime(0,0,0,$mois2,$jour2,$annee2+1);
				$nbreannee = abs($annee2 - $annee);
			} else {
				// Il a pas encore la dernière annee complete
				$nbreannee = abs($annee2 - $annee - 1);
			}
			$nbrejours = abs($timestamp2 - $timestamp1)/86400; //Affichage du nombre de jour
			
			// abs($timestamp2 - $timestamp1)/(86400*7); //Affichage du nombre de semaine : 3.85
		} else {
			$ftdatecrea= '$ftdatecrea date invalide';
		}
		$dispyearborn = $dispyear = "";
		// Si je dois afficher la date de naissance
		if ($w->dispyearborn) {
			$dispyearborn = 'Né le : <span class="annivne">'.$ftdatecrea.'</span><br />';
		}
		// Si je dois afficher le l'age en année
		if ($w->dispyear) {
			$dispyear = 'Age : <span class="annivan">'.$nbreannee.'</span> an(s)<br />';
		}
		return
		'<div class="bloganniv">'.
		'<h2>'.$title.'</h2>'.
		$dispyearborn.
		$dispyear.
		'Anniversaire dans <span class="annivjrs">'.$nbrejours.'</span> jours'.
		'</div>';
	}
}
?>
