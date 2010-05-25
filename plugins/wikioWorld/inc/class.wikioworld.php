<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of wikioWorld, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class wikioWorld
{
	public static function cleanURL($url)
	{
		$url = str_replace('/','%2F',rawurlencode($url));
		return preg_replace('/%3F$/','',$url);
	}
	
	public static function widgetBox($id,$title='',$content='')
	{
		return 
		'<div class="wikioworld-widget-'.$id.'">'.
		($title ? '<h2>'.html::escapeHTML($title).'</h2>' : '').
		$content.
		'</div>';
	}
	
	public static function cssEntryVote()
	{
		return 
		"\n<!-- CSS for wikioWorld --> \n".
		"<style type=\"text/css\"> \n".			
		".wikiotext {font-family:Arial,Helvetica,sans-serif;font-size:9px;color:#666666;} \n".
		# Normal
		".wikiobutton{width:47px;border:1px solid #b7bbbe;margin:0px;padding:0px;background-color:#FFFFFF;} \n".
		".wikio{height:15px;margin-left:1px;border:0px !important;} \n".
		".wikiotxt{height:50px;text-align:center;} \n".
		".a{background:url(http://www.wikio.fr/shared/img/vote/trait.gif) repeat-x bottom !important;} \n".
		".wb{background:url(http://www.wikio.fr/shared/img/vote/degrade.gif) repeat-x bottom !important;} \n".
		".wikionote{font-family:Arial, Helvetica, sans-serif;font-weight:bold;padding:6px 0 4px 0;} \n".
		".wikioaction {font-family:Arial, Helvetica, sans-serif;font-weight:bold;font-size:11px;color:#333333;} \n".
		".wikioimg{border:0px !important;padding:0px !important;margin:0px !important;} \n".
		".wikioimg img{border:0px !important;padding:0px !important;margin:0px !important;} \n".
		".wikionote a {text-decoration:none;color:#f18717;font-size:16px;border:0px !important;} \n".
		".wikionote a:visited {text-decoration:none;color:#f18717;font-size:16px;border:0px !important;} \n".
		".wikionote a:hover{text-decoration:none;color:#f18717;font-size:16px;border:0px !important;} \n".
		".wikioaction a {text-decoration:none;color:#333333;border:0px !important;} \n".
		".wikioaction a:visited {text-decoration:none;color:#333333;border:0px !important;} \n".
		".wikioaction a:hover{text-decoration:underline;color:#333333;border:0px !important;} \n".
		# Compact
		".wikiolink a:link{text-decoration:none;color:#666666;} \n".
		".wikiolink a:visited,.wikiolink a:hover{text-decoration:none;color:#f99412;border:0px !important;} \n".
		".wikiobutton1{float:left;background:url(http://www.wikio.fr/shared/img/vote/bouton.gif) no-repeat;width:120px;height:17px;font-family:Arial,Helvetica,sans-serif;font-size: 9px;color: #666666;} \n".
		".wikioaction1{line-height:14px;float:left;height:14px;padding:1px 2px 2px 4px;font-family:Arial, Helvetica, sans-serif;font-size:11px;font-weight:bold;} \n".
		".wikiovote1{line-height:14px;float:right;height:14px;margin-right:49px;padding:2px 2px 1px 4px;font-family:Arial, Helvetica, sans-serif;font-size:12px;font-weight:bold;color:#f99412;border-left:1px solid #b8bbbd;cursor:pointer; } \n".
		".wikioaction1 a:link,.wikioaction1 a:visited {text-decoration:none;color:#666666;border:0px !important;} \n".
		".wikioaction1 a:hover{text-decoration:none;color:#f99412;border:0px !important;} \n".
		".wikiovote1 a:link,.wikiovote1 a:visited,.wikiovote1 a:hover{text-decoration:none;color:#f99412;border:0px !important;} \n".
		"</style> \n";
	}
	
	public static function buttonEntryVote($url,$style=0)
	{
		$res = '';
		$wikioThisUrl = htmlspecialchars(strip_tags($url),ENT_QUOTES);
		$wikioReferer = htmlspecialchars(strip_tags('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']),ENT_QUOTES);
		
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$wikioCliIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		elseif(isset($_SERVER['HTTP_CLIENT_IP']))
		{
			$wikioCliIp = $_SERVER['HTTP_CLIENT_IP'];
		}
		else
		{
			$wikioCliIp = $_SERVER['REMOTE_ADDR'];
		}
		
		$wikioSuffix="fr";
		$wikioUrl="web.wikio.".$wikioSuffix;
		$wikioSuffix="";
		$wikioNote=0;
		$wikioId=0;
		$wikioPattern1="";
		$wikioPattern2="";
		$wikioPattern3="/article=";
		$wikiohasVoted="";
		$bVoted=0;
		$wikioAllowVote=1;
		
		if($wikioNote=file_get_contents("http://".$wikioUrl.$wikioSuffix."/getnote?u=".MD5($wikioThisUrl)."&i=".$wikioCliIp."&referer=".MD5($wikioReferer)))
		{
			if(ereg("([0-9]*)\|([0-9]*)\|(.*)\|(.*)\|([0-9]*)\|(.*)\|([0-9]*)\|",$wikioNote,$res))
			{
				$wikioNote=$res[1];
				$wikioId=$res[2];
				$wikioPattern1=($res[5]==1)? $res[4]:$res[3];
				$wikioPattern2=$res[4];
				$wikiohasVoted=($res[5]==0 && $res[7]==1)? "&vote=1":"";
				$bVoted=$res[5];
				$wikioSuffix=$res[6];
				$wikioAllowVote=$res[7];
			}
		}
		
		if($wikioId > 0)
		{
			$wikioUrl = 'www.wikio.'.$wikioSuffix;
			
			$res .= 
			"<script type=\"text/javascript\"> \n".
			"var wikiovoted=false; \n".
			"function setWikio(){ \n";
			
			if($wikioAllowVote > 0 && $bVoted==0)
			{
				$res .= 
				"var t=top.document; \n";
				# normal
				if ($style == 'normal')
				{
					$res .= "if(!wikiovoted){n=t.getElementById('wikioNote1');a=t.getElementById('wikioAction1'); \n";
				}
				# Compact
				else
				{
					$res .= "if(!wikiovoted){var n=t.getElementById('wikionote');var a=t.getElementById('wikioaction'); \n";
				}
				$res .= 
				"if(n && !isNaN(parseInt(n.innerHTML))){n.innerHTML=parseInt(n.innerHTML)+1};if(a){a.innerHTML='".$wikioPattern2."'}; \n".
				"} \n".
				"wikiovoted=true; \n";
			}
			
			$res .= 
			"} \n".
			"</script> \n";
			
			# Normal
			if ($style == 'normal')
			{
				$res .= 
				'<div class="wikiobutton1">'.
				'<div class="wikioaction1"><a href="http://'.$wikioUrl.$wikioPattern3.$wikioId.$wikiohasVoted.'" target="_blank" id="wikioAction1" onclick="setWikio();">'.$wikioPattern1.'</a></div>'.
				'<a href="http://'.$wikioUrl.$wikioPattern3.$wikioId.$wikiohasVoted.'" class="wikiolink" target="_blank" onclick="setWikio();">'.
				'<div class="wikiovote1" align="center" id="wikioNote1">'.$wikioNote.'</div>'.
				'</a>'.
				'</div>';
			}
			# Compact
			else
			{
				$res .= 
				'<div class="wikiobutton">'.
				'<a href="http://'.$wikioUrl.$wikioPattern3.$wikioId.$wikiohasVoted.'" class="wikioaction" target="_blank" onclick="setWikio();">'.
				'<div class="wikiotxt wb">'.
				'<div class="wikionote"><a id="wikionote" href="http://'.$wikioUrl.$wikioPattern3.$wikioId.$wikiohasVoted.'" class="wikioaction" target="_blank" onclick="setWikio();">'.$wikioNote.'</a></div>'.
				'<div class="wikioaction"><a href="http://'.$wikioUrl.$wikioPattern3.$wikioId.$wikiohasVoted.'" class="wikioaction" id="wikioaction" target="_blank" onclick="setWikio();">'.$wikioPattern1.'</a></div>'.
				'</div>'.
				'</a>'.
				'<div class="wikio"><a href="http://'.$wikioUrl.'" target="_blank" class="wikioimg"><img src="http://'.$wikioUrl.'/shared/img/vote/wikio.gif" alt="'.$wikioUrl.'" border="0" /></a></div>'.
				'</div>';
			}
		}
		return $res;
	}
	
	public static function topCatCombo()
	{
		return array(
			__('General') => 'general', // Général
			__('Car') => 'auto', // Auto
			__('Cinema') => 'cinema', // Cinéma
			__('Various') => 'divers', // Divers
			__('Economy') => 'economie', // Economie
			__('Undertaker') => 'entrepreneurs', // Entrepreneurs
			__('Football') => 'football', // Football
			__('High-Tech') => 'high-tech', // High-Tech
			__('International') => 'international', // International
			__('Video game') => 'jeux_video', // Jeux vidéo
			__('Freeware') => 'logiciels_libres', // Logiciels libres
			__('Marketing') => 'marketing', // Marketing
			__('Music') => 'musique', // Musique
			__('Health') => 'sante', // Santé
			__('Scrapbooking') => 'scrapbooking', // Scrapbooking
			__('Society') => 'societe', // Société
			__('Mobile technologies') => 'technologies_nomades', // Technologies nomades
			__('Pets') => 'animaux', // Animaux
			__('China') => 'chine', // Chine
			__('Culture') => 'culture', // Culture
			__('Right') => 'droit', // Droit
			__('Employment') => 'emploi', // Emploi
			__('Environment') => 'environnement', // Environnement
			__('Gastronomy') => 'gastronomie', // Gastronomie
			__('Illustration') => 'illustration', // Illustration
			__('Gambling') => "jeux_d'argent", // Jeux d'argent
			__('Literature') => 'litterature',	// Littérature
			__('Entertainment') => 'loisirs', // Loisirs
			__('Fashion') => 'mode', // Mode
			__('Politics') => 'politique', // Politique
			__('Science') => 'science', // Science
			__('SEO') => 'seo', // SEO
			__('Sport') => 'sport' // Sport
		);
	}
}
?>