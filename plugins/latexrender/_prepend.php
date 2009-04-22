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

$core->addBehavior('initStacker',
                   array('latexrenderStacker','initStacker'));

class latexrenderStacker
{
    public static function initStacker(&$core)
    {
        $core->stacker->addFilter('LaTeXifier',
                                  'tplLatexRender', // Class
                                  'LatexText',      // Function
                                  'textonly',       // Context
                                  90,               // Priority
                                  'latexrender',    // Origin
                                  __('Marks [tex]...[/tex] text as LaTeX'),
                                  '/\[tex\]/'       // Trigger
                                  );
    }
}
?>