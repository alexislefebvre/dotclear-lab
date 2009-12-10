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


class tplCommentIpGeo {
  public static function publicHeadContent(&$core)
  {
    echo '<style type="text/css" media="all">
  /*-------------------------Flags and languages--------------------------------*/
  .flags {background: url("' . $core->blog->url . ((substr($core->blog->url,-1) == '?') ? '' : '?') . 'pf=commentIpGeo/default-templates/flags.png") no-repeat top left; width:18px; height:12px; vertical-align:middle;}
</style>
<link rel="stylesheet" media="all" type="text/css" href="' . $core->blog->url . ((substr($core->blog->url,-1) == '?') ? '' : '?') . 'pf=commentIpGeo/default-templates/flags.css" />
<link rel="stylesheet" media="all" type="text/css" href="' . $core->blog->url . ((substr($core->blog->url,-1) == '?') ? '' : '?') . 'pf=commentIpGeo/default-templates/isp.css" />
';
  }

  static private function __process($value) {
    return '<?php
  if ($core->blog->settings->commentIpGeo_debug)
    echo "<!-- commentIpGeo Debug " . 
		var_export($_ctx->comments->getIpGeoCountryCode(),true) .
		" -->";
  echo ' . $value . '; ?>';
  }

  static public function country_code($attr) {
    return self::__process('$_ctx->comments->getIpGeoCountryCode()');
  }

  static public function country_flag($attr) {
    return self::__process('"<img src=\\"" . $core->blog->url' .
    			' . ((substr($core->blog->url,-1) == \'?\') ? \'\' : \'?\')' .
			' . "pf=commentIpGeo/default-templates/_.gif\\" ' .
			' class=\\"flags flag-" . $_ctx->comments->getIpGeoCountryCode() . ' .
			'"\\" alt=\\"" . $_ctx->comments->getIpGeoCountryCode() . "-flag\\" />"');
  }

  static public function country_city($attr) {
    return self::__process('$_ctx->comments->getIpGeoCountryCity()');
  }

  static public function isp($attr) {
    return self::__process('"<img src=\\"" . $core->blog->url' .
    			' . ((substr($core->blog->url,-1) == \'?\') ? \'\' : \'?\')' .
			' . "pf=commentIpGeo/default-templates/_isp.png\\" ' .
			' class=\\"isp isp-" . $_ctx->comments->getIpGeoIsp() . ' .
			'"\\" alt=\\"" . $_ctx->comments->getIpGeoIsp() . "-ISP\\" />"');
  }

  static public function debug($attr) {
    return '<?php
      if ($core->blog->settings->commentIpGeo_debug)
        echo "<!-- DEBUG :\n" . $_ctx->comments->debug() . " -->\n";
	?>';
  }
}
?>
