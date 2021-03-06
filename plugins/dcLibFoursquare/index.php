<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibFoursquare, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

dcPage::checkSuper();

$s_oauth_admin = foursquareUtils::decodeApp('admin');
if (!empty($_POST['superadminsave']))
{
	$s_oauth_admin = foursquareUtils::encodeApp('admin',$_POST['oauth_admin_client_id'],$_POST['oauth_admin_client_secret'],$_POST['oauth_admin_redirect_uri']);
}

echo 
'<html><head><title>'.__('Foursquare manager').'</title>'.
'</head><body><h2>'.__('Foursquare manager').'</h2>'.
'<fieldset><legend>'.__('Register multiblog administration pages').'</legend>'.
'<form action="'.$p_url.'" method="post" id="form-superadmin">';

if (empty($s_oauth_admin['client_id']))
{
	echo 
	'<p>'.sprintf(__('In order to use %s on your blog, you must first create a %s app.'),'Foursquare','Foursquare').'</p>'.
	'<ul>'.
	'<li>1) <a href="https://foursquare.com/oauth/">'.sprintf(__('Go to %s application registration page'),'Foursquare').'</a></li>'.
	'<li>2) '.__('Enter your multiblog name as your application name').'</li>'.
	'<li>3) '.sprintf(__('Enter your multiblog administration URL as your app URL ( %s )'),DC_ADMIN_URL).'</li>'.
	'<li>4) '.__('When its done:').'</li>'.
	'<li><label>'.__('Enter your new App ID here:').' '.form::field(array('oauth_admin_client_id'),100,255,'').'</label></li>'.
	'<li><label>'.__('Enter your new App Secret here:').' '.form::field(array('oauth_admin_client_secret'),100,255,'').'</label></li>'.
	'<li><label>'.__('Confirm your redirect URL (admin URL) here:').' '.form::field(array('oauth_admin_redirect_uri'),100,255,'').'</label></li>'.
	'</ul>'.
	'<p><input type="submit" name="superadminsave" value="'.__('save').'" />';
}
else
{
	echo
	'<p>'.sprintf(__('You have already registered a %s app.'),'Foursquare').'</p>'.
	'<ul>'.
	'<li>'.sprintf(__('Application ID: %s'),$s_oauth_admin['client_id']).'</li>'.
	'<li>'.sprintf(__('Application secret: %s'),$s_oauth_admin['client_secret']).'</li>'.
	'<li>'.sprintf(__('Redirect URL: %s'),$s_oauth_admin['redirect_uri']).'</li>'.
	'</ul>'.
	form::hidden(array('oauth_admin_client_id'),'').
	form::hidden(array('oauth_admin_client_secret'),'').
	form::hidden(array('oauth_admin_redirect_uri'),'').
	'<p><input type="submit" name="superadminsave" value="'.__('delete').'" />';
}
echo 
$core->formNonce().'</p>'.
'</form>'.
'</fieldset>';

dcPage::helpBlock('dcLibFoursquare');
echo '
<hr class="clear"/><p class="right">
dcLibFoursquare - '.$core->plugins->moduleInfo('dcLibFoursquare','version').'&nbsp;
<img alt="'.__('Foursquare manager').'" src="index.php?pf=dcLibFoursquare/icon.png" />
</p>
</body>
</html>';
?>