<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcQRcode, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

dcPage::check('admin');

$tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'qrc_settings';
$returned_id = array();

# QRcode class
$qrc = new dcQRcode($core,QRC_CACHE_PATH);

$combo_img_size = array(
	'S' => 64,
	'M' => 92,
	'L' => 128,
	'X' => 256,
	'XL' => 512
);

# Save settings
if (!empty($_POST['settings']))
{
	try
	{
		$core->blog->settings->setNamespace('dcQRcode');
		$core->blog->settings->put(
			'qrc_active',
			isset($_POST['qrc_active']),
			'boolean',
			'Enabled plugin',
			true,false
		);
		$core->blog->settings->put(
			'qrc_use_mebkm',
			isset($_POST['qrc_use_mebkm']),
			'boolean',
			'Use MEBKM anchor for URL QR codes',
			true,false
		);
		$core->blog->settings->put(
			'qrc_img_size',
			(integer) $_POST['qrc_img_size'],
			'integer',
			'Image size',
			true,false
		);
		$core->blog->settings->put(
			'qrc_cache_use',
			isset($_POST['qrc_cache_use']),
			'boolean',
			'Use image cache',
			true,false
		);
		$core->blog->settings->put(
			'qrc_bhv_entrybeforecontent',
			isset($_POST['qrc_bhv_entrybeforecontent']),
			'boolean',
			'Use posts behavior before content',
			true,false
		);
		$core->blog->settings->put(
			'qrc_bhv_entryaftercontent',
			isset($_POST['qrc_bhv_entryaftercontent']),
			'boolean',
			'Use posts behavior after content',
			true,false
		);

		if ($core->auth->isSuperAdmin() 
		 && isset($_POST['qrc_cache_use']) 
		 && !empty($_POST['qrc_cache_path']))
		{
			if (!is_dir($_POST['qrc_cache_path']))
			{
				throw new Exception('Unable to find cache path');
			}
			$core->blog->settings->put(
				'qrc_cache_path',
				$_POST['qrc_cache_path'],
				'string',
				'Custom cache path',
				true,false
			);
		}

		$qrc->cleanCache();

		$core->blog->triggerBlog();
		http::redirect($p_url.'&t=qrc_settings');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}


?>
<html>
 <head>
  <title><?php echo __('QR code'); ?></title>
  <?php echo dcPage::jsPageTabs($tab); ?>
 </head>
 <body>
  <h2><?php echo html::escapeHTML($core->blog->name).
   ' &rsaquo; '.__('QR code'); ?></h2>

  <div class="multi-part" id="qrc_settings" title="<?php echo __('Settings'); ?>">
   <form method="post" action="plugin.php">

  <div class="two-cols">
  <div class="col">

    <h2><?php echo __('Settings'); ?></h2>

    <p><label class="classic"><?php echo
	 form::checkbox(array('qrc_active'),'1',
	  $core->blog->settings->qrc_active).' '.
     __('Enable plugin'); ?>
	</label></p>

    <p><label class="classic"><?php echo
	 form::checkbox(array('qrc_use_mebkm'),'1',
	  $core->blog->settings->qrc_use_mebkm).' '.
     __('Use MEBKM anchor for URL QR codes'); ?>
	</label></p>
	<p class="form-note">
	 <?php echo __('MEBKM anchors made links as bookmarks with titles.'); ?>
	</p>

    <p><label class="classic">
	 <?php echo __('Image size'); ?><br />
     <?php echo form::combo(array('qrc_img_size'),$combo_img_size,
	  $core->blog->settings->qrc_img_size); ?>
	</label></p>

<?php if ($core->auth->isSuperAdmin()) : ?>

    <p><label class="classic"><?php echo
	 form::checkbox(array('qrc_cache_use'),'1',
	  $core->blog->settings->qrc_cache_use).' '.
     __('Use image cache'); ?>
	</label></p>

    <p><label class="classic">
	 <?php echo __('Custom cache path'); ?><br />
     <?php echo form::field(array('qrc_cache_path'),50,255,
	  $core->blog->settings->qrc_cache_path); ?>
	</label></p>
	<p class="form-note">
	 <?php echo sprintf(__('Default is %s'),path::real($core->blog->public_path).'/qrc'); ?>
	</p>
	<p class="form-note">
	 <?php echo sprintf(__('Currently %s'),QRC_CACHE_PATH); ?>
	</p>

<?php endif; ?>

  </div>
  <div class="col">

   <h2><?php echo __('Theme'); ?></h2>

	<p><label class="classic"><?php echo
	 form::checkbox(array('qrc_bhv_entrybeforecontent'),'1',
	  $core->blog->settings->qrc_bhv_entrybeforecontent).' '.
     __('Include on entries before content'); ?>
	</label></p>
	<p class="form-note">
	 <?php echo __('In order to use this, blog theme must have behavior "publicEntryBeforeContent"'); ?>
	</p>

	<p><label class="classic"><?php echo
	 form::checkbox(array('qrc_bhv_entryaftercontent'),'1',
	  $core->blog->settings->qrc_bhv_entryaftercontent).' '.
     __('Include on entries after content'); ?>
	</label></p>
	<p class="form-note">
	 <?php echo __('In order to use this, blog theme must have behavior "publicEntryAfterContent"'); ?>
	</p>

  </div>
  </div>

    <p>
     <input type="submit" name="settings" value="<?php echo __('Save'); ?>" />
     <?php echo 
      form::hidden(array('p'),'dcQRcode').
      form::hidden(array('tab'),'qrc_settings').
      $core->formNonce();
     ?>
	</p>
   </form>
  </div>

<?php

if ($core->blog->settings->qrc_active)
{ 
	dcQrCodeIndexLib::urlTab($core,$qrc);
	dcQrCodeIndexLib::mecardTab($core,$qrc);
	dcQrCodeIndexLib::geoTab($core,$qrc);
	dcQrCodeIndexLib::marketTab($core,$qrc);
	dcQrCodeIndexLib::icalTab($core,$qrc);
}


# --BEHAVIOR-- QRcodeIndexTab
$core->callBehavior('QRcodeIndexTab',$core,$qrc);


?>

  <hr class="clear"/>
  <p class="right">
   dcQRcode - <?php echo $core->plugins->moduleInfo('dcQRcode','version'); ?>&nbsp;
   <img alt="dcQRcode" src="index.php?pf=dcQRcode/icon.png" />
  </p>
 </body>
</html>