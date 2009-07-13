<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of disclaimer, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) return;

# Is active
if (!$core->blog->settings->disclaimer_active) return;

# Localized l10n
__('Disclaimer');
__('Accept terms of uses');
__('I agree');
__('I disagree');

# Behaviors
$core->addBehavior('publicBeforeDocument',array('urlDisclaimer','publicBeforeDocument'));
$core->addBehavior('publicHeadContent',array('urlDisclaimer','publicHeadContent'));

# Templates
$core->tpl->addValue('DisclaimerTitle',array('tplDisclaimer','DisclaimerTitle'));
$core->tpl->addValue('DisclaimerText',array('tplDisclaimer','DisclaimerText'));
$core->tpl->addValue('DisclaimerFormURL',array('tplDisclaimer','DisclaimerFormURL'));

class urlDisclaimer extends dcUrlHandlers
{
	# Remove public callbacks (and serve disclaimer css)
	public static function overwriteCallbacks($args)
	{
		if ($args == 'disclaimer.css') {
			self::serveDocument('disclaimer.css','text/css',false);
			exit;
		}
		return;
	}

	# Add CSS for disclaimer
	public static function publicHeadContent($args)
	{
		echo "<style type=\"text/css\">\n@import url(".
			$GLOBALS['core']->blog->url."disclaimer.css);\n</style>\n";
	}

	# Check disclaimer
	public static function publicBeforeDocument($args)
	{
		global $core,$_ctx;

		# Set default-templates path for disclaimer files
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');

		// New URL handler
		$urlHandler = new urlHandler();
		$urlHandler->mode = $core->url->mode;
		$urlHandler->registerDefault(array('urlDisclaimer','overwriteCallbacks'));

		// Create session if not exists
		if (!isset($session)) {
			$session = new sessionDB(
				   $core->con,
				   $core->prefix.'session',
				   'dc_disclaimer_sess_'.$core->blog->id,
				   '/'
			);
			$session->start();
		}

		// Remove all URLs representations
		foreach ($core->url->getTypes() as $k=>$v) {
			$urlHandler->register($k,$v['url'],$v['representation'],array('urlDisclaimer','overwriteCallbacks'));
		}

		// Get type
		$urlHandler->getDocument();
		$type = $urlHandler->type;
		unset($urlHandler);

		// Test cookie
		$cookie_name="dc_disclaimer_cookie_".$core->blog->id;
		$cookie_value = 
			empty($_COOKIE[$cookie_name]) || !$core->blog->settings->disclaimer_remember ?
				false : ($_COOKIE[$cookie_name]) == 1;

		// User say "disagree" so go away
		if (isset($_POST['disclaimerdisagree'])) {

			$session->destroy();
			if ($core->blog->settings->disclaimer_remember)
				setcookie($cookie_name,0,time()-86400,'/');

			$redir = $core->blog->settings->disclaimer_redir;
			if (!$redir) 
				$redir = 'http://www.dotclear.org';

			http::redirect($redir);
			exit;
		}
		// Check if user say yes before
		elseif (!isset($_SESSION['sess_blog_disclaimer']) 
		 || $_SESSION['sess_blog_disclaimer'] != 1) {

			if ($core->blog->settings->disclaimer_remember && $cookie_value != false) {

				$_SESSION['sess_blog_disclaimer'] = 1;
				return;
			}

			if (!empty($_POST['disclaimeragree'])) {

				$_SESSION['sess_blog_disclaimer'] = 1;

				if ($core->blog->settings->disclaimer_remember)
					setcookie($cookie_name,1,time()+31536000,'/');

				return;
			}

			$session->destroy();
			self::serveDocument('disclaimer.html','text/html',false);
			exit;
		}
		return;
	}
}

class tplDisclaimer
{
	# Public title of disclaimer page and form
	public static function DisclaimerTitle($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return 
		'<?php echo '.sprintf($f,'$core->blog->settings->disclaimer_title').'; ?>';
	}

	# Public disclaimer text
	public static function DisclaimerText($attr)
	{
		return '<?php echo $core->blog->settings->disclaimer_text; ?>';
	}

	# Public URL of disclaimer form action
	public static function DisclaimerFormURL($attr)
	{
		return '<?php $core->blog->url; ?>';
	}
}
?>