<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of paypalDonation, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

dcPage::check('admin');

$core->blog->settings->addNamespace('paypalDonation');

# Button objet
$paypalDonation = new paypalDonation($core);

$action = isset($_POST['action']) ? $_POST['action'] : '';
$section = isset($_REQUEST['section']) ? $_REQUEST['section'] : '';

# Buttons combos
$combo_buttons = $paypalDonation->getButtons();
$combo_currencies = array();
foreach($paypalDonation->getCurrencies() as $k => $v) {
	$combo_currencies[__($v)] = $k;
}

# Button place (on posts)
$combo_buttonplace = array(
	__('Not on content') => '',
	__('Before content') => 'before',
	__('After content') => 'after'
);

# Button tpl (for post)
$combo_tpltypes = array(
	__('home page') => 'default',
	__('post pages') => 'post',
	__('tags pages') => 'tag',
	__('archives pages') => 'archive',
	__('category pages') => 'category',
	__('entries feed') => 'feed'
);

# Countires combos
$combo_countries = array();
foreach($paypalDonation->getCountries() as $k => $v) {
	$combo_countries[__($v)] = $k;
}

# Settings
$active = (boolean) $core->blog->settings->paypalDonation->active;
$business = (string)  $core->blog->settings->paypalDonation->business;
$page_style = (string)  $core->blog->settings->paypalDonation->page_style;
$return_page = (boolean)  $core->blog->settings->paypalDonation->return_page;
$amount = (string)  $core->blog->settings->paypalDonation->amount;
$item_name = (string)  $core->blog->settings->paypalDonation->item_name;
$item_number = (string)  $core->blog->settings->paypalDonation->item_number;
$currency_code = (string)  $core->blog->settings->paypalDonation->currency_code;
$country_code = (string)  $core->blog->settings->paypalDonation->country_code;
$button_type = (string)  $core->blog->settings->paypalDonation->button_type;
$button_url = (string)  $core->blog->settings->paypalDonation->button_url;
$button_text = (string)  $core->blog->settings->paypalDonation->button_text;
$button_place = (string)  $core->blog->settings->paypalDonation->button_place;
$button_tpl = @unserialize($core->blog->settings->paypalDonation->button_tpl);
if (!is_array($button_tpl)) $button_tpl = array();
$page_title = (string)  $core->blog->settings->paypalDonation->page_title;
$page_content = (string)  $core->blog->settings->paypalDonation->page_content;

if (!$item_name) {
	$item_name = sprintf(__("Donate to '%s'"),html::escapeHTML($core->blog->name));
}
if (!$item_number) {
	$item_number = 'generaldonation';
}
if (!$page_title) {
	$page_title = __('Donation');
}
if (!$page_content) {
	$page_content = __('Thanks you for your donation to our blog.');
}

# Save settings
if ($action == 'savesetting') {
	# Settings
	$active = !empty($_POST['active']);
	$business = $_POST['business'];
	$page_style = $_POST['page_style'];
	$return_page = !empty($_POST['return_page']);
	$amount = $_POST['amount'];
	$item_name = $_POST['item_name'];
	$item_number = $_POST['item_number'];
	$currency_code = $_POST['currency_code'];
	$country_code = $_POST['country_code'];
	$button_type = $_POST['button_type'];
	$button_url = $_POST['button_url'];
	$button_text = $_POST['button_text'];
	$button_place = $_POST['button_place'];
	$button_tpl = $_POST['button_tpl'];
	$page_title = $_POST['page_title'];
	$page_content = $_POST['page_content'];
	
	$core->blog->settings->paypalDonation->put('active',$active);
	$core->blog->settings->paypalDonation->put('business',$business);
	$core->blog->settings->paypalDonation->put('page_style',$page_style);
	$core->blog->settings->paypalDonation->put('return_page',$return_page);
	$core->blog->settings->paypalDonation->put('amount',$amount);
	$core->blog->settings->paypalDonation->put('item_name',$item_name);
	$core->blog->settings->paypalDonation->put('item_number',$item_number);
	$core->blog->settings->paypalDonation->put('currency_code',$currency_code);
	$core->blog->settings->paypalDonation->put('country_code',$country_code);
	$core->blog->settings->paypalDonation->put('button_type',$button_type);
	$core->blog->settings->paypalDonation->put('button_url',$button_url);
	$core->blog->settings->paypalDonation->put('button_text',$button_text);
	$core->blog->settings->paypalDonation->put('button_place',$button_place);
	$core->blog->settings->paypalDonation->put('button_tpl',serialize($button_tpl));
	$core->blog->settings->paypalDonation->put('page_title',$page_title);
	$core->blog->settings->paypalDonation->put('page_content',$page_content);
	
	$core->blog->triggerBlog();
	
	http::redirect('plugin.php?p=paypalDonation&section='.$section.'&upd=1');
}

echo '
<html><head><title>'.__('Paypal donation').'</title>'.
dcPage::jsToolBar().
dcPage::jsModal().
dcPage::jsLoad('index.php?pf=paypalDonation/js/admin.js').
'<script type="text/javascript">'."\n//<![CDATA[\n".
dcPage::jsVar('jcToolsBox.prototype.text_wait',__('Please wait')).
dcPage::jsVar('jcToolsBox.prototype.section',$section).
"\n//]]>\n</script>\n".
'</head>
<body>
<h2>'.$core->blog->name.'
&rsaquo '.__('Paypal donation').'</h2>';

