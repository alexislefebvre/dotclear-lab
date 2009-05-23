<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of commentIpGeo, a plugin for Dotclear.
#
# Copyright (c) 2007-2009 Frederic PLE
# dotclear@frederic.ple.name
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if ($core->blog->settings->commentIpGeo_active) {
	$core->tpl->addValue('commentIpGeo',array('tplCommentIpGeo','country_code'));
	$core->tpl->addValue('commentIpGeoFlag',array('tplCommentIpGeo','country_flag'));
	$core->tpl->addValue('commentDebug',array('tplCommentIpGeo','debug'));
	$core->addBehavior('publicHeadContent',array('tplCommentIpGeo','publicHeadContent'));
	$core->addBehavior('coreBlogGetComments',array('publicCommentIpGeo','coreBlogGetComments'));
}

?>