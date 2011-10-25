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

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

/* Check version
-------------------------------------------------------- */

$m_version = $core->plugins->moduleInfo('PayPalButtons','version');
 
$i_version = $core->getVersion('PayPalButtons');
 
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

/* Settings
-------------------------------------------------------- */
$core->blog->settings->addNamespace('PayPalButtons');
$s =& $core->blog->settings->PayPalButtons;


if (!$s->PayPalButtons_files_path) {
	$public_path = $core->blog->public_path;
	$paypalbuttons_files_path = $public_path.'/paypalbuttons';
	if (is_dir($paypalbuttons_files_path)) {
		if (!is_readable($paypalbuttons_files_path) || !is_writable($paypalbuttons_files_path)) {
			throw new Exception(__('Directory for files repository needs to allow read and write access.'));
		}
	}
	else {
		try {
			files::makeDir($paypalbuttons_files_path);
		}
		catch (Exception $e) {
			throw $e;
		}
	}
	if (!is_file($paypalbuttons_files_path.'/.htaccess')) {
		try {
			file_put_contents($paypalbuttons_files_path.'/.htaccess',"<FilesMatch \"\.(pem)$\">\n order deny,allow\n deny from all\n</FilesMatch>");
		}
		catch (Exception $e) {}
	}
	$s->put('paypalbuttons_files_path',$paypalbuttons_files_path, 'string', 'PayPal Buttons files repository',true);
}

$s->put('PayPalButtons_enabled',false,'boolean','Enable PayPalButtons plugin',false,true);
$s->put('PayPalButtons_testing',true,'boolean','Use PayPal Sandbox',false,true);
$s->put('PayPalButtons_testing_account','','string','PayPal Sandbox testing account',false,true);
$s->put('PayPalButtons_selling_account','','string','PayPal selling account',false,true);
$s->put('PayPalButtons_currency_code','EUR','string','PayPal currency code',false,true);
$s->put('PayPalButtons_cbt','','string','PayPal Continue button text',false,true);
$s->put('PayPalButtons_cn','','string','PayPal note label',false,true);
$s->put('PayPalButtons_no_note',false,'boolean','No note on PayPal payment page',false,true);
$s->put('PayPalButtons_no_shipping','0','integer','Shipping options',false,true);
$s->put('PayPalButtons_image_url','','string','PayPal page logo image',false,true);
$s->put('PayPalButtons_cpp_header_image','','string','PayPal page header image',false,true);
$s->put('PayPalButtons_cpp_headerborder_color','','string','PayPal page header border',false,true);
$s->put('PayPalButtons_cpp_payflow_color','','string','PayPal page payment zone background color',false,true);
$s->put('PayPalButtons_cs','0','boolean','PayPal page background color',false,true);
$s->put('PayPalButtons_page_style','','string','PayPal payment page style',false,true);
$s->put('PayPalButtons_return_url','','string','PayPal payment return URL',false,true);
$s->put('PayPalButtons_cancel_return_url','','string','PayPal payment cancel URL',false,true);
$s->put('PayPalButtons_return_method','0','integer','Return form method',false,true);

$s->put('PayPalButtons_ssl_enabled',false,'boolean','Enable buttons SSL crypting',false,true);
$s->put('PayPalButtons_OpenSSL_path','','string','OpenSSL path on server',false,true);
$s->put('PayPalButtons_pdt_enabled',false,'boolean','Enable PDT treatment',false,true);
$s->put('PayPalButtons_ipn_enabled',false,'boolean','Enable IPN treatment',false,true);
$s->put('PayPalButtons_certificate_ID','','string','ID of your PayPal public certificate',false,true);
$s->put('PayPalButtons_auth_token','','string','PayPal authorization token',false,true);

/* Database connection
-------------------------------------------------------- */
	
global $core;
$con =& $core->con;

/* Database schema : create tables
-------------------------------------------------------- */

$_s = new dbStruct($core->con,$core->prefix);

