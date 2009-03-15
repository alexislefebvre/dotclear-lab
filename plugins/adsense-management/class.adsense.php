<?php
# ***** BEGIN LICENSE BLOCK *****
# Widget Management for DotClear.
# Copyright (c) 2008 Gerits Aurelien. All rights
# reserved.
#

# Cette création est mise à disposition selon le Contrat Paternité-Pas 
# d'Utilisation Commerciale-Pas de Modification 2.0 Belgique disponible 
# en ligne http://creativecommons.org/licenses/by-nc-nd/2.0/be/ ou par 
# courrier postal à Creative Commons, 171 Second Street, Suite 300, San Francisco, 
# California 94105, USA.

class plugins{
	
	public static function getCacheFile()
	{
		$dest_file = DC_TPL_CACHE.'/cbtpl';
		//return $dest_file;
		return(file_exists($dest_file) ? $dest_file : null);
	}

}
#class pluginsAds for deletecache
class pluginsAds extends dcUrlHandlers{
	
	public static function deleteCache()
	{
		$dest_file = plugins::getCacheFile();

		if ($dest_file === null) {return(__('Cache file does not exist or with already be removing'));}

		files::deltree($dest_file);

		return((bool)true);
	}
}
class rewriteInputColor{
	
	public static function rewrite($label)  {  
   
	      $search = array ('@[éèêëÊË]@i','@[àâäÂÄ]@i','@[îïÎÏ]@i','@[ûùüÛÜ]@i','@[ôöÔÖ]@i',  
	        '@[ç]@i','@[^a-zA-Z0-9]@');  
	      $replace = array ('e','a','i','u','o','c',' ');  
	      $label =  preg_replace($search, $replace, $label);  
	      //$label = strtolower($label); // mais toutes les lettres de la chaîne en minuscule  
	      $label = str_replace(" ",'',$label); // remplace les espaces en tirets  
	      $label = preg_replace('#\-+#','',$label); // enlève les autres caractères inutiles  
	      $label = preg_replace('#([-]+)#','',$label);  
	      trim($label,''); // remplace les espaces restants par des tirets  
    
    	return $label  ;
	}
}
?>