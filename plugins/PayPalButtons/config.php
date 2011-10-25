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

if (!$core->auth->check('admin',$core->blog->id)) { return; }

/* Settings
-------------------------------------------------------- */

$s =& $core->blog->settings->PayPalButtons;

$p_url = 'plugin.php?p='.basename(dirname(__FILE__));

$default_tab = isset($_GET['tab']) ? $_GET['tab'] : 'config';

/* Save configurations
-------------------------------------------------------- */

if (isset($_POST['save']))
{
	$type = $_POST['type'];
	
	$core->blog->triggerBlog();
	
	if ($type === 'config')
	{
		$s->put('PayPalButtons_enabled',!empty($_POST['PayPalButtons_enabled']));
		$s->put('PayPalButtons_testing',!empty($_POST['PayPalButtons_testing']));
		$s->put('PayPalButtons_testing_account',$_POST['PayPalButtons_testing_account']);
		$s->put('PayPalButtons_selling_account',$_POST['PayPalButtons_selling_account']);
		$s->put('PayPalButtons_currency_code',$_POST['PayPalButtons_currency_code']);
		
		http::redirect($p_url.'&tab=config&upd=1');

	} elseif ($type === 'pages') {
		$s->put('PayPalButtons_cbt',$_POST['PayPalButtons_cbt']);
		$s->put('PayPalButtons_cn',$_POST['PayPalButtons_cn']);
		$s->put('PayPalButtons_no_note',!empty($_POST['PayPalButtons_no_note']));
		$s->put('PayPalButtons_no_shipping',$_POST['PayPalButtons_no_shipping']);
		$s->put('PayPalButtons_image_url',$_POST['PayPalButtons_image_url']);
		$s->put('PayPalButtons_cpp_header_image',$_POST['PayPalButtons_cpp_header_image']);
		$s->put('PayPalButtons_cpp_headerborder_color',$_POST['PayPalButtons_cpp_headerborder_color']);
		$s->put('PayPalButtons_cpp_payflow_color',$_POST['PayPalButtons_cpp_payflow_color']);
		$s->put('PayPalButtons_cs',!empty($_POST['PayPalButtons_cs']));
		$s->put('PayPalButtons_page_style',$_POST['PayPalButtons_page_style']);
		$s->put('PayPalButtons_return_url',$_POST['PayPalButtons_return_url']);
		$s->put('PayPalButtons_cancel_return_url',$_POST['PayPalButtons_cancel_return_url']);
		$s->put('PayPalButtons_return_method',$_POST['PayPalButtons_return_method']);
		
		http::redirect($p_url.'&tab=pages&upd=2');
	
	} elseif ($type === 'options') {
		$s->put('PayPalButtons_ssl_enabled',!empty($_POST['PayPalButtons_ssl_enabled']));
		$s->put('PayPalButtons_pdt_enabled',!empty($_POST['PayPalButtons_pdt_enabled']));
		$s->put('PayPalButtons_ipn_enabled',!empty($_POST['PayPalButtons_ipn_enabled']));
		$s->put('PayPalButtons_auth_token',$_POST['PayPalButtons_auth_token']);
		$s->put('PayPalButtons_OpenSSL_path',$_POST['PayPalButtons_OpenSSL_path']);
		$s->put('PayPalButtons_certificate_ID',$_POST['PayPalButtons_certificate_ID']);
		
		http::redirect($p_url.'&tab=options&upd=3');
		
	}
	

}

/* Parameters
-------------------------------------------------------- */

$currencies = array(
'AUD' => 'Australian Dollars',
'CAD' => 'Canadian Dollars',
'EUR' => 'Euros',
'GBP' => 'Pounds Sterling',
'JPY' => 'Yen',
'USD' => 'U.S. Dollars',
'NZD' => 'New Zealand Dollar',
'CHF' => 'Swiss Franc',
'HKD' => 'Hong Kong Dollar',
'SGD' => 'Singapore Dollar',
'SEK' => 'Swedish Krona',
'DKK' => 'Danish Krone',
'PLN' => 'Polish Zloty',
'NOK' => 'Norwegian Krone',
'HUF' => 'Hungarian Forint',
'CZK' => 'Czech Koruna',
'ILS' => 'Israeli Shekel',
'MXN' => 'Mexican Peso'
);

