<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of optionsForComment, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$core->blog->settings->addNamespace('optionsForComment');

if ($core->blog->settings->optionsForComment->active) {
	# Avant la construction de la page publique
	$core->addBehavior('publicPrepend',array('optionsForComment','publicPrepend'));
	# Avant la previsualisation
	$core->addBehavior('publicBeforeCommentPreview',array('optionsForComment','publicBeforeCommentPreview'));
	# Avant l'enregitrement dans la base
	$core->addBehavior('publicBeforeCommentCreate',array('optionsForComment','publicBeforeCommentCreate'));
	# Dans l'entete de la page publique
	$core->addBehavior('publicHeadContent',array('optionsForComment','publicHeadContent'));
	# Au debut du formulaire publique
	$core->addBehavior('publicCommentFormBeforeContent',array('optionsForComment','publicCommentFormBeforeContent'));
	# Dans l'entete de la page publique
	$core->addBehavior('publicFooterContent',array('optionsForComment','publicFooterContent'));
}
?>