<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of feedburner, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$m_version = $core->plugins->moduleInfo('feedburner','version');
$i_version = $core->getVersion('feedburner');
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# Création des settings
$feeds = array(
	'rss2' => '',
	'rss2_comments' => '',
	'atom' => '',
	'atom_comments' => ''
);
$core->blog->settings->addNamespace('feedburner');
$core->blog->settings->feedburner->put(
	'feedburner_primary_xml',
	'https://feedburner.google.com/api/awareness/1.0/',
	'string','Primary feedburner XML feed location',true,true
);
$core->blog->settings->feedburner->put(
	'feedburner_secondary_xml',
	'http://zenstyle.free.fr/dc2/',
	'string','Secondary feedburner XML feed location',true,true
);
$core->blog->settings->feedburner->put(
	'feedburner_base_url',
	'http://feeds2.feedburner.com/',
	'string','Base url for feedburner feeds',true,true
);
$core->blog->settings->feedburner->put(
	'feedburner_feeds',
	serialize($feeds),
	'string','Feeds list',false,true
);
$core->blog->settings->feedburner->put(
	'feedburner_proxy',
	'',
	'string','Proxy host to get feedburner API XML',false,true
);

$core->setVersion('feedburner',$m_version);

return true;

?>