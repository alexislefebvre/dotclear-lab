<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of disclaimer, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {

	return null;
}

$core->blog->settings->addNamespace('disclaimer');

# Is active
if (!$core->blog->settings->disclaimer->disclaimer_active) {

	return null;
}

# Localized l10n
__('Disclaimer');
__('Accept terms of uses');
__('I agree');
__('I disagree');

# Behaviors
$core->addBehavior(
	'publicBeforeDocument',
	array('urlDisclaimer', 'publicBeforeDocument')
);
$core->addBehavior(
	'publicHeadContent',
	array('urlDisclaimer', 'publicHeadContent')
);

# Templates
$core->tpl->addValue(
	'DisclaimerTitle',
	array('tplDisclaimer', 'DisclaimerTitle')
);
$core->tpl->addValue(
	'DisclaimerText',
	array('tplDisclaimer', 'DisclaimerText')
);
$core->tpl->addValue(
	'DisclaimerFormURL',
	array('tplDisclaimer', 'DisclaimerFormURL')
);

/**
 * @ingroup DC_PLUGIN_DISCLAIMER
 * @brief Public disclaimer - URL handler.
 * @since 2.6
 */
class urlDisclaimer extends dcUrlHandlers
{
	public static $default_bots_agents = array(
		'bot','Scooter','Slurp','Voila','WiseNut','Fast','Index','Teoma',
		'Mirago','search','find','loader','archive','Spider','Crawler'
	);

 /**
	 * Remove public callbacks (and serve disclaimer css)
	 *
	 * @param  array $args Arguments
	 */
	public static function overwriteCallbacks($args)
	{
		if ($args == 'disclaimer.css') {
			self::serveDocument('disclaimer.css', 'text/css', false);
			exit;
		}

		return null;
	}

	/**
	 * Add CSS for disclaimer
	 *
	 * @param  dcCore $core dcCore instance
	 * @return [type]       [description]
	 */
	public static function publicHeadContent(dcCore $core)
	{
    $url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		echo
		"<style type=\"text/css\">\n@import url(".
			$url.
		"/css/disclaimer.css);\n</style>\n";
	}

	/**
	 * Check disclaimer
	 * 
	 * @param  array $args Arguments
	 */
	public static function publicBeforeDocument($args)
	{
		global $core, $_ctx;

		# Test user-agent to see if it is a bot
		if (!$core->blog->settings->disclaimer->disclaimer_bots_unactive) {
			$bots_agents = $core->blog->settings->disclaimer->diclaimer_bots_agents;
			$bots_agents = !$bots_agents ? 
				self::$default_bots_agents : explode(';', $bots_agents);

			$is_bot = false;
			foreach($bots_agents as $bot) {

				if(stristr($_SERVER['HTTP_USER_AGENT'], $bot)) {
					$is_bot = true;
				}
			}

			if ($is_bot) {

				return null;
			}
		}

		# Set default-templates path for disclaimer files
		$tplset = $core->themes->moduleInfo($core->blog->settings->system->theme,'tplset');
        if (!empty($tplset) && is_dir(dirname(__FILE__).'/default-templates/'.$tplset)) {
            $core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates/'.$tplset);
        } else {
            $core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates/'.DC_DEFAULT_TPLSET);
        }

		# New URL handler
		$urlHandler = new urlHandler();
		$urlHandler->mode = $core->url->mode;
		$urlHandler->registerDefault(array(
			'urlDisclaimer',
			'overwriteCallbacks'
		));

		# Create session if not exists
		if (!isset($session)) {
			$session = new sessionDB(
				   $core->con,
				   $core->prefix.'session',
				   'dc_disclaimer_sess_'.$core->blog->id,
				   '/'
			);
			$session->start();
		}

		# Remove all URLs representations
		foreach ($core->url->getTypes() as $k => $v) {
			$urlHandler->register(
				$k,
				$v['url'],
				$v['representation'],
				array('urlDisclaimer', 'overwriteCallbacks')
			);
		}

		# Get type
		$urlHandler->getDocument();
		$type = $urlHandler->type;
		unset($urlHandler);

		# Test cookie
		$cookie_name="dc_disclaimer_cookie_".$core->blog->id;
		$cookie_value = 
			empty($_COOKIE[$cookie_name]) 
			|| !$core->blog->settings->disclaimer->disclaimer_remember ?
				false : ($_COOKIE[$cookie_name]) == 1;

		# User say "disagree" so go away
		if (isset($_POST['disclaimerdisagree'])) {
			$session->destroy();
			if ($core->blog->settings->disclaimer->disclaimer_remember) {
				setcookie($cookie_name, 0, time()-86400, '/');
			}
			$redir = $core->blog->settings->disclaimer->disclaimer_redir;
			if (!$redir) {
				$redir = 'http://www.dotclear.org';
			}
			http::redirect($redir);
			exit;
		}

		# Check if user say yes before
		elseif (!isset($_SESSION['sess_blog_disclaimer']) 
		 || $_SESSION['sess_blog_disclaimer'] != 1
		) {
			if ($core->blog->settings->disclaimer->disclaimer_remember 
			 && $cookie_value != false
			) {
				$_SESSION['sess_blog_disclaimer'] = 1;

				return null;
			}
			if (!empty($_POST['disclaimeragree'])) {
				$_SESSION['sess_blog_disclaimer'] = 1;

				if ($core->blog->settings->disclaimer->disclaimer_remember) {
					setcookie($cookie_name, 1, time() + 31536000, '/');
				}

				return null;
			}

			$session->destroy();
			self::serveDocument('disclaimer.html', 'text/html', false);
			exit;
		}

		return null;
	}
}

/**
 * @ingroup DC_PLUGIN_DISCLAIMER
 * @brief Public disclaimer - Template.
 * @since 2.6
 */
class tplDisclaimer
{
	/**
	 * Public title of disclaimer page and form
	 * 
	 * @param array $attr Template value attribute
	 */
	public static function DisclaimerTitle($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return 
		'<?php echo '.sprintf(
			$f,
			'$core->blog->settings->disclaimer->disclaimer_title'
		).'; ?>';
	}

	/**
	 * Public disclaimer text
	 * 
	 * @param array $attr Template value attribute
	 */
	public static function DisclaimerText($attr)
	{
		return 
		'<?php echo $core->blog->settings->disclaimer->disclaimer_text; ?>';
	}

	/**
	 * Public URL of disclaimer form action
	 * 
	 * @param array $attr Template value attribute
	 */
	public static function DisclaimerFormURL($attr)
	{
		return '<?php $core->blog->url; ?>';
	}
}
