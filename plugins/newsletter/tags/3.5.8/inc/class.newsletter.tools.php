<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Newsletter, a plugin for Dotclear.
# 
# Copyright (c) 2009 Benoit de Marne
# benoit.de.marne@gmail.com
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
	* génère un code d'enregistrement
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
	
}

?>
