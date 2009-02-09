<?php 
  // ***** BEGIN LICENSE BLOCK *****
  // This file is (c) Jean-Christophe Dubacq.
  // Licensed under CC-BY licence.
  //
  // ***** END LICENSE BLOCK *****

$_menu['Plugins']->addItem('LaTeXrender','plugin.php?p=latexrender','index.php?pf=latexrender/icon.png', preg_match('/plugin.php\?p=latexrender/',$_SERVER['REQUEST_URI']), $core->auth->isSuperAdmin());
?>