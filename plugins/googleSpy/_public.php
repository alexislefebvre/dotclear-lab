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

$core->tpl->addValue('googleSpyPurposePosts',array('googleSpyTpl','purposePosts'));

class googleSpyTpl {

	# {{tpl:googleSpyKeyWords}}

	public static function track($title, $description, $numLinks, $numKeywords) 
	{
		global $core;
		global $_ctx;

		try{
			$google_str = "/^http:\/\/([a-z]+).google\.([a-z]{2,3})|(co\.[a-z]{2})\//i";
			$keywords = "";
		
			// Si le referer est Google
			if ( isset($_SERVER['HTTP_REFERER']) && preg_match( $google_str,$_SERVER['HTTP_REFERER']) ) {
			
				$url_array = parse_url($_SERVER['HTTP_REFERER']);
				if (!isset($url_array['query'])){
					return "";
				}
	
				parse_str($url_array['query'],$variables);

				if (!isset($variables['q'])){
					return "";
				}

				// On transforme la chaine de caractère en minuscule
				$keywords_text = strtolower(urldecode($variables['q']));
				
				// On supprime les espaces doublons et on ajoute un + sur le dernier caractère que la suppression 
				// des mots à ignorer puisse fonctionner.
				$keywords_text = preg_replace('/\s{1,}/', '+', $keywords_text); 

				// On ignore les mots suivants
				$ignore = array( "un", "une",
						 "de", "du", "des",
						 "la", "le",
						 "pour", "sans", "avec",
						 "sous", "dessus",
						 "tu", "je", "il", "elle", "on", "nous", "vous", "ils", "elles", 
						 "mes ", "mon", "ton", "son ", "ses", "tes", "mes",
						 "as ", "ai", "ont", "avons ", "avez",
						 "suis ", "es", "est", "sont", "êtes",
						 "on", "i", "your", "it", "its", "my", "she", "he", "you", "the", "a", "we");

				// On découpe la chaine 
				$keywords = split('\+',$keywords_text);

				if (count($keywords) > 0){
					$strReq ='SELECT P.post_url, P.post_title '.
					'FROM '.$core->prefix.'post P WHERE P.post_status=1 '.
					'AND P.blog_id="'.$core->blog->id.'" '.
					'AND P.post_id!="'.$_ctx->posts->post_id.'" AND (';
		
					$count = 1;
					foreach ($keywords as $i => $w) {
						if (!in_array($w,$ignore) && $count <= $numKeywords){
							$keywords_sql[$i] = "P.post_words LIKE '%".$core->con->escape($w)."%'";
							$count++;
						} else {
							$keywords[$i] = null;
						}
					}
					$strReq .= implode(' OR ',$keywords_sql).' ';
					$strReq .= ") ORDER by P.post_dt desc ";
					$strReq .= $core->con->limit($numLinks);

					// On lance la requête
					$rs = $core->con->select($strReq);
				
					$buffer = "";
	
					// Si des articles ont été trouvés
					if(!$rs->isEmpty()){
						
						$buffer="<div id=\"purposedLinks\"><h3>".$title."</h3>";
						if ($description != ""){
							$buffer.="<p>".$description."</p>";
						}
						$buffer.="<ul>";
						while ($rs->fetch()) {
							$buffer.="<li><a href=\"".$core->blog->url."post/".$rs->post_url."\"/>".bold($rs->post_title,$keywords)."</a></li>";
						}
						$buffer.="</ul><br />";
						

						//foreach($keywords as $keyword){
						//	if ($keyword != null){
						//		$buffer.= $keyword." / ";
						//	}
						//}
						$buffer.="</div>";
					}

					return $buffer;
				}
				
			}

		} catch (Exception $e) {
			$core->error->add($e->getMessage());
		}

		return "";

	}

	public static function purposePosts($args) 
	{
		if (isset($args["num_links"])){
			$numLinks = $args["num_links"];
		} else {
			$numLinks = 5;
		}

		if (isset($args["num_keywords"])){
			$numKeywords = $args["num_keywords"];
		} else {
			$numKeywords = 3;
		}

		if (isset($args["title"])){
			$title = $args["title"];
		} else {
			$title = "A Lire :";
		}

		if (isset($args["description"])){
			$description = $args["description"];
		} else {
			$description = "";
		}

		$phpCode = '<?php echo googleSpyTpl::track("'.$title.'","'.$description.'",'.$numLinks.','.$numKeywords.') ?>';

		return $phpCode;
	}
}

function bold ($texte, $mots) {
	if (!is_array ($mots) || empty ($mots) || !is_string ($texte)) {
		return false;
	}else{
		$mots=implode ('|', $mots);
		return preg_replace ('@\b('.$mots.')\b@si', '<strong>$1</strong>', $texte);
	}
}

