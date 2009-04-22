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

include_once(dirname(__FILE__).'/class.latexrender.php');

class tplLatexRender
{
    public static function LatexText(&$rs,$text,$stack,$elements) {
        if ((isset($elements['pre']) && $elements['pre']>0) ||
            (isset($elements['code']) && $elements['code']>0)) {
            return $text;
        }
        $core=$rs->core;
        $content=$text;
        $newcontent='';
        if (!isset($core->latex)) {
            $core->latex = new LatexRender($core);
        }
        $latex=$core->latex;
        while(preg_match("#\[tex\](.*?)\[/tex\]#s",$content,$tex_matches)) {
            $pos=strpos($content,'[tex]');
            $newcontent.=substr($content,0,$pos);
            $endpos=strpos($content,'[/tex]',$pos);
            $latex_formula=substr($content,$pos+5,$endpos-$pos-5);
            $html = $latex->getFormulaHTML($latex_formula,
                                           isset($core->theme_color)?
                                           $core->theme_color:
                                           '000000');
            $newcontent.=$html;
            $content=substr($content,$endpos+6);
        }
        $newcontent.=$content;
        return $newcontent;
    }
}

?>