<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Newsletter, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Benoit de Marne.
# benoit.de.marne@gmail.com
# Many thanks to Association Dotclear and special thanks to Olivier Le Bris
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class newsletterTools
{
	/**
	* encodage en base64 pour une url
	*/
	public static function base64_url_encode($val)
	{
		return strtr(base64_encode($val), '+/=', '-_,');
	}

	/**
	* decodage en base64 pour une url
	*/
	public static function base64_url_decode($val)
	{
		return base64_decode(strtr($val, '-_,', '+/='));
	}

	/**
	* gÃ©nÃ¨re un code d'enregistrement
	*/
	public static function regcode() 
	{
		return md5(date('Y-m-d H:i:s', strtotime("now")) ); 
	}
	
	// use by : NewsletterFormRandom
	public static function getRandom()
	{
		list($usec, $sec) = explode(' ', microtime());
		$seed = (float) $sec + ((float) $usec * 100000);
		mt_srand($seed);
		return mt_rand();
	}	

	// surcharge de la fonction cutString pour avoir un extrait d'un texte
	public static function cutString($str,$maxlength=false)
	{
		if (mb_strlen($str) > $maxlength && $maxlength) {
			return text::cutString($str,$maxlength).'...';
		} else {			
			return $str;
		}
	}

	// redirection
	public static function redirection($module='subscribers',$msg='') 
	{
		$redir = 'plugin.php?p=newsletter';

		if (isset($_POST['redir']) && strpos($_POST['redir'],'://') === false)
		{
			$redir = $_POST['redir'];
		}
		else
		{
			$redir .= '&m='.$module.
			($_POST['sortby'] ? '&sortby='.$_POST['sortby'] : '' ).
			($_POST['order'] ? '&order='.$_POST['order'] : '' ).
			($_POST['page'] ? '&page='.$_POST['page'] : '' ).
			($_POST['nb'] ? '&nb='.(integer) $_POST['nb'] : '' ).
			'&msg='.rawurldecode($msg);
		}
		http::redirect($redir);	

	}
	
	// recherche si le template existe dans le theme
	public static function requestTemplate(dcCore $core, $filename) 
	{	
		if (file_exists(path::real($core->blog->themes_path.'/'.$core->blog->settings->theme).'/tpl/'.$filename))
			$folder = path::real($core->blog->themes_path.'/'.$core->blog->settings->theme).'/tpl/';
		else
			$folder =  path::real(newsletterPlugin::folder().'..').'/default-templates/';
		return $folder;
	}

	// recherche si le CSS existe dans le theme
	public static function requestPathFileCSS(dcCore $core, $filename) 
	{	
		# Settings compatibility test
		if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
			$blog_settings =& $core->blog->settings->newsletter;
			$system_settings = $core->blog->settings->system;
		} else {
			$blog_settings =& $core->blog->settings;
			$system_settings =& $core->blog->settings;
		}		
		
		if (file_exists(path::real($system_settings->themes_path.'/'.$blog_settings->theme).'/'.$filename))
			$folder = path::real($system_settings->themes_path.'/'.$blog_settings->theme);
		else
			$folder =  path::real(newsletterPlugin::folder().'..');
		return $folder;
	}
	
	/**
	 * Extrait les adresses e-mails présentes dans une chaine.
	 * La fonction retourne un tableau des adresses e-mails. Si
	 * des adresses e-mails se trouvent en doublon dans la chaine,
	 * alors la fonction ne gardera dans le tableau qu'un seul exemplaire
	 * des adresses e-mails.
	 *
	 * @author Hugo HAMON <webmaster@apprendre-php.com>
	 * @licence LGPL
	 * @param string $sChaine la chaine contenant les e-mails
	 * @return array $aEmails[0] Tableau dédoublonné des e-mails
	 */	
	public static function extractEmailsFromString($sChaine) {
	 
		if(false !== preg_match_all('`\w(?:[-_.]?\w)*@\w(?:[-_.]?\w)*\.(?:[a-z]{2,4})`', $sChaine, $aEmails)) {
			if(is_array($aEmails[0]) && sizeof($aEmails[0])>0) {
				return array_unique($aEmails[0]);
			}
		}
		return null;
	}	

}

?>