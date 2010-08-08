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

if (!defined('DC_RC_PATH')){return;}

# Namespace for settings
$core->blog->settings->addNamespace('paypalDonation');

$core->addBehavior('initWidgets',array('paypalDonationAdminWidget','general'));
$core->addBehavior('initWidgets',array('paypalDonationAdminWidget','post'));

class paypalDonationAdminWidget
{
	public static function general($w)
	{
		global $core;
		
		$item_name = sprintf(__("Donate to '%s'"),html::escapeHTML($core->blog->name));
		
		$w->create('generalPaypalDonation',__('Paypal donation'),
			array('paypalDonationPublicWidget','general')
		);
		$w->generalPaypalDonation->setting(
			'title',__('Title:'),__('Donate'),'text'
		);
		$w->generalPaypalDonation->setting(
			'item_name',__('Purpose of this donation:'),$item_name,'text'
		);
		$w->generalPaypalDonation->setting(
			'item_number',__('Reference of this donation:'),'globaldonate','text'
		);
		$w->generalPaypalDonation->setting(
			'amount',__('Amount:'),10,'text'
		);
		$w->generalPaypalDonation->setting(
			'homeonly',__('Home page only'),1,'check'
		);
		
	}
	
	public static function post($w)
	{
		$w->create('postPaypalDonation',__('Paypal donation for entries'),
			array('paypalDonationPublicWidget','post')
		);
		$w->postPaypalDonation->setting(
			'title',__('Title:'),__('Donate for this story'),'text'
		);
	}
}

class paypalDonationPublicWidget
{
	public static function general($w)
	{
		global $core;
		
		if ($w->homeonly && $core->url->type != 'default' 
		|| !$core->blog->settings->paypalDonation->active)
		{
			return;
		}
		
		$ppd = new paypalDonation($core);
		
		if ($w->item_name)
		{
			$ppd->item_name = $w->item_name;
		}
		if ($w->item_number)
		{
			$ppd->item_number = $w->item_number;
		}
		if (preg_match('#[0-9]{1,}[\.]{0,1}[0-9]{0,}#',$w->amount) || $w->amount == '')
		{
			$ppd->amount = $w->amount;
		}
		
		$res =
		'<div class="paypaldonation">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		$ppd->build().
		'</div>';
		
		return $res;
	}
	
	public static function post($w)
	{
		global $core,$_ctx;
		
		if (!$core->blog->settings->paypalDonation->active 
		 || !$_ctx->exists('posts') || $_ctx->posts->post_type != 'post')
		{
			return;
		}
		
		$ppd = new paypalDonation($core);
		$res = $ppd->getPostInfo($_ctx->posts->post_id);
		
		if (!isset($res['use']) || !$res['use']) {
			return;
		}
		
		$ppd->item_name = $ppd->parsePostInfo($res['item_name'],$_ctx->posts);
		$ppd->item_number = $ppd->parsePostInfo($res['item_number'],$_ctx->posts);
		$ppd->amount = $res['amount'];
		
		$res =
		'<div class="paypaldonation">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		$ppd->build().
		'</div>';
		
		return $res;
	}
}
?>