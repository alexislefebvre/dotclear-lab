<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of hum, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) {

	return null;
}

$dc_min = '2.6';
$mod_id = 'hum';

# Install or update
try {
	# Check module version
	if (version_compare(
		$core->getVersion($mod_id),
		$core->plugins->moduleInfo($mod_id, 'version'),
		'>='
	)) {

		return null;
	}

	# Check Dotclear version
	if (!method_exists('dcUtils', 'versionsCompare') 
	 || dcUtils::versionsCompare(DC_VERSION, $dc_min, '<', false)) {
		throw new Exception(sprintf(
			'%s requires Dotclear %s', $mod_id, $dc_min
		));
	}

	# Edit comment table structure
	$t = new dbStruct($core->con, $core->prefix);
	$t->comment->comment_selected('smallint', 0, false, 0);	
	$ti = new dbStruct($core->con, $core->prefix);
	$changes = $ti->synchronize($t);

	$css_extra = "#comments dt a.read-it { font-size: 0.8em; padding: 5px; font-style: italic; } ";

	# Set module settings
	$core->blog->settings->addNamespace($mod_id);
	$s = $core->blog->settings->{$mod_id};
	$s->put('active', false, 'boolean', 'Enabled hum plugin', false, true);
	$s->put('comment_selected', false, 'boolean', 'Select new comment by default', false, true);
	$s->put('jquery_hide', true, 'boolean', 'Hide comments with jQuery fonction', false, true);
	$s->put('title_tag', 'dt', 'string', 'HTML tag of comment title block', false, true);
	$s->put('content_tag', 'dd', 'string', 'HTML tag of comment content block', false, true);
	$s->put('css_extra', $css_extra, 'string', 'Additionnal style sheet', false, true);

	# Set module version
	$core->setVersion(
		$mod_id,
		$core->plugins->moduleInfo($mod_id, 'version')
	);

	return true;
}
catch (Exception $e) {
	$core->error->add($e->getMessage());

	return false;
}