$_s->paypal_buttons
	->post_id('bigint',0,false)
	->button_enabled('smallint',0,false,0)
	->button_type('integer',0,false,1)
	->button_size('integer',0,false,1)
	->hosted_button_id('text',255,false)
	->bn('text',255,false)
	->amount('real',10,false)
	->discount_amount('real',10,false)
	->discount_amount2('real',10,false)
	->discount_rate('smallint',0,false)
	->discount_rate2('smallint',0,false)
	->discount_num('smallint',0,false)
	->item_name('text',127,false)
	->item_number('text',127,false)
	->quantity('integer',0,false)
	->shipping('real',10,false)
	->shipping2('real',10,false)
	->tax('real',10,false)
	->tax_rate('smallint',0,false)
	->undefined_quantity('smallint',0,false)
	->weight('real',10,false)
	->weight_unit('varchar',20,false)
	->on0('text',64,false)
	->on1('text',64,false)
	->os0('text',64,false)
	->os1('text',64,false)	
	
	->primary('pk_paypal_buttons','post_id')
	;
	
	$_s->paypal_buttons->reference('fk_paypal_buttons','post_id','post','post_id','cascade','cascade');

$_s->paypal_cart_info
	->txnid('varchar',30,false,'""')
	->itemname('varchar',255,false,'""')
	->itemnumber('varchar',50,true,null)
	->os0('varchar',20,true,null)
	->on0('varchar',50,true,null)
	->os1('varchar',20,true,null)
	->on1('varchar',50,true,null)
	->quantity('char',3,false,'""')
	->invoice('varchar',255,false,'""')
	->custom('varchar',255,false,'""')
	->blog_id('varchar',32,false)
	;
	
	$_s->paypal_cart_info->reference('fk_paypal_cart_info','blog_id','blog','blog_id','cascade','cascade');

$_s->paypal_subscription_info
	->subscr_id('varchar',255,false,'""')
	->sub_event('varchar',50,false,'""')
	->subscr_date('varchar',255,false,'""')
	->subscr_effective('varchar',255,false,'""')
	->period1('varchar',255,false,'""')
	->period2('varchar',255,false,'""')
	->period3('varchar',255,false,'""')
	->amount1('varchar',255,false,'""')
	->amount2('varchar',255,false,'""')
	->amount3('varchar',255,false,'""')
	->mc_amount1('varchar',255,false,'""')
	->mc_amount2('varchar',255,false,'""')
	->mc_amount3('varchar',255,false,'""')
	->recurring('varchar',255,false,'""')
	->reattempt('varchar',255,false,'""')
	->retry_at('varchar',255,false,'""')
	->recur_times('varchar',255,false,'""')
	->username('varchar',255,false,'""')
	->password('varchar',255,true,null)
	->payment_txn_id('varchar',50,false,'""')
	->subscriber_emailaddress('varchar',255,false,'""')
	->datecreation('date',false,'0000-00-00')
	->blog_id('varchar',32,false)
	;
	
	$_s->paypal_subscription_info->reference('fk_paypal_subscription_info','blog_id','blog','blog_id','cascade','cascade');
	
$_s->paypal_payment_info
	->firstname('varchar',100,false,'""')
	->lastname('varchar',100,false,'""')
	->buyer_email('varchar',100,false,'""')
	->street('varchar',100,false,'""')
	->city('varchar',50,false,'""')
	->state('char',3,false,'""')
	->zipcode('varchar',11,false,'""')
	->memo('varchar',255,true,null)
	->itemname('varchar',255,true,null)
	->itemnumber('varchar',255,true,null)
	->os0('varchar',20,true,null)
	->on0('varchar',50,true,null)
	->os1('varchar',20,true,null)
	->on1('varchar',50,true,null)
	->quantity('char',3,false,'""')
	->paymentdate('varchar',50,false,'""')
	->paymenttype('varchar',10,false,'""')
	->txnid('varchar',30,false,'""')
	->mc_gross('varchar',6,false,'""')
	->mc_fee('varchar',5,false,'""')
	->paymentstatus('varchar',15,false,'""')
	->pendingreason('varchar',50,true,null)
	->txntype('varchar',10,false,'""')
	->tax('varchar',10,true,null)
	->mc_currency('varchar',5,false,'""')
	->reasoncode('varchar',20,false,'""')
	->custom('varchar',255,false,'""')
	->country('varchar',20,false,'""')
	->datecreation('date',false,'0000-00-00')
	->blog_id('varchar',32,false)
	;
	
	$_s->paypal_payment_info->reference('fk_paypal_payment_info','blog_id','blog','blog_id','cascade','cascade');

/* Schema installation finished
-------------------------------------------------------- */

$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($_s);

/* Installation completed
-------------------------------------------------------- */

$core->setVersion('PayPalButtons',$m_version);
return true;

?>