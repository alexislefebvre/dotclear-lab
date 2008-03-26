<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Mymeta plugin.
# Copyright (c) 2007 Bruno Hondelatte,  and contributors.
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# Mymeta plugin for DC2 is free sofwtare; you can redistribute it and/or modify
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

$core->tpl->addValue('MiniSEO', array('tplMiniSEO', 'MetaMiniSEO'));
class tplMiniSEO
{
	// Function de nettoyage et ronquage à 180 carac
	public static function MetaMiniSEO($attr)
	{
		global $core;
		
		// Pour l'instant nous gérons uniquement les billets
		$phpCode="";
		$typpost=$core->url->type;
		// Le if suivant sera a remplacer par un switch
		if ($typpost == 'post'){
			$urls = '0';
			if (!empty($attr['absolute_urls'])) {
				$urls = '1';
			}
			$f = $GLOBALS['core']->tpl->getFilters($attr);
			// Pour l'instant j'ai rien trouvé de plus propre que de peupler les fonctions dans une chaine passer au return
			// J'utilise le double " pour le php interne theme et le simple ' pour le plugin
			$mesfunctions='
			function cleanContent($attr) {
				$attr = ereg_replace("\r?\n", " ", strip_tags($attr));
				$attr = ereg_replace("\""," ",$attr);
				$attr = ereg_replace("&amp;nbsp;"," ",$attr);
				$attr = ereg_replace("&nbsp;"," ",$attr);
				return $attr;
			}';
			
			$mesfunctions.='
			function cleanCut($fttext, $ftsize){
   				if(strlen($fttext)>=$ftsize){
      				$fttext = substr($fttext,0,$ftsize);
      				$ftespace = strrpos($fttext," ");
      				$fttext = substr($fttext,0,$ftespace)."...";
   				}
   			return $fttext;
			}'; 
			

			// On provoque un rafraichissement du cache template ne fonctionne pas sur ma config
			$core->blog->triggerBlog();
			
			return
				'<?php
				'.$mesfunctions.'
				$meta = "";
				// Si le plugin myMeta existe
				if (class_exists("myMeta")){
					$objMeta = new dcMeta($core);
					$objMyMeta = new myMeta($core);
					if ($objMyMeta->isMetaEnabled("description")){
						$meta = $objMeta->getMetaStr($_ctx->posts->post_meta,"description");
					}
				}
				// Si mymeta a pas rempli la var meta on prend d office le contenu du billet sinon priorite a mymeta
				if (rtrim($meta)==""){
					$meta= '.sprintf($f,'$_ctx->posts->getExcerpt('.$urls.').$_ctx->posts->getContent('.$urls.')').';
				}
				$meta = cleanContent($meta);
				$meta = cleanCut($meta,"250");
				if (rtrim($meta)!=""){
					$meta = "<meta name=\"description\" content=\"".$meta."\" />";
					echo $meta;
				}
				?>'."\n";
		}
		return $phpCode;
	}
}
?>