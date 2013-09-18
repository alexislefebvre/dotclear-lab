<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of acronyms, a plugin for DotClear2.
#
# Copyright (c) 2008 Vincent Garnier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$GLOBALS['__autoload']['dcAcronyms'] = dirname(__FILE__).'/class.dc.acronyms.php';

$GLOBALS['core']->url->register('acronyms','acronyms','^acronyms$',array('acronymsURL','acronyms'));
?>
