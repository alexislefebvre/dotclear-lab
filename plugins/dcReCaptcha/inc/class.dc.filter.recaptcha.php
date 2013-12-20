<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dcReCaptcha, a plugin for Dotclear 2.
#
# Copyright (c) 2013 Benoit de Marne and contributors
# benoit.de.marne@gmail.com
# Many thanks to Association Dotclear
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

require_once dirname(__FILE__).'/class.dc.recaptcha.php';

class dcFilterReCaptcha extends dcSpamFilter
{
	public $name = 'dcReCaptcha';
	public $has_gui = true;
	public $active = false;
	public $help = 'dcReCaptcha-filter';
	public $dcReCaptcha;
	
	public function __construct($core)
	{
		parent::__construct($core);
	
		if (!$core->auth->isSuperAdmin()) {
			$this->has_gui = false;
		}
		$this->dcReCaptcha = new dcReCaptcha($core);
	}
	
	protected function setInfo()
	{
		$this->description = __('reCaptcha spam filter');
	}	
	
	public function getStatusMessage($status,$comment_id)
	{
		return sprintf(__('Filtered by %s.'),$this->guiLink());
	}	

	public function isSpam($type,$author,$email,$site,$ip,$content,$post_id,&$status)
	{
		$status = 'Filtered';
		
		$session_id = session_id();
		if (empty($session_id)) {
			session_start();
		}
		
		if (isset($_SESSION['recaptcha_ok']) && ($_SESSION['recaptcha_ok'] === true)) {
			# avoid to post several comments from the same captcha
			unset($_SESSION['recaptcha_ok']);
			
			# not spam
			return(false);
		}

		# there is no session, we check the answer of reCaptcha
		$resp = $this->dcReCaptcha->checkAnswer();
		
		if ($resp === true) {
			# avoid to post several comments from the same captcha
			unset($_SESSION['recaptcha_ok']);
			# not spam
			return(false);			
		} else {
			# spam suspected
			return(true);			
		}
	}	
	
	public function gui($url)
	{
		global $core;

		# Settings
		$blog_settings =& $core->blog->settings->dcReCaptcha;
		
		try  {
			# retrieve operation
			$do = (!empty($_POST['do'])) ? (string)$_POST['do'] : 'none';

			###############################################
			# operations
			###############################################
			switch ($do)
			{
				# Modify options for reCaptcha
				case 'saveconfig':			
			
					if (!empty($_POST['reCaptcha_public_key'])) {
						$blog_settings->put('reCaptcha_public_key',trim($_POST['reCaptcha_public_key']),'string','reCaptcha_public_key');
					}
					if(!empty($_POST['reCaptcha_private_key'])) {
						$blog_settings->put('reCaptcha_private_key',trim($_POST['reCaptcha_private_key']),'string','reCaptcha_private_key');
					}
					if(!empty($_POST['reCaptcha_theme'])) {
						$blog_settings->put('reCaptcha_theme',$_POST['reCaptcha_theme'],'string','reCaptcha_theme');
					}
					$reCaptcha_comments_form_enable = (!empty($_POST['reCaptcha_comments_form_enable']) ? true : false);
					$blog_settings->put('reCaptcha_comments_form_enable',$reCaptcha_comments_form_enable,'boolean','Enable reCaptcha on comments form');

					dcPage::addSuccessNotice(__('Filter configuration have been successfully saved.'));
					http::redirect($url);
				break;
	
				case 'none':
				default:
				break;
			}
			
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
		
		if(isset($_GET['saveconfig']))
		{
			$msg = __('Configuration successfully updated');
		}
		
		$dcRecaptcha_settings = $this->dcReCaptcha->getSettings();
		
		$reCaptchaTheme_combo = array(__('red (Default theme)') => 'red',
				__('white') => 'white',
				__('blackglass') => 'blackglass',
				__('clean') => 'clean'
		);

		$res = dcPage::notices();
		
		$res .= '<h3>'.__('reCaptcha settings').'</h3>';

		$res .=
			'<form action="'.html::escapeURL($url).'" method="post" id="reCaptcha">'.
			
			'<p><a href="'.$this->dcReCaptcha->getApiUrl().'">'.__('Get your owns API key').'</a></p>'.

			'<p class="field">'.
			'<label for="reCaptcha_public_key" class="classic required" title="'.__('Required field').'">'.__('Public Key').'</label>'.
			form::field('reCaptcha_public_key',50,255,$dcRecaptcha_settings['public_key']).
			'</p>'.
			'<p class="field">'.
			'<label for="reCaptcha_private_key" class="classic required" title="'.__('Required field').'">'.__('Private key').'</label>'.
			form::field('reCaptcha_private_key',50,255,$dcRecaptcha_settings['private_key']).
			'</p>'.
			'<p class="field">'.
			'<label for="reCaptcha_theme" class="classic">'.__('Theme').'</label>'.
			form::combo('reCaptcha_theme',$reCaptchaTheme_combo,$dcRecaptcha_settings['theme']).
			'</p>'.
		
			'<p class="field">'.
			'<label for="reCaptcha_comments_form_enable" class="classic">'.__('Enable for comments form').'</label>'.
			form::checkbox('reCaptcha_comments_form_enable', 1, $dcRecaptcha_settings['comments_form_enable']).
			'</p>'.

			'<p>If you want add reCAPTCHA form with the plugin ContactMe:'.
				'<ul>'.
					'<li>insert template {{tpl:dcReCaptchaForm}} to form in file contact_me.html</li>'.
					'<li>activate the spam filter option in ContactMe</li>'.
				'</ul>'.
			'</p>'.			
			
			'<p>'.__('reCAPTCHA is a free CAPTCHA service that protects your site against spam.').'</p>'.
			
			'<p><input type="submit" value="'.__('Save').'" /> '.
			'<input type="reset" value="'.__('Cancel').'" /> '.
			form::hidden(array('do'),'saveconfig').
			$core->formNonce().'</p>'.
		
			'</form>';		

		return($res);
	}
	
	
}