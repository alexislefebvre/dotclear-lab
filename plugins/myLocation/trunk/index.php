<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of myLocation, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

# Initialization
$p_url = 'plugin.php?p=myLocation';

# Save configuration
if (isset($_POST['save'])) {
	try {
		$core->blog->settings->myLocation->put('enable',!empty($_POST['enable']));
		$core->blog->settings->myLocation->put('position',$_POST['position']);
		$core->blog->settings->myLocation->put('css',$_POST['css']);
		$core->blog->settings->myLocation->put('accuracy',$_POST['accuracy']);
		$core->blog->settings->myLocation->put('mask',$_POST['mask']);
		http::redirect($p_url.'&upd=1');
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

$combo_positions = array(
	__('After content') => 'afterContent',
	__('After author name') => 'CommentAuthorLink'
);
$combo_accuracy = array(
	__('Street address') => 'street_address',
	__('Postal code') => 'postal_code',
	__('Administrative area') => 'administrative_area_level_1',
	__('Neighborhood') => 'neighborhood',
	__('State') => 'locality',
	__('Country') => 'country'
);

# DISPLAY
# -------
echo
'<html>'.
'<head>'.
	'<title>'.__('myLocation').'</title>'.
'</head>'.
'<body>';

# General messages
if (isset($_GET['upd'])) {
	echo '<p class="message">'.__('Configuration has been successfully updated').'</p>';
}

echo
'<h2>'.$core->blog->name.' &rsaquo; '.__('myLocation').'</h2>'.
'<form method="post" action="'.$p_url.'">'.
'<fieldset><legend>'.__('General options').'</legend>'.
'<p><label class="classic" for="enable">'.
	form::checkbox('enable','1',$core->blog->settings->myLocation->enable).
	__('Enable geolocation').'</label></p>'.
'<p><label class="field" for="position">'.
	__('Position of location display').'&nbsp;'.
	form::combo('position',$combo_positions,$core->blog->settings->myLocation->position).
'</label></p>'.
'<p><label class="field" for="css">'.
	__('Custom CSS').
	form::field('css',100,255,$core->blog->settings->myLocation->css).
'</label></p>'.
'<p class="form-note">'.__('Leave blank to use the default plugin CSS').'</p>'.
'<p><label class="field" for="accuracy">'.
	__('Geolocalisation accuracy').'&nbsp;'.
	form::combo('accuracy',$combo_accuracy,$core->blog->settings->myLocation->accuracy).
'</label></p>'.
'<p><label class="field" for="mask">'.
	__('Display mask').'&nbsp;'.
	form::field('mask',100,255,html::escapeHTML($core->blog->settings->myLocation->mask)).
'</label></p>'.
'<p class="form-note">'.__('%1$s = Google Maps URL, %2$s = location text').'</p>'.
'</fieldset>'.
'<p>'.$core->formNonce().'<input type="submit" name="save" value="'.__('Save').'" /></p>'.
'</form>'.

'</body>'.
'</html>';