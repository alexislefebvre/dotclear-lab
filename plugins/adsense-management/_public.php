<?php
# ***** BEGIN LICENSE BLOCK *****
# Widget Management for DotClear.
# Copyright (c) 2008 Gerits Aurelien. All rights
# reserved.
#

# Cette création est mise à disposition selon le Contrat Paternité-Pas 
# d'Utilisation Commerciale-Pas de Modification 2.0 Belgique disponible 
# en ligne http://creativecommons.org/licenses/by-nc-nd/2.0/be/ ou par 
# courrier postal à Creative Commons, 171 Second Street, Suite 300, San Francisco, 
# California 94105, USA.

$core->addBehavior('publicPrepend',array('AdsenseBehavior'));
class AdsenseTpl
{
        public static function AdsenseWidgets(&$w)
        {
        	global $core;
        	/* Si on demande d'afficher ou non sur une autre page que la page d'accueil  */
		if ($w->homeonly && $core->url->type != 'default') return;		
		/* Echap les caractères Xhtml */
		$title = '<div class="widget_adsense">'.($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '');
		#code client
		$google_ad_client = html::escapeHTML($w->google_ad_client);
		#Choisir la taille
		if ($w->format === "leaderboard"){
			$width = '728';
			$height = '90';
		}
			elseif ($w->format === "banniere"){
				$width = '468';
				$height = '60';
			}
			elseif ($w->format === "demibanniere"){
				$width = '234';
				$height = '60';
			}
			elseif ($w->format === "skyscraper"){
				$width = '120';
				$height = '600';
			}
			elseif ($w->format === "skyscraperlarge"){
				$width = '160';
				$height = '600';
			}
			elseif ($w->format === "banniereverticale"){
				$width = '120';
				$height = '240';
			}
			elseif ($w->format === "grandrectangle"){
				$width = '336';
				$height = '280';
			}
			elseif ($w->format === "rectanglemoyen"){
				$width = '300';
				$height = '250';
			}
			elseif ($w->format === "carre"){
				$width = '250';
				$height = '250';
			}
			elseif ($w->format === "petitcarre"){
				$width = '200';
				$height = '200';
			}
			elseif ($w->format === "petitrectangle"){
				$width = '180';
				$height = '150';
			}
			elseif ($w->format === "bouton"){
				$width = '125';
				$height = '125';
			}
		#Choisir le type d'arrondi
		if ($w->google_ui_features === "classic"){
			$google_ui_features = '0';
		}
			elseif ($w->google_ui_features === "smallround"){
				$google_ui_features = '6';
			}
			elseif ($w->google_ui_features === "round"){
				$google_ui_features = '10';
			}
		#Choisir Les coloris
		if ($w->color === "default"){
			$color_border = 'CCCCCC';
			$color_link = '0000FF';
			$color_bg = 'FFFFFF';
			$color_text = '000000';
			$color_url = '008000';
		}
			elseif ($w->color === "borddemer"){
					$color_border = '336699';
					$color_link = '0000FF';
					$color_bg = 'FFFFFF';
					$color_text = '000000';
					$color_url = '008000';
			}
			elseif ($w->color === "ombre"){
					$color_border = '000000';
					$color_link = '0000FF';
					$color_bg = 'F0F0F0';
					$color_text = '000000';
					$color_url = '008000';
			}
			elseif ($w->color === "encre"){
					$color_border = '000000';
					$color_link = 'FFFFFF';
					$color_bg = '000000';
					$color_text = 'CCCCCC';
					$color_url = '999999';
			}
			elseif ($w->color === "graphite"){
					$color_border = 'CCCCCC';
					$color_link = '000000';
					$color_bg = 'CCCCCC';
					$color_text = '333333';
					$color_url = '666666';
			}
			elseif ($w->color === "clashdesign"){
					$color_border = 'CAF99B';
					$color_link = '0000FF';
					$color_bg = 'FFFFCC';
					$color_text = '000000';
					$color_url = '008000';
			}
			elseif ($w->color === "fashion"){
					$color_border = 'FF6FCF';
					$color_link = '0066CC';
					$color_bg = 'FFFFCC';
					$color_text = '333333';
					$color_url = '666666';
			}
			elseif ($w->color === "yellow_grey"){
					$color_border = '333333';
					$color_link = '940F04';
					$color_bg = 'FFFF66';
					$color_text = 'AECCEB';
					$color_url = 'AECCEB';
			}
		#Choisir la position
		if ($w->position === "left"){
			$position = 'left';
		}
			elseif ($w->position === "center"){
				$position = 'center';
			}
			elseif ($w->position === "right"){
				$position = 'right';
			}
                return
            $title.
             '<div style="text-align:'.$position.'">'.
				'<script type="text/javascript"><!--
				google_ad_client = "pub-'.$google_ad_client.'";
				google_ad_width = "'.$width.'";
				google_ad_height = "'.$height.'";
				google_ad_format = "'.$width.'x'.$height.'_as";
				google_ad_type = "text_image";
				google_ad_channel = "";
				google_color_border = "'.$color_border.'";
				google_color_bg = "'.$color_bg.'";
				google_color_link = "'.$color_link.'";
				google_color_text = "'.$color_text.'";
				google_color_url = "'.$color_url.'";
				google_ui_features = "rc:'.$google_ui_features.'";
				//-->
				</script>
				<script type="text/javascript"
				  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
				</script>'.
            '</div>'.'</div>';
        }
        public static function adsenseTemplate(){
        	global $core;
        	$google_ad_client = $core->blog->settings->google_ad_client;
        	$width = $core->blog->settings->google_ad_width;
        	$height = $core->blog->settings->google_ad_height;
        	$color_border = $core->blog->settings->google_color_border;
			$color_bg = $core->blog->settings->google_color_bg;
			$color_link = $core->blog->settings->google_color_link;
			$color_text = $core->blog->settings->google_color_text;
			$color_url = $core->blog->settings->google_color_url;
        	$position = $core->blog->settings->position;
        	$google_ui_features = $core->blog->settings->google_ui_features;
        	$title = '<div class="template_adsense" style="text-align:'.$position.'">';
                return
		            $title.
		           '<script type="text/javascript"><!--
					google_ad_client = "pub-'.$google_ad_client.'";
					google_ad_width = "'.$width.'";
					google_ad_height = "'.$height.'";
					google_ad_format = "'.$width.'x'.$height.'_as";
					google_ad_type = "text_image";
					google_ad_channel = "";
					google_color_border = "'.$color_border.'";
					google_color_bg = "'.$color_bg.'";
					google_color_link = "'.$color_link.'";
					google_color_text = "'.$color_text.'";
					google_color_url = "'.$color_url.'";
					google_ui_features = "rc:'.$google_ui_features.'";
					//-->
					</script>
					<script type="text/javascript"
					  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
					</script>'.'</div>';
        }
}
$core->tpl->addValue('adsense',array('AdsenseTpl','adsenseTemplate'));
?>