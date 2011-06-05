<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of flvplayerconfig, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Rasibus Master and contributors
# postmaster@rasib.us
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

 if (!defined('DC_CONTEXT_ADMIN')) {return;}
/**
 * @author kévin lepeltier [lipki] (kevin@lepeltier.info)
 * @license http://creativecommons.org/licenses/by-sa/3.0/deed.fr
 */
 
$media = (integer) !empty($_GET['media']);

if( $media )
	require dirname(__FILE__).'/media.php';
else  require dirname(__FILE__).'/form.php';