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

require dirname(__FILE__).'/_widgets.php';

// chargement des librairies
require_once dirname(__FILE__).'/inc/class.captcha.php';
require_once dirname(__FILE__).'/inc/class.newsletter.plugin.php';
require_once dirname(__FILE__).'/inc/class.newsletter.core.php';

// ajout des templates
$core->tpl->addValue('Newsletter', array('tplNewsletter', 'Newsletter'));
$core->tpl->addValue('NewsletterPageTitle', array('tplNewsletter', 'NewsletterPageTitle'));
$core->tpl->addValue('NewsletterTemplateNotSet', array('tplNewsletter', 'NewsletterTemplateNotSet'));
$core->tpl->addBlock('NewsletterBlock', array('tplNewsletter', 'NewsletterBlock'));
$core->tpl->addBlock('NewsletterMessageBlock', array('tplNewsletter', 'NewsletterMessageBlock'));
$core->tpl->addBlock('NewsletterFormBlock', array('tplNewsletter', 'NewsletterFormBlock'));
$core->tpl->addValue('NewsletterFormSubmit', array('tplNewsletter', 'NewsletterFormSubmit'));
$core->tpl->addValue('NewsletterFormRandom', array('tplNewsletter', 'NewsletterFormRandom'));
$core->tpl->addValue('NewsletterFormCaptchaImg', array('tplNewsletter', 'NewsletterFormCaptchaImg'));
$core->tpl->addValue('NewsletterFormCaptchaInput', array('tplNewsletter', 'NewsletterFormCaptchaInput'));
$core->tpl->addValue('NewsletterFormLabel', array('tplNewsletter', 'NewsletterFormLabel'));
$core->tpl->addValue('NewsletterMsgPresentationForm', array('tplNewsletter', 'NewsletterMsgPresentationForm'));
$core->tpl->addBlock('NewsletterIfUseDefaultFormat',array('tplNewsletter','NewsletterIfUseDefaultFormat'));
$core->tpl->addValue('NewsletterFormFormatSelect', array('tplNewsletter', 'NewsletterFormFormatSelect'));
$core->tpl->addValue('NewsletterFormActionSelect', array('tplNewsletter', 'NewsletterFormActionSelect'));
$core->tpl->addBlock('NewsletterIfUseCaptcha',array('tplNewsletter','NewsletterIfUseCaptcha'));

