<?php if (!defined('DC_CONTEXT_ADMIN')) {return;}
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of flvplayerconfig, a plugin for Dotclear 2.
# 
# Copyright (c) 2011 lipki and contributors
# kevin@lepeltier.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$default_tab = 'generale';
if (isset($_REQUEST['tab']))
	$default_tab = $_REQUEST['tab'];

$popup = (integer) !empty($_GET['popup']);

if( $popup )
	$default_tab = '';

$fileid = $_GET['id'];

if( $fileid ) {
	$core->media = new dcMedia($core);
	$f = $core->media->getFile($fileid);
	$flv = $f->file_url;
} else
	$flv = 'http://dotclear.toopi.info./public/3d_divers/D2D2_1.flv';

if (!empty($_POST['saveconfig'])) {

	$args = '';
	
	if( $_POST['loop'] == 'on' ) $args['loop'] = 1;
	if( $_POST['autoplay'] == 'on' ) $args['autoplay'] = 1;
	if( $_POST['autoload'] == 'on' ) $args['autoload'] = 1;
	if( $_POST['loadonstop'] != 'on' ) $args['loadonstop'] = 0;
	if( $_POST['phpstream'] == 'on' ) $args['phpstream'] = 1;
	if( $_POST['shortcut'] == 'on' ) $args['shortcut'] = 1;
	if( $_POST['showtitleandstartimage'] == 'on' ) $args['showtitleandstartimage'] = 1;
	if( $_POST['showstop'] == 'on' ) $args['showstop'] = 1;
	if( $_POST['showvolume'] == 'on' ) $args['showvolume'] = 1;
	if( $_POST['showfullscreen'] == 'on' ) $args['showfullscreen'] = 1;
	if( $_POST['showswitchsubtitles'] == 'on' ) $args['showswitchsubtitles'] = 1;
	if( $_POST['srt'] == 'on' ) $args['srt'] = 1;
	if( $_POST['buffershowbg'] != 'on' ) $args['buffershowbg'] = 0;
	if( $_POST['showiconplay'] == 'on' ) $args['showiconplay'] = 1;

	if( !empty($_POST['title']) )  $args['title'] = htmlspecialchars  ($_POST['title']);
	if( !empty($_POST['startimage']) )  $args['startimage'] = htmlspecialchars  ($_POST['startimage']);
	if( !empty($_POST['width']) || $_POST['width'] === 0 ) if( $_POST['width'] != 320 ) $args['width'] = $_POST['width'];
	if( !empty($_POST['height']) || $_POST['height'] === 0 ) if( $_POST['height'] != 240 ) $args['height'] = $_POST['height'];
	if( !empty($_POST['volume']) || $_POST['volume'] === 0 ) if( $_POST['volume'] != 100 ) $args['volume'] = $_POST['volume'];
	if( !empty($_POST['skin']) )  $args['skin'] = htmlspecialchars  ($_POST['skin']);
	if( !empty($_POST['margin']) || $_POST['margin'] === 0 ) if( $_POST['margin'] != 5 ) $args['margin'] = $_POST['margin'];
	if( !empty($_POST['bgcolor']) || $_POST['bgcolor'] == "" ) if( $_POST['bgcolor'] != "ffffff" ) $args['bgcolor'] = htmlspecialchars  ($_POST['bgcolor']);
	if( !empty($_POST['bgcolor1']) || $_POST['bgcolor1'] == "" ) if( $_POST['bgcolor1'] != "7c7c7c" ) $args['bgcolor1'] = htmlspecialchars  ($_POST['bgcolor1']);
	if( !empty($_POST['bgcolor2']) || $_POST['bgcolor2'] == "" ) if( $_POST['bgcolor2'] != "333333" ) $args['bgcolor2'] = htmlspecialchars  ($_POST['bgcolor2']);
	if( !empty($_POST['showtime']) || $_POST['showtime'] === 0 ) if( $_POST['showtime'] != 0 ) $args['showtime'] = $_POST['showtime'];
	if( !empty($_POST['showplayer']) || $_POST['showplayer'] === "" ) if( $_POST['showplayer'] != "autohide" ) $args['showplayer'] = htmlspecialchars  ($_POST['showplayer']);
	if( !empty($_POST['showloading']) || $_POST['showloading'] == "" ) if( $_POST['showloading'] != "autohide" ) $args['showloading'] = htmlspecialchars  ($_POST['showloading']);
	if( !empty($_POST['playertimeout']) || $_POST['playertimeout'] === 0 ) if( $_POST['playertimeout'] != 1500 ) $args['playertimeout'] = $_POST['playertimeout'];
	if( !empty($_POST['playercolor']) || $_POST['playercolor'] == "" ) if( $_POST['playercolor'] != "000000" ) $args['playercolor'] = htmlspecialchars  ($_POST['playercolor']);
	if( !empty($_POST['playeralpha']) || $_POST['playeralpha'] === 0 ) if( $_POST['playeralpha'] != 100 ) $args['playeralpha'] = $_POST['playeralpha'];
	if( !empty($_POST['loadingcolor']) || $_POST['loadingcolor'] == "" ) if( $_POST['loadingcolor'] != "ffff00" ) $args['loadingcolor'] = htmlspecialchars  ($_POST['loadingcolor']);
	if( !empty($_POST['buttoncolor']) || $_POST['buttoncolor'] == "" ) if( $_POST['buttoncolor'] != "ffffff" ) $args['buttoncolor'] = htmlspecialchars  ($_POST['buttoncolor']);
	if( !empty($_POST['buttonovercolor']) || $_POST['buttonovercolor'] == "" ) if( $_POST['buttonovercolor'] != "ffff00" ) $args['buttonovercolor'] = htmlspecialchars  ($_POST['buttonovercolor']);
	if( !empty($_POST['slidercolor1']) || $_POST['slidercolor1'] == "" ) if( $_POST['slidercolor1'] != "cccccc" ) $args['slidercolor1'] = htmlspecialchars  ($_POST['slidercolor1']);
	if( !empty($_POST['slidercolor2']) || $_POST['slidercolor2'] == "" ) if( $_POST['slidercolor2'] != "888888" ) $args['slidercolor2'] = htmlspecialchars  ($_POST['slidercolor2']);
	if( !empty($_POST['sliderovercolor']) || $_POST['sliderovercolor'] == "" ) if( $_POST['sliderovercolor'] != "ffff00" ) $args['sliderovercolor'] = htmlspecialchars  ($_POST['sliderovercolor']);
	if( !empty($_POST['buffer']) || $_POST['buffer'] === 0 ) if( $_POST['buffer'] != 5 ) $args['buffer'] = $_POST['buffer'];
	if( !empty($_POST['buffermessage']) || $_POST['buffermessage'] == "" ) if( $_POST['buffermessage'] != "Buffering _n_" ) $args['buffermessage'] = htmlspecialchars  ($_POST['buffermessage']);
	if( !empty($_POST['buffercolor']) || $_POST['buffercolor'] == "" ) if( $_POST['buffercolor'] != "ffffff" ) $args['buffercolor'] = htmlspecialchars  ($_POST['buffercolor']);
	if( !empty($_POST['bufferbgcolor']) || $_POST['bufferbgcolor'] == "" ) if( $_POST['bufferbgcolor'] != "000000" ) $args['bufferbgcolor'] = htmlspecialchars  ($_POST['bufferbgcolor']);
	if( !empty($_POST['titlecolor']) || $_POST['titlecolor'] == "" ) if( $_POST['titlecolor'] != "ffffff" ) $args['titlecolor'] = htmlspecialchars  ($_POST['titlecolor']);
	if( !empty($_POST['titlesize']) || $_POST['titlesize'] === 0 ) if( $_POST['titlesize'] != 20 ) $args['titlesize'] = $_POST['titlesize'];
	if( !empty($_POST['srtcolor']) || $_POST['srtcolor'] == "" ) if( $_POST['srtcolor'] != "ffffff" ) $args['srtcolor'] = htmlspecialchars  ($_POST['srtcolor']);
	if( !empty($_POST['srtbgcolor']) || $_POST['srtbgcolor'] == "" ) if( $_POST['srtbgcolor'] != "000000" ) $args['srtbgcolor'] = htmlspecialchars  ($_POST['srtbgcolor']);
	if( !empty($_POST['srtsize']) || $_POST['srtsize'] === 0 ) if( $_POST['srtsize'] != 11 ) $args['srtsize'] = $_POST['srtsize'];
	if( !empty($_POST['srturl']) )  $args['srturl'] = htmlspecialchars  ($_POST['srturl']);
	if( !empty($_POST['onclick']) || $_POST['onclick'] == "" ) if( $_POST['onclick'] != "playpause" ) $args['onclick'] = htmlspecialchars  ($_POST['onclick']);
	if( !empty($_POST['onclicktarget']) || $_POST['onclicktarget'] == "" ) if( $_POST['onclicktarget'] != "_self" ) $args['onclicktarget'] = htmlspecialchars  ($_POST['onclicktarget']);
	if( !empty($_POST['ondoubleclick']) || $_POST['ondoubleclick'] == "" ) if( $_POST['ondoubleclick'] != "none" ) $args['ondoubleclick'] = htmlspecialchars  ($_POST['ondoubleclick']);
	if( !empty($_POST['ondoubleclicktarget']) || $_POST['ondoubleclicktarget'] == "" ) if( $_POST['ondoubleclicktarget'] != "_self" ) $args['ondoubleclicktarget'] = htmlspecialchars  ($_POST['ondoubleclicktarget']);
	if( !empty($_POST['top1']) )  $args['top1'] = htmlspecialchars  ($_POST['top1']);
	if( !empty($_POST['top2']) )  $args['top2'] = htmlspecialchars  ($_POST['top2']);
	if( !empty($_POST['top3']) )  $args['top3'] = htmlspecialchars  ($_POST['top3']);
	if( !empty($_POST['top4']) )  $args['top4'] = htmlspecialchars  ($_POST['top4']);
	if( !empty($_POST['top5']) )  $args['top5'] = htmlspecialchars  ($_POST['top5']);
	if( !empty($_POST['iconplaycolor']) || $_POST['iconplaycolor'] == "" ) if( $_POST['iconplaycolor'] != "ffffff" ) $args['iconplaycolor'] = htmlspecialchars  ($_POST['iconplaycolor']);
	if( !empty($_POST['iconplaybgcolor']) || $_POST['iconplaybgcolor'] == "" ) if( $_POST['iconplaybgcolor'] != "000000" ) $args['iconplaybgcolor'] = htmlspecialchars  ($_POST['iconplaybgcolor']);
	if( !empty($_POST['iconplaybgalpha']) || $_POST['iconplaybgalpha'] === 0 ) if( $_POST['iconplaybgalpha'] != 75 ) $args['iconplaybgalpha'] = $_POST['iconplaybgalpha'];
	if( !empty($_POST['showmouse']) || $_POST['showmouse'] == "" ) if( $_POST['showmouse'] != "always" ) $args['showmouse'] = htmlspecialchars  ($_POST['showmouse']);
	if( !empty($_POST['videobgcolor']) || $_POST['videobgcolor'] == "" ) if( $_POST['videobgcolor'] != "000000" ) $args['videobgcolor'] = htmlspecialchars  ($_POST['videobgcolor']);
	if( !empty($_POST['netconnection']) )  $args['netconnection'] = htmlspecialchars  ($_POST['netconnection']);
	
	$core->blog->settings->themes->put('flvplayer_style', serialize($args), 'string', 'flvplayer config');
	http::redirect($p_url.'&tab='.$default_tab.'&saveconfig=1');
}

