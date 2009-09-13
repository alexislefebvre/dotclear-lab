<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Apercite, a plugin for Dotclear.
# 
# Copyright (c) 2009 Francis Besset
# francis.besset@free.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }
dcPage::checkSuper();


if ($core->blog->settings->apercite_enabled === null) {
	$core->blog->settings->setNameSpace('apercite');
	$core->blog->settings->put('apercite_enabled',0,'boolean');
	$core->blog->settings->put('apercite_size','120x90');
	$core->blog->settings->put('apercite_javascript',1,'boolean');
	$core->blog->settings->put('apercite_java',1,'boolean');
	$core->blog->settings->put('apercite_login','');
	$core->blog->settings->put('apercite_api_key','');
	
	http::redirect($p_url);
}

if ($core->blog->settings->apercite_local_link === null) {
	$core->blog->settings->setNameSpace('apercite');
	$core->blog->settings->put('apercite_local_link',1,'boolean');
	
	http::redirect($p_url);
}

if ($core->blog->settings->apercite_workers === null) {
	$default_workers = array(
		'.post-excerpt',
		'.post-content'
	);
	
	$core->blog->settings->setNameSpace('apercite');
	$core->blog->settings->put('apercite_workers',serialize($default_workers));
	
	http::redirect($p_url);
}


if (isset($_POST['xd_check'])) {
	$_POST['apercite_size'] = (isset($_POST['apercite_size']) ? $_POST['apercite_size'] : '120x90');
	$_POST['apercite_login'] = (isset($_POST['apercite_login']) ? $_POST['apercite_login'] : '');
	$_POST['apercite_api_key'] = (isset($_POST['apercite_api_key']) ? $_POST['apercite_api_key'] : '');
	
	/*$_POST['apercite_login'] = trim($_POST['apercite_login']);
	$_POST['apercite_api_key'] = trim($_POST['apercite_api_key']);*/
	
	$core->blog->settings->setNameSpace('apercite');
	$core->blog->settings->put('apercite_enabled',!empty($_POST['apercite_enabled']),'boolean');
	$core->blog->settings->put('apercite_size',$_POST['apercite_size']);
	$core->blog->settings->put('apercite_local_link',!empty($_POST['apercite_local_link']),'boolean');
	$core->blog->settings->put('apercite_javascript',!empty($_POST['apercite_javascript']),'boolean');
	$core->blog->settings->put('apercite_java',!empty($_POST['apercite_java']),'boolean');
	$core->blog->settings->put('apercite_login',$_POST['apercite_login']);
	$core->blog->settings->put('apercite_api_key',$_POST['apercite_api_key']);
	
	$_POST['apercite_workers'] = is_array($_POST['apercite_workers']) ? $_POST['apercite_workers'] : array();
	foreach ($_POST['apercite_workers'] as $k=>$v) {
		$v = trim($v);
		if (!empty($v)) {
			$workers[] = $v;
		}
	}
	$core->blog->settings->put('apercite_workers',serialize($workers));
	
	http::redirect($p_url.'&up=1');
}

$workers = @unserialize($core->blog->settings->apercite_workers);
if (!$workers) {
	$workers = array();
}
?>
<html>
<head>
  <title><?php echo __('Apercite'); ?></title>
</head>

<body>
<?php
echo '<h2>'.__('Apercite configuration').'</h2>';

if (!empty($_GET['up'])) {
	echo '<p class="message">'.__('Apercite success save').'</p>';
}

echo
'<form action="'.$p_url.'" method="post" enctype="multipart/form-data">'.
'<fieldset><legend>'.__('Apercite general').'</legend>'.
'<div class="two-cols">'.
'<div class="col">'.
'<p><label class="classic">'.
form::checkbox('apercite_enabled','1',$core->blog->settings->apercite_enabled).
__('Apercite enable').'</label></p>'.
'<p><label class="classic">'.
form::checkbox('apercite_local_link','1',$core->blog->settings->apercite_local_link).
__('Apercite local link').'</label></p>'.
'<p><label class="classic">'.
form::checkbox('apercite_javascript','1',$core->blog->settings->apercite_javascript).
__('Apercite javascript enable').'</label></p>'.
'<p><label class="classic">'.
form::checkbox('apercite_java','1',$core->blog->settings->apercite_java).
__('Apercite java enable').'</label></p>'.
'<p><label class="classic">'.
__('Apercite size').' :<br />'.
form::combo('apercite_size',array(
	'4:3' => array(
		'80x60'		=> '80x60',
		'100x75'	=> '100x75',
		'120x90'	=> '120x90',
		'160x120'	=> '160x120',
		'180x135'	=> '180x135',
		'240x180'	=> '240x180',
		'320x240'	=> '320x240',
		'560x420'	=> '560x420',
		'640x480'	=> '640x480',
		'800x600'	=> '800x600'
	),
	'16:10' => array(
		'80x50'		=> '80x50',
		'120x75'	=> '120x75',
		'160x100'	=> '160x100',
		'200x125'	=> '200x125',
		'240x150'	=> '240x250',
		'320x200'	=> '320x200',
		'560x350'	=> '560x350',
		'640x400'	=> '640x400',
		'800x500'	=> '800x500'
	)
),$core->blog->settings->apercite_size).
'</label></p>'.
'</div>'.
'<div class="col">'.
'<p>'.__('Apercite name workers').' :<br />';

foreach ($workers as $k=>$v) {
	echo
	form::field(array('apercite_workers[]'),40,128,html::escapeHTML($v));
}

echo
form::field(array('apercite_workers[]'),40,128).
'</p>'.
'</div>'.
'</div>'.
'</fieldset>';

echo
'<fieldset><legend>'.__('Apercite member').'</legend>'.
'<p>'.__('Apercite details member').'</p>'.
'<p><label class="classic">'.
__('Apercite login').' :<br />'.
form::field('apercite_login',20,16,html::escapeHTML($core->blog->settings->apercite_login)).
'</label></p>'.
'<p><label class="classic">'.
__('Apercite api key').' :<br />'.
form::field('apercite_api_key',20,32,html::escapeHTML($core->blog->settings->apercite_api_key)).
'</label></p>'.
'</fieldset>';

echo
'<p><input type="submit" value="'.__('Apercite save').'" />'.
$core->formNonce().'</p>'.
'</form>';
?>
</body>
</html>