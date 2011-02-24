<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Empreinte, a plugin for Dotclear.
# 
# Copyright (c) 2007,2008,2011 Alex Pirine <alex pirine.fr>
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$GLOBALS['__autoload']['rsExtCommentEmpreinte'] = dirname(__FILE__).'/rs.extensions.php';
$GLOBALS['__autoload']['empreinte'] = dirname(__FILE__).'/class.empreinte.php';
$GLOBALS['__autoload']['publicEmpreinte'] = dirname(__FILE__).'/class.public.empreinte.php';
$GLOBALS['__autoload']['tplEmpreinte'] = dirname(__FILE__).'/class.tpl.empreinte.php';
?>