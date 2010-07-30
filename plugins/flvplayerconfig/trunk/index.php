<?php if (!defined('DC_CONTEXT_ADMIN')) {return;}
/**
 * @author kévin lepeltier [lipki] (kevin@lepeltier.info)
 * @license http://creativecommons.org/licenses/by-sa/3.0/deed.fr
 */

$default_tab = 'generale';
if (isset($_REQUEST['tab']))
	$default_tab = $_REQUEST['tab'];

if (!empty($_POST['saveconfig'])) {
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
	if( !empty($_POST['width']) || $_POST['width'] == 0 ) if( $_POST['width'] != 320 ) $args['width'] = $_POST['width'];
	if( !empty($_POST['height']) || $_POST['height'] == 0 ) if( $_POST['height'] != 240 ) $args['height'] = $_POST['height'];
	if( !empty($_POST['volume']) || $_POST['volume'] == 0 ) if( $_POST['volume'] != 100 ) $args['volume'] = $_POST['volume'];
	if( !empty($_POST['skin']) )  $args['skin'] = htmlspecialchars  ($_POST['skin']);
	if( !empty($_POST['margin']) || $_POST['margin'] == 0 ) if( $_POST['margin'] != 5 ) $args['margin'] = $_POST['margin'];
	if( !empty($_POST['bgcolor']) || $_POST['bgcolor'] == "" ) if( $_POST['bgcolor'] != "ffffff" ) $args['bgcolor'] = htmlspecialchars  ($_POST['bgcolor']);
	if( !empty($_POST['bgcolor1']) || $_POST['bgcolor1'] == "" ) if( $_POST['bgcolor1'] != "7c7c7c" ) $args['bgcolor1'] = htmlspecialchars  ($_POST['bgcolor1']);
	if( !empty($_POST['bgcolor2']) || $_POST['bgcolor2'] == "" ) if( $_POST['bgcolor2'] != "333333" ) $args['bgcolor2'] = htmlspecialchars  ($_POST['bgcolor2']);
	if( !empty($_POST['showtime']) || $_POST['showtime'] == 0 ) if( $_POST['showtime'] != 0 ) $args['showtime'] = $_POST['showtime'];
	if( !empty($_POST['showplayer']) || $_POST['showplayer'] == "" ) if( $_POST['showplayer'] != "autohide" ) $args['showplayer'] = htmlspecialchars  ($_POST['showplayer']);
	if( !empty($_POST['showloading']) || $_POST['showloading'] == "" ) if( $_POST['showloading'] != "autohide" ) $args['showloading'] = htmlspecialchars  ($_POST['showloading']);
	if( !empty($_POST['playertimeout']) || $_POST['playertimeout'] == 0 ) if( $_POST['playertimeout'] != 1500 ) $args['playertimeout'] = $_POST['playertimeout'];
	if( !empty($_POST['playercolor']) || $_POST['playercolor'] == "" ) if( $_POST['playercolor'] != "000000" ) $args['playercolor'] = htmlspecialchars  ($_POST['playercolor']);
	if( !empty($_POST['playeralpha']) || $_POST['playeralpha'] == 0 ) if( $_POST['playeralpha'] != 100 ) $args['playeralpha'] = $_POST['playeralpha'];
	if( !empty($_POST['loadingcolor']) || $_POST['loadingcolor'] == "" ) if( $_POST['loadingcolor'] != "ffff00" ) $args['loadingcolor'] = htmlspecialchars  ($_POST['loadingcolor']);
	if( !empty($_POST['buttoncolor']) || $_POST['buttoncolor'] == "" ) if( $_POST['buttoncolor'] != "ffffff" ) $args['buttoncolor'] = htmlspecialchars  ($_POST['buttoncolor']);
	if( !empty($_POST['buttonovercolor']) || $_POST['buttonovercolor'] == "" ) if( $_POST['buttonovercolor'] != "ffff00" ) $args['buttonovercolor'] = htmlspecialchars  ($_POST['buttonovercolor']);
	if( !empty($_POST['slidercolor1']) || $_POST['slidercolor1'] == "" ) if( $_POST['slidercolor1'] != "cccccc" ) $args['slidercolor1'] = htmlspecialchars  ($_POST['slidercolor1']);
	if( !empty($_POST['slidercolor2']) || $_POST['slidercolor2'] == "" ) if( $_POST['slidercolor2'] != "888888" ) $args['slidercolor2'] = htmlspecialchars  ($_POST['slidercolor2']);
	if( !empty($_POST['sliderovercolor']) || $_POST['sliderovercolor'] == "" ) if( $_POST['sliderovercolor'] != "ffff00" ) $args['sliderovercolor'] = htmlspecialchars  ($_POST['sliderovercolor']);
	if( !empty($_POST['buffer']) || $_POST['buffer'] == 0 ) if( $_POST['buffer'] != 5 ) $args['buffer'] = $_POST['buffer'];
	if( !empty($_POST['buffermessage']) || $_POST['buffermessage'] == "" ) if( $_POST['buffermessage'] != "Buffering _n_" ) $args['buffermessage'] = htmlspecialchars  ($_POST['buffermessage']);
	if( !empty($_POST['buffercolor']) || $_POST['buffercolor'] == "" ) if( $_POST['buffercolor'] != "ffffff" ) $args['buffercolor'] = htmlspecialchars  ($_POST['buffercolor']);
	if( !empty($_POST['bufferbgcolor']) || $_POST['bufferbgcolor'] == "" ) if( $_POST['bufferbgcolor'] != "000000" ) $args['bufferbgcolor'] = htmlspecialchars  ($_POST['bufferbgcolor']);
	if( !empty($_POST['titlecolor']) || $_POST['titlecolor'] == "" ) if( $_POST['titlecolor'] != "ffffff" ) $args['titlecolor'] = htmlspecialchars  ($_POST['titlecolor']);
	if( !empty($_POST['titlesize']) || $_POST['titlesize'] == 0 ) if( $_POST['titlesize'] != 20 ) $args['titlesize'] = $_POST['titlesize'];
	if( !empty($_POST['srtcolor']) || $_POST['srtcolor'] == "" ) if( $_POST['srtcolor'] != "ffffff" ) $args['srtcolor'] = htmlspecialchars  ($_POST['srtcolor']);
	if( !empty($_POST['srtbgcolor']) || $_POST['srtbgcolor'] == "" ) if( $_POST['srtbgcolor'] != "000000" ) $args['srtbgcolor'] = htmlspecialchars  ($_POST['srtbgcolor']);
	if( !empty($_POST['srtsize']) || $_POST['srtsize'] == 0 ) if( $_POST['srtsize'] != 11 ) $args['srtsize'] = $_POST['srtsize'];
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
	if( !empty($_POST['iconplaybgalpha']) || $_POST['iconplaybgalpha'] == 0 ) if( $_POST['iconplaybgalpha'] != 75 ) $args['iconplaybgalpha'] = $_POST['iconplaybgalpha'];
	if( !empty($_POST['showmouse']) || $_POST['showmouse'] == "" ) if( $_POST['showmouse'] != "always" ) $args['showmouse'] = htmlspecialchars  ($_POST['showmouse']);
	if( !empty($_POST['videobgcolor']) || $_POST['videobgcolor'] == "" ) if( $_POST['videobgcolor'] != "000000" ) $args['videobgcolor'] = htmlspecialchars  ($_POST['videobgcolor']);
	if( !empty($_POST['netconnection']) )  $args['netconnection'] = htmlspecialchars  ($_POST['netconnection']);
	
	$core->blog->settings->themes->put('flvplayer_style', serialize($args), 'string', 'flvplayer config');
	http::redirect($p_url.'&tab='.$default_tab.'&saveconfig=1');
}

