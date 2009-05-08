<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of commentIpGeo, a plugin for Dotclear.
#
# Copyright (c) 2007-2009 Frederic PLE
# dotclear@frederic.ple.name
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if ($core->blog->settings->commentIpGeo_active) {
	$core->tpl->addValue('commentIpGeo',array('commentIpGeo','country_code'));
	$core->tpl->addValue('commentIpGeoFlag',array('commentIpGeo','country_flag'));
	$core->addBehavior('publicHeadContent',array('commentIpGeo','publicHeadContent'));
}


class commentIpGeo {
  public static function publicHeadContent(&$core)
  {
    echo '<link rel="stylesheet" media="all" type="text/css" href="' . $core->blog->url . '?pf=commentIpGeo/default-templates/flags.css" />' . "\n";
  }

  static private function __process($value) {
    return '<?php
  if ($_ctx->comments->comment_ip_geo == "") {
    $ip_geo = netHttp::quickGet("http://api.wipmania.com/" . $_ctx->comments->comment_ip . "?" . $_SERVER["SERVER_NAME"]);
    if ($ip_geo === false)
    	$ip_geo = "XX";
    else {
      $_ctx->comments->comment_ip_geo = $ip_geo;
      $cur = $core->con->openCursor($core->prefix."comment");
      $cur->comment_ip_geo = $ip_geo;
      $cur->update("WHERE comment_id = " . $_ctx->comments->comment_id . ";");
    }
  }
  echo ' . $value . '; ?>';
  }

  static public function country_code($attr) {
    return self::__process('$_ctx->comments->comment_ip_geo');
  }

  static public function country_flag($attr) {
    return self::__process('"<img src=\\"http://static.wipmania.com/_.gif\\" class=\\"flags lang-" . strtolower($_ctx->comments->comment_ip_geo) . "\\" alt=\\"" . strtolower($_ctx->comments->comment_ip_geo) . "-flag\\" />"');
  }
}

