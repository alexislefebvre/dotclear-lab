<?php 
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of latexrender, a plugin for Dotclear.
# 
# Copyright (c) 2009 Jean-Christophe Dubacq
# jcdubacq1@free.fr
# 
# Licensed under the LGPL version 2.1 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/lgpl-2.1.html
# -- END LICENSE BLOCK ------------------------------------

$_menu['Plugins']->addItem('LaTeXrender','plugin.php?p=latexrender',
                           'index.php?pf=latexrender/icon.png', 
                           preg_match('/plugin.php\?p=latexrender/',
                                      $_SERVER['REQUEST_URI']),
                           $core->auth->isSuperAdmin());
?>