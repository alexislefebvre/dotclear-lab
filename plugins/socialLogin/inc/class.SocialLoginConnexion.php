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

class SocialLoginConnexion extends dcUrlHandlers
{
    /**
     * Makes the connexion.
     * @global dcCore $core
     */
    public static function connect()
    {
	global $core;

	if (!session_id())
	    session_start();

	$previousUrl = (isset($_SESSION["socialLogin_previous_url"])) ? $_SESSION["socialLogin_previous_url"] : $core->blog->url;

	$apiKey = $core->blog->settings->socialLogin->janrain_api_key;

	// Call the auth_info API
	$postData = array("token" => $_POST["token"], "apiKey" => $apiKey, "format" => "json");

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_URL, "https://rpxnow.com/api/v2/auth_info");
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	$rawJson = curl_exec($curl);
	curl_close($curl);

	// Decode the response
	$authInfo = json_decode($rawJson, true);

	if (($authInfo["stat"] == "ok") && isset($authInfo["profile"]))
	{
	    if (isset($_SESSION["socialLogin_error"]))
		unset($_SESSION["socialLogin_error"]);

	    $profile = $authInfo["profile"];

	    if (isset($profile["identifier"]))
		$_SESSION["socialLogin_identifier"] = $profile["identifier"];

	    if (isset($profile["displayName"]))
		$_SESSION["socialLogin_pseudo"] = $profile["displayName"];

	    if (isset($profile["email"]))
		$_SESSION["socialLogin_email"] = $profile["email"];

	    if (isset($profile["url"]))
		$_SESSION["socialLogin_website"] = $profile["url"];
	}
	else
	    $_SESSION["socialLogin_error"] = "Profile does not exist";

	http::redirect($previousUrl);
    }

    /**
     * Deconnects the visitor.
     * @global dcCore $core
     */
    public static function deconnect()
    {
	global $core;

	if (!session_id())
	    session_start();

	unset($_SESSION["socialLogin_identifier"]);
	unset($_SESSION["socialLogin_pseudo"]);
	unset($_SESSION["socialLogin_email"]);
	unset($_SESSION["socialLogin_website"]);

	$previousUrl = (isset($_SESSION["socialLogin_previous_url"])) ? $_SESSION["socialLogin_previous_url"] : $core->blog->url;
	http::redirect($previousUrl);
    }
}
?>
