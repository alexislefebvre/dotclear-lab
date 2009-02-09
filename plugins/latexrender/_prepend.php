<?php 
  // ***** BEGIN LICENSE BLOCK *****
  // This file is (c) Jean-Christophe Dubacq.
  // Licensed under CC-BY licence.
  //
  // ***** END LICENSE BLOCK *****

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
