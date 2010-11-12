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

# Get new version
$new_version = $core->plugins->moduleInfo('paypalDonation','version');
$old_version = $core->getVersion('paypalDonation');

# Compare versions
if (version_compare($old_version,$new_version,'>=')) {return;}

# Install or update
try
{
	if (version_compare(str_replace("-r","-p",DC_VERSION),'2.2-alpha','<'))
	{
		throw new Exception('paypalDonation requires Dotclear 2.2');
	}
	
	# Settings
	$core->blog->settings->addNamespace('paypalDonation');
	$s = $core->blog->settings->paypalDonation;
	$s->put('active',false,'boolean','Enabled paypalDonation plugin',false,true);
	$s->put('business','','string','Paypal account ID',false,true);
	$s->put('page_style','','string','Custom Paypal page style',false,true);
	$s->put('return_page',true,'boolean','Return to a custom thank you page',false,true);
	$s->put('amount','2','string','Default amount of donation',false,true);
	$s->put('item_name','','string','Default purpose of donation',false,true);
	$s->put('item_number','','string','Default reference of donation',false,true);
	$s->put('currency_code','EUR','string','Default currency code eg USD, EUR',false,true);
	$s->put('country_code','fr_FR/FR','string','Default country code eg en_US, fr_FR',false,true);
	$s->put('button_type','large','string','Default button type',false,true);
	$s->put('button_url','','string','Custom button url',false,true);
	$s->put('button_text','','string','Simple submit button label',false,true);
	$s->put('button_place','after','string','Place of special button',false,true);
	$s->put('button_tpl',serialize(array('post')),'string','List of templates types which used special button',false,true);
	$s->put('page_title','','string','Title of thank you page',false,true);
	$s->put('page_content','','string','Content of thank you page',false,true);
	
	# Version
	$core->setVersion('paypalDonation',$new_version);
	
	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
	return false;
}
?>