<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of listImages plugin for Dotclear 2.
#
# Copyright (c) 2011 Kozlika, Franck Paul and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

/**
Cette fonction permet d'extraire les images d'un billet

*/

require dirname(__FILE__).'/_widget.php';

class widgetEntryImages
{
	// Code de traitement du widget
	// ----------------------------

	public static function EntryImages($w)
	{
		global $core;

		// Si l'affichage du widget est demandé sur la page d'accueil uniquement et qu'on y est pas, on ressort
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}

		// Mise en place des paramètres de recherche par défaut
		$params['no_content'] = false;
		
		// Récupération de la limite du nb de billets dans lesquels rechercher des images
		$params['limit'] = abs((integer) $w->limit);
		
		// Récupération de la ou des catégories spécifiées
		if ($w->category != '') {
			$category  = $w->category;
			$params['cat_url'] = explode(",", $category);
		}
		
		// Récupération de l'indicateur de billet sélectionné
		if ($w->selected == '1') {
			$params['post_selected'] = '1';
		}
		
		// Recherche des billets correspondants
		$rs = $core->blog->getPosts($params);

		// Récupération des options d'affichage des images
		$size = $w->size;
		$html_tag = $w->html_tag;
		$link = $w->link;
		$exif = 0;
		$legend = $w->legend;
		$bubble = $w->bubble;
		$from = $w->from;
		$start = abs((integer) $w->start);
		$length = abs((integer) $w->length);
		$class = $w->class;
		$alt = $w->alt;
		$img_dim = $w->img_dim;

		// Début d'affichage
		$ret = '<div class="listimages-widget">';
		$ret .= ($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '');
		$ret .= '<'.($html_tag == 'li' ? 'ul' : 'div').' class="listimages-wrapper">';
		
		// Appel de la fonction de traitement pour chacun des billets
		while ($rs->fetch()) {
			$ret .= tplEntryImages::EntryImagesHelper($size, $html_tag, $link, $exif, $legend, $bubble, $from, $start, $length, $class, $alt, $img_dim, $rs);
		}
		
		// Fin d'affichage
		$ret .= '</'.($html_tag == 'li' ? 'ul' : 'div').'>'."\n";
		$ret .= '</div>'."\n";

		return $ret;
	}
}

