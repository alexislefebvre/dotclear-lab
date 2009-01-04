<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
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

class pluginNewsletter
{
	/** ==================================================
	spécificité
	================================================== */

	/**
	* nom du plugin
	*/
	static public function pname() { return (string)"newsletter"; }

	/**
	* renvoi le nom de l'éditeur du blog
	*/
    static public function getEditorName() { return (string)self::get('editorName'); }
	
    /**
	* renseigne le nom de l'éditeur
	*/
	static public function setEditorName($val) { self::setS('editorName', (string)$val); }
	
    /**
	* efface/initialise le nom de l'éditeur
	*/
	static public function clearEditorName() { self::setEditorName(''); }
	
	/**
	* renvoi l'email de l'éditeur du blog
	*/
    static public function getEditorEmail() { return (string)self::get('editorEmail'); }
	
    /**
	* renseigne l'email de l'éditeur
	*/
	static public function setEditorEmail($val) { self::setS('editorEmail', (string)$val); }
	
    /**
	* efface/initialise l'email de l'éditeur
	*/
	static public function clearEditorEmail() { self::setEditorEmail(''); }

	/**
	* renvoi le mode d'envoi de la newsletter
	*/
    static public function getSendMode() { return (string)self::get('mode'); }
	
    /**
	* renseigne le mode d'envoi de la newsletter
	*/
	static public function setSendMode($val) { self::setS('mode', (string)$val); }
	
    /**
	* efface/initialise le mode d'envoi de la newsletter
	*/
	static public function clearSendMode() { self::setSendMode('text'); }
	
	/**
	* nombre maximal de billet retournés
	*/
    static public function getMaxPosts() { return (integer)self::get('maxposts'); }
	
    /**
	* renseigne le nombre maximal de billet retournés
	*/
	static public function setMaxPosts($val) { self::setI('maxposts', (integer)$val); }
	
    /**
	* efface/initialise le nombre maximal de billet retournés
	*/
	static public function clearMaxPosts() { self::setSendMode(0); }	
	
	/**
	* envoi automatique
	*/
    static public function getAutosend() { return (boolean)self::get('autosend'); }
	
    /**
	* indique si on doit envoyer automatiquement
	*/
	static public function setAutosend($val) { self::setB('autosend', (boolean)$val); }
	
    /**
	* réinitialise l'indicateur d'envoi automatique
	*/
	static public function clearAutosend() { self::setAutosend(false); }
		
	/**
	* utilisation d'un captcha
	*/
    static public function getCaptcha() { return (boolean)self::get('captcha'); }
	
    /**
	* indique si on doit utiliser un captcha
	*/
	static public function setCaptcha($val) { self::setB('captcha', (boolean)$val); }
	
    /**
	* réinitialise l'indicateur d'utilisation de captcha
	*/
	static public function clearCaptcha() { self::setCaptcha(false); }
	
	/**
	* initialise les paramètres par défaut
	*/
	static public function defaultsSettings()
	{
		self::Install();
		self::Inactivate();

		self::clearEditorName();
		self::clearEditorEmail();
		self::setSendMode('text');
		self::setMaxPosts(7);
		self::setAutosend(false);
		self::setCaptcha(false);

		self::Trigger();
	}

	/**
	* supprime les paramètres
	*/
	static public function deleteSettings()
	{
		self::delete('active');
		self::delete('installed');

		self::delete('editorName');
		self::delete('editorEmail');
		self::delete('mode');
		self::delete('maxposts');
		self::delete('autosend');
		self::delete('captcha');

		self::Trigger();
	}
    
	/** ==================================================
	gestion de base
	================================================== */

	/**
	* répertoire du plugin
	*/
	static public function folder() { return (string)dirname(__FILE__).'/'; }

	/**
	* adresse pour la partie d'administration
	*/
	static public function urlwidgets() { return (string)'plugin.php?p=widgets'; }

