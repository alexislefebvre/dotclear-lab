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


$tabsOrder = array(
	__('Apercite overview')			=> 0,
	__('Apercite configuration')	=> 1,
	__('Apercite subscription')		=> 2,
	__('Apercite about')			=> 3
);


if (isset($_POST['xd_check'])) {
	if(!empty($_GET['tab'])) {
		switch($_GET['tab']) {
			case 1:
				$_POST['apercite_size'] = (isset($_POST['apercite_size']) ? $_POST['apercite_size'] : '120x90');
				
				
				$_POST['apercite_workers'] = isset($_POST['apercite_workers']) ? $_POST['apercite_workers'] : array();
				if (isset($_POST['apercite_workers']) && is_array($_POST['apercite_workers'])) {
					$workers = array();
					foreach ($_POST['apercite_workers'] as $k=>$v) {
						$v = trim($v);
						if (!empty($v)) {
							$workers[] = $v;
						}
					}
				}
				else {
					$workers = array();
				}
				
					
				$core->blog->settings->setNameSpace('apercite');
				
				$core->blog->settings->put('apercite_enabled',!empty($_POST['apercite_enabled']),'boolean');
				$core->blog->settings->put('apercite_size',$_POST['apercite_size']);
				$core->blog->settings->put('apercite_local_link',!empty($_POST['apercite_local_link']),'boolean');
				$core->blog->settings->put('apercite_javascript',!empty($_POST['apercite_javascript']),'boolean');
				$core->blog->settings->put('apercite_java',!empty($_POST['apercite_java']),'boolean');
				$core->blog->settings->put('apercite_workers',serialize($workers));
			break;
			
			case 2:
				$_POST['apercite_login'] = (isset($_POST['apercite_login']) ? $_POST['apercite_login'] : '');
				$_POST['apercite_api_key'] = (isset($_POST['apercite_api_key']) ? $_POST['apercite_api_key'] : '');
				
				$core->blog->settings->setNameSpace('apercite');
				$core->blog->settings->put('apercite_login',$_POST['apercite_login']);
				$core->blog->settings->put('apercite_api_key',$_POST['apercite_api_key']);
			break;
		}
	}
	
	http::redirect($p_url.(!empty($_GET['tab']) ? '&tab='.$_GET['tab'] : '').'&up=1');
}

$workers = @unserialize($core->blog->settings->apercite_workers);
if (!$workers) {
	$workers = array();
}
?>
<html>
<head>
  <title><?php echo __('Apercite'); ?></title>
  <?php echo dcPage::jsPageTabs(!empty($_GET['tab']) ? $_GET['tab'] : ''); ?>
</head>

<body>
<?php
echo '<h2>'.__('Apercite').'</h2>';

if (!empty($_GET['up'])) {
	echo '<p class="message">'.__('Apercite success save').'</p>';
}
?>
<div class="multi-part" title="<?php echo __('Apercite overview'); ?>">
	<p><?php echo nl2br(__('Apercite overview txt')); ?></p>
</div>
<div class="multi-part" title="<?php echo __('Apercite configuration'); ?>">
<?php
	echo
	'<form action="'.$p_url.'&tab='.$tabsOrder[__('Apercite configuration')].'" method="post" enctype="multipart/form-data">'.
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
	'<p><input type="submit" value="'.__('Apercite save').'" />'.
	$core->formNonce().'</p>'.
	'</form>';
?>
</div>
<div class="multi-part" title="<?php echo __('Apercite subscription'); ?>">
<?php
	echo
	'<form action="'.$p_url.'&tab='.$tabsOrder[__('Apercite subscription')].'" method="post" enctype="multipart/form-data">';
	
	echo
	'<fieldset><legend>'.__('Apercite member').'</legend>'.
	'<p>'.nl2br(__('Apercite details member')."\n").'</p>'.
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
</div>
<div class="multi-part" title="<?php echo __('Apercite about'); ?>">
	<p><?php echo __('Apercite developed by'); ?> Francis Besset.</p>
	<p><?php echo __('Apercite more informations'); ?> <a href="http://www.apercite.fr<?php echo __("Apercite url"); ?>"><?php echo __("Apercite author website"); ?></a>.</p>
</div>
</body>
</html>