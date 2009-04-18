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
require dirname(__FILE__).'/class.newsletter.mailing.php';

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
			try {
				$blog = &$core->blog;
				$con = &$core->con;
				$blogid = (string)$blog->id;

				// mise en forme du tableau d'id
                	if (is_array($id)) 
                		$ids = implode(", ", $id);
				else 
					$ids = $id;

				// requète sur les données et renvoi null si erreur
				$strReq =
	    			'SELECT subscriber_id,email,regcode,state,subscribed,lastsent,modesend' .
	    			' FROM '.$core->prefix.newsletterPlugin::pname().
	    			' WHERE blog_id=\''.$blogid.'\' AND subscriber_id IN('.$ids.')';

				$rs = $con->select($strReq);
				if ($rs->isEmpty()) 
					return null;
				else 
					return $rs;
			} catch (Exception $e) { 
				$core->error->add($e->getMessage()); 
			}
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
					$_regcode = newsletterTools::regcode();
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

			// requête sur les données et renvoi null si erreur
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
	public static function getPosts($dt=null)
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
			if ($maxPost > 0) {
				$params['limit'] = $maxPost;
			}

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

			/*			
			$year = dt::dt2str('%Y', $dt);
			$month = dt::dt2str('%m', $dt);
			$day = dt::dt2str('%d', $dt);
			$hours = dt::dt2str('%H', $dt);
			$minutes = dt::dt2str('%M', $dt);
			$seconds = dt::dt2str('%S', $dt);

			// depuis lastsent
			$params['sql'] .= ' AND '.$con->dateFormat('P.post_dt','%Y-%m-%d %H:%M:%S')."> '$year-$month-$day $hours:$minutes:$seconds'";
			*/

			// récupération des billets
			$rs = $blog->getPosts($params, false);
			
			$minPosts = newsletterPlugin::getMinPosts();
            	if($rs->count() < $minPosts)
            		return null;
            	else 
            		return($rs->isEmpty()?null:$rs);

		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	public static function getNewsletterPosts()
	{
		global $core;

		// boucle sur les billets concernés pour l'abonnés
		$bodies = array();
		$posts = array();

		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}			
	
		$posts = self::getPosts();
		
		//$posts->core = $core;
		//$posts->moveStart();
		while ($posts->fetch())
		{
			//$p_ids[] = $posts->post_id;

			// récupération des informations du billet
			if(newsletterPlugin::getViewContentPost()) {
				$bodies[] = array(
					'title' => $posts->post_title,
					'url' => $posts->getURL(),
					'date' => $posts->getDate($format),
					'category' => $posts->getCategoryURL(),
					'content' => html::escapeHTML(newsletterTools::cutString(html::decodeEntities(html::clean($posts->getContent())),newsletterPlugin::getSizeContentPost())),
					'author' => $posts->getAuthorCN(),
					'post_dt' => $posts->post_dt
					);
			} else {
				$bodies[] = array(
					'title' => $posts->post_title,
					'url' => $posts->getURL(),
					'date' => $posts->getDate($format),
					'category' => $posts->getCategoryURL(),
					'content' => html::escapeHTML(''),
					'author' => $posts->getAuthorCN(),
					'post_dt' => $posts->post_dt
					);
			}
		}
		return $bodies;
	}

	public function getUserPosts($posts=array(),$dt=null)
	{
		$bodies = array();
		foreach ($posts as $k => $v) {
			if($dt < $v['post_dt']) {
				$bodies[] = $posts[$k];
			}
		}
		
		return $bodies;
	}

	/* ==================================================
		emails
	================================================== */

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

			if ($cmd == '') 
				return http::concatURL($blogurl, $url->getBase('newsletter'));
			else 
				return http::concatURL($blogurl, $url->getBase('newsletter')).'/'.$cmd;
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

			if(newsletterPlugin::getCheckUseSuspend()) {
				nlTemplate::assign('txt_intro_suspend', newsletterPlugin::getTxtIntroSuspend().', ');
				nlTemplate::assign('txtSuspend', newsletterPlugin::getTxtSuspend());
				nlTemplate::assign('txtSuspended', __('Your account has been suspended.'));
			} else {
				nlTemplate::assign('txt_intro_suspend', ' ');
				nlTemplate::assign('txtSuspend', ' ');
				nlTemplate::assign('txtSuspended', ' ');
			}
			
			nlTemplate::assign('txtSubscribed', __('Thank you for your subscription.'));
			nlTemplate::assign('txtDisabled', __('Your account has been canceled.'));
			nlTemplate::assign('txtEnabled', __('Your account has been validated.'));
			nlTemplate::assign('txtChangingMode', __('Your sending format has been updated.'));
			nlTemplate::assign('txtBy', __(', by'));
			nlTemplate::assign('txtMsgPresentationForm', newsletterPlugin::getMsgPresentationForm());
		}
		catch (Exception $e) { $core->error->add($e->getMessage()); }
	}



	/**
	 * Prepare la liste des messages et declenche l'envoi de cette liste.
	 * Retourne les resultats des envois dans un string
	 *
	 * @param:	$id			array
	 * @param:	$action		string
	 *
	 * @return:	string
	 */
	public function send($id=-1,$action=null)
	{
		global $core;

		$url = &$core->url;
		$blog = &$core->blog;
		$blogurl = &$blog->url;

		$send = array();

		try {
			if (!newsletterPlugin::isActive()) { 		// test si le plugin est actif
				return false;
			} else if ($id == -1 || $action === null) { 	// test sur la valeur de l'id qui doit être positive ou null
				return false;
			} else {								// envoi des mails aux abonnés

				// prise en compte du paramètres: liste d'id ou id simple
				if (is_array($id)) {
					$ids = $id;
				} else { 
					$ids = array(); 
					$ids[] = $id; 
				}
		
				$newsletter_mailing = new newsletterMailing($core);		

				// filtrage sur le type de mail
				switch ($action) {
					case 'newsletter':
						self::prepareMessagesNewsletter($ids,$newsletter_mailing);
						break;
					case 'confirm':
						self::prepareMessagesConfirm($ids,$newsletter_mailing);
						break;
					case 'suspend':
						self::prepareMessagesSuspend($ids,$newsletter_mailing);
						break;
					case 'enable':
						self::prepareMessagesEnable($ids,$newsletter_mailing);
						break;
					case 'disable':
						self::prepareMessagesDisable($ids,$newsletter_mailing);
						break;
					case 'resume':
						self::prepareMessagesResume($ids,$newsletter_mailing);
						break;
					case 'changemode':
						self::prepareMessagesChangeMode($ids,$newsletter_mailing);
						break;
					default:
						return false;
				}

				// Envoi des messages
				$newsletter_mailing->batchSend();
				
				$sent_states = $newsletter_mailing->getStates();
				$sent_success = $newsletter_mailing->getSuccess();
				$sent_errors = $newsletter_mailing->getErrors();
				$sent_nothing = $newsletter_mailing->getNothingToSend();
				
				if (is_array($sent_states) && count($sent_states) > 0) {
					// positionnement de l'état des comptes
					switch ($action) {
						case 'newsletter':
							self::lastsent($sent_states);
							break;
						case 'confirm':
							self::confirm($sent_states);
							break;
						case 'suspend': 
							self::suspend($sent_states);
                    			break;
						case 'enable': 
							self::enable($sent_states);
                    			break;
						case 'disable': 
							self::disable($sent_states);
                    			break;
						case 'resume':
                    			break;
						case 'changemode':
                    			break;
					}
				}		
                
				$msg = '';
				
				if (isset($sent_success) && count($sent_success) > 0) 
					$msg .= __('Successful mail sent for').' '.implode(', ', $sent_success).'<br />';

				if (isset($sent_errors) && count($sent_errors) > 0) 
					$msg .= __('Mail sent error for').' '.implode(', ', $sent_errors).'<br />';

				if (isset($sent_nothing) &&count($sent_nothing) > 0) 
					$msg .= __('Nothing to send for').' '.implode(', ', $sent_nothing).'<br />';
				
				return $msg;
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	 * Prepare le contenu des messages de type newsletter
	 * Modifie l'objet newsletterMailing fourni en parametre
	 *
	 * @param:	$ids					array
	 * @param:	$newsletter_mailing		newsletterMailing
	 *
	 * @return:	boolean
	 */
	private static function prepareMessagesNewsletter($ids=-1,&$newsletter_mailing)
	{
		// initialisation des variables de travail
		$mode = newsletterPlugin::getSendMode();
		$subject = text::toUTF8(newsletterPlugin::getNewsletterSubject());
		$minPosts = newsletterPlugin::getMinPosts();
				
		// initialisation du moteur de template
		self::BeforeSendmailTo(newsletterPlugin::getPresentationMsg(), newsletterPlugin::getConcludingMsg());
				
		// recuperation des billets
		$newsletter_posts = self::getNewsletterPosts();

		// boucle sur les ids des abonnés
		foreach ($ids as $subscriber_id)
		{
			// récupération de l'abonné et extraction des données
			$subscriber = self::get($subscriber_id);

			// récupération des billets en fonction de l'abonné (date de dernier envoi)
			$user_posts = self::getUserPosts($newsletter_posts,$subscriber->lastsent);
		
			if(count($user_posts) < $minPosts) {
				$newsletter_mailing->addNothingToSend($subscriber_id,$subscriber->email);
			} else {
				$body = '';
				/*$convert = new html2text();
				$convert->labelLinks = __('Links:');
				$convert->set_base_url($blogurl);*/
						
				// définition du format d'envoi
				if (!newsletterPlugin::getUseDefaultFormat() && $subscriber->modesend != null) {
					$mode = $subscriber->modesend;
				}
						
				// intégration dans le template des billets en génération du rendu
				if(newsletterPlugin::getCheckUseSuspend()) {
					nlTemplate::assign('urlSuspend', self::url('suspend/'.newsletterTools::base64_url_encode($subscriber->email)));
				} else {
					nlTemplate::assign('urlSuspend', ' ');
				}
				nlTemplate::assign('urlDisable', self::url('disable/'.newsletterTools::base64_url_encode($subscriber->email)));
				nlTemplate::assign('posts', $user_posts);

				$body = nlTemplate::render('newsletter', $mode);
						
				if($mode == 'text') {
					$convert = new html2text();
					$convert->set_html($body);
					$convert->labelLinks = __('Links:');
					$body = $convert->get_text();
				}
						
				// ajoute le message dans la liste d'envoi
				$newsletter_mailing->addMessage($subscriber_id,$subscriber->email,$subject,$body,$mode);
   			}
		}
		return true;
	}

	/**
	 * Prepare le contenu des messages de type confirm
	 * Modifie l'objet newsletterMailing fourni en parametre
	 *
	 * @param:	$ids					array
	 * @param:	$newsletter_mailing		newsletterMailing
	 *
	 * @return:	boolean
	 */
	private static function prepareMessagesConfirm($ids=-1,&$newsletter_mailing)
	{
		// initialisation des variables de travail
		$mode = newsletterPlugin::getSendMode();
		$subject = text::toUTF8(newsletterPlugin::getConfirmSubject());

		// initialisation du moteur de template
		self::BeforeSendmailTo(__('Newsletter subscription confirmation for'), __('Thanks you for subscribing.'));

		// boucle sur les ids des abonnés
		foreach ($ids as $subscriber_id)
		{
			$body = '';
			// récupération de l'abonné et extraction des données
			$subscriber = self::get($subscriber_id);

			// définition du format d'envoi
			if (!newsletterPlugin::getUseDefaultFormat() && $subscriber->modesend != null) {
				$mode = $subscriber->modesend;
			}

			// génération du rendu
			nlTemplate::assign('urlConfirm', self::url('confirm/'.newsletterTools::base64_url_encode($subscriber->email).'/'.$subscriber->regcode.'/'.newsletterTools::base64_url_encode($subscriber->modesend)));
			nlTemplate::assign('urlDisable', self::url('disable/'.newsletterTools::base64_url_encode($subscriber->email)));

			$body = nlTemplate::render('confirm', $mode);

			if($mode == 'text') {
				$convert = new html2text();
				$convert->set_html($body);
				$convert->labelLinks = __('Links:');
				$body = $convert->get_text();
			}

			// ajoute le message dans la liste d'envoi
			$newsletter_mailing->addMessage($subscriber_id,$subscriber->email,$subject,$body,$mode);
		}
		return true;
	}

	/**
	 * Prepare le contenu des messages de type suspend
	 * Modifie l'objet newsletterMailing fourni en parametre
	 *
	 * @param:	$ids					array
	 * @param:	$newsletter_mailing		newsletterMailing
	 *
	 * @return:	boolean
	 */
	private static function prepareMessagesSuspend($ids=-1,&$newsletter_mailing)
	{
		// initialisation des variables de travail
		$mode = newsletterPlugin::getSendMode();
		$subject = text::toUTF8(newsletterPlugin::getSuspendSubject());

		// initialisation du moteur de template
		self::BeforeSendmailTo(__('Newsletter account suspend for'), __('Have a nice day !'));

		// boucle sur les ids des abonnés
		foreach ($ids as $subscriber_id)
		{
			// récupération de l'abonné et extraction des données
			$subscriber = self::get($subscriber_id);

			// définition du format d'envoi
			if (!newsletterPlugin::getUseDefaultFormat() && $subscriber->modesend != null) {
				$mode = $subscriber->modesend;
			}

			// génération du rendu
			nlTemplate::assign('urlEnable', self::url('enable/'.newsletterTools::base64_url_encode($subscriber->email)));

			$body = nlTemplate::render('suspend', $mode);
			
			if($mode == 'text') {
				$convert = new html2text();
				$convert->set_html($body);
				$convert->labelLinks = __('Links:');
				$body = $convert->get_text();
			}

			// ajoute le message dans la liste d'envoi
			$newsletter_mailing->addMessage($subscriber_id,$subscriber->email,$subject,$body,$mode);
		}
		return true;
	}

	/**
	 * Prepare le contenu des messages de type enable
	 * Modifie l'objet newsletterMailing fourni en parametre
	 *
	 * @param:	$ids					array
	 * @param:	$newsletter_mailing		newsletterMailing
	 *
	 * @return:	boolean
	 */
	private static function prepareMessagesEnable($ids=-1,&$newsletter_mailing)
	{
		// initialisation des variables de travail
		$mode = newsletterPlugin::getSendMode();
		$subject = text::toUTF8(newsletterPlugin::getEnableSubject());

		// initialisation du moteur de template
		self::BeforeSendmailTo(__('Newsletter account activation for'), __('Thank you for subscribing.'));

		// boucle sur les ids des abonnés
		foreach ($ids as $subscriber_id)
		{
			// récupération de l'abonné et extraction des données
			$subscriber = self::get($subscriber_id);

			// définition du format d'envoi
			if (!newsletterPlugin::getUseDefaultFormat() && $subscriber->modesend != null) {
				$mode = $subscriber->modesend;
			}

			// génération du rendu
			nlTemplate::assign('urlDisable', self::url('disable/'.newsletterTools::base64_url_encode($subscriber->email)));
				if(newsletterPlugin::getCheckUseSuspend()) {
					nlTemplate::assign('urlSuspend', self::url('suspend/'.newsletterTools::base64_url_encode($subscriber->email)));
				} else {
					nlTemplate::assign('urlSuspend', ' ');
				}

			$body = nlTemplate::render('enable', $mode);

			if($mode == 'text') {
				$convert = new html2text();
				$convert->set_html($body);
				$convert->labelLinks = __('Links:');
				$body = $convert->get_text();
			}
		
			// ajoute le message dans la liste d'envoi
			$newsletter_mailing->addMessage($subscriber_id,$subscriber->email,$subject,$body,$mode);
		}
		return true;
	}

	/**
	 * Prepare le contenu des messages de type disable
	 * Modifie l'objet newsletterMailing fourni en parametre
	 *
	 * @param:	$ids					array
	 * @param:	$newsletter_mailing		newsletterMailing
	 *
	 * @return:	boolean
	 */
	private static function prepareMessagesDisable($ids=-1,&$newsletter_mailing)
	{
		// initialisation des variables de travail
		$mode = newsletterPlugin::getSendMode();
		$subject = text::toUTF8(newsletterPlugin::getDisableSubject());

		// initialisation du moteur de template
		self::BeforeSendmailTo(__('Newsletter account removal for'), __('Have a nice day !'));

		// boucle sur les ids des abonnés
		foreach ($ids as $subscriber_id)
		{
			// récupération de l'abonné et extraction des données
			$subscriber = self::get($subscriber_id);

			// définition du format d'envoi
			if (!newsletterPlugin::getUseDefaultFormat() && $subscriber->modesend != null) {
				$mode = $subscriber->modesend;
			}

			// génération du rendu
			nlTemplate::assign('urlEnable', self::url('enable/'.newsletterTools::base64_url_encode($subscriber->email)));

			$body = nlTemplate::render('disable', $mode);

			if($mode == 'text') {
				$convert = new html2text();
				$convert->set_html($body);
				$convert->labelLinks = __('Links:');
				$body = $convert->get_text();
			}
		
			// ajoute le message dans la liste d'envoi
			$newsletter_mailing->addMessage($subscriber_id,$subscriber->email,$subject,$body,$mode);
		}
		return true;
	}

	/**
	 * Prepare le contenu des messages de type resume
	 * Modifie l'objet newsletterMailing fourni en parametre
	 *
	 * @param:	$ids					array
	 * @param:	$newsletter_mailing		newsletterMailing
	 *
	 * @return:	boolean
	 */
	private static function prepareMessagesResume($ids=-1,&$newsletter_mailing)
	{
		// initialisation des variables de travail
		$mode = newsletterPlugin::getSendMode();
		$subject = text::toUTF8(newsletterPlugin::getResumeSubject());

		// initialisation du moteur de template
		self::BeforeSendmailTo(__('Newsletter account resume for'), __('Have a nice day !'));

		// boucle sur les ids des abonnés
		foreach ($ids as $subscriber_id)
		{
			// récupération de l'abonné et extraction des données
			$subscriber = self::get($subscriber_id);

			$txt_intro_enable = newsletterPlugin::getTxtIntroEnable().', ';
			$urlEnable = self::url('enable/'.newsletterTools::base64_url_encode($subscriber->email));
			$txtEnable = newsletterPlugin::getTxtEnable();
					
			$txt_intro_disable = newsletterPlugin::getTxtIntroDisable().', ';
			$urlDisable = self::url('disable/'.newsletterTools::base64_url_encode($subscriber->email));
			$txtDisable = newsletterPlugin::getTxtDisable();

			$txt_intro_suspend = newsletterPlugin::getTxtIntroSuspend().', ';
			$urlSuspend = self::url('suspend/'.newsletterTools::base64_url_encode($subscriber->email));
			$txtSuspend = newsletterPlugin::getTxtSuspend();
					
			$txt_intro_confirm = newsletterPlugin::getTxtIntroConfirm().', ';
			$urlConfirm = self::url('confirm/'.newsletterTools::base64_url_encode($subscriber->email).'/'.$subscriber->regcode.'/'.newsletterTools::base64_url_encode($subscriber->modesend));
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
					if(newsletterPlugin::getCheckUseSuspend()) {
						$urlResume .= $txt_intro_suspend.' <a href="'.$urlSuspend.'">'.$txtSuspend.'</a>';
					}
					nlTemplate::assign('txtResume', __('Your account is disabled.'));
					break;
				}
				case 'enabled':
				{
					$urlResume = $txt_intro_disable.' <a href="'.$urlDisable.'">'.$txtDisable.'</a><br />';
					if(newsletterPlugin::getCheckUseSuspend()) {
						$urlResume .= $txt_intro_suspend.' <a href="'.$urlSuspend.'">'.$txtSuspend.'</a>';
					}
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

			nlTemplate::assign('txtMode', __('Your sending mode is'). ' ' .__(''.$mode.''). '.');
			nlTemplate::assign('urlResume', $urlResume);
			$body = nlTemplate::render('resume', $mode);

			if($mode == 'text') {
				$convert = new html2text();
				$convert->set_html($body);
				$convert->labelLinks = __('Links:');
				$body = $convert->get_text();
			}

			// ajoute le message dans la liste d'envoi
			$newsletter_mailing->addMessage($subscriber_id,$subscriber->email,$subject,$body,$mode);
		}
		return true;
	}

	/**
	 * Prepare le contenu des messages de type changemode
	 * Modifie l'objet newsletterMailing fourni en parametre
	 *
	 * @param:	$ids					array
	 * @param:	$newsletter_mailing		newsletterMailing
	 *
	 * @return:	boolean
	 */
	private static function prepareMessagesChangeMode($ids=-1,&$newsletter_mailing)
	{
		// initialisation des variables de travail
		$mode = newsletterPlugin::getSendMode();
		$subject = text::toUTF8(newsletterPlugin::getChangeModeSubject());

		// initialisation du moteur de template
		self::BeforeSendmailTo(__('Newsletter account change format for'), __('Have a nice day !'));

		// boucle sur les ids des abonnés
		foreach ($ids as $subscriber_id)
		{
			// récupération de l'abonné et extraction des données
			$subscriber = self::get($subscriber_id);

			// définition du format d'envoi
			if (!newsletterPlugin::getUseDefaultFormat() && $subscriber->modesend != null) {
				$mode = $subscriber->modesend;
			}					
					
			// génération du rendu
			nlTemplate::assign('urlEnable', self::url('enable/'.newsletterTools::base64_url_encode($subscriber->email)));

			$body = nlTemplate::render('changemode', $mode);

			if($mode == 'text') {
				$convert = new html2text();
				$convert->set_html($body);
				$convert->labelLinks = __('Links:');
				$body = $convert->get_text();
			}

			// ajoute le message dans la liste d'envoi
			$newsletter_mailing->addMessage($subscriber_id,$subscriber->email,$subject,$body,$mode);
		}
		return true;
	}

	/**
	 * Envoi automatique de la newsletter pour tous les abonnés actifs
	 *
	 * @return:	boolean
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
				$ids = array();
				$datas->moveStart();
               	while ($datas->fetch()) { 
               		$ids[] = $datas->subscriber_id;
               	}
				self::send($ids,'newsletter');
			}            	
		}	
	}

	/**
	 * Envoi par tâche planifiée de la newsletter pour tous les abonnés actifs
	 *
	 * @return:	boolean
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
				$ids = array();
				$datas->moveStart();
               	while ($datas->fetch()) { 
               		$ids[] = $datas->subscriber_id;
               	}
				self::send($ids,'newsletter');
			}
		}
	}

	/* ==================================================
		gestion des comptes
	================================================== */

	/**
	* création du compte
	*/
	public static function accountCreate($email = null, $regcode = null, $modesend = null)
	{
		global $core;
		try {		
			if ($email == null) { 			// l'email doit être renseigné
				return __('Bad email !');
			} else {						// création du compte
				if (self::getemail($email) != null) {
					return __('Email already exist !');
				} else if (!self::add($email, null, null, $modesend)) {
					return __('Error creating account !');
				} else {
					$subscriber = self::getemail($email);
					$msg = self::send($subscriber->subscriber_id,'confirm');
					return $msg;
				}
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* suppression du compte
	*/
	public static function accountDelete($email = null)
	{
		global $core;
		try {		
			if ($email == null) { 			// l'email doit être renseigné
				return __('Bad email !');
			} else { 						// suppression du compte
				$subscriber = self::getemail($email);
				$msg = null;
				if (!$subscriber || $subscriber->subscriber_id == null) 
					return __('Email don\'t exist !');
				else {
					$msg = self::send($subscriber->subscriber_id,'disable');
					self::delete($subscriber->subscriber_id);
					return $msg;
				}
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* suspension du compte
	*/
	static function accountSuspend($email = null)
	{
		global $core;
		try {

			if ($email == null) { 			// l'email doit être renseigné
				return __('Bad email !');
			} else { 						// suspension du compte
				$subscriber = self::getemail($email);
				$msg = '';
				if (!$subscriber || $subscriber->subscriber_id == null) 
					return __('Email don\'t exist !');
				else {
					$msg = self::send($subscriber->subscriber_id,'suspend');					
					self::suspend($subscriber->subscriber_id);
					return $msg;
				}
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* information sur le compte
	*/
	public static function accountResume($email = null)
	{
		global $core;
		try {		
			if ($email == null) { 			// l'email doit être renseigné
				return __('Bad email !');
			} else { 						// information sur le compte
				$subscriber = self::getemail($email);
				$msg = '';
				if (!$subscriber || $subscriber->subscriber_id == null) 
					return __('Email don\'t exist !');
				else {
					$msg = self::send($subscriber->subscriber_id,'resume');					
					//self::resume($subscriber->subscriber_id);
					return $msg;
				}
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}		
	}

	/**
	* changement du format sur le compte
	*/
	public static function accountChangeMode($email = null, $modesend = null)
	{
		global $core;
		try {
			if ($email == null) { 			// l'email doit être renseigné
				return __('Bad email !');
			} else { 						// information sur le compte
				$subscriber = self::getemail($email);
				$msg = '';
				if (!$subscriber || $subscriber->subscriber_id == null) 
					return __('Email don\'t exist !');
				else {
					$msg = self::send($subscriber->subscriber_id,'changemode');					
					self::changeMode($subscriber->subscriber_id, $modesend);
					return $msg;
				}
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

}

?>
