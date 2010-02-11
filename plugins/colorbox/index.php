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

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

#test compatibilité des settings

global $plugins_settings_version;

if (!version_compare(DC_VERSION,'2.1.6','<='))
{
	$core->blog->settings->addNamespace('colorbox');
	$plugins_settings_version = $core->blog->settings->colorbox;
} else {
	$core->blog->settings->setNamespace('colorbox');
	$plugins_settings_version = $core->blog->settings;
}

$default_tab = 'tab-1';
 
if (isset($_REQUEST['tab'])) {
	$default_tab = $_REQUEST['tab'];
}


if (!empty($_POST['saveconfiggeneral']))
{
	$url = $core->plugins->moduleRoot('colorbox');
	if (version_compare(DC_VERSION,'2.1.6','<=')){
		$core->blog->settings->setNamespace('colorbox');
	} else {
		$core->blog->settings->addNamespace('colorbox');
	}
	$plugins_settings_version->put('colorbox_enabled',!empty($_POST['colorbox_enabled']),'boolean');
	if (isset($_POST['colorbox_theme'])) {
		$plugins_settings_version->put('colorbox_theme',$_POST['colorbox_theme'],'integer');
	} else {
		$plugins_settings_version->put('colorbox_theme','3','integer');
	}
	
	$core->blog->triggerBlog();
	# redirect to the page, avoid conflicts with old settings
	http::redirect($p_url.'&saveconfig=1');
}

if (!empty($_POST['saveconfigicon']))
{
	$url = $core->plugins->moduleRoot('colorbox');
	if (version_compare(DC_VERSION,'2.1.6','<=')){
		$core->blog->settings->setNamespace('colorbox');
	} else {
		$core->blog->settings->addNamespace('colorbox');
	}
	$plugins_settings_version->put('colorbox_zoom_icon',!empty($_POST['colorbox_zoom_icon']),'boolean');
	$plugins_settings_version->put('colorbox_zoom_icon_permanent',!empty($_POST['colorbox_zoom_icon_permanent']),'boolean');
	$plugins_settings_version->put('colorbox_position',!empty($_POST['colorbox_position']),'boolean');
	
	$core->blog->triggerBlog();
	# redirect to the page, avoid conflicts with old settings
	http::redirect($p_url.'&saveconfig=1&tab=tab-2');
}
?>
<html>
<head>
	<title><?php echo(__('ColorBox')); ?></title>
    <script type="text/javascript">
    $(document).ready(function(){
		
		$("input[type=radio][name=colorbox_theme]").click(function(){
			var p = $(this).attr('value');
			$("img#thumbnail").attr('src','index.php?pf=colorbox/themes/'+p+'/images/thumbnail.jpg');
			
      	});
		
		$("#colorbox_zoom_icon").click(function(){
			if ($("#colorbox_zoom_icon").is(":checked"))
			{
				
			}
			else
			{     
				
				$("#colorbox_zoom_icon_permanent").attr('checked', false);
			}
      	});
   
    });
     </script>
     <style type="text/css">
	#content.with-help #help{width:50%;}
	#content.with-help #help-button {right:50%;}
	</style>
    <?php echo dcPage::jsPageTabs($default_tab); ?>
    
    
</head>
<body>
<?php

global $plugins_settings_version;

#enregistrement des paramètres
if (!empty($_GET['saveconfig'])) {
	echo '<p class="message">'.__('Configuration successfully updated').'</p>';
}
echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('ColorBox').'</h2>';

if ($plugins_settings_version->colorbox_position == true) {
	$left = true;
	$right = false;
} else {
	$left = false;
	$right = true;
}

echo 

'<div class="multi-part" id="tab-1" title="'.__('Modal Window').'">'.

'<form action="'.$p_url.'" method="post">'.

	'<fieldset><legend>'.__('Activation').'</legend>'.
		
		'<p><label class="classic">'.
		form::checkbox('colorbox_enabled','1',$plugins_settings_version->colorbox_enabled).
		__('Enable ColorBox').'</label></p>'.
		
	'</fieldset>'.
	
	'<fieldset><legend>'.__('Theme').'</legend>'.
		'<div class="two-cols clear">'.
			'<div class="col">'.
				'<p class="classic">'.__('Choose your theme for ColorBox:').'</p>';
		
				$colorbox_theme = array(
				'1' => __("Dark Mac"),
				'2' => __("Simple White"),
				'3' => __("Lightbox Classic"),
				'4' => __("White Mac"),
				'5' => __("Thick Grey"),
				);
				foreach ($colorbox_theme as $k => $v) {
					echo '<p><label class="classic">'.
					form::radio(array('colorbox_theme'),$k,$plugins_settings_version->colorbox_theme == $k).' '.$v.'</label></p>';
				}
				$thumb_url = 'index.php?pf=colorbox/themes/'.$plugins_settings_version->colorbox_theme.'/images/thumbnail.jpg';
				
				echo
			'</div>'.
			'<div class="col">'.
			'<p><img id="thumbnail" style="border:1px solid #ccc;" src="'.$thumb_url.'" alt="'.__('Window').'" title="'.__('Window').'" /></p>'.
			
			'</div>'.
		'</div>'.		
	'</fieldset>'.
	
	'<p class="clear"><input type="submit" name="saveconfiggeneral" value="'.__('Save configuration').'" />'.$core->formNonce().'</p>'.
'</form>'.
'</div>'.
'<div class="multi-part" id="tab-2" title="'.__('Zoom Icon').'">'.	
	'<form action="'.$p_url.'" method="post">'.	
		'<fieldset><legend>'.__('Parameters').'</legend>'.
			'<p><label class="classic">'.
			form::checkbox('colorbox_zoom_icon','1',$plugins_settings_version->colorbox_zoom_icon).
			__('Enable zoom icon on thumbnails').'</label></p>'.
			
			
				'<p style="margin-left:1em;"><label class="classic">'.
				form::radio(array('colorbox_position'),true,$left).
				__('on the left').'</label></p>'.
				'<p style="margin-left:1em;"><label class="classic">'.
				form::radio(array('colorbox_position'),false,$right).
				__('on the right').'</label></p>'.
				'<p><label class="classic">'.
				form::checkbox('colorbox_zoom_icon_permanent','1',$plugins_settings_version->colorbox_zoom_icon_permanent).
				__('Always show zoom icon on thumbnails').'</label></p>'.
			
		'</fieldset>'.
		'<p class="clear"><input type="submit" name="saveconfigicon" value="'.__('Save configuration').'" />'.$core->formNonce().'</p>'.
	'</form>'.
	
	
'</div>';
dcPage::helpBlock('colorbox');
?>
</body>
</html>