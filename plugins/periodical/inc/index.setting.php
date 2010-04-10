<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of periodical, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$sortby_combo = array(
	__('Create date') => 'post_creadt',
	__('Date') => 'post_dt',
	__('ID') => 'post_id'
);
$order_combo = array(
	__('Descending') => 'desc',
	__('Ascending') => 'asc'
);

$s_active = (boolean) $s->periodical_active;
$s_upddate = (boolean) $s->periodical_upddate;
$s_updurl = (boolean) $s->periodical_updurl;
$e_order = (string) $s->periodical_pub_order;
$e_order = explode(' ',$e_order);
$s_sortby = in_array($e_order[0],$sortby_combo) ?
	$e_order : 'post_dt';
$s_order = isset($e_order[1]) && strtolower($e_order[1]) == 'desc' ?
	'desc' : 'asc';

if ($default_part == 'setting' && $action == 'savesetting')
{
	try {
		$s->put('periodical_active',!empty($_POST['s_active']));
		$s->put('periodical_upddate',!empty($_POST['s_upddate']));
		$s->put('periodical_updurl',!empty($_POST['s_updurl']));
		$s->put('periodical_pub_order',$_POST['s_sortby'].' '.$_POST['s_order']);
		$core->blog->triggerBlog();

		http::redirect('plugin.php?p=periodical&part=setting&msg='.$action);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

echo '
<html><head><title>'.__('Periodical').'</title></head>
<body>
<h2>'.
html::escapeHTML($core->blog->name).
' &rsaquo; <a href="'.$p_url.'&amp;part=periods">'.__('Periodical').'</a>'.
' &rsaquo; '.__('Settings').
' - <a class="button" href="'.$p_url.'&amp;part=addperiod">'.__('New period').'</a>'.
'</h2>'.$msg.'

<form method="post" action="plugin.php">
<fieldset><legend>'.__('Extension').'</legend>
<p class="field"><label>'.
form::checkbox(array('s_active'),'1',$s_active).' '.
__('Enable extension').'</label></p>
</fieldset>
<fieldset><legend>'.__('Dates of published entries').'</legend>
<p class="field"><label>'.
form::checkbox(array('s_upddate'),'1',$s_upddate).' '.
__('Update post date').'</label></p>
<p class="field"><label>'.
form::checkbox(array('s_updurl'),'1',$s_updurl).' '.
__('Update post url').'</label></p>
</fieldset>
<fieldset><legend>'.__('Order of publication of entries').'</legend>
<p class="field"><label>'.__('Order by:').
form::combo('s_sortby',$sortby_combo,$s_sortby).'</label></p>
<p class="field"><label>'.__('Sort:').
form::combo('s_order',$order_combo,$s_order).'</label></p>
</fieldset>
<div class="clear">
<p><input type="submit" name="save" value="'.__('save').'" />'.
$core->formNonce().
form::hidden(array('p'),'periodical').
form::hidden(array('part'),'setting').
form::hidden(array('action'),'savesetting').'
</p></div>
</form>';
?>