<?php

class tplCommentIpGeo {
  public static function publicHeadContent(&$core)
  {
    echo '<style type="text/css" media="all">
  /*-------------------------Flags and languages--------------------------------*/
  .flags {background: url("' . $core->blog->url . '?pf=commentIpGeo/default-templates/flags.png") no-repeat top left; width:18px; height:12px; vertical-align:middle;}
</style>
<link rel="stylesheet" media="all" type="text/css" href="' . $core->blog->url . '?pf=commentIpGeo/default-templates/flags.css" />
';
  }

  static private function __process($value) {
    return '<?php
  $ip_geo = $_ctx->comments->comment_ip_geo ;
  $dmsg = "COMMENT = " . var_export($ip_geo,true) . "\n";
  if ($ip_geo === false or $ip_geo === "" or $ip_geo === null) {
    $ip_geo = netHttp::quickGet("http://api.wipmania.com/" . $_ctx->comments->comment_ip . "?" . $_SERVER["SERVER_NAME"]);
    $dmsg .= "\twipmania ======> " . $_ctx->comments->comment_ip ." -> " . $ip_geo . "\n";
    if ($ip_geo === false)
    	$ip_geo = "XX";
    else {
      $cur = $core->con->openCursor($core->prefix."comment");
      $cur->comment_ip_geo = $ip_geo;
      $cur->update("WHERE comment_id = " . $_ctx->comments->comment_id . ";");
      $cur->clean();
    }
    $_ctx->comments->comment_ip_geo = $ip_geo;
  }
  if ($core->blog->settings->commentIpGeo_debug)
    echo "<!-- commentIpGeo Debug\n" . $dmsg . "\n -->\n";
  echo ' . $value . ';
?>';
  }

  static public function country_code($attr) {
    return self::__process('$_ctx->comments->comment_ip_geo');
  }

  static public function country_flag($attr) {
    return self::__process('"<img src=\\"" . $core->blog->url . "?pf=commentIpGeo/default-templates/_.gif\\" class=\\"flags flag-" . $ip_geo . "\\" alt=\\"" . $ip_geo . "-flag\\" />"');
  }
}

?>