	/**
	* adresse pour la partie d'administration
	*/
	static public function urladmin() { return (string)'index.php?'; }

	/**
	* adresse pour la partie d'administration
	*/
	static public function urlplugin() { return (string)'plugin.php'; }

	/**
	* adresse pour la partie d'administration
	*/
	static public function urldatas() { return (string)'index.php?pf='.self::pname(); }

	/**
	* adresse du plugin pour la partie d'administration
	*/
	static public function adminCitations() { return (string)self::urlplugin().'?p='; }

	/**
	* adresse du plugin pour la partie d'administration
	*/
	static public function admin() { return (string)self::adminCitations().self::pname(); }

	/** ==================================================
	gestion des paramètres
	================================================== */

	/**
	* namespace pour le plugin
	*/
    static protected function namespace() { return (string)self::pname(); }

	/**
	* préfix pour ce plugin
	*/
    static protected function prefix() { return (string)self::namespace().'_'; }

	/**
	* notifie le blog d'une mise à jour
	*/
	static public function Trigger()
	{
	    global $core;
        try
        {
	        $blog = &$core->blog;
			$blog->triggerBlog();
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}

	/**
	* redirection http
	*/
	static public function redirect($url)
	{
		global $core;
        try
        {
			http::redirect($url);
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}

	/**
	* lit le paramètre
	*/
    static public function get($param, $global=false)
    {
		global $core;
        try
        {
	        $blog = &$core->blog;
	        $settings = &$blog->settings;
            if (!$global) $settings->setNamespace(self::namespace());
            return (string)$settings->get(self::prefix().$param);
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
    }

	/**
	* test l'éxistence d'un paramètre
	*/
	static public function exist($param)
	{
		global $core;
        try
        {
	        $blog = &$core->blog;
	        $settings = &$blog->settings;
            if (isset($settings->$param)) return true;
			else return false;
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}

	/**
	* enregistre une chaine dans le paramètre
	*/
    static public function setS($param, $val, $global=false)
    {
		global $core;
        try
        {
	        $blog = &$core->blog;
	        $settings = &$blog->settings;
            $settings->setNamespace(self::namespace());
            $settings->put((string)self::prefix().$param, (string)$val, 'string', null, true, $global);
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
    }

	/**
	* enregistre un entier dans le paramètre
	*/
    static public function setI($param, $val, $global=false)
    {
		global $core;
        try
        {
	        $blog = &$core->blog;
	        $settings = &$blog->settings;
            $settings->setNamespace(self::namespace());
            $settings->put((string)self::prefix().$param, (integer)$val, 'integer', null, true, $global);
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
    }

	/**
	* enregistre un booléen dans le paramètre
	*/
    static public function setB($param, $val, $global=false)
    {
		global $core;
        try
        {
	        $blog = &$core->blog;
	        $settings = &$blog->settings;
            $settings->setNamespace(self::namespace());
            $settings->put((string) self::prefix().$param, (boolean)$val, 'boolean', null, true, $global);
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
    }

	/**
	* supprime le paramètre
	*/
    static public function delete($param)
    {
		global $core;
        try
        {
	        $blog = &$core->blog;
	        $settings = &$blog->settings;
            $settings->setNamespace(self::namespace());
            $settings->drop((string)self::prefix().$param);
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
    }

	/**
	* état d'installation du plugin
	*/
	static public function isInstalled() { return (boolean)self::get('installed'); }

    /**
	* positionne l'état d'installation du plugin
	*/
	static public function setInstalled($val) { self::setB('installed', (boolean)$val, true); }

    /**
	* active l'installation du plugin
	*/
	static public function Install() { self::setInstalled(true); }

    /**
	* désactive l'installation plugin
	*/
	static public function Uninstall() { self::setInstalled(false); }

	/**
	* état d'activation du plugin
	*/
    static public function isActive() { return (boolean)self::get('active'); }

    /**
	* positionne l'état d'activation du plugin
	*/
	static public function setActive($val) { self::setB('active', (boolean)$val); }

    /**
	* active le plugin
	*/
	static public function Activate() { self::setActive(true); }

    /**
	* désactive le plugin
	*/
	static public function Inactivate() { self::setActive(false); }

	/** ==================================================
	récupération des informations de mise à jour
	================================================== */

	static protected $remotelines = null;

	/**
	* url de base pour les mises à jour
	*/
	static public function baseUpdateUrl() { return html::escapeURL("http://phoenix.cybride.net/public/plugins/update/"); }

	/**
	* url pour le fichier de mise à jour
	*/
	static public function updateUrl() { return html::escapeURL(self::baseUpdateUrl().self::pname().'.txt'); }

	/**
	* renvoit le nom du plugin
	*/
    static public function Name() { return (string)self::tag('name'); }

	/**
	* est-ce qu'on a le nom du plugin
	*/
    static public function hasName() { return (bool)(self::pname() != null && strlen(self::pname()) > 0); }

	/**
	* renvoit la version du plugin
	*/
    static public function Version() { return (string)self::tag('version'); }

	/**
	* est-ce qu'on a la version du plugin
	*/
    static public function hasVersion() { return (bool)(self::Version() != null && strlen(self::Version()) > 0); }

	/**
	* renvoit l'url du billet de publication du plugin
	*/
    static public function Post() { return (string)self::tag('post'); }

	/**
	* est-ce qu'on a l'url du billet de publication du plugin
	*/
    static public function hasPost() { return (bool)(self::Post() != null && strlen(self::Post()) > 0); }

	/**
	* renvoit l'url du package d'installation du plugin
	*/
    static public function Package() { return (string)self::tag('package'); }

	/**
	* est-ce qu'on a l'url du package d'installation du plugin
	*/
    static public function hasPackage() { return (bool)(self::Package() != null && strlen(self::Package()) > 0); }

	/**
	* renvoit l'url de l'archive du plugin
	*/
    static public function Archive() { return (string)self::tag('archive'); }

	/**
	* est-ce qu'on a l'url de l'archive du plugin
	*/
    static public function hasArchive() { return (bool)(self::Archive() != null && strlen(self::Archive()) > 0); }

	/**
	* est-ce qu'on a les informations lues depuis le fichier de mise à jour
	*/
    static public function hasDatas() { return (bool)(self::$remotelines != null && is_array(self::$remotelines)); }

	/**
	* renvoit une information parmis les lignes lues
	*/
    static protected function tag($tag)
    {
		global $core;
		try
		{
	        if ($tag == null) return null;
	        else if (!self::hasDatas()) return null;
	        else if (!array_key_exists($tag, self::$remotelines)) return null;
	        else return (string) self::$remotelines[$tag];
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
    }

	/**
	* lit les informations
	*/
    static public function readUpdate()
    {
		global $core;
        try
        {
	        if (!ini_get('allow_url_fopen'))
	            throw new Exception('Unable to check for upgrade since \'allow_url_fopen\' is disabled on this system.');

			self::$remotelines = null;
            $content = netHttp::quickGet(self::updateUrl());
            if (!empty($content))
            {
                $lines = explode("\n", $content);
                if (is_array($lines))
                {
					self::$remotelines = array();
                    foreach ($lines as $datas)
                    {
                        if (strlen($datas) > 0)
                        {
                            $line = trim($datas);
                            $parts = explode('=', $line);
                            self::$remotelines[ trim($parts[0]) ] = trim($parts[1]);
                        }
                    }
                }
            }
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
    }

	/** ==================================================
	mises à jour
	================================================== */

	static protected $newversionavailable;

	/**
	* renvoit l'indicateur de disponibilité de mise à jour
	*/
	static public function isNewVersionAvailable() { return (boolean)self::$newversionavailable; }

	/**
	* lecture d'une information particulière concernant un plugin (api dotclear 2)
	*/
    static protected function getInfo($info)
    {
		global $core;
		try
		{
			$plugins = $core->plugins;
			return $plugins->moduleInfo(self::pname(), $info);
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
    }

	/**
	* racine des fichiers du plugin
	*/
    static public function dcRoot() { return self::getInfo('root'); }

	/**
	* nom du plugin
	*/
    static public function dcName() { return self::getInfo('name'); }

	/**
	* description du plugin
	*/
    static public function dcDesc() { return self::getInfo('desc'); }

	/**
	* auteur du plugin
	*/
    static public function dcAuthor() { return self::getInfo('author'); }

	/**
	* version du plugin
	*/
    static public function dcVersion() { return self::getInfo('version'); }

	/**
	* permissions du plugin
	*/
    static public function dcPermissions() { return self::getInfo('permissions'); }

	/**
	* priorité du plugin
	*/
    static public function dcPriority() { return self::getInfo('priority'); }

	/**
	* comparaison des deux versions
	* renvoit <0 si old < new
	* renvoit >0 si old > new
	* renvoit =0 si old = new
	*/
	static public function compareVersion($oldv, $newv) { return (integer)version_compare($oldv, $newv); }

	/**
	* vérifie les mises à jour et positionne le flag indicateur
	*/
	static public function checkUpdate()
	{
		self::$newversionavailable = false;
		self::readUpdate();
		if (self::hasDatas())
		{
	        $v_current = self::dcVersion();
	        $v_remote = self::Version();

			if (self::compareVersion($v_current, $v_remote) < 0)
				self::$newversionavailable = true;
		}
	}

	/**
	* génère le code html pour affichage dans l'admin des informations de mise à jour
	*/
    static public function htmlNewVersion($check = true)
    {
		if (!$check)
			return '';
		else
		{
			$msg = '';
			self::checkUpdate();
			if (!self::isNewVersionAvailable())
				 $msg .= __('No new version available.');
			else
			{
                $msg .= __('New version available:').' '.self::Version().' ';

				$m = array();
                if (self::hasPost() || self::hasPackage() || self::hasArchive()) $msg .= '[';
                if (self::hasPost()) $m[] = '<a href="'.self::post().'" title="'.__('Read the post.').'">'.__('post').'</a>';
                if (self::hasPackage()) $m[] = '<a href="'.self::Package().'" title="'.__('Installer.').'">'.__('pkg.gz').'</a>';
                if (self::hasArchive()) $m[] = '<a href="'.self::Archive().'" title="'.__('Archive.').'">'.__('tar.gz').'</a>';

                if (self::hasPost() || self::hasPackage() || self::hasArchive())
					$msg .= (string) implode(" | ", $m) . ']';
			}
            return $msg;
		}
	}

	/** ==================================================
	intégration avec Dotclear
	================================================== */

	/**
	* permet de savoir si la version de Dotclear installé une version finale
	* compatible Dotclear 2.0 beta 6 ou SVN
	*/
    static public function dbVersion()
    {
		global $core;
        try
        {
            return (string)$core->getVersion('core');
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
    }

	/**
	* permet de savoir si la version de Dotclear installé une version finale
	*/
	static public function isRelease()
    {
		global $core;
        try
        {
	        $version = (string)self::dbVersion();
	        if (!stripos($version, 'beta')) return true;
	        else return false;
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
    }

	/**
	* permet de savoir si la version de Dotclear installé la beta 6
	*/
    static public function isBeta($sub = '6')
    {
		global $core;
        try
        {
	        $version = (string)self::dbVersion();
			if (stripos($version, 'beta'.$sub)) return true;
	        else return false;
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
    }

	/**
	* permet de savoir si la version de Dotclear installé est une version 'svn'
	*/
    static public function isSVN() { return !self::isRelease() && (!self::isBeta('6') || !self::isBeta('7')); }
}

?>