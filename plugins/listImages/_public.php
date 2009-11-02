<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of listImages plugin for Dotclear 2.
#
# Copyright (c) 2008 Kozlika, Franck Paul and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

/**
	Cette fonction permet d'extraire les images d'un billet
	
	Balise : {{tpl:EntryImages}}

	Attributs (optionnels) :
		size :	sq, t (défaut), s, m, o (voir tailles de miniature du gestionnaire de médias)
		html_tag : span (défaut), li, div
		link : entry, image (défaut), none
		from : excerpt, content, full (défaut)
		legend : none (défaut), image, entry
		start : 1 (défaut) à n
		length : 0 (défaut) à n, 0 = toutes

	Non développés (pour l'instant, peut-être, chépô, etc) :
		exif : 0 (défaut), 1
*/

// Déclaration de la balise {{tpl:EntryImages}}
$GLOBALS['core']->tpl->addValue('EntryImages',array('tplEntryImages','EntryImages'));

class tplEntryImages
{
	// Code de traitement de la balise
	// -------------------------------

	/*
		Balise d'extraction des images des billets sélectionnés par la balise <tpl:Entries> dans laquelle elle est placée
		Exemple :
			{{tpl:EntryImages}} -> extraira toutes les images et les retourne sous la forme d'une série de span contenant l'image au format thumbnail liée vers l'image au format original
		Attributs (optionnels) :
			size :	sq, t (défaut), s, m, o (voir tailles de miniature du gestionnaire de médias)
			html_tag : span (défaut), li, div
			link : entry, image (défaut), none
			from : excerpt, content, full (défaut)
			legend : none (défaut), image, entry
			start : 1 (défaut) à n
			length : 0 (défaut) à n, 0 = toutes
	*/
	public static function EntryImages($attr)
	{
		// Récupération des attributs
		$size = isset($attr['size']) ? trim($attr['size']) : '';
		$html_tag = isset($attr['html_tag']) ? trim($attr['html_tag']) : '';
		$link = isset($attr['link']) ? trim($attr['link']) : '';
		$exif = isset($attr['exif']) ? 1 : 0;
		$legend = isset($attr['legend']) ? trim($attr['legend']) : '';
		$from = isset($attr['from']) ? trim($attr['from']) : '';
		$start = isset($attr['start']) ? (int)$attr['start'] : 1;
		$length = isset($attr['length']) ? (int)$attr['length'] : 0;

		return "<?php echo tplEntryImages::EntryImagesHelper(".
			"'".addslashes($size)."', ".
			"'".addslashes($html_tag)."', ".
			"'".addslashes($link)."', ".
			$exif.", ".
			"'".addslashes($legend)."', ".
			"'".addslashes($from)."', ".
			$start.", ".
			$length."".
			"); ?>";
	}

	// Code utilisé par la balise compilée
	// -----------------------------------