class tplNewsletter
{
	/**
	 * Select the newsletter to send
	 *
 	 * @return:	string	msg
	 */
	public static function Newsletter()
	{
		global $core;
		
		if (isset($GLOBALS['newsletter']['cmd'])) 
			$cmd = (string) html::clean($GLOBALS['newsletter']['cmd']);
		else 
			$cmd = 'about';
      
		if (isset($GLOBALS['newsletter']['email'])) 
			$email = (string) html::clean($GLOBALS['newsletter']['email']);
		else 
			$email = null;
      
		if (isset($GLOBALS['newsletter']['code'])) 
			$code = (string) html::clean($GLOBALS['newsletter']['code']);
		else 
			$code = null;

		if (isset($GLOBALS['newsletter']['modesend'])) 
			$modesend = (string) html::clean($GLOBALS['newsletter']['modesend']);
		else 
			$modesend = null;

		switch ($cmd) {
			case 'test':
				$msg = __('Test display template.');
				break;

			case 'about':
				$msg = __('About Newsletter ...');
				//$msg = __('About'). ' ' . newsletterPlugin::dcName() . ' ...' ;
				$msg .= '<br />'. __('Version'). ' : ' . newsletterPlugin::dcVersion();
				$msg .= '<br />'. __('Authors'). ' : ' . newsletterPlugin::dcAuthor();
				$msg .= '<br />'. __('Description'). ' : ' . newsletterPlugin::dcDesc();
				break;

			case 'confirm':
				if ($email == null || $code == null)
					$msg = __('Missing informations. ');
				else {
					$rs = newsletterCore::getemail($email);
					if ($rs == null || $rs->regcode != $code) 
						$msg = __('Your subscription code is invalid.');
					else if ($rs->state == 'enabled') 
						$msg = __('Account already confirmed.');
					else {
						newsletterCore::sendEnable($rs->subscriber_id);
						$msg = __('Your subscription is confirmed.').'<br />'.__('You will soon receive an email.');
					}
				}
				break;

			case 'enable':
				if ($email == null)
					$msg = __('Missing informations. ');
				else {
					$rs = newsletterCore::getemail($email);
					if ($rs == null) 
						$msg = __('Unable to find you account informations.');
					else if ($rs->state == 'enabled') 
						$msg = __('Account already enabled.');
					else {
						newsletterCore::sendEnable($rs->subscriber_id);
						$msg = __('Your account is enabled.').'<br />'.__('You will soon receive an email.');
					}
				}
				break;

			case 'disable':
				if ($email == null)
					$msg = __('Missing informations. ');
				else {
					$rs = newsletterCore::getemail($email);
					if ($rs == null) 
						$msg = __('Unable to find you account informations.');
					else if ($rs->state == 'disabled') 
						$msg = __('Account already disabled.');
					else {
						newsletterCore::sendDisable($rs->subscriber_id);
						$msg = __('Your account is disabled.').'<br />'.__('You will soon receive an email.');
					}
				}
				break;

			case 'suspend':
				if ($email == null)
					$msg = __('Missing informations. ');
				else {
					$rs = newsletterCore::getemail($email);
					if ($rs == null) 
						$msg = __('Unable to find you account informations.');
					else if ($rs->state == 'suspended') 
						$msg = __('Account already suspended.');
					else {
						newsletterCore::sendSuspend($rs->subscriber_id);
						$msg = __('Your account is suspended.').'<br />'.__('You will soon receive an email.');
					}
				}
				break;

			case 'changemode':
				if ($email == null)
					$msg = __('Missing informations. ');
				else {
					$rs = newsletterCore::getemail($email);
					if ($rs == null) 
						$msg = __('Unable to find you account informations.');
					else {
						newsletterCore::sendChangeMode($rs->subscriber_id);
						$msg = __('Your sending format is').$modesend.'<br />'.__('You will soon receive an email.');
					}
				}
				break;

			case 'submit':
				$email = (string)html::clean($_POST['nl_email']);
				$option = (string)html::clean($_POST['nl_option']);
				$modesend = (string)html::clean($_POST['nl_modesend']);
				$check = true;
				if (newsletterPlugin::getCaptcha()) {
					$captcha = (string)html::clean($_POST['nl_captcha']);
					$read = Captcha::read();
					if ($read != $captcha) {
						$check = false;
						$ca = new Captcha(80, 30, 5);
						$ca->generate();
						$ca->file();
						$ca->write();
					}
				}

				if (!$check) {
					$msg = __('Bad captcha code.');
				} else switch ($option) {
					case 'subscribe':
						$msg = newsletterCore::accountCreate($email,null,$modesend);
						break;
					
					case 'unsubscribe':
						$msg = newsletterCore::accountDelete($email);
						break;

					case 'suspend':
						$msg = newsletterCore::accountSuspend($email);
						break;

					case 'resume':
						$msg = newsletterCore::accountResume($email);
						break;

					case 'changemode':
						$msg = newsletterCore::accountChangeMode($email,$modesend);
						break;

					default:
						$msg = __('Error in formular.');
						break;
				}
				break;

			default:
				$msg = '';
				break;
		}

		return $msg;
	}

	/**
	* titre de la page html
	*/
	public static function NewsletterPageTitle()
	{
		return __('Newsletter');
	}	

	/**
	* indication à l'utilisateur que la page newsletter n'a pas été initialisée
	*/
	public static function NewsletterTemplateNotSet()
	{
		return '<?php echo newsletterCore::TemplateNotSet(); ?>';
	}

	public static function NewsletterBlock($attr, $content)
	{
		return $content;
	}

	public static function NewsletterMessageBlock($attr, $content)
	{
		// ajout d'un bouton retour quelque soit l'evenement
		global $core;
		$text = '<?php echo "'.
			'<p>'.
			'<br /><br />'.
			'<form action=\"'.newsletterCore::url('form').'\" method=\"post\" id=\"comment-form\" class=\"newsletter\">'.
			$core->formNonce().
			//'<label for=\"nl_back\">'.__('Back').'</label>'.
			'<input type=\"submit\" name=\"nl_back\" id=\"nl_back\" value=\"'.__('Back').'\" class=\"submit\" />'.
			'</form>'.
			'</p>'.
			'" ?>';		
		return '<?php if (!empty($GLOBALS[\'newsletter\'][\'msg\'])) { ?>'.$content.$text.'<?php } ?>';
	}

