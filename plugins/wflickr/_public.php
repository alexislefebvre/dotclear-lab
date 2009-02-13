<?php

// Name 			  'wflickr',
// Description	'Affichage d\'images depuis Flickr dans un widget',
// Author 			'Charles Delorme http://www.suricat.net/',
//              http://www.suricat.net/web/index.php/2008/01/24/423-wflickr-widget-dotclear-pour-vos-photos-flickr
// Version 			'1.2',
// License      'CC-BY-SA' http://creativecommons.org/licenses/by-sa/2.0/fr/


class publicMyWidget
{
	public static function myWidget(&$w)
	{
    echo "\n<!-- wflickr 1.2 by suricat - start \"".$w->title."\" -->\n";
    
    // On peut ne rien vouloir afficher
    if($w->afficher !=0)
    {
      echo "<div ".$w->stylediv.">\n";
      echo "<h2>".$w->title."</h2>\n";
      
      require_once("phpFlickr.php");
      $f = new phpFlickr($w->apikey);
      
      if($w->nbphotos == "")
      {
        $nbphotos = 5;
      }
      else
      {
        $nbphotos = $w->nbphotos;
      }
      
      if($w->nbcols == "")
      {
        $nbcols = 1;
      }
      else
      {
        $nbcols = $w->nbcols;
      }
      
      // Choix des photos à afficher
      // 1 defaut : un utilisateur
      // 2        : un album appartement à l'utilisateur
      // 3        : un groupe
      switch($w->afficher)
      {
        default:
          $photos = $f->people_getPublicPhotos($w->userid,'date_taken',$nbphotos);
        break;
        case 2: 
          $photos = $f->photosets_getPhotos($w->albumid,'date_taken',1,$nbphotos);
        break;
        case 3: 
          $photos = $f->groups_pools_getPhotos($w->groupid,NULL,NULL,'date_taken',$nbphotos);
        break;
      }
      
      //echo "<!--\n";
      //print_r($photos);
      //echo "-->\n";
      
      //echo "<pre>";
      //print_r($photos);
      //echo "</pre>";
      
      if ($photos != FALSE)
      {
        $i=0;
        foreach ($photos['photo'] as $photo) 
        {
          //echo "<pre>";
          //print_r($photo);
          //echo "</pre>";
      
          echo "<a href=\"http://www.flickr.com/photos/".$w->userid."/".$photo['id']."/\">";
          echo "<img src=\"";
          echo "http://farm";
          echo $photo['farm'];
          echo ".static.flickr.com/";
          echo $photo['server'];
          echo "/";
          echo $photo['id'];
          echo "_";
          echo $photo['secret'];
          echo $w->tailleimage;
          echo ".jpg";
          echo "\"";
          echo " alt=\"Photo Flickr\"";
          echo " title=\"";
          if ($photo['title'] != "")
          {
            echo $photo['title'];
            echo " ";
          }
      
          echo $photo['datetaken'];
          
          echo "\"";
          echo " ";
          echo $w->styleimg;
          echo " ";
          echo "/></a>\n";
          
          $i++;
          if(($i % $nbcols) == 0)
          {
            echo "<br />\n";
          }
        }
      }
      else
      {
        echo "Oups ! <br />";
      }
      
      echo "<br />\n".$w->text."\n";
      echo "</div>\n";
    }
    else
    {
      echo "<!-- affichage de rien volontaire ! -->\n";
    }
    echo "<!-- wflickr 1.2 by suricat - end \"".$w->title."\" -->\n\n";
    
		return "";
	}
}
?>