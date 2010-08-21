<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Apercite, a plugin for Dotclear.
# 
# Copyright (c) 2009 Francis Besset
# francis.besset@free.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('publicHeadContent',   array('ApercitePublic','publicHeadContent'));
$core->addBehavior('publicFooterContent', array('ApercitePublic','publicFooterContent'));

class ApercitePublic
{
  public static function publicHeadContent($core)
  {
    if (!$core->blog->settings->apercite->enabled)
    {
      return;
    }

    $url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));

    $size       = ($core->blog->settings->apercite->size       || $core->blog->settings->apercite->size === null ? explode('x', $core->blog->settings->apercite->size) : array(120,90));
    $javascript = ($core->blog->settings->apercite->javascript || $core->blog->settings->apercite->javascript === null ? 'oui' : 'non');
    $java       = ($core->blog->settings->apercite->java       || $core->blog->settings->apercite->java === null ? 'oui' : 'non');
    $workers    = @unserialize($core->blog->settings->apercite->workers);

    if (!$workers)
    {
      $workers = array();
    }

    echo
      '<style type="text/css">
        @import url('.$url.'/css/style.css);
      </style>
      <script type="text/javascript" src="'.$url.'/js/apercite.js"></script>
      <script type="text/javascript">
        //<![CDATA[
          $(function() {
            $("body").apercite({
              "workers":Array('."\n";
                foreach ($workers as $k=>$v)
                {
                  if ($k)
                  {
                    echo ','."\n";
                  }

                  echo '"'.html::escapeHTML($v).'"';
                }
    echo
              "\n".'),
              "baseURL":"'.$core->blog->host.'",
              "localLink":"'.($core->blog->settings->apercite->localLink || $core->blog->settings->apercite->localLink === null ? 'oui' : 'non').'",
              "sizeX":'.$size[0].',
              "sizeY":'.$size[1].',
              "javascript":"'.$javascript.'",
              "java":"'.$java.'"
            });
          });
        //]]>
      </script>'."\n";
  }

  public static function publicFooterContent($core)
  {
    echo
      '<div id="apercite-thumbnail">
        <a href="http://www.apercite.fr/">Générateur de miniatures</a>
      </div>'."\n";
  }
}
?>