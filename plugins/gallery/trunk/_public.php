<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Gallery plugin.
#
# Copyright (c) 2004-2008 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

require dirname(__FILE__).'/_widgets.php';

// Later on, some rest features :)
if (!empty($core->pubrest))
	require dirname(__FILE__).'/_pubrest.php';
require_once dirname(__FILE__).'/_public_tpl.php';
require_once dirname(__FILE__).'/_public_behaviors.php';
require_once dirname(__FILE__).'/_public_widgets.php';
require_once dirname(__FILE__).'/_public_urlhandlers.php';




?>