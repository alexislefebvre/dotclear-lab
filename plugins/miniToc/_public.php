<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of miniToc, a plugin for Dotclear.
# 
# Copyright (c) 2009 Kozlika (ahem) and kindly contributors
# Actually, this plugin shows how I gracefully use copy-paste
# of Olivier Meunier's & Pep's code. Big thanks to them :-)
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('publicBeforeDocument',array('behaviorTocMode','addTplPath'));

$core->url->register('minitoc','minitoc','^minitoc(?:/?)$',array('urlMiniToc','minitoc'));
$core->tpl->addValue('MiniTocByCat', array('tplMiniToc','MiniTocByCat'));
$core->tpl->addValue('MiniTocCss', array('tplMiniToc','MiniTocCss'));

$core->tpl->use_cache = false; //ça serait mieux si ça marchait sans ce bout de code non ?

/* -----------------------------------------------------------
pour pouvoir aller piocher le template par defaut dans le ploug
(piqué chez Pep, rien pigé mais ça fonctionne ;))
------------------------------------------------------------ */
class behaviorTocMode
{
	public static function addTplPath(&$core)
	{
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
	}
}

/* -------------------------------------------------------
pour creer l'url http://url_vers_le_blog/minitoc
--------------------------------------------------------- */
class urlMiniToc extends dcUrlHandlers
{
	public static function minitoc($args)
	{
		dcUrlHandlers::serveDocument('minitoc.html','text/html',true);
		exit;
	}
}


/* -------------------------------------------------------
Fonctions template diverses 
--------------------------------------------------------- */
class tplMiniToc
{
 /*
 		Cette fonction va chercher la css minitoc.css
 		(inspire de la fonction dans wikicomments de pep) 
 */	
	public static function MiniTocCss()
	{
		global $core;

		$plop =
			$core->blog->themes_path.'/'.
			$core->blog->settings->theme.'/minitoc.css';
		
		$tagada = 
			$core->blog->themes_path.
			'/default/minitoc.css';
		
		if (file_exists($plop)) { /* s'il y a une minitoc.css dans le thème actif, on la prend */
			$css =
			$core->blog->settings->themes_url.'/'.
			$core->blog->settings->theme.'/minitoc.css';
		}
		elseif (file_exists($tagada)) { /* si pas dans le thème actif on regarde dans le theme par défaut */
			$css =
			$core->blog->settings->themes_url.'/default/minitoc.css';
		}
		else { /* et si aucune des deux celle dans le rep du ploug */
			$css =
				$core->blog->url.
				(($core->blog->settings->url_scan == 'path_info')?'?':'').
				'pf=miniToc/minitoc.css';
		}
		$res =
			"\n<?php \n".
				"echo '<style type=\"text/css\" media=\"screen\">@import url(".$css.");</style>';\n".
			"?>\n";
			
		return $res;
	}

 /* Cette fonction fait une boucle sur tous les billets (en <dl>) sur deux critères : 
		critere 1 la categorie (<dt>)
		critère 2 la date du billet (ici ascendant, 
		modifier asc en desc ligne 133 si on veut du plus récent au plus ancien) 
 */
	public static function MiniTocByCat()
	{
		global $core;

		$params_cats['order'] = 'cat_position asc';
		$rs_cat = $core->blog->getCategories($params_cats);

		if ($rs_cat->isEmpty()) {
			return;
		}
		
		$res = 
			'<dl id="minitoc">'.
			"\n \t";

		$tmp_cat = null;
		while ($rs_cat->fetch()) {

			if ($tmp_cat != $rs_cat->cat_title) {
				$res .= 
					"\n \t".
					'<dt><a href="#'.
					$rs_cat->cat_url.'">'.
					$rs_cat->cat_title.
					'</a></dt>'.
					"\n \t";
			}

				$tmp_cat = $rs_cat->cat_title;
				// affichage des posts de la catégorie tmp_cat
				$params_posts['cat_id'] = $rs_cat->cat_id;
				$params_posts['order'] = 'post_dt asc';
				$params_posts['no_content'] = true;
				$rs_post = $core->blog->getPosts($params_posts);
				
				$res .= '<dd>'."\n \t".
				'<div class="toc-cat-desc">'.
				$rs_cat->cat_desc.'</div>'.
				"\n \t".'<ul>'."\n \t";
				
				while ($rs_post->fetch()) {
					$res .= 
						'<li>'.
						'<span class="toc-post-date">'.dt::dt2str(__('%d-%m-%Y'),$rs_post->post_dt).'.&emsp;</span>'.
						'<a href="'.$rs_post->getURL().'">'.
						$rs_post->post_title.'</a></li>'.
						"\n \t";
				}
				$res .= '</ul>'."\n\t".'<p class="hide-cat"><a href="">tout replier</a></p>'.'</dd>'."\n \t";
		}
		$res .= '</dl>';
		return $res;
	}
}
?>