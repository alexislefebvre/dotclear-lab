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
$warped_styles = array(
	__('Blue') => 'blue',
	__('Green') => 'green',
	__('Orange') => 'orange'
);

if (!$core->blog->settings->themes->warped_style) {
	$core->blog->settings->themes->warped_style = 'orange';
}

if (!empty($_POST['warped_style']) && in_array($_POST['warped_style'],$warped_styles))
{
	$core->blog->settings->themes->warped_style = $_POST['warped_style'];
	$core->blog->settings->addNamespace('themes');
	$core->blog->settings->themes->put('warped_style',$core->blog->settings->themes->warped_style,'string','Warped theme style',true);
	$core->blog->triggerBlog();

	dcPage::success(__('Theme configuration has been successfully updated.'));
}

echo
'<div class="fieldset"><h4>'.__('Warped style').'</h4>'.
'<p class="field"><label>'.__('Style:').'</label>'.
form::combo('warped_style',$warped_styles,$core->blog->settings->themes->warped_style).
'</p>'.
'</div>';

?>
