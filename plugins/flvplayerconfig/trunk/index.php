<?php if (!defined('DC_CONTEXT_ADMIN')) {return;}
/**
 * @author k�vin lepeltier [lipki] (kevin@lepeltier.info)
 * @license http://creativecommons.org/licenses/by-sa/3.0/deed.fr
 */

$default_tab = 'generale';
if (isset($_REQUEST['tab']))
	$default_tab = $_REQUEST['tab'];

if (!empty($_POST['saveconfig'])) {

	if( !empty($_POST['title']) || $_POST['title'] == 0 ) $args['title'] = htmlspecialchars  ($_POST['title']);
	if( !empty($_POST['startimage']) || $_POST['startimage'] == 0 ) $args['startimage'] = htmlspecialchars  ($_POST['startimage']);
	if( !empty($_POST['width']) || $_POST['width'] == 0 ) $args['width'] = htmlspecialchars  ($_POST['width']);
	if( !empty($_POST['height']) || $_POST['height'] == 0 ) $args['height'] = htmlspecialchars  ($_POST['height']);
	if( !empty($_POST['loop']) || $_POST['loop'] == 0 ) $args['loop'] = $_POST['loop'];
	if( !empty($_POST['autoplay']) || $_POST['autoplay'] == 0 ) $args['autoplay'] = $_POST['autoplay'];
	if( !empty($_POST['autoload']) || $_POST['autoload'] == 0 ) $args['autoload'] = $_POST['autoload'];
	if( !empty($_POST['buffer']) || $_POST['buffer'] == 0 ) $args['buffer'] = $_POST['buffer'];
	if( !empty($_POST['skin']) || $_POST['skin'] == 0 ) $args['skin'] = htmlspecialchars  ($_POST['skin']);
	if( !empty($_POST['margin']) || $_POST['margin'] == 0 ) $args['margin'] = htmlspecialchars  ($_POST['margin']);
	if( !empty($_POST['bgcolor']) || $_POST['bgcolor'] == 0 ) $args['bgcolor'] = htmlspecialchars  ($_POST['bgcolor']);
	if( !empty($_POST['bgcolor1']) || $_POST['bgcolor1'] == 0 ) $args['bgcolor1'] = htmlspecialchars  ($_POST['bgcolor1']);
	if( !empty($_POST['bgcolor2']) || $_POST['bgcolor2'] == 0 ) $args['bgcolor2'] = htmlspecialchars  ($_POST['bgcolor2']);
	if( !empty($_POST['playercolor']) || $_POST['playercolor'] == 0 ) $args['playercolor'] = htmlspecialchars  ($_POST['playercolor']);
	if( !empty($_POST['loadingcolor']) || $_POST['loadingcolor'] == 0 ) $args['loadingcolor'] = htmlspecialchars  ($_POST['loadingcolor']);
	if( !empty($_POST['buttoncolor']) || $_POST['buttoncolor'] == 0 ) $args['buttoncolor'] = htmlspecialchars  ($_POST['buttoncolor']);
	if( !empty($_POST['buttonovercolor']) || $_POST['buttonovercolor'] == 0 ) $args['buttonovercolor'] = htmlspecialchars  ($_POST['buttonovercolor']);
	if( !empty($_POST['slidercolor1']) || $_POST['slidercolor1'] == 0 ) $args['slidercolor1'] = htmlspecialchars  ($_POST['slidercolor1']);
	if( !empty($_POST['slidercolor2']) || $_POST['slidercolor2'] == 0 ) $args['slidercolor2'] = htmlspecialchars  ($_POST['slidercolor2']);
	if( !empty($_POST['sliderovercolor']) || $_POST['sliderovercolor'] == 0 ) $args['sliderovercolor'] = htmlspecialchars  ($_POST['sliderovercolor']);
	if( !empty($_POST['showstop']) || $_POST['showstop'] == 0 ) $args['showstop'] = $_POST['showstop'];
	if( !empty($_POST['showvolume']) || $_POST['showvolume'] == 0 ) $args['showvolume'] = $_POST['showvolume'];
	if( !empty($_POST['showtime']) || $_POST['showtime'] == 0 ) $args['showtime'] = $_POST['showtime'];
	if( !empty($_POST['loadonstop']) || $_POST['loadonstop'] == 0 ) $args['loadonstop'] = $_POST['loadonstop'];
	if( !empty($_POST['phpstream']) || $_POST['phpstream'] == 0 ) $args['phpstream'] = $_POST['phpstream'];

	$core->blog->settings->themes->put('flvplayer_style', serialize($args), 'string', 'flvplayer config');
	http::redirect($p_url.'&tab='.$default_tab.'&saveconfig=1');
}

