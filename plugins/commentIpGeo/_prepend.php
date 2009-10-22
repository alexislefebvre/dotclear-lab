<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of commentIpGeo, a plugin for Dotclear.
#
# Copyright (c) 2009 Frederic PLE
# dotclear@frederic.ple.name
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$GLOBALS['__autoload']['commentIpGeo'] = dirname(__FILE__).'/class.commentIpGeo.php';
$GLOBALS['__autoload']['publicCommentIpGeo'] = dirname(__FILE__).'/class.public.commentIpGeo.php';
$GLOBALS['__autoload']['tplCommentIpGeo'] = dirname(__FILE__).'/class.tpl.commentIpGeo.php';
$GLOBALS['__autoload']['rsExtCommentIpGeo'] = dirname(__FILE__).'/rs.extensions.php';
?>