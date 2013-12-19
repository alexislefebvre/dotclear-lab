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

require_once(dirname(__FILE__).'/lib.google.recaptcha.php');

class dcReCaptcha 
{
	protected $core;
	protected $blog_settings;
	protected $settings;
	
	/**
	 * Class constructor
	 *
	 * @param:	$core	dcCore
	 */
	public function __construct($core)
	{
		$core->blog->settings->addNamespace('dcReCaptcha');
		$this->blog_settings =& $core->blog->settings->dcReCaptcha;
		
		$public_key = $this->blog_settings->reCaptcha_public_key;
		$private_key = $this->blog_settings->reCaptcha_private_key;
		$theme = $this->blog_settings->reCaptcha_theme;
		$comments_form_enable = $this->blog_settings->reCaptcha_comments_form_enable;
		
		$this->settings = array(
				'public_key' => $public_key,
				'private_key' => $private_key,
				'theme' => $theme,
				'comments_form_enable' => $comments_form_enable
				);

	}	

	public function setSettings($public_key,$private_key,$theme,$comments_form_enable) {
		$this->settings = array(
				'public_key' => $public_key,
				'private_key' => $private_key,
				'theme' => $theme,
				'comments_form_enable' => $comments_form_enable
		);		
	}
		
	public function getSettings() {
		return $this->settings;
	}

	public function checkAnswer() {
		
		if (!isset($_POST["recaptcha_challenge_field"]) || !isset($_POST["recaptcha_response_field"])) {
			return null;
		} else {
			$recaptcha_challenge_field = $_POST["recaptcha_challenge_field"];
			$recaptcha_response_field = $_POST["recaptcha_response_field"];
		}
		
		$resp = recaptcha_check_answer($this->settings['private_key'],
				http::realIP(),
				$recaptcha_challenge_field,
				$recaptcha_response_field);		
		
		if(!$resp->is_valid) {
			return $resp->error;
		} else {
			return (boolean) true;
		}
	}
	
	public function getReCaptchaHtml() {
		return recaptcha_get_html($this->settings['public_key'],null);
	}

	public function getReCaptchaJs($lang='fr') {
		
		$res =
		'<script type="text/javascript">'."\n".
		'var RecaptchaOptions = {'."\n".
		'	 lang : \''.$lang.'\','."\n".
		'	 theme : \''.$this->settings['theme'].'\''."\n".
		'};'."\n".
		'</script>'."\n";
		
		return $res;
	}
	
	public function getApiUrl() {
		return 'http://www.google.com/recaptcha';
	}
	
}
