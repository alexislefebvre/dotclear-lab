<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Sup Sub Tags.
# Copyright 2007,2009 Moe (http://gniark.net/)
#
# Sup Sub Tags is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Sup Sub Tags is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Images are from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

$_menu['Plugins']->addItem(__('Sup Sub Tags'),'plugin.php?p=supSubTags',
	'index.php?pf=supSubTags/icon.png',
	preg_match('/plugin.php\?p=supSubTags(&.*)?$/',
	$_SERVER['REQUEST_URI']),$core->auth->check('admin',$core->blog->id));

$core->addBehavior('adminPostHeaders',array('supsub','postHeaders'));
$core->addBehavior('coreInitWikiPost',array('supsub','wiki'));

class supsub
{
	public static function wiki($wiki2xhtml)
	{	
		$s = $GLOBALS['core']->blog->settings;
		
		# the empty() function can't test directly a setting,
		# we avoid this by testing a new variable with the same value
		$sup_open = (string) $s->supsub_tags_sup_open;
		$sup_close = (string) $s->supsub_tags_sup_close;
		$sub_open = (string) $s->supsub_tags_sub_open;
		$sub_close = (string) $s->supsub_tags_sub_close;

		$tags = array();
		
		# empty tags cause errors, we ignore them
		if (!empty($sup_open) && (!empty($sup_close)))
		{
			# declare the <sup></sup> tag
			$tags['sup'] = array($sup_open,$sup_close);
		}
		
		if (!empty($sub_open) && (!empty($sub_close)))
		{
			# declare the <sub></sub> tag
			$tags['sub'] = array($sub_open,$sub_close);
		}
		
		if (empty($tags)) {return;}
		
		$wiki2xhtml->custom_tags = array_merge($wiki2xhtml->custom_tags,
			$tags);
	}

	public static function postHeaders()
	{
		$s = $GLOBALS['core']->blog->settings;

		return
		'<script type="text/javascript" src="index.php?pf=supSubTags/post.js"></script>'.
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		dcPage::jsVar('jsToolBar.prototype.elements.sup.title',
			__('Superscript')).
		dcPage::jsVar('jsToolBar.prototype.elements.sub.title',
			__('Subscript')).
		"\n//]]>\n".
		"jsToolBar.prototype.elements.sup.fn.wiki = ".
		"function() { this.encloseSelection('".
		html::escapeJS($s->supsub_tags_sup_open)."','".
		html::escapeJS($s->supsub_tags_sup_close)."') };".
		"jsToolBar.prototype.elements.sub.fn.wiki = ".
		"function() { this.encloseSelection('".
		html::escapeJS($s->supsub_tags_sub_open)."','".
		html::escapeJS($s->supsub_tags_sub_close)."') };".
		"</script>\n";
	}
}

?>