	public static function NewsletterFormBlock($attr, $content)
	{
		return '<?php	if (!empty($GLOBALS[\'newsletter\'][\'form\'])) { ?>'.$content.'<?php } ?>';
	}

	/**
	* adresse de soumission du formulaire
	*/
	public static function NewsletterFormSubmit()
	{
		return '<?php echo newsletterCore::url(\'submit\'); ?>';
	}

	public static function NewsletterFormRandom()
	{
		return '<?php  echo "'.newsletterCore::getRandom().'" ?>';
	}

	public static function NewsletterFormCaptchaImg()
	{
		return '<?php echo "<img src=\"'.Captcha::www().'/captcha.img.png\" style=\"vertical-align: middle;\" alt=\"'.__('Captcha').'\" />" ?>';
	}

	public static function NewsletterFormCaptchaInput()
	{
		return '<?php echo "<input type=\"text\" name=\"nl_captcha\" id=\"nl_captcha\" value=\"\" style=\"width:90px; vertical-align:top;\" />" ?>';
	}

	/* 
	 * labels du formulaire 
	 */	
	public static function NewsletterFormLabel($attr, $content)
	{
		switch ($attr['id'])
		{
			case 'ok':
				return '<?php echo __(\'Send\') ?>';

			case 'subscribe':
				return '<?php echo __(\'Subscribe\') ?>';

			case 'unsubscribe':
				return '<?php echo __(\'Unsubscribe\') ?>';

			case 'suspend':
				return '<?php echo __(\'Suspend\') ?>';
				// __('Suspend') 

			case 'resume':
				return '<?php echo __(\'Resume\') ?>';
				// __('Resume') 

			case 'nl_email':
				return '<?php echo __(\'Email\') ?>';

			case 'nl_option':
				return '<?php echo __(\'Action\') ?>';

			case 'nl_captcha':
				return '<?php echo  \'<label for="nl_captcha">\'. __(\'Captcha\') .\'</label>\' ?>';

			case 'nl_submit':
				return '';

			case 'html':
				return '<?php echo __(\'html\') ?>';

			case 'text':
				return '<?php echo __(\'text\') ?>';
				// __('text') 

			case 'nl_modesend':
				return '<?php echo __(\'Format\') ?>';

			case 'changemode':
				return '<?php echo __(\'Change format\') ?>';
				// __('Change format') 

			case 'back':
				return '<?php echo __(\'Back\') ?>';

		}
	}

	/* 
	 * message de présentation du formulaire 
	 */
	public static function NewsletterMsgPresentationForm()
	{
		return newsletterPlugin::getMsgPresentationForm();
	}

	/* 
	 * affichage du choix du format si le mode par défaut n'est pas défini
	 */
	public static function NewsletterIfUseDefaultFormat($attr,$content)
	{
		return
			'<?php if (!newsletterPlugin::getUseDefaultFormat()) : ?>'.
				$content.
			'<?php endif; ?>';
	}

	/* 
	 * affichage du choix du format si le mode par défaut n'est pas défini
	 */
	public static function NewsletterFormFormatSelect($attr,$content)
	{
		$text = '<?php echo "'.
			'<label for=\"nl_modesend\">'.__('Format').'&nbsp;:</label>'.
			'<select style=\"border:1px inset silver; width:150px;\" name=\"nl_modesend\" id=\"nl_modesend\" size=\"1\" maxlength=\"255\">'.
			'<option value=\"html\" selected=\"selected\">'.__('html').'</option>'.
			'<option value=\"text\">'.__('text').'</option>'.
			'</select>'.
			'" ?>';

		return $text;
	}

