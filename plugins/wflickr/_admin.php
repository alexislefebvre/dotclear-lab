<?php

// Name 			  'wflickr',
// Description	'Affichage d\'images depuis Flickr dans un widget',
// Author 			'Charles Delorme http://www.suricat.net/',
//              http://www.suricat.net/web/index.php/2008/01/24/423-wflickr-widget-dotclear-pour-vos-photos-flickr
// Version 			'1.2',
// License      'CC-BY-SA' http://creativecommons.org/licenses/by-sa/2.0/fr/


$core->addBehavior('initWidgets',	array('myWidgetBehaviors','initWidgets'));
 
class myWidgetBehaviors
{
	public static function initWidgets(&$w)
	{
		$w->create('MyWidget',__('wFlickr'), array('publicMyWidget','myWidget'));
 
		$w->MyWidget->setting('title',__('Title:'), 'Photos de Suricat','text');
		
		$w->MyWidget->setting('apikey',__('Flickr API key :'),'<Saisir votre cle API Flickr>','text');
 
		
		$w->MyWidget->setting('afficher',__('Afficher :'), null,'combo',array('toutes les photos de l\'utilisateur' => 1, 'seulement les photos d\'un album' => 2,'seulement les photos d\'un groupe' => 3,'rien' => 0));

		$w->MyWidget->setting('userid',__('Flickr User ID :'),'21108291@N06','text');
		$w->MyWidget->setting('albumid',__('Id de l\'album :'),'72157603248521610','text');
		$w->MyWidget->setting('groupid',__('Id du group :'),'19621373@N00','text');

		$w->MyWidget->setting('stylediv',__('Style du bloc :'),'class="wFlickrDiv"','text');
		$w->MyWidget->setting('styleimg',__('Style des images :'),'class="wFlickrImg"','text');
 
		//$w->MyWidget->setting('nbphotos',__('Nombre de photos :'),
 		//	null,'combo',array('5' => 5, '6' => 6, '10' => 10, '14' => 14,'15' => 15, '20' => 20)); 

		//$w->MyWidget->setting('nbcols',__('Nombre de colonnes :'),
 		//	null,'combo',array('1' => 1, '2' => 2, '3' => 3)); 

		$w->MyWidget->setting('nbphotos',__('Nombre de photos :'),'5','text');

		$w->MyWidget->setting('nbcols',__('Nombre de colonnes:'),'1','text');

		$w->MyWidget->setting('tailleimage',__('Taille des images :'),null,'combo',array('carr&eacute;' => '_s', 'miniature' => '_t')); 
 			
		$w->MyWidget->setting('text',__('Texte sous les photos :'),
			'<a href="http://www.flickr.com/photos/21108291@N06/">Toutes les photos de Suricat sur son album Flickr</a>','textarea');

		$w->MyWidget->setting('checked',
		__('Retrouvez toutes les informations sur <a href="//              http://www.suricat.net/web/index.php/2008/01/24/423-wflickr-widget-dotclear-pour-vos-photos-flickr">suricat.net</a>'),true,'check');
						
	}
}
?>