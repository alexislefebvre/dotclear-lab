<?php
# ***** BEGIN LICENSE BLOCK *****
# Widget Management for DotClear.
# Copyright (c) 2008 Gerits Aurelien. All rights
# reserved.
#

# Cette création est mise à disposition selon le Contrat Paternité-Pas 
# d'Utilisation Commerciale-Pas de Modification 2.0 Belgique disponible 
# en ligne http://creativecommons.org/licenses/by-nc-nd/2.0/be/ ou par 
# courrier postal à Creative Commons, 171 Second Street, Suite 300, San Francisco, 
# California 94105, USA.

if (!defined('DC_CONTEXT_ADMIN')) { exit; }
require_once(dirname(__FILE__).'/class.adsense.php');
if (!$core->auth->check('admin_adsense',$core->blog->id)) { exit; }
$core->url->register('plugins','plugins','^plugins(/.+)?$',array('pluginsAds'));
$m_version = $core->plugins->moduleInfo('adsense','version');
// Set quick-access variables
$theme = $core->blog->settings->theme;
$themebase = $core->blog->themes_path.'/'. $theme .'/'.'tpl/';
$default = $core->blog->themes_path.'/'.'default'.'/'.'tpl/';
$file = 'post.html';
$int_error= '<p><img src="index.php?pf=adsense/img/message-warn.png" width="16" height="16" />'.'<span style="color:red; font-weight:800;">'
		.__('google_ad_client is empty').'</span>'.'</p>';
		
if (!empty($_POST['sendtheme']))
	{
		try
		{
			# Style of ThemeSwitcher
			$configtheme = $_POST['configtheme'];
			files::putContent($themebase.$file,$configtheme);
			$core->blog->triggerBlog();
			$msg = __('Configuration successfully updated.');
			$tab = 'settings';
		}catch (Exception $e){

			$core->error->add($e->getMessage());

		}
}
try{
	if(isset($_POST['saveconfig'])){
		if(!empty($_POST['saveconfig']) && $core->auth->check('admin_adsense',$core->blog->id)) {
			if(!empty($_POST['google_ad_client'])){
			$core->blog->settings->setNamespace('adsense');
			$core->blog->settings->put('google_ad_client', $_POST['google_ad_client'], 'string','google_ad_client :16 digits');
			$core->blog->settings->put('google_ad_width', $_POST['google_ad_width'], 'string','width');
			$core->blog->settings->put('google_ad_height', $_POST['google_ad_height'], 'string','height');
			$core->blog->settings->put('google_color_border', rewriteInputColor::rewrite($_POST['google_color_border']), 'string','google_color_border');
			$core->blog->settings->put('google_color_bg', rewriteInputColor::rewrite($_POST['google_color_bg']), 'string','google_color_bg');
			$core->blog->settings->put('google_color_link', rewriteInputColor::rewrite($_POST['google_color_link']), 'string','google_color_link');
			$core->blog->settings->put('google_color_text', rewriteInputColor::rewrite($_POST['google_color_text']), 'string','google_color_text');
			$core->blog->settings->put('google_color_url', rewriteInputColor::rewrite($_POST['google_color_url']), 'string','google_color_url');
			$core->blog->settings->put('position', $_POST['position'], 'string','position');
			$core->blog->settings->put('google_ui_features', $_POST['google_ui_features'], 'string','google_ui_features');
			$core->blog->triggerBlog();
			$msg = __('Configuration successfully updated.');//.'<ul><li>'.$erase.'</li></ul>';

			//http::redirect();
		}else {
			$int_error;
			}
		}
	}
}catch (Exception $e)
{

	$core->error->add($e->getMessage());
}
$google_ad_client = $core->blog->settings->get('google_ad_client');
$width = $core->blog->settings->get('google_ad_width');
$height = $core->blog->settings->get('google_ad_height');
$color_border = $core->blog->settings->get('google_color_border');
$color_bg = $core->blog->settings->get('google_color_bg');
$color_link = $core->blog->settings->get('google_color_link');
$color_text = $core->blog->settings->get('google_color_text');
$color_url = $core->blog->settings->get('google_color_url');
$position = $core->blog->settings->get('position');
$google_ui_features = $core->blog->settings->get('google_ui_features');
$cache_exists = file_exists(plugins::getCacheFile());
	if (!empty($_POST['deletecache']))
	{
		$dir = pluginsAds::deleteCache();
		if ($dir === true){
			
		$msg = __('Cache has been successfully deleted.');
		}
		else{
			
			$error = $dir;
		}
		$core->blog->triggerBlog();
	}

