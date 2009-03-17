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

// chargement des librairies
require dirname(__FILE__).'/class.template.php';

// le plugin
class newsletterCore
{
	// Définition des variables
	protected $blog;
 	protected $con;
 	protected $table;
 	protected $blogid;
 	protected $errors;

	/**
	Fonction d'init
	*/	
	public function __construct(&$core)
	{
		$this->core =& $core;
		$this->blog =& $core->blog;
		$this->con =& $this->blog->con;
		$this->blogid = $con->escape((string)$blog->id);
	}
	
	/* ==================================================
		fonction techniques
	================================================== */

	/**
	* est-ce que la version de Dotclear est installée
	*/
	public static function isAllowed()
	{
		//if (newsletterPlugin::isRelease() || newsletterPlugin::isBeta('7')) 
		if (newsletterPlugin::isRelease()) 
			return true;
		else 
			return false;
	}

	/**
	* est-ce que le plugin est installé
	*/
	public static function isInstalled() 
	{ 
		return newsletterPlugin::isInstalled(); 
	}

	// Fonction récupérée dans le plugin dCom
	// Oleksandr Syenchuk, Jean-François Michaud and contributors.
	public static function cutString($str,$maxlength=false)
	{
		if (mb_strlen($str) > $maxlength && $maxlength)
			return self::myCutString($str,$maxlength).'...';
		return $str;
	}
	
	// Fonction cutString() de Dotclear écrite par Olivier Meunier
	// Corrigée pour supporter le UTF-8
	// https://clearbricks.org/svn/trunk/common/lib.text.php [72]
	public static function myCutString($str,$l)
	{
		$s = preg_split('/([\s]+)/u',$str,-1,PREG_SPLIT_DELIM_CAPTURE);
		
		$res = '';
		$L = 0;
		
		if (mb_strlen($s[0]) >= $l) {
			return mb_substr($s[0],0,$l);
		}
		
		foreach ($s as $v)
		{
			$L = $L+strlen($v);
			
			if ($L > $l) {
				break;
			} else {
				$res .= $v;
			}
		}
		
		return trim($res);
	}