	// Fonction de génération de la liste des images ciblées par la balise template
	public static function EntryImagesHelper($size, $html_tag, $link, $exif, $legend, $from, $start, $length)
	{
		global $core, $_ctx;
		
		// Contrôle des valeurs fournies et définition de la valeur par défaut pour les attributs
		if (!preg_match('/^sq|t|s|m|o$/',$size)) {
			$size = 't';
		}
		if (!preg_match('/^span|li|div$/',$html_tag)) {
			$html_tag = 'span';
		}
		if (!preg_match('/^entry|image|none$/',$link)) {
			$link = 'image';
		}
		$exif = (bool)$exif;
		if (!preg_match('/^entry|image|none$/',$legend)) {
			$legend = 'none';
		}
		if (!preg_match('/^excerpt|content|full$/',$from)) {
			$from = 'full';
		}
		$start = ((int)$start > 0 ? (int)$start - 1 : 0);
		$length = ((int)$length > 0 ? (int)$length : 0);

		// Récupération de l'URL du dossier public
		$p_url = $core->blog->settings->public_url;
		// Récupération du chemin du dossier public
		$p_root = $core->blog->public_path;

		// Contruction du pattern de recherche de la source des images dans les balises img
		// -> à noter que seules les images locales sont traitées
		$p_site = preg_replace('#^(.+?//.+?)/(.*)$#','$1',$core->blog->url);
		$pattern_path = '(?:'.preg_quote($p_site,'/').')?'.preg_quote($p_url,'/');
		$pattern_src = sprintf('/src="%s(.*?\.(?:jpg|gif|png))"/msu',$pattern_path);

		// Buffer de retour
		$res = '';
		
		if ($_ctx->posts) {
			// Recherche dans le contenu du billet
			$subject = ($from != 'content' ? $_ctx->posts->post_excerpt_xhtml : '').($from != 'excerpt' ? $_ctx->posts->post_content_xhtml : '');

			if (preg_match_all('/<img(.*?)\/\>/msu',$subject,$m) > 0) {

				// Récupération du nombre d'images trouvées
				$img_count = count($m[0]);

				// Contrôle des possibilités par rapport aux début demandé
				if (($img_count - $start) > 0) {

					// Au moins une image est disponible, calcul du nombre d'image à lister
					if ($length == 0) $length = $img_count;
					$length = min($img_count, $start + $length);

					for ($idx = $start; $idx < $length; $idx++) {

						// Récupération de la source de l'image dans le contenu (attribut src de la balise img)
						$i = (!preg_match($pattern_src,$m[1][$idx],$src) ? '' : $src[1]);
						if ($i != '') {

							// Recherche de l'image au format demandé
							$sens = '';
							if (($src_img = self::ContentImageLookup($p_root,$i,$size,$sens)) !== false) {

								// L'image existe, on construit son URL
								$src_img = $p_url.(dirname($i) != '/' ? dirname($i) : '').'/'.$src_img;

								// Recherche alt et title
								$img_alt = (!preg_match('/alt="(.*?)"/msu',$m[1][$idx],$alt) ? '' : $alt[1]);
								$img_title = (!preg_match('/title="(.*?)"/msu',$m[1][$idx],$title) ? '' : $title[1]);

								if ($legend != 'none') {
									// Une légende est requise
									if ($legend == 'image') {
										// On utilise les attributs de la balise image
										if ($img_title != '' or $img_alt != '') {
											// On utilise l'attribut title s'il existe sinon l'attribut alt s'il existe
											$img_legend = ($img_title != '' ? $img_title : $img_alt);
										} else {
											// Aucune légende n'est possible pour l'image
											$img_legend = '';
										}
									} else {
										// On utilise le titre du billet
										$img_legend = $_ctx->posts->post_title;
										// La légende est liée au billet
										$img_legend = '<a href="'.$_ctx->posts->getURL().'" title="'.sprintf(__('Go to entry %s'),$img_legend).'">'.$img_legend.'</a>';
									}
								}

								// Ouverture div englobante si en div et légende requise (et existante)
								if ($legend != 'none' && $html_tag == 'div') {
									$res .= '<div class="outer_'.$sens.'">';
									$res .= "\n";
								}

								// Ouverture balise
								$res .= '<'.$html_tag.' class="'.$sens.'">';

								if ($link != 'none') {
									// Si un lien est requis
									if ($link == 'image') {
										// Lien vers l'image originale
										$href = self::ContentImageLookup($p_root,$i,"o",$sens);
										$href = $p_url.(dirname($i) != '/' ? dirname($i) : '').'/'.$href;
										$href_title = $img_alt;
									} else {
										// Lien vers le billet d'origine
										$href = $_ctx->posts->getURL();
										$href_title = $_ctx->posts->post_title;
									}
									$res .= '<a href="'.$href.'" title="'.$href_title.'">';
								}

								$res .= '<img src="'.$src_img.'" alt="'.$img_alt.'" '.($img_title == '' ? '' : 'title="'.$img_title.'" ').'/>';

								if ($link != 'none') {
									// Fermeture du lien requis
									$res .= '</a>';
								}

								if ($legend != 'none' && $html_tag == 'div') {
									// Fermeture balise
									$res .= '</'.$html_tag.'>';
									$res .= "\n";
								}

								if ($legend != 'none') {
									// Une légende est requise
									if ($img_legend != '') {
										if ($html_tag == 'div') {
											$res .= '<p class="legend">'.$img_legend.'</p>';
										} else {
											$res .= '<br /><span class="legend">'.$img_legend.'</span>';
										}
									}
								}

								// Fermeture div englobante si en div et légende requise (et existante)
								if ($legend != 'none' && $html_tag == 'div') {
									$res .= '</div>';
									$res .= "\n";
								} else {
									// Fermeture balise
									$res .= '</'.$html_tag.'>';
									$res .= "\n";
								}
							} else {
								// L'image au format demandé n'a pas été trouvée, on cherchera une image de plus pour tenter de satisafaire la demande
								if ($length < $img_count) $length++;
							}

						} else {
							// L'image ne comporte pas de source locale, on cherchera une image de plus pour tenter de satisfaire la demande
							if ($length < $img_count) $length++;
						}
					}
				}
			}
		}
				
		if ($res) {
			return $res;
		}
	}

	// Fonction utilitaire de recherche d'une image selon un format spécifié (indique aussi l'orientation)
	private static function ContentImageLookup($root, $img, $size, &$sens)
	{
		// Récupération du nom et de l'extension de l'image source
		$info = path::info($img);
		$base = $info['base'];
		
		// Suppression du suffixe rajouté pour la création des miniatures s'il existe dans le nom de l'image
		if (preg_match('/^\.(.+)_(sq|t|s|m)$/',$base,$m)) {
			$base = $m[1];
		}
		
		$res = false;
		if ($size != 'o' && file_exists($root.'/'.$info['dirname'].'/.'.$base.'_'.$size.'.jpg')) {
			// Une miniature au format demandé a été trouvée
			$res = '.'.$base.'_'.$size.'.jpg';
		}
		else {
			// Recherche l'image originale
			$f = $root.'/'.$info['dirname'].'/'.$base;
			if (file_exists($f.'.'.$info['extension'])) {
				$res = $base.'.'.$info['extension'];
			} elseif (file_exists($f.'.jpg')) {
				$res = $base.'.jpg';
			} elseif (file_exists($f.'.png')) {
				$res = $base.'.png';
			} elseif (file_exists($f.'.gif')) {
				$res = $base.'.gif';
			}
		}
		// Récupération des dimensions de l'image originale
		$media_info = getimagesize($root.'/'.$info['dirname'].'/'.$base.'.'.$info['extension']);
		// Détermination de l'orientation de l'image
		$sens = ($media_info[0] > $media_info[1] ? "landscape" : "portrait");
		
		if ($res) {
			return $res;
		}
		return false;
	}
}
?>