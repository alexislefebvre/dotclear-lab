<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of ColorBox, a plugin for Dotclear 2.
#
# Copyright (c) 2009 Philippe Amalgame and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

if (!$core->auth->check('admin',$core->blog->id)) { return; }

# Settings compatibility test
if (!version_compare(DC_VERSION,'2.1.6','<=')) {
	$s =& $core->blog->settings->colorbox;
} else {
	$core->blog->settings->setNamespace('colorbox');
	$s =& $core->blog->settings;
}

# Init var
$p_url		= 'plugin.php?p='.basename(dirname(__FILE__));
$default_tab	= isset($_GET['tab']) ? $_GET['tab'] : 'modal';
$themes		= array(
	'1' => __("Dark Mac"),
	'2' => __("Simple White"),
	'3' => __("Lightbox Classic"),
	'4' => __("White Mac"),
	'5' => __("Thick Grey"),
	'6' => __("Vintage Lightbox"),
);

# Saving configurations
if (isset($_POST['save']))
{
	$type = $_POST['type'];
	
	$core->blog->triggerBlog();
	
	if ($type === 'modal')
	{
		$s->put('colorbox_enabled',!empty($_POST['colorbox_enabled']));

		if (isset($_POST['colorbox_theme'])) {
			$s->put('colorbox_theme',$_POST['colorbox_theme']);
		}
		
		http::redirect($p_url.'&upd=1');
	}
	elseif ($type === 'zoom')
	{
		$s->put('colorbox_zoom_icon',!empty($_POST['colorbox_zoom_icon']));
		$s->put('colorbox_zoom_icon_permanent',!empty($_POST['colorbox_zoom_icon_permanent']));
		$s->put('colorbox_position',!empty($_POST['colorbox_position']));

		http::redirect($p_url.'&tab=zoom&upd=2');
	}
	elseif ($type === 'advanced')
	{
		$opts = array(
			'transition' => $_POST['transition'],
			'speed' => !empty($_POST['speed']) ? $_POST['speed'] : '350',
			'title' => $_POST['title'],
			'width' => $_POST['width'],
			'height' => $_POST['height'],
			'innerWidth' => $_POST['innerWidth'],
			'innerHeight' => $_POST['innerHeight'],
			'initialWidth' => !empty($_POST['initialWidth']) ? $_POST['initialWidth'] : '300',
			'initialHeight' => !empty($_POST['initialHeight']) ? $_POST['initialHeight'] : '100',
			'maxWidth' => $_POST['maxWidth'],
			'maxHeight' => $_POST['maxHeight'],
			'scalePhotos' => !empty($_POST['scalePhotos']),
			'scrolling' => !empty($_POST['scrolling']),
			'iframe' => !empty($_POST['iframe']),
			'opacity' => !empty($_POST['opacity']) ? $_POST['opacity'] : '0.85',
			'open' => !empty($_POST['open']),
			'preloading' => !empty($_POST['preloading']),
			'overlayClose' => !empty($_POST['overlayClose']),
			'slideshow' => !empty($_POST['slideshow']),
			'slideshowSpeed' => !empty($_POST['slideshowSpeed']) ? $_POST['slideshowSpeed'] : '2500',
			'slideshowAuto' => !empty($_POST['slideshowAuto']),
			'slideshowStart' => $_POST['slideshowStart'],
			'slideshowStop' => $_POST['slideshowStop'],
			'current' => $_POST['current'],
			'previous' => $_POST['previous'],
			'next' => $_POST['next'],
			'close' => $_POST['close'],
			'onOpen' => $_POST['onOpen'],
			'onLoad' => $_POST['onLoad'],
			'onComplete' => $_POST['onComplete'],
			'onCleanup' => $_POST['onCleanup'],
			'onClosed' => $_POST['onClosed']
		);
		
		$s->put('colorbox_advanced',serialize($opts));
		$s->put('colorbox_selectors',$_POST['colorbox_selectors']);
		
		http::redirect($p_url.'&tab=advanced&upd=3');
	}
}

