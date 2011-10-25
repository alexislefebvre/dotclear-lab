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

/* Admin icons and favorites
-------------------------------------------------------- */

$_menu['Plugins']->addItem(

	__('PayPal Buttons'),
	'plugin.php?p=PayPalButtons','index.php?pf=PayPalButtons/icon.png',
	preg_match('/plugin.php\?p=PayPalButtons(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id));

$core->addBehavior('adminDashboardFavs',array('PayPalButtonsBehaviors','dashboardFavs'));

class PayPalButtonsBehaviors
{
    public static function dashboardFavs($core,$favs)
    {
        $favs['PayPalButtons'] = new ArrayObject(array(
            'PayPalButtons',
            __('PayPal Buttons'),
            'plugin.php?p=PayPalButtons',
            'index.php?pf=PayPalButtons/icon.png',
            'index.php?pf=PayPalButtons/icon-big.png',
            'usage,contentadmin',
            null,
            null));
    }
}

/* Settings and admin pages behaviors
-------------------------------------------------------- */

$core->blog->settings->addNamespace('PayPalButtons');
$s =& $core->blog->settings->PayPalButtons;

if ($core->auth->check('admin',$core->blog->id) && $s->PayPalButtons_enabled) {
	$core->addBehavior('adminPostForm',array('adminPayPalButtonsBehavior','adminPostForm'));
	$core->addBehavior('adminPostHeaders',array('adminPayPalButtonsBehavior','adminHeaders'));
	$core->addBehavior('adminPageHeaders',array('adminPayPalButtonsBehavior','adminHeaders'));
}

class adminPayPalButtonsBehavior
{
	public static function adminHeaders()
	{
		return 
		'<script type="text/javascript">'."\n".
		'$(document).ready(function() {'."\n".
			
			'$(\'a.button-remove\').click(function() {'."\n".
			'msg = \''.__('Are you sure you want to remove this button?').'\';'."\n".
			'if (!window.confirm(msg)) {'."\n".
				'return false;'."\n".
			'}'."\n".
			'});'."\n".
		'});'."\n".
		'</script>'.
		dcPage::jsLoad('index.php?pf=PayPalButtons/js/_post.js');
	}
	
	public static function adminPostForm($cur)
	{
		global $core;
				
		if (is_null($cur)) {
			return;
		}
		$user_lang = $core->auth->getInfo('user_lang');
		
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

		$button_sizes = array(
			'1' => '_SM',
			'2' => '_LG',
			'3' => 'CC_LG'
		);
		
		$post_id = $cur->post_id;
		$user_lang = $core->auth->getInfo('user_lang');
		
		$con =& $core->con;
		
		$post_id = (integer) $post_id;
		
		$query = 'SELECT * FROM '.$core->prefix.'paypal_buttons WHERE post_id ="'.$post_id.'" ';   
		$rs = $con->select($query);
		
		if ($post_id) {
			if ($rs->f('button_enabled') != '1') {
				echo 
				'<div class="area" id="paypal-area">'.
					'<h3>'.__('PayPal button').'</h3>'.
					'<div id="paypal">'.
						'<fieldset><legend>'.__('No button').'</legend>'.
							'<p><a class="button" href="plugin.php?p=PayPalButtons&amp;do=add&amp;post_id='.$post_id.'">'.__('Add a button to entry').'</a></p>'.
						'</fieldset>'.
					'</div>'.
				'</div>';
			} else {
				echo 
				'<div class="area" id="paypal-area">'.
					'<h3>'.__('PayPal button').'</h3>'.
					'<div id="paypal" >'.
						'<fieldset><legend>'.__('Preview').'</legend>'.
							'<p><img src="https://www.paypal.com/'.$button_lang.'/i/btn/btn_'.$button_types[$rs->f('button_type')].$button_sizes[$rs->f('button_size')].'.gif" alt="" /></p>'.
							'<p><a class="button" href="plugin.php?p=PayPalButtons&amp;do=edit&amp;post_id='.$post_id.'">'.__('Edit button').'</a> '.
							'<a class="button delete button-remove" href="plugin.php?p=PayPalButtons&amp;do=delete&amp;post_id='.$post_id.'">'.__('Delete button').'</a></p>'.
						'</fieldset>'.
					'</div>'.
				'</div>';
			}
		}		
	}
}

/* Behaviors for Import/export plugin
-------------------------------------------------------- */

$core->addBehavior('exportSingle',array('backupPayPalButtons','exportSingle'));
$core->addBehavior('exportFull',array('backupPayPalButtons','exportFull'));
$core->addBehavior('importInit',array('backupPayPalButtons','importInit'));
$core->addBehavior('importFull',array('backupPayPalButtons','importFull'));

class backupPayPalButtons
{
	public static function exportSingle($core,$exp,$blog_id)
	{
		$exp->export('paypal_buttons',
			'SELECT * '.
			'FROM '.$core->prefix.'paypal_buttons'
		);
		
		$exp->export('paypal_cart_info',
			'SELECT * '.
			'FROM '.$core->prefix.'paypal_cart_info '.
			'WHERE blog_id = "'.$blog_id.'"'
		);
		
		$exp->export('paypal_subscription_info',
			'SELECT * '.
			'FROM '.$core->prefix.'paypal_subscription_info '.
			'WHERE blog_id = "'.$blog_id.'"'
		);
		
		$exp->export('paypal_payment_info',
			'SELECT * '.
			'FROM '.$core->prefix.'paypal_payment_info '.
			'WHERE blog_id = "'.$blog_id.'"'
		);
	}
	
	public static function exportFull($core,$exp)
	{
		$exp->exportTable('paypal_buttons');
		$exp->exportTable('paypal_cart_info');
		$exp->exportTable('paypal_subscription_info');
		$exp->exportTable('paypal_payment_info');
		
	}

	
	public static function importInit($bk,$core)
	{
		$strReq = 'TRUNCATE TABLE '.$core->prefix.'paypal_buttons';
		$core->con->execute($strReq);
		
		$strReq = 'TRUNCATE TABLE '.$core->prefix.'paypal_cart_info';
		$core->con->execute($strReq);
		
		$strReq = 'TRUNCATE TABLE '.$core->prefix.'paypal_subscription_info';
		$core->con->execute($strReq);
		
		$strReq = 'TRUNCATE TABLE '.$core->prefix.'paypal_payment_info';
		$core->con->execute($strReq);		
		
		$bk->cur_paypal_buttons = $core->con->openCursor($core->prefix.'paypal_buttons');
		$bk->cur_paypal_cart_info = $core->con->openCursor($core->prefix.'paypal_cart_info');
		$bk->cur_paypal_subscription_info = $core->con->openCursor($core->prefix.'paypal_subscription_info');
		$bk->cur_paypal_payment_info = $core->con->openCursor($core->prefix.'paypal_payment_info');
		
	}
	
	public static function importFull($line,$bk,$core)
	{
        if ($line->__name == 'paypal_buttons') {

            $bk->cur_paypal_buttons->clean();
            $bk->cur_paypal_buttons->post_id   = (integer) $line->post_id;
			$bk->cur_paypal_buttons->button_enabled   = (integer) $line->button_enabled;
			$bk->cur_paypal_buttons->button_type   = (integer) $line->button_type;
			$bk->cur_paypal_buttons->button_size   = (integer) $line->button_size;
			$bk->cur_paypal_buttons->hosted_button_id   = (string) $line->hosted_button_id;
			
            $bk->cur_paypal_buttons->insert();
        }
		
		if ($line->__name == 'paypal_cart_info') {

            $bk->cur_paypal_cart_info->clean();
            $bk->cur_paypal_cart_info->txnid   = (string) $line->txnid;
			$bk->cur_paypal_cart_info->itemname   = (string) $line->itemname;
			$bk->cur_paypal_cart_info->itemnumber   = (string) $line->itemnumber;
			$bk->cur_paypal_cart_info->os0   = (string) $line->os0;
			$bk->cur_paypal_cart_info->on0   = (string) $line->on0;
			$bk->cur_paypal_cart_info->os1   = (string) $line->os1;
			$bk->cur_paypal_cart_info->on1   = (string) $line->on1;
			$bk->cur_paypal_cart_info->quantity   = (string) $line->quantity;
			$bk->cur_paypal_cart_info->invoice   = (string) $line->invoice;
			$bk->cur_paypal_cart_info->invoice   = (string) $line->custom;
			$bk->cur_paypal_cart_info->blog_id   = (string) $line->blog_id;
			
            $bk->cur_paypal_cart_info->insert();
        }
		
		if ($line->__name == 'paypal_subscription_info') {

            $bk->cur_paypal_subscription_info->clean();
            $bk->cur_paypal_subscription_info->subscr_id   = (string) $line->subscr_id;
			$bk->cur_paypal_subscription_info->sub_event   = (string) $line->sub_event;
			$bk->cur_paypal_subscription_info->subscr_date   = (string) $line->subscr_date;
			$bk->cur_paypal_subscription_info->subscr_effective   = (string) $line->subscr_effective;
			$bk->cur_paypal_subscription_info->period1   = (string) $line->period1;
			$bk->cur_paypal_subscription_info->period2   = (string) $line->period2;
			$bk->cur_paypal_subscription_info->period3   = (string) $line->period3;
			$bk->cur_paypal_subscription_info->amount1   = (string) $line->amount1;
			$bk->cur_paypal_subscription_info->amount2   = (string) $line->amount2;
			$bk->cur_paypal_subscription_info->amount3   = (string) $line->amount3;
			$bk->cur_paypal_subscription_info->mc_amount1   = (string) $line->mc_amount1;
			$bk->cur_paypal_subscription_info->mc_amount2   = (string) $line->mc_amount2;
			$bk->cur_paypal_subscription_info->mc_amount3   = (string) $line->mc_amount3;
			$bk->cur_paypal_subscription_info->recurring   = (string) $line->recurring;
			$bk->cur_paypal_subscription_info->reattempt   = (string) $line->reattempt;
			$bk->cur_paypal_subscription_info->retry_at   = (string) $line->retry_at;
			$bk->cur_paypal_subscription_info->recur_times   = (string) $line->recur_times;
			$bk->cur_paypal_subscription_info->username   = (string) $line->username;
			$bk->cur_paypal_subscription_info->password   = (string) $line->password;
			$bk->cur_paypal_subscription_info->subscriber_emailaddress   = (string) $line->subscriber_emailaddress;
			$bk->cur_paypal_subscription_info->datecreation   = (string) $line->datecreation;
			$bk->cur_paypal_subscription_info->blog_id   = (string) $line->blog_id;
			
            $bk->cur_paypal_subscription_info->insert();
        }
		
		if ($line->__name == 'paypal_payment_info') {

            $bk->cur_paypal_payment_info->clean();
            $bk->cur_paypal_payment_info->firstname   = (string) $line->firstname;
			$bk->cur_paypal_payment_info->lastname   = (string) $line->lastname;
			$bk->cur_paypal_payment_info->buyer_email   = (string) $line->buyer_email;
			$bk->cur_paypal_payment_info->street   = (string) $line->street;
			$bk->cur_paypal_payment_info->city   = (string) $line->city;
			$bk->cur_paypal_payment_info->state   = (string) $line->state;
			$bk->cur_paypal_payment_info->zipcode   = (string) $line->zipcode;
			$bk->cur_paypal_payment_info->memo   = (string) $line->memo;
			$bk->cur_paypal_payment_info->itemname   = (string) $line->itemname;
			$bk->cur_paypal_payment_info->itemnumber   = (string) $line->itemnumber;
			$bk->cur_paypal_payment_info->os0   = (string) $line->os0;
			$bk->cur_paypal_payment_info->on0   = (string) $line->on0;
			$bk->cur_paypal_payment_info->os1   = (string) $line->os1;
			$bk->cur_paypal_payment_info->on1   = (string) $line->on1;
			$bk->cur_paypal_payment_info->quantity   = (string) $line->quantity;
			$bk->cur_paypal_payment_info->paymentdate   = (string) $line->paymentdate;
			$bk->cur_paypal_payment_info->paymenttype   = (string) $line->paymenttype;
			$bk->cur_paypal_payment_info->txnid   = (string) $line->txnid;
			$bk->cur_paypal_payment_info->mc_gross   = (string) $line->mc_gross;
			$bk->cur_paypal_payment_info->paymentstatus   = (string) $line->paymentstatus;
			$bk->cur_paypal_payment_info->pendingreason   = (string) $line->pendingreason;
			$bk->cur_paypal_payment_info->txntype   = (string) $line->txntype;
			$bk->cur_paypal_payment_info->tax   = (string) $line->tax;
			$bk->cur_paypal_payment_info->mc_currency   = (string) $line->mc_currency;
			$bk->cur_paypal_payment_info->reasoncode   = (string) $line->reasoncode;
			$bk->cur_paypal_payment_info->custom   = (string) $line->custom;
			$bk->cur_paypal_payment_info->country   = (string) $line->country;
			$bk->cur_paypal_payment_info->datecreation   = (string) $line->datecreation;
			$bk->cur_paypal_payment_info->blog_id   = (string) $line->blog_id;
			
            $bk->cur_paypal_payment_info->insert();
        }
	}
}
?>