<?php

# ***** BEGIN LICENSE BLOCK *****
# Widget SnapMe for DotClear.
# Copyright (c) 2007 Ludovic Toinel, All rights
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

$core->url->register('snapme-gallery','snapme/gallery','^snapme/gallery$',array('snapMeUrlHandler','galleryHandler'));
$core->url->register('snapme-upload','snapme/upload','^snapme/upload$',array('snapMeUrlHandler','uploadHandler'));
$core->url->register('snapme-lastsnap','snapme/lastsnap','^snapme/lastsnap$',array('snapMeUrlHandler','lastSnapHandler'));
$core->url->register('snapme-getsnap','snapme/getsnap','^snapme/getsnap/(.*)$',array('snapMeUrlHandler','getSnapHandler'));

require dirname(__FILE__).'/class.dc.snapme.php';

/**
 Classe contenant les différents éléments graphiques 
 */
class snapMeTpl
{

	private static function getUrl($resource){

		global $core;

		if($core->blog->settings->system->url_scan == "path_info"){
		
			if (substr($core->blog->url, -1, 1) != "/"){
				$snapMeResource = $core->blog->url."/".$resource;
			} else {
				$snapMeResource = $core->blog->url.$resource;
			}

		} else if($core->blog->settings->system->url_scan == "query_string"){
		
			if (substr($resource, 0, 1) == "?"){
				$resource = substr($resource, 1);
			}

			if (substr($core->blog->url, -1, 1) != "?"){
				$snapMeResource = $core->blog->url."?".$resource;
			}  else {
				$snapMeResource = $core->blog->url.$resource;
			}
		}


		return $snapMeResource;
	}

	private static function getResource($filename){
		return snapMeTpl::getUrl("?pf=/snapme/".$filename);
		//return snapMeTpl::getUrl("plugins/snapme/".$filename);
	}

	/**
	@function widget
	Affiche le widget SnapMe

	return le XHTML structurant le widget
	*/
	public static function widget($w)
	{
		global $core;

	        if ($w->homeonly && $core->url->type != 'default') {
                        return;
                }

		$buffer = "<div id=\"snapMe\">";

		// DAO
		$dcSnapMe = new dcSnapMe($core->blog);
	
		if ($w->display == 2){
			$rs_snap = $dcSnapMe->getRandomSnap();
		} else {
			$rs_snap = $dcSnapMe->getLastSnap();
		}

		$buffer .= "<h2><a href=\"http://www.geeek.org/category/SnapMe\">".$w->title."</a></h2>";

		$snapMeRegister = $core->blog->url."snapme";
		
		// Si des snaps ont été trouvés
		if ($rs_snap != null && $rs_snap->post_time != null){

			// Date
			$post_time = (string) $rs_snap->post_time;
			$date = date("d/m/Y", $post_time);
			$heure = date("H\hi", $post_time);
	
			if ($rs_snap->pseudo != ""){
				$pseudo = $rs_snap->pseudo;
			} else {
				$pseudo = __('Unknown');
			}
	
			if($rs_snap->blog_url != ""){
				$buffer .= "<a href=\"".$rs_snap->blog_url."\">";
			} else {
				$buffer .= "<a href=\"#\" onclick=\"window.open('".snapMeTpl::getResource("snap.swf")."', 'snapme','width=220,height=310,resizable=no,scrollbars=no,status=no');\" >";
			}
			$buffer .= "<img src=\"".snapMeTpl::getResource("snapshots/".$rs_snap->file_name)."\" alt=\"".$rs_snap->pseudo."\" />";
			$buffer .= "</a><br/>";
			$buffer .= "<b>".$pseudo."</b><br/><small>Le ".$date." @ " .$heure."</small><br/><br/>";

		// Si aucun snap en base de données
		} else {
				$buffer .="<p>".__('No snap')."</p>";
		}

		$buffer .= "<ul>";
		$buffer .= "<li><a href=\"#\" onclick=\"window.open('".snapMeTpl::getResource("snap.swf")."', 'snapme', 'width=220,height=310,resizable=no,scrollbars=no,status=no');\" >".__('Take a picture')."</a></li>";
		$buffer .= "<li><a href=\"".snapMeTpl::getUrl("snapme/gallery")."\">".__('Watch the gallery')."</a></li>";
		$buffer .= "</ul>";
		$buffer .= "</div>";

		return $buffer;
	}

	/**
	@function tplPageTitle
	Retourne le nom de la page de la galerie

	return le nom de la page de la galerie
	*/
        public static function tplPageTitle($args)
        {
		return __('SnapMe Gallery');
	}

