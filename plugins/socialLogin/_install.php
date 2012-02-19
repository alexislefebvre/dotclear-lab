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

if (!defined("DC_CONTEXT_ADMIN"))
    return;

// We read the plugin version
$m_version = $core->plugins->moduleInfo("socialLogin", "version");

// We read the plugin version from the version database table
$i_version = $core->getVersion("socialLogin");

/**
 * If the db version is greater or equals the module version, then the plugin
 * is already installed and up to date.
 */
if (version_compare($i_version, $m_version, ">="))
    return;


if (!get_extension_funcs("curl"))
{
    // Load translations of alert messages
    $_lang = $core->auth->getInfo("user_lang");
    $_lang = preg_match("/^[a-z]{2}(-[a-z]{2})?$/", $_lang) ? $_lang : "en";
    l10n::set(dirname(__FILE__) . "/../locales/" . $_lang . "/error");
    throw new Exception(__("SocialLogin: curl lib needed."));
}

// Create the workspace
$core->blog->settings->addNameSpace("socialLogin");
$core->blog->settings->socialLogin->put("janrain_api_key", "", "string", "Janrain API Key");
$core->blog->settings->socialLogin->put("janrain_app_domain", "", "string", "Janrain application domain");
$core->setVersion("socialLogin", $m_version);
?>