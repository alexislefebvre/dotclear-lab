<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

require dirname(__FILE__).'/../inc/admin/prepend.php';

dcPage::check('usage,contentadmin');

try 
{
	if(!file_exists(dirname(__FILE__).'/dclb.js/dclb.popup_link.js'))
	{
		throw new Exception(__('Missing file:').' dclb.popup_link.js in '.dirname(__FILE__).'/dclb.js/');
	}
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

$href = !empty($_GET['href']) ? $_GET['href'] : '';
$title = !empty($_GET['title']) ? $_GET['title'] : '';
$hreflang = !empty($_GET['hreflang']) ? $_GET['hreflang'] : '';
$hreflang = !empty($_GET['lbox']) ? $_GET['lbox'] : '';
$hreflang = !empty($_GET['gname']) ? $_GET['gname'] : '';

dcPage::openPopup(__('Add a link'),dcPage::jsLoad('dclb.js/dclb.popup_link.js'));

echo '<h2>'.__('Add a link').'</h2>';

echo
'<form id="link-insert-form" action="#" method="get">'.
'<p><label class="required" title="'.__('Required field').'">'.__('Link URL:').' '.
form::field('href',35,512,html::escapeHTML($href)).'</label></p>'.
'<p><label>'.__('Link title:').' '.
form::field('title',35,512,html::escapeHTML($title)).'</label></p>'.
'<p><label>'.__('Link language:').' '.
form::field('hreflang',5,5,html::escapeHTML($hreflang)).'</label></p>'.
'<fieldset>'.
'<legend>'.__('Lightbox effect:').'</legend>'.
'<p>'.
'<label>'.form::radio(array('lbox','enable_lbox'),1,false,'','',false,'style="display:inline;"').__('yes').'</label>'.
'<label>'.form::radio(array('lbox','disable_lbox'),0,true,'','',false,'style="display:inline;"').__('no').'</label>'.
'<label>'.__('Groupe name:').' '.
form::field('gname',35,512,html::escapeHTML($gname)).'</label></p>'.
'</fieldset>'.
'</form>'.

'<p><a href="#" id="link-insert-cancel">'.__('cancel').'</a> - '.
'<strong><a href="#" id="link-insert-ok">'.__('insert link').'</a></strong></p>';

dcPage::closePopup();
?>