<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2007 Olivier Meunier & Oleksandr Syenchuk and contributors.
# All rights reserved.
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
# This file is part of dcFlickr, which inserts Flickr photos in a blog note
# Charles Delorme http://www.suricat.net/
# 
@error_reporting(E_ALL);
#@error_reporting(E_ALL | E_STRICT);

# Apparement, c'est le premier truc a faire
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

try
{
  # Mise en place de la table de parametres si elle n'existe pas
  # A priori, premiere utilisation et donc pas d'autres parametres dans $_POST
  if(!$core->blog->settings->dcflickrSettingApiKey)
  {  
    #settings->put : 
    # nom du parametre
    # valeur
    # type : 'boolean', 'string', 'integer'
    # description (label)
    # $value_change=true : permettre de changer la valeur
    # $global=false      : parametre global a tous les blogs
    
    $core->blog->settings->setNameSpace('dcflickr');
    
		$core->blog->settings->put('dcflickrSettingApiKey',__('<Enter your Flickr API key>'), 'string','La clef d\'API Flickr');
		
		$core->blog->settings->put('dcflickrSettingDefautImg','Square', 'string','La taille dans le billet');
		$core->blog->settings->put('dcflickrSettingDefautHref','Large', 'string','La taille dans le lien');
		$core->blog->settings->put('dcflickrSettingDefautAlign','none', 'string','Alignement de la photo dans le billet');
		$core->blog->settings->put('dcflickrSettingFastInsert',0, 'boolean','Insertion rapide');
		
		$core->blog->settings->put('dcflickrSettingNbPreview',12, 'integer','Le nombre d\'images en preview');
		$core->blog->settings->put('dcflickrSettingUserIdPreview','21108291@N06', 'string','L\'utilisateur Flickr pour la preview');
		
		$core->blog->settings->put('dcflickrSettingNbImg',9, 'integer','Le nombre d\'images en mosaique');
		$core->blog->settings->put('dcflickrSettingNbCol',3, 'integer','Le nombre de colonnes en mosaique');
    
    # La creation des parametres est faite, recharge la page du plugin
    http::redirect($p_url); 
  }

  # Si on a dcflickrSettingApiKey dans $_POST, c'est que l'on est 
  # en train de modifier les parametres
  if (isset($_POST['dcflickrSettingApiKey']))
  {
    #Validation des parametres
    $core->blog->settings->setNameSpace('dcflickr');

		if($_POST['dcflickrSettingApiKey'] == __('<Enter your Flickr API key>'))
		{
		  http::redirect($p_url.'&up=ko');
		}
		$core->blog->settings->put('dcflickrSettingApiKey',$_POST['dcflickrSettingApiKey'], null,null);

    if(isset($_POST['dcflickrSettingDefautImg']))
    {
		  $core->blog->settings->put('dcflickrSettingDefautImg',$_POST['dcflickrSettingDefautImg'], null,null);
		}
		if(isset($_POST['dcflickrSettingDefautHref']))
		{
		  $core->blog->settings->put('dcflickrSettingDefautHref',$_POST['dcflickrSettingDefautHref'], null,null);
		}
		if(isset($_POST['dcflickrSettingDefautAlign']))
		{
  		$core->blog->settings->put('dcflickrSettingDefautAlign',$_POST['dcflickrSettingDefautAlign'], null,null);
  	}
  	
  	if(isset($_POST['dcflickrSettingFastInsert']))
  	{
      $core->blog->settings->put('dcflickrSettingFastInsert',$_POST['dcflickrSettingFastInsert'], 'boolean','Insertion rapide');
    }
    
  	if(isset($_POST['dcflickrSettingNbPreview']))
  	{
  		$core->blog->settings->put('dcflickrSettingNbPreview',$_POST['dcflickrSettingNbPreview'], null,null);
  	}
  	if(isset($_POST['dcflickrSettingUserIdPreview']))
  	{
		  $core->blog->settings->put('dcflickrSettingUserIdPreview',$_POST['dcflickrSettingUserIdPreview'], null,null);
		}
		
		#TODO
		#$core->blog->settings->put('dcflickrSettingNbImg',9, null,null);
    #$core->blog->settings->put('dcflickrSettingNbCol',3, null,null);

    # La modification des parametres est faite, recharge la page du plugin
    
    http::redirect($p_url.'&up=ok');
  }
  
}//try
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}    

