<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Wiki Tables, a plugin for Dotclear 2
# Copyright (C) 2009,2010 Moe (http://gniark.net/)
#
# Wiki Tables is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Wiki Tables is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# Icon (icon.png) is from Silk Icons :
# http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/admin');

$core->addBehavior('adminPostHeaders',
	array('wikiTablesAdmin','postHeaders'));

# modified from the YASH plugin
$core->addBehavior('coreInitWikiPost',
	array('wikiTablesAdmin','coreInitWikiPost'));

class wikiTablesAdmin
{
	public static function coreInitWikiPost($wiki2xhtml)
	{
		$wiki2xhtml->registerFunction('macro:table',
			array('wikiTables','transform'));
	}
	
	# /modified from the YASH plugin
	
	public static function postHeaders()
	{
		return('<script type="text/javascript" '.
			'src="index.php?pf=wikiTables/js/post.js"></script>'.
			
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		dcPage::jsVar('jsToolBar.prototype.elements.wiki_table.title',
			__('Wiki Table')).
		"\n//]]>\n".
		"</script>\n");
	}
}

?>