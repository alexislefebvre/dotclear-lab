<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 "Upload Updater" plugin.
#
# Copyright (c) 2003-2010 DC Team
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------


dcPage::checkSuper();

if (is_readable(DC_DIGESTS)) {
	require(dirname(__FILE__).'/update.php');
} else {
	echo '<html><head>'.
		'<title>'.__('Dotclear update by upload').'</title>'.
		'</head>'.
		'<body>'.
		'<div class="error"><strong>'.__('Errors:').'</strong>'.
		__('Digests file is not readable, or does not exist. Update cannot be performed.').
		'</div>'.
		'</body>'.
		'</html>';
} 
?>