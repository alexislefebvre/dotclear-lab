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

if (!defined('DC_RC_PATH'))
    return;

require_once(dirname(__FILE__) . "/inc/class.SocialLoginWidget.php");
require_once(dirname(__FILE__) . "/inc/class.SocialLoginPublic.php");
require_once(dirname(__FILE__) . "/inc/class.SocialLoginConnexion.php");

$core->url->register("socialLoginOff", "socialLogin", "^socialLogin/deco$", array("SocialLoginConnexion", "deconnect"));
$core->url->register("socialLoginOn", "socialLogin", "^socialLogin-[0-9a-z]*$", array("SocialLoginConnexion", "connect"));

$core->addBehavior("initWidgets", array("SocialLoginWidget", "init"));
$core->addBehavior("publicHeadContent",array("SocialLoginPublic", "getScript"));
?>