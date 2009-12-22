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

class newsletterPlugin
{
	
	/** ==================================================
	spécificité
	================================================== */

	/**
	* nom du plugin
	*/
	public static function pname()
	{ 
		return (string)'newsletter'; 
	}

	/**
	* supprime les paramètres
	*/
	public static function deleteSettings()
	{
		try {
			global $core;
			$blog = $core->blog;
	      	$settings = $blog->settings;			
			
			$settings->setNamespace('newsletter');
			
			$param = array('active', 
						'installed',
						'parameters',
						'errors',
						'messages'
						);
	
			// deleting settings
			foreach ($param as $v) {
				$settings->drop((string)self::prefix().$v);
			}
			unset($v);
			self::triggerBlog();
		
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}		
	}
    
	/** ==================================================
	gestion de base
	================================================== */

	/**
	* répertoire du plugin
	*/
	public static function folder() 
	{ 
		return (string)dirname(__FILE__).'/'; 
	}

	/**
	* adresse pour la partie d'administration
	*/
	public static function urlwidgets() 
	{ 
		return (string)'plugin.php?p=widgets'; 
	}

	/**
	* adresse pour la partie d'administration
	*/
	public static function urladmin() 
	{ 
		return (string)'index.php?'; 
	}

	/**
	* adresse pour la partie d'administration
	*/
	public static function urlplugin() 
	{ 
		return (string)'plugin.php'; 
	}

	/**
	* adresse du plugin pour la partie d'administration
	*/
	public static function adminLetter() 
	{ 
		return (string)self::urlplugin().'?p='; 
	}

	/**
	* adresse du plugin pour la partie d'administration
	*/
	public static function admin() 
	{ 
		return (string)self::adminLetter().self::pname(); 
	}

	/** ==================================================
	gestion des paramètres
	================================================== */

	/**
	* namespace pour le plugin
	*/
	protected static function namespace() 
	{ 
    		return (string)self::pname(); 
	}

	/**
	* préfix pour ce plugin
	*/
	protected static function prefix() 
	{ 
		return (string)self::namespace().'_'; 
	}

	/**
	* notifie le blog d'une mise à jour
	*/
	public static function triggerBlog()
	{
		global $core;
		try {
	   		$blog = &$core->blog;
			$blog->triggerBlog();
		} catch (Exception $e) { 
	    		$core->error->add($e->getMessage()); 
		}
	}

	/**
	* redirection http
	*/
	public static function redirect($url)
	{
		global $core;
		try {
			http::redirect($url);
      	} catch (Exception $e) { 
	   		$core->error->add($e->getMessage()); 
	   	}
	}

	/**
	* lit le paramètre
	*/
	public static function get($param, $global=false)
	{
		global $core;
      	try {
			$blog = &$core->blog;
	      	$settings = &$blog->settings;
         		if (!$global) {
         			$settings->setNamespace(self::namespace());
         		}
         		return (string)$settings->get(self::prefix().$param);
    		} catch (Exception $e) { 
    			$core->error->add($e->getMessage()); 
    		}
	}

	/**
	* test l'existence d'un paramètre
	*/
	public static function exist($param)
	{
		global $core;
      	try {
	     	$blog = &$core->blog;
	      	$settings = &$blog->settings;
         		if (isset($settings->$param)) 
         			return true;
			else 
				return false;
   		} catch (Exception $e) { 
   			$core->error->add($e->getMessage()); 
   		}
	}