$currencies = array_flip($currencies);

$shippings = array(
'0' => __("Customer is prompted to include a shipping address"),
'1' => __("Customer is not asked for a shipping address"),
'2' => __("Customer must provide a shipping address")
);

$shippings = array_flip($shippings);

$return_methods = array(
'0' => __("GET: Default or shopping cart transactions"),
'1' => __("GET and no transaction variables are posted"),
'2' => __("POST and all transaction variables are posted")
);

$return_methods = array_flip($return_methods);

/* DISPLAY
-------------------------------------------------------- */
?>
<html>
<head>
	<title><?php echo(__('PayPalButtons')); ?></title>
	
	<?php echo dcPage::jsPageTabs($default_tab).
	dcPage::jsConfirmClose('config-form').
	dcPage::jsConfirmClose('pages-form').
	dcPage::jsConfirmClose('options-form')
	; ?>
	
</head>
<body>

<?php

/* Display messages
-------------------------------------------------------- */

if (isset($_GET['upd']))
{
	$p_msg = '<p class="message">%s</p>';
	
	$a_msg = array(
		__('Configuration has been successfully saved.'),
		__('PayPal pages options have been successfully saved.'),
		__('Advanced options have been successfully saved.')
		
	);
	
	$k = (integer) $_GET['upd']-1;
	
	if (array_key_exists($k,$a_msg)) {
		echo sprintf($p_msg,$a_msg[$k]);
	}
}


echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; <span class="page-title">'.__('PayPal buttons').'</span></h2>';

/* Config tab
-------------------------------------------------------- */

echo
'<div class="multi-part" id="config" title="'.__('Configuration').'">'.
	'<form action="'.$p_url.'" method="post" id="config-form">'.
	
		'<fieldset><legend>'.__('Activation').'</legend>'.
			'<p><label class="classic" for="PayPalButtons_enabled">'.
			form::checkbox('PayPalButtons_enabled','1',$s->PayPalButtons_enabled).
			__('Enable plugin').'</label></p>'.
			'<p><label class="classic" for="PayPalButtons_testing">'.
			form::checkbox('PayPalButtons_testing','1',$s->PayPalButtons_testing).
			__('Use PayPal Sandbox for transactions (testing)').'</label></p>'.
		'</fieldset>'.
		
		'<fieldset><legend>'.__('PayPal account').'</legend>'.
			'<div class="two-cols">
				<div class="col">'.
					'<p><label for="PayPalButtons_testing_account">'.__('Testing account login:').
					form::field('PayPalButtons_testing_account',60,255,$s->PayPalButtons_testing_account).
					'</label></p>'.
					'<p class="form-note">'.__('Your testing business account email in PayPal Sandbox').'</p>'.
					'<p><label class="required" for="PayPalButtons_selling_account">'.__('Selling account login:').
					form::field('PayPalButtons_selling_account',60,255,$s->PayPalButtons_selling_account).
					'</label></p>'.
					'<p class="form-note">'.__('Your PayPal account email address or your Paypal secure merchant account ID').'</p>'.
				'</div>'.
				'<div class="col">'.
					'<p><label for="PayPalButtons_currency_code">'.__('Currency:').
					form::combo(array('PayPalButtons_currency_code','PayPalButtons_currency_code'),$currencies,$s->PayPalButtons_currency_code).'</label></p>'.
					'<p class="form-note">'.__('Payments to your account will be made in this currency').'</p>'.
				'</div>'.
			'</div>'.	
		'</fieldset>'.
		
		
		'<p>'.form::hidden(array('type'),'config').'</p>'.
		'<p class="clear"><input type="submit" name="save" value="'.__('Save').'" />'.$core->formNonce().'</p>'.
	'</form>'.
'</div>';

/* Advanced options tab
-------------------------------------------------------- */

