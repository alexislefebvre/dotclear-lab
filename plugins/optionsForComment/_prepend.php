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

if (!defined('DC_RC_PATH')){return;}global $_autoload, $core;

# Classe principal, tous les addons doivent utiliser les behaviors de cette classe
$__autoload['optionsForComment'] = dirname(__FILE__).'/inc/class.optionsforcomment.php';

# Button Twitter login
// see plugin dcLibTwitter

# Email optionnel
$__autoload['ofcEmailOptionnel'] = dirname(__FILE__).'/inc/lib.ofc.emailoptionnel.php';
$core->addBehavior('optionsForCommentAdminFormMode',array('ofcEmailOptionnel','optionsForCommentAdminFormMode'));
$core->addBehavior('optionsForCommentPublicPrepend',array('ofcEmailOptionnel','optionsForCommentPublicPrepend'));
$core->addBehavior('optionsForCommentPublicCreate',array('ofcEmailOptionnel','optionsForCommentPublicCreate'));
$core->addBehavior('optionsForCommentPublicHead',array('ofcEmailOptionnel','optionsForCommentPublicHead'));

# Anonymous comment
$__autoload['ofcAnonymousComment'] = dirname(__FILE__).'/inc/lib.ofc.anonymouscomment.php';
$core->addBehavior('optionsForCommentAdminPrepend',array('ofcAnonymousComment','optionsForCommentAdminPrepend'));
$core->addBehavior('optionsForCommentAdminFormMode',array('ofcAnonymousComment','optionsForCommentAdminFormMode'));
$core->addBehavior('optionsForCommentPublicPrepend',array('ofcAnonymousComment','optionsForCommentPublicPrepend'));
$core->addBehavior('optionsForCommentPublicHead',array('ofcAnonymousComment','optionsForCommentPublicHead'));
$core->addBehavior('optionsForCommentPublicCreate',array('ofcAnonymousComment','optionsForCommentPublicCreate'));
$core->addBehavior('optionsForCommentPublicForm',array('ofcAnonymousComment','optionsForCommentPublicForm'));
?>