	public static function NewsletterFormActionSelect($attr,$content)
	{
		$text = '<?php echo "'.
		'<label for=\"nl_option\">'.__('Action').'&nbsp;:</label>'.
		'<select style=\"border:1px inset silver; width:150px;\" name=\"nl_option\" id=\"nl_option\" size=\"1\" maxlength=\"255\">'.
		'<option value=\"subscribe\" selected=\"selected\">'.__('Subscribe').'</option>'.
		'<option value=\"changemode\">'.__('Change format').'</option>';
		
		if(newsletterPlugin::getCheckUseSuspend()) {
			$text .= '<option value=\"suspend\">'.__('Suspend').'</option>';
		}
		
		$text .= '<option value=\"resume\">'.__('Resume').'</option>'.
		'<option value=\"\">---</option>'.
		'<option value=\"unsubscribe\">'.__('Unsubscribe').'</option>'.
		'</select>'.
		'" ?>';
		return $text;
	}

	/* 
	 * affichage du choix du format si le mode par défaut n'est pas défini
	 */
	public static function NewsletterIfUseCaptcha($attr,$content)
	{
		if (!empty($GLOBALS['newsletter']['form']) && newsletterPlugin::getCaptcha()) {
			$ca = new Captcha(80, 30, 5);
			$ca->generate();
			$ca->file();
			$ca->write();
		}
		return
			'<?php if (newsletterPlugin::getCaptcha()) : ?>'.
				$content.
			'<?php endif; ?>';
	}

}

/* 
 * classe pour l'affichage du widget public
 */
