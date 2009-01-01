<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of plugin feedburner for Dotclear 2.
# Copyright (c) 2008 Thomas Bouron.
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$m_version = $core->plugins->moduleInfo('feedburner','version');
$i_version = $core->getVersion('feedburner');
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# Création des settings
$feeds = array(
	'rss' => '',
	'rssco' => '',
	'atom' => '',
	'atomco' => ''
);
$core->blog->settings->setNamespace('feedburner');
$core->blog->settings->put(
	'feedburner_primary_xml',
	'http://api.feedburner.com/awareness/1.0/',
	'string','Primary feedburner XML feed location',true,true
);
$core->blog->settings->put(
	'feedburner_secondary_xml',
	'http://zenstyle.free.fr/dc2/',
	'string','Secondary feedburner XML feed location',true,true
);
$core->blog->settings->put(
	'feedburner_feeds',
	serialize($feeds),
	'string','Feeds list',false,true
);
$core->blog->settings->put(
	'feedburner_proxy',
	'',
	'string','Proxy host to get feedburner API XML',true,true
);

$core->setVersion('feedburner',$m_version);

return true;

?>
