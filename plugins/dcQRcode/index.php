<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcQRcode, a plugin for Dotclear 2.
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

$s =& $core->blog->settings;

$tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'qrc_settings';
$_REQUEST['nb_per_page'] =  $s->qrc_nb_per_page;
if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
	$_REQUEST['nb_per_page'] = (integer) $_GET['nb'];
}
$returned_id = array();
$combo_img_size = dcQRcodeIndexLib::$combo_img_size;

# QRcode class
$qrc = new dcQRcode($core,QRC_CACHE_PATH);

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
		$s->setNamespace('dcQRcode');
		$s->put('qrc_active',isset($_POST['qrc_active']));
		$s->put('qrc_use_mebkm',isset($_POST['qrc_use_mebkm']));
		$s->put('qrc_img_size',(integer) $_POST['qrc_img_size']);
		$s->put('qrc_cache_use',isset($_POST['qrc_cache_use']));
		$s->put('qrc_bhv_entrytplhome',isset($_POST['qrc_bhv_entrytplhome']));
		$s->put('qrc_bhv_entrytplpost',isset($_POST['qrc_bhv_entrytplpost']));
		$s->put('qrc_bhv_entrytplcategory',isset($_POST['qrc_bhv_entrytplcategory']));
		$s->put('qrc_bhv_entrytpltag',isset($_POST['qrc_bhv_entrytpltag']));
		$s->put('qrc_bhv_entrytplarchive',isset($_POST['qrc_bhv_entrytplarchive']));
		$s->put('qrc_bhv_entryplace',$_POST['qrc_bhv_entryplace']);

		if ($core->auth->isSuperAdmin() 
		 && isset($_POST['qrc_cache_use']) 
		 && !empty($_POST['qrc_cache_path']))
		{
			if (!is_dir($_POST['qrc_cache_path']))
			{
				throw new Exception('Unable to find cache path');
			}
			$s->put('qrc_cache_path',$_POST['qrc_cache_path']);
		}
		$s->setNamespace('system');

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
<?php
foreach(array('txt','url','mecard','geo','market','ical','iappli','matmsg') as $k)
{
	echo 
	"\$('#list-title-".$k."').toggleWithLegend($('#list-content-".$k."'),{cookie:'dcx_dcqrcode_list_".$k."'});\n".
	"\$('#form-title-".$k."').toggleWithLegend($('#form-content-".$k."'),{cookie:'dcx_dcqrcode_form_".$k."'});\n";
}
?>
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
	  $s->qrc_active).' '.
     __('Enable plugin'); ?>
	</label></p>

    <p><label class="classic"><?php echo
	 form::checkbox(array('qrc_use_mebkm'),'1',
	  $s->qrc_use_mebkm).' '.
     __('Use MEBKM anchor for URL QR codes'); ?>
	</label></p>
	<p class="form-note">
	 <?php echo __('MEBKM anchors made links as bookmarks with titles.'); ?>
	</p>

    <p><label class="classic">
	 <?php echo __('Image size:'); ?><br />
     <?php echo form::combo(array('qrc_img_size'),$combo_img_size,
	  $s->qrc_img_size); ?>
	</label></p>

<?php if ($core->auth->isSuperAdmin()) : ?>

    <p><label class="classic"><?php echo
	 form::checkbox(array('qrc_cache_use'),'1',
	  $s->qrc_cache_use).' '.
     __('Use image cache'); ?>
	</label></p>

    <p><label class="classic">
	 <?php echo __('Custom path for cache:'); ?><br />
     <?php echo form::field(array('qrc_cache_path'),50,255,
	  $s->qrc_cache_path); ?>
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
	  $s->qrc_bhv_entrytplhome).' '.
     __('Include on entries on home page'); ?>
	</label></p>

	<p><label class="classic"><?php echo
	 form::checkbox(array('qrc_bhv_entrytplpost'),'1',
	  $s->qrc_bhv_entrytplpost).' '.
     __('Include on entries on post page'); ?>
	</label></p>

	<p><label class="classic"><?php echo
	 form::checkbox(array('qrc_bhv_entrytplcategory'),'1',
	  $s->qrc_bhv_entrytplcategory).' '.
     __('Include on entries on category page'); ?>
	</label></p>

	<p><label class="classic"><?php echo
	 form::checkbox(array('qrc_bhv_entrytpltag'),'1',
	  $s->qrc_bhv_entrytpltag).' '.
     __('Include on entries on tag page'); ?>
	</label></p>

	<p><label class="classic"><?php echo
	 form::checkbox(array('qrc_bhv_entrytplarchive'),'1',
	  $s->qrc_bhv_entrytplarchive).' '.
     __('Include on entries on monthly archive page'); ?>
	</label></p>

    <p><label class="classic">
	 <?php echo __('Place where to insert image:'); ?><br />
     <?php echo form::combo(array('qrc_bhv_entryplace'),
	  array(__('before content')=>'before',__('after content')=>'after'),
	  $s->qrc_bhv_entryplace); ?>
	</label></p>

  </div>
  </div>

	<p class="form-note">* 
	 <?php echo 
	 __('In order to use this, blog theme must have behaviors "publicEntryBeforeContent" and  "publicEntryAfterContent".').'<br />'.
	 __('A template value is also available, you can add {{tpl:QRcode}} anywhere inside &lt;tpl:Entries&gt; loop in templates.'); ?>
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


echo dcPage::helpBlock('dcQRcode');
?>
  <hr class="clear"/>
  <p class="right">
   dcQRcode - <?php echo $core->plugins->moduleInfo('dcQRcode','version'); ?>&nbsp;
   <img alt="dcQRcode" src="index.php?pf=dcQRcode/icon.png" />
  </p>
 </body>
</html>