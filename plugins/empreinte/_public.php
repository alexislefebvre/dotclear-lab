<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Empreinte, a plugin for Dotclear.
# 
# Copyright (c) 2007,2008,2011 Alex Pirine <alex pirine.fr>
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('publicBeforeCommentCreate',array('publicEmpreinte','publicBeforeCommentCreate'));
$core->addBehavior('coreBlogGetComments',array('publicEmpreinte','coreBlogGetComments'));

$core->tpl->addValue('PluginFileURL',array('tplEmpreinte','PluginFileURL'));
$core->tpl->addValue('CommentCheckNoEmpreinte',array('tplEmpreinte','CommentCheckNoEmpreinte'));
$core->tpl->addBlock('CommentIfUserAgent',array('tplEmpreinte','CommentIfUserAgent'));
$core->tpl->addValue('CommentSystem',array('tplEmpreinte','CommentSystem'));
$core->tpl->addValue('CommentBrowser',array('tplEmpreinte','CommentBrowser'));
$core->tpl->addValue('CommentSystemImg',array('tplEmpreinte','CommentSystemImg'));
$core->tpl->addValue('CommentBrowserImg',array('tplEmpreinte','CommentBrowserImg'));

$core->blog->settings->addNamespace('empreinte');
?>