if (!empty($_GET['upd'])) {
	echo '<p class="message">'.__('Configuration successfully saved').'</p>';
}

echo
'<form method="post" action="'.$p_url.'" id="setting-form">

<fieldset id="plugin"><legend>'. __('Plugin activation').'</legend>
<p><label class="classic">'.
form::checkbox(array('active'),'1',$active).' '.
__('Enable extension').'</label></p>
</fieldset>

<fieldset id="account"><legend>'. __('Account').'</legend>
<p><label class="classic">'.__('Your Paypal account ID:').'<br />'.
form::field('business',64,255,$business).'</label></p>
<p class="form-note">'.__('It is your Paypal email address or your Paypal secure merchant account ID.').'</p>
<p><label class="classic">'.__('Currency:').'<br />'.
form::combo(array('currency_code'),$combo_currencies,$currency_code).'</label></p>
<p class="form-note">'.__('It is the currency in which payment will be made.').'</p>
<p><label class="classic">'.__('Default amount:').'<br />'.
form::field('amount',6,6,$amount).'</label></p>
<p class="form-note">'.__('It is the amount of the donation. Leave it empty to let people choose an amount.').'</p>
<p><label class="classic">'.__('Default purpose:').'<br />'.
form::field('item_name',64,255,$item_name).'</label></p>
<p class="form-note">'.__('It is the name of the donation like it will be shown on paypal page.').'</p>
<p><label class="classic">'.__('Default reference:').'<br />'.
form::field('item_number',64,255,$item_number).'</label></p>
<p class="form-note">'.__('It is a reference for the donation in order to help you to manage donations.').'</p>
<p><label class="classic">'.__('Paypal page style:').'<br />'.
form::field('page_style',64,255,$page_style).'</label></p>
<p class="form-note">'.__('You can specify the name of a custom payment page style from your account profile.').'</p>
</fieldset>

<fieldset id="button"><legend>'. __('Button').'</legend>
<p><label class="classic">'.__('Language:').'<br />'.
form::combo(array('country_code'),$combo_countries,$country_code).'</label></p>
<p class="form-note">'.__('It is the language that is used on the button.').'</p>
<p>'.__('Style:').'</p>';

foreach($combo_buttons as $k => $v) {
	echo 
	'<p><label class="classic">'.
	form::radio(array('button_type'),$k,$button_type==$k).' '.
	'<img src="'.$v.'" alt="'.$k.'"/></label></p>';

}
echo '
<p><label class="classic">'.
form::radio(array('button_type'),'custom',$button_type=='custom').' '.
__('Or set the URL of a custom button:').'</label> '.
form::field('button url',64,255,$button_url).'</p>
<p><label class="classic">'.
form::radio(array('button_type'),'none',$button_type=='none').' '.
__('Or use a simple submit button with this text:').'</label> '.
form::field('button text',64,255,$button_text).'</p>
</fieldset>

<fieldset id="entries"><legend>'. __('Entries').'</legend>
<p><label class="classic">'.__('Place of specific button on entries:').'<br />'.
form::combo(array('button_place'),$combo_buttonplace,$button_place).'</label></p>
<p>'.__('Show on:').'</p>';

foreach($combo_tpltypes as $k => $v)
{
	echo '
	<p class="field"><label>'.
	form::checkbox(array('button_tpl[]'),$v,in_array($v,$button_tpl)).
	sprintf(__($k)).'</label></p>';
}
echo '
<p class="form-note">'.__('You can place custom buttons on entries, select here where to place them.').'</p>
</fieldset>

<fieldset id="page"><legend>'. __('Page').'</legend>
<p><label class="classic">'.
form::checkbox(array('return_page'),'1',$return_page).' '.
__('Use a page of thanks').'</label></p>
<p class="form-note">'.__('People who donate can be redirected to a specific page of your blog.').'</p>
<p class="col"><label>'.__('Title:').
form::field('page_title',20,255,html::escapeHTML($page_title),'maximal',2).'
</label></p>
<p class="area"><label for="post_content">'.__('Content:').'</label> '.
form::textarea('page_content',50,$core->auth->getOption('edit_size'),
	html::escapeHTML($page_content),'',2).'
</p>';
if ($active && $return_page) {
	echo 
	'<p><a href="'.$core->blog->url.$core->url->getBase('paypaldonation').
	'" title="'.__('See the thank you page').'">'.
	$core->blog->url.$core->url->getBase('paypaldonation').'</a></p>';
}
echo '
</fieldset>

<div class="clear">
<p><input type="submit" name="save" value="'.__('save').'" />'.
$core->formNonce().
form::hidden(array('p'),'paypalDonation').
form::hidden(array('action'),'savesetting').
form::hidden(array('section'),$section).'
</p></div>
</form>';

dcPage::helpBlock('paypalDonation');

echo '
<hr class="clear"/><p class="right">
paypalDonation - '.$core->plugins->moduleInfo('paypalDonation','version').'&nbsp;
<img alt="'.__('paypalDonation').'" src="index.php?pf=paypalDonation/icon.png" />
</p>
</body>
</html>';
?>