?>
<html>
<head>
<title><?php echo __('Google Adsense'); ?></title>
<?php
echo dcPage::jsPageTabs($tab);
?>
<link rel="stylesheet" type="text/css" href="index.php?pf=adsense/styles.css" />
<link rel="stylesheet" type="text/css" href="index.php?pf=adsense/farbtastic.css" />
<script type="text/javascript" src="index.php?pf=adsense/js/farbtastic.js"></script>
<script src="index.php?pf=adsense/js/jquery.select.js" type="text/javascript"></script>
<script src="index.php?pf=adsense/js/jquery.popupwindow.js" type="text/javascript"></script>
<script type="text/javascript" src="index.php?pf=adsense/js/script.js"></script>
<script type="text/javascript">//<![CDATA[

/*window.onload = function(){
	
 fctLoad();
	}
window.onscroll = function(){
	
 fctShow();
	}
window.onresize = function(){
 
	fctShow();
}//]]>*/
$(document).ready(function() {
//$('#picker').farbtastic('#google_color_border');
var f = $.farbtastic('#picker');
    var p = $('#picker').css('opacity', 0.75);
    var selected;
    $('.colorwell')
      .each(function () { f.linkTo(this); $(this).css('opacity', 0.75); })
      .focus(function() {
        if (selected) {
          $(selected).css('opacity', 0.75).removeClass('colorwell-selected');
        }
        f.linkTo(this);
        p.css('opacity', 1);
        $(selected = this).css('opacity', 1).addClass('colorwell-selected');
      });
      $('a.targetblank').click( function() {
        	window.open($(this).attr('href'));
        	return false;
    });
});
var profiles = {
				windowCenter:
				{
					height:764,
					width:1028,
					center:1,
					toolbar:1,
					scrollbars:1,
					status:1,
					resizable:0,
					left:50,
					top:100
				}
			};
	jQuery(function()
		{
			jQuery(".popupwindow").popupwindow(profiles);
		});
</script>
<style type="text/css">
  <!--

  	label {display:inline;}

  -->
 
  </style>
</head>
<body>
<?php echo '<h2>'.html::escapeHTML($core->blog->name).' &gt; '.__('Google Adsense').'</h2>';
		if (!empty($msg)) {echo '<div class="message">'.$msg.'</div>';}
		if (!empty($error)) {echo '<div class="error"><strong>'.__('Error:').'</strong> '.$error.'</div>';}
echo '<div id="adsense" title="'.__('Adsense').'" class="multi-part">'; 
echo '<h3>'.__('Display Widget Adsense').'</h3>'. 
'<p>'.__('To display Adsense on the page, you have to active the <em>Adsense</em> widget by dragging it into the navigation sidebar from the').' <a href="plugin.php?p=widgets">'.__('the widget page').'</a>&nbsp;'.__('and click on the &quot;update sidebars&quot; button.').'</p>'.'<p>'.__('To set and personalize the displaying, use the widget.').'</p>'
.'<h3>'.__('Display Adsense').'</h3>'
.'<p>'.__('Copy/Paste the following code in _post.html').' '.__('or another file').'(<a href="plugin.php?p=themeEditor">'.__('with the theme editor').'</a>)'.'</p>'
.'<h3>'.__('Example').'</h3>';
	?>
		<pre class="code">
		{{tpl:adsense}}
		</pre>
		<?php echo __('Line 65, right before:') ?>
		<pre class="code">
		< !-- # Entry with an excerpt -->
		</pre>
		<h4><?php echo __('Documentation of Google AdSense'); ?></h4>
<p><?php echo __('To find your unique AdSense publisher ID number, log into your AdSense account at <a class="popupwindow" href="http://www.google.com/adsense">http://www.google.com/adsense</a>.').'<br />'.__('On the My Account tab, scroll down to the Property info section.').'<br />'.__('Your publisher ID for each AdSense product and feature will be located in this section.') ; ?>
</p>
<b><?php echo __('Summary:'); ?></b>
<p><?php echo __('This code will easily find it to you on your account google adsense while following the indications above.').'<br />'.__('You should copy only the 16 figures without the "pub".'); ?></p>
</div>
<?php
#deuxième onglet pour modifier le fichier html
?>
<div class="multi-part" id="template" title="<?php echo __('Template'); ?>">
<p>Pour modifier une autre page, utilisez <a href="plugin.php?p=themeEditor">l'éditeur de thème</a></p>
	<div id="file-box">
		<div id="file-editor">
			<form method="post" action="<?php echo(http::getSelfURI()); ?>" id="file-form">
					<h3><label for="configtheme"><?php echo(__('Edit a file')); ?></label></h3>
					<p><?php print __('HTML file:').'<strong>';
					file_exists($themebase.$file) ? print html::escapeHTML($themebase.$file):print html::escapeHTML($default.$file).'<br />'.__('your template uses the files by default');
					print '</strong>'; ?></p>
					<p><?php // if (file_exists($themebase . $file)) echo(form::textarea('configtheme',100,40,html::escapeHTML(file_get_contents($themebase.$file)),'cp-html')); 
					//else echo(form::textarea('configtheme',100,40,html::escapeHTML(file_get_contents($default.$file)),'cp-html'));?></p>    
					<!--<textarea cols="75" rows="25" name="configtheme" id="maximal">-->
					<?php /*if (file_exists($themebase . $file)) echo htmlentities(file_get_contents($themebase . $file)); 
					else echo htmlentities(file_get_contents($default . $file));*/
					if (file_exists($themebase . $file)) echo form::textarea('configtheme',72,25,html::escapeHTML(file_get_contents($themebase . $file)),'maximal','');
					else echo(form::textarea('configtheme',100,40,html::escapeHTML(file_get_contents($default.$file)),'maximal'));
					?>
				</textarea>
				<p><?php echo $core->formNonce(); ?></p>
				<p><input type="submit" name="sendtheme" value="<?php echo __('Save'); ?>" /></p>
			</form>
		</div>
	</div>
