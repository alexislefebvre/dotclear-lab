<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of openidDelegation, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------

$providers["myopenid"] = array("name" => "myOpenID",
                               "header" => '
<link rel="openid.server" href="http://www.myopenid.com/server" />
<link rel="openid.delegate" href="http://%1$s.myopenid.com/" />
<link rel="openid2.local_id" href="http://%1$s.myopenid.com" />
<link rel="openid2.provider" href="http://www.myopenid.com/server" />
<meta http-equiv="X-XRDS-Location"
      content="http://www.myopenid.com/xrds?username=%1$s.myopenid.com" />
');

$providers["verisign"] = array("name" => "Verisign PIP",
                               "header" => '
<link rel="openid.server"
      href="http://pip.verisignlabs.com/server" />
<link rel="openid.delegate" 
      href="http://%1$s.pip.verisignlabs.com" />
<link rel="openid2.provider" 
      href="http://pip.verisignlabs.com/server" />
<link rel="openid2.local_id"
      href="http://%1$s.pip.verisignlabs.com" />
<meta http-equiv="X-XRDS-Location" 
      content="http://pip.verisignlabs.com/user/%1$s/yadisxrds" />
');

$providers["startssl"] = array("name" => "StartSSL",
                                "header" => '
<link rel="openid.server" href="https://www.startssl.com/id.ssl" />
<link rel="openid.delegate" href="https://%1$s.startssl.com/" />
<link rel="openid2.provider" href="https://www.startssl.com/id.ssl" />
<link rel="openid2.local_id" href="https://%1$s.startssl.com/" />
<meta http-equiv="X-XRDS-Location" content="https://%1$s.startssl.com/xrds/" />
');

$providers["livejournal"] = array("name" => "LiveJournal",
                                  "header" => '
<link rel="openid.server"
      href="http://www.livejournal.com/openid/server.bml" />
<link rel="openid.delegate"
      href="http://%1$s.livejournal.com/" />
');

$providers["aol"] = array("name" => "AOL",
                          "header" => '
<link rel="openid.server" 
      href="https://api.screenname.aol.com/auth/openidServer" />
<link rel="openid.delegate" 
      href="http://openid.aol.com/%1$s" />
');

?>
