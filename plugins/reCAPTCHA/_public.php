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

if (!defined('DC_RC_PATH')) {return;}

# if the filter has no settings
$public_key = $core->blog->settings->recaptcha_public_key;
if (empty($public_key)) {return;}

$private_key = $core->blog->settings->recaptcha_private_key;
if (empty($private_key)) {return;}

require_once(dirname(__FILE__).'/recaptcha-php-1.10/recaptchalib.php');

// what if the filter is not active ?
$core->addBehavior('publicBeforeDocument',
	array('reCAPTCHAPublic','publicBeforeDocument'));

$core->addBehavior('publicCommentFormAfterContent',
	array('reCAPTCHAPublic','publicCommentFormAfterContent'));

$core->addBehavior('publicHeadContent',
	array('reCAPTCHAPublic','publicHeadContent'));

class reCAPTCHAPublic
{
	public static function publicBeforeDocument()
	{
		# start session
		$session_id = session_id();
		if (empty($session_id)) {session_start();}
		
		# if the commentator is not authenticated
		if (!isset($_SESSION['recaptcha_ok']))
		{
			$check = self::checkAnswer(
				$GLOBALS['core']->blog->settings->recaptcha_private_key,
				http::realIP());
			
			if ($check === true) {
				# authenticate the commentator
				$_SESSION['recaptcha_ok'] = true;
			}
			else
			{
				# display the error code
				$GLOBALS['_ctx']->form_error = $check;
				$_SESSION['recaptcha_ok'] = false;
				unset($_SESSION['recaptcha_ok']);
			}
		}
	}
	
	/**
	display reCAPTCHA form if the visitor is not authenticated
	*/
	public static function publicCommentFormAfterContent()
	{
		if (!isset($_SESSION['recaptcha_ok']))
		{
			echo(recaptcha_get_html(
				$GLOBALS['core']->blog->settings->recaptcha_public_key,null));
		}
	}
	
	/**
	return true if the answer is ok, null if the answer is missing
	or the error message
	*/
	public static function checkAnswer($private_key,$ip)
	{
		if (!isset($_POST["recaptcha_challenge_field"])
			OR !isset($_POST["recaptcha_response_field"]))
		{
			return(null);
		}
		
		$resp = recaptcha_check_answer($private_key,$ip,
			$_POST["recaptcha_challenge_field"],
			$_POST["recaptcha_response_field"]);
		
		if ($resp->is_valid)
		{
			return((boolean) true);
		}
		else
		{
			return($resp->error);
		}
	}
	
	public static function publicHeadContent(&$core)
	{
		global $_lang;
		
		# use the blog language and the selected theme
		# see http://code18.blogspot.com/2009/05/service-recaptcha-en-francais.html
		echo ( 
			'<script type= "text/javascript">'.
			'var RecaptchaOptions = {'.
			'	 lang : \''.$_lang.'\','.
			'	 theme : \''.$core->blog->settings->recaptcha_theme.'\''.
			'};'.
			'</script>'."\n");
	}
}

?>