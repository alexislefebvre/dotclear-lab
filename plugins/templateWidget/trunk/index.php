<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of templateWidget
# Copyright (c) 2009 Olivier Azeau and contributors. All rights reserved.
#
# This is free software; you can redistribute it and/or modify
# it under the terms of the Affero GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
# 
# This is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the Affero GNU General Public License
# along with templateWidget; If not, see <http://www.gnu.org/licenses/>.
#
# templateWidget is a plugin for Dotclear software.
# templateWidget is not part of Dotclear.
# templateWidget can be used inside Dotclear but this license only applies to templateWidget
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { return; }

dcPage::check('usage,contentadmin');

require_once(dirname(__FILE__).'/inc/Settings.php');

try
{
?>
<html>
<head>
  <title>Template Widget</title>
</head>
<body>
<?php
  $activeWidgets = new templateWidgetSettings();
  if ($activeWidgets->LoadFromHTTP()) {
    $activeWidgets->Store();
    print '<p class="message">'.__('Settings have been successfully updated.').'</p>';
  }
  foreach (templateWidgetAdmin::GetAllWidgetDefinitions() as $widgetId => $widgetDefinition) {
    $activeWidgets->UpdateWith( templateWidgetActive::FromWidgetDefinition($widgetDefinition) );
  }
  print '<h2>'.__('Define active widgets').'</h2>';
  print '<form action="'.$p_url.'" method="post">';
  $activeWidgets->Display();
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