$dcflickrMsg="";
$dcflickrIsValidUrl=0;
$dcflickrApiKey=$core->blog->settings->dcflickrSettingApiKey;

$dcflickrUrl = !empty($_POST['dcflickrUrl']) ? $_POST['dcflickrUrl'] : null;

# S'il y a une URL, on va chercher les informations de la photo pour l'insertion
if ($dcflickrUrl)
{
	try
	{
    // Validation de l'URL
    $string=explode('/',$dcflickrUrl);
    $dcflickrPhoto_id=$string[5];
        
    // Connexion a phpFlickr
    require_once("phpFlickr-2.2.0/phpFlickr.php");
    $f = new phpFlickr($dcflickrApiKey);

    // Recuperation des informations d'une photo    
    $dcflickrInfos=$f->photos_getInfo($dcflickrPhoto_id);
    if($dcflickrInfos == false)
    {
      $dcflickrMsg = __('Invalid page URL');
    }
    else
    {
      $dcflickrIsValidUrl=1;
      
      $dcflickrSizes=$f->photos_getSizes($dcflickrPhoto_id);
    }
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
?>
<html>
<head>
  <title><?php echo __('dcFlickr selector') ?></title>
  <script type="text/javascript" src="index.php?pf=dcFlickr/popup.js"></script>
</head>

<body>

<?php

# Alignement possible des photos. Utiles dans l'affichage et dans la configuration  
$dcflickrAlignArray = array(
	'none' => __('None'),
	'left' => __('Left'),
	'right' => __('Right'),
	'center' => __('Center')
);


# Est-on en en train de valider une url ou de choisir une image ?
# popup est mis en place par le lien dans post.js
# dcflickrUrl est mis en place par le premier passage dans le formulaire
if (!empty($_GET['popup']) || $dcflickrUrl)
{
  # On est dans la partie execution du plugin
  echo '<h2>'.__('dcFlickr selector').'</h2>';
  echo "\n";
  
  if (!$dcflickrIsValidUrl)
  {
    # L'URL n'a pas encore ete choisie ou alors une erreur est survenue
    # Affichage de la preview et du choix de l'url
    if($dcflickrMsg != "")
    {
       echo '<div class="error">'.$dcflickrMsg.'</div>';
    }
   
    // Affichage d'une mosaique en preview pour faciliter le choix des photos
    // Controle de la valeur du parametre pour eviter les abus/erreurs
    // (a mettre aussi/plutot lors de la modification ?)    
    if ($core->blog->settings->dcflickrSettingNbPreview < 0)
    {
      $dcflickrNbPreview = 0;
    }
    elseif ($core->blog->settings->dcflickrSettingNbPreview > 18)
    {
      $dcflickrNbPreview = 18;
    }
    else
    {
      $dcflickrNbPreview = $core->blog->settings->dcflickrSettingNbPreview;
    }

    $i=0;
    if($dcflickrNbPreview != 0)
    {
      if($core->blog->settings->dcflickrSettingUserIdPreview != "")
      {
        # Affichage du tableau de preview
        try
	      {
          // Connexion a phpFlickr
          require_once("phpFlickr-2.2.0/phpFlickr.php");
          $f = new phpFlickr($dcflickrApiKey);
          
          $photos = $f->people_getPublicPhotos($core->blog->settings->dcflickrSettingUserIdPreview,'date_taken',$dcflickrNbPreview);
          if ($photos != FALSE)
          {
            $nbcols = 6;
            foreach ($photos['photo'] as $photo) 
            {
              echo "<img src=\"";
              echo "http://farm";
              echo $photo['farm'];
              echo ".static.flickr.com/";
              echo $photo['server'];
              echo "/";
              echo $photo['id'];
              echo "_";
              echo $photo['secret'];
              echo "_s";
              echo ".jpg";
              echo "\"";
              echo " onclick=\"document.getElementById('dcflickrUrl').value='";
              echo "http://www.flickr.com/photos/".$core->blog->settings->dcflickrSettingUserIdPreview."/".$photo['id']."/";
              echo "'\" ";
              echo " style=\"cursor: pointer; cursor: hand;\" alt=\"\" ";
              echo "/>\n";
              
              $i++;
              if(($i % $nbcols) == 0)
              {
                echo "<br />\n";
              }
            }
          }
          else
          {
            echo '<div class="error">';
            echo sprintf (__('Ooops ! A problem occured with preview. <br /> Check API Key (%s) and User Id (%s)'),$dcflickrApiKey,$core->blog->settings->dcflickrSettingUserIdPreview);
            echo '</div>';
          }      
	      }
	      catch (Exception $e)
	      {
	      	$core->error->add($e->getMessage());
	      }    
	    }
   
      # Lien vers la page de l'utilisateur
      echo "<br /><a href=\"http://www.flickr.com/photos/".$core->blog->settings->dcflickrSettingUserIdPreview."/\" onclick=\"window.open(this.href); return false;\">";
      echo __('Go to user\'s Flickr photos page (new window)');
      echo "</a><br />\n";
    }
   
  	echo'<form action="'.$p_url.'&amp;popup=1" method="post"><p><br />';
  	if($dcflickrNbPreview != 0 && $i != 0)
  	{
  	  echo __('Click on one image or enter the URL of the page containing the photo you want to include in your post.');
  	}
  	else
  	{
  	  echo __('Please enter the URL of the page containing the photo you want to include in your post.');
  	}
  	echo '</p><p><label>'.__('Page URL:').' '.
  	form::field('dcflickrUrl',80,250,html::escapeHTML($dcflickrUrl)).'</label></p>'.  	  	
  	'<p><input type="submit" value="'.__('ok').'" />'.
  	'  <label class="classic"> <input type="checkbox" name="dcflickrFastInsert"' ;
  	
  	if($core->blog->settings->dcflickrSettingFastInsert == 1)
  	{
  	  echo ' checked="checked" ';
  	}
  	echo ' value="1" /> '.__('Fast insert').' </label>';
  	echo (is_callable(array($core,'formNonce')) ? $core->formNonce() : '').'</p>'.
  	'</form>';
  }
  else
  {  
    # Traitement de l'url en parametre pour l'insertion dans le billet
    # On renvoit les parametres suivants (traité dans popup.js puis post.js)
		#   dcflickrTitle
 		#   dcflickrAlignment
		#   dcflickrImg
		#   dcflickrHref
		#   dcflickrPhotopage
 
    # Determine le lien vers la page photo
    $dcflickrPhotopage = "";
    foreach ($dcflickrInfos['urls']['url'] as $url)
    {
      if($url['type'] == "photopage")
      {
        $dcflickrPhotopage = $url['_content'];
      }
    }

    # Test le mode d'insertion
    if(! isset($_POST['dcflickrFastInsert']))
    {
      #Insertion detaillee
      echo '<h3>'.__('Photo found at Flickr').'</h3>';
      echo "<p>";
      
      #Affichage de l'image avec lien vers la photo
      echo "<img src=\"http://farm".$dcflickrInfos['farm'].".static.flickr.com/".$dcflickrInfos['server']."/".$dcflickrInfos['id']."_".$dcflickrInfos['secret']."_t.jpg\" alt=\"\" />";
      echo "<br />";
      if($dcflickrPhotopage != "")
      {
        echo "<a href=\"$dcflickrPhotopage\" onclick=\"window.open(this.href); return false;\">";
        echo __("Open this photo's Flickr page (new window)");
        echo "</a>";
      }
      echo "</p>\n";
      
      #Formulaire d'insertion
  	  echo '<form id="media-insert-form" action="" method="get">';
  	  
  	  echo
  	  '<h3>'.__('Photo title').'</h3>'.
  	  '<p><label>'.
  	  form::field(array('dcflickrTitle'),50,250,html::escapeHTML($dcflickrInfos['title'])).'</label></p>';
      
      echo '<h3>'.__('Please choose the photo you want to include in your post and to which it should be linked').'</h3>';
      echo "<table border=\"1\">\n";  
      echo '<tr><th colspan ="2">';
      echo __('Taille');
      echo "</th>";
      echo '<th>'.__('Post').'</th><th>'.__('Link').'</th>';
      echo "</tr>\n";
      foreach ($dcflickrSizes as $size)
      {
        echo "<tr><td>";
        echo __($size['label']);
        echo "</td><td>";
        echo $size['width']."x".$size['height'];
        echo "</td>";
      
        echo '<td><label class="classic"><input type="radio" name="dcflickrImg" value="'.$size['source'].'"';
        if ($size['label'] == $core->blog->settings->dcflickrSettingDefautImg)
        {
          echo ' checked="checked" ';
        }      
        echo ' /></label></td>';
      
        echo '<td><label class="classic"><input type="radio" name="dcflickrHref" value="'.$size['source'].'"';
        if ($size['label'] == $core->blog->settings->dcflickrSettingDefautHref)
        {
          echo ' checked="checked" ';
        }      
        echo ' /></label></td>';
        echo "</tr>\n";
      }
      echo "</table>\n";
  	  
  	  echo '<h3>'.__('Photo alignment').'</h3>';
  	  echo '<p>';
  	  foreach ($dcflickrAlignArray as $k => $v) {
  	  	echo '<label class="classic">';
  	  	if($k == $core->blog->settings->dcflickrSettingDefautAlign)
  	  	{
  	  	  $checked=1;
  	  	}
  	  	else
  	  	{
  	  	  $checked=0;
  	  	}  		
  	  	echo form::radio(array('dcflickrAlignment'),$k,$checked).' '.$v.'</label><br /> ';
  	  }
  	  echo '</p>';
  	  	
  	  echo
  	  '<p><a id="media-insert-cancel" href="#">'.__('Cancel').'</a> - '.
  	  '<strong><a id="media-insert-ok" href="#">'.__('Insert').'</a></strong>'.
      form::hidden(array('dcflickrPhotopage'),html::escapeHTML($dcflickrPhotopage)).
  	  '</p></form>';
  	}
  	else
  	{
  	  #Insertion rapide
  	  
  	  echo '<h3>'.__('Fast Insert').'</h3>';
  	  
  	  # On masque le formulaire 
  	  echo '<div style="display: none;">';
  	  echo '<form id="media-insert-form" action="" method="get">';
      echo form::hidden(array('dcflickrTitle'),html::escapeHTML($dcflickrInfos['title']));
      echo form::radio(array('dcflickrAlignment'),$core->blog->settings->dcflickrSettingDefautAlign,1);

      # Verifie que les tailles par defaut sont disponibles pour la photo choisie
      $dcflickrImgFound=0;
      $dcflickrHrefFound=0;
      foreach ($dcflickrSizes as $size)
      {
        if ($size['label'] == $core->blog->settings->dcflickrSettingDefautImg)
        {
          echo form::radio(array('dcflickrImg'),$size['source'],1);
          $dcflickrImgFound=1;
        }      
        if ($size['label'] == $core->blog->settings->dcflickrSettingDefautHref)
        {
          echo form::radio(array('dcflickrHref'),$size['source'],1);
          $dcflickrHrefFound=1;
        }      
      }
      
      echo form::hidden(array('dcflickrPhotopage'),html::escapeHTML($dcflickrPhotopage));
      echo '<a id="media-insert-ok" href="#">'.__('Insert').'</a>';
      if($dcflickrImgFound && $dcflickrHrefFound)
      {
        # Si ce champs est present, la photo sera postee dans popup.js
        echo form::hidden(array('dcflickrFastInsert'),"true");
      }
  	  echo '</form>';
      echo '</div>';
      
      echo "\n";
      
      # Erreur dans les tailles par defaut
      if(!$dcflickrImgFound)
      {
        echo '<div class="error">';
        echo __('Post image default size not found. Fast insert impossible');
        echo '</div>';
      }
      if(!$dcflickrHrefFound)
      {
        echo '<div class="error">';
        echo __('Link image default size not found. Fast insert impossible');
        echo '</div>';
      }            
      if(! ($dcflickrImgFound && $dcflickrHrefFound) )
      {
        echo '<a href="javascript:history.back()">'.__('Go back').'</a>';
      }
    }
  }
}
else
{
  # on est dans la partie administration
  echo '<h2>'.html::escapeHTML($core->blog->name).' &gt; dcFlickr</h2>';
  echo "\n";

  #insertion du script pour la gestion du multi-part
  #(un peu crado non un <script> au mileu d'une page ?)
  echo dcPage::jsPageTabs();
  
  # tab Configuration
  echo '<div id="dcflickrSetup" class="multi-part" title="'.__('Setup').'">';
  
  # On revient a cette page apres une mise a jour.
  if (isset($_GET['up'])) 
  {
    if ($_GET['up'] == "ok")
    {
  	  echo '<div class="message">'.__('Settings have been successfully updated').'</div>';
  	}
  	else
    {
  	  echo '<div class="error">'.__('You must enter a valid Flickr API key').'</div>';
  	}
  	echo '<br />';
  }
  echo '<form action="'.$p_url.'" method="post">';

  #API
  echo '<fieldset><legend>'.__('Main').'</legend>';
  echo '<p>'.__("Flickr API key is mandatory. You need a Flickr (or Yahoo) account and request a key <a href=\"http://www.flickr.com/services/api/key.gne\" onclick=\"window.open(this.href); return false;\">here</a>.").'</p>';
  echo '<p>';
  echo '<label class="classic">'.__('Flickr API key:').' '.
        form::field(array('dcflickrSettingApiKey'),50,32,html::escapeHTML($core->blog->settings->dcflickrSettingApiKey)).'</label>'.
        '<br />';
  echo '</p>';
  echo '</fieldset>';
       
  #Taille et position
  echo '<fieldset><legend>'.__('Default choices').'</legend>';
  
  echo '<h3>'.__('Photos size in posts and links').'</h3>';
  echo '<p>'.__("Not all sizes are available, depending of the size of the original photo. The size given in pixel in the table bellow is the largest side of the photo.<br />If a size is not available, \"Fast Insert\" option will generate an error message.").'</p>';

  $dcflickrSizeArray = array(
  	'Square'    => array(__('Square'),'75px'),
  	'Thumbnail' => array(__('Thumbnail'),'100px'),
  	'Small'     => array(__('Small'),'240px'),
  	'Medium'    => array(__('Medium'),'500px'),
  	'Large'     => array(__('Large'),'1024px'),
  	'Original'  => array(__('Original'),__('Original size'))
  );


  echo "<table border=\"1\">\n";  
  echo '<tr><th colspan ="2">';
  echo __('Taille');
  echo "</th>";
  echo '<th>'.__('Post').'</th><th>'.__('Link').'</th>';
  echo "</tr>\n";
  
  foreach ($dcflickrSizeArray as $k => $v)
  {
    echo "<tr><td>";
    echo $v[0];
    echo "</td><td>";
    echo $v[1];
    echo "</td>";

    echo '<td><label class="classic"><input type="radio" name="dcflickrSettingDefautImg" value="'.$k.'"';
    if ($k == $core->blog->settings->dcflickrSettingDefautImg)
    {
      echo ' checked="checked" ';
    }      
    echo ' /></label></td>';

    echo '<td><label class="classic"><input type="radio" name="dcflickrSettingDefautHref" value="'.$k.'"';
    if ($k == $core->blog->settings->dcflickrSettingDefautHref)
    {
      echo ' checked="checked" ';
    }      
    echo ' /></label></td>';
    echo "</tr>\n";
  
  }
  echo "</table>\n";
  echo "\n";
  
  echo '<h3>'.__('Photo alignment').'</h3>';
  echo '<p>';
  	foreach ($dcflickrAlignArray as $k => $v) {
  		echo '<label class="classic">';
  		if($k == $core->blog->settings->dcflickrSettingDefautAlign)
  		{
  		  $checked=1;
  		}
  		else
  		{
  		  $checked=0;
  		}  		
  		echo form::radio(array('dcflickrSettingDefautAlign'),$k,$checked).' '.$v.'</label><br /> ';
  	}
  echo '</p>';
  
  echo '<h3>'.__('Fast insert').'</h3>';
  echo '<p>';
  echo '<label class="classic">';
  echo '<select name="dcflickrSettingFastInsert">';
  echo '<option value="1" ';
  if($core->blog->settings->dcflickrSettingFastInsert == 1)
  {
    echo 'selected="selected" ';
  }
  echo '>'.__('yes').'</option>';
  echo '<option value="0" ';
  if($core->blog->settings->dcflickrSettingFastInsert == 0)
  {
    echo 'selected="selected" ';
  }  
  echo '>'.__('no').'</option>';
  echo '</select></label>';
  echo ' '.__('If this option is active, all default values will be used when inserting an image, without asking.');
  echo '</p>';
  echo '</fieldset>';

  #Preview
  echo '<fieldset><legend>'.__('Preview').'</legend>';
  echo '<label class="classic" for="dcflickrSettingNbPreview" style="display: block; float:left; width:200px;">'.__('Images quantity').'</label>'.
        form::field('dcflickrSettingNbPreview',20,20,html::escapeHTML($core->blog->settings->dcflickrSettingNbPreview)).
        ' <p>'.__('18 photos max - Enter 0 to disable preview').'</p>';

  echo '<label class="classic" for="dcflickrSettingUserIdPreview" style="display: block; float:left; width:200px;">'.__('Flickr User ID').'</label>'.
        form::field('dcflickrSettingUserIdPreview',20,20,html::escapeHTML($core->blog->settings->dcflickrSettingUserIdPreview));

  echo '<p>';
  echo __("Flickr User ID has to be the numeric form (eg: 21108291@N06). To find it, copy the URL of any photo and submit it at <a href=\"http://idgettr.com/\">idgettr.com</a>.");
  echo '</p>';
  echo '</fieldset>';

		
	#Pour la mosaique
	#$core->blog->settings->put('dcflickrSettingNbImg',9, null,null);
  #$core->blog->settings->put('dcflickrSettingNbCol',3, null,null);


  echo '<p><input type="submit" value="'.__('save').'" />'.
  $core->formNonce().'</p>';  
  echo '</form>';
  
  echo "</div>\n";

  # Tab A propos  
  echo '<div id="dcflickrAbout" class="multi-part" title="'.__('About').'">';

  echo '<img src="index.php?pf=dcFlickr/dcflickr-logo.png" alt="dcFlickr logo" style="float:right; margin: 0 1em 1em 0;" />';
  echo '<fieldset>'.
       '<legend>'.__('Author').'</legend>'.
       '<p>'.
       __("dcFlickr has been written by <a href=\"http://www.suricat.net/\" onclick=\"window.open(this.href); return false;\">Suricat</a>.").
       '</p><p>'.
       __("If you need help (even in english), leave a comment on <a href=\"http://www.suricat.net/web/index.php/2008/04/22/458-dcflickr-vos-photos-flickr-dans-dotclear-2\" onclick=\"window.open(this.href); return false;\">dcFlickr support page</a>").
       '</p></fieldset>';

  echo '<fieldset>'.
       '<legend>'.__('Thanks').'</legend>'.
       '<ul>'.
       '<li>'.__("<a href=\"http://www.standblog.com/\" onclick=\"window.open(this.href); return false;\">Tristan Nitot (Standblog)</a> who asked for this plugin").'</li>'.
       '<li>'.__("<a href=\"http://www.sous-anneau.org/\" onclick=\"window.open(this.href); return false;\">Alain Vagner</a> for pointing me to the \"External Media\" plugin and for his javascript help").'</li>'.
       '<li>'.__("Dan Coulter for his <a href=\"http://phpflickr.com/\" onclick=\"window.open(this.href); return false;\">phpFlickr</a> library which connects to Flickr").'</li>'.
       '<li>'.__("Olivier Meunier for <a href=\"http://www.dotclear.net/\" onclick=\"window.open(this.href); return false;\">Dotclear</a> and the \"External Media\" plugin, the dcTeam and <a href=\"http://www.dotaddict.org/\" onclick=\"window.open(this.href); return false;\">Dotaddict</a> for all this work").'</li>'.
       '</ul>'.
       '</fieldset>';
  
  echo "</div>\n";

}

?>
</body>
</html>
