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

$flavin_styles = array(
	__('Pink') => 'pink',
	__('Blue') => 'blue',
	__('Green') => 'green'
);

if (!empty($_POST['flavin_style']) && in_array($_POST['flavin_style'],$flavin_styles))
{
	$core->blog->settings->flavin_style = $_POST['flavin_style'];
	$core->blog->settings->setNamespace('themes');
	$core->blog->settings->put('flavin_style',$core->blog->settings->flavin_style,'string','Flavin theme style',true);
	$core->blog->triggerBlog();
}

echo
'<fieldset><legend>Flavin style</legend>'.
'<p class="field"><label>'.__('Style:').' '.
form::combo('flavin_style',$flavin_styles,$core->blog->settings->flavin_style).
'</p>'.
'</fieldset>';
?>