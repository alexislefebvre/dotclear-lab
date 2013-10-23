<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Plugins Page.
# Copyright 2007 Moe (http://gniark.net/)
#
# Plugins Page is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Plugins Page is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/main');

?>
<html>
<head>
  <title><?php echo __('Plugins Page'); ?></title>
</head>
<body>

	<h2><?php echo __('Plugins Page'); ?></h2>

	<p class="warning"><?php echo(__('The developement of this plugin is stopped.'). 
		' '. 
		__('Please remove this plugin.')) ?></p>

</body>
</html>