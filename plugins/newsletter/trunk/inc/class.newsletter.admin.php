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

/** ==================================================
	administration
================================================== */

class newsletterAdmin
{
	/**
	* uninstall plugin
	*/
	public static function uninstall()
	{
		// delete schema
		global $core;
		try {
			// delete parameters
			newsletterPlugin::deleteSettings();
			newsletterPlugin::delete_version();
			newsletterPlugin::delete_table_newsletter();
			
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* export the schema content
	*/
	public static function Export($onlyblog = true, $outfile = null)
	{
		global $core;
		try {
			$blog = &$core->blog;
			$blogid = (string)$blog->id;

			// generate the content of file from data
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

					// generate component
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

			// write in file
			if(@file_put_contents($filename, $content)) {
				return $msg = __('Datas exported in file').' '.$filename;
			} else {
				throw new Exception(__('Error during export'));
			}
			
			
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* import a backup file
	*/
	public static function importFromBackupFile($infile = null)
	{
		global $core;

		$blog = &$core->blog;
		$blogid = (string)$blog->id;
		$counter=0;
		$counter_ignore=0;
		$counter_failed=0;

		try {
			if (!empty($infile)){
        		//$core->error->add('Traitement du fichier ' . $infile);

				if(file_exists($infile) && is_readable($infile)) {
					$file_content = file($infile);		
		
					foreach($file_content as $ligne) {
						// explode line
						$line = (string) html::clean((string) $ligne);
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
				
						if (!text::isEmail($email)) {
							$core->error->add(html::escapeHTML($email).' '.__('is not a valid email address.'));
							$counter_failed++;
						} else {
							try {
							if(newsletterCore::add($email, $blog_id, $regcode, $modesend)) {
								$subscriber = newsletterCore::getEmail($email);
								if ($subscriber != null) {
								//	$core->error->add('id : '.$subscriber->subscriber_id);
									newsletterCore::update($subscriber->subscriber_id, $email, $state, $regcode, $subscribed, $lastsent, $modesend);
								}								
								$counter++;
							} else
								$counter_ignore++;
							} catch (Exception $e) { 
								 $counter_ignore++;
							} 
						}
					}				

					// message de retour
					if(0 == $counter || 1 == $counter) {
						$retour = $counter . ' ' . __('email inserted');
					} else {
						$retour = $counter . ' ' . __('emails inserted');
					}
					if(0 == $counter_ignore || 1 == $counter_ignore) {
						$retour .= ', ' . $counter_ignore . ' ' . __('email ignored');
					} else {
						$retour .= ', ' . $counter_ignore . ' ' . __('emails ignored');
					}
					if(1 == $counter_failed) {
						$retour .= ', ' . $counter_failed . ' ' . __('line incorrect');
					} else {
						$retour .= ', ' . $counter_failed . ' ' . __('lines incorrect');
					}				

					return $retour;					
				} else {
					throw new Exception(__('No file to read.'));
				}
			} else {
				throw new Exception(__('No file to read.'));
			}				
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* import email addresses from a file
	*/
	public static function importFromTextFile($infile = null)
	{
		global $core;
		try {
			$blog = &$core->blog;
			$blogid = (string)$blog->id;
			$counter=0;
			$counter_ignore=0;
			$counter_failed=0;
			$tab_mail=array();
			
			$newsletter_settings = new newsletterSettings($core);
			$modesend = $newsletter_settings->getSendMode();
                
 			if (!empty($infile)){
        		//$core->error->add('Traitement du fichier ' . $infile['name']);
				files::uploadStatus($infile);
				$filename = $infile['tmp_name'];
			
				if(file_exists($filename) && is_readable($filename)) {
					$file_content = file($filename);

					foreach($file_content as $ligne) {
						$tab_mail=newsletterTools::extractEmailsFromString($ligne);
						
						foreach($tab_mail as $an_email) {
							$email = trim($an_email);
							if (!text::isEmail($email)) {
								$core->error->add(html::escapeHTML($email).' '.__('is not a valid email address.'));
								$counter_failed++;
							} else {
								$regcode = newsletterTools::regcode();
								try {
								if(newsletterCore::add($email, $blog_id, $regcode, $modesend))
									$counter++;
								else
									$counter_ignore++;
								} catch (Exception $e) { 
									 $counter_ignore++;
								} 
							}
						}
					}
					
					// message de retour
					if(0 == $counter || 1 == $counter) {
						$retour = $counter . ' ' . __('email inserted');
					} else {
						$retour = $counter . ' ' . __('emails inserted');
					}
					if(0 == $counter_ignore || 1 == $counter_ignore) {
						$retour .= ', ' . $counter_ignore . ' ' . __('email ignored');
					} else {
						$retour .= ', ' . $counter_ignore . ' ' . __('emails ignored');
					}
					if(1 == $counter_failed) {
						$retour .= ', ' . $counter_failed . ' ' . __('line incorrect');
					} else {
						$retour .= ', ' . $counter_failed . ' ' . __('lines incorrect');
					}				
					
					return $retour;
				} else {
					throw new Exception(__('No file to read.'));
				}
			} else {
				throw new Exception(__('No file to read.'));
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
    
	/**
	* formulaire d'adaptation de template
	*/
	public static function adapt($theme = null)
	{
		if ($theme == null) 
			echo __('No template to adapt.');
		else {
			global $core;
			try {
				$blog = &$core->blog;
				
				// fichier source
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
						case 'hybrid':
						{
							// traitement du theme particulier hybrid
							$patterns[0] = '/<div id=\"maincontent\">[\S\s]*<div id=\"sidenav\"/';
							$replacements[0] = '<div class="maincontent">'."\n".$tcontent."\n".'</div>'."\n".'</div>'."\n".'<div id="sidenav"';
							$patterns[1] = '/<title>.*<\/title>/';
							$replacements[1] = '<title>{{tpl:NewsletterPageTitle encode_html="1"}} - {{tpl:BlogName encode_html="1"}}</title>';
							$patterns[2] = '/dc-home/';
							$replacements[2] = 'dc-newsletter';
							$patterns[3] = '/<script type=\"text\/javascript\">[\S\s]*<\/script>/';
							$replacements[3] = '';
							$patterns[4] = '/<meta name=\"dc.title\".*\/>/';
							$replacements[4] = '<meta name="dc.title" content="{{tpl:NewsletterPageTitle encode_html="1"}} - {{tpl:BlogName encode_html="1"}}" />';
							$patterns[5] = '/<h2 class=\"post-title\">{{tpl:NewsletterPageTitle encode_html=\"1\"}}<\/h2>/';
							$replacements[5] = '<div id="content-info">'."\n".'<h2>{{tpl:NewsletterPageTitle encode_html="1"}}</h2>'."\n".'</div>'."\n".'<div class="content-inner">';
							$patterns[6] = '/<div id=\"sidenav\">[\S\s]*<!-- end #sidenav -->/';
							$replacements[6] = '<div id="sidenav">'."\n".'</div>'."\n".'<!-- end #sidenav -->';
							$patterns[7] = '/<tpl:Categories>[\S\s]*<link rel=\"alternate\"/';
							$replacements[7] = '<link rel=alternate"';
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

					// Writing new template file
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
	public static function displayTabSettings()
	{
		global $core;
		try {
			$blog = &$core->blog;
				
			$mode_combo = array(__('text') => 'text',
							__('html') => 'html');
			$date_combo = array(__('creation date') => 'post_creadt',
							__('update date') => 'post_dt',
							__('publication date') => 'post_upddt'
							);

				
				$newsletter_settings = new newsletterSettings($core);
				
				// initialisation des variables
				$feditorname = $newsletter_settings->getEditorName();
				$feditoremail = $newsletter_settings->getEditorEmail();
				$fcaptcha = $newsletter_settings->getCaptcha();
				$fmode = $newsletter_settings->getSendMode();
				$f_use_default_format = $newsletter_settings->getUseDefaultFormat();
				$fmaxposts = $newsletter_settings->getMaxPosts();
				$fminposts = $newsletter_settings->getMinPosts();
				$f_view_content_post = $newsletter_settings->getViewContentPost();
				$f_size_content_post = $newsletter_settings->getSizeContentPost();
				$fautosend = $newsletter_settings->getAutosend();
				$f_check_notification = $newsletter_settings->getCheckNotification();
				$f_check_use_suspend = $newsletter_settings->getCheckUseSuspend();
				$f_order_date = $newsletter_settings->getOrderDate();
				$f_send_update_post = $newsletter_settings->getSendUpdatePost();

				$rs = $core->blog->getCategories(array('post_type'=>'post'));
				$categories = array('' => '', __('Uncategorized') => 'null');
				while ($rs->fetch()) {
					$categories[str_repeat('&nbsp;&nbsp;',$rs->level-1).'&bull; '.html::escapeHTML($rs->cat_title)] = $rs->cat_id;
				}
				$f_category = $newsletter_settings->getCategory();
				$f_check_subcategories = $newsletter_settings->getCheckSubCategories();
				
				// gestion des paramètres du plugin
				echo	
				'<form action="plugin.php?p=newsletter&amp;m=settings" method="post" id="settings">'.
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
							'<label for="f_use_default_format" class="classic">'.__('Use default format for sending').'</label>'.
							form::checkbox('f_use_default_format',1,$f_use_default_format).
						'</p>'.
						'<p class="field">'.
							'<label for="fmode" class="classic">'.__('Default format for sending').'</label>'.
							form::combo('fmode',$mode_combo,$fmode).
						'</p>'.						
						'<p class="field">'.
							'<label for="f_check_notification" class="classic">'.__('Notification sending').'</label>'.
							form::checkbox('f_check_notification',1,$f_check_notification).
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
							'<label for="f_check_use_suspend" class="classic">'.__('Use suspend option').'</label>'.
							form::checkbox('f_check_use_suspend',1,$f_check_use_suspend).
						'</p>'.
						'<p class="field">'.
							'<label for="f_order_date" class="classic">'.__('Date selection for sorting posts').'</label>'.
							form::combo('f_order_date',$date_combo,$f_order_date).
						'</p>'.						
					'</fieldset>'.

					'<fieldset id="advanced">'.
						'<legend>'.__('Settings for auto letter').'</legend>'.
						'<p class="field">'.
							'<label for="fautosend" class="classic">'.__('Automatic send when create post').'</label>'.
							form::checkbox('fautosend',1,$fautosend).
						'</p>'.
						'<p class="field">'.
							'<label for="f_send_update_post" class="classic">'.__('Automatic send when update post').'</label>'.
							form::checkbox('f_send_update_post',1,$f_send_update_post).
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
							'<label for="f_category" class="classic">'.__('Category').'</label>'.
							form::combo('f_category',$categories,$f_category).
						'</p>'.
						'<p class="field">'.
							'<label for="f_check_subcategories" class="classic">'.__('Include sub-categories').'</label>'.
							form::checkbox('f_check_subcategories',1,$f_check_subcategories).
						'</p>'.
					'</fieldset>'.							
					// boutons du formulaire
					'<p>'.
						'<input type="submit" name="save" value="'.__('Save').'" /> '.
						'<input type="reset" name="reset" value="'.__('Cancel').'" /> '.
					'</p>'.
					'<p>'.
						form::hidden(array('p'),newsletterPlugin::pname()).
						form::hidden(array('op'),'settings').
						$core->formNonce().
					'</p>'.
				'</form>'.
				'';
			
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* paramétrage du plugin
	*/
	public static function displayTabMessages()
	{
		global $core;
		try {
			$blog = &$core->blog;


				$newsletter_settings = new newsletterSettings($core);

				// newsletter
				$f_newsletter_subject = $newsletter_settings->getNewsletterSubject();
				$f_introductory_msg = $newsletter_settings->getIntroductoryMsg();
				$f_concluding_msg = $newsletter_settings->getConcludingMsg();
				$f_msg_presentation_form = $newsletter_settings->getMsgPresentationForm();
				$f_presentation_msg = $newsletter_settings->getPresentationMsg();
				$f_presentation_posts_msg = $newsletter_settings->getPresentationPostsMsg();

				// confirm
				$f_confirm_subject = $newsletter_settings->getConfirmSubject();
				$f_txt_intro_confirm = $newsletter_settings->getTxtIntroConfirm();
				$f_txtConfirm = $newsletter_settings->getTxtConfirm();
				$f_confirm_msg = $newsletter_settings->getConfirmMsg();
				$f_concluding_confirm_msg = $newsletter_settings->getConcludingConfirmMsg();

				// disable
				$f_disable_subject = $newsletter_settings->getDisableSubject();
				$f_txt_intro_disable = $newsletter_settings->getTxtIntroDisable();
				$f_txtDisable = $newsletter_settings->getTxtDisable();
				$f_disable_msg = $newsletter_settings->getDisableMsg();
				$f_concluding_disable_msg = $newsletter_settings->getConcludingDisableMsg();
				$f_txt_disabled_msg = $newsletter_settings->getTxtDisabledMsg();

				// enable
				$f_txt_intro_enable = $newsletter_settings->getTxtIntroEnable();
				$f_txtEnable = $newsletter_settings->getTxtEnable();
				$f_enable_subject = $newsletter_settings->getEnableSubject();
				$f_enable_msg = $newsletter_settings->getEnableMsg();
				$f_concluding_enable_msg = $newsletter_settings->getConcludingEnableMsg();
				$f_txt_enabled_msg = $newsletter_settings->getTxtEnabledMsg();

				// suspend
				$f_suspend_subject = $newsletter_settings->getSuspendSubject();
				$f_suspend_msg = $newsletter_settings->getSuspendMsg();
				$f_txt_suspended_msg = $newsletter_settings->getTxtSuspendedMsg();
				$f_concluding_suspend_msg = $newsletter_settings->getConcludingSuspendMsg();
				$f_txt_intro_suspend = $newsletter_settings->getTxtIntroSuspend();
				$f_txtSuspend = $newsletter_settings->getTxtSuspend();

				// changemode
				$f_change_mode_subject = $newsletter_settings->getChangeModeSubject();
				$f_header_changemode_msg = $newsletter_settings->getHeaderChangeModeMsg();
				$f_footer_changemode_msg = $newsletter_settings->getFooterChangeModeMsg();
				$f_changemode_msg = $newsletter_settings->getChangeModeMsg();

				// resume
				$f_resume_subject = $newsletter_settings->getResumeSubject();
				$f_header_resume_msg = $newsletter_settings->getHeaderResumeMsg();
				$f_footer_resume_msg = $newsletter_settings->getFooterResumeMsg();
				
				// subscribe
				$f_form_title_page = $newsletter_settings->getFormTitlePage();
				$f_txt_subscribed_msg = $newsletter_settings->getTxtSubscribedMsg();				

				// gestion des paramètres du plugin
				echo	
				'<form action="plugin.php" method="post" id="messages">'.
				
					// boutons du formulaire
					'<fieldset>'.
					'<p>'.
					__('Update messages').
					'</p>'.
					'<p>'.
						'<input type="submit" name="save" value="'.__('Update').'" /> '.
						'<input type="reset" name="reset" value="'.__('Cancel').'" /> '.
					'</p>'.
					'</fieldset>'.
				
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
							'<label for="f_form_title_page">'.__('Title page of the subscribe form').'</label>'.
							form::field('f_form_title_page',50,255,html::escapeHTML($f_form_title_page)).
						'</p>'.
						'<p>'.
							'<label for="f_msg_presentation_form">'.__('Message presentation form').' : </label>'.
							form::textarea('f_msg_presentation_form',30,4,html::escapeHTML($f_msg_presentation_form)).
						'</p>'.
						'<p>'.
							'<label for="f_txt_subscribed_msg">'.__('Subcribed message').'</label>'.
							form::field('f_txt_subscribed_msg',50,255,html::escapeHTML($f_txt_subscribed_msg)).
						'</p>'.
					'</fieldset>'.

					// boutons du formulaire
					'<fieldset>'.
					'<p>'.
					__('Update messages').
					'</p>'.					
					'<p>'.
						'<input type="submit" name="save" value="'.__('Update').'" /> '.
						'<input type="reset" name="reset" value="'.__('Cancel').'" /> '.
					'</p>'.
					'</fieldset>'.
					'<p>'.
						form::hidden(array('p'),newsletterPlugin::pname()).
						form::hidden(array('op'),'messages').
						$core->formNonce().
					'</p>'.
					'</form>'.
				'';


		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}

	}

	/**
	* edit the CSS for letters
	*/
	public static function displayTabEditCSS()
	{
		global $core;

		try {
			$blog = &$core->blog;



				$letter_css = new newsletterCSS($core);
				$f_name = $letter_css->getFilenameCSS();
				$f_content = $letter_css->getLetterCSS();
				$f_editable = $letter_css->isEditable();
				
				$default_folder = path::real(newsletterPlugin::folder().'..');
				
				if ($default_folder == $letter_css->getPathCSS())
					$f_editable = false;
				
				// gestion des paramètres du plugin
				echo	
				'<form action="plugin.php" method="post" id="file-form">'.

					'<fieldset id="edit_css_file">'.
						'<legend>'.__('File editor').'</legend>'.
						'<p>'.sprintf(__('Editing file %s'),'<strong>'.$f_name).'</strong></p>'.
						'<p>'.
							form::textarea('f_content',72,25,html::escapeHTML($f_content),'maximal','',!$f_editable).
						'</p>';

					if(!$f_editable)
							echo '<p>'.__('This file is not writable. Please check your theme files permissions.').'</p>';
					else {
					echo
					'<p>'.
						'<input type="submit" name="write" value="'.__('save').' (s)" accesskey="s" /> '.
						form::hidden(array('p'),newsletterPlugin::pname()).
						form::hidden(array('op'),'write_css').
						$core->formNonce().
					'</p>';
					}
					echo
					'</fieldset>'.
				'</form>'.
				'';
						

		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}

	}	

	/**
	* paramétrage des options de planification
	*/
	public static function displayTabPlanning()
	{
		global $core;
		try {
			$blog = &$core->blog;

				// Utilisation de dcCron
				if (isset($blog->dcCron)) {
					$newsletter_cron=new newsletterCron($core);
					$newsletter_settings = new newsletterSettings($core);

					$f_check_schedule = $newsletter_settings->getCheckSchedule();
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
								__('samples').' : ( 1 '.__('day').' = 86400s / 1 '.__('week').' = 604800s / 28 '.__('days').' =  2420000s )'.
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
			

		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}

	}

	/**
	* Maintenance du plugin
	*/
	public static function displayTabMaintenance()
	{
		global $core;
		
		# Settings compatibility test
		if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
			$blog_settings =& $core->blog->settings->newsletter;
			$system_settings = $core->blog->settings->system;
		} else {
			$blog_settings =& $core->blog->settings;
			$system_settings->system_settings =& $core->blog->settings;
		}		
		
		try {
			$blog = &$core->blog;
			$auth = &$core->auth;
			$url = &$core->url;
			$blogurl = &$blog->url;
			$urlBase = http::concatURL($blogurl, $url->getBase('newsletter'));			
			$core->themes = new dcThemes($core);
			$core->themes->loadModules($core->blog->themes_path,null);

			$bthemes = array();
			foreach ($core->themes->getModules() as $k => $v)
			{
				if (file_exists($blog->themes_path . '/' . $k . '/tpl/home.html'))
					$bthemes[html::escapeHTML($v['name'])] = $k;
			}			
			$theme = $system_settings->theme;

			$sadmin = (($auth->isSuperAdmin()) ? true : false);
		
			echo
			'<fieldset>'.
				'<legend>'.__('Plugin state').'</legend>'.			
				'<form action="plugin.php" method="post" id="state">'.
					'<p class="field">'.
					'<label for="newsletter_flag" class="classic">'.__('Enable plugin').'</label>'.
					form::checkbox('newsletter_flag', 1, $blog_settings->newsletter_flag).
					'</p>'.
					'<p>'.
						'<input type="submit" value="'.__('Save').'" /> '.
						'<input type="reset" value="'.__('Cancel').'" /> '.
					'</p>'.
					form::hidden(array('p'),newsletterPlugin::pname()).
					form::hidden(array('op'),'state').
					$core->formNonce().
				'</form>'.
			'</fieldset>'.
			'';

		if ($blog_settings->newsletter_flag) {
				echo
				// export/import pour le blog
				'<fieldset>'.
				'<legend>'.__('Import/Export subscribers list').'</legend>'.
					'<form action="plugin.php" method="post" id="export" name="export">'.
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
						'</p>'.
						form::hidden(array('p'),newsletterPlugin::pname()).
						form::hidden(array('op'),'export').
						$core->formNonce().											
					'</form>'.
					
					'<form action="plugin.php" method="post" id="import" name="import">'.
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
						'<input type="submit" value="'.__('Import').'" />'.
						'</p>'.
						form::hidden(array('p'),newsletterPlugin::pname()).
						form::hidden(array('op'),'import').
						$core->formNonce().											
					'</form>'.					
					
				'</fieldset>'.
				'';

				echo
				// reprise d'une liste d'adresse email
				'<fieldset>'.
				'<legend>'.__('Importing a subscribers list from a file text in the current blog').'</legend>'.				
					'<form action="plugin.php" method="post" id="reprise" name="reprise" enctype="multipart/form-data">'.
						'<p>'.
						'<label class="classic required" title="'.__('Required field').'">'.
						__('File to import :').' '.
						'<input type="file" name="file_reprise" />'.
						'</label>'.
						'</p>'.
						'<p>'.
						'<label class="classic required" title="'.__('Required field').'">'.
						__('Your password:').' '.
						form::password(array('your_pwd'),20,255).'</label></p>'.						
						'<p>'.
						'<input type="submit" value="'.__('Launch reprise').'" />'.
						'</p>'.
						form::hidden(array('p'),newsletterPlugin::pname()).
						form::hidden(array('op'),'reprise').
						$core->formNonce().
					'</form>'.
				'</fieldset>'.
				'';
				
				if ($sadmin) {
					echo
					// adaptation du template
					'<fieldset>'.
					'<legend>'.__('Adapt the template for the theme').'</legend>'.
						'<form action="plugin.php" method="post" id="adapt">'.
							'<p>'.__('<strong>Caution :</strong> use the theme adapter only if you experience some layouts problems when viewing newsletter form on your blog.')."</p>".
							'<p><label class="classic" for="fthemes">'.__('Theme name').' : '.
							form::combo('fthemes',$bthemes,$theme).
							'</label></p>'.
							'<p>'.
							'<input type="submit" value="'.__('Adapt').'" />'.
							'</p>'.
							'<p>'.
							'<a href="'.$urlBase.'/test'.'" target="_blank">'.__('Clic here to test the template.').'</a>'.
							'</p>'.
							form::hidden(array('p'),newsletterPlugin::pname()).
							form::hidden(array('op'),'adapt').
							$core->formNonce().
						'</form>'.
					'</fieldset>'.
					'';
				}

				if ($sadmin) {
					echo
					// Nettoyage de la base
					'<fieldset>'.
					'<legend>'.__('Erasing all informations about newsletter in database').'</legend>'.
						'<form action="plugin.php" method="post" id="erasingnewsletter">'.
							'<p>'.__('<strong>Caution :</strong> please backup your database before use this option.')."</p>".
							'</p>'.
							'<p>'.
							//'<input type="submit" value="'.__('Erasing').'" name="delete" class="delete" onclick="erasingnewsletterConfirm(); return false" />'.
							'<input type="submit" value="'.__('Erasing').'" name="delete" class="delete"/>'.
							'</p>'.
							form::hidden(array('p'),newsletterPlugin::pname()).
							form::hidden(array('op'),'erasingnewsletter').
							$core->formNonce().
						'</form>'.
					'</fieldset>'.
					'';
				}
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


				// test si ajout ou édition
				if (!empty($_GET['id']))
				{
					$id = (integer)$_GET['id'];
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
					'<form action="plugin.php?p=newsletter&amp;m=subscribers" method="post" id="addedit">'.
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
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}


}

?>