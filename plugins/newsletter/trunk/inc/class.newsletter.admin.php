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

/** ==================================================
	administration
================================================== */

class newsletterAdmin
{
	/**
	* installation du plugin
	*/
	public static function Install()
	{
		// test de possibilité d'installation
		if (!newsletterCore::isAllowed()) 
			return false;

		// création du schéma
		global $core;
		try {
			// création du schéma de la table
			$_s = new dbStruct($core->con, $core->prefix);
			require dirname(__FILE__).'/db-schema.php';

			$si = new dbStruct($core->con, $core->prefix);
			$changes = $si->synchronize($_s);
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}

		// activation des paramètres par défaut
		newsletterPlugin::defaultsSettings();

		return true;
	}

	/**
	* désinstallation du plugin
	*/
	public static function Uninstall()
	{
		// désactivation du plugin
		newsletterPlugin::Inactivate();
		//newsletterAdmin::Export(false);

		// suppression du schéma
		global $core;
		try {
			$con = &$core->con;

			$strReq =
				'DROP TABLE '.
				$core->prefix.newsletterPlugin::pname();

			$rs = $con->execute($strReq);
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}

		// suppression des paramètres par défaut
		newsletterPlugin::deleteSettings();
		newsletterPlugin::delete_version();
	}

	/**
	* export du contenu du schéma
	*/
	public static function Export($onlyblog = true, $outfile = null)
	{
		global $core;
		try {
			$blog = &$core->blog;
			$blogid = (string)$blog->id;

			// générer le contenu du fichier à partir des données
			if (isset($outfile)) {
				$filename = $outfile;
			} else {
				if ($onlyblog) 
					$filename = $core->blog->public_path.'/'.$blogid.'-'.newsletterPlugin::pname().'.dat';
				else 
					$filename = $core->blog->public_path.'/'.newsletterPlugin::pname().'.dat';
			}

			$content = '';
			$datas = newsletterCore::getRawDatas($onlyblog);
			if (is_object($datas) !== FALSE) {
				$datas->moveStart();
				while ($datas->fetch())
				{
					$elems = array();

					// génération des élements de données
					$elems[] = $datas->subscriber_id;
					$elems[] = base64_encode($datas->blog_id);
					$elems[] = base64_encode($datas->email);
					$elems[] = base64_encode($datas->regcode);
					$elems[] = base64_encode($datas->state);
					$elems[] = base64_encode($datas->subscribed);
					$elems[] = base64_encode($datas->lastsent);
					$elems[] = base64_encode($datas->modesend);

					// génération de la ligne de données exportées(séparateur -> ;)
					$line = implode(";", $elems);
                    	$content .= "$line\n";
				}
			}

			// écrire le contenu dans le fichier
			@file_put_contents($filename, $content);
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* import du contenu du schéma
	*/
	public static function Import($onlyblog = true, $infile = null)
	{
		global $core;
		try {
			$blog = &$core->blog;
			$blogid = (string)$blog->id;
        
			// lire le contenu du fichier à partir des données
			if (isset($infile)) 
				$filename = $infile;
			else {
				if ($onlyblog) 
					$filename = $core->blog->public_path.'/'.$blogid.'-'.newsletterPlugin::pname().'.dat';
				else 
					$filename = $core->blog->public_path.'/'.newsletterPlugin::pname().'.dat';
			}

			// ouverture du fichier
			$content = '';
			$fh = @fopen($filename, "r");
			if ($fh === FALSE) 
				return false;
			else {
				// boucle de lecture sur les lignes du fichier
				$err = false;
				while (!feof($fh)) {
					// lecture d'une ligne du fichier
					$l = @fgetss($fh, 4096);
					if ($l != FALSE) {
						// sécurisation du contenu de la ligne et décomposition en élements (séparateur -> ;)
						$line = (string) html::clean((string) $l);
						$elems = explode(";", $line);

						// traitement des données lues
						$subscriber_id = $elems[0];
						$blog_id = base64_decode($elems[1]);
						$email = base64_decode($elems[2]);
						$regcode = base64_decode($elems[3]);
						$state = base64_decode($elems[4]);
						$subscribed = base64_decode($elems[5]);
						$lastsent = base64_decode($elems[6]);
						$modesend = base64_decode($elems[7]);

						newsletterCore::add($email, $blog_id, $regcode, $modesend);

						$subscriber = newsletterCore::getEmail($email);
						if ($subscriber != null) {
							//$core->error->add('id : '.$subscriber->subscriber_id);
							newsletterCore::update($subscriber->subscriber_id, $email, $state, $regcode, $subscribed, $lastsent, $modesend);
						}
					}
				}

				// fermeture du fichier
				@fclose($fh);

				if ($err) 
					return false;
				else 
					return true;
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
    
	/**
	* formulaire d'adaptation de template
	*/
	public static function Adapt($theme = null)
	{
		// prise en compte du plugin installé
		if (!newsletterPlugin::isInstalled()) 
			return;

		if ($theme == null) 
			echo __('No template to adapt.');
		else {
			global $core;
			try {
			
				$blog = &$core->blog;
				$settings = &$blog->settings;

				// fichier source
				//$sfile = 'post.html';
				$sfile = 'home.html';
				$source = $blog->themes_path.'/'.$theme.'/tpl/'.$sfile;

				// fichier de template
				$tfile = 'template.newsletter.html';
				$template = dirname(__FILE__).'/../default-templates/'.$tfile;
						
				// fichier destination
				$dest = $blog->themes_path.'/'.$theme.'/tpl/'.'subscribe.newsletter.html';
				
				
				if (!@file_exists($source)) {			// test d'existence de la source
					$msg = $sfile.' '.__('is not in your theme folder.').' ('.$blog->themes_path.')';
					$core->error->add($msg);
					return;
				} else if (!@file_exists($template)) { 	// test d'existence du template source
					$msg = $tfile.' '.__('is not in the plugin folder.').' ('.dirname(__FILE__).')';
					$core->error->add($msg);
					return;
				} else if (!@is_readable($source)) { 	// test si le fichier source est lisible
					$msg = $sfile.' '.__('is not readable.');
					$core->error->add($msg);
					return;
				} else {
					// lecture du contenu des fichiers template et source
					$tcontent = @file_get_contents($template);
					$scontent = @file_get_contents($source);
					
					// definition des remplacements
					switch ($theme) {
						case 'noviny':
						{
							// traitement du theme particulier noviny
							$patterns[0] = '/<div id=\"overview\" class=\"grid-l\">[\S\s]*<div id=\"extra\"/';
							$replacements[0] = '<div class="grid-l">'. "\n" .'<div class="post">'. "\n" . $tcontent . "\n" .'</div>'. "\n" . '</div>'. "\n" .'<div id="extra"';
							$patterns[1] = '/<title>.*<\/title>/';
							$replacements[1] = '<title>{{tpl:NewsletterPageTitle encode_html="1"}} - {{tpl:BlogName encode_html="1"}}</title>';
							$patterns[2] = '/dc-home/';
							$replacements[2] = 'dc-newsletter';
							$patterns[3] = '/<meta name=\"dc.title\".*\/>/';
							$replacements[3] = '<meta name="dc.title" content="{{tpl:NewsletterPageTitle encode_html="1"}} - {{tpl:BlogName encode_html="1"}}" />';
							$patterns[4] = '/<div id=\"lead\" class="grid-l home-lead">[\S\s]*<div id=\"meta\"/';
							$replacements[4] = '<div id="lead" class="grid-l">'. "\n\t" .'<h2>{{tpl:NewsletterPageTitle encode_html="1"}}</h2>'. "\n\t" .'</div>'. "\n\t" . '<div id="meta"';
							$patterns[5] = '/<div id=\"meta\" class=\"grid-s\">[\S\s]*{{tpl:include src=\"inc_meta.html\"}}/';
							$replacements[5] = '<div id="meta" class="grid-s">'. "\n\t" .'{{tpl:include src="inc_meta.html"}}';
							$patterns[6] = '/<h2 class=\"post-title\">{{tpl:NewsletterPageTitle encode_html=\"1\"}}<\/h2>/';
							$replacements[6] = '';
							break;
						}
						default:
						{
							$patterns[0] = '/<tpl:Entries>[\S\s]*<\/tpl:Entries>/';
							$replacements[0] = $tcontent;
							$patterns[1] = '/<title>.*<\/title>/';
							$replacements[1] = '<title>{{tpl:NewsletterPageTitle encode_html="1"}} - {{tpl:BlogName encode_html="1"}}</title>';
							$patterns[2] = '/dc-home/';
							$replacements[2] = 'dc-newsletter';
							$patterns[3] = '/<meta name=\"dc.title\".*\/>/';
							$replacements[3] = '<meta name="dc.title" content="{{tpl:NewsletterPageTitle encode_html="1"}} - {{tpl:BlogName encode_html="1"}}" />';
							$patterns[4] = '/<tpl:Entries no_content=\"1\">[\S\s]*<\/tpl:Entries>/';
							$replacements[4] = '';
						}
					}


					$count = 0;
					$scontent = preg_replace($patterns, $replacements, $scontent, 1, $count);
					//$core->error->add('Nombre de remplacements : ' . $count); 

					// suppression des lignes vides et des espaces de fin de ligne
					$a2 = array();
					$tok = strtok($scontent, "\n\r");
					while ($tok !== FALSE)
					{
						$l = rtrim($tok);
						if (strlen($l) > 0)
						    $a2[] = $l;
						$tok = strtok("\n\r");
					}
					$c2 = implode("\n", $a2);
					$scontent = $c2;

					// écriture du fichier de template
					if ((@file_exists($dest) && @is_writable($dest)) || @is_writable($blog->themes_path)) {
	                    	$fp = @fopen($dest, 'w');
	                    	@fputs($fp, $scontent);
	                    	@fclose($fp);
	                    	$msg = __('Template created.');
	                	} else {
	                		$msg = __('Unable to write file.');
	                	}

					//@file_put_contents($dest,$scontent);
					$msg = __('Template created.');

				}

				return $msg;
			} catch (Exception $e) { 
				$core->error->add($e->getMessage()); 
			}
		}
	}    
}

/** ==================================================
	onglets de la partie d'administration
================================================== */

class tabsNewsletter
{
	/**
	* paramétrage du plugin
	*/
	public static function Settings()
	{
		// prise en compte du plugin installé
		if (!newsletterCore::isInstalled()) {
			return;
		}

		global $core;
		try {
			$blog = &$core->blog;
			$auth = &$core->auth;
				
			$mode_combo = array(__('text') => 'text',
							__('html') => 'html');

			$sadmin = (($auth->isSuperAdmin()) ? true : false);

			if (newsletterPlugin::isActive()) {

				// initialisation des variables
				$feditorname = newsletterPlugin::getEditorName();
				$feditoremail = newsletterPlugin::getEditorEmail();
				$fcaptcha = newsletterPlugin::getCaptcha();
				$fmode = newsletterPlugin::getSendMode();
				$f_use_default_format = newsletterPlugin::getUseDefaultFormat();
				$fmaxposts = newsletterPlugin::getMaxPosts();
				$fminposts = newsletterPlugin::getMinPosts();
				$f_view_content_post = newsletterPlugin::getViewContentPost();
				$f_size_content_post = newsletterPlugin::getSizeContentPost();
				$fautosend = newsletterPlugin::getAutosend();
				$f_check_notification = newsletterPlugin::getCheckNotification();
				$f_check_use_suspend = newsletterPlugin::getCheckUseSuspend();

				$rs = $core->blog->getCategories(array('post_type'=>'post'));
				$categories = array('' => '', __('Uncategorized') => 'null');
				while ($rs->fetch()) {
					$categories[str_repeat('&nbsp;&nbsp;',$rs->level-1).'&bull; '.html::escapeHTML($rs->cat_title)] = $rs->cat_id;
				}
				$f_category = newsletterPlugin::getCategory();

				// gestion des paramètres du plugin
				echo	
				'<form action="plugin.php" method="post" id="settings">'.
					'<fieldset id="advanced">'.
						'<legend>'.__('Advanced Settings').'</legend>'.
						'<p class="field">'.
							'<label for="feditorname" class="classic required" title="'.__('Required field').'">'.__('Editor name').'</label>'.
							form::field('feditorname',50,255,html::escapeHTML($feditorname)).
						'</p>'.
						'<p class="field">'.
							'<label for="feditoremail" class="classic required" title="'.__('Required field').'">'.__('Editor email').'</label>'.
							form::field('feditoremail',50,255,html::escapeHTML($feditoremail)).
						'</p>'.
						'<p class="field">'.
							'<label for="fcaptcha" class="classic">'.__('Captcha').'</label>'.
							form::checkbox('fcaptcha',1,$fcaptcha).
						'</p>'.
						'<p class="field">'.
							'<label for="fmode" class="classic">'.__('Default format for sending').'</label>'.
							form::combo('fmode',$mode_combo,$fmode).
						'</p>'.
						'<p class="field">'.
							'<label for="f_use_default_format" class="classic">'.__('Use default format for sending').'</label>'.
							form::checkbox('f_use_default_format',1,$f_use_default_format).
						'</p>'.
						'<p class="field">'.
							'<label for="fautosend" class="classic">'.__('Automatic send').'</label>'.
							form::checkbox('fautosend',1,$fautosend).
						'</p>'.
						'<p class="field">'.
							'<label for="f_check_notification" class="classic">'.__('Notification sending').'</label>'.
							form::checkbox('f_check_notification',1,$f_check_notification).
						'</p>'.
						'<p class="field">'.
							'<label for="fminposts" class="classic">'.__('Minimum posts').'</label>'.
							form::field('fminposts',4,4,$fminposts).
						'</p>'.
						'<p class="field">'.
							'<label for="fmaxposts" class="classic">'.__('Maximum posts').'</label>'.
							form::field('fmaxposts',4,4,$fmaxposts).
						'</p>'.
						'<p class="field">'.
							'<label for="f_view_content_post" class="classic">'.__('View contents posts').'</label>'.
							form::checkbox('f_view_content_post',1,$f_view_content_post).
						'</p>'.
						'<p class="field">'.
							'<label for="f_size_content_post" class="classic">'.__('Size contents posts').'</label>'.
							form::field('f_size_content_post',4,4,$f_size_content_post).
						'</p>'.
						'<p class="field">'.
							'<label for="f_category" class="classic">'.__('Category').'</label>'.
							form::combo('f_category',$categories,$f_category).
						'</p>'.
						'<p class="field">'.
							'<label for="f_check_use_suspend" class="classic">'.__('Use suspend option').'</label>'.
							form::checkbox('f_check_use_suspend',1,$f_check_use_suspend).
						'</p>'.
					'</fieldset>'.
					// boutons du formulaire
					'<p>'.
						'<input type="submit" name="save" value="'.__('Save').'" /> '.
						'<input type="reset" name="reset" value="'.__('Cancel').'" /> '.
						'<input type="button" value="'.__('Defaults').'" onclick="pdefaults(); return false" />'.
					'</p>'.
					'<p>'.
						form::hidden(array('p'),newsletterPlugin::pname()).
						form::hidden(array('op'),'settings').
						$core->formNonce().
					'</p>'.
				'</form>'.
				'';
			} else {
				echo
				'<fieldset>'.
					'<p>'.
						'<label class="classic">'.__('Activate the plugin in the Maintenance tab to view all options').'</label>'.
					'</p>'.
				'</fieldset>';
			}
			
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* paramétrage du plugin
	*/
	public static function Messages()
	{
		global $core;
		try {
			$blog = &$core->blog;
			$auth = &$core->auth;

			$sadmin = (($auth->isSuperAdmin()) ? true : false);

			if (newsletterPlugin::isActive()) {

				// newsletter
				$f_newsletter_subject = newsletterPlugin::getNewsletterSubject();
				$f_introductory_msg = newsletterPlugin::getIntroductoryMsg();
				$f_concluding_msg = newsletterPlugin::getConcludingMsg();
				$f_msg_presentation_form = newsletterPlugin::getMsgPresentationForm();
				$f_presentation_msg = newsletterPlugin::getPresentationMsg();
				$f_presentation_posts_msg = newsletterPlugin::getPresentationPostsMsg();

				// confirm
				$f_confirm_subject = newsletterPlugin::getConfirmSubject();
				$f_txt_intro_confirm = newsletterPlugin::getTxtIntroConfirm();
				$f_txtConfirm = newsletterPlugin::getTxtConfirm();
				$f_confirm_msg = newsletterPlugin::getConfirmMsg();
				$f_concluding_confirm_msg = newsletterPlugin::getConcludingConfirmMsg();

				// disable
				$f_disable_subject = newsletterPlugin::getDisableSubject();
				$f_txt_intro_disable = newsletterPlugin::getTxtIntroDisable();
				$f_txtDisable = newsletterPlugin::getTxtDisable();
				$f_disable_msg = newsletterPlugin::getDisableMsg();
				$f_concluding_disable_msg = newsletterPlugin::getConcludingDisableMsg();
				$f_txt_disabled_msg = newsletterPlugin::getTxtDisabledMsg();

				// enable
				$f_txt_intro_enable = newsletterPlugin::getTxtIntroEnable();
				$f_txtEnable = newsletterPlugin::getTxtEnable();
				$f_enable_subject = newsletterPlugin::getEnableSubject();
				$f_enable_msg = newsletterPlugin::getEnableMsg();
				$f_concluding_enable_msg = newsletterPlugin::getConcludingEnableMsg();
				$f_txt_enabled_msg = newsletterPlugin::getTxtEnabledMsg();

				// suspend
				$f_suspend_subject = newsletterPlugin::getSuspendSubject();
				$f_suspend_msg = newsletterPlugin::getSuspendMsg();
				$f_txt_suspended_msg = newsletterPlugin::getTxtSuspendedMsg();
				$f_concluding_suspend_msg = newsletterPlugin::getConcludingSuspendMsg();
				$f_txt_intro_suspend = newsletterPlugin::getTxtIntroSuspend();
				$f_txtSuspend = newsletterPlugin::getTxtSuspend();

				// changemode
				$f_change_mode_subject = newsletterPlugin::getChangeModeSubject();
				$f_header_changemode_msg = newsletterPlugin::getHeaderChangeModeMsg();
				$f_footer_changemode_msg = newsletterPlugin::getFooterChangeModeMsg();
				$f_changemode_msg = newsletterPlugin::getChangeModeMsg();

				// resume
				$f_resume_subject = newsletterPlugin::getResumeSubject();
				$f_header_resume_msg = newsletterPlugin::getHeaderResumeMsg();
				$f_footer_resume_msg = newsletterPlugin::getFooterResumeMsg();
				
				// subscribe
				$f_form_title_page = newsletterPlugin::getFormTitlePage();
				$f_txt_subscribed_msg = newsletterPlugin::getTxtSubscribedMsg();				

				// gestion des paramètres du plugin
				echo	
				'<form action="plugin.php" method="post" id="messages">'.
					'<fieldset id="define_newsletter">'.
						'<legend>'.__('Define message content Newsletter').'</legend>'.
						'<p>'.
							'<label for="f_newsletter_subject">'.__('Subject of the Newsletter').'</label>'.
							form::field('f_newsletter_subject',50,255,html::escapeHTML($f_newsletter_subject)).
						'</p>'.
						'<p>'.
							'<label for="f_presentation_msg">'.__('Message presentation').'</label>'.
							form::field('f_presentation_msg',50,255,html::escapeHTML($f_presentation_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_introductory_msg">'.__('Introductory message').' : </label>'.
							form::textarea('f_introductory_msg',30,4,html::escapeHTML($f_introductory_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_presentation_posts_msg">'.__('Presentation message for posts').'</label>'.
							form::field('f_presentation_posts_msg',50,255,html::escapeHTML($f_presentation_posts_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_concluding_msg">'.__('Concluding message').' : </label>'.
							form::textarea('f_concluding_msg',30,4, html::escapeHTML($f_concluding_msg)).
						'</p>'.
					'</fieldset>'.					
					'<fieldset id="define_confirm">'.
						'<legend>'.__('Define message content Confirm').'</legend>'.
						'<p>'.
							'<label for="f_confirm_subject">'.__('Subject of the mail Confirm').'</label>'.
							form::field('f_confirm_subject',50,255,html::escapeHTML($f_confirm_subject)).
						'</p>'.
						'<p>'.
							'<label for="f_confirm_msg">'.__('Confirm message').'</label>'.
							form::field('f_confirm_msg',50,255,html::escapeHTML($f_confirm_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_txt_intro_confirm">'.__('Introductory confirm message').'</label>'.
							form::field('f_txt_intro_confirm',50,255,html::escapeHTML($f_txt_intro_confirm)).
						'</p>'.
						'<p>'.
							'<label for="f_txtConfirm">'.__('Title confirmation link').'</label>'.
							form::field('f_txtConfirm',50,255,html::escapeHTML($f_txtConfirm)).
						'</p>'.
						'<p>'.
							'<label for="f_concluding_confirm_msg">'.__('Concluding confirm message').'</label>'.
							form::field('f_concluding_confirm_msg',50,255,html::escapeHTML($f_concluding_confirm_msg)).
						'</p>'.
					'</fieldset>'.
					'<fieldset id="define_disable">'.
						'<legend>'.__('Define message content Disable').'</legend>'.
						'<p>'.
							'<label for="f_txt_disabled_msg">'.__('Txt disabled msg').
							form::field('f_txt_disabled_msg',50,255,html::escapeHTML($f_txt_disabled_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_disable_subject">'.__('Subject of the mail Disable').'</label>'.
							form::field('f_disable_subject',50,255,html::escapeHTML($f_disable_subject)).
						'</p>'.
						'<p>'.
							'<label for="f_disable_msg">'.__('Disable message').'</label>'.
							form::field('f_disable_msg',50,255,html::escapeHTML($f_disable_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_txt_intro_disable">'.__('Introductory disable message').'</label>'.
							form::field('f_txt_intro_disable',50,255,html::escapeHTML($f_txt_intro_disable)).
						'</p>'.
						'<p>'.
							'<label for="f_txtDisable">'.__('Title disable link').'</label>'.
							form::field('f_txtDisable',50,255,html::escapeHTML($f_txtDisable)).
						'</p>'.
						'<p>'.
							'<label for="f_concluding_disable_msg">'.__('Concluding disable msg').'</label>'.
							form::field('f_concluding_disable_msg',50,255,html::escapeHTML($f_concluding_disable_msg)).
						'</p>'.
					'</fieldset>'.					
					'<fieldset id="define_enable">'.
						'<legend>'.__('Define message content Enable').'</legend>'.
						'<p>'.
							'<label for="f_txt_enabled_msg">'.__('Texte enabled message').'</label>'.
							form::field('f_txt_enabled_msg',50,255,html::escapeHTML($f_txt_enabled_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_enable_subject">'.__('Subject of the mail Enable').'</label>'.
							form::field('f_enable_subject',50,255,html::escapeHTML($f_enable_subject)).
						'</p>'.
						'<p>'.
							'<label for="f_enable_msg">'.__('Enable message').'</label>'.
							form::field('f_enable_msg',50,255,html::escapeHTML($f_enable_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_txt_intro_enable">'.__('Introductory enable message').'</label>'.
							form::field('f_txt_intro_enable',50,255,html::escapeHTML($f_txt_intro_enable)).
						'</p>'.
						'<p>'.
							'<label for="f_txtEnable">'.__('Title enable link').'</label>'.
							form::field('f_txtEnable',50,255,html::escapeHTML($f_txtEnable)).
						'</p>'.
						'<p>'.
							'<label for="f_concluding_enable_msg">'.__('Concluging enable message').'</label>'.
							form::field('f_concluding_enable_msg',50,255,html::escapeHTML($f_concluding_enable_msg)).
						'</p>'.
					'</fieldset>'.	
					'<fieldset id="define_suspend">'.
						'<legend>'.__('Define message content Suspend').'</legend>'.
						'<p>'.
							'<label for="f_txt_suspended_msg">'.__('Txt suspended msg').'</label>'.
							form::field('f_txt_suspended_msg',50,255,html::escapeHTML($f_txt_suspended_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_suspend_subject">'.__('Subject of the mail Suspend').'</label>'.
							form::field('f_suspend_subject',50,255,html::escapeHTML($f_suspend_subject)).
						'</p>'.
						'<p>'.
							'<label for="f_suspend_msg">'.__('Suspend message').'</label>'.
							form::field('f_suspend_msg',50,255,html::escapeHTML($f_suspend_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_txt_intro_suspend">'.__('Introductory suspend message').'</label>'.
							form::field('f_txt_intro_suspend',50,255,html::escapeHTML($f_txt_intro_suspend)).
						'</p>'.
						'<p>'.
							'<label for="f_txtSuspend">'.__('Title suspend link').'</label>'.
							form::field('f_txtSuspend',50,255,html::escapeHTML($f_txtSuspend)).
						'</p>'.
						'<p>'.
							'<label for="f_concluding_suspend_msg">'.__('Concluding suspend message').'</label>'.
							form::field('f_concluding_suspend_msg',50,255,html::escapeHTML($f_concluding_suspend_msg)).
						'</p>'.
					'</fieldset>'.
					'<fieldset id="define_changemode">'.
						'<legend>'.__('Define message content Changemode').'</legend>'.
						'<p>'.
							'<label for="f_changemode_msg">'.__('Change mode message').'</label>'.
							form::field('f_changemode_msg',50,255,html::escapeHTML($f_changemode_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_change_mode_subject">'.__('Subject of the mail Changing mode').'</label>'.
							form::field('f_change_mode_subject',50,255,html::escapeHTML($f_change_mode_subject)).
						'</p>'.
						'<p>'.
							'<label for="f_header_changemode_msg">'.__('Introductory change mode message').'</label>'.
							form::field('f_header_changemode_msg',50,255,html::escapeHTML($f_header_changemode_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_footer_changemode_msg">'.__('Concludind change mode message').'</label>'.
							form::field('f_footer_changemode_msg',50,255,html::escapeHTML($f_footer_changemode_msg)).
						'</p>'.
					'</fieldset>'.					
					'<fieldset id="define_resume">'.
						'<legend>'.__('Define message content Resume').'</legend>'.
						'<p>'.
							'<label for="f_resume_subject">'.__('Subject of the mail Resume').'</label>'.
							form::field('f_resume_subject',50,255,html::escapeHTML($f_resume_subject)).
						'</p>'.
						'<p>'.
							'<label for="f_header_resume_msg">'.__('Introductory resume message').'</label>'.
							form::field('f_header_resume_msg',50,255,html::escapeHTML($f_header_resume_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_footer_resume_msg">'.__('Concluding resume message').'</label>'.
							form::field('f_footer_resume_msg',50,255,html::escapeHTML($f_footer_resume_msg)).
						'</p>'.
					'</fieldset>'.					
					'<fieldset id="define_subscribe">'.					
						'<legend>'.__('Define formulary content Subscribe').'</legend>'.
						'<p>'.
							'<label for="f_txt_subscribed_msg">'.__('Subcribed message').'</label>'.
							form::field('f_txt_subscribed_msg',50,255,html::escapeHTML($f_txt_subscribed_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_form_title_page">'.__('Title page of the subscribe form').'</label>'.
							form::field('f_form_title_page',50,255,html::escapeHTML($f_form_title_page)).
						'</p>'.
						'<p>'.
							'<label for="f_msg_presentation_form">'.__('Message presentation form').' : </label>'.
							form::textarea('f_msg_presentation_form',30,4,html::escapeHTML($f_msg_presentation_form)).
						'</p>'.
					'</fieldset>'.

					// boutons du formulaire
					'<p>'.
						'<input type="submit" name="save" value="'.__('Save').'" /> '.
						'<input type="reset" name="reset" value="'.__('Cancel').'" /> '.
					'</p>'.
					'<p>'.
						form::hidden(array('p'),newsletterPlugin::pname()).
						form::hidden(array('op'),'messages').
						$core->formNonce().
					'</p>'.
					'</form>'.
				'';

			} else {
				echo
				'<fieldset>'.
					'<p>'.
						'<label class="classic">'.__('Activate the plugin in the Maintenance tab to view all options').'</label>'.
					'</p>'.
				'</fieldset>';
			}

		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}

	}


	/**
	* paramétrage des options de planification
	*/
	public static function Planning()
	{
		// prise en compte du plugin installé
		if (!newsletterCore::isInstalled()) {
			return;
		}

		global $core;
		try {
			$blog = &$core->blog;
			$auth = &$core->auth;

			if (newsletterPlugin::isActive()) {

				$sadmin = (($auth->isSuperAdmin()) ? true : false);
				if ($sadmin) {
					
					// Utilisation de dcCron
					if (isset($blog->dcCron)) {
						$newsletter_cron=new newsletterCron($core);

						$f_check_schedule = newsletterPlugin::getCheckSchedule();
						$f_interval = ($newsletter_cron->getTaskInterval() ? $newsletter_cron->getTaskInterval() : 604800);
						$f_first_run = ($newsletter_cron->getFirstRun() ? $newsletter_cron->getFirstRun() : '');
						
				     	echo
						'<fieldset>'.
						'<legend>'.__('Planning newsletter').'</legend>'.
							'<form action="plugin.php" method="post" name="planning">'.
								'<p class="field">'.
								'<label class="classic" for="f_interval">'.__('Interval time in seconds between 2 runs').
								form::field('f_interval',20,20,$f_interval).
								'</label></p>'.
								'<p class="comments">'.
								__('samples').' : ( 1 '.__('day').' = 86400s / 1 '.__('week').' = 604800s / 28 '.__('days').' =  16934400s )'.
								'</p>'.
								'<p class="field">'.
								'<label for="f_first_run" class="classic">'.__('Date for the first run').'</label>'.
								form::field('f_first_run',20,20,$f_first_run).
								'</p>'.

								// boutons du formulaire
								'<p>'.
									'<input type="submit" name="submit" value="'.(($f_check_schedule)?__('Unschedule'):__('Schedule')).'" /> '.
									'<input type="reset" name="reset" value="'.__('Cancel').'" /> '.
								'</p>'.
								form::hidden(array('p'),newsletterPlugin::pname()).
								form::hidden(array('op'),(($f_check_schedule)?'unschedule':'schedule')).
								$core->formNonce().
								'</form>'.
						'</fieldset>'.
						'';
						
						if ($f_check_schedule) {
							
							$f_task_state = $newsletter_cron->getState();
							
							echo
							'<fieldset>'.
							'<legend>'.__('Scheduled task').' : '.html::escapeHTML($newsletter_cron->getTaskName()).'</legend>'.
							'<table summary="resume" class="minimal">'.
							'<thead>'.
							'<tr>'.
		  						'<th>'.__('Name').'</th>'.
		  						'<th>'.__('Value').'</th>'.
							'</tr>'.
							'</thead>'.
							'<tbody id="classes-list">'.
							'<tr class="line">'.
							'<td>'.__('State').'</td>'.
							'<td>'.html::escapeHTML(__($newsletter_cron->printState())).'</td>'.
							'</tr>'.
							'<tr class="line">'.
							'<td>'.__('Interval').'</td>'.
							'<td>'.html::escapeHTML($newsletter_cron->printTaskInterval()).'</td>'.
							'</tr>'.
							'<tr class="line">'.
							'<td>'.__('Last run').'</td>'.
							'<td>'.html::escapeHTML($newsletter_cron->printLastRunDate()).'</td>'.
							'</tr>'.
							'<tr class="line">'.
							'<td>'.__('Next run').'</td>'.
							'<td>'.html::escapeHTML($newsletter_cron->printNextRunDate()).'</td>'.
							'</tr>'.
							'<tr class="line">'.
							'<td>'.__('Remaining Time').'</td>'.
							'<td>'.html::escapeHTML($newsletter_cron->printRemainingTime()).'</td>'.
							'</tr>'.
							'</tbody>'.
							'</table>'.										

							'<form action="plugin.php" method="post" name="taskplanning">'.

							// boutons du formulaire
							'<p>'.
								'<input type="submit" name="submit" value="'.(($f_task_state)?__('Disable'):__('Enable')).'" /> '.
							'</p>'.
							form::hidden(array('p'),newsletterPlugin::pname()).
							form::hidden(array('op'),(($f_task_state)?'disabletask':'enabletask')).
							$core->formNonce().
							'</form>'.
							
							'</fieldset>'.
							'';
						}					
					
					} else {
						echo
						'<fieldset>'.
							'<legend>'.__('Planning newsletter').'</legend>'.
							'<p>'.
								'<label class="classic">'.__('Install the plugin dcCron for using planning').'</label>'.
							'</p>'.
						'</fieldset>';
					}
			
				}
			} else {
				echo
				'<fieldset>'.
					'<p>'.
						'<label class="classic">'.__('Activate the plugin in the Maintenance tab to view all options').'</label>'.
					'</p>'.
				'</fieldset>';
			}

		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}

	}

	/**
	* Maintenance du plugin
	*/
	public static function Maintenance()
	{
		// prise en compte du plugin installé
		if (!newsletterCore::isInstalled()) {
			return;
		}

		global $core;
		try {
			$blog = &$core->blog;
			$auth = &$core->auth;
			$url = &$core->url;
			$themes = &$core->themes;
			$blogurl = &$blog->url;
			$urlBase = http::concatURL($blogurl, $url->getBase('newsletter'));			

			//$core->themes = new dcModules($core);
			$core->themes = new dcThemes($core);
			$core->themes->loadModules($blog->themes_path, NULL);
			$theme = $blog->settings->theme;
			//$theme = $themes->getModules($theme);
			$bthemes = array();
			foreach ($themes->getModules() as $k => $v)
			{
				if (file_exists($blog->themes_path . '/' . $k . '/tpl/post.html'))
					$bthemes[html::escapeHTML($v['name'])] = $k;
			}			

			$sadmin = (($auth->isSuperAdmin()) ? true : false);
			
			// paramétrage de l'état d'activation du plugin
			if (newsletterPlugin::isActive()) 
				$pactive = 'checked';
			else 
				$pactive = '';
			
			echo
			'<form action="plugin.php" method="post" id="state">'.
				'<fieldset>'.
					'<legend>'.__('Plugin state').'</legend>'.
					'<p class="field">'.
					'<label class="classic" for="active">'.__('Activate plugin').'</label>'.
					form::checkbox('active', 1, $pactive).
					'</p>'.
				'</fieldset>'.
				'<p>'.
					'<input type="submit" value="'.__('Save').'" /> '.
					'<input type="reset" value="'.__('Cancel').'" /> '.
				'</p>'.
				'<p>'.
					form::hidden(array('p'),newsletterPlugin::pname()).
					form::hidden(array('op'),'state').
					$core->formNonce().
				'</p>'.
			'</form>'.
			'';

			if (newsletterPlugin::isActive()) {
				echo
				// export/import pour le blog
				'<form action="plugin.php" method="post" id="impexp">'.
					'<fieldset>'.
						'<legend>'.__('Import/Export subscribers list').'</legend>'.
						'<p>'.
						'<label class="classic">'.
						form::radio(array('type'),'blog',(!$sadmin) ? true : false).__('This blog only').
						'</label>'.
						'<br />'.
						'<label class="classic">'.
						form::radio(array('type'),'all',($sadmin) ? true : false,'','',(!$sadmin) ? true : false).__('All datas').
						'</label>'.
						'</p>'.
						'<p>'.
						'<input type="submit" value="'.__('Export').'" />'.
						'<input type="button" value="'.__('Import').'" onclick="pimport(); return false" />'.
						form::hidden(array('p'),newsletterPlugin::pname()).
						form::hidden(array('op'),'export').
						$core->formNonce().					
						'</p>'.
					'</fieldset>'.
				'</form>'.
				
				// adaptation du template
				///*
				'<form action="plugin.php" method="post" id="adapt">'.
					'<fieldset>'.
						'<legend>'.__('Adapt the template for the theme').'</legend>'.
						'<p><label class="classic" for="fthemes">'.__('Theme name').' : '.
						form::combo('fthemes', $bthemes, $theme).
						'</label></p>'.
						'<p>'.
						'<input type="submit" value="'.__('Adapt').'" />'.
						form::hidden(array('p'),newsletterPlugin::pname()).
						form::hidden(array('op'),'adapt').
						$core->formNonce().					
						'</p>'.
						'<p>'.
						'<a href="'.$urlBase.'/test'.'">'.__('Clic here to test the template.').'</a>'.
						'</p>'.
					'</fieldset>'.
				'</form>'.
				//*/
				
				// gestion de la mise à jour
				/* désactivée => redondance avec dotaddict : à supprimer
				if ($sadmin)
				{
			        echo
					'<fieldset>'.
					'<legend>'.__('Check for plugin update').'</legend>'.
						'<form action="plugin.php" method="post" name="update">'.
	                        $core->formNonce().
							form::hidden(array('p'),newsletterPlugin::pname()).
							form::hidden(array('op'),'update').
							'<p></p>'.
							'<p>'.
								'<input type="submit" value="'.__('Check').'" />'.
							'</p>'.
						'</form>'.
					'</fieldset>'.
				}
				//*/				

				// Nettoyage de la base
				'<form action="plugin.php" method="post" id="erasingnewsletter">'.
					'<fieldset>'.
						'<legend>'.__('Erasing all informations about newsletter in database').'</legend>'.
						'<p>'.__('Be careful, please backup your database before erasing').
						'</p>'.
						'<p>'.
						'<input type="submit" value="'.__('Erasing').'" name="delete" class="delete" onclick="erasingnewsletterConfirm(); return false" />'.
						form::hidden(array('p'),newsletterPlugin::pname()).
						form::hidden(array('op'),'erasingnewsletter').
						$core->formNonce().					
						'</p>'.
					'</fieldset>'.
				'</form>'.
				
				'';

			} else {
				echo
				'<fieldset>'.
					'<p>'.
						'<label class="classic">'.__('Activate the plugin in the Maintenance tab to view all options').'</label>'.
					'</p>'.
				'</fieldset>';
			}

		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* liste des abonnés du blog
	*/
	public static function ListBlog()
	{
		// prise en compte du plugin installé
		if (!newsletterCore::isInstalled()) {
			return;
		}

		global $core;
		try {
			if (newsletterPlugin::isActive()) {
				$datas = newsletterCore::getlist();
				if (!is_object($datas)) {
					echo __('No subscriber for this blog.');
				} else {
					$blog = &$core->blog;
					$settings = &$blog->settings;
							
					// début du tableau et en-têtes
					echo
					'<form action="plugin.php" method="post" id="listblog"><p>' .
						$core->formNonce().
						form::hidden(array('p'),newsletterPlugin::pname()).
						form::hidden(array('op'),'remove').
						form::hidden(array('id'),'')."</p>\n".
						'<table class="clear" id="userslist">'.
							'<tr>'.
								'<th>&nbsp;</th>'.
								'<th class="nowrap">'.__('Subscriber').'</th>' .
								'<th class="nowrap">'.__('Subscribed').'</th>' .
								'<th class="nowrap">'.__('Status').'</th>' .
								'<th class="nowrap">'.__('Last sent').'</th>' .
								'<th class="nowrap">'.__('Mode send').'</th>' .
								'<th class="nowrap" colspan="2">'.__('Edit').'</th>'.
							'</tr>';

					// parcours la liste pour l'affichage
					$datas->moveStart();
					while ($datas->fetch()) {
						$k = (integer)$datas->subscriber_id;
						$editlink = 'onclick="ledit('.$k.'); return false"';
						$guilink = '<a href="#" '.$editlink.' title="'.__('Edit subscriber').'"><img src="images/edit-mini.png" alt="'.__('Edit subscriber').'" /></a>';

						if ($datas->subscribed != null) 
							$subscribed = dt::dt2str('%d/%m/%Y', $datas->subscribed).' '.dt::dt2str('%H:%M', $datas->subscribed);
						else 
							$subscribed = __('Never');
						
						if ($datas->lastsent != null) 
							$lastsent = dt::dt2str('%d/%m/%Y', $datas->lastsent).' '.dt::dt2str('%H:%M', $datas->lastsent);
						else 
							$lastsent = __('Never');

						echo
						'<tr class="line">'.
							'<td>'.form::checkbox(array('subscriber['.html::escapeHTML($k).']'), 1).'</td>'.
							'<td class="maximal nowrap">'.html::escapeHTML(text::cutString($datas->email, 50)).'</td>'.
							'<td class="minimal nowrap">'.html::escapeHTML(text::cutString($subscribed, 50)).'</td>'.
							'<td class="minimal nowrap">'.html::escapeHTML(text::cutString(__($datas->state), 50)).'</td>'.
							'<td class="minimal nowrap">'.html::escapeHTML(text::cutString($lastsent, 50)).'</td>'.
							'<td class="minimal nowrap">'.html::escapeHTML(text::cutString(__($datas->modesend), 10)).'</td>'.
							'<td class="status">'.$guilink.'</td>'.
						'</tr>';
					}

					$bstates = array();
					$bstates['-'] = '-';
					if (newsletterPlugin::getCheckUseSuspend()) {
						$bstates[__('Suspend')] = 'suspend';
					}
					$bstates[__('Disable')] = 'disable';
					$bstates[__('Enable')] = 'enable';
					$bstates[__('Last sent')] = 'lastsent';

					$bmails = array();
					$bmails[__('Newsletter')] = 'send';
					$bmails[__('Confirmation')] = 'sendconfirm';
					if (newsletterPlugin::getCheckUseSuspend()) {
						$bmails[__('Suspension')] = 'sendsuspend';
					}
					$bmails[__('Deactivation')] = 'senddisable';
					$bmails[__('Activation')] = 'sendenable';
					//$bmails[__('Changing format')] = 'sendchangemode';

					$bmodes = array();
					$bmodes['-'] = '-';
					$bmodes[__('html')] = 'changemodehtml';
					$bmodes[__('text')] = 'changemodetext';

					// fermeture du tableau
					echo
					'</table>'.
					'<p>'.
						'<a class="small" href="'.html::escapeHTML(newsletterPlugin::admin()).'">'.__('refresh').'</a> - ' .
						'<a class="small" href="#" onclick="checkAll(\'userslist\'); return false">'.__('check all').'</a> - ' .
						'<a class="small" href="#" onclick="uncheckAll(\'userslist\'); return false">'.__('uncheck all').'</a> - ' .
						'<a class="small" href="#" onclick="invertcheckAll(\'userslist\'); return false">'.__('toggle check all').'</a>'.
					'</p>'.
					'<p>'.
						'<input type="submit" value="'.__('Delete').'" onclick="deleteUsersConfirm(); return false" /><br /><br />'.
					'</p>'.
					'<fieldset id="modifyUsers">'.
					'<p class="field">'.
						'<label for="fstates" class="classic">'.__('Set state').'&nbsp;:&nbsp;</label>'.
						form::combo('fstates', $bstates).
						'<input type="button" value="'.__('Set').'" onclick="lset(); return false" />'.
					'</p>'.
					'<p class="field">'.
						'<label for="fmodes" class="classic">'.__('Set format').'&nbsp;:&nbsp;</label>'.
						form::combo('fmodes', $bmodes).
						'<input type="button" value="'.__('Change').'" onclick="lchangemode(); return false" />'.
					'</p>'.
					'<p class="field">'.
						'<label for="fmails" class="classic">'.__('Mail to send').'&nbsp;:&nbsp;</label>'.
						form::combo('fmails', $bmails).
						'<input type="button" value="'.__('Send').'" onclick="lsend(); return false" />'.
					'</p>'.
					'</form>'.
					'</fieldset>'.
					'';
				}
			} else {
				echo
				'<fieldset>'.
					'<p>'.
						'<label class="classic">'.__('Activate the plugin in the Maintenance tab to view all options').'</label>'.
					'</p>'.
				'</fieldset>';
			}

		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* formulaire d'ajout d'un abonné
	*/
	public static function AddEdit()
	{
		// prise en compte du plugin installé
		if (!newsletterCore::isInstalled()) {
			return;
		}

		global $core;
		try
		{
			$blog = &$core->blog;
			$settings = &$blog->settings;
			$allowed = true;
			$mode_combo = array(__('text') => 'text',
							__('html') => 'html');

			$state_combo = array(__('pending') => 'pending',
							__('enabled') => 'enabled',
							__('suspended') => 'suspended',
							__('disabled') => 'disabled');

			if (newsletterPlugin::isActive()) {

				// test si ajout ou édition
				if (!empty($_POST['id']))
				{
					$id = (integer)$_POST['id'];
					$datas = newsletterCore::get($id);
					if ($datas == null) {
						$allowed = false;
					} else {
						$email = $datas->f('email');
						$subscribed = $datas->f('subscribed');
						$lastsent = $datas->f('lastsent');
						$modesend = $datas->f('modesend');
						$regcode = $datas->f('regcode');
						$state = $datas->f('state');
						$form_title = __('Edit a subscriber');
						$form_op = 'edit';
						$form_libel = __('Update');
						$form_id = '<input type="hidden" name="id" value="'.$id.'" />';

						if ($subscribed != null) 
							$subscribed = dt::dt2str($settings->date_format, $subscribed).' @'.dt::dt2str($settings->time_format, $subscribed);
						else 
							$subscribed = __('Never');
				
						if ($lastsent != null) 
							$lastsent = dt::dt2str($settings->date_format, $lastsent).' @'.dt::dt2str($settings->time_format, $lastsent);
						else 
							$lastsent = __('Never');

						$form_update =
						'<p class="field">'.
							'<label for="fsubscribed" class="classic">'.__('Subscribed:').'</label>'.
							form::field('fsubscribed',50,255,$subscribed,'','',true).
						'</p>'.
						'<p class="field">'.
							'<label for="flastsent" class="classic">'.__('Last sent:').'</label>'.
							form::field('flastsent',50,255,$lastsent,'','',true).
						'</p>'.
						'<p class="field">'.
							'<label for="fmodesend" class="classic">'.__('Mode send').'</label>'.
							form::combo('fmodesend',$mode_combo,$modesend).
						'</p>'.
						'<p class="field">'.
							'<label for="fregcode" class="classic">'.__('Registration code:').'</label>'.
							form::field('fregcode',50,255,$regcode,'','',true).
						'</p>'.
						'<p class="field">'.
							'<label for "fstate" class="classic">'.__('Status:').'</label>'.
							form::combo('fstate',$state_combo,$state).
						'</p>'.
						'';
					}
			
				} else {
					$id = -1;
					$email = '';
					$subscribed = '';
					$lastsent = '';
					$modesend = '';
					$status = '';
					$form_title = __('Add a subscriber');
					$form_op = 'add';
					$form_libel = __('Add');
					$form_id = '';
					$form_update = '';
				}

				if (!$allowed) {
					echo __('Not allowed.');
				} else {
					echo 
					'<form action="plugin.php" method="post" id="addedit">'.
					'<fieldset id="addedit">'.
						'<legend>'.$form_title.'</legend>'.
							'<p class="field">'.
								'<label for="femail" class="classic">'.__('Email:').'</label>'.
								form::field('femail',50,255, $email).
								
							'</p>'.
							$form_update.
					'</fieldset>'.	
					'<p>'.
						'<input type="submit" value="'.$form_libel.'" />'.
						'<input type="reset" name="reset" value="'.__('Cancel').'" /> '.
						form::hidden(array('p'),newsletterPlugin::pname()).
						form::hidden(array('op'),$form_op).
						$form_id.
						$core->formNonce().
					'</p>'.
					
					'</form>'.
					'';
				}
			} else {
				echo
				'<fieldset>'.
					'<p>'.
						'<label class="classic">'.__('Activate the plugin in the Maintenance tab to view all options').'</label>'.
					'</p>'.
				'</fieldset>';
			}

		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}


}

?>
