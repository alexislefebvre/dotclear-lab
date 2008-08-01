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

$papierpeint_styles = array(
	"30's" => '1930',
	"50's" => '1950',
	"70's" => '1970'
);

if (!$core->blog->settings->papierpeint_style) {
	$core->blog->settings->papierpeint_style = '1950';
}

if (!empty($_POST['papierpeint_style']) && in_array($_POST['papierpeint_style'],$papierpeint_styles))
{
	$core->blog->settings->papierpeint_style = $_POST['papierpeint_style'];
	$core->blog->settings->setNamespace('themes');
	$core->blog->settings->put('papierpeint_style',$core->blog->settings->papierpeint_style,'string','Papier Peint theme style',true);
	$core->blog->triggerBlog();
}

echo
'<fieldset><legend>Papier Peint style</legend>'.
'<p class="field"><label>'.__('Style:').' '.
form::combo('papierpeint_style',$papierpeint_styles,$core->blog->settings->papierpeint_style).
'</p>'.
'</fieldset>';

?>