class publicWidgetsNewsletter
{
	/**
	* initialisation du widget
	*/
	public static function initWidgets(&$w)
	{
		global $core;
		try {
			// prise en compte de l'état d'activation du plugin
			if (!newsletterPlugin::isActive()) 
				return;

			// prise en compte paramètre: uniquement sur la page d'accueil
			$url = &$core->url;
			if ($w->homeonly && $url->type != 'default')  {
				return;
			}

			// paramétrage des variables
			$plugin_name = __('Newsletter');
			$title = ($w->title) ? html::escapeHTML($w->title) : $plugin_name;
			$showTitle = ($w->showtitle) ? true : false;
			$subscription_link = ($w->subscription_link) ? html::escapeHTML($w->subscription_link) : __('Subscription link');
			
			$text = '';

			// mise en place du contenu du widget dans $text
			if ($w->inwidget) {
				$link = newsletterCore::url('submit');
				$text .=
				'<form action="'.$link.'" method="post" id="nl_form">'.
				$core->formNonce().
				form::hidden(array('nl_random'),newsletterCore::getRandom()).
				'<ul>'.
				'<li><label for="nl_email">'.__('Email').'</label>&nbsp;:&nbsp;'.
				form::field(array('nl_email','nl_email'),15,255).
				'</li>'.
				'<li><label for="nl_modesend">'.__('Format').'</label>&nbsp;:&nbsp;'.
				'<select style="border:1px inset silver; width:140px;" name="nl_modesend" id="nl_modesend" size="1" maxlength="255">'.
					'<option value="html" selected="selected">'.__('html').'</option>'.
					'<option value="text">'.__('text').'</option>'.
				'</select>'.
				'<li><label for="nl_submit">'.__('Actions').'</label>&nbsp;:&nbsp;'.
				'<select style="border:1px inset silver; width:140px;" name="nl_option" id="nl_option" size="1" maxlength="255">'.
					'<option value="subscribe" selected="selected">'.__('Subscribe').'</option>'.
					'<option value="changemode">'.__('Change format').'</option>'.
					'<option value="suspend">'.__('Suspend').'</option>'.
					'<option value="resume">'.__('Resume').'</option>'.
					'<option value="">---</option>'.
					'<option value="unsubscribe">'.__('Unsubscribe').'</option>'.
				'</select>'.
				/*
				'<li><label for="nl_submit">'.__('Actions').'</label>&nbsp;:<br />'.
				form::radio(array('nl_option'),'subscribe', true).__('Subscribe').'<br />'.
				form::radio(array('nl_option'),'unsubscribe').__('Unsubscribe').'<br />'.
				//*/
				'</li>';

				if (newsletterPlugin::getCaptcha()) {
					require_once dirname(__FILE__).'/inc/class.captcha.php';							
					$as = new Captcha(80, 30, 5);
					$as->generate();
					$as->file();
					$as->write();
						
					$text .=
					//'<p>'.
					'<li><label for="nl_captcha">'.__('Captcha').'</label>&nbsp;:<br />'.
					'<img src="'.Captcha::www().'/captcha.img.png" alt="'.__('Captcha').'" /><br />'.
					form::field(array('nl_captcha','nl_captcha'),9,30).
					'</li>';
					//'</p>'.
				}
				
				$text .=
				'<p><input class="submit" type="submit" name="nl_submit" id="nl_submit" value="'.__('Send').'" /></p>'.

				'</ul>'.
				//'</fieldset>'.
				'</form>';
				
			} else {
         			$link = newsletterCore::url('form');
				if ($w->insublink) {
					$title = str_replace('%I', '<img src="?pf=newsletter/icon.png" alt="" width="16" height="16" style="margin: 0 3px; vertical-align: middle;" />', $title);
					$text = $w->text ? $w->text : '';

					$text .= '<ul><li><a href="'.$link.'">'.$subscription_link.'</a></li></ul>';
				} else {
	         			$title = '<a href="'.$link.'">'.$title.'</a>';
	            		$title = str_replace('%I', '</a><img src="?pf=newsletter/icon.png" alt="newsletter" width="16" height="16" style="margin:0 3px; vertical-align:middle;" /> <a href="'.$link.'">', $title);
				}
			}

			// renvoi du code à afficher pour le widget
			if ($showTitle === true) 
				$title = '<h2>'.$title.'</h2>';
			else 
				$title = '';
			
			return "\n".'<div class="'.newsletterPlugin::pname().'">'.$title.$text.'</div>'."\n";
			//return "\n".'<div class="categories">'.$title.$text.'</div>'."\n";
			

		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
}

// gestionnaire d'url
class urlNewsletter extends dcUrlHandlers
{
    // gestion des paramètres
    public static function newsletter($args)
    {
		global $core;
		try	{
			// tests des arguments
	      	if (empty($args) || $args == '') {
	      		self::p404();
	      	}

			// initialisation des variables
			$tpl = &$core->tpl;
			$cmd = null;
			$GLOBALS['newsletter']['cmd'] = null;
			$GLOBALS['newsletter']['msg'] = false;
			$GLOBALS['newsletter']['form'] = false;
			$GLOBALS['newsletter']['email'] = null;
			$GLOBALS['newsletter']['code'] = null;
			$GLOBALS['newsletter']['modesend'] = null;

			// décomposition des arguments et aiguillage
			$params = explode('/', $args);
			if (isset($params[0]) && !empty($params[0])) 
				$cmd = (string)html::clean($params[0]);
			else 
				$cmd = null;
	      
	      	if (isset($params[1]) && !empty($params[1])) {
	      		$email = base64_decode( (string)html::clean($params[1]) );
	      	}
			else 
				$email = null;
	      
	      	if (isset($params[2]) && !empty($params[2])) 
	      		$regcode = (string)html::clean($params[2]);
			else 
				$regcode = null;			

	      	if (isset($params[3]) && !empty($params[3])) 
	      		$modesend = base64_decode( (string)html::clean($params[3]) );
			else 
				$modesend = null;			

         
         	switch ($cmd) {
				case 'test':
				case 'about':
				    $GLOBALS['newsletter']['msg'] = true;
				    break;

				case 'form':
				    $GLOBALS['newsletter']['form'] = true;
				    break;
                
				case 'submit':
					$GLOBALS['newsletter']['msg'] = true;
					break;
					
				case 'confirm':
				case 'enable':
				case 'disable':
				case 'suspend':
				case 'changemode':
				case 'resume':
					{
						if ($email == null) 
							self::p404();
						$GLOBALS['newsletter']['msg'] = true;
					}
					break;
			}

			$GLOBALS['newsletter']['cmd'] = $cmd;
			$GLOBALS['newsletter']['email'] = $email;
			$GLOBALS['newsletter']['code'] = $regcode;
			$GLOBALS['newsletter']['modesend'] = $modesend;

			// préparation de l'utilisation du moteur de template
			$tpl->setPath($tpl->getPath(), dirname(__FILE__).'/default-templates');
			$file = $tpl->getFilePath('subscribe.newsletter.html');

			// utilise le moteur de template pour générer la page pour le navigateur
			files::touch($file);

			header('Pragma: no-cache');
			header('Cache-Control: no-cache');
	        	self::serveDocument('subscribe.newsletter.html','text/html',false,false);
	        	exit;
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
    }
}

?>
