<?php if (!defined('DC_CONTEXT_ADMIN')) {return;}
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of flvplayerconfig, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 lipki and contributors
# kevin@lepeltier.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
 
$media = (integer) !empty($_GET['media']);

if( $media )
	require dirname(__FILE__).'/media.php';
else  require dirname(__FILE__).'/form.php';