echo
'<div class="multi-part" id="options" title="'.__('Advanced options').'">'.
	'<form action="'.$p_url.'" method="post" id="options-form">'.
		'<fieldset><legend>'.__('SSL buttons crypting').'</legend>'.
			'<p class="form-note info">'.__('Note : to use encrypted buttons, you must be able to connect to your server using SSH protocol, and generate your certificates.').'</p>'.
			'<p><label class="classic" for="PayPalButtons_ssl_enabled">'.
			form::checkbox('PayPalButtons_ssl_enabled','1',$s->PayPalButtons_ssl_enabled).
			__('Activate').'</label></p>'.
			
			'<p><label for="PayPalButtons_OpenSSL_path">'.__('Path to OpenSSL on your server:').
			form::field('PayPalButtons_OpenSSL_path',50,255,$s->PayPalButtons_OpenSSL_path).
			'</label></p>'.
			'<p class="form-note">'.__('example: /usr/bin/openssl').'</p>'.
			'<p><label for="PayPalButtons_certificate_ID">'.__('ID of your PayPal certificate:').
			form::field('PayPalButtons_certificate_ID',50,255,$s->PayPalButtons_certificate_ID).
			'</label></p>'.
			'<p class="form-note">'.__('This ID is displayed in your PayPal account once you have submitted your public certificate').'</p>'.
		'</fieldset>'.
		
		'<fieldset><legend>'.__('Instant Payment Notifications').'</legend>'.
			'<p><label class="classic" for="PayPalButtons_ipn_enabled">'.
			form::checkbox('PayPalButtons_ipn_enabled','1',$s->PayPalButtons_ipn_enabled).
			__('Activate').'</label></p>'.
			'<p>'.__('Notification URL:').' <strong>'.$core->blog->url.$core->url->getBase('paypal').'/notify</strong></p>'.
		'</fieldset>'.
		
		'<fieldset><legend>'.__('Payment Data Transfer').'</legend>'.
			'<p><label class="classic" for="PayPalButtons_pdt_enabled">'.
			form::checkbox('PayPalButtons_pdt_enabled','1',$s->PayPalButtons_pdt_enabled).
			__('Activate').'</label></p>'.			
			'<p><label for="PayPalButtons_auth_token">'.__('Your PayPal authorization token:').
			form::field('PayPalButtons_auth_token',80,255,$s->PayPalButtons_auth_token).
			'</label></p>'.
			'<p class="form-note">'.__('This token is displayed in your PayPal account once you have activated Payment Data Transfer').'</p>'.
		'</fieldset>'.
		'<p>'.form::hidden(array('type'),'options').'</p>'.
		'<p class="clear"><input type="submit" name="save" value="'.__('Save').'" />'.$core->formNonce().'</p>'.
	'</form>'.
'</div>';

/* Payment pages tab
-------------------------------------------------------- */