</div>
<?php
echo '<div id="settings" title="'.__('settings').'" class="multi-part">'; ?>
<?php if ($cache_exists) { ?>
<?php echo '<h3>'.__('Delete the cache').'</h3>';?>
<form method="post" action="<?php echo(http::getSelfURI()); ?>">
			<p><?php echo $core->formNonce(); ?></p>
			<p><input type="submit" name="deletecache" value="<?php echo __('Delete the cache'); ?>" /></p>
		</form>
		<?php } 
		echo '<h3>'.__('Tips').'</h3>'.
		'<p>'.__('Using the plugin').' <a class="targetblank" href="http://www.clashdesign.net/blog/index.php/post/2007/08/02/Plugin-eraseCache" title="clashdesign.net plugin: erasacache">EraseCache</a>'.'</p>';
		?>
<form method="post" action="<?php echo (http::getSelfURI()); ?>" enctype="multipart/form-data" name="objForm">
			<fieldset>
				<legend><?php echo(__('Adsense')); ?></legend>
				<p><label class="required" title="<?php echo __('Required field')?>"><?php echo __('Google ad client:(16 digits)'); ?></label><?php echo form::field('google_ad_client', 18, 16, $google_ad_client,true, true);?>
				<?php 
				if(isset($_POST['saveconfig'])){
					if(empty($_POST['google_ad_client'])){
						echo $int_error; 
					}
				}
				?></p>
	<p><label class="classic"><?php echo __('Width:'); ?></label><?php echo form::combo('google_ad_width',array(__('728')=>'728',__('468')=>'468',__('234')=>'234',__('120')=>'120',__('160')=>'160',__('336')=>'336',__('300')=>'300',__('250')=>'250',__('234')=>'234',__('200')=>'200',__('180')=>'180'))?>px</p>
	<p><label class="classic"><?php echo __('Height:'); ?></label>
	<?php //echo form::combo('google_ad_height',array(__('90')=>'90',__('60')=>'60',__('600')=>'600',__('240')=>'240',__('280')=>'280',__('250')=>'250',__('200')=>'200',__('150')=>'150'))?>
	<select name="google_ad_height" id="google_ad_height">
			 <option class="sub_728" value="90">90</option>
			 <option class="sub_468" value="60">60</option>
			 <option class="sub_234" value="60">60</option>
			 <option class="sub_125" value="125">125</option>
			 <option class="sub_120" value="600">600</option>
			 <option class="sub_160" value="600">600</option>
			 <option class="sub_180" value="150">150</option>
			 <option class="sub_200" value="200">200</option>
			 <option class="sub_250" value="250">250</option>
			 <option class="sub_300" value="250">250</option>
			 <option class="sub_336" value="280">90</option>
	</select>
	px</p>
    <div id="picker"></div>
	<p><label class="classic"><?php echo __('Border color:'); ?></label><?php echo form::field('google_color_border', 6, 6,'#'.$color_border,'colorwell');?></p>
	<p><label class="classic"><?php echo __('Background color:'); ?></label><?php echo form::field('google_color_bg', 6, 6,'#'.$color_bg,'colorwell');?></p>
	<p><label class="classic"><?php echo __('Color link:'); ?></label><?php echo form::field('google_color_link', 6, 6,'#'.$color_link,'colorwell');?></p>
	<p><label class="classic"><?php echo __('Color text:'); ?></label><?php echo form::field('google_color_text', 6, 6,'#'.$color_text,'colorwell');?></p>
	<p><label class="classic"><?php echo __('Color url:'); ?></label><?php echo form::field('google_color_url', 6, 6,'#'.$color_url,'colorwell');?></p>
	<p><label class="classic"><?php echo __('Styles of the angles:'); ?></label>

				<?php echo form::combo('google_ui_features',array(__('Right angles')=>'0',__('Angles slightly rounded')=>'6',__('Angles very rounded')=>'10'))?></p><p><a href=""></a></p>
	<p><label class="classic">Position:</label>
				<?php echo form::combo('position',array(__('Left')=>'left',__('Center')=>'center',__('Right')=>'right'))?></p>
			</fieldset>
			<p><?php echo $core->formNonce(); ?></p>
			<p><input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" /></p>
		</form>
		<?php
		 if (isset($_POST['saveconfig'])){?>
		<h3><?php echo __('Preview') ?></h3>
<?php 
echo
		           '<script type="text/javascript"><!--
					google_ad_client = "pub-'.$core->blog->settings->google_ad_client.'";
					google_ad_width = "'.$core->blog->settings->google_ad_width.'";
					google_ad_height = "'.$core->blog->settings->google_ad_height.'";
					google_ad_format = "'.$core->blog->settings->google_ad_width.'x'.$core->blog->settings->google_ad_height.'_as";
					google_ad_type = "text_image";
					google_ad_channel = "";
					google_color_border = "'.$core->blog->settings->google_color_border.'";
					google_color_bg = "'.$core->blog->settings->google_color_bg.'";
					google_color_link = "'.$core->blog->settings->google_color_link.'";
					google_color_text = "'.$core->blog->settings->google_color_text.'";
					google_color_url = "'.$core->blog->settings->google_color_url.'";
					google_ui_features = "rc:'.$core->blog->settings->google_ui_features.'";
					//-->
					</script>
					<script type="text/javascript"
					  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
					</script>'; 
		 }else{
		 	?><h3><?php echo 'Actuellement :' ?></h3><?php
		 	echo
		           '<script type="text/javascript"><!--
					google_ad_client = "pub-'.$core->blog->settings->google_ad_client.'";
					google_ad_width = "'.$core->blog->settings->google_ad_width.'";
					google_ad_height = "'.$core->blog->settings->google_ad_height.'";
					google_ad_format = "'.$core->blog->settings->google_ad_width.'x'.$core->blog->settings->google_ad_height.'_as";
					google_ad_type = "text_image";
					google_ad_channel = "";
					google_color_border = "'.$core->blog->settings->google_color_border.'";
					google_color_bg = "'.$core->blog->settings->google_color_bg.'";
					google_color_link = "'.$core->blog->settings->google_color_link.'";
					google_color_text = "'.$core->blog->settings->google_color_text.'";
					google_color_url = "'.$core->blog->settings->google_color_url.'";
					google_ui_features = "rc:'.$core->blog->settings->google_ui_features.'";
					//-->
					</script>
					<script type="text/javascript"
					  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
					</script>'; 
		 }
		 	?>
