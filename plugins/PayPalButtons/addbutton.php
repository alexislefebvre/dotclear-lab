<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of PayPalButtons, a plugin for Dotclear 2.
#
# Copyright (c) 2011 Philippe aka amalgame
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

dcPage::check('usage,contentadmin');

# Settings

$s =& $core->blog->settings->PayPalButtons;

global $core;

$post_id = (integer) $_GET['post_id'];

$user_lang = $core->auth->getInfo('user_lang');

/* Get button values or set default
-------------------------------------------------------- */

$con =& $core->con;

$query = 'SELECT * FROM '.$core->prefix.'paypal_buttons WHERE post_id ="'.$post_id.'" ';   
$rs = $con->select($query);

if ($_GET['do'] == 'edit') {
	$action = 'update';
	$button_type = $rs->f('button_type');
	$button_enabled = $rs->f('button_enabled');
	$button_size = $rs->f('button_size');
	$hosted_button_id = $rs->f('hosted_button_id');
	
	$page_title = __('Edit button');
	
} elseif ($_GET['do'] == 'add') {
	$action = 'create';
	$button_type = '1';
	$button_enabled = '1';
	$button_size = '1';
	$hosted_button_id = '';
	
	$page_title = __('Add a button to entry');
	
} elseif ($_GET['do'] == 'delete') {
	$query = 'DELETE FROM '.$core->prefix.'paypal_buttons WHERE post_id ="'.$post_id.'" ';   
	$rs = $con->execute($query);
	
	http::redirect(DC_ADMIN_URL.'post.php?id='.$post_id.'#paypal-area');
}


$countries = array(
'fr_FR' => 'fr',
'de_DE' => 'de',
'it_IT' => 'it',
'nl_NL' => 'nl',
'pl_PL' => 'pl',
'es_ES' => 'es',
'en_US' => 'en'
);

$countries_langs = array_flip($countries);

if (array_key_exists($user_lang,$countries_langs)) {
	$button_lang = array_search($user_lang,$countries);
} else {
	$button_lang = 'en_US';
}

$button_types = array(
	'1' => 'buynow',
	'2' => 'donate',
	'3' => 'subscribe',
	'4' => 'gift',
	'5' => 'cart',
	'6' => 'saved'
);

$button_names = array(
	'1' => __('Buy now'),
	'2' => __('Donate'),
	'3' => __('Subscribe'),
	'4' => __('Buy gift certificate'),
	'5' => __('Add to cart'),
);

$button_sizes = array(
	'1' => 'SM',
	'2' => 'LG',
	'3' => 'CC_LG'
);

$button_sizes_names = array(
	'1' => __('Small'),
	'2' => __('Large'),
	'3' => __('Large with Credit Cards')
);

/* Process posted values
-------------------------------------------------------- */

if ($_POST) {
	$post_id = (integer) $_POST['post_id'];
	
	if (isset($_POST['cancel'])) {
	
		http::redirect(DC_ADMIN_URL.'post.php?id='.$post_id.'#paypal-area');
	
	} else {
		global $core;
		$con =& $core->con;
		$cur = $con->openCursor($core->prefix.'paypal_buttons');
		
		$button_enabled = '1';
		$button_type = $_POST['PayPalButton_button_type'];
		$button_size = $_POST['PayPalButton_button_size'];
		$hosted_button_id = $_POST['PayPalButton_hosted_button_id'];
		
		$cur->post_id = $post_id;
		$cur->button_enabled = $button_enabled;
		$cur->button_type = $button_type;
		$cur->button_size = $button_size;
		$cur->hosted_button_id = $hosted_button_id;
		
		
		if (isset($_POST['create'])) {						
			$cur->insert();
			
			http::redirect(DC_ADMIN_URL.'post.php?id='.$post_id.'#paypal-area');
		} elseif (isset($_POST['update'])) {
			$cur->update('WHERE post_id = '.$post_id.'');
			
			http::redirect(DC_ADMIN_URL.'post.php?id='.$post_id.'#paypal-area');
		}
	}
}

/* DISPLAY
-------------------------------------------------------- */
?>
<html>
	<head>
		<title><?php echo $page_title.' - '.__('PayPal buttons'); ?></title>
		<?php
		echo
		dcPage::jsToolMan().
		dcPage::jsLoad('index.php?pf=PayPalButtons/js/_post.js');
		?>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>
	</head>
	<body>
<?php


	
echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; <a href="'.$p_url.'">'.__('PayPal buttons').'</a> &rsaquo; <span class="page-title">'.$page_title.'</span></h2>';

