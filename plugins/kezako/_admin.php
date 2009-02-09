<?php
  // ***** BEGIN LICENSE BLOCK *****
  // This file is (c) Jean-Christophe Dubacq.
  // Licensed under CC-BY licence.
  //
  // ***** END LICENSE BLOCK *****

$_menu['Blog']->addItem(__('Kezako'),
                        'plugin.php?p=kezako',
                        'index.php?pf=kezako/icon.png',
                        preg_match('/plugin.php\?p=kezako/',
                                   $_SERVER['REQUEST_URI']),
                        $core->auth->check('editor',$core->blog->id));
$core->auth->setPermissionType('editor',
                               __('manage translations and descriptions'));

?>