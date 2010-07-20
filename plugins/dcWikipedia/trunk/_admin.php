<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcWikipedia, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('adminPostHeaders',array('dcWikipediaBehaviors','postHeaders'));
$core->addBehavior('adminPageHeaders',array('dcWikipediaBehaviors','postHeaders'));
$core->addBehavior('adminRelatedHeaders',array('dcWikipediaBehaviors','postHeaders'));
$core->addBehavior('adminBlogPreferencesForm',array('dcWikipediaBehaviors','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('dcWikipediaBehaviors','adminBeforeBlogSettingsUpdate'));

class dcWikipediaBehaviors
{
	public static function postHeaders()
	{
		global $core;
		
		$flag = 'no';
		if (!is_null($core->blog->settings->getSetting('dcwp_add_lang_flag'))) {
			$flag = $core->blog->settings->dcwikipedia->dcwp_add_lang_flag ? 'yes' : 'no';
		}

		return
		'<script type="text/javascript" src="index.php?pf=dcWikipedia/js/post.min.js"></script>'.
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		dcPage::jsVar('jsToolBar.prototype.elements.dcWikipedia.title',__('Wikipedia')).
		dcPage::jsVar('dcWikipedia.option.langFlag',$flag).
		dcPage::jsVar('dcWikipedia.msg.noselection',__('Please, select a word or an expression')).
		"\n//]]>\n".
		"</script>\n";
	}

	public static function adminBlogPreferencesForm($core,$settings)
	{
		echo
		'<fieldset><legend>'.__('dcWikipedia').'</legend>'.
		'<p><label class="classic">'.
		form::checkbox('dcwp_add_lang_flag','1',(!is_null($settings->getSetting('dcwp_add_lang_flag')) ? $settings->dcwikipedia->dcwp_add_lang_flag : false)).
		__('Add lang flag in link').'</label></p>'.
		'</fieldset>';
	}

	public static function adminBeforeBlogSettingsUpdate($settings)
	{
		$settings->addNameSpace('dcwikipedia');
		$settings->dcwikipedia->put('dcwp_add_lang_flag',!empty($_POST['dcwp_add_lang_flag']),'boolean');
	}
}

?>