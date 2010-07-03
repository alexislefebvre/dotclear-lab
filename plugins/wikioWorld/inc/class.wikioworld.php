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
		
		# Object to vote on
		$obj_url = htmlspecialchars(strip_tags($url),ENT_QUOTES);
		$obj_ref = htmlspecialchars(strip_tags('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']),ENT_QUOTES);
		
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$obj_id = $wikioCliIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		elseif(isset($_SERVER['HTTP_CLIENT_IP']))
		{
			$obj_id = $wikioCliIp = $_SERVER['HTTP_CLIENT_IP'];
		}
		else
		{
			$obj_id = $wikioCliIp = $_SERVER['REMOTE_ADDR'];
		}
		
		# Query default values
		//0|0|votez|a voté|0|com|0|
		//Note Wikio | Id du billet dans Wikio | Texte pour voter | Texte déjà voté | A déjà voté | Domaine wikio à utiliser pour voter | Autorisé à voter
		$rsp_note = 0;
		$rsp_id = 0;
		$rs_txt_tovote = __('Vote');
		$rsp_txt_voted = __('Voted');
		$rsp_voted = 0;
		$rsp_domain = '';
		$rsp_auth = 0;
		$rsp_more = '';
		
		# Request values
		$api_url = 'http://web.wikio.fr/getnote?';
		$api_path = '';
		$api_data = array(
			'u' => MD5($obj_url),
			'i' => $obj_id,
			'referer' => MD5($obj_ref)
		);
		
		# Send request
		$client = netHttp::initClient($api_url,$api_path);
		$client->setUserAgent('wikioWorld for Dotclear');
		$client->setPersistReferers(false);
		$client->get($api_path,$api_data);
		
		# Request response
		if ($client->getStatus() == 200) 
		{
			$rs = $client->getContent();
			$exp = explode('|',$rs);
			
			# Parse response
			if(count($exp) == 8)
			{
				list(
					$rsp_note,
					$rsp_id,
					$rs_txt_tovote,
					$rsp_txt_voted,
					$rsp_voted,
					$rsp_domain,
					$rsp_auth,
					$rsp_more,
				) = $exp;
			}
		}
		
		# Existing
		if($rsp_id > 0)
		{
			$res .= 
			"<script type=\"text/javascript\"> \n".
			"var wikiovoted=false; \n".
			"function setWikio(){ \n";
			
			# Can vote
			$suffix = '';
			if($rsp_auth && !$rsp_voted)
			{
				$suffix = '&vote=1';
				
				$res .= 
				"var t=top.document; \n";
				# normal
				if ($style == 'normal')
				{
					$res .= 
					"if(!wikiovoted){ ".
					"n=t.getElementById('wikioNote1'); ".
					"a=t.getElementById('wikioAction1'); \n";
				}
				# Compact
				else
				{
					$res .= 
					"if(!wikiovoted){ ".
					"var n=t.getElementById('wikionote'); ".
					"var a=t.getElementById('wikioaction'); \n";
				}
				$res .= 
				"if(n && !isNaN(parseInt(n.innerHTML))){ ".
				"n.innerHTML=parseInt(n.innerHTML)+1}; ".
				"if(a){a.innerHTML='".$rsp_txt_voted."'}; \n".
				"} \n".
				"wikiovoted=true; \n";
			}
			
			$res .= 
			"} \n".
			"</script> \n";
			
			# Normal
			if ($style == 'normal')
			{
				$voted = 
				$res .= 
				'<div class="wikiobutton1">'.
				'<div class="wikioaction1"><a href="http://www.wikio.'.$rsp_domain.'/article='.$rsp_id.$suffix.'" target="_blank" id="wikioAction1" onclick="setWikio();">'.$suffix.'</a></div>'.
				'<a href="http://www.wikio.'.$rsp_domain.'/article='.$rsp_id.$suffix.'" class="wikiolink" target="_blank" onclick="setWikio();">'.
				'<div class="wikiovote1" align="center" id="wikioNote1">'.$rsp_note.'</div>'.
				'</a>'.
				'</div>';
			}
			# Compact
			else
			{
				$res .= 
				'<div class="wikiobutton">'.
				'<a href="http://www.wikio.'.$rsp_domain.'/article='.$rsp_id.$suffix.'" class="wikioaction" target="_blank" onclick="setWikio();">'.
				'<div class="wikiotxt wb">'.
				'<div class="wikionote"><a id="wikionote" href="http://www.wikio.'.$rsp_domain.'/article='.$rsp_id.$suffix.'" class="wikioaction" target="_blank" onclick="setWikio();">'.$rsp_note.'</a></div>'.
				'<div class="wikioaction"><a href="http://www.wikio.'.$rsp_domain.'/article='.$rsp_id.$suffix.'" class="wikioaction" id="wikioaction" target="_blank" onclick="setWikio();">'.($rsp_voted ? $rsp_txt_voted : $rsp_txt_vote).'</a></div>'.
				'</div>'.
				'</a>'.
				'<div class="wikio"><a href="http://www.wikio.'.$rsp_domain.'" target="_blank" class="wikioimg"><img src="http://www.wikio.'.$rsp_domain.'/shared/img/vote/wikio.gif" alt="www.wikio.'.$rsp_domain.'" border="0" /></a></div>'.
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