$args = unserialize($core->blog->settings->themes->flvplayer_style);



foreach( $args as $key => $val )
	$FlashVars[] = $key.'='.$val;
$FlashVars[] = 'flv=http://44.toopi.info/public/3d_divers/D2D2_1.flv';
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
	<title><?php echo(__('FLV Player Config')); ?></title>
	<?php echo dcPage::jsPageTabs($default_tab); ?>
	<?php echo dcPage::jsLoad('index.php?pf=flvplayerconfig/js/generator.js'); ?>
	<link rel="stylesheet" href="index.php?pf=flvplayerconfig/style.css" type="text/css" />
</head>
<body>
 
	<h2><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.__('FLV Player Config'); ?></h2> 
	
	<?php if (!empty($msg)) {echo '<p class="message">'.$msg.'</p>';} ?>

	<div id="player">
		<object width="<?php echo $args['width']; ?>" height="<?php echo $args['height']; ?>" style="text-align: center;" type="application/x-shockwave-flash" data="index.php?pf=player_flv.swf"><br>
			<param name="movie" value="index.php?pf=player_flv.swf"><br>
			<param name="wmode" value="transparent"><br>
			<param name="allowFullScreen" value="true"><br>
			<param name="FlashVars" value="<?php echo $FlashVars; ?>"><br>
		</object>
	</div>


	<div id="generator">
                
                <form method="post" action="<?php echo($p_url); ?>">
			<?php echo $core->formNonce(); ?>
			
			<div class="multi-part" id="generale" title="<?php echo __('Générale'); ?>">
				<table class="visible" summary="Générale">
				    <thead>
					<tr><th>Name</th><th>Value</th><th>Actions</th></tr>
				    </thead>
				    <tbody>
					<tr><td class="name"><label for="title">title</label></td>
						<td class="value"><input type="text" value="<?php echo $args['text']; ?>" class="text" name="text" id="text"></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'Le titre affiché avant le chargement de la vidéo')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="startimage">startimage</label></td>
						<td class="value"><input type="text" value="<?php echo $args['startimage']; ?>" class="url" name="startimage" id="startimage"></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'L\'URL du fichier JPEG (non progressif) à afficher avant le chargement de la vidéo')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="width">width</label></td>
						<td class="value"><input type="text" value="<?php echo $args['width']; ?>" class="int" name="width" id="width"></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'Forcer la largeur du lecteur')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="height">height</label></td>
						<td class="value"><input type="text" value="<?php echo $args['height']; ?>" class="int" name="height" id="height"></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'Forcer la hauteur du lecteur')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="loop">loop</label></td>
						<td class="value"><input name="loop" id="loop" type="checkbox" <?php echo $args['loop']? 'checked="checked"':''; ?>/></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, '&lt;code&gt;1&lt;/code&gt; pour boucler')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="autoplay">autoplay</label></td>
						<td class="value"><input name="autoplay" id="autoplay" type="checkbox" <?php echo $args['autoplay']? 'checked="checked"':''; ?>/></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, '&lt;code&gt;1&lt;/code&gt; pour lire automatiquement')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="autoload">autoload</label></td>
						<td class="value"><input name="autoload" id="autoload" type="checkbox" <?php echo $args['autoload']? 'checked="checked"':''; ?>/></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, '&lt;code&gt;1&lt;/code&gt; pour lancer le chargement et afficher la première image de la vidéo')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="volume">volume</label></td>
						<td class="value"><select id="volume" name="volume">
						<option <?php echo $args['volume']==0? 'selected="selected"':''; ?> value="0">0</option>
						<option <?php echo $args['volume']==25? 'selected="selected"':''; ?> value="25">25</option>
						<option <?php echo $args['volume']==50? 'selected="selected"':''; ?> value="50">50</option>
						<option <?php echo $args['volume']==75? 'selected="selected"':''; ?> value="75">75</option>
						<option <?php echo $args['volume']==100? 'selected="selected"':''; ?> value="100">100</option>
						<option <?php echo $args['volume']==125? 'selected="selected"':''; ?> value="125">125</option>
						<option <?php echo $args['volume']==150? 'selected="selected"':''; ?> value="150">150</option>
						<option <?php echo $args['volume']==175? 'selected="selected"':''; ?> value="175">175</option>
						<option <?php echo $args['volume']==200? 'selected="selected"':''; ?> value="200">200</option>
						</select><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'Le volume initial, entre &lt;code&gt;0&lt;/code&gt; et &lt;code&gt;200&lt;/code&gt;.')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
				    </tbody>
				</table>
				<br/>
				<h3><?php echo __('Divers'); ?></h3>
				<table class="visible" summary="Divers">
				    <thead>
					<tr><th>Name</th><th>Value</th><th>Actions</th></tr>
				    </thead>
				    <tbody>
					<tr><td class="name"><label for="showmouse">showmouse</label></td>
						<td class="value"><select id="showmouse" name="showmouse">
						<option <?php echo $args['showmouse']=='autohide'? 'selected="selected"':''; ?> value="autohide">autohide</option>
						<option <?php echo $args['showmouse']=='always'? 'selected="selected"':''; ?> value="always">always</option>
						<option <?php echo $args['showmouse']=='never'? 'selected="selected"':''; ?> value="never">never</option>
						</select></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'Affichage de la souris : &lt;code&gt;always&lt;/code&gt;, &lt;code&gt;autohide&lt;/code&gt;, &lt;code&gt;never&lt;/code&gt;.')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="videobgcolor">videobgcolor</label></td>
						<td class="value"><input type="text" value="<?php echo $args['videobgcolor']; ?>" class="color" name="videobgcolor" id="videobgcolor"></td><td class="actions"><a href="javascript:void(0)" onclick="colorpicker.show(this, 'videobgcolor')"><img src="index.php?pf=flvplayerconfig/color_wheel.png" alt="Colopicker"></a> <a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La couleur du fond de la vidéo quand il n\'y a pas de vidéo.')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="loadonstop">loadonstop</label></td>
						<td class="value"><input name="loadonstop" id="loadonstop" type="checkbox" <?php echo $args['loadonstop']? 'checked="checked"':''; ?>/></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, '&lt;code&gt;0&lt;/code&gt; pour arrêter le chargement de la vidéo au STOP')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="phpstream">phpstream</label></td>
						<td class="value"><input name="phpstream" id="phpstream" type="checkbox" <?php echo $args['phpstream']? 'checked="checked"':''; ?>/></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, '&lt;code&gt;1&lt;/code&gt; pour utiliser un streaming php')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="shortcut">shortcut</label></td>
						<td class="value"><input name="shortcut" id="shortcut" type="checkbox" <?php echo $args['shortcut']? 'checked="checked"':''; ?>/></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, '&lt;code&gt;0&lt;/code&gt; pour désactiver les raccourcis clavier.')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="netconnection">netconnection</label></td>
						<td class="value"><input type="text" value="<?php echo $args['netconnection']; ?>" class="text" name="netconnection" id="netconnection"></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'L\'URL du serveur RTMP')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="showtitleandstartimage">showtitleandstartimage</label></td>
						<td class="value"><input name="showtitleandstartimage" id="showtitleandstartimage" type="checkbox" <?php echo $args['showtitleandstartimage']? 'checked="checked"':''; ?>/></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, '&lt;code&gt;1&lt;/code&gt; pour afficher le titre et l\'image de départ en même temps.')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
				    </tbody>
				</table>
			</div>
			 
			<div class="multi-part" id="bordure" title="<?php echo __('Bordure'); ?>">
				<table class="hidden" summary="Bordure">
				    <thead>
					<tr><th>Name</th><th>Value</th><th>Actions</th></tr>
				    </thead>
				    <tbody>
					<tr><td class="name"><label for="skin">skin</label></td>
						<td class="value"><input type="text" value="<?php echo $args['skin']; ?>" class="url" name="skin" id="skin"></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'L\'URL du fichier JPEG (non progressif) à charger')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="margin">margin</label></td>
						<td class="value"><input type="text" value="<?php echo $args['margin']; ?>" class="int" name="margin" id="margin"></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La marge de la vidéo par rapport au Flash (utile pour les skins)')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="bgcolor">bgcolor</label></td>
						<td class="value"><input type="text" value="<?php echo $args['bgcolor']; ?>" class="color" name="bgcolor" id="bgcolor"></td><td class="actions"><a href="javascript:void(0)" onclick="colorpicker.show(this, 'bgcolor')"><img src="index.php?pf=flvplayerconfig/color_wheel.png" alt="Colopicker"></a> <a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La couleur de fond')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="bgcolor1">bgcolor1</label></td>
						<td class="value"><input type="text" value="<?php echo $args['bgcolor1']; ?>" class="color" name="bgcolor1" id="bgcolor1"></td><td class="actions"><a href="javascript:void(0)" onclick="colorpicker.show(this, 'bgcolor1')"><img src="index.php?pf=flvplayerconfig/color_wheel.png" alt="Colopicker"></a> <a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La première couleur du dégradé du fond')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="bgcolor2">bgcolor2</label></td>
						<td class="value"><input type="text" value="<?php echo $args['bgcolor2']; ?>" class="color" name="bgcolor2" id="bgcolor2"></td><td class="actions"><a href="javascript:void(0)" onclick="colorpicker.show(this, 'bgcolor2')"><img src="index.php?pf=flvplayerconfig/color_wheel.png" alt="Colopicker"></a> <a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La seconde couleur du dégradé du fond')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
				   </tbody>
				</table>
			</div>
			 
			<div class="multi-part" id="barredecontrole" title="<?php echo __('Barre de contrôle'); ?>">
				<table class="hidden" summary="Barre de contrôle">
				    <thead>
					<tr><th>Name</th><th>Value</th><th>Actions</th></tr>
				    </thead>
				    <tbody>
					<tr><td class="name"><label for="showstop">showstop</label></td>
						<td class="value"><input name="showstop" id="showstop" type="checkbox" <?php echo $args['showstop']? 'checked="checked"':''; ?>/></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, '&lt;code&gt;1&lt;/code&gt; pour afficher le bouton STOP')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="showvolume">showvolume</label></td>
						<td class="value"><input name="showvolume" id="showvolume" type="checkbox" <?php echo $args['showvolume']? 'checked="checked"':''; ?>/></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, '&lt;code&gt;1&lt;/code&gt; pour afficher le bouton VOLUME')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="showtime">showtime</label></td>
						<td class="value"><select id="showtime" name="showtime">
						<option <?php echo $args['showtime']==0? 'selected="selected"':''; ?> value="0">0</option>
						<option <?php echo $args['showtime']==1? 'selected="selected"':''; ?> value="1">1</option>
						<option <?php echo $args['showtime']==2? 'selected="selected"':''; ?> value="2">2</option>
						</select></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, '&lt;code&gt;1&lt;/code&gt; pour afficher le bouton TIME, &lt;code&gt;2&lt;/code&gt; pour l\'afficher avec le temps restant')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="showplayer">showplayer</label></td>
						<td class="value"><select id="showplayer" name="showplayer">
						<option <?php echo $args['showplayer']=='autohide'? 'selected="selected"':''; ?> value="autohide">autohide</option>
						<option <?php echo $args['showplayer']=='always'? 'selected="selected"':''; ?> value="always">always</option>
						<option <?php echo $args['showplayer']=='never'? 'selected="selected"':''; ?> value="never">never</option>
						</select></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'Affichage de la barre des boutons : &lt;code&gt;autohide&lt;/code&gt;, &lt;code&gt;always&lt;/code&gt; ou &lt;code&gt;never&lt;/code&gt;')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="showloading">showloading</label></td>
						<td class="value"><select id="showloading" name="showloading">
						<option <?php echo $args['showloading']=='autohide'? 'selected="selected"':''; ?> value="autohide">autohide</option>
						<option <?php echo $args['showloading']=='always'? 'selected="selected"':''; ?> value="always">always</option>
						<option <?php echo $args['showloading']=='never'? 'selected="selected"':''; ?> value="never">never</option>
						</select></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'Affichage du chargement : &lt;code&gt;autohide&lt;/code&gt;, &lt;code&gt;always&lt;/code&gt; ou &lt;code&gt;never&lt;/code&gt;')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="showfullscreen">showfullscreen</label></td>
						<td class="value"><input name="showfullscreen" id="showfullscreen" type="checkbox" <?php echo $args['showfullscreen']? 'checked="checked"':''; ?>/></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, '&lt;code&gt;1&lt;/code&gt; pour afficher le bouton pour le plein écran (nécessite Flash Player 9.0.16.60 ou supérieur)')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="showswitchsubtitles">showswitchsubtitles</label></td>
						<td class="value"><input name="showswitchsubtitles" id="showswitchsubtitles" type="checkbox" <?php echo $args['showswitchsubtitles']? 'checked="checked"':''; ?>/></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, '&lt;code&gt;1&lt;/code&gt; pour afficher le bouton qui affiche/cache les sous-titres')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="playertimeout">playertimeout</label></td>
						<td class="value"><input type="text" value="<?php echo $args['playertimeout']; ?>" class="int" name="playertimeout" id="playertimeout"></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'Le délai en milliseconde avant que le lecteur se cache (quand il est en mode &lt;code&gt;autohide&lt;/code&gt; bien sûr. Par défaut à &lt;code&gt;1500&lt;/code&gt;.')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
				    </tbody>
				</table>
				<br/>
				<h3><?php echo __('Couleurs'); ?></h3>
				<table class="visible" summary="Couleurs">
				    <thead>
					<tr><th>Name</th><th>Value</th><th>Actions</th></tr>
				    </thead>
				    <tbody>
					<tr><td class="name"><label for="playercolor">playercolor</label></td>
						<td class="value"><input type="text" value="<?php echo $args['playercolor']; ?>" class="color" name="playercolor" id="playercolor"></td><td class="actions"><a href="javascript:void(0)" onclick="colorpicker.show(this, 'playercolor')"><img src="index.php?pf=flvplayerconfig/color_wheel.png" alt="Colopicker"></a> <a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La couleur du lecteur (pas du flash)')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="playeralpha">playeralpha</label></td>
						<td class="value"><select id="playeralpha" name="playeralpha">
						<option <?php echo $args['playeralpha']==0? 'selected="selected"':''; ?> value="0">0</option>
						<option <?php echo $args['playeralpha']==25? 'selected="selected"':''; ?> value="25">25</option>
						<option <?php echo $args['playeralpha']==50? 'selected="selected"':''; ?> value="50">50</option>
						<option <?php echo $args['playeralpha']==75? 'selected="selected"':''; ?> value="75">75</option>
						<option <?php echo $args['playeralpha']==100? 'selected="selected"':''; ?> value="100">100</option>
						</select></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La transparence du fond du lecteur entre &lt;code&gt;0&lt;/code&gt; et &lt;code&gt;100&lt;/code&gt;.')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="loadingcolor">loadingcolor</label></td>
						<td class="value"><input type="text" value="<?php echo $args['loadingcolor']; ?>" class="color" name="loadingcolor" id="loadingcolor"></td><td class="actions"><a href="javascript:void(0)" onclick="colorpicker.show(this, 'loadingcolor')"><img src="index.php?pf=flvplayerconfig/color_wheel.png" alt="Colopicker"></a> <a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La couleur de la barre de chargement')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="buttoncolor">buttoncolor</label></td>
						<td class="value"><input type="text" value="<?php echo $args['buttoncolor']; ?>" class="color" name="buttoncolor" id="buttoncolor"></td><td class="actions"><a href="javascript:void(0)" onclick="colorpicker.show(this, 'buttoncolor')"><img src="index.php?pf=flvplayerconfig/color_wheel.png" alt="Colopicker"></a> <a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La couleur des boutons')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="buttonovercolor">buttonovercolor</label></td>
						<td class="value"><input type="text" value="<?php echo $args['buttonovercolor']; ?>" class="color" name="buttonovercolor" id="buttonovercolor"></td><td class="actions"><a href="javascript:void(0)" onclick="colorpicker.show(this, 'buttonovercolor')"><img src="index.php?pf=flvplayerconfig/color_wheel.png" alt="Colopicker"></a> <a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La couleur des boutons au survol')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="slidercolor1">slidercolor1</label></td>
						<td class="value"><input type="text" value="<?php echo $args['slidercolor1']; ?>" class="color" name="slidercolor1" id="slidercolor1"></td><td class="actions"><a href="javascript:void(0)" onclick="colorpicker.show(this, 'slidercolor1')"><img src="index.php?pf=flvplayerconfig/color_wheel.png" alt="Colopicker"></a> <a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La première couleur du dégradé de la barre')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="slidercolor2">slidercolor2</label></td>
						<td class="value"><input type="text" value="<?php echo $args['slidercolor2']; ?>" class="color" name="slidercolor2" id="slidercolor2"></td><td class="actions"><a href="javascript:void(0)" onclick="colorpicker.show(this, 'slidercolor2')"><img src="index.php?pf=flvplayerconfig/color_wheel.png" alt="Colopicker"></a> <a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La seconde couleur du dégradé de la barre')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="sliderovercolor">sliderovercolor</label></td>
						<td class="value"><input type="text" value="<?php echo $args['sliderovercolor']; ?>" class="color" name="sliderovercolor" id="sliderovercolor"></td><td class="actions"><a href="javascript:void(0)" onclick="colorpicker.show(this, 'sliderovercolor')"><img src="index.php?pf=flvplayerconfig/color_wheel.png" alt="Colopicker"></a> <a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La couleur de la barre au survol')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>

				    </tbody>
				</table>
			</div>
			 
			<div class="multi-part" id="titre" title="<?php echo __('Titre'); ?>">
				<table class="hidden" summary="Titre">
				    <thead>
					<tr><th>Name</th><th>Value</th><th>Actions</th></tr>
				    </thead>
				    <tbody>
					<tr><td class="name"><label for="titlecolor">titlecolor</label></td>
						<td class="value"><input type="text" value="<?php echo $args['titlecolor']; ?>" class="color" name="titlecolor" id="titlecolor"></td><td class="actions"><a href="javascript:void(0)" onclick="colorpicker.show(this, 'titlecolor')"><img src="index.php?pf=flvplayerconfig/color_wheel.png" alt="Colopicker"></a> <a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La couleur du titre. Par défaut à &lt;code&gt;ffffff&lt;/code&gt;.')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="titlesize">titlesize</label></td>
						<td class="value"><select id="titlesize" name="titlesize">
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
						</select></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La taille de la police du titre. Par défaut à &lt;code&gt;20&lt;/code&gt;.')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					    </tbody>
				</table>
				<br/>
				<h3><?php echo __('Sous-titre'); ?></h3>
				<table class="visible" summary="Sous-titre">
				    <thead>
					<tr><th>Name</th><th>Value</th><th>Actions</th></tr>
				    </thead>
				    <tbody>
					<tr><td class="name"><label for="srt">srt</label></td>
						<td class="value"><input name="srt" id="srt" type="checkbox" <?php echo $args['srt']? 'checked="checked"':''; ?>/></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, '&lt;code&gt;1&lt;/code&gt; pour utiliser les sous-titres SRT (le fichier doit être au même endroit que la vidéo et avoir le même nom que le fichier vidéo mais avec l\'extension .srt)')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="srtcolor">srtcolor</label></td>
						<td class="value"><input type="text" value="<?php echo $args['srtcolor']; ?>" class="color" name="srtcolor" id="srtcolor"></td><td class="actions"><a href="javascript:void(0)" onclick="colorpicker.show(this, 'srtcolor')"><img src="index.php?pf=flvplayerconfig/color_wheel.png" alt="Colopicker"></a> <a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La couleur du texte des sous-titres')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="srtbgcolor">srtbgcolor</label></td>
						<td class="value"><input type="text" value="<?php echo $args['srtbgcolor']; ?>" class="color" name="srtbgcolor" id="srtbgcolor"></td><td class="actions"><a href="javascript:void(0)" onclick="colorpicker.show(this, 'srtbgcolor')"><img src="index.php?pf=flvplayerconfig/color_wheel.png" alt="Colopicker"></a> <a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La couleur de fond des sous-titres')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="srtsize">srtsize</label></td>
						<td class="value"><select id="srtsize" name="srtsize">
						<option <?php echo $args['srtsize']==8? 'selected="selected"':''; ?> value="8">8</option>
						<option <?php echo $args['srtsize']==9? 'selected="selected"':''; ?> value="9">9</option>
						<option <?php echo $args['srtsize']==10? 'selected="selected"':''; ?> value="10">10</option>
						<option <?php echo $args['srtsize']==11? 'selected="selected"':''; ?> value="11">11</option>
						<option <?php echo $args['srtsize']==12? 'selected="selected"':''; ?> value="12">12</option>
						<option <?php echo $args['srtsize']==13? 'selected="selected"':''; ?> value="13">13</option>
						<option <?php echo $args['srtsize']==14? 'selected="selected"':''; ?> value="14">14</option>
						<option <?php echo $args['srtsize']==15? 'selected="selected"':''; ?> value="15">15</option>
						<option <?php echo $args['srtsize']==16? 'selected="selected"':''; ?> value="16">16</option>
						</select></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La taille du texte des sous-titres. Par défaut à &lt;code&gt;11&lt;/code&gt;.')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="srturl">srturl</label></td>
						<td class="value"><input type="text" value="<?php echo $args['srturl']; ?>" class="url" name="srturl" id="srturl"></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'L\'URL du fichier de sous-titres (si on ne veut pas de la détection automatique)')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
				    </tbody>
				</table>
			</div>
			 
			<div class="multi-part" id="technique" title="<?php echo __('Technique'); ?>">
			<h3><?php echo __('Affichage de la mémoire tampon'); ?></h3>
				<table class="hidden" summary="Affichage de la mémoire tampon">
				    <thead>
					<tr><th>Name</th><th>Value</th><th>Actions</th></tr>
				    </thead>
				    <tbody>
					<tr><td class="name"><label for="buffer">buffer</label></td>
					<td class="value"><select id="buffer" name="buffer">
						<option <?php echo $args['buffer']==5? 'selected="selected"':''; ?> value="5">5</option>
						<option <?php echo $args['buffer']==10? 'selected="selected"':''; ?> value="10">10</option>
						<option <?php echo $args['buffer']==20? 'selected="selected"':''; ?> value="20">20</option>
						<option <?php echo $args['buffer']==30? 'selected="selected"':''; ?> value="30">30</option>
						<option <?php echo $args['buffer']==60? 'selected="selected"':''; ?> value="60">60</option>
						</select></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'Le nombre de secondes pour la mémoire tampon. Par défaut à &lt;code&gt;5&lt;/code&gt;.')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="buffermessage">buffermessage</label></td>
						<td class="value"><input type="text" value="<?php echo $args['buffermessage']; ?>" class="text" name="buffermessage" id="buffermessage"></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'Le message de la mémoire tampon. Par défaut à &lt;code&gt;Buffering _n_&lt;/code&gt;, &lt;code&gt;_n_&lt;/code&gt; indiquant le pourcentage.')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="buffercolor">buffercolor</label></td>
						<td class="value"><input type="text" value="<?php echo $args['buffercolor']; ?>" class="color" name="buffercolor" id="buffercolor"></td><td class="actions"><a href="javascript:void(0)" onclick="colorpicker.show(this, 'buffercolor')"><img src="index.php?pf=flvplayerconfig/color_wheel.png" alt="Colopicker"></a> <a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La couleur du texte du message tampon')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="bufferbgcolor">bufferbgcolor</label></td>
						<td class="value"><input type="text" value="<?php echo $args['bufferbgcolor']; ?>" class="color" name="bufferbgcolor" id="bufferbgcolor"></td><td class="actions"><a href="javascript:void(0)" onclick="colorpicker.show(this, 'bufferbgcolor')"><img src="index.php?pf=flvplayerconfig/color_wheel.png" alt="Colopicker"></a> <a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La couleur de fond du message tampon')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="buffershowbg">buffershowbg</label></td>
						<td class="value"><input name="buffershowbg" id="buffershowbg" type="checkbox" <?php echo $args['buffershowbg']? 'checked="checked"':''; ?>/></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, '&lt;code&gt;0&lt;/code&gt; pour ne pas afficher le fond du message tampon')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
				    </tbody>
				</table>
				<br/>
				<h3><?php echo __('Contrôles par la souris'); ?></h3>
				<table class="hidden" summary="Contrôles par la souris">
				    <thead>
					<tr><th>Name</th><th>Value</th><th>Actions</th></tr>
				    </thead>
				    <tbody>
					<tr><td class="name"><label for="onclick">onclick</label></td>
						<td class="value"><input type="text" value="<?php echo $args['onclick']; ?>" class="text" name="onclick" id="onclick"></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'L\'URL de la destination au click sur la vidéo. Par défaut à &lt;code&gt;playpause&lt;/code&gt; qui signifie que la vidéo fait play ou pause au click. Pour ne rien faire, il faut mettre &lt;code&gt;none&lt;/code&gt;.')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="onclicktarget">onclicktarget</label></td>
						<td class="value"><input type="text" value="<?php echo $args['onclicktarget']; ?>" class="text" name="onclicktarget" id="onclicktarget"></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La cible de l\'URL au click sur la vidéo. Par défaut à &lt;code&gt;_self&lt;/code&gt;. Pour ouvrir une nouvelle fenêtre, mettez &lt;code&gt;_blank&lt;/code&gt;.')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="ondoubleclick">ondoubleclick</label></td>
						<td class="value"><input type="text" value="<?php echo $args['ondoubleclick']; ?>" class="text" name="ondoubleclick" id="ondoubleclick"></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'Action sur le double click: &lt;code&gt;none&lt;/code&gt;, &lt;code&gt;fullscreen&lt;/code&gt;, &lt;code&gt;playpause&lt;/code&gt;, ou l\'url à ouvrir.')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="ondoubleclicktarget">ondoubleclicktarget</label></td>
						<td class="value"><input type="text" value="<?php echo $args['ondoubleclicktarget']; ?>" class="text" name="ondoubleclicktarget" id="ondoubleclicktarget"></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La cible de l\'URL au double click sur la vidéo. Par défaut à &lt;code&gt;_self&lt;/code&gt;. Pour ouvrir une nouvelle fenêtre, mettez &lt;code&gt;_blank&lt;/code&gt;.')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
				    </tbody>
				</table>
			</div>
			 
			<div class="multi-part" id="imagespardessuslavideo" title="<?php echo __('Images par dessus la vidéo'); ?>">
				<table class="hidden" summary="Images par dessus la vidéo">
				    <thead>
					<tr><th>Name</th><th>Value</th><th>Actions</th></tr>
				    </thead>
				    <tbody>
					<tr><td class="name"><label for="top1">top1</label></td>
						<td class="value"><input type="text" value="<?php echo $args['top1']; ?>" class="text" name="top1" id="top1"></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'Charger une image par dessus la vidéo et la placer à une coordonnée &lt;code&gt;x&lt;/code&gt; et &lt;code&gt;y&lt;/code&gt; (par exemple &lt;code&gt;url|x|y&lt;/code&gt;)')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="top2">top2</label></td>
						<td class="value"><input type="text" value="<?php echo $args['top2']; ?>" class="text" name="top2" id="top2"></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'Charger une image par dessus la vidéo et la placer à une coordonnée &lt;code&gt;x&lt;/code&gt; et &lt;code&gt;y&lt;/code&gt; (par exemple &lt;code&gt;url|x|y&lt;/code&gt;)')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="top3">top3</label></td>
						<td class="value"><input type="text" value="<?php echo $args['top3']; ?>" class="text" name="top3" id="top3"></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'Charger une image par dessus la vidéo et la placer à une coordonnée &lt;code&gt;x&lt;/code&gt; et &lt;code&gt;y&lt;/code&gt; (par exemple &lt;code&gt;url|x|y&lt;/code&gt;)')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="top4">top4</label></td>
						<td class="value"><input type="text" value="<?php echo $args['top4']; ?>" class="text" name="top4" id="top4"></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'Charger une image par dessus la vidéo et la placer à une coordonnée &lt;code&gt;x&lt;/code&gt; et &lt;code&gt;y&lt;/code&gt; (par exemple &lt;code&gt;url|x|y&lt;/code&gt;)')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="top5">top5</label></td>
						<td class="value"><input type="text" value="<?php echo $args['top5']; ?>" class="text" name="top5" id="top5"></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'Charger une image par dessus la vidéo et la placer à une coordonnée &lt;code&gt;x&lt;/code&gt; et &lt;code&gt;y&lt;/code&gt; (par exemple &lt;code&gt;url|x|y&lt;/code&gt;)')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
				    </tbody>
				</table>
			</div>
			 
			<div class="multi-part" id="lesiconesdelavideo" title="<?php echo __('Les icones de la vidéo'); ?>">
				<table class="hidden" summary="Les icones de la vidéo">
				    <thead>
					<tr><th>Name</th><th>Value</th><th>Actions</th></tr>
				    </thead>
				    <tbody>
					<tr><td class="name"><label for="showiconplay">showiconplay</label></td>
						<td class="value"><input name="showiconplay" id="showiconplay" type="checkbox" <?php echo $args['showiconplay']? 'checked="checked"':''; ?>/></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, '&lt;code&gt;1&lt;/code&gt; pour afficher l\'icone PLAY au milieu de la vidéo.')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="iconplaycolor">iconplaycolor</label></td>
						<td class="value"><input type="text" value="<?php echo $args['iconplaycolor']; ?>" class="color" name="iconplaycolor" id="iconplaycolor"></td><td class="actions"><a href="javascript:void(0)" onclick="colorpicker.show(this, 'iconplaycolor')"><img src="index.php?pf=flvplayerconfig/color_wheel.png" alt="Colopicker"></a> <a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La couleur de l\'icone PLAY.')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="iconplaybgcolor">iconplaybgcolor</label></td>
						<td class="value"><input type="text" value="<?php echo $args['iconplaybgcolor']; ?>" class="color" name="iconplaybgcolor" id="iconplaybgcolor"></td><td class="actions"><a href="javascript:void(0)" onclick="colorpicker.show(this, 'iconplaybgcolor')"><img src="index.php?pf=flvplayerconfig/color_wheel.png" alt="Colopicker"></a> <a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La couleur de fond de l\'icone PLAY.')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
					<tr><td class="name"><label for="iconplaybgalpha">iconplaybgalpha</label></td>
						<td class="value"><select id="iconplaybgalpha" name="iconplaybgalpha">
						<option <?php echo $args['iconplaybgalpha']==0? 'selected="selected"':''; ?> value="0">0</option>
						<option <?php echo $args['iconplaybgalpha']==25? 'selected="selected"':''; ?> value="25">25</option>
						<option <?php echo $args['iconplaybgalpha']==50? 'selected="selected"':''; ?> value="50">50</option>
						<option <?php echo $args['iconplaybgalpha']==75? 'selected="selected"':''; ?> value="75">75</option>
						<option <?php echo $args['iconplaybgalpha']==100? 'selected="selected"':''; ?> value="100">100</option>
						</select></td><td class="actions"><a href="javascript:void(0)" onmouseover="tooltip.show(this, 'La transparence du fond de l\'icone PLAY entre &lt;code&gt;0&lt;/code&gt; et &lt;code&gt;100&lt;/code&gt;.')" onmouseout="tooltip.hide()"><img src="index.php?pf=flvplayerconfig/help.png" alt="Help"></a></td></tr>
				    </tbody>
				</table>
			</div>
			
			<p><input type="submit" name="saveconfig" value="<?php echo __('Valider'); ?>" /></p>
                </form>
            </div>
 
</body>
</html>