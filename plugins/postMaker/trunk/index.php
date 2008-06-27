<?php
# ***** BEGIN LICENSE BLOCK *****
# Copyright (c) 2008 Olivier Azeau and contributors. All rights
# reserved.
#
# This is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# This is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_CONTEXT_ADMIN')) { return; }

dcPage::check('usage,contentadmin');

try
{
  $postMakerSettings = new PostMakerSettings();
  $postMakerUpdate = $postMakerSettings->LoadFromHTTP();
?>
<html>
<head>
  <title>Post Maker</title>
</head>
<body>
<?php
  if ($postMakerUpdate) {
    print '<p class="message">'.__('Settings have been successfully updated.').'</p>';
  }
  print '<h2>'.__('Define custom entries').'</h2>';
  print '<form action="'.$p_url.'" method="post">';
  $postMakerSettings->Display();
  print '<p><input type="submit" value="'.__('save').'" />'.$core->formNonce().'</p>'.'</form>';
?>
</body>
</html>
<?php
}
catch (Exception $e)
{
  $core->error->add($e->getMessage());
}
?>