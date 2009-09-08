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

class dcQRcodeIndexLib
{
	public static $combo_img_size = array(
		'S' => 64,
		'M' => 92,
		'L' => 128,
		'X' => 256,
		'XL' => 512
	);

	public static function returnImg($core,$id)
	{
		?>
		<h2><?php echo __('QRcode successfully created'); ?></h2>
		<p><?php echo $core->blog->url.$core->url->getBase('dcQRcodeImage').'/'.$id; ?>.png</p>
		<p><img alt="QR code" src="<?php echo $core->blog->url.$core->url->getBase('dcQRcodeImage').'/'.$id; ?>.png" /></p>
		<?php
	}

	public static function txtTab($core,$qrc)
	{
		?>
		<div class="multi-part" id="qrc_create_txt" title="<?php echo __('Simple text'); ?>">
		<?php

		if (!isset($_POST['TXT'])) $_POST['TXT'] = array(128,'');

		if (!empty($_POST['create_txt']) && !empty($_POST['TXT'][1]))
		{
			try
			{
				$qrc->setType('TXT');
				$qrc->setSize($_POST['TXT'][0]);
				$id = $qrc->encode($_POST['TXT'][1]);
				self::returnImg($core,$id);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		?>

		<form method="post" action="plugin.php">

		<h2 id="form-title-txt"><?php echo __('Create a QR code'); ?></h2>
		<div id="form-content-txt">

		<p><label class="classic">
		<?php echo __('Image size'); ?><br />
		<?php echo form::combo(array('TXT[0]'),self::$combo_img_size,html::escapeHTML($_POST['TXT'][0])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Content:'); ?> *<br />
		<?php echo form::field(array('TXT[1]'),60,255,html::escapeHTML($_POST['TXT'][1])); ?>
		</label></p>

		<p>
		<input type="submit" name="create_txt" value="<?php echo __('Create'); ?>" />
		<?php echo 
		form::hidden(array('p'),'dcQRcode').
		form::hidden(array('tab'),'qrc_create_txt').
		$core->formNonce();
		?>
		</p>
		<p class="form-note">* <?php echo __('Required field'); ?></p>
		</div>
		</form>

		<h2 id="list-title-txt"><?php echo __('List of records'); ?></h2>
		<div id="list-content-txt">
		<?php
		$page_num = !empty($_GET['page_txt']) ? (integer) $_GET['page_txt'] : 1;

		$pager_base_url = 
		'plugin.php?p=dcQRcode'.
		'&amp;tab=qrc_create_txt'.
		'&amp;nb='.$_REQUEST['nb_per_page'].
		'&amp;page_txt=%s';
		
		$redir = 'plugin.php?p=dcQRcode&amp;tab=qrc_create_txt';

		try
		{
			$params = array();
			$params['qrcode_type'] = 'TXT';
			$params['limit'] = array((($page_num -1)*$_REQUEST['nb_per_page']),$_REQUEST['nb_per_page']);
			$lists = $qrc->getQRcodes($params);

			$params = array();
			$params['qrcode_type'] = 'TXT';
			$counter = $qrc->getQRcodes($params,true);

			$list_url = new dcQRcodeList($core,$lists,$counter->f(0),$pager_base_url);
		} 
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}

		$list_url->display($page_num,$_REQUEST['nb_per_page'],'page_txt',$pager_base_url,$redir);
		?>
		</div>
		</div>
		<?php
	}

	public static function urlTab($core,$qrc)
	{
		?>
		<div class="multi-part" id="qrc_create_url" title="<?php echo __('Bookmark'); ?>">
		<?php

		if (!isset($_POST['URL'])) $_POST['URL'] = array(128,'','');
		$_POST['URL'][3] = !isset($_POST['URL'][3]) ? false : true;

		if (!empty($_POST['create_url']) && !empty($_POST['URL'][2]))
		{
			try
			{
				$qrc->setType('URL');
				$qrc->setSize($_POST['URL'][0]);
				$qrc->setParams('use_mebkm',$_POST['URL'][3]);
				$id = $qrc->encode($_POST['URL'][2],$_POST['URL'][1]);
				self::returnImg($core,$id);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		?>

		<form method="post" action="plugin.php">

		<h2 id="form-title-url"><?php echo __('Create a QR code'); ?></h2>
		<div id="form-content-url">

		<p><label class="classic">
		<?php echo __('Image size'); ?><br />
		<?php echo form::combo(array('URL[0]'),self::$combo_img_size,html::escapeHTML($_POST['URL'][0])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Title:'); ?><br />
		<?php echo form::field(array('URL[1]'),60,255,html::escapeHTML($_POST['URL'][1])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('URL:'); ?> *<br />
		<?php echo form::field(array('URL[2]'),60,255,html::escapeHTML($_POST['URL'][2])); ?>
		</label></p>

		<p><label class="classic"><?php echo
		form::checkbox(array('URL[3]'),'1',html::escapeHTML($_POST['URL'][3])).' '.
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
		<p class="form-note">* <?php echo __('Required field'); ?></p>
		</div>
		</form>

		<h2 id="list-title-url"><?php echo __('List of records'); ?></h2>
		<div id="list-content-url">
		<?php
		$page_num = !empty($_GET['page_url']) ? (integer) $_GET['page_url'] : 1;

		$pager_base_url = 
		'plugin.php?p=dcQRcode'.
		'&amp;tab=qrc_create_url'.
		'&amp;nb='.$_REQUEST['nb_per_page'].
		'&amp;page_url=%s';
		
		$redir = 'plugin.php?p=dcQRcode&amp;tab=qrc_create_url';

		try
		{
			$params = array();
			$params['qrcode_type'] = 'URL';
			$params['limit'] = array((($page_num -1)*$_REQUEST['nb_per_page']),$_REQUEST['nb_per_page']);
			$lists = $qrc->getQRcodes($params);

			$params = array();
			$params['qrcode_type'] = 'URL';
			$counter = $qrc->getQRcodes($params,true);

			$list_url = new dcQRcodeList($core,$lists,$counter->f(0),$pager_base_url);
		} 
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}

		$list_url->display($page_num,$_REQUEST['nb_per_page'],'page_url',$pager_base_url,$redir);
		?>
		</div>
		</div>
		<?php
	}

	public static function mecardTab($core,$qrc)
	{
		?>
		<div class="multi-part" id="qrc_create_mecard" title="<?php echo __('Phonebook'); ?>">
		<?php

		if (!isset($_POST['MECARD']))
		{
			$_POST['MECARD'] = array(128,'','',array('',''),array('',''),'','','','','','');
		}

		if (!empty($_POST['create_mecard']) && !empty($_POST['MECARD'][1]))
		{
			try
			{
				$qrc->setType('MECARD');
				$qrc->setSize($_POST['MECARD'][0]);
				$id = $qrc->encode(
					$_POST['MECARD'][1],$_POST['MECARD'][2],$_POST['MECARD'][3],
					$_POST['MECARD'][4],$_POST['MECARD'][5],$_POST['MECARD'][6],
					$_POST['MECARD'][7],$_POST['MECARD'][8],$_POST['MECARD'][9],
					$_POST['MECARD'][10]
				);
				self::returnImg($core,$id);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		?>

		<form method="post" action="plugin.php">

		<h2 id="form-title-mecard"><?php echo __('Create a QR code'); ?></h2>
		<div id="form-content-mecard">

		<p><label class="classic">
		<?php echo __('Image size'); ?><br />
		<?php echo form::combo(array('MECARD[0]'),self::$combo_img_size,html::escapeHTML($_POST['MECARD'][0])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Name:'); ?> *<br />
		<?php echo form::field(array('MECARD[1]'),60,255,html::escapeHTML($_POST['MECARD'][1])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Address:'); ?> *<br />
		<?php echo form::field(array('MECARD[2]'),60,255,html::escapeHTML($_POST['MECARD'][2])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Phone:'); ?> *<br />
		<?php echo form::field(array('MECARD[3][0]'),60,255,html::escapeHTML($_POST['MECARD'][3][0])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Second phone:'); ?><br />
		<?php echo form::field(array('MECARD[3][1]'),60,255,html::escapeHTML($_POST['MECARD'][3][1])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Email:'); ?> *<br />
		<?php echo form::field(array('MECARD[4][0]'),60,255,html::escapeHTML($_POST['MECARD'][4][0])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Second email:'); ?><br />
		<?php echo form::field(array('MECARD[4][1]'),60,255,html::escapeHTML($_POST['MECARD'][4][1])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('URL:'); ?><br />
		<?php echo form::field(array('MECARD[5]'),60,255,html::escapeHTML($_POST['MECARD'][5])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Birthdate:'); ?><br />
		<?php echo form::field(array('MECARD[6]'),60,255,html::escapeHTML($_POST['MECARD'][6])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Memo:'); ?><br />
		<?php echo form::field(array('MECARD[7]'),60,255,html::escapeHTML($_POST['MECARD'][7])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Nickname:'); ?><br />
		<?php echo form::field(array('MECARD[8]'),60,255,html::escapeHTML($_POST['MECARD'][8])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Video phone:'); ?><br />
		<?php echo form::field(array('MECARD[9]'),60,255,html::escapeHTML($_POST['MECARD'][9])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Sound:'); ?><br />
		<?php echo form::field(array('MECARD[10]'),60,255,html::escapeHTML($_POST['MECARD'][10])); ?>
		</label></p>

		<p>
		<input type="submit" name="create_mecard" value="<?php echo __('Create'); ?>" />
		<?php echo 
		form::hidden(array('p'),'dcQRcode').
		form::hidden(array('tab'),'qrc_create_mecard').
		$core->formNonce();
		?>
		</p>
		<p class="form-note">* <?php echo __('Required field'); ?></p>
		</div>
		</form>

		<h2 id="list-title-mecard"><?php echo __('List of records'); ?></h2>
		<div id="list-content-mecard">
		<?php
		$page_num = !empty($_GET['page_mecard']) ? (integer) $_GET['page_mecard'] : 1;

		$pager_base_url = 
		'plugin.php?p=dcQRcode'.
		'&amp;tab=qrc_create_mecard'.
		'&amp;nb='.$_REQUEST['nb_per_page'].
		'&amp;page_url=%s';
		
		$redir = 'plugin.php?p=dcQRcode&amp;tab=qrc_create_mecard';

		try
		{
			$params = array();
			$params['qrcode_type'] = 'MECARD';
			$params['limit'] = array((($page_num -1)*$_REQUEST['nb_per_page']),$_REQUEST['nb_per_page']);
			$lists = $qrc->getQRcodes($params);

			$params = array();
			$params['qrcode_type'] = 'MECARD';
			$counter = $qrc->getQRcodes($params,true);

			$list_url = new dcQRcodeList($core,$lists,$counter->f(0),$pager_base_url);
		} 
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}

		$list_url->display($page_num,$_REQUEST['nb_per_page'],'page_mecard',$pager_base_url,$redir);
		?>
		</div>
		</div>
		<?php
	}

	public static function geoTab($core,$qrc)
	{
		?>
		<div class="multi-part" id="qrc_create_geo" title="<?php echo __('Geographic'); ?>">
		<?php

		if (!isset($_POST['GEO'])) $_POST['GEO'] = array(128,'','','');

		if (!empty($_POST['create_geo']) && !empty($_POST['GEO'][1]) && !empty($_POST['GEO'][2]))
		{
			try
			{
				$qrc->setType('GEO');
				$qrc->setSize($_POST['GEO'][0]);
				$id = $qrc->encode($_POST['GEO'][1],$_POST['GEO'][2],$_POST['GEO'][3]);
				self::returnImg($core,$id);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		?>

		<form method="post" action="plugin.php">

		<h2 id="form-title-geo"><?php echo __('Create a QR code'); ?></h2>
		<div id="form-content-geo">

		<p><label class="classic">
		<?php echo __('Image size'); ?><br />
		<?php echo form::combo(array('GEO[0]'),self::$combo_img_size,html::escapeHTML($_POST['GEO'][0])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Latitude:'); ?> *<br />
		<?php echo form::field(array('GEO[1]'),60,255,html::escapeHTML($_POST['GEO'][1])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Longitude:'); ?> *<br />
		<?php echo form::field(array('GEO[2]'),60,255,html::escapeHTML($_POST['GEO'][2])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Altitude:'); ?><br />
		<?php echo form::field(array('GEO[3]'),60,255,html::escapeHTML($_POST['GEO'][3])); ?>
		</label></p>

		<p>
		<input type="submit" name="create_geo" value="<?php echo __('Create'); ?>" />
		<?php echo 
		form::hidden(array('p'),'dcQRcode').
		form::hidden(array('tab'),'qrc_create_geo').
		$core->formNonce();
		?>
		</p>
		<p class="form-note">* <?php echo __('Required field'); ?></p>
		</div>
		</form>

		<h2 id="list-title-geo"><?php echo __('List of records'); ?></h2>
		<div id="list-content-geo">
		<?php
		$page_num = !empty($_GET['page_geo']) ? (integer) $_GET['page_geo'] : 1;

		$pager_base_url = 
		'plugin.php?p=dcQRcode'.
		'&amp;tab=qrc_create_geo'.
		'&amp;nb='.$_REQUEST['nb_per_page'].
		'&amp;page_url=%s';
		
		$redir = 'plugin.php?p=dcQRcode&amp;tab=qrc_create_geo';

		try
		{
			$params = array();
			$params['qrcode_type'] = 'GEO';
			$params['limit'] = array((($page_num -1)*$_REQUEST['nb_per_page']),$_REQUEST['nb_per_page']);
			$lists = $qrc->getQRcodes($params);

			$params = array();
			$params['qrcode_type'] = 'GEO';
			$counter = $qrc->getQRcodes($params,true);

			$list_url = new dcQRcodeList($core,$lists,$counter->f(0),$pager_base_url);
		} 
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}

		$list_url->display($page_num,$_REQUEST['nb_per_page'],'page_geo',$pager_base_url,$redir);
		?>
		</div>
		</div>
		<?php
	}

	public static function marketTab($core,$qrc)
	{
		?>
		<div class="multi-part" id="qrc_create_market" title="<?php echo __('Android market'); ?>">
		<?php

		if (!isset($_POST['MARKET'])) $_POST['MARKET'] = array('pname','',128);

		if (!empty($_POST['create_market']) && !empty($_POST['MARKET'][0]) && !empty($_POST['MARKET'][1]))
		{
			try
			{
				$qrc->setType('MARKET');
				$qrc->setSize($_POST['MARKET'][2]);
				$id = $qrc->encode($_POST['MARKET'][0],$_POST['MARKET'][1]);
				self::returnImg($core,$id);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		?>

		<form method="post" action="plugin.php">

		<h2 id="form-title-market"><?php echo __('Create a QR code'); ?></h2>
		<div id="form-content-market">

		<p><label class="classic">
		<?php echo __('Category:'); ?> *<br />
		<?php echo form::combo(array('MARKET[0]'),
		array(__('Publisher')=>'pub',__('Package')=>'pname'),html::escapeHTML($_POST['MARKET'][0])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Search:'); ?> *<br />
		<?php echo form::field(array('MARKET[1]'),60,255,html::escapeHTML($_POST['MARKET'][1])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Image size'); ?><br />
		<?php echo form::combo(array('MARKET[2]'),self::$combo_img_size,html::escapeHTML($_POST['MARKET'][2])); ?>
		</label></p>

		<p>
		<input type="submit" name="create_market" value="<?php echo __('Create'); ?>" />
		<?php echo 
		form::hidden(array('p'),'dcQRcode').
		form::hidden(array('tab'),'qrc_create_market').
		$core->formNonce();
		?>
		</p>
		</div>
		</form>

		<h2 id="list-title-market"><?php echo __('List of records'); ?></h2>
		<div id="list-content-market">
		<?php
		$page_num = !empty($_GET['page_market']) ? (integer) $_GET['page_market'] : 1;

		$pager_base_url = 
		'plugin.php?p=dcQRcode'.
		'&amp;tab=qrc_create_market'.
		'&amp;nb='.$_REQUEST['nb_per_page'].
		'&amp;page_url=%s';
		
		$redir = 'plugin.php?p=dcQRcode&amp;tab=qrc_create_market';

		try
		{
			$params = array();
			$params['qrcode_type'] = 'MARKET';
			$params['limit'] = array((($page_num -1)*$_REQUEST['nb_per_page']),$_REQUEST['nb_per_page']);
			$lists = $qrc->getQRcodes($params);

			$params = array();
			$params['qrcode_type'] = 'MARKET';
			$counter = $qrc->getQRcodes($params,true);

			$list_url = new dcQRcodeList($core,$lists,$counter->f(0),$pager_base_url);
		} 
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}

		$list_url->display($page_num,$_REQUEST['nb_per_page'],'page_market',$pager_base_url,$redir);
		?>
		</div>
		</div>
		<?php
	}

	public static function icalTab($core,$qrc)
	{
		?>
		<div class="multi-part" id="qrc_create_ical" title="<?php echo __('iCal'); ?>">
		<?php

		if (!isset($_POST['ICAL'])) $_POST['ICAL'] = array('','','',128);

		if (!empty($_POST['create_ical']) && !empty($_POST['ICAL'][0]) && !empty($_POST['ICAL'][1]))
		{
			try
			{
				$qrc->setType('ICAL');
				$qrc->setSize($_POST['ICAL'][3]);
				$id = $qrc->encode($_POST['ICAL'][0],$_POST['ICAL'][1],$_POST['ICAL'][2]);
				self::returnImg($core,$id);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		?>

		<form method="post" action="plugin.php">

		<h2 id="form-title-ical"><?php echo __('Create a QR code'); ?></h2>
		<div id="form-content-ical">

		<p><label class="classic">
		<?php echo __('Summary'); ?> *<br />
		<?php echo form::field(array('ICAL[0]'),60,255,html::escapeHTML($_POST['ICAL'][0])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Start date:'); ?> *<br />
		<?php echo form::field(array('ICAL[1]'),60,255,html::escapeHTML($_POST['ICAL'][1])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('End date:'); ?> *<br />
		<?php echo form::field(array('ICAL[2]'),60,255,html::escapeHTML($_POST['ICAL'][2])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Image size'); ?><br />
		<?php echo form::combo(array('ICAL[3]'),self::$combo_img_size,html::escapeHTML($_POST['ICAL'][3])); ?>
		</label></p>

		<p>
		<input type="submit" name="create_ical" value="<?php echo __('Create'); ?>" />
		<?php echo 
		form::hidden(array('p'),'dcQRcode').
		form::hidden(array('tab'),'qrc_create_ical').
		$core->formNonce();
		?>
		</p>
		<p class="form-note">* <?php echo __('Required field'); ?></p>
		</div>
		</form>

		<h2 id="list-title-ical"><?php echo __('List of records'); ?></h2>
		<div id="list-content-ical">
		<?php
		$page_num = !empty($_GET['page_ical']) ? (integer) $_GET['page_ical'] : 1;

		$pager_base_url = 
		'plugin.php?p=dcQRcode'.
		'&amp;tab=qrc_create_ical'.
		'&amp;nb='.$_REQUEST['nb_per_page'].
		'&amp;page_url=%s';
		
		$redir = 'plugin.php?p=dcQRcode&amp;tab=qrc_create_ical';

		try
		{
			$params = array();
			$params['qrcode_type'] = 'ICAL';
			$params['limit'] = array((($page_num -1)*$_REQUEST['nb_per_page']),$_REQUEST['nb_per_page']);
			$lists = $qrc->getQRcodes($params);

			$params = array();
			$params['qrcode_type'] = 'ICAL';
			$counter = $qrc->getQRcodes($params,true);

			$list_url = new dcQRcodeList($core,$lists,$counter->f(0),$pager_base_url);
		} 
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}

		$list_url->display($page_num,$_REQUEST['nb_per_page'],'page_ical',$pager_base_url,$redir);
		?>
		</div>
		</div>
		<?php
	}

	public static function iappliTab($core,$qrc)
	{
		?>
		<div class="multi-part" id="qrc_create_iappli" title="<?php echo __('i-appli'); ?>">
		<?php

		$p = array('','','','','','','','','','','','','','','','');
		if (!isset($_POST['IAPPLI'])) $_POST['IAPPLI'] = array(128,'','',$p,);

		if (!empty($_POST['create_iappli']) && !empty($_POST['IAPPLI'][1]) && !empty($_POST['IAPPLI'][2]))
		{
			try
			{
				$qrc->setType('IAPPLI');
				$qrc->setSize($_POST['IAPPLI'][0]);
				$params = array();
				foreach($_POST['IAPPLI'][3] as $param)
				{
					if (!empty($param))
					{
						$params[] = $param;
					}
				}
				$id = $qrc->encode($_POST['IAPPLI'][1],$_POST['IAPPLI'][2],$params);
				self::returnImg($core,$id);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		?>

		<form method="post" action="plugin.php">

		<h2 id="form-title-iappli"><?php echo __('Create a QR code'); ?></h2>
		<div id="form-content-iappli">

		<p><label class="classic">
		<?php echo __('Image size'); ?><br />
		<?php echo form::combo(array('IAPPLI[0]'),self::$combo_img_size,html::escapeHTML($_POST['IAPPLI'][0])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('ADF URL:'); ?> *<br />
		<?php echo form::field(array('IAPPLI[1]'),60,255,html::escapeHTML($_POST['IAPPLI'][1])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Command:'); ?> *<br />
		<?php echo form::field(array('IAPPLI[2]'),60,255,html::escapeHTML($_POST['IAPPLI'][2])); ?>
		</label></p>

		<?php for($i = 0; $i < 16; $i++) { ?>
		
			<p><label class="classic">
			<?php echo sprintf(__('Param %s'),($i+1)); ?><br />
			<?php echo form::field(array('IAPPLI[3]['.$i.']'),60,255,html::escapeHTML($_POST['IAPPLI'][3][$i])); ?>
			</label></p>

		<?php } ?>
		<p class="form-note"><?php echo 
			__('Designates a text string to be set as the parameter sent to the i-appli to be activated. (1 to 255 bytes)').'<br />'. 
			__('The "name" and "value" are separated by a comma (,).').'<br />'.
			__('16 parameters can be designated within a single LAPL: identifier.');
		?></p>

		<p>
		<input type="submit" name="create_iappli" value="<?php echo __('Create'); ?>" />
		<?php echo 
		form::hidden(array('p'),'dcQRcode').
		form::hidden(array('tab'),'qrc_create_iappli').
		$core->formNonce();
		?>
		</p>
		</div>
		</form>

		<h2 id="list-title-iappli"><?php echo __('List of records'); ?></h2>
		<div id="list-content-iappli">
		<?php
		$page_num = !empty($_GET['page_iappli']) ? (integer) $_GET['page_iappli'] : 1;

		$pager_base_url = 
		'plugin.php?p=dcQRcode'.
		'&amp;tab=qrc_create_iappli'.
		'&amp;nb='.$_REQUEST['nb_per_page'].
		'&amp;page_url=%s';
		
		$redir = 'plugin.php?p=dcQRcode&amp;tab=qrc_create_iappli';

		try
		{
			$params = array();
			$params['qrcode_type'] = 'IAPPLI';
			$params['limit'] = array((($page_num -1)*$_REQUEST['nb_per_page']),$_REQUEST['nb_per_page']);
			$lists = $qrc->getQRcodes($params);

			$params = array();
			$params['qrcode_type'] = 'IAPPLI';
			$counter = $qrc->getQRcodes($params,true);

			$list_url = new dcQRcodeList($core,$lists,$counter->f(0),$pager_base_url);
		} 
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}

		$list_url->display($page_num,$_REQUEST['nb_per_page'],'page_iappli',$pager_base_url,$redir);
		?>
		</div>
		</div>
		<?php
	}

	public static function matmsgTab($core,$qrc)
	{
		?>
		<div class="multi-part" id="qrc_create_matmsg" title="<?php echo __('E-mail'); ?>">
		<?php

		if (!isset($_POST['MATMSG'])) $_POST['MATMSG'] = array('','','',128);

		if (!empty($_POST['create_matmsg']) && !empty($_POST['MATMSG'][0]) && !empty($_POST['MATMSG'][1]))
		{
			try
			{
				$qrc->setType('MATMSG');
				$qrc->setSize($_POST['MATMSG'][3]);
				$id = $qrc->encode($_POST['MATMSG'][0],$_POST['MATMSG'][1],$_POST['MATMSG'][2]);
				self::returnImg($core,$id);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		?>

		<form method="post" action="plugin.php">

		<h2 id="form-title-matmsg"><?php echo __('Create a QR code'); ?></h2>
		<div id="form-content-matmsg">

		<p><label class="classic">
		<?php echo __('Receiver:'); ?> *<br />
		<?php echo form::field(array('MATMSG[0]'),60,255,html::escapeHTML($_POST['MATMSG'][0])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Subject:'); ?> *<br />
		<?php echo form::field(array('MATMSG[1]'),60,255,html::escapeHTML($_POST['MATMSG'][1])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Message:'); ?> *<br />
		<?php echo form::field(array('MATMSG[2]'),60,255,html::escapeHTML($_POST['MATMSG'][2])); ?>
		</label></p>

		<p><label class="classic">
		<?php echo __('Image size'); ?><br />
		<?php echo form::combo(array('MATMSG[3]'),self::$combo_img_size,html::escapeHTML($_POST['MATMSG'][3])); ?>
		</label></p>

		<p>
		<input type="submit" name="create_matmsg" value="<?php echo __('Create'); ?>" />
		<?php echo 
		form::hidden(array('p'),'dcQRcode').
		form::hidden(array('tab'),'qrc_create_matmsg').
		$core->formNonce();
		?>
		</p>
		<p class="form-note">* <?php echo __('Required field'); ?></p>
		</div>
		</form>

		<h2 id="list-title-matmsg"><?php echo __('List of records'); ?></h2>
		<div id="list-content-matmsg">
		<?php
		$page_num = !empty($_GET['page_matmsg']) ? (integer) $_GET['page_matmsg'] : 1;

		$pager_base_url = 
		'plugin.php?p=dcQRcode'.
		'&amp;tab=qrc_create_matmsg'.
		'&amp;nb='.$_REQUEST['nb_per_page'].
		'&amp;page_url=%s';
		
		$redir = 'plugin.php?p=dcQRcode&amp;tab=qrc_create_matmsg';

		try
		{
			$params = array();
			$params['qrcode_type'] = 'MATMSG';
			$params['limit'] = array((($page_num -1)*$_REQUEST['nb_per_page']),$_REQUEST['nb_per_page']);
			$lists = $qrc->getQRcodes($params);

			$params = array();
			$params['qrcode_type'] = 'MATMSG';
			$counter = $qrc->getQRcodes($params,true);

			$list_url = new dcQRcodeList($core,$lists,$counter->f(0),$pager_base_url);
		} 
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}

		$list_url->display($page_num,$_REQUEST['nb_per_page'],'page_matmsg',$pager_base_url,$redir);
		?>
		</div>
		</div>
		<?php
	}
}

?>