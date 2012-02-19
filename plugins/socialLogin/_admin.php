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
if (!defined('DC_CONTEXT_ADMIN'))
    return;

require_once(dirname(__FILE__) . "/inc/class.SocialLoginWidget.php");
$core->addBehavior("initWidgets", array("SocialLoginWidget", "init"));
?>