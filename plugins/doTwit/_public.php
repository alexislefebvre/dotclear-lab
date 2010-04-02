<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Dotwit, a plugin for Dotclear.
# 
# Copyright (c) 2007 Valentin VAN MEEUWEN
# <adresse email>
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class doTwit
{
	private static $clean = 0;
	private static $path_cache = 'cache/';
	
	public static function dotwitWidget(&$w) {
    	global $core;
		$cache_file = self::$path_cache.'dotwit_'.md5($w->idTwitter.$w->timeline_friends);
    
    	//Affichage page d'accueil seulement
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
    try{
		$host = 'twitter.com';
		if( $w->timeline_friends  ) {
			$url = 'http://twitter.com/statuses/friends_timeline/'.$w->idTwitter.'.xml';
			//$path = '/statuses/friends_timeline/'.$w->idTwitter.'.xml';
		}else{
			$url = 'http://twitter.com/statuses/user_timeline/'.$w->idTwitter.'.xml';
			//$path = '/statuses/user_timeline/'.$w->idTwitter.'.xml';
		}		
		
		if( @filemtime($cache_file) < time() - 60*15 ) 
		{			
			$oHttp = new netHttp('');
			$oHttp->readURL($url,$ssl,$host,$port,$path,$user,$pass);
			$oHttp->setHost($host,$port);
			//$oHttp->useSSL($ssl);
			
			$user = $w->idTwitter;
			$pass = $w->pwdTwitter;
			
			$oHttp->setAuthorization($user,$pass);
			$oHttp->get($path);
			$xml_file = $oHttp->getContent();
			
			if ( $xml = @simplexml_load_string($xml_file) )
			{
				if ( $xml->error == '' && $fp = @fopen($cache_file, 'wb'))
				{
					fwrite($fp, $xml_file);
					fclose($fp);
				}
			} else {
				$xml = @simplexml_load_string(@file_get_contents($cache_file));
			}
		} elseif ( file_exists($cache_file) ) {
			$xml = @simplexml_load_string(@file_get_contents($cache_file));
		}
		
	}catch (Exception $e){
		  
	}
		$res =
		'<div class="doTwit">'.
		($w->title ? '<h2><a href="http://twitter.com/'.$w->idTwiter.'">'.$w->title.'</a></h2>' : '').
		'<ul>';
		
		$nb = 0;
		
		if( count($xml->status) == 0 )
		{
			$res .= 'Données indisponible sur Twitter !';
			return $res;
		}
		
		foreach($xml->status as $elm) {
			
			$twi['id'][$nb] = (int) $elm->id;
			$twi['desc'][$nb] = eregi_replace("(http|mailto|news|ftp|https)://(([-éa-z0-9\/\.\?_=#@:~])*)", "<a href=\"\\1://\\2\" target=\"_blank\" style=\"color:blue;\">\\1://\\2</a>",$elm->text);
			$twi['screen_name'][$nb] = (string) $elm->user->screen_name;
			$twi['name'][$nb] = (string) $elm->user->name;
			$twi['location'][$nb] = (string) $elm->user->location;
			
			if( $w->display_profil_image ) $twi['img'][$nb] = eregi_replace("_normal.", "_mini.",$elm->user->profile_image_url);
			if( $w->display_timeout) {
				$twi['time'][$nb] = ((int) strtotime($elm->created_at));
				$twi['date'][$nb] = date('d/m/Y H\hi', $twi['time'][$nb]);
				$twi['desc'][$nb] .= ' <a href="http://twitter.com/'.$twi['screen_name'][$nb].'/statuses/'.$twi['id'][$nb].'" target="_blank">depuis '. $twi['date'][$nb].'</a>';
				
			}
						
			$nb++;
			
			if($nb >= $w->limit)break;
		}
		
			
		for ($i=0;$i<$nb;$i++) {

			if( $w->display_profil_image && $twi['img'][$i] != '' ) {
				$res .= '<li class="img" style="background: none;padding-left:2px;border-bottom: 1px solid silver;">';
				$res .= '<a href="http://twitter.com/'.$twi['screen_name'][$i].'" target="_blank" style="font-weight:bold;" title="'.$twi['name'][$i].' ('.$twi['location'][$i].')">';
				$res .= '<img src="'.$twi['img'][$i].'" alt="'.$twi['name'][$i].'" style="float:left;margin-right:3px;margin-bottom:2px;border:1px solid silver;" />';
				$res .= '</a>';
				$res .= '<div style="word-wrap: break-word;padding-left:30px;">';
				$res .= '<a href="http://twitter.com/'.$twi['screen_name'][$i].'" target="_blank" style="font-weight:bold;" title="'.$twi['name'][$i].' ('.$twi['location'][$i].')">';
				$res .= $twi['screen_name'][$i].'</a> ';
				$res .= $twi['desc'][$i].'</div>';
				$res .= '<div style="clear:both;height:1px;">&nbsp;</div></li>';
			}else {
				$res .= '<li style="border-bottom: 1px solid silver;">';
				$res .= '<div style="word-wrap: break-word;">';
				$res .= '<a href="http://twitter.com/'.$twi['screen_name'][$i].'" target="_blank" style="font-weight:bold;" title="'.$twi['name'][$i].' ('.$twi['location'][$i].')">';
				$res .= $twi['screen_name'][$i];
				$res .= '</a> '.$twi['desc'][$i].'</div>';
				$res .= '</li>';				
			}
			
		}
		
		$res .= '</ul></div>';
		
		self::clean_cache();
		
		return $res;
  }
  
  private static function clean_cache() {
		
		if( self::$clean == 1) return true;
		
		if( $dir = @opendir(self::$path_cache) ) {

			while ($f = @readdir($dir)) {
				if(is_file(self::$path_cache.$f) && substr($f,0,7) == 'dotwit_' ) {
					if( filemtime(self::$path_cache.$f) < time() - 60 * 15 ) {
						@unlink(self::$path_cache.$f);
					}
				}
			}
			
			@closedir($dir);
		}
		
		self::$clean = 1;
		return true;
  }
  
}
?>
