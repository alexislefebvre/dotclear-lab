<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# Copyright (c) 2008 Steven Tlucek
#
# This work is licensed under the Creative Commons
# Attribution-Share Alike 3.0 Unported License.
# To view a copy of this license, visit
# http://creativecommons.org/licenses/by-sa/3.0/ or send a
# letter to Creative Commons, 171 Second Street, Suite 300,
# San Francisco, California, 94105, USA.
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }
l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/main');

$flavin_styles = array(
	__('Pink') => 'pink',
	__('Blue') => 'blue',
	__('Green') => 'green'
);

if (!$core->blog->settings->themes->flavin_style) {
	$core->blog->settings->themes->flavin_style = 'pink';
}

if (!empty($_POST['flavin_style']) && in_array($_POST['flavin_style'],$flavin_styles))
{
	$core->blog->settings->themes->flavin_style = $_POST['flavin_style'];
	$core->blog->settings->addNamespace('themes');
	$core->blog->settings->themes->put('flavin_style',$core->blog->settings->themes->flavin_style,'string','Flavin theme style',true);
	$core->blog->triggerBlog();

	dcPage::success(__('Theme configuration has been successfully updated.'));
}

echo
'<div class="fieldset"><h4>'.__('Flavin style').'</h4>'.
'<p class="field"><label>'.__('Style:').'</label>'.
form::combo('flavin_style',$flavin_styles,$core->blog->settings->themes->flavin_style).
'</p>'.
'</div>';
?>
