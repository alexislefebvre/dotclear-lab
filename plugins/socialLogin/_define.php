<?php
/**
 * This file is part of socialLogin, a plugin for Dotclear.
 * 
 * @author Nicolas Frandeboeuf <nicofrand@gmail.com>
 * @version 2.5
 * @package socialLogin
 * Licensed under the GPL version 3 license.
 * http://www.gnu.org/licenses/gpl-3.0.html
 */
if (!defined('DC_RC_PATH'))
    return;

$this->registerModule(
	/* Name */			"SocialLogin widget",
	/* Description*/		"This widget will allow visitors to identify through social services",
	/* Author */			"Nicolas <nicofrand> Frandeboeuf",
	/* Version */			"1.0",
	/* Permissions */		"usage,contentadmin"
);
?>