/**
	Balise : {{tpl:EntryImages}}

	Attributs (optionnels) :
		size :	sq, t (défaut), s, m, o (voir tailles de miniature du gestionnaire de médias)
		html_tag : span (défaut), li, div, none
		link : entry, image (défaut), none
		from : excerpt, content, full (défaut)
		legend : none (défaut), image, entry
		bubble : none, image (défaut), entry
		start : 1 (défaut) à n
		length : 0 (défaut) à n, 0 = toutes
		class : ajoutée à la balise <img />
		alt : none, inherit
		img_dim : ajoute les dimensions de l'image

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
			{{tpl:EntryImages}} -> extraira toutes les images du billet courant et les retourne sous la forme d'une série de span contenant l'image au format thumbnail liée vers l'image au format original
		Attributs (optionnels) :
			size :	sq, t (défaut), s, m, o (voir tailles de miniature du gestionnaire de médias)
			html_tag : span (défaut), li, div, none
			link : entry, image (défaut), none
			from : excerpt, content, full (défaut)
			legend : none (défaut), image, entry
			bubble : none, image (défaut), entry
			start : 1 (défaut) à n
			length : 0 (défaut) à n, 0 = toutes
			class : ajoutée à la balise <img />
			alt : none, inherit (defaut)
			img_dim : ajoute les dimensions de l'image
	*/
	public static function EntryImages($attr)
	{
		// Récupération des attributs
		$size = isset($attr['size']) ? trim($attr['size']) : '';
		$html_tag = isset($attr['html_tag']) ? trim($attr['html_tag']) : '';
		$link = isset($attr['link']) ? trim($attr['link']) : '';
		$exif = isset($attr['exif']) ? 1 : 0;
		$legend = isset($attr['legend']) ? trim($attr['legend']) : '';
		$bubble = isset($attr['bubble']) ? trim($attr['bubble']) : '';
		$from = isset($attr['from']) ? trim($attr['from']) : '';
		$start = isset($attr['start']) ? (int)$attr['start'] : 1;
		$length = isset($attr['length']) ? (int)$attr['length'] : 0;
		$class = isset($attr['class']) ? trim($attr['class']) : '';
		$alt = isset($attr['alt']) ? trim($attr['alt']) : 'inherit';
		$img_dim = isset($attr['img_dim']) ? trim($attr['img_dim']) : 'none';

		return "<?php echo tplEntryImages::EntryImagesHelper(".
			"'".addslashes($size)."', ".
			"'".addslashes($html_tag)."', ".
			"'".addslashes($link)."', ".
			$exif.", ".
			"'".addslashes($legend)."', ".
			"'".addslashes($bubble)."', ".
			"'".addslashes($from)."', ".
			$start.", ".
			$length.", ".
			"'".addslashes($class)."', ".
			"'".addslashes($alt)."', ".
			"'".addslashes($img_dim)."'".
			"); ?>";
	}

	// Code utilisé par la balise compilée
	// -----------------------------------

	// Fonction de génération de la liste des images ciblées par la balise template
	public static function EntryImagesHelper($size, $html_tag, $link, $exif, $legend, $bubble, $from, $start, $length,
		$class, $alt, $img_dim, $rs = null)
	{
		global $core, $_ctx;
		
		// Contrôle des valeurs fournies et définition de la valeur par défaut pour les attributs
		$media = new dcMedia($core);
		$sizes = implode('|',array_keys($media->thumb_sizes));
		if (!preg_match('/^'.$sizes.'|o'.'$/',$size)) {
			$size = 't';
		}
		if (!preg_match('/^span|li|div|none$/',$html_tag)) {
			$html_tag = 'span';
		}
		if (!preg_match('/^entry|image|none$/',$link)) {
			$link = 'image';
		}
		$exif = (bool)$exif;
		if (!preg_match('/^entry|image|none$/',$legend)) {
			$legend = 'none';
		}
		if (!preg_match('/^entry|image|none$/',$bubble)) {
			$bubble = 'image';
		}
		if (!preg_match('/^excerpt|content|full$/',$from)) {
			$from = 'full';
		}
		if (!preg_match('/^none|inherit$/',$alt)) {
			$alt = 'inherit';
		}
		$start = ((int)$start > 0 ? (int)$start - 1 : 0);
		$length = ((int)$length > 0 ? (int)$length : 0);

		// Récupération de l'URL du dossier public
		if (version_compare(DC_VERSION, '2.2-alpha1', '>=')) {
			// New settings system
			$p_url = $core->blog->settings->system->public_url;
		} else {
			// Old settings system
			$p_url = $core->blog->settings->public_url;
		}
		// Récupération du chemin du dossier public
		$p_root = $core->blog->public_path;

		// Contruction du pattern de recherche de la source des images dans les balises img
		// -> à noter que seules les images locales sont traitées
		$p_site = preg_replace('#^(.+?//.+?)/(.*)$#','$1',$core->blog->url);
		$pattern_path = '(?:'.preg_quote($p_site,'/').')?'.preg_quote($p_url,'/');
		$pattern_src = sprintf('/src="%s(.*?\.(?:jpg|jpeg|gif|png|JPEG|JPG|GIF|PNG))"/msu',$pattern_path);

		// Buffer de retour
		$res = '';
		
		// Si aucune liste de billet n'est fournie en paramètre, on utilise le contexte courant
		if (is_null($rs)) {
			$rs = $_ctx->posts;
		}
		if (is_null($rs)) {
			exit;
		}

		if (!$rs->isEmpty()) {
			// Recherche dans le contenu du billet
			$subject = ($from != 'content' ? $rs->post_excerpt_xhtml : '').($from != 'excerpt' ? $rs->post_content_xhtml : '');

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
							$dim = '';
							if (($src_img = self::ContentImageLookup($p_root,$i,$size,$sens,$dim,$sizes)) !== false) {

								// L'image existe, on construit son URL
								$src_img = $p_url.(dirname($i) != '/' ? dirname($i) : '').'/'.$src_img;

								// Recherche alt et title
								$img_alt = (!preg_match('/alt="(.*?)"/msu',$m[1][$idx],$alt_value) ? '' : $alt_value[1]);
								$img_title = (!preg_match('/title="(.*?)"/msu',$m[1][$idx],$title_value) ? '' : $title_value[1]);

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
										$img_legend = $rs->post_title;
										// La légende est liée au billet
										$img_legend = '<a href="'.$rs->getURL().'" title="'.sprintf(__('Go to entry %s'),$img_legend).'">'.$img_legend.'</a>';
									}
								}
								
								if ($bubble != 'none') {
									// Un titre d'image est requis
									if ($bubble == 'image') {
										// Le titre est déjà positionné
										;
									} else {
										// On utilise le titre du billet
										$img_title = html::escapeHTML($rs->post_title);
									}
								} else {
									// Pas de titre sur l'image
									$img_title = '';
								}

								// Ouverture div englobante si en div et légende requise (et existante)
								if ($legend != 'none' && $html_tag == 'div') {
									$res .= '<div class="outer_'.$sens.'">';
									$res .= "\n";
								}

								// Ouverture balise
								if ($html_tag != 'none') {
									// Début de la balise englobante
									$res .= '<'.$html_tag.' class="'.$sens.'">';

									if ($link != 'none') {
										// Si un lien est requis
										if ($link == 'image') {
											// Lien vers l'image originale
											$href = self::ContentImageLookup($p_root,$i,"o",$sens,$dim,$sizes);
											$href = $p_url.(dirname($i) != '/' ? dirname($i) : '').'/'.$href;
											$href_title = $img_alt;
										} else {
											// Lien vers le billet d'origine
											$href = $rs->getURL();
											$href_title = html::escapeHTML($rs->post_title);
										}
										$res .= '<a href="'.$href.'" title="'.$href_title.'">';
									}
								}
								
								// Gestion option alt : inherit / none
								if ($alt == 'none') $img_alt = '';

								// Mise en place de l'image
								$res .= '<img src="'.$src_img.'" ';

								// Rajout de la classe si indiquée
								if ($class != '') {
									$res .= 'class="'.html::escapeHTML($class).'" ';
								}
								// Mise en place des dimensions de l'image si demandé
								if ($img_dim <> 'none') {
									$res .= 'width="'.$dim[0].'px" height="'.$dim[1].'px" ';
								}
								$res .= 'alt="'.$img_alt.'" '.($img_title == '' ? '' : 'title="'.$img_title.'" ').'/>';

								if ($html_tag != 'none') {
									// Fin de la balise englobante
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
								}
							} else {
								// L'image au format demandé n'a pas été trouvée, on cherchera une image de plus pour tenter de satisfaire la demande
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
	private static function ContentImageLookup($root, $img, $size, &$sens, &$dim, $sizes)
	{
		// Récupération du nom et de l'extension de l'image source
		$info = path::info($img);
		$base = $info['base'];

		if (substr($info['dirname'],-1) != '/') $info['dirname'] .= '/';
		if (substr($root,-1) != '/') $root .= '/';
		
		// Suppression du suffixe rajouté pour la création des miniatures s'il existe dans le nom de l'image
		if (preg_match('/^\.(.+)_('.$sizes.')$/',$base,$m)) {
			$base = $m[1];
		}
		
		$res = false;
		if ($size != 'o' && file_exists($root.$info['dirname'].'.'.$base.'_'.$size.'.jpg')) {
			// Une miniature au format demandé a été trouvée
			$res = '.'.$base.'_'.$size.'.jpg';
			//Récupération des dimensions de la miniature
			$media_info = getimagesize($root.$info['dirname'].$res);
		}
		else {
			// Recherche l'image originale
			$f = $root.$info['dirname'].$base;
			if (file_exists($f.'.'.$info['extension'])) {
				$res = $base.'.'.$info['extension'];
			} elseif (file_exists($f.'.jpg')) {
				$info['extension'] = 'jpg';
				$res = $base.'.'.$info['extension'];
			} elseif (file_exists($f.'.jpeg')) {
				$info['extension'] = 'jpeg';
				$res = $base.'.'.$info['extension'];
			} elseif (file_exists($f.'.png')) {
				$info['extension'] = 'png';
				$res = $base.'.'.$info['extension'];
			} elseif (file_exists($f.'.gif')) {
				$info['extension'] = 'gif';
				$res = $base.'.'.$info['extension'];
			}
			// Récupération des dimensions de l'image originale
			if (file_exists($root.$info['dirname'].$base.'.'.strtoupper($info['extension']))) {
				$media_info = getimagesize($root.$info['dirname'].$base.'.'.strtoupper($info['extension']));
			} else {
				$media_info = getimagesize($root.$info['dirname'].$base.'.'.$info['extension']);
			}
		}
		// Détermination de l'orientation de l'image
		$sens = ($media_info[0] > $media_info[1] ? "landscape" : "portrait");
		if (!$dim) {
			$dim = $media_info;
		}
		
		if ($res) {
			return $res;
		}
		return false;
	}
}
?>