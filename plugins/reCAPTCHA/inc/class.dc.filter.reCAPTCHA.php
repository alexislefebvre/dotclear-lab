<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of reCAPTCHA, a plugin for Dotclear 2
# Copyright 2009 Moe (http://gniark.net/)
#
# reCAPTCHA is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# reCAPTCHA is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with this program. If not, see
# <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

class dcFilterreCAPTCHA extends dcSpamFilter
{
	public $name = 'reCAPTCHA';
	public $has_gui = true;
	
	public function __construct(&$core)
	{
		parent::__construct($core);
	}
	
	protected function setInfo()
	{
		$this->description = __('Check comments with reCAPTCHA');
	}
	
	public function getStatusMessage($status,$comment_id)
	{
		return sprintf(__('Filtered by %s.'),$this->guiLink());
	}

	public function isSpam($type,$author,$email,
		$site,$ip,$content,$post_id,&$status)
	{
		$status = 'Filtered';
		
		# start session
		$session_id = session_id();
		if (empty($session_id)) {session_start();}
		
		if (isset($_SESSION['recaptcha_ok'])
			&& ($_SESSION['recaptcha_ok'] === true))
		{
			# avoid to post several comments from the same CAPTCHA
			unset($_SESSION['recaptcha_ok']);
			# this is not a spam
			return(false);
		}
		
		# there is no session, we check the answer of reCAPTCHA
		$check = reCAPTCHAPublic::checkAnswer(
			$GLOBALS['core']->blog->settings->recaptcha_private_key,
			http::realIP());
		
		if ($check === true) {
			# avoid to post several comments from the same CAPTCHA
			unset($_SESSION['recaptcha_ok']);
			# this is not a spam
			return(false);
		}
		else
		{
			# this is a spam
			return(true);
		}
	}
	
	public function gui($url)
	{
		require_once(dirname(__FILE__).'/../recaptcha-php-1.10/recaptchalib.php');
		
		global $core, $_lang;
		
		l10n::set(dirname(__FILE__).'/../locales/'.$_lang.'/admin');
		
		$settings =& $core->blog->settings;
		
		try
		{
			if (!empty($_POST['saveconfig']))
			{
				$settings->setNameSpace('recaptcha');
				$settings->put('recaptcha_public_key',
					$_POST['recaptcha_public_key'],
					'string', 'reCAPTCHA public key');
				$settings->put('recaptcha_private_key',
					$_POST['recaptcha_private_key'],
					'string', 'reCAPTCHA private key');
				$settings->put('recaptcha_theme',
					$_POST['recaptcha_theme'],
					'string', 'reCAPTCHA theme');
				
				http::redirect($url.'&saveconfig=1');
			}
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
		
		$themes = array(
			__('Red') => 'red',
			__('White') => 'white',
			__('Blackglass') => 'blackglass',
			__('Clean') => 'clean',
		);
		
		$res = '';
		
		if (isset($_GET['saveconfig']))
		{
			$res .= '<p class="message">'.
				__('Configuration successfully updated.').'</p>';
		}
		
		$res .= '<p>'.
			__('reCAPTCHA keys are required to use the reCAPTCHA service').
			'</p>';
		
		$res .= '<p><a href="'.recaptcha_get_signup_url(
			preg_replace('/^(http([s]*)\:\/\/)/i','',$core->blog->host),
			'Dotclear 2').'">'.
			__('Get reCAPTCHA keys').
			'</a></p>';
		
		$res .= '<form method="post" action="'.$url.'">'.
			'<fieldset>'.
				'<p>'.
					'<label for="recaptcha_public_key">'.
					__('reCAPTCHA public key:').
				form::field('recaptcha_public_key',50,50,
					$settings->recaptcha_public_key).
					'</label>'.
				'</p>'.
				'<p>'.
					'<label for="recaptcha_private_key">'.
					__('reCAPTCHA private key:').
				form::field('recaptcha_private_key',50,50,
					$settings->recaptcha_private_key).
					'</label>'.
				'</p>'.
				'<p>'.
					'<label for="recaptcha_theme">'.
					__('reCAPTCHA theme:').
				form::combo('recaptcha_theme',$themes,
					$settings->recaptcha_theme).
					'</label>'.
				'</p>'.
			'</fieldset>'.
			'<p>'.$core->formNonce().'</p>'.
			'<p><input type="submit" name="saveconfig" value="'.__('Save configuration').'" /></p>'.
		'</form>';
		
		return($res);
	}
}
?>