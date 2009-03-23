<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Freshy2, a theme for Dotclear.
# Original WP Theme from Julien de Luca
# (http://www.jide.fr/francais/)
#
# Copyright (c) 2008-2009
# Bruno Hondelatte dsls@morefnu.org
# Pierre Van Glabeke contact@brol.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
header('Content-type: text/css');
$path = substr("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], 0, -10);
?>

body {
	behavior:url(<?php print $path; ?>csshover2.htc);
}

* html #page, * html #header {
	padding:0 10px 0 10px;
	zoom:1;
	filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php print $path; ?>images/ie_shadow.png', sizingMethod='scale');
}

* html #header {
	padding-top:10px;
	filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php print $path; ?>images/ie_header_shadow.png', sizingMethod='scale');
}

* html #footer {
	padding:0 10px 10px 10px;
	zoom:1;
	filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php print $path; ?>images/ie_footer_shadow.png', sizingMethod='scale');
}

* html .comment.comment_author {
	background-image:none;
	zoom:1;
	filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php print $path; ?>images/transparency/white-90.png', sizingMethod='scale');
}

* html code {
	background-image:none;
	zoom:1;
	filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php print $path; ?>images/code_bg.png', sizingMethod='crop');
}

* html #menu {
	filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php print $path; ?>images/transparency/black-60.png', sizingMethod='scale');
}

* html #menu .menu_container {
	filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php print $path; ?>images/menu/first_menu.png', sizingMethod='crop');
}

* html .menu_end {
	filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php print $path; ?>images/menu/menu_end.png', sizingMethod='crop');
}

* html #menu ul li.top_parent a, * html #menu ul li:hover a, * html #menu ul li a:hover, * html #menu ul li.current_page_parent a, * html #menu ul li.current_page_item a {
	background-image:none;
	filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php print $path; ?>images/menu/reflect.png', sizingMethod='scale');
}

* html .download {
	background-image:none;
	height:35px;
	filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php print $path; ?>images/download.png', sizingMethod='crop');
}