echo
'<div class="multi-part" id="pages" title="'.__('Options for PayPal pages').'">'.
	'<form action="'.$p_url.'" method="post" id="pages-form">'.		
		'<fieldset><legend>'.__('Display options').'</legend>'.
			'<div class="two-cols">
				<div class="col">'.
					'<p><label for="PayPalButtons_cn">'.__('Note field label:').
					form::field('PayPalButtons_cn',60,40,$s->PayPalButtons_cn).
					'</label></p>'.
					'<p class="form-note">'.__('Title for the Note field on the PayPal Payment page.').'</p>'.
					'<p><label class="classic" for="PayPalButtons_no_note">'.
					form::checkbox('PayPalButtons_no_note','1',$s->PayPalButtons_no_note).
					__('Notes are not allowed').'</label></p>'.
				'</div>'.
				'<div class="col">'.	
					'<p><label for="PayPalButtons_cbt">'.__('Continue button text:').
					form::field('PayPalButtons_cbt',60,60,$s->PayPalButtons_cbt).
					'</label></p>'.
					'<p class="form-note">'.__('Text for the Continue button on the PayPal Payment Complete page.').'</p>'.
					'<p><label for="PayPalButtons_no_shipping">'.__('Shipping address options:').
					form::combo(array('PayPalButtons_no_shipping','PayPalButtons_no_shipping'),$shippings,$s->PayPalButtons_no_shipping).'</label></p>'.
				'</div>'.
			'</div>'.
		'</fieldset>'.
		
		'<fieldset><legend>'.__('Presentation options').'</legend>'.
			'<p class="clear form-note info">'.__('Note: other options will be ignored if a custom saved PayPal style is defined.').'</p>'.
			
			
			'<div class="two-cols">
				<div class="col">'.
					'<p><label for="PayPalButtons_page_style">'.__('Page style:').
					form::field('PayPalButtons_page_style',30,30,$s->PayPalButtons_page_style).
					'</label></p>'.
					'<p class="form-note">'.__('Custom PayPal style name').'</p>'.
					'<p>'.__('PayPal page background color:').'</p>'.
					'<p><label style="margin-left:1em" for="PayPalButtons_cs-1" class="classic">'.
					form::radio(array('PayPalButtons_cs','PayPalButtons_cs-1'),'0',$s->PayPalButtons_cs == '0').
					__('White').'</label>'.
					'<label style="margin-left:2em" for="PayPalButtons_cs-2" class="classic">'.
					form::radio(array('PayPalButtons_cs','PayPalButtons_cs-2'),'1',$s->PayPalButtons_cs == '1').
					__('Black').'</label></p>'.
					'<p><label for="PayPalButtons_cpp_headerborder_color">'.__('Header border color:').
					form::field('PayPalButtons_cpp_headerborder_color',6,6,$s->PayPalButtons_cpp_headerborder_color).
					'</label></p>'.
					'<p class="form-note">'.__('Six-character HTML hexadecimal color code in ASCII').'</p>'.
					'<p><label for="PayPalButtons_cpp_payflow_color">'.__('Payment zone background color:').
					form::field('PayPalButtons_cpp_payflow_color',6,6,$s->PayPalButtons_cpp_payflow_color).
					'</label></p>'.
					'<p class="form-note">'.__('Six-character HTML hexadecimal color code in ASCII').'</p>'.
				'</div>'.
				'<div class="col">'.
					'<p><label for="PayPalButtons_image_url">'.__('Logo:').
					form::field('PayPalButtons_image_url',80,255,$s->PayPalButtons_image_url).
					'</label></p>'.
					'<p class="form-note">'.__('URL of custom logo image (150*50 px). Only secure server (https).').'</p>'.
					'<p><label for="PayPalButtons_cpp_header_image">'.__('Header image:').
					form::field('PayPalButtons_cpp_header_image',80,255,$s->PayPalButtons_cpp_header_image).
					'</label></p>'.
					'<p class="form-note">'.__('URL of custom header image (750*90 px). Only secure server (https).').'</p>'.
				'</div>'.
			'</div>'.
		'</fieldset>'.
		
		'<fieldset><legend>'.__('Redirect options').'</legend>'.
			'<p class="clear form-note info">'.__('Note: these options will be ignored if you activate instant payment notification (IPN) in advanced options.').'</p>'.
			'<div class="two-cols">
				<div class="col">'.					
					'<p><label for="PayPalButtons_return_url">'.__('Return URL:').
					form::field('PayPalButtons_return_url',60,255,$s->PayPalButtons_return_url).
					'</label></p>'.
					'<p class="form-note">'.__('The URL to which the customer is returned after completing the payment (default : PayPal website)').'</p>'.
					'<p><label for="PayPalButtons_cancel_return_url">'.__('Cancel URL:').
					form::field('PayPalButtons_cancel_return_url',60,255,$s->PayPalButtons_cancel_return_url).
					'</label></p>'.
					'<p class="form-note">'.__('The URL to which the customer is returned if payment is canceled (default : PayPal website)').'</p>'.
				'</div>'.
				'<div class="col">'.
					'<p><label for="PayPalButtons_return_method">'.__('Form method used to send data to the URL specified by the return variable:').
					form::combo(array('PayPalButtons_return_method','PayPalButton_return_method'),$return_methods,$s->PayPalButtons_return_method).'</label></p>'.
					
				'</div>'.
			'</div>'.
		'</fieldset>'.
		'<p>'.form::hidden(array('type'),'pages').'</p>'.
		'<p class="clear"><input type="submit" name="save" value="'.__('Save').'" />'.$core->formNonce().'</p>'.
	'</form>'.
'</div>';

dcPage::helpBlock('PayPalButtons');

?>

</body>
</html>