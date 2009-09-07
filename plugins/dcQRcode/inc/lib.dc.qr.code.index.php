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

class dcQrCodeIndexLib
{
	public static $combo_img_size = array(
		'S' => 64,
		'M' => 92,
		'L' => 128,
		'X' => 256,
		'XL' => 512
	);

	public static function urlTab($core,$qrc)
	{
		if (!isset($_POST['URL'])) $_POST['URL'] = array('','',128);
		$_POST['URL'][3] = !isset($_POST['URL'][3]) ? false : true;

		if (!empty($_POST['create_url']) && !empty($_POST['URL'][1]))
		{
			try
			{
				$qrc->setType('URL');
				$qrc->setSize($_POST['URL'][2]);
				$qrc->setParams('use_mebkm',$_POST['URL'][3]);
				$returned_id['URL'] = $qrc->encode($_POST['URL'][1],$_POST['URL'][0]);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		?>

		<div class="multi-part" id="qrc_create_url" title="<?php echo sprintf(__('Create %s QRcode'),'URL'); ?>">

		<?php if (isset($returned_id['URL'])) { ?>

		<h2><?php echo __('QRcode successfully created'); ?></h2>
		<p><?php echo $core->blog->url.$core->url->getBase('dcQRcodeImage').'/'.$returned_id['URL']; ?>.png</p>
		<p><img alt="QR code" src="<?php echo $core->blog->url.$core->url->getBase('dcQRcodeImage').'/'.$returned_id['URL']; ?>.png" /></p>

		<?php } ?>

		<form method="post" action="plugin.php">

		<h2><?php echo __('Create a QR code'); ?></h2>

		<p><label class="classic">
		<?php echo __('Title:'); ?><br />
		<?php echo form::field(array('URL[0]'),60,255,$_POST['URL'][0]); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('URL:'); ?><br />
		<?php echo form::field(array('URL[1]'),60,255,$_POST['URL'][1]); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Image size'); ?><br />
		<?php echo form::combo(array('URL[2]'),self::$combo_img_size,$_POST['URL'][2]); ?>
		</label></p>

		<p><label class="classic"><?php echo
		form::checkbox(array('URL[3]'),'1',$_POST['URL'][3]).' '.
		__('Use MEBKM anchor'); ?>
		</label></p>

		<p>
		<input type="submit" name="create_url" value="<?php echo __('Create'); ?>" />
		<?php echo 
		form::hidden(array('p'),'dcQRcode').
		form::hidden(array('tab'),'qrc_create_url').
		$core->formNonce();
		?>
		</p>
		</form>
		</div>

		<?php
	}

	public static function mecardTab($core,$qrc)
	{
		if (!isset($_POST['MECARD'])) $_POST['MECARD'] = array('','','','',128);

		if (!empty($_POST['create_mecard']) && !empty($_POST['MECARD'][0]))
		{
			try
			{
				$qrc->setType('MECARD');
				$qrc->setSize($_POST['MECARD'][4]);
				$returned_id['MECARD'] = $qrc->encode($_POST['MECARD'][0],$_POST['MECARD'][1],$_POST['MECARD'][2],$_POST['MECARD'][3]);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		?>

		<div class="multi-part" id="qrc_create_mecard" title="<?php echo sprintf(__('Create %s QRcode'),'MECARD'); ?>">

		<?php if (isset($returned_id['MECARD'])) { ?>

		<h2><?php echo __('QRcode successfully created'); ?></h2>
		<p><?php echo $core->blog->url.$core->url->getBase('dcQRcodeImage').'/'.$returned_id['MECARD']; ?>.png</p>
		<p><img alt="QR code" src="<?php echo $core->blog->url.$core->url->getBase('dcQRcodeImage').'/'.$returned_id['MECARD']; ?>.png" /></p>

		<?php } ?>

		<form method="post" action="plugin.php">

		<h2><?php echo __('Create a QR code'); ?></h2>

		<p><label class="classic">
		<?php echo __('Name:'); ?><br />
		<?php echo form::field(array('MECARD[0]'),60,255,$_POST['MECARD'][0]); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Address:'); ?><br />
		<?php echo form::field(array('MECARD[1]'),60,255,$_POST['MECARD'][1]); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Phone:'); ?><br />
		<?php echo form::field(array('MECARD[2]'),60,255,$_POST['MECARD'][2]); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Email:'); ?><br />
		<?php echo form::field(array('MECARD[3]'),60,255,$_POST['MECARD'][3]); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Image size'); ?><br />
		<?php echo form::combo(array('MECARD[4]'),self::$combo_img_size,$_POST['MECARD'][4]); ?>
		</label></p>

		<p>
		<input type="submit" name="create_mecard" value="<?php echo __('Create'); ?>" />
		<?php echo 
		form::hidden(array('p'),'dcQRcode').
		form::hidden(array('tab'),'qrc_create_mecard').
		$core->formNonce();
		?>
		</p>
		</form>
		</div>

		<?php
	}

	public static function geoTab($core,$qrc)
	{
		if (!isset($_POST['GEO'])) $_POST['GEO'] = array('','','',128);

		if (!empty($_POST['create_geo']) && !empty($_POST['GEO'][0]) && !empty($_POST['GEO'][1]))
		{
			try
			{
				$qrc->setType('GEO');
				$qrc->setSize($_POST['GEO'][3]);
				$returned_id['GEO'] = $qrc->encode($_POST['GEO'][0],$_POST['GEO'][1],$_POST['GEO'][2]);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		?>

		<div class="multi-part" id="qrc_create_geo" title="<?php echo sprintf(__('Create %s QRcode'),'GEO'); ?>">

		<?php if (isset($returned_id['GEO'])) { ?>

		<h2><?php echo __('QRcode successfully created'); ?></h2>
		<p><?php echo $core->blog->url.$core->url->getBase('dcQRcodeImage').'/'.$returned_id['GEO']; ?>.png</p>
		<p><img alt="QR code" src="<?php echo $core->blog->url.$core->url->getBase('dcQRcodeImage').'/'.$returned_id['GEO']; ?>.png" /></p>

		<?php } ?>

		<form method="post" action="plugin.php">

		<h2><?php echo __('Create a QR code'); ?></h2>

		<p><label class="classic">
		<?php echo __('Latitude:'); ?><br />
		<?php echo form::field(array('GEO[0]'),60,255,$_POST['GEO'][0]); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Longitude:'); ?><br />
		<?php echo form::field(array('GEO[1]'),60,255,$_POST['GEO'][1]); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Altitude:'); ?><br />
		<?php echo form::field(array('GEO[2]'),60,255,$_POST['GEO'][2]); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Image size'); ?><br />
		<?php echo form::combo(array('GEO[3]'),self::$combo_img_size,$_POST['GEO'][3]); ?>
		</label></p>

		<p>
		<input type="submit" name="create_geo" value="<?php echo __('Create'); ?>" />
		<?php echo 
		form::hidden(array('p'),'dcQRcode').
		form::hidden(array('tab'),'qrc_create_geo').
		$core->formNonce();
		?>
		</p>
		</form>
		</div>

		<?php
	}

	public static function marketTab($core,$qrc)
	{
		if (!isset($_POST['MARKET'])) $_POST['MARKET'] = array('pname','',128);

		if (!empty($_POST['create_market']) && !empty($_POST['MARKET'][0]) && !empty($_POST['MARKET'][1]))
		{
			try
			{
				$qrc->setType('MARKET');
				$qrc->setSize($_POST['MARKET'][2]);
				$returned_id['MARKET'] = $qrc->encode($_POST['MARKET'][0],$_POST['MARKET'][1]);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		?>

		<div class="multi-part" id="qrc_create_market" title="<?php echo sprintf(__('Create %s QRcode'),'Android market'); ?>">

		<?php if (isset($returned_id['MARKET'])) { ?>

		<h2><?php echo __('QRcode successfully created'); ?></h2>
		<p><?php echo $core->blog->url.$core->url->getBase('dcQRcodeImage').'/'.$returned_id['MARKET']; ?>.png</p>
		<p><img alt="QR code" src="<?php echo $core->blog->url.$core->url->getBase('dcQRcodeImage').'/'.$returned_id['MARKET']; ?>.png" /></p>

		<?php } ?>

		<form method="post" action="plugin.php">

		<h2><?php echo __('Create a QR code'); ?></h2>

		<p><label class="classic">
		<?php echo __('Category:'); ?><br />
		<?php echo form::combo(array('MARKET[0]'),
		array(__('Publisher')=>'pub',__('Package')=>'pname'),$_POST['MARKET'][0]); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Search:'); ?><br />
		<?php echo form::field(array('MARKET[1]'),60,255,$_POST['MARKET'][1]); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Image size'); ?><br />
		<?php echo form::combo(array('MARKET[2]'),self::$combo_img_size,$_POST['MARKET'][2]); ?>
		</label></p>

		<p>
		<input type="submit" name="create_market" value="<?php echo __('Create'); ?>" />
		<?php echo 
		form::hidden(array('p'),'dcQRcode').
		form::hidden(array('tab'),'qrc_create_market').
		$core->formNonce();
		?>
		</p>
		</form>
		</div>

		<?php
	}

	public static function icalTab($core,$qrc)
	{
		if (!isset($_POST['ICAL'])) $_POST['ICAL'] = array('','','',128);

		if (!empty($_POST['create_ical']) && !empty($_POST['ICAL'][0]) && !empty($_POST['ICAL'][1]))
		{
			try
			{
				$qrc->setType('ICAL');
				$qrc->setSize($_POST['ICAL'][3]);
				$returned_id['ICAL'] = $qrc->encode($_POST['ICAL'][0],$_POST['ICAL'][1],$_POST['ICAL'][2]);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		?>

		<div class="multi-part" id="qrc_create_ical" title="<?php echo sprintf(__('Create %s QRcode'),'iCal'); ?>">

		<?php if (isset($returned_id['ICAL'])) { ?>

		<h2><?php echo __('QRcode successfully created'); ?></h2>
		<p><?php echo $core->blog->url.$core->url->getBase('dcQRcodeImage').'/'.$returned_id['ICAL']; ?>.png</p>
		<p><img alt="QR code" src="<?php echo $core->blog->url.$core->url->getBase('dcQRcodeImage').'/'.$returned_id['ICAL']; ?>.png" /></p>

		<?php } ?>

		<form method="post" action="plugin.php">

		<h2><?php echo __('Create a QR code'); ?></h2>

		<p><label class="classic">
		<?php echo __('Summary'); ?><br />
		<?php echo form::field(array('ICAL[0]'),60,255,$_POST['ICAL'][0]); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Start date:'); ?><br />
		<?php echo form::field(array('ICAL[1]'),60,255,$_POST['ICAL'][1]); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('End date:'); ?><br />
		<?php echo form::field(array('ICAL[2]'),60,255,$_POST['ICAL'][2]); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Image size'); ?><br />
		<?php echo form::combo(array('ICAL[3]'),self::$combo_img_size,$_POST['ICAL'][3]); ?>
		</label></p>

		<p>
		<input type="submit" name="create_ical" value="<?php echo __('Create'); ?>" />
		<?php echo 
		form::hidden(array('p'),'dcQRcode').
		form::hidden(array('tab'),'qrc_create_ical').
		$core->formNonce();
		?>
		</p>
		</form>
		</div>

		<?php
	}

	public static function iappliTab($core,$qrc)
	{
		$p = array('','','','','','','','','','','','','','','','');
		if (!isset($_POST['IAPPLI'])) $_POST['IAPPLI'] = array('','',$p,128);

		if (!empty($_POST['create_iappli']) && !empty($_POST['IAPPLI'][0]) && !empty($_POST['IAPPLI'][1]))
		{
			try
			{
				$qrc->setType('IAPPLI');
				$qrc->setSize($_POST['IAPPLI'][3]);
				$params = array();
				foreach($_POST['IAPPLI'][2] as $param)
				{
					if (!empty($param))
					{
						$params[] = $param;
					}
				}
				$returned_id['IAPPLI'] = $qrc->encode($_POST['IAPPLI'][0],$_POST['IAPPLI'][1],$params);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		?>

		<div class="multi-part" id="qrc_create_iappli" title="<?php echo sprintf(__('Create %s QRcode'),'i-appli'); ?>">

		<?php if (isset($returned_id['IAPPLI'])) { ?>

		<h2><?php echo __('QRcode successfully created'); ?></h2>
		<p><?php echo $core->blog->url.$core->url->getBase('dcQRcodeImage').'/'.$returned_id['IAPPLI']; ?>.png</p>
		<p><img alt="QR code" src="<?php echo $core->blog->url.$core->url->getBase('dcQRcodeImage').'/'.$returned_id['IAPPLI']; ?>.png" /></p>

		<?php } ?>

		<form method="post" action="plugin.php">

		<h2><?php echo __('Create a QR code'); ?></h2>

		<p><label class="classic">
		<?php echo __('ADF URL:'); ?><br />
		<?php echo form::field(array('IAPPLI[0]'),60,255,$_POST['IAPPLI'][0]); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Command:'); ?><br />
		<?php echo form::field(array('IAPPLI[1]'),60,255,$_POST['IAPPLI'][1]); ?>
		</label></p>

		<?php for($i = 0; $i < 16; $i++) { ?>
		
			<p><label class="classic">
			<?php echo sprintf(__('Param %s'),($i+1)); ?><br />
			<?php echo form::field(array('IAPPLI[2]['.$i.']'),60,255,$_POST['IAPPLI'][2][$i]); ?>
			</label></p>

		<?php } ?>
		<p class="form-note"><?php echo 
			__('Designates a text string to be set as the parameter sent to the i-appli to be activated. (1 to 255 bytes)').'<br />'. 
			__('The "name" and "value" are separated by a comma (,).').'<br />'.
			__('16 parameters can be designated within a single LAPL: identifier.');
		?></p>

		<p><label class="classic">
		<?php echo __('Image size'); ?><br />
		<?php echo form::combo(array('IAPPLI[3]'),self::$combo_img_size,$_POST['IAPPLI'][3]); ?>
		</label></p>

		<p>
		<input type="submit" name="create_iappli" value="<?php echo __('Create'); ?>" />
		<?php echo 
		form::hidden(array('p'),'dcQRcode').
		form::hidden(array('tab'),'qrc_create_iappli').
		$core->formNonce();
		?>
		</p>
		</form>
		</div>

		<?php
	}

	public static function matmsgTab($core,$qrc)
	{
		if (!isset($_POST['MATMSG'])) $_POST['MATMSG'] = array('','','',128);

		if (!empty($_POST['create_matmsg']) && !empty($_POST['MATMSG'][0]) && !empty($_POST['MATMSG'][1]))
		{
			try
			{
				$qrc->setType('MATMSG');
				$qrc->setSize($_POST['MATMSG'][3]);
				$returned_id['MATMSG'] = $qrc->encode($_POST['MATMSG'][0],$_POST['MATMSG'][1],$_POST['MATMSG'][2]);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		?>

		<div class="multi-part" id="qrc_create_matmsg" title="<?php echo sprintf(__('Create %s QRcode'),'MailTo'); ?>">

		<?php if (isset($returned_id['MATMSG'])) { ?>

		<h2><?php echo __('QRcode successfully created'); ?></h2>
		<p><?php echo $core->blog->url.$core->url->getBase('dcQRcodeImage').'/'.$returned_id['MATMSG']; ?>.png</p>
		<p><img alt="QR code" src="<?php echo $core->blog->url.$core->url->getBase('dcQRcodeImage').'/'.$returned_id['MATMSG']; ?>.png" /></p>

		<?php } ?>

		<form method="post" action="plugin.php">

		<h2><?php echo __('Create a QR code'); ?></h2>

		<p><label class="classic">
		<?php echo __('Receiver'); ?><br />
		<?php echo form::field(array('MATMSG[0]'),60,255,$_POST['MATMSG'][0]); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Subject:'); ?><br />
		<?php echo form::field(array('MATMSG[1]'),60,255,$_POST['MATMSG'][1]); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Message:'); ?><br />
		<?php echo form::field(array('MATMSG[2]'),60,255,$_POST['MATMSG'][2]); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Image size'); ?><br />
		<?php echo form::combo(array('MATMSG[3]'),self::$combo_img_size,$_POST['MATMSG'][3]); ?>
		</label></p>

		<p>
		<input type="submit" name="create_matmsg" value="<?php echo __('Create'); ?>" />
		<?php echo 
		form::hidden(array('p'),'dcQRcode').
		form::hidden(array('tab'),'qrc_create_matmsg').
		$core->formNonce();
		?>
		</p>
		</form>
		</div>

		<?php
	}
}

?>