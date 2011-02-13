<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of soCialMe, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Read settings
$s_css = $page['setting']->css;

# Save settings
if ($request_act == 'save')
{
	try
	{
		$s_css = $_POST['s_css'];
		
		$page['setting']->put('css',$s_css);
		
		$core->blog->triggerBlog();
		
		http::redirect(soCialMeAdmin::link(0,$request_page,$request_part));
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

# Settings form
echo soCialMeAdmin::top($page).
'<p>'.__('Configure global settings for this part.').'</p>'.
'<form id="setting-form" method="post" action="'.soCialMeAdmin::link(1,$request_page).'">'.

'<fieldset id="setting-style"><legend>'. __('Style').'</legend>'.
'<p><label class="classic">'.__('Additionnal style sheet:').'<br />'.
form::textarea(array('s_css'),164,5,$s_css,'maximal').'</label></p>'.
'<p class="form-note">'.sprintf(__('Elements are placed in HTML tag "div" of class "social-%1$ss social-LOCATION_ID". Each Element in HTML tag "li" of class "social-%1$s social-id-SERVICE_ID".'),$request_page).'</p>'.
'</fieldset>'.
'<div class="clear">'.
'<p><input type="submit" name="save" value="'.__('save').'" />'.
$core->formNonce().
form::hidden(array('p'),'soCialMe').
form::hidden(array('page'),$request_page).
form::hidden(array('part'),$request_part).
form::hidden(array('act'),'save').
'</p></div>'.
'</form>';
?>