?>

<html>
<head>
	<title><?php echo(__('ColorBox')); ?></title>
	<script type="text/javascript">
	$(document).ready(function() {
		$("input[type=radio][name=colorbox_theme]").click(function() {
			var p = $(this).attr('value');
			$("img#thumbnail").attr('src','index.php?pf=colorbox/themes/'+p+'/images/thumbnail.jpg');
		});
		$("#colorbox_zoom_icon").click(function() {
			if (!$("#colorbox_zoom_icon").is(":checked")) {
				$("#colorbox_zoom_icon_permanent").attr('checked', false);
			}
		});
	});
	</script>
	<?php echo dcPage::jsPageTabs($default_tab); ?>
	<style type="text/css">
	#content.with-help #help{ width:40%; }
	#content.with-help #help-button {right:40%; }
	#thumbnail { border: 1px solid #ccc; }
	</style>
</head>
<body>

<?php

# Display messages
if (isset($_GET['upd']))
{
	$p_msg = '<p class="message">%s</p>';
	
	$a_msg = array(
		__('Modal window configuration successfully saved'),
		__('Zoom icon configuration successfully saved'),
		__('Advanced configuration successfully saved')
	);
	
	$k = (integer) $_GET['upd']-1;
	
	if (array_key_exists($k,$a_msg)) {
		echo sprintf($p_msg,$a_msg[$k]);
	}
}

# Baseline
echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('ColorBox').'</h2>';

# Modal tab
$theme_choice = '';
foreach ($themes as $k => $v) {
	$theme_choice .= '<p><label class="classic">'.
	form::radio(array('colorbox_theme'),$k,$s->colorbox_theme == $k).
	' '.$v.'</label></p>';
}
$thumb_url = 'index.php?pf=colorbox/themes/'.$s->colorbox_theme.'/images/thumbnail.jpg';

echo
'<div class="multi-part" id="modal" title="'.__('Modal Window').'">'.
	'<form action="'.$p_url.'" method="post">'.
	'<fieldset><legend>'.__('Activation').'</legend>'.
		'<p><label class="classic">'.
		form::checkbox('colorbox_enabled','1',$s->colorbox_enabled).
		__('Enable ColorBox').'</label></p>'.
	'</fieldset>'.
	'<fieldset><legend>'.__('Theme').'</legend>'.
		'<div class="two-cols clear">'.
			'<div class="col">'.
				'<p class="classic">'.__('Choose your theme for ColorBox:').'</p>'.
				$theme_choice.
			'</div>'.
			'<div class="col">'.
			'<p><img id="thumbnail" src="'.$thumb_url.'" alt="'.__('Window').'" title="'.__('Window').'" /></p>'.
			'</div>'.
		'</div>'.
	'</fieldset>'.
	'<p>'.form::hidden(array('type'),'modal').'</p>'.
	'<p class="clear"><input type="submit" name="save" value="'.__('Save configuration').'" />'.$core->formNonce().'</p>'.
'</form>'.
'</div>';

# Zoom tab
if ($s->colorbox_position == true) {
	$left = true;
	$right = false;
} else {
	$left = false;
	$right = true;
}

echo
'<div class="multi-part" id="zoom" title="'.__('Zoom Icon').'">'.
	'<form action="'.$p_url.'" method="post">'.
		'<fieldset><legend>'.__('Parameters').'</legend>'.
			'<p><label class="classic">'.
			form::checkbox('colorbox_zoom_icon','1',$s->colorbox_zoom_icon).
			__('Enable zoom icon on thumbnails').'</label></p>'.
				'<p style="margin-left:1em;"><label class="classic">'.
				form::radio(array('colorbox_position'),true,$left).
				__('on the left').'</label></p>'.
				'<p style="margin-left:1em;"><label class="classic">'.
				form::radio(array('colorbox_position'),false,$right).
				__('on the right').'</label></p>'.
				'<p><label class="classic">'.
				form::checkbox('colorbox_zoom_icon_permanent','1',$s->colorbox_zoom_icon_permanent).
				__('Always show zoom icon on thumbnails').'</label></p>'.
		'</fieldset>'.
		'<p>'.form::hidden(array('type'),'zoom').'</p>'.
		'<p class="clear"><input type="submit" name="save" value="'.__('Save configuration').'" />'.$core->formNonce().'</p>'.
	'</form>'.
