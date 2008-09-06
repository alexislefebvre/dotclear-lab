<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2006-2008 Pep and contributors. All rights
# reserved. Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$feed_tpl = <<<EOD
<?php
\$url = '%sfeed';

if (!empty(\$_GET['lang'])) {
	\$url .= '/'.\$_GET['lang'];
}
if (!empty(\$_GET['cat'])) {
	\$url .= '/category/'.\$_GET['cat'];
}
\$url .= '/%s';
if (!empty(\$_GET['type']) && \$_GET['type'] == 'co') {
	\$url .= '/comments';
}

if (preg_match('/cgi/',PHP_SAPI)) {
	header('Status: 302 Moved Permanently');
} else {
	header('Moved Permanently',true,302);
}
header('Location: '.\$url); exit;
?>
EOD;
?>
<html>
<head>
	<title><?php echo __('Dotclear 1 URLs'); ?></title>
</head>

<body>
<?php
echo '<h2>'.__('Dotclear 1 URLs').'</h2>';

echo
'<p>'.__('I order to avoid broken feeds, you should replace your old '.
'Atom and RSS feeds by redirections to new ones. The following files replace '.
'your Dotclear 1 atom.php and rss.php files.').'</p>';

echo
'<h3>atom.php</h3>'.
'<p>'.form::textarea('atom',60,20,html::escapeHTML(sprintf($feed_tpl,$core->blog->url,'atom')),'maximal').'</p>'.
'<h3>rss2.php</h3>'.
'<p>'.form::textarea('rss2',60,20,html::escapeHTML(sprintf($feed_tpl,$core->blog->url,'rss2')),'maximal').'</p>';

?>
</body>
</html>