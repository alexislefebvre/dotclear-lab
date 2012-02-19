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

class SocialLoginWidget
{
    /**
     * Initializes the widget.
     * @param object $widgetContainer the widget container.
     */
    public static function init($widgetContainer)
    {
	//id, name, callback to display the widget on the public area
	$widgetContainer->create("socialLogin", "SocialLogin", array("SocialLoginPublic", "getWidgetContent"));
	$widgetContainer->socialLogin->setting("connexion_text", __("Connexion text"), "Sign-in", "text");
	$widgetContainer->socialLogin->setting("deconnexion_text", __("Deconnexion text"), "Sign off", "text");
    }
}
?>