</div>
<?php
	#troisième onglet pour afficher quelques informations

echo '<div id="about" title="'.__('About').'" class="multi-part">'.
	'<h3>'.__('About').'</h3>'.
	'<p>'.
      __('Adsense Management').
      ' version '.'<strong>'.$m_version.'</strong>'.'</p>'.
      '<ul>'.
      	'<li><a href="http://www.clashdesign.net">Gtraxx</a></li>'
     .'</ul>'.
      __('More information on Adsense Management:').
      '<div id="link_site"><a href="http://www.clashdesign.net">
      <img src="index.php?pf=adsense/img/clashdesign.png" alt="clashdesign" />'
      .'</a>&nbsp;
      <a href="http://www.dotclear.net"><img src="index.php?pf=adsense/img/plugin_dotclear.png" alt="plugin dotclear" />
      </a>
      </div>'.
     '<h3>'.__('Support and Update').'</h3>'.
      __('Please go to:').'<a href="http://blog.clashdesign.net">http://www.clashdesign.net/blog/</a><br /><br />'.
      '<a rel="license" href="http://creativecommons.org/licenses/by-nc-nd/2.0/be/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-nc-nd/2.0/be/88x31.png" /></a>
      <br /><span xmlns:dc="http://purl.org/dc/elements/1.1/" href="http://purl.org/dc/dcmitype/Text" property="dc:title" rel="dc:type">
      Adsense Management </span> by <a xmlns:cc="http://creativecommons.org/ns#" href="http://www.clashdesign.net" property="cc:attributionName" rel="cc:attributionURL">
      Clashdesign</a> est mis &#224; disposition selon les termes de la <a rel="license" href="http://creativecommons.org/licenses/by-nc-nd/2.0/be/">
      licence Creative Commons Paternit&#233;-Pas d\'Utilisation Commerciale-Pas de Modification 2.0 Belgique</a>.<br />Bas&#233;(e) sur une oeuvre &#224;<br />
      Les autorisations au-del&#224; du champ de cette licence peuvent &#234;tre obtenues &#224; <a xmlns:cc="http://creativecommons.org/ns#" href="http://www.clashdesign.net" rel="cc:morePermissions">
      http://www.clashdesign.net</a>.'.'</div>';
 ?>
</body>
</html>