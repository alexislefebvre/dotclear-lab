<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of doTwit, a plugin for Dotclear.
#
# Copyright (c) 2007 Valentin VAN MEEUWEN
# <adresse email>
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) { return; }

require dirname(__FILE__).'/_widgets.php';

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
		($w->title ? '<h2><a href="http://twitter.com/'.$w->idTwitter.'">'.$w->title.'</a></h2>' : '').
		'<ul>';
		
		$nb = 0;
		
		if( count($xml->status) == 0 )
		{
			$res .= '<p>'.__('Data inalienable on Twitter!').'</p>';
			return $res;
		}
		
  foreach($xml->status as $elm) {

			$search = array(
				'url' => "/(http|mailto|news|ftp|https):\/\/(([-A-z0-9\/.?_=#@+%&:~])*)/",
				'reply' =>"/@([A-Za-z0-9-_]+)/",
				'hashtag' => "/#([A-z0-9-_]+)/"
			);
			$replace = array(
				'url' => "<span class=\"doTwitlink\"><a href=\"$1://$2\" onclick=\"window.open(this.href); return false;\">$1://$2</a></span>",
				'reply' =>"<span class=\"doTwitreply\"><a href=\"http://twitter.com/$1\" onclick=\"window.open(this.href); return false;\">@$1</a></span>",
				'hashtag' => "<span class=\"doTwithashtag\"><a href=\"http://search.twitter.com/search?q=%23$1\">#$1</a></span>"
			);
			$twi['id'][$nb] = (int) $elm->id;
			$twi['desc'][$nb] = preg_replace($search, $replace, $elm->text);
			$twi['screen_name'][$nb] = (string) $elm->user->screen_name;
			$twi['name'][$nb] = (string) $elm->user->name;
			$twi['location'][$nb] = (string) $elm->user->location;
			
			if( $w->display_profil_image ) $twi['img'][$nb] = eregi_replace("_normal.", "_mini.",$elm->user->profile_image_url);
			if( $w->display_timeout) {
				$twi['time'][$nb] = ((int) strtotime($elm->created_at));
				$twi['date'][$nb] = date('d/m/y H\hi', $twi['time'][$nb]);
				$twi['desc'][$nb] .= ' <br /><span class="doTwitstatut"><a href="http://twitter.com/'.$twi['screen_name'][$nb].'/statuses/'.$elm->id.'" onclick="window.open(this.href); return false;">'.__('on').' '. $twi['date'][$nb].'</a></span>';
				
			}
						
			$nb++;
			
			if($nb >= $w->limit)break;
		}
		
			
		for ($i=0;$i<$nb;$i++) {

			if( $w->display_profil_image && $twi['img'][$i] != '' ) {
				$res .= '<li class="doTwitavatar">';
				$res .= '<a href="http://twitter.com/'.$twi['screen_name'][$i].'" onclick="window.open(this.href); return false;" title="'.$twi['name'][$i].' ('.$twi['location'][$i].')">';
				$res .= '<img src="'.$twi['img'][$i].'" alt="'.$twi['name'][$i].'" />';
				$res .= '</a>';
				$res .= '<div class="doTwittxt">';
				$res .= '<a href="http://twitter.com/'.$twi['screen_name'][$i].'" onclick="window.open(this.href); return false;" title="'.$twi['name'][$i].' ('.$twi['location'][$i].')">';
				$res .= $twi['screen_name'][$i].'</a> ';
				$res .= $twi['desc'][$i].'</div>';
				$res .= '<div class="doTwitclear">&nbsp;</div></li>';
			}else {
				$res .= '<li class="doTwitnoavatar">';
				$res .= '<div class="doTwittxt">';
				$res .= '<a href="http://twitter.com/'.$twi['screen_name'][$i].'" onclick="window.open(this.href); return false;" title="'.$twi['name'][$i].' ('.$twi['location'][$i].')">';
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