	/**
	@function tplGallery
	Affiche la galerie de snaps

	return le XHTML structurant la galerie
	*/

	public static function tplGallery($args)
	{

		global $core;
		
		if (isset($args["nb_snap"])){
			$nb_snap = $args["nb_snap"];	
		} else {
		  	$nb_snap = 51;	
		}


                if (isset($args["nb_cols"])){
                        $nb_cols = $args["nb_cols"];
                } else {
                        $nb_cols = 3;
                }
	

		// DAO
		$dcSnapMe = new dcSnapMe($core->blog);
		$rs_snap = $dcSnapMe->getAllSnaps($nb_snap);
		$rs_count = $dcSnapMe->getCountSnap();

		// Affichage des éléments
		$i=0;
		
		// Initialisation du buffer
		$buffer = "";

		if ($rs_snap->count() == 0 ){
			$buffer .= "<p>".__('No snap has been found').".</p>";
			return $buffer;
		}  else if (!defined('DC_CONTEXT_ADMIN')){
			$buffer .= "<h1>".__('SnapMe Gallery')." <small>(".$rs_count->count." snaps)</small></h1>";
		}


		$buffer .= "<table class=\"gallery\">";

		while($rs_snap->fetch()){

			 // Début d'une ligne
			 if($i==0){
				$buffer.="<tr>";
			 }

			 // Récupération des différents éléments
			 $snapfile = $rs_snap->file_name;

			 $pseudo = $rs_snap->pseudo;
			 if ($pseudo == "") $pseudo = "Inconnu";
			 
			// Pour le panneau d'admnistration 
			if (defined('DC_CONTEXT_ADMIN')){
					$ip = "<br/>(". $rs_snap->ip.")";
					$deleteThisPicture = "<br /><a href=\"plugin.php?p=snapme&amp;delete=".$rs_snap->id."\">[ ".__('Remove')." ]</a>";

			} else {
				$ip = "";
				$deleteThisPicture = "";
			}

			 $blog_url = $rs_snap->blog_url;
			 $post_time = (string) $rs_snap->post_time;

			 $date = date("d/m/Y", $post_time);
			 $heure = date("H\hi", $post_time);

			 // La photo a t'elle été postée aujourd'hui ?
			 $today = (date("d/m/Y", $post_time) == date("d/m/Y", time()));

			 if ($today){
				$buffer.="<td class=\"today\"><a href=\"$blog_url\" rel=\"nofollow\"><img src=\"".snapMeTpl::getResource("snapshots/$snapfile")."\" alt=\"".__('Snap')." $pseudo\" /></a><br/>".__('Today')." $heure <br/>".__('from')." $pseudo $ip $deleteThisPicture</td>";
			 } else {
				$buffer.="<td><a href=\"$blog_url \" rel=\"nofollow\"><img src=\"".snapMeTpl::getResource("snapshots/$snapfile")."\" alt=\"$snapfile\" /></a><br/>$date $heure <br />".__('from')." $pseudo $ip $deleteThisPicture </td>";
			 }

			 // Fin de lignes
			 if($i==($nb_cols-1)) {
				$buffer.="</tr>";
			 }

			 $i= ($i + 1) % $nb_cols;
		}

		// Affichage des cases vides
		while ($i != 0 && $i < $nb_cols){
			
			$buffer.="<td></td>";
			
			 // Fin de lignes
			 if($i == ($nb_cols-1)) {
				$buffer.="</tr>";
			 }

			$i++;
		}

		$buffer.="</table>";

		return $buffer;
	}

	/**
	@function tplUpload
	Affiche la page d'envoi de l'image

	return le XHTML structurant la page d'upload
	*/
	public static function tplUpload($attr,$content)
	{
		global $core;

		$buffer='';

		return $buffer;
	}

}

/**
 Classe de gestion des URL
 */
class snapMeUrlHandler extends dcUrlHandlers
{
	/**
	@function galleryHandler
	Affiche la gallery
	*/
	public static function galleryHandler($args) {
		global $core;
		
		$old = $core->tpl->use_cache;

		// Nous sommes obligé de désactiver le cache pour générer la page
		$core->tpl->use_cache=false;
		$core->tpl->addValue('snapMeGallery',array('snapMeTpl','tplGallery'));
		$core->tpl->addValue('snapMePageTitle',array('snapMeTpl','tplPageTitle'));
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/tpl/');

		@self::serveDocument('gallery.html');

		// Nous remettons le cache à son état initial
		$core->tpl->use_cache=$old;
		exit;
	}