	/**
	* retourne le contenu total de la table sous forme de tableau de données brutes
	* (tout blog confondu)
	*/
	public static function getRawDatas($onlyblog = false)
	{
		global $core;
		try
		{
			$blog = &$core->blog;
			$con = &$core->con;
			$blogid = (string)$blog->id;

			// requète sur les données et renvoi null si erreur
			$strReq =
				'SELECT *'.
				' FROM '.$core->prefix.newsletterPlugin::pname();
				
			if($onlyblog) {
				$strReq .= ' WHERE blog_id=\''.$blogid.'\'';	
			}

			$rs = $con->select($strReq);
			if ($rs->isEmpty())
				return null;
			else 
				return $rs;
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}

	/**
	* retourne le prochain id de la table
	*/
	public static function nextId()
	{
		global $core;
		try {
			$blog = &$core->blog;
			$con = &$core->con;
			$blogid = (string)$blog->id;

			// requète sur les données et renvoi un entier
			/*
			$strReq =
				'SELECT max(subscriber_id)'.
				' FROM '.$core->prefix.newsletterPlugin::pname().
				' WHERE blog_id=\''.$blogid.'\'';
			//*/
			$strReq =
				'SELECT max(subscriber_id)'.
				' FROM '.$core->prefix.newsletterPlugin::pname();

			$rs = $con->select($strReq);
			if ($rs->isEmpty()) 
				return 0;
			else 
				return ((integer)$rs->f(0)) +1;
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* renvoi un id pris au hasard dans la table
	*/
	public static function randomId()
	{
		global $core;
		try {
			$blog = &$core->blog;
			$con = &$core->con;
			$blogid = (string)$blog->id;

			// requète sur les données et renvoi un entier
			$strReq =
    			'SELECT min(subscriber_id), max(subscriber_id)'.
    			' FROM '.$core->prefix.newsletterPlugin::pname().
    			' WHERE blog_id=\''.$blogid.'\'';

			$rs = $con->select($strReq);
			if ($rs->isEmpty()) 
				return 0;
			else 
				return rand($rs->f(0), $rs->f(1));
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* génère un code d'enregistrement
	*/
	public static function regcode() 
	{
		return md5(date('Y-m-d H:i:s', strtotime("now")) ); 
	}

	/**
	* test l'existence d'un abonné par son id
	*/
	public static function exist($id = -1) 
	{
		if (!is_numeric($id)) { // test sur l'id qui doit être numérique
			return null;
		} else if ($id < 0) {	// test sur la valeur de l'id qui doit être positive ou null
			return null;
		} else { 				// récupère l'abonné
			global $core;
			try {
				$blog = &$core->blog;
				$con = &$core->con;
				$blogid = (string)$blog->id;

				// requète sur les données et renvoi null si erreur
	            	$strReq =
	    			'SELECT subscriber_id'.
	    			' FROM '.$core->prefix.newsletterPlugin::pname().
	    			' WHERE blog_id=\''.$blogid.'\' AND subscriber_id='.$id;

				$rs = $con->select($strReq);
				if ($rs->isEmpty()) 
	            		return false;
				else 
					return true;
			} catch (Exception $e) { 
	        		$core->error->add($e->getMessage()); 
			}
		}
	}

	/**
	* récupère un abonné par son email
	*/
	public static function getEmail($_email = null)
	{
		// test sur l'email qui doit être renseigné
		if ($_email == null) {
			return null;
		} else { // récupère l'abonné

			global $core;
	        	try {
				$blog = &$core->blog;
				$con = &$core->con;
				$blogid = (string)$blog->id;

				// nettoyage et sécurisation des données saisies
				$email = $con->escape(html::escapeHTML(html::clean($_email)));

				// requète sur les données et renvoi null si erreur
	          	$strReq =
	    			'SELECT subscriber_id'.
	    			' FROM '.$core->prefix.newsletterPlugin::pname().
	    			' WHERE blog_id=\''.$blogid.'\' AND email=\''.$email.'\'';

				$rs = $con->select($strReq);
	            	if ($rs->isEmpty()) 
	            		return null;
				else 
					return self::get($rs->f('subscriber_id'));
			} catch (Exception $e) { 
	        		$core->error->add($e->getMessage()); 
			}
		}
	}


	/**
	* récupère des abonnés par leur id
	*/
	public static function get($id = -1)
	{
		// test sur la valeur de l'id qui doit être positive ou null
		if ($id < 0) return null;

		// récupère les abonnés
		else
		{
			global $core;
	        try
	        {
				$blog = &$core->blog;
				$con = &$core->con;
				$blogid = (string)$blog->id;

                // mise en forme du tableau d'id
                if (is_array($id)) $ids = implode(", ", $id);
                else $ids = $id;

				// requète sur les données et renvoi null si erreur
	            $strReq =
	    			'SELECT subscriber_id,email,regcode,state,subscribed,lastsent,modesend' .
	    			' FROM '.$core->prefix.newsletterPlugin::pname().
	    			' WHERE blog_id=\''.$blogid.'\' AND subscriber_id IN('.$ids.')';

				$rs = $con->select($strReq);
	            if ($rs->isEmpty()) return null;
				else return $rs;
	        }
		    catch (Exception $e) { $core->error->add($e->getMessage()); }
		}
	}

	/**
	* ajoute un abonné
	*/
	public static function add($_email = null, $_blogid = null, $_regcode = null, $_modesend = null)
	{
		// test des paramètres
		if ($_email == null) {
			return null;
		} else {
			
			global $core;
			try {
				$blog = &$core->blog;
				$con = &$core->con;
				$blogid = $con->escape((string)$blog->id);

				if (newsletterCore::getEmail($_email)) {
					return false;
				}

				// génération des informations manquantes
				if ($_regcode == null) {
					$_regcode = self::regcode();
				}

				if ($_modesend == null) {
					$_modesend = newsletterPlugin::getSendMode();
				}

				if ($_blogid == null) {
					$_blogid = $blogid;
				}
				
				// génération de la requète
				$cur = $con->openCursor($core->prefix.newsletterPlugin::pname());
				$cur->subscriber_id = self::nextId();
				$cur->blog_id = $_blogid;
				$cur->email = $con->escape(html::escapeHTML(html::clean($_email)));
				$cur->regcode = $con->escape(html::escapeHTML(html::clean($_regcode)));
				$cur->state = 'pending';
				$cur->lastsent = $cur->subscribed = date('Y-m-d H:i:s');
				$cur->modesend = $con->escape(html::escapeHTML(html::clean($_modesend)));

				// requète sur les données et retourne un booléen
				$cur->insert();
				return true;
			} catch (Exception $e) { 
				$core->error->add($e->getMessage()); 
			}
		}
	}
	
	/**
	* implode un tableau associatif (http://www.php.net/manual/fr/function.implode.php)
	*/
	private static function implode_assoc($glue1, $glue2, $array)
	{
		foreach($array as $key => $val) {
			$array2[] = $key.$glue1.$val;
		}
		return implode($glue2, $array2);
	}

	/**
	* met à jour un abonné par son id
	*/
	public static function update($id = -1, 
							$_email = null, 
							$_state = null, 
							$_regcode = null, 
							$_subscribed = null, 
							$_lastsent = null, 
							$_modesend = null)
	{
		// test des paramètres
		if (!self::exist($id)) {
			return null;
		} else { // met à jour l'abonné
			global $core;
			try {
				$blog = &$core->blog;
				$con = &$core->con;
				$blogid = $con->escape((string)$blog->id);

				// génération de la requète
				$cur = $con->openCursor($core->prefix.newsletterPlugin::pname());

				$cur->subscriber_id = $id;
				$cur->blog_id = $blogid;

				if ($_email != null) 
					$cur->email = $con->escape(html::escapeHTML(html::clean($_email)));
				
				if ($_state != null) 
					$cur->state = $con->escape(html::escapeHTML(html::clean($_state)));
				
				if ($_regcode != null) 
					$cur->regcode = $con->escape(html::escapeHTML(html::clean($_regcode)));
				
				if ($_subscribed != null) 
					$cur->subscribed = $con->escape(html::escapeHTML(html::clean($_subscribed)));
				
				if ($_lastsent != null) 
					$cur->lastsent = $con->escape(html::escapeHTML(html::clean($_lastsent)));
				
				if ($_modesend != null) 
					$cur->modesend = $con->escape(html::escapeHTML(html::clean($_modesend)));

				$cur->update('WHERE blog_id=\''.$con->escape($blogid).'\' AND subscriber_id='.$id);
				
				return true;
			} catch (Exception $e) { 
				$core->error->add($e->getMessage()); 
			}
		}
	}

	/**
	* supprime un abonné par son id
	*/
	public static function delete($id = -1)
	{
		// test sur la valeur de l'id qui doit être positive ou null
		if ($id < 0) {
			return null;
		} else { // supprime les abonnés
			global $core;
			try {
				$blog = &$core->blog;
				$con = &$core->con;
				$blogid = $con->escape((string)$blog->id);

				// mise en forme du tableau d'id
				if (is_array($id)) 
					$ids = implode(", ", $id);
				else 
					$ids = $id;

				// requète sur les données et renvoi un booléen
				$strReq =
				'DELETE FROM '.$core->prefix.newsletterPlugin::pname().
				' WHERE blog_id=\''.$blogid.'\' AND subscriber_id IN('.$ids.')';

				if ($con->execute($strReq)) 
					return true;
				else
					return false;
			} catch (Exception $e) { 
				$core->error->add($e->getMessage()); 
			}
		}
	}

	/**
	* retourne le contenu de la table sous forme de tableau de données brutes
	*/
	public static function getlist($active = false)
	{
		global $core;
		try {
			$blog = &$core->blog;
			$con = &$core->con;
			$blogid = $con->escape((string)$blog->id);

			// requète sur les données et renvoi null si erreur
			$strReq =
				'SELECT *'.
				' FROM '.$core->prefix.newsletterPlugin::pname().
				' WHERE blog_id=\''.$blogid.'\'';

			if ($active) $strReq .= ' AND state=\'enabled\'';            
                
			$rs = $con->select($strReq);
			if ($rs->isEmpty()) 
				return null;
			else 
				return $rs;
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* modifie l'état de l'abonné
	*/
	public static function state($id = -1, $_state = null)
	{
		// test sur la valeur de l'id qui doit être positive ou null
		if ($id < 0) {
			return null;
		} else { 
			// modifie l'état des abonnés
		
			// filtrage sur le code de status
			switch ($_state) {
				case 'pending':
				case 'enabled':
				case 'suspended':
				case 'disabled':
					break;
				default:
					return false;
			}

			global $core;
			try {
				$blog = &$core->blog;
				$con = &$core->con;
				$blogid = $con->escape((string)$blog->id);

				// mise en forme du tableau d'id
				if (is_array($id)) 
					$ids = implode(", ", $id);
				else 
					$ids = $id;

				// génération de la requète
				$cur = $con->openCursor($core->prefix.newsletterPlugin::pname());

				$cur->state = $con->escape(html::escapeHTML(html::clean($_state)));

				$cur->update('WHERE blog_id=\''.$con->escape($blogid).'\' AND subscriber_id IN('.$ids.')');
				return true;
			} catch (Exception $e) { 
				$core->error->add($e->getMessage()); 
			}
		}
	}

	/**
	* place les comptes en attente
	*/
	public static function pending($id = -1) 
	{ 
		return self::state($id, 'pending'); 
	}

	/**
	* active les comptes
	*/
	public static function enable($id = -1) 
	{ 
		return self::state($id, 'enabled'); 
	}

	/**
	* suspend les comptes
	*/
	public static function suspend($id = -1)
	{ 
		return self::state($id, 'suspended'); 
	}

	/**
	* désactive les comptes
	*/
	public static function disable($id = -1)
	{ 
		return self::state($id, 'disabled'); 
	}

	/**
	* comptes en attente de confirmation
	*/
	public static function confirm($id = -1)
	{ 
		return self::state($id, 'confirm'); 
	}

	/**
	* modifie la date de dernier envoi
	*/
	public static function lastsent($id = -1, $_lastsent = null) 
	{
		// test sur la valeur de l'id qui doit être positive ou null
		if ($id < 0) 
			return null;
		// modifie l'état des abonnés
		else {
			global $core;
	        try {
				$blog = &$core->blog;
				$con = &$core->con;
				$blogid = $con->escape((string)$blog->id);

                // mise en forme du tableau d'id
                if (is_array($id)) 
                	$ids = implode(", ", $id);
                else 
                	$ids = $id;

                // génération de la requète
                if ($_lastsent == 'clear') 
                	$req = 'UPDATE '.$core->prefix.newsletterPlugin::pname().' SET lastsent=subscribed';
                else if ($_lastsent == null) 
                	$req = 'UPDATE '.$core->prefix.newsletterPlugin::pname().' SET lastsent=now()';
                else 
                	$cur->lastsent = $con->escape(html::escapeHTML(html::clean($_lastsent)));
                
                $req .= ' WHERE blog_id=\''.$con->escape($blogid).'\' AND subscriber_id IN('.$ids.')';
                $con->execute($req);
                				
				return true;
	        } catch (Exception $e) { 
	        	$core->error->add($e->getMessage()); 
	        }
		}
	}

	/**
	* modifie le format de la lettre pour l'abonné
	*/
	public static function changemode($id = -1, $_modesend = null)
	{
		// test sur la valeur de l'id qui doit être positive ou null
		if ($id < 0) {
			return null;
		} else { 
			// modifie le format de la lettre de l'abonné
		
			// filtrage sur le code de status
			switch ($_modesend) {
				case 'html':
				case 'text':
					break;
				default:
					return false;
			}

			global $core;
			try {
				$blog = &$core->blog;
				$con = &$core->con;
				$blogid = $con->escape((string)$blog->id);

				// mise en forme du tableau d'id
				if (is_array($id)) 
					$ids = implode(", ", $id);
				else 
					$ids = $id;

				// génération de la requète
				$cur = $con->openCursor($core->prefix.newsletterPlugin::pname());

				$cur->modesend = $con->escape(html::escapeHTML(html::clean($_modesend)));

				$cur->update('WHERE blog_id=\''.$con->escape($blogid).'\' AND subscriber_id IN('.$ids.')');
				return true;
			} catch (Exception $e) { 
				$core->error->add($e->getMessage()); 
			}
		}
	}
	
	/**
	* change le format en html des comptes
	*/
	public static function changemodehtml($id = -1)
	{ 
		return self::changemode($id, 'html'); 
	}

	/**
	* change le format en text des comptes
	*/
	public static function changemodetext($id = -1)
	{ 
		return self::changemode($id, 'text'); 
	}

	/* ==================================================
		billets
	================================================== */

	/**
	* retourne les billets pour la newsletter:
	*/
	public static function getPosts($dt)
	{
		global $core;
		try	{
		
			$con = &$core->con;
			$blog = &$core->blog;

			// paramétrage de la récupération des billets
			$params = array();

			// sélection du contenu
			$params['no_content'] = (newsletterPlugin::getViewContentPost() ? false : true); 
			// sélection des billets
			$params['post_type'] = 'post';
			// uniquement les billets publiés, sans mot de passe
			$params['post_status'] = 1;
			// sans mot de passe
			$params['sql'] = ' AND P.post_password IS NULL';
			
			// limitation du nombre de billets
			$maxPost = newsletterPlugin::getMaxPosts();
			if ($maxPost > 0) 
				$params['limit'] = $maxPost;

			// définition du tris des enregistrements et filtrage dans le temps
			$params['order'] = ' P.post_id DESC, P.post_dt ASC';
			
			// filtre sur la cartegorie
			$category = newsletterPlugin::getCategory();
			if ($category)
			{
				if ($category == 'null') {
					$params['sql'] = ' AND P.cat_id IS NULL ';
				} elseif (is_numeric($category)) {
					$params['cat_id'] = (integer) $category;
				} else {
					$params['cat_url'] = $category;
				}
			}
			
	         $year = dt::dt2str('%Y', $dt);
	         $month = dt::dt2str('%m', $dt);
	         $day = dt::dt2str('%d', $dt);
	         $hours = dt::dt2str('%H', $dt);
	         $minutes = dt::dt2str('%M', $dt);
	         $seconds = dt::dt2str('%S', $dt);

			// depuis lastsent
	         $params['sql'] .= ' AND '.$con->dateFormat('P.post_dt','%Y-%m-%d %H:%M:%S')."> '$year-$month-$day $hours:$minutes:$seconds'";

			// récupération des billets
			$rs = $blog->getPosts($params, false);
            
			return($rs->isEmpty()?null:$rs);

		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/* ==================================================
		emails
	================================================== */

	/**
	* convertit le texte en 7 bits
	*/
	private static function to7bit($text, $from_enc)
	{
		global $core;
		try {
			return preg_replace(
				array('/&szlig;/', '/&(..)lig;/','/&([aouAOU])uml;/', '/&(.)[^;]*;/'),
				array('ss', "$1", "$1" . 'e', "$1"),
				mb_convert_encoding($text, 'HTML-ENTITIES', $from_enc));
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* envoi de mail
	*/
	public static function Sendmail($_from, $_name, $_email, $_subject, $_body, $_type = 'text', $_lang = 'fr')
	{
		if (empty($_from) || empty($_email) || empty($_subject) || empty($_body)) {
			return false;
		} else {
      		if (empty($_name)) {
      			$_name = $_from;
      		}
			
			try {
				/*
				$subject = self::to7bit($_subject, 'UTF-8');
	            	$headers =
	    			"X-Sender: $_name <$_from>\n".
	    			"From: $_name <$_from>\n".
	    			"Reply-To: $_name <$_from>\n".
	    			"Return-Path: $_name <$_from>\n".
	    			"Date: ".date("r").
	    			"\n" . "Message-ID: <".md5(time())."@".$_SERVER['SERVER_NAME'].">\n".
	    			"MIME-Version: 1.0\n".
	    			(($_type == 'html') ? "Content-Type: text/html;charset=utf-8\n" : "Content-Type: text/plain;charset=utf-8\n").
	    			"Delivered-to: $_email <$_email>\n".
	    			"X-Mailer: Mail With PHP\n";
	                    
		          return (mail::sendMail($_email, $subject, $_body, $headers, NULL) == TRUE) ? true : false;
		          //*/
		          
		          $f_check_notification = newsletterPlugin::getCheckNotification();
				$email_from = mail::B64Header($_name.' <'.$_from.'>');
				$email_to = mail::B64Header($_email.' <'.$_email.'>');

				$headers = array(
					'From: '.$email_from,
					'Reply-To: '.$email_from,
					'Delivered-to: '.$email_to,			
					'X-Sender:'.$email_from,
					'MIME-Version: 1.0',
					(($_type == 'html') ? 'Content-Type: text/html; charset=UTF-8;' : 'Content-Type: text/plain; charset=UTF-8;'),
					'X-Mailer: Dotclear '.mail::B64Header(newsletterPlugin::dbVersion()),
					'X-Blog-Id: '.mail::B64Header($core->blog->id),
					'X-Blog-Name: '.mail::B64Header($core->blog->name),
					'X-Blog-Url: '.mail::B64Header($core->blog->url),
					'X-Originating-IP: '.http::realIP(),
					(($f_check_notification) ? 'Disposition-Notification-To: '.$email_from : '')
				);
		          
		          $subject = mail::B64Header($_subject);
		          
		          return (mail::sendMail($_email, $subject, $_body, $headers));
			} catch (Exception $e) { 
	      		//$core->error->add($e->getMessage());
	      		return false;
			}
		}
	}

	/**
	* renvoi l'url de base de newsletter
	*/
    public static function url($cmd = '')
    {
        global $core;
        try {
	        $url = &$core->url;
	        $blog = &$core->blog;
	        $blogurl = &$blog->url;

            if ($cmd == '') return http::concatURL($blogurl, $url->getBase('newsletter'));
            else return http::concatURL($blogurl, $url->getBase('newsletter')).'/'.$cmd;
        } catch (Exception $e) { 
        	$core->error->add($e->getMessage()); 
        }
    }

	/**
	* préparation de l'envoi d'un mail à un abonné
	*/
	private static function BeforeSendmailTo($header, $footer)
	{
		global $core;
		try
		{
			$url = &$core->url;
			$blog = &$core->blog;
			$blogname = &$blog->name;
			$blogdesc = &$blog->desc;
			$blogurl = &$blog->url;
			$urlBase = http::concatURL($blogurl, $url->getBase('newsletter'));

			nlTemplate::clear();
			nlTemplate::assign('header', $header);
			nlTemplate::assign('footer', $footer);
			nlTemplate::assign('blogName', $blogname);
			nlTemplate::assign('blogDesc', $blogdesc);
			nlTemplate::assign('blogUrl', $blogurl);
			nlTemplate::assign('txtIntroductoryMsg', newsletterPlugin::getIntroductoryMsg());
			nlTemplate::assign('txtHeading', newsletterPlugin::getPresentationPostsMsg());
			nlTemplate::assign('txt_intro_confirm', newsletterPlugin::getTxtIntroConfirm().', ');
			nlTemplate::assign('txtConfirm', newsletterPlugin::getTxtConfirm());
			nlTemplate::assign('txt_intro_disable', newsletterPlugin::getTxtIntroDisable().', ');
			nlTemplate::assign('txtDisable', newsletterPlugin::getTxtDisable());
			nlTemplate::assign('txt_intro_enable', newsletterPlugin::getTxtIntroEnable().', ');
			nlTemplate::assign('txtEnable', newsletterPlugin::getTxtEnable());
			nlTemplate::assign('txt_intro_suspend', newsletterPlugin::getTxtIntroSuspend().', ');
			nlTemplate::assign('txtSuspend', newsletterPlugin::getTxtSuspend());
			nlTemplate::assign('txtSubscribed', __('Thank you for your subscription.'));
			nlTemplate::assign('txtSuspended', __('Your account has been suspended.'));
			nlTemplate::assign('txtDisabled', __('Your account has been canceled.'));
			nlTemplate::assign('txtEnabled', __('Your account has been validated.'));
			nlTemplate::assign('txtChangingMode', __('Your sending format has been updated.'));
			nlTemplate::assign('txtBy', __('by'));
			nlTemplate::assign('txtMsgPresentationForm', newsletterPlugin::getMsgPresentationForm());
		}
		catch (Exception $e) { $core->error->add($e->getMessage()); }
	}

	/**
	* envoi de la newsletter
	*/
	public static function sendNewsletter($id = -1)
	{
		if (!newsletterPlugin::isActive()) { 	// test si le plugin est actif
			return false;
		} else if ($id == -1) { 				// test sur la valeur de l'id qui doit être positive ou null
			return false;
		} else {							// envoi des mails aux abonnés
			global $core;
			try
			{
				$url = &$core->url;
				$blog = &$core->blog;
				$blogurl = &$blog->url;
			    
				$format = '';
				if (!empty($attr['format'])) {
					$format = addslashes($attr['format']);
				}			    

				// prise en compte du paramètres: liste d'id ou id simple
				if (is_array($id)) {
					$ids = $id;
				} else { 
					$ids = array(); 
					$ids[] = $id; 
				}

				// initialisation du moteur de template
				$send = array();
				self::BeforeSendmailTo(newsletterPlugin::getPresentationMsg(), newsletterPlugin::getConcludingMsg());
				$states = array();

				// initialisation des variables de travail
				$blogname = &$blog->name;
				$editorName = newsletterPlugin::getEditorName();
				$editorEmail = newsletterPlugin::getEditorEmail();
				$mode = newsletterPlugin::getSendMode();
				$subject = text::toUTF8(__('Newsletter for').' '.$blogname);

				// boucle sur les ids des abonnés à mailer
				foreach ($ids as $subscriber_id)
				{
					// récupération de l'abonné et extraction des données
				    $subscriber = self::get($subscriber_id);

					// récupération des billets en fonction de l'abonné (date de dernier envoi et billets déja envoyés)
					$posts = self::getPosts($subscriber->lastsent);
					if ($posts == null) {
						$send['nothing'][] = $subscriber->email; // rien à envoyer (aucun billet)
					} else {
						$body = '';
						$bodies = array();
						$convert = new html2text();
						$convert->labelLinks = __('Links:');
						$convert->set_base_url($blogurl);
						// boucle sur les billets concernés pour l'abonnés
						$posts->core = $core;
						$posts->moveStart();
						while ($posts->fetch())
						{
							$p_ids[] = $posts->post_id;

							// récupération des informations du billet
							if(newsletterPlugin::getViewContentPost()) {
								$bodies[] = array(
								'title' => $posts->post_title,
								'url' => $posts->getURL(),
								'date' => $posts->getDate($format),
								'category' => $posts->getCategoryURL(),
								'content' => html::escapeHTML(self::cutString(html::decodeEntities(html::clean($posts->getContent())),newsletterPlugin::getSizeContentPost())),
								'author' => $posts->getAuthorCN()
								);
							} else {
								$bodies[] = array(
								'title' => $posts->post_title,
								'url' => $posts->getURL(),
								'date' => $posts->getDate($format),
								'category' => $posts->getCategoryURL(),
								'content' => html::escapeHTML(''),
								'author' => $posts->getAuthorCN()
								);
							}
						}

						// définition du format d'envoi
						if (!newsletterPlugin::getUseDefaultFormat() && $subscriber->modesend != null) {
							$mode = $subscriber->modesend;
						}
						
						// intégration dans le template des billets en génération du rendu
						nlTemplate::assign('urlSuspend', self::url('suspend/'.base64_encode($subscriber->email)));
						nlTemplate::assign('urlDisable', self::url('disable/'.base64_encode($subscriber->email)));
						nlTemplate::assign('posts', $bodies);
						$body = nlTemplate::render('newsletter', $mode);

						// envoi du mail et log
						if (self::Sendmail($editorEmail, $editorName, $subscriber->email, $subject, $body, $mode)) {
							// prise en compte email envoyé et mise à jour de l'abonné (date de dernier envoi et liste de billets déja envoyés)
							$send['ok'][] = $subscriber->email;
							$states[] = $subscriber->subscriber_id;
						} else { 
							// erreur d'envoi de mail
							$send['error'][] = $subscriber->email;
						}
						
   					}
				}

            if (is_array($states) && count($states) > 0)
            	self::lastsent($states);							
                
				$msg = '';
				if (isset($send['ok']) && count($send['ok']) > 0) 
					$msg .= __('Successful mail sent for').' '.implode(', ', $send['ok']).'<br />';
				if (isset($send['error']) && count($send['error']) > 0) 
					$msg .= __('Mail sent error for').' '.implode(', ', $send['error']).'<br />';
				if (isset($send['nothing']) &&count($send['nothing']) > 0) 
					$msg .= __('Nothing to send for').' '.implode(', ', $send['nothing']).'<br />';

				return $msg;
			}
			catch (Exception $e) { 
				$core->error->add($e->getMessage()); 
			}
		}
	}

	/**
	* envoi automatique de la newsletter
	*/
	public static function autosendNewsletter()
	{
		// test si le plugin est actif
		if (!newsletterPlugin::isActive()) {
			return;
		}

		// test si l'envoi automatique est activé
		if (!newsletterPlugin::getAutosend()) {
			return;
		} else {
			$datas = self::getlist(true);
         	if (!is_object($datas)) {
         		return;
         	} else {
				global $core;
		      	try {
					$datas->moveStart();
               		while ($datas->fetch()) { 
               			self::sendNewsletter($datas->subscriber_id); 
               		}
				} catch (Exception $e) { 
					$core->error->add($e->getMessage()); 
					
				}
			}            	
		}	
	}

	/**
	* envoi automatique de la newsletter par la tâche planifiée
	*/
	public static function cronSendNewsletter()
	{
		// test si le plugin est actif
		if (!newsletterPlugin::isActive()) {
			return;
		}
		
		// test si la planification est activée
		if (!newsletterPlugin::getCheckSchedule()) {
			return;
		} else {
			$datas = self::getlist(true);
			if (!is_object($datas)) {
				return;
			} else {
				global $core;
				try {
					$datas->moveStart();
					while ($datas->fetch()) { 
						$msg = self::sendNewsletter($datas->subscriber_id); 
					}
				} catch (Exception $e) { 
					$core->error->add($e->getMessage()); 
				}
			}
		}
	}
	
	/**
	* envoi de la confirmation
	*/
	public static function sendConfirm($id = -1)
	{
		// test si le plugin est actif
		if (!newsletterPlugin::isActive()) return false;

		// test sur la valeur de l'id qui doit être positive ou null
		else if ($id == -1) return false;

		// envoi des mails aux abonnés
		else
		{
			global $core;
			try
			{
			    $url = &$core->url;
			    $blog = &$core->blog;
			    $blogurl = &$blog->url;

				// prise en compte du paramètres: liste d'id ou id simple
				if (is_array($id)) {
					$ids = $id;
				} else { 
					$ids = array(); $ids[] = $id; 
				}

				// initialisation du moteur de template
				$send_ok = array();
				$send_error = array();
				$states = array();
				self::BeforeSendmailTo(__('Newsletter subscription confirmation for'), __('Thanks you for subscribing.'));

				// initialisation des variables de travail
				$blogname = &$blog->name;
				$editorName = newsletterPlugin::getEditorName();
				$editorEmail = newsletterPlugin::getEditorEmail();
				$mode = newsletterPlugin::getSendMode();
				$subject = text::toUTF8(__('Newsletter subscription confirmation for').' '.$blogname);

				// boucle sur les ids des abonnés à mailer
				foreach ($ids as $subscriber_id)
				{
					// récupération de l'abonné et extraction des données
					$subscriber = self::get($subscriber_id);

					// définition du format d'envoi
					if (!newsletterPlugin::getUseDefaultFormat() && $subscriber->modesend != null) {
						$mode = $subscriber->modesend;
					}

					// génération du rendu
					/*
					nlTemplate::assign('urlConfirm', self::url('confirm/'.base64_encode($subscriber->email).'/'.$subscriber->regcode));
					nlTemplate::assign('urlDisable', self::url('disable/'.base64_encode($subscriber->email)));
					//*/
					nlTemplate::assign('urlConfirm', self::url('confirm/'.str_replace('=','',base64_encode($subscriber->email).'/'.$subscriber->regcode.'/'.base64_encode($subscriber->modesend))));
					nlTemplate::assign('urlDisable', self::url('disable/'.str_replace('=','',base64_encode($subscriber->email))));
					$body = nlTemplate::render('confirm', $mode);

					// envoi du mail et log
					if (self::Sendmail($editorEmail, $editorName, $subscriber->email, $subject, $body, $mode))
					{
						$send_ok[] = $subscriber->email;
						$states[] = $subscriber_id;
					}
					else 
						$send_error[] = $subscriber->email;
				}

                if (is_array($states) && count($states) > 0)
                    self::confirm($states);
                
				$msg = '';
				if (count($send_ok) > 0) $msg .= __('Successful mail sent for').' '.implode(', ', $send_ok);
				if (count($send_ok) > 0 && count($send_error) > 0) $msg .= '<br />';
				if (count($send_error) > 0) $msg .= __('Mail sent error for').' '.implode(', ', $send_error);

				return $msg;
			}
			catch (Exception $e) { $core->error->add($e->getMessage()); }
		}
	}

	/**
	* envoi de la notification de suspension
	*/
	public static function sendSuspend($id = -1)
	{
		if (!newsletterPlugin::isActive()) { // test si le plugin est actif
			return false;
		
		} else if ($id == -1) { // test sur la valeur de l'id qui doit être positive ou null
			return false;
		} else { 	
			// envoi des mails aux abonnés
			global $core;
			try
			{
			    $url = &$core->url;
			    $blog = &$core->blog;
			    $blogurl = &$blog->url;

				// prise en compte du paramètres: liste d'id ou id simple
				if (is_array($id)) 
					$ids = $id;
				else { 
					$ids = array(); 
					$ids[] = $id; 
				}

				// initialisation du moteur de template
				$send_ok = array();
				$send_error = array();
				$states = array();
				self::BeforeSendmailTo(__('Newsletter account suspend for'), __('Have a nice day !'));

				// initialisation des variables de travail
				$blogname = &$blog->name;
				$editorName = newsletterPlugin::getEditorName();
				$editorEmail = newsletterPlugin::getEditorEmail();
				$mode = newsletterPlugin::getSendMode();
				$subject = text::toUTF8(__('Newsletter account suspend for').' '.$blogname);

				// boucle sur les ids des abonnés à mailer
				foreach ($ids as $subscriber_id)
				{
					// récupération de l'abonné et extraction des données
				    $subscriber = self::get($subscriber_id);

					// définition du format d'envoi
					if (!newsletterPlugin::getUseDefaultFormat() && $subscriber->modesend != null) {
						$mode = $subscriber->modesend;
					}

					// génération du rendu
					/*
					nlTemplate::assign('urlEnable', self::url('enable/'.base64_encode($subscriber->email)));
					//*/
					nlTemplate::assign('urlEnable', self::url('enable/'.str_replace('=','',base64_encode($subscriber->email))));
					$body = nlTemplate::render('suspend', $mode);

					// envoi du mail et log
					if (self::Sendmail($editorEmail, $editorName, $subscriber->email, $subject, $body, $mode))
					{
						$send_ok[] = $subscriber->email;
						$states[] = $subscriber_id;
					}
					else 
						$send_error[] = $subscriber->email;
				}
               
				// positionnement de l'état des comptes sur 'compte suspendu'
                if (is_array($states) && count($states) > 0)
                    self::suspend($states);

				$msg = '';
				if (count($send_ok) > 0) $msg .= __('Successful mail sent for').' '.implode(', ', $send_ok);
				if (count($send_ok) > 0 && count($send_error) > 0) $msg .= '<br />';
				if (count($send_error) > 0) $msg .= __('Mail sent error for').' '.implode(', ', $send_error);

				return $msg;
			}
			catch (Exception $e) { $core->error->add($e->getMessage()); }
		}
	}

	/**
	* envoi de la notification d'activation
	*/
	static function sendEnable($id = -1)
	{
		// test si le plugin est actif
		if (!newsletterPlugin::isActive()) return false;

		// test sur la valeur de l'id qui doit être positive ou null
		else if ($id == -1) return false;

		// envoi des mails aux abonnés
		else
		{
			global $core;
			try
			{
			    $url = &$core->url;
			    $blog = &$core->blog;
			    $blogurl = &$blog->url;

				// prise en compte du paramètres: liste d'id ou id simple
                if (is_array($id)) $ids = $id;
                else { $ids = array(); $ids[] = $id; }

				// initialisation du moteur de template
				$send_ok = array();
				$send_error = array();
				$states = array();
				self::BeforeSendmailTo(__('Newsletter account activation for'), __('Thank you for subscribing.'));

				// initialisation des variables de travail
				$blogname = &$blog->name;
				$editorName = newsletterPlugin::getEditorName();
				$editorEmail = newsletterPlugin::getEditorEmail();
				$mode = newsletterPlugin::getSendMode();
				$subject = text::toUTF8(__('Newsletter account activation for').' '.$blogname);

				// boucle sur les ids des abonnés à mailer
				foreach ($ids as $subscriber_id)
				{
					// récupération de l'abonné et extraction des données
					$subscriber = self::get($subscriber_id);

					// définition du format d'envoi
					if (!newsletterPlugin::getUseDefaultFormat() && $subscriber->modesend != null) {
						$mode = $subscriber->modesend;
					}

					// génération du rendu
					/*
					nlTemplate::assign('urlDisable', self::url('disable/'.base64_encode($subscriber->email)));
					nlTemplate::assign('urlSuspend', self::url('suspend/'.base64_encode($subscriber->email)));
					//*/
					nlTemplate::assign('urlDisable', self::url('disable/'.str_replace('=','',base64_encode($subscriber->email))));
					nlTemplate::assign('urlSuspend', self::url('suspend/'.str_replace('=','',base64_encode($subscriber->email))));
					$body = nlTemplate::render('enable', $mode);

					// envoi du mail et log
					if (self::Sendmail($editorEmail, $editorName, $subscriber->email, $subject, $body, $mode))
					{
						$send_ok[] = $subscriber->email;
						$states[] = $subscriber_id;
					}
					else 
						$send_error[] = $subscriber->email;
				}

				// positionnement de l'état des comptes sur 'compte validé'
                if (is_array($states) && count($states) > 0)
                    self::enable($states);

				$msg = '';
				if (count($send_ok) > 0) $msg .= __('Successful mail sent for').' '.implode(', ', $send_ok);
				if (count($send_ok) > 0 && count($send_error) > 0) $msg .= '<br />';
				if (count($send_error) > 0) $msg .= __('Mail sent error for').' '.implode(', ', $send_error);

				return $msg;
			}
			catch (Exception $e) { $core->error->add($e->getMessage()); }
		}
	}

	/**
	* envoi de la notification de désactivation de compte
	*/
	static function sendDisable($id = -1)
	{
		// test si le plugin est actif
		if (!newsletterPlugin::isActive()) return false;

		// test sur la valeur de l'id qui doit être positive ou null
		else if ($id == -1) return false;

		// envoi des mails aux abonnés
		else
		{
			global $core;
			try
			{
			    $url = &$core->url;
			    $blog = &$core->blog;
			    $blogurl = &$blog->url;

				// prise en compte du paramètres: liste d'id ou id simple
                if (is_array($id)) $ids = $id;
                else { $ids = array(); $ids[] = $id; }

				// initialisation du moteur de template
				$send_ok = array();
				$send_error = array();
				$states = array();
				self::BeforeSendmailTo(__('Newsletter account removal for'), __('Have a nice day !'));

				// initialisation des variables de travail
				$blogname = &$blog->name;
				$editorName = newsletterPlugin::getEditorName();
				$editorEmail = newsletterPlugin::getEditorEmail();
				$mode = newsletterPlugin::getSendMode();
				$subject = text::toUTF8(__('Newsletter account removal for').' '.$blogname);

				// boucle sur les ids des abonnés à mailer
				foreach ($ids as $subscriber_id)
				{
					// récupération de l'abonné et extraction des données
				    $subscriber = self::get($subscriber_id);

					// définition du format d'envoi
					if (!newsletterPlugin::getUseDefaultFormat() && $subscriber->modesend != null) {
						$mode = $subscriber->modesend;
					}

					// génération du rendu
					/*
					nlTemplate::assign('urlEnable', self::url('enable/'.base64_encode($subscriber->email)));
					//*/
					nlTemplate::assign('urlEnable', self::url('enable/'.str_replace('=','',base64_encode($subscriber->email))));
					$body = nlTemplate::render('disable', $mode);

					// envoi du mail et log
					if (self::Sendmail($editorEmail, $editorName, $subscriber->email, $subject, $body, $mode))
					{
						$send_ok[] = $subscriber->email;
						$states[] = $subscriber_id;
					}
					else $send_error[] = $subscriber->email;
				}

				// positionnement de l'état des comptes sur 'compte désactivés'
                if (is_array($states) && count($states) > 0)
                    self::disable($states);

				$msg = '';
				if (count($send_ok) > 0) $msg .= __('Successful mail sent for').' '.implode(', ', $send_ok);
				if (count($send_ok) > 0 && count($send_error) > 0) $msg .= '<br />';
				if (count($send_error) > 0) $msg .= __('Mail sent error for').' '.implode(', ', $send_error);

				return $msg;
			}
			catch (Exception $e) { $core->error->add($e->getMessage()); }
		}
	}

	/**
	* envoi d'un resumé du compte
	*/
	public static function sendResume($id = -1)
	{
		if (!newsletterPlugin::isActive()) { // test si le plugin est actif
			return false;
		} else if ($id == -1) { // test sur la valeur de l'id qui doit être positive ou null
			return false;
		} else { 	
			// envoi des mails aux abonnés
			global $core;
			try
			{
			    $url = &$core->url;
			    $blog = &$core->blog;
			    $blogurl = &$blog->url;

				// prise en compte du paramètres: liste d'id ou id simple
				if (is_array($id)) 
					$ids = $id;
				else { 
					$ids = array(); 
					$ids[] = $id; 
				}

				// initialisation du moteur de template
				$send_ok = array();
				$send_error = array();
				$states = array();
				self::BeforeSendmailTo(__('Newsletter account resume for'), __('Have a nice day !'));

				// initialisation des variables de travail
				$blogname = &$blog->name;
				$editorName = newsletterPlugin::getEditorName();
				$editorEmail = newsletterPlugin::getEditorEmail();
				$mode = newsletterPlugin::getSendMode();
				$subject = text::toUTF8(__('Newsletter account resume for').' '.$blogname);

				// boucle sur les ids des abonnés à mailer
				foreach ($ids as $subscriber_id)
				{
					// récupération de l'abonné et extraction des données
					$subscriber = self::get($subscriber_id);

					$txt_intro_enable = newsletterPlugin::getTxtIntroEnable().', ';
					$urlEnable = self::url('enable/'.str_replace('=','',base64_encode($subscriber->email)));
					$txtEnable = newsletterPlugin::getTxtEnable();
					
					$txt_intro_disable = newsletterPlugin::getTxtIntroDisable().', ';
					$urlDisable = self::url('disable/'.str_replace('=','',base64_encode($subscriber->email)));
					$txtDisable = newsletterPlugin::getTxtDisable();

					$txt_intro_suspend = newsletterPlugin::getTxtIntroSuspend().', ';
					$urlSuspend = self::url('suspend/'.str_replace('=','',base64_encode($subscriber->email)));
					$txtSuspend = newsletterPlugin::getTxtSuspend();

					$txt_intro_confirm = newsletterPlugin::getTxtIntroConfirm().', ';
					$urlConfirm = self::url('confirm/'.str_replace('=','',base64_encode($subscriber->email).'/'.$subscriber->regcode.'/'.base64_encode($subscriber->modesend)));
					$txtConfirm = newsletterPlugin::getTxtConfirm();
			
					$urlResume = '';
					
					switch ($subscriber->state) {
						case 'suspended':
						{
							$urlResume = $txt_intro_enable.' <a href="'.$urlEnable.'">'.$txtEnable.'</a><br />';
							$urlResume .= $txt_intro_disable.' <a href="'.$urlDisable.'">'.$txtDisable.'</a>';
							nlTemplate::assign('txtResume', __('Your account is suspended.'));
							break;
						}
						case 'disabled':
						{
							$urlResume = $txt_intro_enable.' <a href="'.$urlEnable.'">'.$txtEnable.'</a><br />';
							$urlResume .= $txt_intro_suspend.' <a href="'.$urlSuspend.'">'.$txtSuspend.'</a>';
							nlTemplate::assign('txtResume', __('Your account is disabled.'));
							break;
						}
						case 'enabled':
						{
							$urlResume = $txt_intro_disable.' <a href="'.$urlDisable.'">'.$txtDisable.'</a><br />';
							$urlResume .= $txt_intro_suspend.' <a href="'.$urlSuspend.'">'.$txtSuspend.'</a>';
							nlTemplate::assign('txtResume', __('Your account is enabled.'));
							break;
						}
						case 'pending':
						{
							$urlResume = $txt_intro_disable.' <a href="'.$urlDisable.'">'.$txtDisable.'</a><br />';
							$urlResume .= $txt_intro_confirm.' <a href="'.$urlConfirm.'">'.$txtConfirm.'</a>';
							nlTemplate::assign('txtResume', __('Your account is pending confirmation.'));
							break;
						}
						default:
						{
						}
					}
 
					// définition du format d'envoi
					if (!newsletterPlugin::getUseDefaultFormat() && $subscriber->modesend != null) {
						$mode = $subscriber->modesend;
					}

					if($mode == 'text') {
						$convert = new html2text();
						$convert->set_html($urlResume);
						$urlResume = $convert->get_text();
						/*
						$urlResume = str_replace("<br />", "\n", $urlResume);
						$urlResume = str_replace("<a href", "<url", $urlResume);
						$urlResume = str_replace("</a>", "", $urlResume);
						//*/
					} 
					
					nlTemplate::assign('txtMode', __('Your sending mode is'). ' ' .__(''.$mode.''). '.');
					nlTemplate::assign('urlResume', $urlResume);
					$body = nlTemplate::render('resume', $mode);
					
					// envoi du mail et log
					if (self::Sendmail($editorEmail, $editorName, $subscriber->email, $subject, $body, $mode))
					{
						$send_ok[] = $subscriber->email;
						$states[] = $subscriber_id;
					}
					else $send_error[] = $subscriber->email;
				}
               	/*
				$msg = '';
				$msg = 'Not yet available ...';
				//*/
			
				if (count($send_ok) > 0) $msg .= __('Successful mail sent for').' '.implode(', ', $send_ok);
				if (count($send_ok) > 0 && count($send_error) > 0) $msg .= '<br />';
				if (count($send_error) > 0) $msg .= __('Mail sent error for').' '.implode(', ', $send_error);

				return $msg;
			}
			catch (Exception $e) { 
				$core->error->add($e->getMessage()); 
			}
		}
	}

	/**
	* envoi de la notification de changement de format
	*/
	static function sendChangeMode($id = -1)
	{
		// test si le plugin est actif
		if (!newsletterPlugin::isActive()) 
			return false;

		// test sur la valeur de l'id qui doit être positive ou null
		else if ($id == -1) 
			return false;

		// envoi des mails aux abonnés
		else
		{
			global $core;
			try
			{
				$url = &$core->url;
				$blog = &$core->blog;
				$blogurl = &$blog->url;

				// prise en compte du paramètres: liste d'id ou id simple
                	if (is_array($id)) 
                		$ids = $id;
                	else { 
                		$ids = array(); $ids[] = $id; 
                	}

				// initialisation du moteur de template
				$send_ok = array();
				$send_error = array();
				$states = array();
				self::BeforeSendmailTo(__('Newsletter account change format for'), __('Have a nice day !'));

				// initialisation des variables de travail
				$blogname = &$blog->name;
				$editorName = newsletterPlugin::getEditorName();
				$editorEmail = newsletterPlugin::getEditorEmail();
				$mode = newsletterPlugin::getSendMode();
				$subject = text::toUTF8(__('Newsletter account change format for').' '.$blogname);

				// boucle sur les ids des abonnés à mailer
				foreach ($ids as $subscriber_id)
				{
					// récupération de l'abonné et extraction des données
					$subscriber = self::get($subscriber_id);

					// définition du format d'envoi
					if (!newsletterPlugin::getUseDefaultFormat() && $subscriber->modesend != null) {
						$mode = $subscriber->modesend;
					}					
					
					// génération du rendu
					nlTemplate::assign('urlEnable', self::url('enable/'.str_replace('=','',base64_encode($subscriber->email))));

					$body = nlTemplate::render('changemode', $mode);

					// envoi du mail et log
					if (self::Sendmail($editorEmail, $editorName, $subscriber->email, $subject, $body, $mode))
					{
						$send_ok[] = $subscriber->email;
						$states[] = $subscriber_id;
					}
					else 
						$send_error[] = $subscriber->email;
				}

				$msg = '';
				if (count($send_ok) > 0) $msg .= __('Successful mail sent for').' '.implode(', ', $send_ok);
				if (count($send_ok) > 0 && count($send_error) > 0) $msg .= '<br />';
				if (count($send_error) > 0) $msg .= __('Mail sent error for').' '.implode(', ', $send_error);

				return $msg;
			}
			catch (Exception $e) { $core->error->add($e->getMessage()); }
		}
	}

	/* ==================================================
		gestion des comptes
	================================================== */

	/**
	* création du compte
	*/
	static function accountCreate($email = null, $regcode = null, $modesend = null)
	{
		
		if (!newsletterPlugin::isActive()) {	// test si le plugin est actif
			return __('Newsletter is disabled.');
		} else if ($email == null) { 			// l'email doit être renseigné
			return __('Bad email !');
		} else {							// création du compte
			global $core;
			try {
			   if (self::getemail($email) != null) {
			   	return __('Email already exist !');
			   } else if (!self::add($email, null, null, $modesend)) {
			   	return __('Error creating account !');
			   } else {
				   $subscriber = self::getemail($email);
				   return self::sendConfirm($subscriber->subscriber_id);
				}
			} catch (Exception $e) { 
				$core->error->add($e->getMessage()); 
			}
		}
	}

	/**
	* suppression du compte
	*/
	static function accountDelete($email = null)
	{
		// test si le plugin est actif
		if (!newsletterPlugin::isActive()) 
			return __('Newsletter is disabled.');
		// l'email doit être renseigné
		else if ($email == null) 
			return __('Bad email !');
		// création du compte
		else {
			global $core;
			try {
				$subscriber = self::getemail($email);
				$msg = null;
				if (!$subscriber || $subscriber->subscriber_id == null) 
					return __('Email don\'t exist !');
				else {
					$msg = self::sendDisable($subscriber->subscriber_id);
					self::delete($subscriber->subscriber_id);
					return $msg;
				}
			} catch (Exception $e) { 
				$core->error->add($e->getMessage()); 
			}
		}
	}

	/**
	* suspension du compte
	*/
	static function accountSuspend($email = null)
	{
		if (!newsletterPlugin::isActive()) { // test si le plugin est actif
			return __('Newsletter is disabled.');
		} else if ($email == null) { // l'email doit être renseigné
			return __('Bad email !');
		} else { // suspension du compte
			global $core;
			try {
				$subscriber = self::getemail($email);
					$msg = null;
				if (!$subscriber || $subscriber->subscriber_id == null) 
					return __('Email don\'t exist !');
				else {
					$msg = self::sendSuspend($subscriber->subscriber_id);
					self::suspend($subscriber->subscriber_id);
					return $msg;
				}
			} catch (Exception $e) { 
				$core->error->add($e->getMessage()); 
			}
		}
	}

	/**
	* information sur le compte
	*/
	static function accountResume($email = null)
	{
		if (!newsletterPlugin::isActive()) { // test si le plugin est actif
			return __('Newsletter is disabled.');
		} else if ($email == null) { // l'email doit être renseigné
			return __('Bad email !');
		} else { // information sur le compte
			global $core;
			try {
				$subscriber = self::getemail($email);
					$msg = null;
				if (!$subscriber || $subscriber->subscriber_id == null) 
					return __('Email don\'t exist !');
				else {
					$msg = self::sendResume($subscriber->subscriber_id);
					//self::resume($subscriber->subscriber_id);
					return $msg;
				}
			} catch (Exception $e) { 
				$core->error->add($e->getMessage()); 
			}
		}
	}

	/**
	* changement du format sur le compte
	*/
	static function accountChangeMode($email = null, $modesend = null)
	{
		if (!newsletterPlugin::isActive()) { // test si le plugin est actif
			return __('Newsletter is disabled.');
		} else if ($email == null) { // l'email doit être renseigné
			return __('Bad email !');
		} else { // information sur le compte
			global $core;
			try {
				$subscriber = self::getemail($email);
					$msg = null;
				if (!$subscriber || $subscriber->subscriber_id == null) 
					return __('Email don\'t exist !');
				else {
					$msg = self::sendChangeMode($subscriber->subscriber_id);
					self::changeMode($subscriber->subscriber_id, $modesend);
					return $msg;
				}
			} catch (Exception $e) { 
				$core->error->add($e->getMessage()); 
			}
		}
	}

	// use by : NewsletterFormRandom
	public static function getRandom()
	{
		list($usec, $sec) = explode(' ', microtime());
		$seed = (float) $sec + ((float) $usec * 100000);
		mt_srand($seed);
		return mt_rand();
	}

}

?>