$args = unserialize($core->blog->settings->themes->flvplayer_style);

if (isset($_GET['saveconfig']))
	$msg = __('Configuration successfully updated.');




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
						<td class="value"><input type="text" value="<?php echo $args['title']; ?>" class="text" name="title" id="title"></td><td class="actions"><a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, 'Le titre affiché avant le chargement de la vidéo')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="startimage">startimage</label></td>
						<td class="value"><input type="text" value="<?php echo $args['startimage']; ?>" class="url" name="startimage" id="startimage"></td><td class="actions"><a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, 'L\'URL du fichier JPEG (non progressif) à afficher avant le chargement de la vidéo')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="width">width</label></td>
						<td class="value"><input type="text" value="<?php echo $args['width']; ?>" class="int" name="width" id="width"></td><td class="actions"><a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, 'Forcer la largeur du lecteur')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="height">height</label></td>
						<td class="value"><input type="text" value="<?php echo $args['height']; ?>" class="int" name="height" id="height"></td><td class="actions"><a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, 'Forcer la hauteur du lecteur')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="loop">loop</label></td>
						<td class="value"><input name="loop" id="loop" type="checkbox" <?php echo $args['loop']? 'checked="checked"':''; ?>/></td><td class="actions"><a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, '&lt;code&gt;1&lt;/code&gt; pour boucler')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="autoplay">autoplay</label></td>
						<td class="value"><input name="autoplay" id="autoplay" type="checkbox" <?php echo $args['autoplay']? 'checked="checked"':''; ?>/></td><td class="actions"><a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, '&lt;code&gt;1&lt;/code&gt; pour lire automatiquement')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="autoload">autoload</label></td>
						<td class="value"><input name="autoload" id="autoload" type="checkbox" <?php echo $args['autoload']? 'checked="checked"':''; ?>/></td><td class="actions"><a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, '&lt;code&gt;1&lt;/code&gt; pour lancer le chargement et afficher la première image de la vidéo')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="srt">srt</label></td>
						<td class="value"><input name="srt" id="srt" type="checkbox" <?php echo $args['srt']? 'checked="checked"':''; ?>/></td><td class="actions"><a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, '&lt;code&gt;1&lt;/code&gt; pour utiliser les sous-titres SRT (le fichier doit être au même endroit que la vidéo et avoir le même nom que le fichier vidéo mais avec l\'extension .srt)')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="buffer">buffer</label></td>
						<td class="value"><select name="buffer" id="buffer">
						<option <?php echo $args['buffer']==5? 'selected="selected"':''; ?> value="5">5</option>
						<option <?php echo $args['buffer']==10? 'selected="selected"':''; ?> value="10">10</option>
						<option <?php echo $args['buffer']==20? 'selected="selected"':''; ?> value="20">20</option>
						<option <?php echo $args['buffer']==30? 'selected="selected"':''; ?> value="30">30</option>
						<option <?php echo $args['buffer']==60? 'selected="selected"':''; ?> value="60">60</option>
						</select></td><td class="actions"><a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, 'Le nombre de secondes pour la mémoire tampon. Par défaut à &lt;code&gt;5&lt;/code&gt;.')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
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
						<td class="value"><input type="text" value="<?php echo $args['skin']; ?>" class="url" name="skin" id="skin"></td><td class="actions"><a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, 'L\'URL du fichier JPEG (non progressif) à charger')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="margin">margin</label></td>
						<td class="value"><input type="text" value="<?php echo $args['margin']; ?>" class="int" name="margin" id="margin"></td><td class="actions"><a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, 'La marge de la vidéo par rapport au Flash (utile pour les skins)')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="bgcolor">bgcolor</label></td>
						<td class="value"><input type="text" value="<?php echo $args['bgcolor']; ?>" class="color" name="bgcolor" id="bgcolor"></td><td class="actions"><a onclick="colorpicker.show(this, 'bgcolor')" href="javascript:void(0)"><img alt="Colopicker" src="index.php?pf=flvplayerconfig/color_wheel.png"></a> <a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, 'La couleur de fond')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="bgcolor1">bgcolor1</label></td>
						<td class="value"><input type="text" value="<?php echo $args['bgcolor1']; ?>" class="color" name="bgcolor1" id="bgcolor1"></td><td class="actions"><a onclick="colorpicker.show(this, 'bgcolor1')" href="javascript:void(0)"><img alt="Colopicker" src="index.php?pf=flvplayerconfig/color_wheel.png"></a> <a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, 'La première couleur du dégradé du fond')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="bgcolor2">bgcolor2</label></td>
						<td class="value"><input type="text" value="<?php echo $args['bgcolor2']; ?>" class="color" name="bgcolor2" id="bgcolor2"></td><td class="actions"><a onclick="colorpicker.show(this, 'bgcolor2')" href="javascript:void(0)"><img alt="Colopicker" src="index.php?pf=flvplayerconfig/color_wheel.png"></a> <a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, 'La seconde couleur du dégradé du fond')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
				    </tbody>
				</table>
			</div>
			 
			<div class="multi-part" id="barredecontrole" title="<?php echo __('Barre de contrôle'); ?>">
				<table class="hidden" summary="Barre de contrôle">
				    <thead>
					<tr><th>Name</th><th>Value</th><th>Actions</th></tr>
				    </thead>
				    <tbody>
					<tr><td class="name"><label for="playercolor">playercolor</label></td>
						<td class="value"><input type="text" value="<?php echo $args['playercolor']; ?>" class="color" name="playercolor" id="playercolor"></td><td class="actions"><a onclick="colorpicker.show(this, 'playercolor')" href="javascript:void(0)"><img alt="Colopicker" src="index.php?pf=flvplayerconfig/color_wheel.png"></a> <a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, 'La couleur du lecteur (pas du flash)')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="loadingcolor">loadingcolor</label></td>
						<td class="value"><input type="text" value="<?php echo $args['loadingcolor']; ?>" class="color" name="loadingcolor" id="loadingcolor"></td><td class="actions"><a onclick="colorpicker.show(this, 'loadingcolor')" href="javascript:void(0)"><img alt="Colopicker" src="index.php?pf=flvplayerconfig/color_wheel.png"></a> <a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, 'La couleur de la barre de chargement')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="buttoncolor">buttoncolor</label></td>
						<td class="value"><input type="text" value="<?php echo $args['buttoncolor']; ?>" class="color" name="buttoncolor" id="buttoncolor"></td><td class="actions"><a onclick="colorpicker.show(this, 'buttoncolor')" href="javascript:void(0)"><img alt="Colopicker" src="index.php?pf=flvplayerconfig/color_wheel.png"></a> <a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, 'La couleur des boutons')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="buttonovercolor">buttonovercolor</label></td>
						<td class="value"><input type="text" value="<?php echo $args['buttonovercolor']; ?>" class="color" name="buttonovercolor" id="buttonovercolor"></td><td class="actions"><a onclick="colorpicker.show(this, 'buttonovercolor')" href="javascript:void(0)"><img alt="Colopicker" src="index.php?pf=flvplayerconfig/color_wheel.png"></a> <a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, 'La couleur des boutons au survol')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="slidercolor1">slidercolor1</label></td>
						<td class="value"><input type="text" value="<?php echo $args['slidercolor1']; ?>" class="color" name="slidercolor1" id="slidercolor1"></td><td class="actions"><a onclick="colorpicker.show(this, 'slidercolor1')" href="javascript:void(0)"><img alt="Colopicker" src="index.php?pf=flvplayerconfig/color_wheel.png"></a> <a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, 'La première couleur du dégradé de la barre')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="slidercolor2">slidercolor2</label></td>
						<td class="value"><input type="text" value="<?php echo $args['slidercolor2']; ?>" class="color" name="slidercolor2" id="slidercolor2"></td><td class="actions"><a onclick="colorpicker.show(this, 'slidercolor2')" href="javascript:void(0)"><img alt="Colopicker" src="index.php?pf=flvplayerconfig/color_wheel.png"></a> <a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, 'La seconde couleur du dégradé de la barre')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="sliderovercolor">sliderovercolor</label></td>
						<td class="value"><input type="text" value="<?php echo $args['sliderovercolor']; ?>" class="color" name="sliderovercolor" id="sliderovercolor"></td><td class="actions"><a onclick="colorpicker.show(this, 'sliderovercolor')" href="javascript:void(0)"><img alt="Colopicker" src="index.php?pf=flvplayerconfig/color_wheel.png"></a> <a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, 'La couleur de la barre au survol')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="showstop">showstop</label></td>
						<td class="value"><input name="showstop" id="showstop" type="checkbox" <?php echo $args['showstop']? 'checked="checked"':''; ?>/></td><td class="actions"><a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, '&lt;code&gt;1&lt;/code&gt; pour afficher le bouton STOP')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="showvolume">showvolume</label></td>
						<td class="value"><input name="showvolume" id="showvolume" type="checkbox" <?php echo $args['showvolume']? 'checked="checked"':''; ?>/></td><td class="actions"><a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, '&lt;code&gt;1&lt;/code&gt; pour afficher le bouton VOLUME')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="showtime">showtime</label></td>
						<td class="value"><input name="showtime" id="showtime" type="checkbox" <?php echo $args['showtime']? 'checked="checked"':''; ?>/></td><td class="actions"><a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, '&lt;code&gt;1&lt;/code&gt; pour afficher le bouton TIME')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
				    </tbody>
				</table>
			</div>
			 
			<div class="multi-part" id="divers" title="<?php echo __('Divers'); ?>">
				<table class="hidden" summary="Divers">
				    <thead>
					<tr><th>Name</th><th>Value</th><th>Actions</th></tr>

				    </thead>
				    <tbody>
					<tr><td class="name"><label for="loadonstop">loadonstop</label></td>
						<td class="value"><input name="loadonstop" id="loadonstop" type="checkbox" <?php echo $args['loadonstop']? 'checked="checked"':''; ?>/></td><td class="actions"><a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, '&lt;code&gt;0&lt;/code&gt; pour arrêter le chargement de la vidéo au STOP')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
					<tr><td class="name"><label for="phpstream">phpstream</label></td>
						<td class="value"><input name="phpstream" id="phpstream" type="checkbox" <?php echo $args['phpstream']? 'checked="checked"':''; ?>/></td><td class="actions"><a onmouseout="tooltip.hide()" onmouseover="tooltip.show(this, '&lt;code&gt;1&lt;/code&gt; pour utiliser un streaming php')" href="javascript:void(0)"><img alt="Help" src="index.php?pf=flvplayerconfig/help.png"></a></td></tr>
				    </tbody>
				</table>
			</div>
			
			<p><input type="submit" name="saveconfig" value="<?php echo __('Valider'); ?>" /></p>
                </form>
            </div>
 
</body>
</html>