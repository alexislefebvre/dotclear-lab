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
		return (string)"newsletter"; 
	}

	/**
	* renvoi le nom de l'éditeur du blog
	*/
	public static function getEditorName() 
	{ 
		return (string)self::get('editorName'); 
	}
	
	/**
	* renseigne le nom de l'éditeur
	*/
	public static function setEditorName($val) 
	{ 
		self::setS('editorName', (string)$val, 'Name of publisher'); 
	}
	
	/**
	* efface/initialise le nom de l'éditeur
	*/
	public static function clearEditorName() 
	{ 
		self::setEditorName(''); 
	}
	
	/**
	* renvoi l'email de l'éditeur du blog
	*/
	public static function getEditorEmail() 
	{ 
		return (string)self::get('editorEmail'); 
	}
	
	/**
	* renseigne l'email de l'éditeur
	*/
	public static function setEditorEmail($val) 
	{ 
		self::setS('editorEmail', (string)$val, 'Email of publisher'); 
	}
	
	/**
	* efface/initialise l'email de l'éditeur
	*/
	public static function clearEditorEmail() 
	{ 
		self::setEditorEmail(''); 
	}

	/**
	* renvoi le mode d'envoi de la newsletter
	*/
	public static function getSendMode() 
	{ 
		return (string)self::get('mode'); 
	}
	
	/**
	* renseigne le mode d'envoi de la newsletter
	*/
	public static function setSendMode($val) 
	{ 
		self::setS('mode', (string)$val, 'Format of the newsletter'); 
	}
	
	/**
	* efface/initialise le mode d'envoi de la newsletter
	*/
	public static function clearSendMode() 
	{ 
		self::setSendMode('html'); 
	}
	
	/**
	* utilisation du format d'envoi par utilisateur ou défaut
	*/
	public static function getUseDefaultFormat() 
	{ 
		return (boolean)self::get('use_default_format'); 
	}
	
	public static function setUseDefaultFormat($val) 
	{ 
		self::setB('use_default_format', (boolean)$val, 'Use global format for sending'); 
	}
	
	public static function clearUseDefaultFormat() 
	{ 
		self::setUseDefaultFormat(false); 
	}	
	
	/**
	* nombre maximal de billet retournés
	*/
	public static function getMaxPosts() 
	{ 
		return (integer)self::get('maxposts'); 
	}
	
	/**
	* renseigne le nombre maximal de billet retournés
	*/
	public static function setMaxPosts($val) 
	{ 
		self::setI('maxposts', (integer)$val, 'Maximum number of posts returned'); 
	}
	
	/**
	* efface/initialise le nombre maximal de billet retournés
	*/
	public static function clearMaxPosts() 
	{ 
		self::setMaxPosts(15); 
	}
	
	/**
	* envoi automatique
	*/
	public static function getAutosend() 
	{ 
		return (boolean)self::get('autosend'); 
	}
	
	/**
	* indique si on doit envoyer automatiquement
	*/
	public static function setAutosend($val) 
	{ 
		self::setB('autosend', (boolean)$val, 'Enable automatic sending'); 
	}
	
	/**
	* réinitialise l'indicateur d'envoi automatique
	*/
	public static function clearAutosend() 
	{ 
		self::setAutosend(false); 
	}
		
	/**
	* utilisation d'un captcha
	*/
	public static function getCaptcha() 
	{ 
		return (boolean)self::get('captcha'); 
	}
	
	/**
	* indique si on doit utiliser un captcha
	*/
	public static function setCaptcha($val) 
	{ 
		self::setB('captcha', (boolean)$val, 'Enable captcha'); 
	}
	
	/**
	* réinitialise l'indicateur d'utilisation de captcha
	*/
	public static function clearCaptcha() 
	{ 
		self::setCaptcha(false); 
	}

	/**
	* Affichage du contenu du post dans la newsletter
	*/
	public static function getViewContentPost() 
	{ 
		return (boolean)self::get('view_content_post');
	}
	
	/**
	* indique si on doit afficher le contenu du post
	*/
	public static function setViewContentPost($val) 
	{ 
		self::setB('view_content_post', (boolean)$val, 'Enable view content post');
	}
	
	/**
	* réinitialise l'indicateur d'affichage du contenu du post
	*/
	public static function clearViewContentPost() 
	{ 
		self::setViewContentPost(true);
	}

	/**
	* retourne la taille maximale du contenu du billet
	*/
	public static function getSizeContentPost() 
	{ 
		return (integer)self::get('size_content_post'); 
	}
	
	/**
	* renseigne la taille maximale du contenu du billet
	*/
	public static function setSizeContentPost($val) 
	{ 
		self::setI('size_content_post', (integer)$val, 'The maximum size content post'); 
	}
	
    /**
	* efface/initialise la taille maximale du contenu du billet
	*/
	public static function clearSizeContentPost() 
	{ 
		self::setSizeContentPost(100); 
	}

	/**
	* retourne le message d'introduction de la newsletter
	*/
	public static function getIntroductoryMsg() 
	{ 
		return (string)self::get('introductory_msg'); 
	}
	
	/**
	* renseigne le message d'introduction de la newsletter
	*/
	public static function setIntroductoryMsg($val) 
	{ 
		self::setS('introductory_msg', (string)$val, 'Introductory message for newsletter'); 
	}
	
	/**
	* efface/initialise le message d'introduction de la newsletter
	*/
	public static function clearIntroductoryMsg() 
	{ 
		self::setIntroductoryMsg(''); 
	}

	/**
	* retourne le message de conclusion
	*/
	public static function getConcludingMsg()
	{ 
		return (string)self::get('concluding_msg');
	}
	
	/**
	* renseigne le message de conclusion
	*/
	public static function setConcludingMsg($val) 
	{ 
		self::setS('concluding_msg', (string)$val, 'Concluding message for newsletter'); 
	}
	
	/**
	* efface/initialise le message de conclusion
	*/
	public static function clearConcludingMsg() 
	{ 
		self::setConcludingMsg(__('Thanks you for reading.')); 
	}

	/**
	* retourne le message de présentation du formulaire d'inscription
	*/
	public static function getMsgPresentationForm() 
	{ 
		return (string)self::get('msg_presentation_form'); 
	}
	
	/**
	* renseigne le message de présentation du formulaire d'inscription
	*/
	public static function setMsgPresentationForm($val) 
	{ 
		self::setS('msg_presentation_form', (string)$val, 'Message presentation form'); 
	}
	
	/**
	* efface/initialise le message de présentation du formulaire d'inscription
	*/
	public static function clearMsgPresentationForm() 
	{ 
		self::setMsgPresentationForm(''); 
	}

	/**
	* retourne le message de présentation
	*/
	public static function getPresentationMsg()
	{ 
		return (string)self::get('presentation_msg');
	}
	
	/**
	* renseigne le message de présentation
	*/
	public static function setPresentationMsg($val) 
	{ 
		self::setS('presentation_msg', (string)$val, 'Presentation message for newsletter'); 
	}
	
	/**
	* efface/initialise le message de présentation
	*/
	public static function clearPresentationMsg() 
	{ 
		self::setPresentationMsg(__('This is the newsletter for')); 
	}

	/**
	* retourne le message de présentation des billets
	*/
	public static function getPresentationPostsMsg()
	{ 
		return (string)self::get('presentation_posts_msg');
	}
	
	/**
	* renseigne le message de présentation des billets
	*/
	public static function setPresentationPostsMsg($val) 
	{ 
		self::setS('presentation_posts_msg', (string)$val, 'Posts presentation message for newsletter'); 
	}
	
	/**
	* efface/initialise le message de présentation des billets
	*/
	public static function clearPresentationPostsMsg() 
	{ 
		self::setPresentationPostsMsg(__('Here are the last post:')); 
	}

	/**
	* retourne le message d'introduction pour la confirmation
	*/
	public static function getTxtIntroConfirm()
	{ 
		return (string)self::get('txt_intro_confirm');
	}

	public static function setTxtIntroConfirm($val) 
	{ 
		self::setS('txt_intro_confirm', (string)$val, 'Introductory confirm message'); 
	}

	public static function clearTxtIntroConfirm() 
	{ 
		self::setTxtIntroConfirm(__('To confirm you subscription')); 
	}	
	
	/**
	* retourne le titre du lien de confirmation
	*/
	public static function getTxtConfirm()
	{ 
		return (string)self::get('txtConfirm');
	}
	
	public static function setTxtConfirm($val) 
	{ 
		self::setS('txtConfirm', (string)$val, 'Title confirm link'); 
	}
	
	public static function clearTxtConfirm() 
	{ 
		self::setTxtConfirm(__('Click here')); 
	}	
	
	/**
	* retourne le message d'introduction pour la suppression
	*/
	public static function getTxtIntroDisable()
	{ 
		return (string)self::get('txt_intro_disable');
	}
	
	public static function setTxtIntroDisable($val) 
	{ 
		self::setS('txt_intro_disable', (string)$val, 'Introductory disable message'); 
	}
	
	public static function clearTxtIntroDisable() 
	{ 
		self::setTxtIntroDisable(__('To cancel your account')); 
	}	
	
	/**
	* retourne le titre du lien de suppression
	*/
	public static function getTxtDisable()
	{ 
		return (string)self::get('txtDisable');
	}
	
	public static function setTxtDisable($val) 
	{ 
		self::setS('txtDisable', (string)$val, 'Title disable link'); 
	}
	
	public static function clearTxtDisable() 
	{ 
		self::setTxtDisable(__('Click here')); 
	}	
	
	/**
	* retourne le message d'introduction pour l'activation
	*/
	public static function getTxtIntroEnable()
	{ 
		return (string)self::get('txt_intro_enable');
	}
	
	public static function setTxtIntroEnable($val) 
	{ 
		self::setS('txt_intro_enable', (string)$val, 'Introductory enable message'); 
	}
	
	public static function clearTxtIntroEnable() 
	{ 
		self::setTxtIntroEnable(__('To enable your account')); 
	}	
	
	/**
	* retourne le titre du lien de confirmation
	*/
	public static function getTxtEnable()
	{ 
		return (string)self::get('txtEnable');
	}
	
	public static function setTxtEnable($val) 
	{ 
		self::setS('txtEnable', (string)$val, 'Title enable link'); 
	}
	
	public static function clearTxtEnable() 
	{ 
		self::setTxtEnable(__('Click here')); 
	}	
	
	/**
	* retourne le message d'introduction pour la suspension
	*/
	public static function getTxtIntroSuspend()
	{ 
		return (string)self::get('txt_intro_suspend');
	}
	
	public static function setTxtIntroSuspend($val) 
	{ 
		self::setS('txt_intro_suspend', (string)$val, 'Introductory suspend message'); 
	}
	
	public static function clearTxtIntroSuspend() 
	{ 
		self::setTxtIntroSuspend(__('To suspend your account')); 
	}	
	
	/**
	* retourne le titre du lien de suspension
	*/
	public static function getTxtSuspend()
	{ 
		return (string)self::get('txtSuspend');
	}
	
	public static function setTxtSuspend($val) 
	{ 
		self::setS('txtSuspend', (string)$val, 'Title suspend link'); 
	}
	
	public static function clearTxtSuspend() 
	{ 
		self::setTxtSuspend(__('Click here')); 
	}

	/**
	* retourne le filtre sur la catégorie
	*/
	public static function getCategory() 
	{ 
		return (integer)self::get('category'); 
	}
	
	/**
	* renseigne le filtre sur la catégorie
	*/
	public static function setCategory($val) 
	{ 
		self::setI('category', (integer)$val, 'Filter by category'); 
	}
	
	/**
	* efface/initialise le filtre sur la catégorie
	*/
	public static function clearCategory() 
	{ 
		self::setCategory(null);
	}

	/**
	* retourne l'état de la planification
	*/
	public static function getCheckSchedule() 
	{ 
		return (boolean)self::get('check_schedule');
	}
	
	/**
	* indique si on doit utiliser la planification
	*/
	public static function setCheckSchedule($val) 
	{ 
		self::setB('check_schedule', (boolean)$val, 'Enable schedule');
	}
	
	/**
	* réinitialise l'indicateur de planification
	*/
	public static function clearCheckSchedule() 
	{ 
		self::setCheckSchedule(false);
	}

	/**
	* retourne l'état de la notification
	*/
	public static function getCheckNotification() 
	{ 
		return (boolean)self::get('check_notification');
	}
	
	/**
	* indique si on doit utiliser la notification
	*/
	public static function setCheckNotification($val) 
	{ 
		self::setB('check_notification', (boolean)$val, 'Enable notification');
	}
	
	/**
	* réinitialise l'indicateur de notification
	*/
	public static function clearCheckNotification()
	{ 
		self::setCheckNotification(false);
	}

	/**
	* retourne le titre de la newsletter
	*/
	public static function getNewsletterSubject()
	{ 
		return (string)self::get('newsletter_subject');
	}
	
	public static function setNewsletterSubject($val) 
	{ 
		self::setS('newsletter_subject', (string)$val, 'Subject of the mail Newsletter'); 
	}
	
	public static function clearNewsletterSubject() 
	{ 
		global $core;
		$blogname = &$core->blog->name;
		self::setNewsletterSubject(__('Newsletter for').' '.$blogname); 
	}

	/**
	* retourne le titre du mail de confirmation
	*/
	public static function getConfirmSubject()
	{ 
		return (string)self::get('confirm_subject');
	}
	
	public static function setConfirmSubject($val) 
	{ 
		self::setS('confirm_subject', (string)$val, 'Subject of the mail Confirm'); 
	}
	
	public static function clearConfirmSubject() 
	{ 
		global $core;
		$blogname = &$core->blog->name;
		self::setConfirmSubject(__('Newsletter subscription confirmation for').' '.$blogname); 
	}

	/**
	* retourne le titre du mail de suspension
	*/
	public static function getSuspendSubject()
	{ 
		return (string)self::get('suspend_subject');
	}
	
	public static function setSuspendSubject($val) 
	{ 
		self::setS('suspend_subject', (string)$val, 'Subject of the mail Suspend'); 
	}
	
	public static function clearSuspendSubject() 
	{ 
		global $core;
		$blogname = &$core->blog->name;
		self::setSuspendSubject(__('Newsletter account suspend for').' '.$blogname); 
	}

	/**
	* retourne le titre du mail d'activation
	*/
	public static function getEnableSubject()
	{ 
		return (string)self::get('enable_subject');
	}
	
	public static function setEnableSubject($val) 
	{ 
		self::setS('enable_subject', (string)$val, 'Subject of the mail Enable'); 
	}
	
	public static function clearEnableSubject() 
	{ 
		global $core;
		$blogname = &$core->blog->name;
		self::setEnableSubject(__('Newsletter account activation for').' '.$blogname);
	}

	/**
	* retourne le titre du mail de désactivation
	*/
	public static function getDisableSubject()
	{ 
		return (string)self::get('disable_subject');
	}
	
	public static function setDisableSubject($val) 
	{ 
		self::setS('disable_subject', (string)$val, 'Subject of the mail Disable'); 
	}
	
	public static function clearDisableSubject() 
	{ 
		global $core;
		$blogname = &$core->blog->name;
		self::setDisableSubject(__('Newsletter account removal for').' '.$blogname);
	}

	/**
	* retourne le titre du mail de résumé
	*/
	public static function getResumeSubject()
	{ 
		return (string)self::get('resume_subject');
	}
	
	public static function setResumeSubject($val) 
	{ 
		self::setS('resume_subject', (string)$val, 'Subject of the mail Resume'); 
	}
	
	public static function clearResumeSubject() 
	{ 
		global $core;
		$blogname = &$core->blog->name;
		self::setResumeSubject(__('Newsletter account resume for').' '.$blogname);
	}

	/**
	* retourne le titre du mail de changement de mode d'envoi
	*/
	public static function getChangeModeSubject()
	{ 
		return (string)self::get('change_mode_subject');
	}
	
	public static function setChangeModeSubject($val) 
	{ 
		self::setS('change_mode_subject', (string)$val, 'Subject of the mail Change Mode'); 
	}
	
	public static function clearChangeModeSubject() 
	{ 
		global $core;
		$blogname = &$core->blog->name;
		self::setChangeModeSubject(__('Newsletter account change format for').' '.$blogname);
	}

	/**
	* Utilisation de l'option Suspend
	*/
	public static function getCheckUseSuspend() 
	{ 
		return (boolean)self::get('check_use_suspend');
	}
	
	/**
	* indique si on doit utiliser la notification
	*/
	public static function setCheckUseSuspend($val) 
	{ 
		self::setB('check_use_suspend', (boolean)$val, 'Enable suspend option');
	}
	
	/**
	* réinitialise l'indicateur de notification
	*/
	public static function clearCheckUseSuspend()
	{ 
		self::setCheckUseSuspend(false);
	}

	/*
	* nombre minimum de billet retournés
	*/
	public static function getMinPosts() 
	{ 
		return (integer)self::get('minposts'); 
	}
	
	/**
	* renseigne le nombre minimal de billet retournés
	*/
	public static function setMinPosts($val) 
	{ 
		self::setI('minposts', (integer)$val, 'Minimum number of posts returned'); 
	}
	
	/**
	* efface/initialise le nombre maximal de billet retournés
	*/
	public static function clearMinPosts() 
	{ 
		self::setMinPosts(1); 
	}

	/**
	* retourne le titre de la page du formulaire
	*/
	public static function getFormTitlePage()
	{
		return (string)self::get('form_title_page');
	}
	
	public static function setFormTitlePage($val)
	{
		self::setS('form_title_page', (string)$val, 'Title page of the subscribe form');
	}
	
	public static function clearFormTitlePage()
	{
		self::setFormTitlePage(__('Newsletter'));
	}
	
	/**
	* initialise les paramètres par défaut
	*/
	public static function defaultsSettings()
	{
		
		if(!self::getEditorName()) self::clearEditorName();
		if(!self::getEditorEmail()) self::clearEditorEmail();
		if(!self::getMaxPosts()) self::clearMaxPosts();
		if(!self::getMinPosts()) self::clearMinPosts();
		if(!self::getAutosend()) self::clearAutosend();
		if(!self::getCaptcha()) self::clearCaptcha();
		if(!self::getViewContentPost()) self::clearViewContentPost();
		if(!self::getSizeContentPost()) self::clearSizeContentPost();
		if(!self::getIntroductoryMsg()) self::clearIntroductoryMsg();
		if(!self::getConcludingMsg()) self::clearConcludingMsg();
		if(!self::getPresentationMsg()) self::clearPresentationMsg();
		if(!self::getPresentationPostsMsg()) self::clearPresentationPostsMsg();
		if(!self::getTxtIntroConfirm()) self::clearTxtIntroConfirm();
		if(!self::getTxtConfirm()) self::clearTxtConfirm();
		if(!self::getTxtIntroDisable()) self::clearTxtIntroDisable();
		if(!self::getTxtDisable()) self::clearTxtDisable();
		if(!self::getTxtIntroEnable()) self::clearTxtIntroEnable();
		if(!self::getTxtEnable()) self::clearTxtEnable();
		if(!self::getTxtIntroSuspend()) self::clearTxtIntroSuspend();
		if(!self::getTxtSuspend()) self::clearTxtSuspend();
		if(!self::getMsgPresentationForm()) self::clearMsgPresentationForm();
		if(!self::getCategory()) self::clearCategory();
		if(!self::getCheckSchedule()) self::clearCheckSchedule();
		if(!self::getCheckNotification()) self::clearCheckNotification();
		if(!self::getSendMode()) self::clearSendMode();
		if(!self::getUseDefaultFormat()) self::clearUseDefaultFormat();
		if(!self::getNewsletterSubject()) self::clearNewsletterSubject();
		if(!self::getConfirmSubject()) self::clearConfirmSubject();
		if(!self::getSuspendSubject()) self::clearSuspendSubject();
		if(!self::getEnableSubject()) self::clearEnableSubject();
		if(!self::getDisableSubject()) self::clearDisableSubject();
		if(!self::getResumeSubject()) self::clearResumeSubject();
		if(!self::getChangeModeSubject()) self::clearChangeModeSubject();
		if(!self::getCheckUseSuspend()) self::clearCheckUseSuspend();
		if(!self::getFormTitlePage()) self::clearFormTitlePage();

		if(!self::isInstalled()) {
			self::Inactivate();
		}
		self::Install();

		self::Trigger();
	}

	/**
	* supprime les paramètres
	*/
	public static function deleteSettings()
	{
		
		$parameters = array('active', 
						'installed', 
						'editorName',
						'editorEmail',
						'maxposts',
						'minposts',
						'autosend',
						'captcha',
						'view_content_post',
						'size_content_post',
						'introductory_msg',
						'concluding_msg',
						'presentation_msg',
						'presentation_posts_msg',
						'txt_intro_confirm',
						'txtConfirm',
						'txt_intro_disable',
						'txtDisable',
						'txt_intro_enable',
						'txtEnable',
						'txt_intro_suspend',
						'txtSuspend',
						'msg_presentation_form',
						'category',
						'check_schedule',
						'check_notification',
						'mode',
						'use_global_modesend',
						'newsletter_subject',
						'confirm_subject',
						'suspend_subject',
						'enable_subject',
						'disable_subject',
						'resume_subject',
						'change_mode_subject',
						'check_use_suspend',
						'form_title_page'
						);
		// deleting settings
		foreach ($parameters as $v) {
			self::delete($v);
		}
		unset($v);
		
		self::Trigger();
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
	* adresse pour la partie d'administration
	*/
	public static function urldatas() 
	{ 
		return (string)'index.php?pf='.self::pname(); 
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
	public static function Trigger()
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
	* active l'installation du plugin
	*/
	public static function Install() 
	{ 
		self::setInstalled(true); 
	}

	/**
	* désactive l'installation plugin
	*/
	public static function Uninstall() 
	{ 
		self::setInstalled(false); 
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
	public static function Activate() 
	{ 
		self::setActive(true); 
	}

	/**
	* désactive le plugin
	*/
	public static function Inactivate() 
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

	/**
	* lit les informations
	*/
	public static function readUpdate()
	{
		global $core;
		try {
			if (!ini_get('allow_url_fopen'))
				throw new Exception('Unable to check for upgrade since \'allow_url_fopen\' is disabled on this system.');

			self::$remotelines = null;
			$content = netHttp::quickGet(self::updateUrl());
			if (!empty($content)) {
				$lines = explode("\n", $content);
				if (is_array($lines)) {
					self::$remotelines = array();
					foreach ($lines as $datas)
					{
						if (strlen($datas) > 0) {
							$line = trim($datas);
							$parts = explode('=', $line);
							self::$remotelines[ trim($parts[0]) ] = trim($parts[1]);
						}
					}
				}
			}
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
	* priorité du plugin
	*/
	public static function dcPriority() 
	{ 
		return self::getInfo('priority'); 
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
	* vérifie les mises à jour et positionne le flag indicateur
	*/
	public static function checkUpdate()
	{
		self::$newversionavailable = false;
		self::readUpdate();
		if (self::hasDatas()) {
			$v_current = self::dcVersion();
			$v_remote = self::Version();
			if (self::compareVersion($v_current, $v_remote) < 0)
				self::$newversionavailable = true;
		}
	}

	/**
	* génère le code html pour affichage dans l'admin des informations de mise à jour
	*/
	public static function htmlNewVersion($check = true)
	{
		if (!$check)
			return '';
		else {
			$msg = '';
			self::checkUpdate();
			if (!self::isNewVersionAvailable())
				$msg .= __('No new version available.');
			else {
				$msg .= __('New version available:').' '.self::Version().' ';

			$m = array();
			if (self::hasPost() || self::hasPackage() || self::hasArchive()) 
				$msg .= '[';
			if (self::hasPost()) 
				$m[] = '<a href="'.self::post().'" title="'.__('Read the post.').'">'.__('post').'</a>';
			if (self::hasPackage()) 
				$m[] = '<a href="'.self::Package().'" title="'.__('Installer.').'">'.__('pkg.gz').'</a>';
			if (self::hasArchive()) 
				$m[] = '<a href="'.self::Archive().'" title="'.__('Archive.').'">'.__('tar.gz').'</a>';

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
	public static function dbVersion()
	{
		global $core;
		try {
			return (string)$core->getVersion('core');
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* permet de savoir si la version de Dotclear installé une version finale
	*/
	public static function isRelease()
	{
		global $core;
		try {
			$version = (string)self::dbVersion();
			if (!stripos($version, 'beta')) 
				return true;
			else 
				return false;
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* permet de savoir si la version de Dotclear installé la beta 6
	*/
	public static function isBeta($sub = '6')
	{
		global $core;
		try {
			$version = (string)self::dbVersion();
			if (stripos($version, 'beta'.$sub)) 
				return true;
			else 
				return false;
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* permet de savoir si la version de Dotclear installé est une version 'svn'
	*/
	public static function isSVN() 
	{ 
		return !self::isRelease() && (!self::isBeta('6') || !self::isBeta('7')); 
	}

}

?>
