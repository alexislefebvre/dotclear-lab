<?php
/**
 * This file is part of socialLogin, a plugin for Dotclear.
 * 
 * @author Nicolas Frandeboeuf <nicofrand@gmail.com>
 * @version 1.0
 * @package socialLogin
 * Licensed under the GPL version 3 license.
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

class SocialLoginPublic
{
    /**
     * Inserts the javascript provided by janrain in the head.
     * @param dcCore $core
     */
    public static function getScript($core)
    {
	if (!session_id())
	    session_start();

	$tokenUrl = $core->blog->url . $core->url->getBase("socialLoginOn") . "-" . uniqid();

	$applicationDomain = $core->blog->settings->socialLogin->janrain_app_domain;
	if (!$applicationDomain)
	    return;

	$applicationName = str_replace("https://", "", $applicationDomain);
	$applicationName = str_replace(".rpxnow.com/", "", $applicationName);

	$securedUrl = "https://rpxnow.com/js/lib/" . $applicationName . "/engage.js";
	$unsecuredUrl = "http://widget-cdn.rpxnow.com/js/lib/" . $applicationName . "/engage.js";

	$content = "
	    <script type=\"text/javascript\">
		$(document).ready(function()
		{
		    // Hide comment fields
	";

	if (isset($_SESSION["socialLogin_pseudo"]))
	    $content .= "$('#c_name').val('" . $_SESSION["socialLogin_pseudo"] . "').parent().hide();";

	if (isset($_SESSION["socialLogin_email"]))
	    $content .= "$('#c_mail').val('" . $_SESSION["socialLogin_email"] . "').parent().hide();";

	if (isset($_SESSION["socialLogin_website"]))
	    $content .= "$('#c_site').val('" . $_SESSION["socialLogin_website"] . "').parent().hide();";

	$content .= "
		});

		(function()
		{
		    if (typeof window.janrain !== 'object') window.janrain = {};
		    window.janrain.settings = {};
		    janrain.settings.tokenUrl = '" . $tokenUrl . "';

		    function isReady() { janrain.ready = true; };
		    if (document.addEventListener){
		    document.addEventListener('DOMContentLoaded', isReady, false);
		    } else {
		    window.attachEvent('onload', isReady);
		    }

		    var e = document.createElement('script');
		    e.type = 'text/javascript';
		    e.id = 'janrainAuthWidget';

		    if (document.location.protocol === 'https:') {
		    e.src = '" . $securedUrl . "';
		    } else {
		    e.src = '" . $unsecuredUrl . "';
		    }

		    var s = document.getElementsByTagName('script')[0];
		    s.parentNode.insertBefore(e, s);
		})();
	    </script>";
	echo($content);
    }

    /**
     * Gets the widget content to display on the public area.
     * @global dcCore $core
     * @param object $widget the widget.
     * @return string the widget content
     */
    public static function getWidgetContent($widget)
    {
	global $core;

	$scheme = (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on")) ? "https://" : "http://";
	$_SESSION["socialLogin_previous_url"] = $scheme . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

	if (isset($_SESSION["socialLogin_identifier"]))
	    return "<a href=\"" . $core->blog->url . $core->url->getBase("socialLoginOff") . "/deco\" id=\"socialLogin_deconnect\" href=\"socialLogin_button\">" . $widget->deconnexion_text . "</a>";

	if (!$core->blog->settings->socialLogin->janrain_app_domain)
	    return;

	$widgetContent = "";

	if (isset($_SESSION["socialLogin_error"]))
	    $widgetContent .= "<p class=\"socialLogin_error\">" . $_SESSION["socialLogin_error"] . "</p>";

	$widgetContent .= "<a class=\"janrainEngage socialLogin_button\" href=\"#\">" . $widget->connexion_text . "</a>";

	return $widgetContent;
    }
}
?>
