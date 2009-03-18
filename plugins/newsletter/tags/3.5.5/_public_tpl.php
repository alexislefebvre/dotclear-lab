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
					require_once dirname(__FILE__).'/inc/class.captcha.php';
					$read = Captcha::read();
					if ($read != $captcha) 
						$check = false;
				}

				if (!$check) 
					$msg = __('Bad captcha code.');
				else switch ($option) {
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
		
		// ajout d'un bouton retour quelque soit l'evenement
		$msg .= '<p>';
		$msg .= '<br /><br />';
		$msg .= '<form action="'.newsletterCore::url('form').'" method="post" id="comment-form" class="newsletter">';
		$msg .= ''.$core->formNonce().'';
		$msg .= '<label for="nl_back">{{tpl:NewsletterFormLabel id="nl_back"}}</label>';
		$msg .= '<input type="submit" name="nl_back" id="nl_back" value="{{tpl:NewsletterFormLabel id="back"}}" class="submit" />';
		$msg .= '</form>';
		$msg .= '</p>';

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
		return '<?php if (!empty($GLOBALS[\'newsletter\'][\'msg\'])) { ?>'.$content.'<?php } ?>';
	}

	public static function NewsletterFormBlock($attr, $content)
	{
		if (!empty($GLOBALS['newsletter']['form']))
		{
			if (newsletterPlugin::getCaptcha()) {
				require_once dirname(__FILE__).'/inc/class.captcha.php';
				$ca = new Captcha(80, 30, 5);
				$ca->generate();
				$ca->file();
				$ca->write();
			}
		}
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
		if (!newsletterPlugin::getCaptcha()) {
			return '';
		} else {
			require_once dirname(__FILE__).'/inc/class.captcha.php';
			return '<?php echo "<img src=\"'.Captcha::www().'/captcha.img.png\" style=\"vertical-align: middle;\" alt=\"'.__('Captcha').'\" />" ?>';
		}
	}

    public static function NewsletterFormCaptchaInput()
    {
         if (!newsletterPlugin::getCaptcha()) 
         	return '';
         else 
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
				if (!newsletterPlugin::getCaptcha()) 
					return '';
				else 
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

}

?>