echo
	'<form action="'.$p_url.'" method="post" id="addbutton">'.
		'<fieldset><legend>'.__('Button type').'</legend>'.
			'<div class="two-cols">
				<div class="col">'.
				
					'<p><label for="PayPalButton_button_type-1" class="classic">'.
					form::radio(array('PayPalButton_button_type','PayPalButton_button_type-1'),'1',$button_type == '1', true).
					__('Buy now').'</label></p>'.
					'<p><label for="PayPalButton_button_type-2" class="classic">'.
					form::radio(array('PayPalButton_button_type','PayPalButton_button_type-2'),'2',$button_type == '2', false).
					__('Donate').'</label></p>'.
					'<p><label for="PayPalButton_button_type-3" class="classic">'.
					form::radio(array('PayPalButton_button_type','PayPalButton_button_type-3'),'3',$button_type == '3', false).
					__('Subscribe').'</label></p>'.
					'<p><label for="PayPalButton_button_type-4" class="classic">'.
					form::radio(array('PayPalButton_button_type','PayPalButton_button_type-4'),'4',$button_type == '4', false).
					__('Buy gift certificate').'</label></p>'.						
					'<p><label for="PayPalButton_button_type-5" class="classic">'.
					form::radio(array('PayPalButton_button_type','PayPalButton_button_type-5'),'5',$button_type == '5', false).
					__('Add to cart').'</label></p>'.
					'<p><label for="PayPalButton_button_type-6" class="classic">'.
					form::radio(array('PayPalButton_button_type','PayPalButton_button_type-6'),'6',$button_type == '6', false).
					__('Button saved in your PayPal account').'</label></p>'.
					'<p><label for="PayPalButton_hosted_button_id" style="margin-left:1.5em">'.__('Button ID:').'&nbsp;'.
					form::field('PayPalButton_hosted_button_id',30,255,$hosted_button_id).'</label></p>'.
				'</div>'.
				'<div class="col">';
					
				if ($button_type == '5') {
					echo
					'<p><label for="PayPalButton_button_size-1" class="classic">'.
					form::radio(array('PayPalButton_button_size','PayPalButton_button_size-1'),'1',$button_size == '1', true).''.
					'<img id="first-image" style="vertical-align:text-top" src="https://www.paypal.com/'.$button_lang.'/i/btn/btn_'.$button_types[$button_type].'_SM.gif" alt="'.$button_names[$button_type].' - '.__('Small').'" /></label></p>'.
					'<p><label for="PayPalButton_button_size-2" class="classic">'.
					form::radio(array('PayPalButton_button_size','PayPalButton_button_size-2'),'2',$button_size == '2', false).''.
					'<img id="second-image" style="vertical-align:text-top" src="https://www.paypal.com/'.$button_lang.'/i/btn/btn_'.$button_types[$button_type].'_LG.gif" alt="'.$button_names[$button_type].' - '.__('Large').'" /></label></p>'.
					'<p style="display:none"><label for="PayPalButton_button_size-3" class="classic">'.
					form::radio(array('PayPalButton_button_size','PayPalButton_button_size-3'),'3',$button_size == '3', false).''.
					'<img id="third-image" style="vertical-align:text-top" src="index.php?pf=PayPalButtons/no-preview.png" alt="" /></label></p>';
				} elseif ($button_type == '6') {
					echo
					'<p style="display:none"><label for="PayPalButton_button_size-1" class="classic">'.
					form::radio(array('PayPalButton_button_size','PayPalButton_button_size-1'),'1',$button_size == '1', true).''.
					'<img id="first-image" style="vertical-align:text-top" src="index.php?pf=PayPalButtons/no-preview.png" alt="" /></label></p>'.
					'<p style="display:none"><label for="PayPalButton_button_size-2" class="classic">'.
					form::radio(array('PayPalButton_button_size','PayPalButton_button_size-2'),'2',$button_size == '2', false).''.
					'<img id="second-image" style="vertical-align:text-top" src="index.php?pf=PayPalButtons/no-preview.png" alt="" /></label></p>'.
					'<p style="display:none"><label for="PayPalButton_button_size-3" class="classic">'.
					form::radio(array('PayPalButton_button_size','PayPalButton_button_size-3'),'3',$button_size == '3', false).''.
					'<img id="third-image" style="vertical-align:text-top" src="index.php?pf=PayPalButtons/no-preview.png" alt="" /></label></p>';
				} elseif (($button_type == '1') || ($button_type == '2') ||($button_type == '3') ||($button_type == '4')) {
					echo
					'<p><label for="PayPalButton_button_size-1" class="classic">'.
					form::radio(array('PayPalButton_button_size','PayPalButton_button_size-1'),'1',$button_size == '1', true).''.
					'<img id="first-image" style="vertical-align:text-top" src="https://www.paypal.com/'.$button_lang.'/i/btn/btn_'.$button_types[$button_type].'_SM.gif" alt="'.$button_names[$button_type].' - '.__('Small').'" /></label></p>'.
					'<p><label for="PayPalButton_button_size-2" class="classic">'.
					form::radio(array('PayPalButton_button_size','PayPalButton_button_size-2'),'2',$button_size == '2', false).''.
					'<img id="second-image" style="vertical-align:text-top" src="https://www.paypal.com/'.$button_lang.'/i/btn/btn_'.$button_types[$button_type].'_LG.gif" alt="'.$button_names[$button_type].' - '.__('Large').'" /></label></p>'.
					'<p><label for="PayPalButton_button_size-3" class="classic">'.
					form::radio(array('PayPalButton_button_size','PayPalButton_button_size-3'),'3',$button_size == '3', false).''.
					'<img id="third-image" style="vertical-align:text-top" src="https://www.paypal.com/'.$button_lang.'/i/btn/btn_'.$button_types[$button_type].'CC_LG.gif" alt="'.$button_names[$button_type].' - '.__('Large with Credit Cards').'" /></label></p>';
				}
				echo
					
				'</div>'.
			'</div>'.
		'</fieldset>'.
		
		'<fieldset><legend>'.__('Button options').'</legend>'.
	
		'</fieldset>'.
		$core->formNonce().
		'<p>'.form::hidden('post_id', $post_id).
		form::hidden('button_enabled',$button_enabled).
		form::hidden('button_lang',$button_lang).
		form::hidden('button_type',$button_type).
		form::hidden('button_names',__('Buy now').','.__('Donate').','.__('Subscribe').','.__('Buy gift certificate').','.__('Add to cart')).
		form::hidden('button_sizes_names',__('Small').','.__('Large').','.__('Large with Credit Cards')).
		form::hidden('button_size',$button_size).
		form::hidden('hosted_button_id',$hosted_button_id).
		'<input type="submit" value="'.__('Save').'" name="'.$action.'" />&nbsp;<input type="submit" class="reset" value="'.__('Cancel').'" name="cancel" /></p>'.
	'</form>';

dcPage::helpBlock('PayPalButtonsAdd');
?>
	</body>
</html>