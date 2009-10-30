<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Puzzle, a plugin for Dotclear.
# 
# Copyright (c) 2009 kévin lepeltier
# kevin@lepeltier.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$default_tab = 'active';

if (isset($_REQUEST['tab']))
	$default_tab = $_REQUEST['tab'];

require_once (dirname(__FILE__).'/inc/class.puzzle.php');
new puzzle( $core, $default_tab, $p_url );