$args = unserialize($core->blog->settings->themes->flvplayer_style);

foreach( $args as $key => $val )
	$FlashVars[] = $key.'='.$val;

$FlashVars[] = 'flv='.$flv;
$FlashVars = implode( '&', $FlashVars);

if( !isset($args['title']) ) $args['title'] = "";
if( !isset($args['startimage']) ) $args['startimage'] = "";
if( !isset($args['width']) ) $args['width'] = 320;
if( !isset($args['height']) ) $args['height'] = 240;
if( !isset($args['loop']) ) $args['loop'] = 0;
if( !isset($args['autoplay']) ) $args['autoplay'] = 0;
if( !isset($args['autoload']) ) $args['autoload'] = 0;
if( !isset($args['volume']) ) $args['volume'] = 100;
if( !isset($args['skin']) ) $args['skin'] = "";
if( !isset($args['margin']) ) $args['margin'] = 5;
if( !isset($args['bgcolor']) ) $args['bgcolor'] = "ffffff";
if( !isset($args['bgcolor1']) ) $args['bgcolor1'] = "7c7c7c";
if( !isset($args['bgcolor2']) ) $args['bgcolor2'] = "333333";
if( !isset($args['showstop']) ) $args['showstop'] = 0;
if( !isset($args['showvolume']) ) $args['showvolume'] = 0;
if( !isset($args['showtime']) ) $args['showtime'] = 0;
if( !isset($args['showplayer']) ) $args['showplayer'] = "autohide";
if( !isset($args['showloading']) ) $args['showloading'] = "autohide";
if( !isset($args['showfullscreen']) ) $args['showfullscreen'] = 0;
if( !isset($args['showswitchsubtitles']) ) $args['showswitchsubtitles'] = 0;
if( !isset($args['playertimeout']) ) $args['playertimeout'] = 1500;
if( !isset($args['playercolor']) ) $args['playercolor'] = "000000";
if( !isset($args['playeralpha']) ) $args['playeralpha'] = 100;
if( !isset($args['loadingcolor']) ) $args['loadingcolor'] = "ffff00";
if( !isset($args['buttoncolor']) ) $args['buttoncolor'] = "ffffff";
if( !isset($args['buttonovercolor']) ) $args['buttonovercolor'] = "ffff00";
if( !isset($args['slidercolor1']) ) $args['slidercolor1'] = "cccccc";
if( !isset($args['slidercolor2']) ) $args['slidercolor2'] = "888888";
if( !isset($args['sliderovercolor']) ) $args['sliderovercolor'] = "ffff00";
if( !isset($args['buffer']) ) $args['buffer'] = 5;
if( !isset($args['buffermessage']) ) $args['buffermessage'] = "Buffering _n_";
if( !isset($args['buffercolor']) ) $args['buffercolor'] = "ffffff";
if( !isset($args['bufferbgcolor']) ) $args['bufferbgcolor'] = "000000";
if( !isset($args['buffershowbg']) ) $args['buffershowbg'] = 1;
if( !isset($args['titlecolor']) ) $args['titlecolor'] = "ffffff";
if( !isset($args['titlesize']) ) $args['titlesize'] = 20;
if( !isset($args['srt']) ) $args['srt'] = 0;
if( !isset($args['srtcolor']) ) $args['srtcolor'] = "ffffff";
if( !isset($args['srtbgcolor']) ) $args['srtbgcolor'] = "000000";
if( !isset($args['srtsize']) ) $args['srtsize'] = 11;
if( !isset($args['srturl']) ) $args['srturl'] = "";
if( !isset($args['onclick']) ) $args['onclick'] = "playpause";
if( !isset($args['onclicktarget']) ) $args['onclicktarget'] = "_self";
if( !isset($args['ondoubleclick']) ) $args['ondoubleclick'] = "none";
if( !isset($args['ondoubleclicktarget']) ) $args['ondoubleclicktarget'] = "_self";
if( !isset($args['top1']) ) $args['top1'] = "";
if( !isset($args['top2']) ) $args['top2'] = "";
if( !isset($args['top3']) ) $args['top3'] = "";
if( !isset($args['top4']) ) $args['top4'] = "";
if( !isset($args['top5']) ) $args['top5'] = "";
if( !isset($args['showiconplay']) ) $args['showiconplay'] = 0;
if( !isset($args['iconplaycolor']) ) $args['iconplaycolor'] = "ffffff";
if( !isset($args['iconplaybgcolor']) ) $args['iconplaybgcolor'] = "000000";
if( !isset($args['iconplaybgalpha']) ) $args['iconplaybgalpha'] = 75;
if( !isset($args['showmouse']) ) $args['showmouse'] = "always";
if( !isset($args['videobgcolor']) ) $args['videobgcolor'] = "000000";
if( !isset($args['loadonstop']) ) $args['loadonstop'] = 1;
if( !isset($args['phpstream']) ) $args['phpstream'] = 0;
if( !isset($args['shortcut']) ) $args['shortcut'] = 0;
if( !isset($args['netconnection']) ) $args['netconnection'] = "";
if( !isset($args['showtitleandstartimage']) ) $args['showtitleandstartimage'] = 0;