	/**
	@function uploadHandler

	Gère les upload de photo
	*/
	public static function uploadHandler($args) {
		global $core;

		// Récupération des données du POST HTTP
		if (isset($_POST['pseudo'])) {
			$pseudo = escapeSQL($_POST['pseudo']);
		} else {
			$pseudo = "";
		}

		if (isset($_POST['blog_url']) && $_POST['blog_url']!= "http://") {
			$blog_url = escapeSQL($_POST['blog_url']);
		} else {
			$blog_url="";
		}

		$ip = $_SERVER['REMOTE_ADDR'];
		$lv = $_POST['tab'];

		$temp = explode(",",$lv);

		// Vérification de la taille du tableau
		if (sizeof($temp) != (160*120)){
			throw new Exception ("Taille des données reçues invalide !");
		}

		// Création de l'image
		settype($temp[1],'integer');
		$sortie = imagecreatetruecolor(160,120);
		$k=0;

		$check = false;
		for($i=0;$i<120;$i++){
		  for($j=0;$j<160;$j++){
		     if ($temp[$k] != 0){
				$check = true;
		     }
		     imagesetpixel($sortie,$j,$i,$temp[$k]);
		     $k++;
		  }
		}

		// Si l'image est toute noire il s'agit d'une erreur
		if ($check == false){
			throw new Exception ("Image reçue toute noire !");
		}

		// Enregistrement du jpeg sur le file system
		$filename = "snap_".time().".jpg";
		$filepath = dirname(__FILE__)."/snapshots/".$filename;
		imagejpeg($sortie,$filepath,100);
		imagedestroy($sortie);

		// Enregistrement du snap en base
		$dcSnapMe = new dcSnapMe($core->blog);
		$dcSnapMe->addSnap($pseudo,$blog_url,$ip,$filename);
		
		$smallfilepath = dirname(__FILE__).'/snapshots/small/'.$pseudo.'.jpg';
		if (file_exists($smallfilepath)){
	              unlink($smallfilepath);	
		}
	        

		// Affichage de l'image et fermeture de la fenêtre
		echo "<script type=\"text/javascript\">opener.window.location.reload(1);self.close();</script>";

		exit;
	}

	/**
	@function uploadHandler
	Affiche le dernier snap
	*/
	public static function lastSnapHandler($args) {

		global $core;

		// DAO
		$dcSnapMe = new dcSnapMe($core->blog);
		$rs_snap = $dcSnapMe->getLastSnap();

		$filepath =  dirname(__FILE__).'/snapshots/'.$rs_snap->file_name;
		header('Content-type: image/jpeg');
                header('Content-length: '.filesize($filepath));
                readfile($filepath);

		exit;
	}

	/**
	@function uploadHandler
	Affiche le dernier snap pour un pseudo défini
	*/
	public static function getSnapHandler($args) {

		global $core;

		$login = escapeSQL($args);

		$smallfilepath = dirname(__FILE__).'/snapshots/small/'.$login.'.jpg';
		
		// Si le fichier existe on le retourne ...
		if (!file_exists($smallfilepath)){

			// DAO
			$dcSnapMe = new dcSnapMe($core->blog);
			$rs_snap = $dcSnapMe->getLastSnapByNickname($login);
	
			if ($rs_snap->count() == 0){
				// Le pseudo est inconnu de la base
				$smallfilepath = dirname(__FILE__).'/snapshots/small/unknown.jpg';
			} else {
				// Le pseudo est connu
				$filepath =  dirname(__FILE__).'/snapshots/'.$rs_snap->file_name;
				$img_in = imagecreatefromjpeg($filepath);
				$img_out = imagecreatetruecolor(80, 60);
				imagecopyresampled($img_out, $img_in, 0, 0, 0, 0, imagesx($img_out), imagesy($img_out), imagesx($img_in), imagesy($img_in));
				$status = imagejpeg($img_out,$smallfilepath);
				if (!$status){
					$smallfilepath = dirname(__FILE__).'/snapshots/small/unknown.jpg';
				}
			}
		}

		header('Content-type: image/jpeg');
		header('Content-length: '.filesize($smallfilepath));
   		readfile($smallfilepath);

		exit;
	}
}

 function escapeSQL($str)
{
	$str = htmlspecialchars($str,ENT_COMPAT,'UTF-8');
	return mysql_escape_string($str);
}
?>