<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of DotClear.
#
# Plugin Bloganniv by Francis Trautmann
# Contributor: Pierre Van Glabeke
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) { return; }

require dirname(__FILE__).'/_widgets.php';

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
	
	public static function BlogAnnivWidget($w)
	{
		global $core;
		
		// Si nous sommes pas en page accueil et que c'est coché page accueil uniquement on fait rien

		if (($w->homeonly == 1 && $core->url->type != 'default') ||
			($w->homeonly == 2 && $core->url->type == 'default')) {
			return;
		}
		
		$title = $w->title ? html::escapeHTML($w->title) : __('Subscribe');
		$ftdatecrea = $w->ftdatecrea;
		//Si la date est vide nous recherchons la date en base
		if (strlen(rtrim($ftdatecrea))==0){
			///////////////////////////////////////////////
			//ACCES BDD
			//je récupère la date du blog
			require_once dirname(__FILE__).'/class.dc.dateBlog.php';
		
			$lc = new dateBlog($GLOBALS['core']->blog);
			try {
				$Posts = $lc->getdateBlog();
			} 
			catch (Exception $e) {
				return false;
			}
			foreach($Posts->rows() as $k => $v)
			{
				$ftdatecrea = html::clean($v['blog_creadt']);
				$ftdatecrea = substr($ftdatecrea,0,10);
				$ftdatecrea = str_replace("-","/",$ftdatecrea);
				list($annee, $mois, $jour) = explode('/', $ftdatecrea);
				// On remet la date en forme française
				$ftdatecrea=$jour."/".$mois."/".$annee;
				#printf($ftdatecrea);
				#printf(html::clean($v['blog_id']));
			}
			//FIN ACCES BDD
			///////////////////////////////////////////////
		} else {
			list($jour, $mois, $annee) = explode('/', $ftdatecrea);
		}
		$nbrejours=0;
		$nbreannee=0;
		// Test si la date est valide
		if(@checkdate($mois,$jour,$annee)){
			// Ok nous pouvons calculer la date anniversaire et le nombre de jours restant avant
			
			//Extraction des données
			list($jour2, $mois2, $annee2) = explode('-', date('d-m-Y'));

			//Calcul des timestamp
			$timestamp1 = mktime(0,0,0,$mois,$jour,$annee2); // La date anniversaire cette année
			$timestamp2 = mktime(0,0,0,$mois2,$jour2,$annee2); 
			//Affichage du nombre de jour

      //je regarde si la date anniv n'est pas passé
      if (($timestamp2 - $timestamp1)> 0)
      {
      $timestamp1 = mktime(0,0,0,$mois,$jour,$annee2 + 1);
      $nbrejours = round(abs(mktime(0,0,0,$mois2,$jour2,$annee2) - $timestamp1)/86400);
      $nbreannee = abs($annee2 - $annee);
      }
      else {
      $nbrejours = abs($timestamp2 - $timestamp1)/86400;
      $nbreannee = abs($annee2 - $annee - 1);
      }
			// abs($timestamp2 - $timestamp1)/(86400*7); //Affichage du nombre de semaine : 3.85
		} else {
			$ftdatecrea= '$ftdatecrea date invalide';
		}
		$dispyearborn = $dispyear = "";
		// Si je dois afficher la date de naissance
		if ($w->dispyearborn) {
			$dispyearborn = '<li>'.__('Born:').
      ' <span class="annivne">'.$ftdatecrea.'</span></li>';
		}
		// Si je dois afficher l'age en année
		if ($w->dispyear) {
			$dispyear = '<li>'.__('Age:').
      ' <span class="annivan">'.$nbreannee.'</span> '.
      __('year(s)').
      '</li>';
		}
		return
		$res = ($w->content_only ? '' : '<div class="bloganniv'.($w->class ? ' '.html::escapeHTML($w->class) : '').'">').
		'<h2>'.$title.'</h2>'.'<ul>'.
		$dispyearborn.
		$dispyear.
		'<li>'.__('Birthday in').
    ' <span class="annivjrs">'.$nbrejours.'</span> '.
    __('day(s)').'</li></ul>'.
    ($w->content_only ? '' : '</div>');
	}
}