if (isset($_GET['saveconfig']))
	$msg .= __('Configuration successfully updated.');

?>
<html>
<head>
	<title><?php echo __('FLV Player Config'); ?></title>
	<link rel="stylesheet" media="screen" type="text/css" href="index.php?pf=flvplayerconfig/js/colorpicker/css/colorpicker.css" />
	<link rel="stylesheet" media="screen" type="text/css" href="index.php?pf=flvplayerconfig/style.css" />
	<?php echo dcPage::jsPageTabs($default_tab); ?>
	<?php if( $popup ) { echo dcPage::jsLoad('index.php?pf=flvplayerconfig/js/popup.js'); } ?>
	<?php echo dcPage::jsLoad('index.php?pf=flvplayerconfig/js/colorpicker/js/colorpicker.js'); ?>
	<?php echo dcPage::jsLoad('index.php?pf=flvplayerconfig/js/form.js'); ?>
</head>
<body>
	
	<h2><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.__('FLV Player Config'); ?></h2> 
	
	<?php if (!empty($msg)) {echo '<p class="message">'.$msg.'</p>';} ?>

	<div id="player">
		<object width="<?php echo $args['width']; ?>" height="<?php echo $args['height']; ?>" style="text-align: center;" type="application/x-shockwave-flash" data="index.php?pf=player_flv.swf">
			<param name="movie" value="index.php?pf=player_flv.swf">
			<param name="wmode" value="transparent">
			<param name="allowFullScreen" value="true">
			<param name="FlashVars" value="<?php echo $FlashVars; ?>">
		</object>
	</div>
	
	<form id="flvplayerconfig-insert-form"  method="post" action="<?php echo($p_url); ?>">
	
			
			<p class="clear">
				<?php echo $core->formNonce(); ?>
				<input id="flv" type="hidden" value="<?php echo $flv; ?>"/>
		<?php if( !$popup ) { ?>
				<input type="submit" tabindex="33" name="saveconfig" accesskey="s" value="<?php echo __('OK'); ?>" />
		<?php } else { ?>
					<a href="#" class="button" id="flvplayerconfig-cancel"><?php echo __('Undo'); ?></a> - 
					<a href="#" class="button" id="flvplayerconfig-preview"><?php echo __('Preview'); ?></a> - 
				<strong><a href="#" class="button" id="flvplayerconfig-ok"><?php echo __('OK'); ?></a></strong>
		<?php } ?>
			</p>
		
		<?php if( $popup ) { ?>
		
			<div class="multi-part" id="rapide" title="<?php echo __('Fast'); ?>" >
				<div class="two-cols">
					<div class="col">
						<fieldset>
							<legend><?php echo __('Video size'); ?></legend>
							<p><label for="width"><?php echo __('Width'); ?> :
								<input type="text" value="<?php echo $args['width']; ?>" class="int" name="width" id="width">
							</label></p>
							<p><label for="height"><?php echo __('Height'); ?> :
								<input type="text" value="<?php echo $args['height']; ?>" class="int" name="height" id="height">
							</label></p>
							<br class="clear">
						</fieldset>
					</div>
					<div class="col">
						<fieldset>
							<legend><?php echo __('Disposition'); ?></legend>
							<p><label for="align"><?php echo __('Disposition'); ?> :
								<select id="align" name="align">
									<option <?php echo $args['align']=="none"? 'selected="selected"':''; ?> value="none"><?php echo __('None'); ?></option>
									<option <?php echo $args['align']=="left"? 'selected="selected"':''; ?> value="left"><?php echo __('Left'); ?></option>
									<option <?php echo $args['align']=="right"? 'selected="selected"':''; ?> value="right"><?php echo __('Right'); ?></option>
									<option <?php echo $args['align']=="center"? 'selected="selected"':''; ?> value="center"><?php echo __('Center'); ?></option>
								</select>
							</label></p>
							<br class="clear">
						</fieldset>
					</div>
				</div>
			</div>
			
		<?php } ?>
		
			<div class="multi-part" id="generale" title="<?php echo __('General'); ?>" >
				<div class="two-cols">
					<div class="col">
						<fieldset>
							<legend><?php echo __('General'); ?></legend>
							<p><label for="title"><?php echo __('Title'); ?> :
								<input type="text" value="<?php echo $args['text']; ?>" class="text" name="text" id="text">
							</label></p>
							<p><label for="startimage"><?php echo __('Startimage'); ?> :
								<input type="text" value="<?php echo $args['startimage']; ?>" class="url" name="startimage" id="startimage">
							</label></p>
							<p class="form-note">
								<?php echo(__('Retrieves the URL of your image in the media manager.')); ?>
							</p>
							
				<?php if( !$popup ) { ?>
							<p><label for="width"><?php echo __('Width'); ?> :
								<input type="text" value="<?php echo $args['width']; ?>" class="int" name="width" id="width">
							</label></p>
							<p><label for="height"><?php echo __('Height'); ?> :
								<input type="text" value="<?php echo $args['height']; ?>" class="int" name="height" id="height">
							</label></p>
							<p><label for="align"><?php echo __('Disposition'); ?> :
								<select id="align" name="align">
									<option <?php echo $args['align']=="none"? 'selected="selected"':''; ?> value="none"><?php echo __('None'); ?></option>
									<option <?php echo $args['align']=="left"? 'selected="selected"':''; ?> value="left"><?php echo __('Left'); ?></option>
									<option <?php echo $args['align']=="right"? 'selected="selected"':''; ?> value="right"><?php echo __('Right'); ?></option>
									<option <?php echo $args['align']=="center"? 'selected="selected"':''; ?> value="center"><?php echo __('Center'); ?></option>
								</select>
							</label></p>
				<?php } ?>
							<p><label for="loop" class="classic">
								<input name="loop" id="loop" type="checkbox" <?php echo $args['loop']? 'checked="checked"':''; ?>/>
								<?php echo __('Loop'); ?>
							</label></p>
							<p><label for="autoplay" class="classic">
								<input name="autoplay" id="autoplay" type="checkbox" <?php echo $args['autoplay']? 'checked="checked"':''; ?>/>
								<?php echo __('Autoplay'); ?>
							</label></p>
							<p><label for="autoload" class="classic">
								<input name="autoload" id="autoload" type="checkbox" <?php echo $args['autoload']? 'checked="checked"':''; ?>/>
								<?php echo __('Autoload'); ?>
							</label></p>
							<p><label for="volume"><?php echo __('Volume'); ?> :
								<select id="volume" name="volume">
									<option <?php echo $args['volume']==0? 'selected="selected"':''; ?> value="0">0</option>
									<option <?php echo $args['volume']==25? 'selected="selected"':''; ?> value="25">25</option>
									<option <?php echo $args['volume']==50? 'selected="selected"':''; ?> value="50">50</option>
									<option <?php echo $args['volume']==75? 'selected="selected"':''; ?> value="75">75</option>
									<option <?php echo $args['volume']==100? 'selected="selected"':''; ?> value="100">100</option>
									<option <?php echo $args['volume']==125? 'selected="selected"':''; ?> value="125">125</option>
									<option <?php echo $args['volume']==150? 'selected="selected"':''; ?> value="150">150</option>
									<option <?php echo $args['volume']==175? 'selected="selected"':''; ?> value="175">175</option>
									<option <?php echo $args['volume']==200? 'selected="selected"':''; ?> value="200">200</option>
								</select>
							</label></p>
							<br class="clear">
						</fieldset>
					</div>
					<div class="col">
						<fieldset>
							<legend><?php echo __('Miscellaneous'); ?></legend>
							<p><label for="showmouse"><?php echo __('Show mouse'); ?> :
								<select id="showmouse" name="showmouse">
									<option <?php echo $args['showmouse']=='autohide'? 'selected="selected"':''; ?> value="autohide"><?php echo __('autohide'); ?></option>
									<option <?php echo $args['showmouse']=='always'? 'selected="selected"':''; ?> value="always"><?php echo __('always'); ?></option>
									<option <?php echo $args['showmouse']=='never'? 'selected="selected"':''; ?> value="never"><?php echo __('never'); ?></option>
								</select>
							</label></p>
							<p><label for="videobgcolor"><?php echo __('Video background color'); ?> :
								<input type="text" value="<?php echo $args['videobgcolor']; ?>" class="color" name="videobgcolor" id="videobgcolor">
							</label></p>
							<p><label for="loadonstop" class="classic">
								<input name="loadonstop" id="loadonstop" type="checkbox" <?php echo $args['loadonstop']? 'checked="checked"':''; ?>/>
								<?php echo __('Load on stop'); ?>
							</label></p>
							<p><label for="phpstream" class="classic">
								<input name="phpstream" id="phpstream" type="checkbox" <?php echo $args['phpstream']? 'checked="checked"':''; ?>/>
								<?php echo __('PHP stream'); ?>
							</label></p>
							<p><label for="shortcut" class="classic">
								<input name="shortcut" id="shortcut" type="checkbox" <?php echo $args['shortcut']? 'checked="checked"':''; ?>/>
								<?php echo __('Shortcut'); ?>
							</label></p>
							<p><label for="netconnection"><?php echo __('Net connection'); ?> :
								<input type="text" value="<?php echo $args['netconnection']; ?>" class="text" name="netconnection" id="netconnection">
							</label></p>
							<p><label for="showtitleandstartimage" class="classic">
								<input name="showtitleandstartimage" id="showtitleandstartimage" type="checkbox" <?php echo $args['showtitleandstartimage']? 'checked="checked"':''; ?>/>
								<?php echo __('Show title and startimage'); ?>
							</label></p>
							<br class="clear">
						</fieldset>
					</div>
				</div>
			</div>
			
			<div class="multi-part" id="bordure" title="<?php echo __('Border'); ?> & <?php echo __('Title'); ?>" >
				<div class="two-cols">
					<div class="col">
						<fieldset>
							<legend><?php echo __('Border'); ?></legend>
							<p><label for="skin"><?php echo __('Skin'); ?> :
								<input type="text" value="<?php echo $args['skin']; ?>" class="url" name="skin" id="skin">
							</label></p>
							<p><label for="margin"><?php echo __('Margin'); ?> :
								<input type="text" value="<?php echo $args['margin']; ?>" class="int" name="margin" id="margin">
							</label></p>
							<p><label for="bgcolor"><?php echo __('Background color'); ?> :
								<input type="text" value="<?php echo $args['bgcolor']; ?>" class="color" name="bgcolor" id="bgcolor">
							</label></p>
							<p><label for="bgcolor1"><?php echo __('Background color 1'); ?> :
								<input type="text" value="<?php echo $args['bgcolor1']; ?>" class="color" name="bgcolor1" id="bgcolor1">
							</label></p>
							<p><label for="bgcolor2"><?php echo __('Background color 2'); ?> :
								<input type="text" value="<?php echo $args['bgcolor2']; ?>" class="color" name="bgcolor2" id="bgcolor2">
							</label></p>
							<br class="clear">
						</fieldset>
					</div>
					<div class="col">
						<fieldset>
							<legend><?php echo __('Title'); ?></legend>
							<p><label for="titlecolor"><?php echo __('Title color'); ?> :
								<input type="text" value="<?php echo $args['titlecolor']; ?>" class="color" name="titlecolor" id="titlecolor">
							</label></p>
							<p><label for="titlesize"><?php echo __('Title size'); ?> :
								<select id="titlesize" name="titlesize">
									<option <?php echo $args['titlesize']==8? 'selected="selected"':''; ?> value="8">8</option>
									<option <?php echo $args['titlesize']==9? 'selected="selected"':''; ?> value="9">9</option>
									<option <?php echo $args['titlesize']==10? 'selected="selected"':''; ?> value="10">10</option>
									<option <?php echo $args['titlesize']==11? 'selected="selected"':''; ?> value="11">11</option>
									<option <?php echo $args['titlesize']==12? 'selected="selected"':''; ?> value="12">12</option>
									<option <?php echo $args['titlesize']==13? 'selected="selected"':''; ?> value="13">13</option>
									<option <?php echo $args['titlesize']==14? 'selected="selected"':''; ?> value="14">14</option>
									<option <?php echo $args['titlesize']==15? 'selected="selected"':''; ?> value="15">15</option>
									<option <?php echo $args['titlesize']==16? 'selected="selected"':''; ?> value="16">16</option>
									<option <?php echo $args['titlesize']==17? 'selected="selected"':''; ?> value="17">17</option>
									<option <?php echo $args['titlesize']==18? 'selected="selected"':''; ?> value="18">18</option>
									<option <?php echo $args['titlesize']==19? 'selected="selected"':''; ?> value="19">19</option>
									<option <?php echo $args['titlesize']==20? 'selected="selected"':''; ?> value="20">20</option>
									<option <?php echo $args['titlesize']==21? 'selected="selected"':''; ?> value="21">21</option>
									<option <?php echo $args['titlesize']==22? 'selected="selected"':''; ?> value="22">22</option>
									<option <?php echo $args['titlesize']==23? 'selected="selected"':''; ?> value="23">23</option>
									<option <?php echo $args['titlesize']==24? 'selected="selected"':''; ?> value="24">24</option>
									<option <?php echo $args['titlesize']==25? 'selected="selected"':''; ?> value="25">25</option>
									<option <?php echo $args['titlesize']==26? 'selected="selected"':''; ?> value="26">26</option>
								</select>
							</label></p>
							<br class="clear">
						</fieldset>
						<fieldset>
							<legend><?php echo __('Subtitle'); ?></legend>
							<p><label for="srt" class="classic">
								<input name="srt" id="srt" type="checkbox" <?php echo $args['srt']? 'checked="checked"':''; ?>/>
								<?php echo __('Subtitle'); ?>
							</label></p>
							<p><label for="srtcolor"><?php echo __('Subtitle color'); ?> :
								<input type="text" value="<?php echo $args['srtcolor']; ?>" class="color" name="srtcolor" id="srtcolor">
							</label></p>
							<p><label for="srtbgcolor"><?php echo __('Subtitle background color'); ?> :
								<input type="text" value="<?php echo $args['srtbgcolor']; ?>" class="color" name="srtbgcolor" id="srtbgcolor">
							</label></p>
							<p><label for="srtsize"><?php echo __('Subtitle size'); ?> :
								<select id="srtsize" name="srtsize">
									<option <?php echo $args['srtsize']==8? 'selected="selected"':''; ?> value="8">8</option>
									<option <?php echo $args['srtsize']==9? 'selected="selected"':''; ?> value="9">9</option>
									<option <?php echo $args['srtsize']==10? 'selected="selected"':''; ?> value="10">10</option>
									<option <?php echo $args['srtsize']==11? 'selected="selected"':''; ?> value="11">11</option>
									<option <?php echo $args['srtsize']==12? 'selected="selected"':''; ?> value="12">12</option>
									<option <?php echo $args['srtsize']==13? 'selected="selected"':''; ?> value="13">13</option>
									<option <?php echo $args['srtsize']==14? 'selected="selected"':''; ?> value="14">14</option>
									<option <?php echo $args['srtsize']==15? 'selected="selected"':''; ?> value="15">15</option>
									<option <?php echo $args['srtsize']==16? 'selected="selected"':''; ?> value="16">16</option>
								</select>
							</label></p>
							<p><label for="srturl"><?php echo __('Subtitle url'); ?> :
								<input type="text" value="<?php echo $args['srturl']; ?>" class="url" name="srturl" id="srturl">
							</label></p>
							<p class="form-note">
								<?php echo(__('Retrieves the URL of your file in the media manager.')); ?>
							</p>
							<br class="clear">
						</fieldset>
					</div>
				</div>
			</div>
			
			<div class="multi-part" id="barredecontrole" title="<?php echo __('Control Bar'); ?>" >
				<div class="two-cols">
					<div class="col">
						<fieldset>
							<legend><?php echo __('Control Bar'); ?></legend>
							<p><label for="showstop" class="classic">
								<input name="showstop" id="showstop" type="checkbox" <?php echo $args['showstop']? 'checked="checked"':''; ?>/>
								<?php echo __('Show stop'); ?>
							</label></p>
							<p><label for="showvolume" class="classic">
								<input name="showvolume" id="showvolume" type="checkbox" <?php echo $args['showvolume']? 'checked="checked"':''; ?>/>
								<?php echo __('Show volume'); ?>
							</label></p>
							<p><label for="showtime"><?php echo __('Show time'); ?> :
								<select id="showtime" name="showtime">
									<option <?php echo $args['showtime']==0? 'selected="selected"':''; ?> value="0">0</option>
									<option <?php echo $args['showtime']==1? 'selected="selected"':''; ?> value="1">1</option>
									<option <?php echo $args['showtime']==2? 'selected="selected"':''; ?> value="2">2</option>
								</select>
							</label></p>
							<p><label for="showplayer"><?php echo __('Show player'); ?> :
								<select id="showplayer" name="showplayer">
									<option <?php echo $args['showplayer']=='autohide'? 'selected="selected"':''; ?> value="autohide"><?php echo __('autohide'); ?></option>
									<option <?php echo $args['showplayer']=='always'? 'selected="selected"':''; ?> value="always"><?php echo __('always'); ?></option>
									<option <?php echo $args['showplayer']=='never'? 'selected="selected"':''; ?> value="never"><?php echo __('never'); ?></option>
								</select>
							</label></p>
							<p><label for="showloading"><?php echo __('Show loading'); ?> :
								<select id="showloading" name="showloading">
									<option <?php echo $args['showloading']=='autohide'? 'selected="selected"':''; ?> value="autohide"><?php echo __('autohide'); ?></option>
									<option <?php echo $args['showloading']=='always'? 'selected="selected"':''; ?> value="always"><?php echo __('always'); ?></option>
									<option <?php echo $args['showloading']=='never'? 'selected="selected"':''; ?> value="never"><?php echo __('never'); ?></option>
								</select>
							</label></p>
							<p><label for="showfullscreen" class="classic">
								<input name="showfullscreen" id="showfullscreen" type="checkbox" <?php echo $args['showfullscreen']? 'checked="checked"':''; ?>/>
								<?php echo __('Show full screen'); ?>
							</label></p>
							<p><label for="showswitchsubtitles" class="classic">
								<input name="showswitchsubtitles" id="showswitchsubtitles" type="checkbox" <?php echo $args['showswitchsubtitles']? 'checked="checked"':''; ?>/>
								<?php echo __('Show switch subtitles'); ?>
							</label></p>
							<p><label for="playertimeout"><?php echo __('Player time out'); ?> :
								<input type="text" value="<?php echo $args['playertimeout']; ?>" class="int" name="playertimeout" id="playertimeout">
							</label></p>
							<br class="clear">
						</fieldset>
					</div>
					<div class="col">
						<fieldset>
							<legend><?php echo __('Colors'); ?></legend>
							<p><label for="playercolor"><?php echo __('Player color'); ?> :
								<input type="text" value="<?php echo $args['playercolor']; ?>" class="color" name="playercolor" id="playercolor">
							</label></p>
							<p><label for="playeralpha"><?php echo __('Player alpha'); ?> :
								<select id="playeralpha" name="playeralpha">
									<option <?php echo $args['playeralpha']==0? 'selected="selected"':''; ?> value="0">0</option>
									<option <?php echo $args['playeralpha']==25? 'selected="selected"':''; ?> value="25">25</option>
									<option <?php echo $args['playeralpha']==50? 'selected="selected"':''; ?> value="50">50</option>
									<option <?php echo $args['playeralpha']==75? 'selected="selected"':''; ?> value="75">75</option>
									<option <?php echo $args['playeralpha']==100? 'selected="selected"':''; ?> value="100">100</option>
								</select>
							</label></p>
							<p><label for="loadingcolor"><?php echo __('Loading color'); ?> :
								<input type="text" value="<?php echo $args['loadingcolor']; ?>" class="color" name="loadingcolor" id="loadingcolor">
							</label></p>
							<p><label for="buttoncolor"><?php echo __('Button color'); ?> :
								<input type="text" value="<?php echo $args['buttoncolor']; ?>" class="color" name="buttoncolor" id="buttoncolor">
							</label></p>
							<p><label for="buttonovercolor"><?php echo __('Button over color'); ?> :
								<input type="text" value="<?php echo $args['buttonovercolor']; ?>" class="color" name="buttonovercolor" id="buttonovercolor">
							</label></p>
							<p><label for="slidercolor1"><?php echo __('Slider color 1'); ?> :
								<input type="text" value="<?php echo $args['slidercolor1']; ?>" class="color" name="slidercolor1" id="slidercolor1">
							</label></p>
							<p><label for="slidercolor2"><?php echo __('Slider color 2'); ?> :
								<input type="text" value="<?php echo $args['slidercolor2']; ?>" class="color" name="slidercolor2" id="slidercolor2">
							</label></p>
							<p><label for="sliderovercolor"><?php echo __('Slider over color'); ?> :
								<input type="text" value="<?php echo $args['sliderovercolor']; ?>" class="color" name="sliderovercolor" id="sliderovercolor">
							</label></p>
							<br class="clear">
						</fieldset>
					</div>
				</div>
			</div>
			
			<div class="multi-part" id="technique" title="<?php echo __('Technical '); ?>" >
				<div class="two-cols">
					<div class="col">
						<fieldset>
							<legend><?php echo __('Display Buffer'); ?></legend>
							<p><label for="buffer"><?php echo __('Buffer'); ?> :
								<select id="buffer" name="buffer">
									<option <?php echo $args['buffer']==5? 'selected="selected"':''; ?> value="5">5</option>
									<option <?php echo $args['buffer']==10? 'selected="selected"':''; ?> value="10">10</option>
									<option <?php echo $args['buffer']==20? 'selected="selected"':''; ?> value="20">20</option>
									<option <?php echo $args['buffer']==30? 'selected="selected"':''; ?> value="30">30</option>
									<option <?php echo $args['buffer']==60? 'selected="selected"':''; ?> value="60">60</option>
								</select>
							</label></p>
							<p><label for="buffermessage"><?php echo __('Buffer message'); ?> :
								<input type="text" value="<?php echo $args['buffermessage']; ?>" class="text" name="buffermessage" id="buffermessage">
							</label></p>
							<p><label for="buffercolor"><?php echo __('Buffer color'); ?> :
								<input type="text" value="<?php echo $args['buffercolor']; ?>" class="color" name="buffercolor" id="buffercolor">
							</label></p>
							<p><label for="bufferbgcolor"><?php echo __('Buffer background color'); ?> :
								<input type="text" value="<?php echo $args['bufferbgcolor']; ?>" class="color" name="bufferbgcolor" id="bufferbgcolor">
							</label></p>
							<p><label for="buffershowbg" class="classic">
								<input name="buffershowbg" id="buffershowbg" type="checkbox" <?php echo $args['buffershowbg']? 'checked="checked"':''; ?>/>
								<?php echo __('Buffer show background'); ?>
							</label></p>
							<br class="clear">
						</fieldset>
					</div>
					<div class="col">
						<fieldset>
							<legend><?php echo __('Controls the mouse'); ?></legend>
							<p><label for="onclick"><?php echo __('Onclick'); ?> :
								<input type="text" value="<?php echo $args['onclick']; ?>" class="text" name="onclick" id="onclick">
							</label></p>
							<p><label for="onclicktarget"><?php echo __('Onclick target'); ?> :
								<input type="text" value="<?php echo $args['onclicktarget']; ?>" class="text" name="onclicktarget" id="onclicktarget">
							</label></p>
							<p><label for="ondoubleclick"><?php echo __('Ondoubleclick'); ?> :
								<input type="text" value="<?php echo $args['ondoubleclick']; ?>" class="text" name="ondoubleclick" id="ondoubleclick">
							</label></p>
							<p><label for="ondoubleclicktarget"><?php echo __('Ondoubleclick target'); ?> :
								<input type="text" value="<?php echo $args['ondoubleclicktarget']; ?>" class="text" name="ondoubleclicktarget" id="ondoubleclicktarget">
							</label></p>
							<br class="clear">
						</fieldset>
					</div>
				</div>
			</div>
			
			<div class="multi-part" id="imagespardessuslavideo" title="<?php echo __('Images on the video'); ?> & <?php echo __('The icons of the video'); ?>" >
				<div class="two-cols">
					<div class="col">
						<fieldset>
							<legend><?php echo __('Images on the video'); ?></legend>
							<p><label for="top1"><?php echo __('Top 1'); ?> :
								<input type="text" value="<?php echo $args['top1']; ?>" class="text" name="top1" id="top1">
							</label></p>
							<p><label for="top2"><?php echo __('Top 2'); ?> :
								<input type="text" value="<?php echo $args['top2']; ?>" class="text" name="top2" id="top2">
							</label></p>
							<p><label for="top3"><?php echo __('Top 3'); ?> :
								<input type="text" value="<?php echo $args['top3']; ?>" class="text" name="top3" id="top3">
							</label></p>
							<p><label for="top4"><?php echo __('Top 4'); ?> :
								<input type="text" value="<?php echo $args['top4']; ?>" class="text" name="top4" id="top4">
							</label></p>
							<p><label for="top5"><?php echo __('Top 5'); ?> :
								<input type="text" value="<?php echo $args['top5']; ?>" class="text" name="top5" id="top5">
							</label></p>
							<br class="clear">
						</fieldset>
					</div>
					<div class="col">
						<fieldset>
							<legend><?php echo __('The icons of the video'); ?></legend>
							<p><label for="showiconplay" class="classic">
								<input name="showiconplay" id="showiconplay" type="checkbox" <?php echo $args['showiconplay']? 'checked="checked"':''; ?>/>
								<?php echo __('Show icon play'); ?>
							</label></p>
							<p><label for="iconplaycolor"><?php echo __('Icon play color'); ?> :
								<input type="text" value="<?php echo $args['iconplaycolor']; ?>" class="color" name="iconplaycolor" id="iconplaycolor">
							</label></p>
							<p><label for="iconplaybgcolor"><?php echo __('Icon play background color'); ?> :
								<input type="text" value="<?php echo $args['iconplaybgcolor']; ?>" class="color" name="iconplaybgcolor" id="iconplaybgcolor">
							</label></p>
							<p><label for="iconplaybgalpha"><?php echo __('Icon play background alpha'); ?> :
								<select id="iconplaybgalpha" name="iconplaybgalpha">
									<option <?php echo $args['iconplaybgalpha']==0? 'selected="selected"':''; ?> value="0">0</option>
									<option <?php echo $args['iconplaybgalpha']==25? 'selected="selected"':''; ?> value="25">25</option>
									<option <?php echo $args['iconplaybgalpha']==50? 'selected="selected"':''; ?> value="50">50</option>
									<option <?php echo $args['iconplaybgalpha']==75? 'selected="selected"':''; ?> value="75">75</option>
									<option <?php echo $args['iconplaybgalpha']==100? 'selected="selected"':''; ?> value="100">100</option>
								</select>
							</label></p>
							<br class="clear">
						</fieldset>
					</div>
				</div>
			</div>
			
			<p class="clear">
				<?php echo $core->formNonce(); ?>
				<input id="flv" type="hidden" value="<?php echo $flv; ?>"/>
		<?php if( !$popup ) { ?>
				<input type="submit" tabindex="33" name="saveconfig" accesskey="s" value="<?php echo __('OK'); ?>" />
		<?php } else { ?>
					<a href="#" class="button" id="flvplayerconfig-cancel"><?php echo __('Undo'); ?></a> - 
					<a href="#" class="button" id="flvplayerconfig-preview"><?php echo __('Preview'); ?></a> - 
				<strong><a href="#" class="button" id="flvplayerconfig-ok"><?php echo __('OK'); ?></a></strong>
		<?php } ?>
			</p>
		
        </form>
	
	<?php dcPage::helpBlock('flvplayerconfig');?>
	
</body>
</html>