'</div>';

# Advanced tab
$effects = array(
	__('Elastic') => 'elastic',
	__('Fade') => 'fade',
	__('None') => 'none'
);
$as = unserialize($s->colorbox_advanced);
echo
'<div class="multi-part" id="advanced" title="'.__('Advanced configuration').'">'.
	'<form action="'.$p_url.'" method="post">'.
		'<fieldset><legend>'.__('Selectors').'</legend>'.
			'<p><label>'.__('Apply ColorBox to the following supplementary selectors (ex: div#sidebar,div#pictures):').
			form::field('colorbox_selectors',60,255,$s->colorbox_selectors).
			'</label></p>'.
			'<p class="form-note">'.__('Leave blank to default: (div.post)').'</p>'.
		'</fieldset>'.
		'<fieldset><legend>'.__('Effects').'</legend>'.
		'<div class="two-cols"><div class="col">'.
			'<p class="field"><label class="classic">'.__('Transition type').'&nbsp;'.
			form::combo('transition',$effects,$as['transition']).
			'</label></p>'.
			'<p class="field"><label class="classic">'.__('Transition speed').'&nbsp;'.
			form::field('speed',30,10,$as['speed']).
			'</label></p>'.
			'<p class="field"><label class="classic">'.__('Opacity').'&nbsp;'.
			form::field('opacity',30,10,$as['opacity']).
			'</label></p>'.
			'<p class="field"><label class="classic">'.
			form::checkbox('open',1,$as['open']).
			__('Auto open ColorBox').'</label></p>'.
			'<p class="field"><label class="classic">'.
			form::checkbox('preloading',1,$as['preloading']).
			__('Enable preloading for photo group').'</label></p>'.
			'<p class="field"><label class="classic">'.
			form::checkbox('overlayClose',1,$as['overlayClose']).
			__('Enable close by clicking on overlay').'</label></p>'.
		'</div><div class="col">'.
			'<p class="field"><label class="classic">'.
			form::checkbox('slideshow',1,$as['slideshow']).
			__('Enable slideshow').'</label></p>'.
			'<p class="field"><label class="classic">'.
			form::checkbox('slideshowAuto',1,$as['slideshowAuto']).
			__('Auto start slideshow').'</label></p>'.
			'<p class="field"><label class="classic">'.__('Slideshow speed').'&nbsp;'.
			form::field('slideshowSpeed',30,10,$as['slideshowSpeed']).
			'</label></p>'.
			'<p class="field"><label class="classic">'.__('Slideshow start display text').'&nbsp;'.
			form::field('slideshowStart',30,255,$as['slideshowStart']).
			'</label></p>'.
			'<p class="field"><label class="classic">'.__('Slideshow stop display text').'&nbsp;'.
			form::field('slideshowStop',30,255,$as['slideshowStop']).
			'</label></p>'.
		'</div></div>'.
		'</fieldset>'.
		'<fieldset><legend>'.__('Modal window').'</legend>'.
		'<div class="two-cols"><div class="col">'.
			'<p class="field"><label class="classic">'.__('Default title').'&nbsp;'.
			form::field('title',30,255,$as['title']).
			'</label></p>'.
			'<p class="field"><label class="classic">'.__('Current text').'&nbsp;'.
			form::field('current',30,255,$as['current']).
			'</label></p>'.
			'<p class="field"><label class="classic">'.__('Previous text').'&nbsp;'.
			form::field('previous',30,255,$as['previous']).
			'</label></p>'.
			'<p class="field"><label class="classic">'.__('Next text').'&nbsp;'.
			form::field('next',30,255,$as['next']).
			'</label></p>'.
			'<p class="field"><label class="classic">'.__('Close text').'&nbsp;'.
			form::field('close',30,255,$as['close']).
			'</label></p>'.
			'<p class="field"><label class="classic">'.
			form::checkbox('iframe',1,$as['iframe']).
			__('Display content in  an iframe').'</label></p>'.
		'</div></div>'.
		'</fieldset>'.
		'<fieldset><legend>'.__('Dimensions').'</legend>'.
		'<div class="two-cols"><div class="col">'.
			'<p class="field"><label class="classic">'.__('Fixed width').'&nbsp;'.
			form::field('width',30,10,$as['width']).
			'</label></p>'.
			'<p class="field"><label class="classic">'.__('Fixed height').'&nbsp;'.
			form::field('height',30,10,$as['height']).
			'</label></p>'.
			'<p class="field"><label class="classic">'.__('Fixed inner width').'&nbsp;'.
			form::field('innerWidth',30,10,$as['innerWidth']).
			'</label></p>'.
			'<p class="field"><label class="classic">'.__('Fixed inner height').'&nbsp;'.
			form::field('innerHeight',30,10,$as['innerHeight']).
			'</label></p>'.
			'<p class="field"><label class="classic">'.
			form::checkbox('scalePhotos',1,$as['scalePhotos']).
			__('Scale photos').'</label></p>'.
			'<p class="field"><label class="classic">'.
			form::checkbox('scrolling',1,$as['scrolling']).
			__('Hide overflowing content').'</label></p>'.
		'</div><div class="col">'.
			'<p class="field"><label class="classic">'.__('Initial width').'&nbsp;'.
			form::field('initialWidth',30,10,$as['initialWidth']).
			'</label></p>'.
			'<p class="field"><label class="classic">'.__('Initial height').'&nbsp;'.
			form::field('initialHeight',30,10,$as['initialHeight']).
			'</label></p>'.
			'<p class="field"><label class="classic">'.__('Max width').'&nbsp;'.
			form::field('maxWidth',30,10,$as['maxWidth']).
			'</label></p>'.
			'<p class="field"><label class="classic">'.__('Max height').'&nbsp;'.
			form::field('maxHeight',30,10,$as['maxHeight']).
			'</label></p>'.
		'</div></div>'.
		'</fieldset>'.
		'<fieldset><legend>'.__('Javascript').'</legend>'.
		'<div class="two-cols"><div class="col">'.
			'<p class="field"><label class="classic">'.__('Callback name for onOpen event').'&nbsp;'.
			form::field('onOpen',30,255,$as['onOpen']).
			'</label></p>'.
			'<p class="field"><label class="classic">'.__('Callback name for onLoad event').'&nbsp;'.
			form::field('onLoad',30,255,$as['onLoad']).
			'</label></p>'.
			'<p class="field"><label class="classic">'.__('Callback name for onComplete event').'&nbsp;'.
			form::field('onComplete',30,255,$as['onComplete']).
			'</label></p>'.
		'</div><div class="col">'.
			'<p class="field"><label class="classic">'.__('Callback name for onCleanup event').'&nbsp;'.
			form::field('onCleanup',30,255,$as['onCleanup']).
			'</label></p>'.
			'<p class="field"><label class="classic">'.__('Callback name for onClosed event').'&nbsp;'.
			form::field('onClosed',30,255,$as['onClosed']).
			'</label></p>'.
		'</div></div>'.
		'</fieldset>'.
		'<p>'.form::hidden(array('type'),'advanced').'</p>'.
		'<p class="clear"><input type="submit" name="save" value="'.__('Save configuration').'" />'.$core->formNonce().'</p>'.
	'</form>'.
'</div>';

dcPage::helpBlock('colorbox');

?>

</body>
</html>