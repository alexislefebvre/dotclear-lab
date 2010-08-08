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

# Namespace for settings
$core->blog->settings->addNamespace('paypalDonation');

require_once dirname(__FILE__).'/_widgets.php';

if ($core->auth->check('admin',$core->blog->id)
 && $core->blog->settings->paypalDonation->active
 && $core->blog->settings->paypalDonation->business) {
	$core->addBehavior('adminPostHeaders',array('adminPaypalDonation','header'));
	$core->addBehavior('adminPostFormSidebar',array('adminPaypalDonation','sidebar'));
	$core->addBehavior('adminAfterPostCreate',array('adminPaypalDonation','save'));
	$core->addBehavior('adminAfterPostUpdate',array('adminPaypalDonation','save'));
}
$core->addBehavior('adminBeforePostDelete',array('adminPaypalDonation','delete'));

# Plugin menu
$_menu['Plugins']->addItem(
	__('Paypal donation'),
	'plugin.php?p=paypalDonation','index.php?pf=paypalDonation/icon.png',
	preg_match('/plugin.php\?p=paypalDonation(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id)
);

class adminPaypalDonation
{
	public static function header()
	{
		return
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		'$(function(){'.
		'$("#paypaldonation-form-title").toggleWithLegend('.
		 '$("#paypaldonation-form-content"),{'.
		 'cookie:"dcx_paypaldonation_admin_form_sidebar"});'.
		"});\n".
		"\n//]]>\n".
		"</script>\n";
	}
	
	public static function sidebar($post)
	{
		global $core;
		
		$post_id = $post ? $post->post_id : 0;
		$ppd = new paypalDonation($core);
		$res = $ppd->getPostInfo($post_id);
		
		if (!$res['use']) {
			$res['item_name'] = '%T';
			$res['item_number'] = 'b%B-p%I';
		}
		
		if (!empty($_POST['ppd_use'])) {
			$res['use'] = true;
		}
		if (!empty($_POST['ppd_item_name'])) {
			$res['item_name'] = $_POST['ppd_item_name'];
		}
		if (!empty($_POST['ppd_item_number'])) {
			$res['item_number'] = $_POST['ppd_item_number'];
		}
		if (!empty($_POST['ppd_amount'])) {
			$res['amount'] = $_POST['ppd_amount'];
		}
		
		echo 
		'<h3 id="paypaldonation-form-title">'.__('Paypal donation:').'</h3>'.
		'<div id="paypaldonation-form-content">'.
		'<p><label class="classic">'.form::checkbox('ppd_use',1,$res['use'],'',3).' '.
		__('Add special Paypal donation').'</label></p>'.
		'<p><label>'.__('Purpose:').
		form::field('ppd_item_name',32,255,$res['item_name'],'maximal',3).
		'</label></p>'.
		'<p class="form-note">'.__('Use %T for post title').'</p>'.
		'<p><label>'.__('Reference:').
		form::field('ppd_item_number',32,255,$res['item_number'],'maximal',3).
		'</label></p>'.
		'<p class="form-note">'.__('Use %I for post id and %B for blog id').'</p>'.
		'<p><label>'.__('Amount:').
		form::field('ppd_amount',32,4,$res['amount'],'maximal',3).
		'</label></p>'.
		'<p class="form-note">'.sprintf(__('Amount is in %s'),
			$core->blog->settings->paypalDonation->currency_code).'</p>'.
		'</div>';
	}
	
	public static function save($cur,$post_id)
	{
		$ppd = new paypalDonation($GLOBALS['core']);
		$ppd->delPostInfo($post_id);
		
		if (!isset($_POST['ppd_use'])) {
			return;
		}
		
		$item_name = !empty($_POST['ppd_item_name']) ?
			$_POST['ppd_item_name'] : '%T';
		$item_number = !empty($_POST['ppd_item_number']) ?
			$_POST['ppd_item_number'] : 'b%B-p%I';
		$amount = !empty($_POST['ppd_amount']) ?
			$_POST['ppd_amount'] : $ppd->amount;
		
		$ppd->setPostInfo($post_id,$item_name,$item_number,$amount);
	}
	
	public static function delete($post_id)
	{
		$ppd = new paypalDonation($GLOBALS['core']);
		$ppd->delPostInfo($post_id);
	}
}
?>