	/**
	* enregistre une chaine dans le paramètre
	*/
	public static function setS($param, $val, $description)
	{
		global $core;
		try {
			$blog = &$core->blog;
			$settings = &$blog->settings;
			$settings->setNamespace(self::namespace());
			$settings->put((string)self::prefix().$param, (string)$val, 'string', (string)$description);
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
   }

	/**
	* enregistre un entier dans le paramètre
	*/
	public static function setI($param, $val, $description)
	{
		global $core;
		try {
			$blog = &$core->blog;
			$settings = &$blog->settings;
			$settings->setNamespace(self::namespace());
			$settings->put((string)self::prefix().$param, (integer)$val, 'integer', (string)$description);
		} catch (Exception $e) { 
			$core->error->add($e->getMessage());
		}
	}

	/**
	* enregistre un booléen dans le paramètre
	*/
	public static function setB($param, $val, $description)
	{
		global $core;
		try {
			$blog = &$core->blog;
			$settings = &$blog->settings;
			$settings->setNamespace(self::namespace());
			$settings->put((string) self::prefix().$param, (boolean)$val, 'boolean', (string)$description);
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* supprime le paramètre
	*/
	public static function delete($param)
	{
		global $core;
		try {
			$blog = &$core->blog;
			$settings = &$blog->settings;
			$settings->setNamespace(self::namespace());
			$settings->drop((string)self::prefix().$param);
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* supprime le paramètre
	*/
	public static function delete_version()
	{
		global $core;

		try {
			$blog = &$core->blog;
			$con = &$core->con;

			$strReq = 
				'DELETE FROM '.$core->prefix.'version '.
				'WHERE module = \''.newsletterPlugin::pname().'\';';

			$core->con->execute($strReq);

		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}


	/**
	* état d'installation du plugin
	*/
	public static function isInstalled() 
	{ 
		return (boolean)self::get('installed'); 
	}

	/**
	* positionne l'état d'installation du plugin
	*/
	public static function setInstalled($val) 
	{ 
		self::setB('installed', (boolean)$val, 'Installation state of the plugin'); 
	}

	/**
	* état d'activation du plugin
	*/
	public static function isActive() 
	{ 
		return (boolean)self::get('active'); 
	}

	/**
	* positionne l'état d'activation du plugin
	*/
	public static function setActive($val) 
	{ 
		self::setB('active', (boolean)$val, 'Enable plugin'); 
	}

	/**
	* active le plugin
	*/
	public static function activate() 
	{ 
		self::setActive(true); 
	}

	/**
	* désactive le plugin
	*/
	public static function inactivate() 
	{ 
		self::setActive(false); 
	}

	/** ==================================================
	récupération des informations de mise à jour
	================================================== */

	protected static $remotelines = null;

	/**
	* url de base pour les mises à jour
	*/
	//public static function baseUpdateUrl() { return html::escapeURL("http://phoenix.cybride.net/public/plugins/update/"); }
	public static function baseUpdateUrl() 
	{ 
		return html::escapeURL("http://"); 
	}

	/**
	* url pour le fichier de mise à jour
	*/
	public static function updateUrl() 
	{ 
		return html::escapeURL(self::baseUpdateUrl().self::pname().'.txt'); 
	}

	/**
	* retourne le nom du plugin
	*/
	public static function Name() 
	{ 
		return (string)self::tag('name'); 
	}

	/**
	* est-ce qu'on a le nom du plugin
	*/
	public static function hasName() 
	{ 
		return (bool)(self::pname() != null && strlen(self::pname()) > 0); 
	}

	/**
	* retourne la version du plugin
	*/
	public static function Version() 
	{ 
		return (string)self::tag('version'); 
	}

	/**
	* est-ce qu'on a la version du plugin
	*/
	public static function hasVersion() 
	{ 
		return (bool)(self::Version() != null && strlen(self::Version()) > 0); 
	}

	/**
	* retourne l'url du billet de publication du plugin
	*/
	public static function Post() 
	{ 
		return (string)self::tag('post'); 
	}

	/**
	* est-ce qu'on a l'url du billet de publication du plugin
	*/
	public static function hasPost() 
	{ 
		return (bool)(self::Post() != null && strlen(self::Post()) > 0); 
	}

	/**
	* retourne l'url du package d'installation du plugin
	*/
	public static function Package() 
	{ 
		return (string)self::tag('package'); 
	}

	/**
	* est-ce qu'on a l'url du package d'installation du plugin
	*/
	public static function hasPackage() 
	{ 
		return (bool)(self::Package() != null && strlen(self::Package()) > 0); 
	}

	/**
	* retourne l'url de l'archive du plugin
	*/
	public static function Archive() 
	{ 
		return (string)self::tag('archive'); 
	}

	/**
	* est-ce qu'on a l'url de l'archive du plugin
	*/
	public static function hasArchive() 
	{ 
		return (bool)(self::Archive() != null && strlen(self::Archive()) > 0); 
	}

	/**
	* est-ce qu'on a les informations lues depuis le fichier de mise à jour
	*/
	public static function hasDatas() 
	{ 
		return (bool)(self::$remotelines != null && is_array(self::$remotelines)); 
	}

	/**
	* renvoi une information parmis les lignes lues
	*/
	protected static function tag($tag)
	{
		global $core;
		try {
			if ($tag == null) 
				return null;
			else if (!self::hasDatas()) 
				return null;
	      	else if (!array_key_exists($tag, self::$remotelines)) 
				return null;
			else 
				return (string) self::$remotelines[$tag];
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/** ==================================================
	mises à jour
	================================================== */

	protected static $newversionavailable;

	/**
	* retourne l'indicateur de disponibilité de mise à jour
	*/
	public static function isNewVersionAvailable() 
	{ 
		return (boolean)self::$newversionavailable; 
	}

	/**
	* lecture d'une information particulière concernant un plugin (api dotclear 2)
	*/
	protected static function getInfo($info)
	{
		global $core;
		try {
			$plugins = $core->plugins;
			return $plugins->moduleInfo(self::pname(), $info);
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* racine des fichiers du plugin
	*/
	public static function dcRoot() 
	{ 
		return self::getInfo('root'); 
	}

	/**
	* nom du plugin
	*/
	public static function dcName() 
	{ 
		return self::getInfo('name'); 
	}

	/**
	* description du plugin
	*/
	public static function dcDesc() 
	{ 
		return self::getInfo('desc'); 
	}

	/**
	* auteur du plugin
	*/
	public static function dcAuthor() 
	{ 
		return self::getInfo('author'); 
	}

	/**
	* version du plugin
	*/
	public static function dcVersion() 
	{ 
		return self::getInfo('version'); 
	}

	/**
	* permissions du plugin
	*/
	public static function dcPermissions() 
	{ 
		return self::getInfo('permissions'); 
	}

	/**
	* comparaison des deux versions
	* retour <0 si old < new
	* retour >0 si old > new
	* retour =0 si old = new
	*/
	public static function compareVersion($oldv, $newv) 
	{ 
		return (integer)version_compare($oldv, $newv); 
	}

	/**
	* permet de savoir si la version de Dotclear installé une version finale
	* compatible Dotclear 2.0 beta 6 ou SVN
	*/
	public static function dbVersion()
	{
		global $core;
		try {
			return (string)$core->getVersion('core');
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

}

?>
