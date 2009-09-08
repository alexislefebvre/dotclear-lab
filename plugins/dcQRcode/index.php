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
$_REQUEST['nb_per_page'] =  $core->blog->settings->qrc_nb_per_page;
if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
	$_REQUEST['nb_per_page'] = (integer) $_GET['nb'];
}
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

# Delete records
if (!empty($_POST['delete_qrcode']) && !empty($_POST['entries']))
{
	try
	{
		foreach($_POST['entries'] as $entry)
		{
			$qrc->delQRcode($entry);
		}
		$qrc->cleanCache();

		$core->blog->triggerBlog();
		http::redirect($_POST['redir']);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

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
			'qrc_bhv_entrytplhome',
			isset($_POST['qrc_bhv_entrytplhome']),
			'boolean',
			'Use posts behavior on home page',
			true,false
		);
		$core->blog->settings->put(
			'qrc_bhv_entrytplpost',
			isset($_POST['qrc_bhv_entrytplpost']),
			'boolean',
			'Use posts behavior on post page',
			true,false
		);
		$core->blog->settings->put(
			'qrc_bhv_entrytplcategory',
			isset($_POST['qrc_bhv_entrytplcategory']),
			'boolean',
			'Use posts behavior on category page',
			true,false
		);
		$core->blog->settings->put(
			'qrc_bhv_entrytpltag',
			isset($_POST['qrc_bhv_entrytpltag']),
			'boolean',
			'Use posts behavior on tag page',
			true,false
		);
		$core->blog->settings->put(
			'qrc_bhv_entryplace',
			$_POST['qrc_bhv_entryplace'],
			'string',
			'In what place insert image',
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
<?php 
echo 
dcPage::jsLoad('js/_posts_list.js').
dcPage::jsToolBar().
dcPage::jsPageTabs($tab); 


# --BEHAVIOR-- dcQRcodeIndexHeader
$core->callBehavior('dcQRcodeIndexHeader',$core,$qrc);


?>
  <script type="text/javascript">
    $(function() {
		$('#list-title-txt').toggleWithLegend($('#list-content-txt'),{cookie:'dcx_dcqrcode_list_txt'});
		$('#form-title-txt').toggleWithLegend($('#form-content-txt'),{cookie:'dcx_dcqrcode_form_txt'});
		$('#list-title-url').toggleWithLegend($('#list-content-url'),{cookie:'dcx_dcqrcode_list_url'});
		$('#form-title-url').toggleWithLegend($('#form-content-url'),{cookie:'dcx_dcqrcode_form_url'});
		$('#list-title-mecard').toggleWithLegend($('#list-content-mecard'),{cookie:'dcx_dcqrcode_list_mecard'});
		$('#form-title-mecard').toggleWithLegend($('#form-content-mecard'),{cookie:'dcx_dcqrcode_form_mecard'});
		$('#list-title-geo').toggleWithLegend($('#list-content-geo'),{cookie:'dcx_dcqrcode_list_geo'});
		$('#form-title-geo').toggleWithLegend($('#form-content-geo'),{cookie:'dcx_dcqrcode_form_geo'});
		$('#list-title-market').toggleWithLegend($('#list-content-market'),{cookie:'dcx_dcqrcode_list_market'});
		$('#form-title-market').toggleWithLegend($('#form-content-market'),{cookie:'dcx_dcqrcode_form_market'});
		$('#list-title-ical').toggleWithLegend($('#list-content-ical'),{cookie:'dcx_dcqrcode_list_ical'});
		$('#form-title-ical').toggleWithLegend($('#form-content-ical'),{cookie:'dcx_dcqrcode_form_ical'});
		$('#list-title-iappli').toggleWithLegend($('#list-content-iappli'),{cookie:'dcx_dcqrcode_list_iappli'});
		$('#form-title-iappli').toggleWithLegend($('#form-content-iappli'),{cookie:'dcx_dcqrcode_form_iappli'});
		$('#list-title-matmsg').toggleWithLegend($('#list-content-matmsg'),{cookie:'dcx_dcqrcode_list_matmsg'});
		$('#form-title-matmsg').toggleWithLegend($('#form-content-matmsg'),{cookie:'dcx_dcqrcode_form_matmsg'});
    });
  </script>
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

   <h2><?php echo __('Entries'); ?> *</h2>

	<p><label class="classic"><?php echo
	 form::checkbox(array('qrc_bhv_entrytplhome'),'1',
	  $core->blog->settings->qrc_bhv_entrytplhome).' '.
     __('Include on entries on home page'); ?>
	</label></p>

	<p><label class="classic"><?php echo
	 form::checkbox(array('qrc_bhv_entrytplpost'),'1',
	  $core->blog->settings->qrc_bhv_entrytplpost).' '.
     __('Include on entries on post page'); ?>
	</label></p>

	<p><label class="classic"><?php echo
	 form::checkbox(array('qrc_bhv_entrytplcategory'),'1',
	  $core->blog->settings->qrc_bhv_entrytplcategory).' '.
     __('Include on entries on category page'); ?>
	</label></p>

	<p><label class="classic"><?php echo
	 form::checkbox(array('qrc_bhv_entrytpltag'),'1',
	  $core->blog->settings->qrc_bhv_entrytpltag).' '.
     __('Include on entries on tag page'); ?>
	</label></p>

    <p><label class="classic">
	 <?php echo __('In what place insert image:'); ?><br />
     <?php echo form::combo(array('qrc_bhv_entryplace'),
	  array(__('before content')=>'before',__('after content')=>'after'),
	  $core->blog->settings->qrc_bhv_entryplace); ?>
	</label></p>

  </div>
  </div>

	<p class="form-note">* 
	 <?php echo __('In order to use this, blog theme must have behaviors "publicEntryBeforeContent" and  "publicEntryAfterContent"'); ?>
	</p>

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
	dcQRcodeIndexLib::txtTab($core,$qrc);
	dcQRcodeIndexLib::urlTab($core,$qrc);
	dcQRcodeIndexLib::mecardTab($core,$qrc);
	dcQRcodeIndexLib::geoTab($core,$qrc);
	dcQRcodeIndexLib::marketTab($core,$qrc);
	dcQRcodeIndexLib::icalTab($core,$qrc);
	dcQRcodeIndexLib::iappliTab($core,$qrc);
	dcQRcodeIndexLib::matmsgTab($core,$qrc);
}


# --BEHAVIOR-- dcQRcodeIndexTab
$core->callBehavior('dcQRcodeIndexTab',$core,$qrc);


?>
<div class="multi-part" id="about" title="<?php echo __('About'); ?>">
<div class="two-cols">
<div class="col">
<h3>Version:</h3>
<ul><li>dcQRcode <?php echo $core->plugins->moduleInfo('dcQRcode','version'); ?></li></ul>
<h3>Support:</h3>
<ul>
<li><a href="http://dotclear.jcdenis.com/go/dcQRcode">Author's blog</a></li>
<li><a href="http://dotclear.jcdenis.com/go/dcQRcode-support">Dotclear forum</a></li>
<li><a href="http://lab.dotclear.org/wiki/plugin/dcQRcode">Dotclear lab</a></li>
</ul>
<h3>Copyrights:</h3>
<ul>
<li><strong>Files</strong><br />
These files are parts of dcQRcode, a plugin for Dotclear 2.<br />
Copyright (c) 2009 JC Denis and contributors<br />
Licensed under the GPL version 2.0 license.<br />
<a href="http://www.gnu.org/licenses/old-licenses/gpl-2.0.html">http://www.gnu.org/licenses/old-licenses/gpl-2.0.html</a>
</li>
<li><strong>Images</strong><br />
Some icons from Silk icon set 1.3 by Mark James at:<br />
<a href="http://www.famfamfam.com/lab/icons/silk/">http://www.famfamfam.com/lab/icons/silk/</a><br />
under a Creative Commons Attribution 2.5 License<br />
<a href="http://creativecommons.org/licenses/by/2.5/">http://creativecommons.org/licenses/by/2.5/</a>.
</li>
<li><strong>QR Code</strong><br />
QR Code is an open format<br />
The format's specification is available royalty-free <br />
from its owner, who has promised not to exert patent <br />
rights on it.<br />
The term QR Code itself is a registered trademark of <br />
Denso Wave Incorporated.</li>
</ul>
<h3>Tools:</h3>
<ul>
<li>Traduced with Dotclear plugin Translater,</li>
<li>Packaged with Dotclear plugin Packager.</li>
</ul>
<h3>Read more:</h3>
<ul>
<li>Definition on <a href="http://en.wikipedia.org/wiki/QR_Code">Wikipedia</a></li>
<li>Charts API on <a href="http://code.google.com/intl/fr/apis/chart/">Google</a></li>
<li>QRcode API on <a href="http://code.google.com/p/zxing/">Google</a></li>
<li>Description on <a href="http://www.nttdocomo.co.jp/english/service/imode/make/content/barcode/">NTT docomo</a></li>
</ul>
</div>
<div class="col">
<p><img alt="QR code" src="index.php?pf=dcQRcode/icon-b.png" /></p>
<pre><?php readfile(dirname(__FILE__).'/release.txt'); ?></pre>
</div>
</div>
</div>

  <hr class="clear"/>
  <p class="right">
   dcQRcode - <?php echo $core->plugins->moduleInfo('dcQRcode','version'); ?>&nbsp;
   <img alt="dcQRcode" src="index.php?pf=dcQRcode/icon.png" />
  </p>
 </body>
</html>