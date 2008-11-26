<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Clock.
# Copyright 2007-2008 Moe (http://gniark.net/)
#
# Clock is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Clock is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$tz = $core->blog->settings->blog_timezone;

?>

<html>
<head>
  <title><?php echo __('Clock'); ?></title>
</head>
<body>
<h2><?php echo __('Clock'); ?></h2>
<?php
	echo('<fieldset><legend>'.__('Select a timezone').'</legend>'.form::combo('tz',dt::getZones(true,true),$tz,null,null,null,'onchange="javascript:document.getElementById(\'echo_tz\').value=this.value;document.getElementById(\'echo_tz\').select();"').'</fieldset>');
	echo('<fieldset><legend>'.__('Copy and paste this timezone in a Clock widget').'</legend>'.form::field('echo_tz',40,40,$tz,null,null,null,'onfocus="javascript:this.select();"').'</fieldset>');
?>
</body>
</html>