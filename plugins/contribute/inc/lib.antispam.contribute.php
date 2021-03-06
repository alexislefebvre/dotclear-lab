<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Contribute, a plugin for Dotclear 2
# Copyright (C) 2008,2009,2010 Moe (http://gniark.net/)
#
# Contribute is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Contribute is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons :
# <http://www.famfamfam.com/lab/icons/silk/>
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

/**
@ingroup Contribute
@brief Antispam
\see /dotclear/plugins/antispam/inc/lib.dc.antispam.php
*/
class contributeAntispam
{
	public static $filters;
	
	public static function initFilters()
	{
		global $core;
		
		if (!isset($core->spamfilters)) {
			return;
		}
		
		self::$filters = new dcSpamFilters($core);
		self::$filters->init($core->spamfilters);
	}
	
	public static function isSpam($cur)
	{
		self::initFilters();
		return(self::$filters->isSpam($cur));
	}
}

?>