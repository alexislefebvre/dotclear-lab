<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of cinecturlink2, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------


if (!defined('DC_CONTEXT_CINECTURLINK') || DC_CONTEXT_CINECTURLINK != 'setting'){return;}

# Read settings
$s = $core->blog->settings->cinecturlink2;
$cinecturlink2_active = (boolean) $s->cinecturlink2_active;
$cinecturlink2_widthmax = abs((integer) $s->cinecturlink2_widthmax);
$cinecturlink2_folder = (string) $s->cinecturlink2_folder;
$cinecturlink2_triggeronrandom = (boolean) $s->cinecturlink2_triggeronrandom;
$cinecturlink2_public_active = (boolean) $s->cinecturlink2_public_active;
$cinecturlink2_public_title = (string) $s->cinecturlink2_public_title;
$cinecturlink2_public_description = (string) $s->cinecturlink2_public_description;
$cinecturlink2_public_nbrpp = (integer) $s->cinecturlink2_public_nbrpp;
if ($cinecturlink2_public_nbrpp < 1) $cinecturlink2_public_nbrpp = 10;

# Update settings
if ($action == 'save_setting')
{
	try
	{
		$cinecturlink2_active = !empty($_POST['cinecturlink2_active']);
		$cinecturlink2_public_active = !empty($_POST['cinecturlink2_public_active']);
		$cinecturlink2_public_title = (string) $_POST['cinecturlink2_public_title'];
		$cinecturlink2_public_description = (string) $_POST['cinecturlink2_public_description'];
		$cinecturlink2_public_nbrpp = (integer) $_POST['cinecturlink2_public_nbrpp'];
		if ($cinecturlink2_public_nbrpp < 1) $cinecturlink2_public_nbrpp = 10;
		$cinecturlink2_widthmax = abs((integer) $_POST['cinecturlink2_widthmax']);
		$cinecturlink2_folder = (string) files::tidyFileName($_POST['cinecturlink2_folder']);
		$cinecturlink2_triggeronrandom = !empty($_POST['cinecturlink2_triggeronrandom']);
		if (empty($cinecturlink2_folder))
		{
			throw new Exception(__('You must provide a specific folder for images.'));
		}
		cinecturlink2::test_folder($root,$cinecturlink2_folder,true);
		
		$s->put('cinecturlink2_active',$cinecturlink2_active);
		$s->put('cinecturlink2_public_active',$cinecturlink2_public_active);
		$s->put('cinecturlink2_public_title',$cinecturlink2_public_title);
		$s->put('cinecturlink2_public_description',$cinecturlink2_public_description);
		$s->put('cinecturlink2_public_nbrpp',$cinecturlink2_public_nbrpp);
		$s->put('cinecturlink2_widthmax',$cinecturlink2_widthmax);
		$s->put('cinecturlink2_folder',$cinecturlink2_folder);
		$s->put('cinecturlink2_triggeronrandom',$cinecturlink2_triggeronrandom);
		
		$core->blog->triggerBlog();
		
		http::redirect($p_url.'&part=setting&msg='.$action.'&section='.$section);
	}
	catch (Exception $e)
	{
		$core->error->add(sprintf($messages_errors[$action],$e->getMessage()));
	}
}

# Page
echo 
'<html><head>'.
'<title>'.__('Cinecturlink 2').' - '.__('Settings').'</title>'.
dcPage::jsLoad('index.php?pf=cinecturlink2/js/main.js').
'<script type="text/javascript">'."\n//<![CDATA[\n".
dcPage::jsVar('jcToolsBox.prototype.text_wait',__('Please wait')).
dcPage::jsVar('jcToolsBox.prototype.section',$section).
"\n//]]>\n</script>\n".
'</head>'.
'<body>'.$menu.'<h3>'.__('Settings').'</h3>';

if ($msg == 'save_setting')
{
	echo sprintf($message,__('Configuration successfully updated'));
}

echo '
<div>
<form id="setting-form" method="post" action="plugin.php">

<fieldset id="setting-main"><legend>'.__('General').'</legend>
<p><label class="classic">'.
form::checkbox(array('cinecturlink2_active'),'1',$cinecturlink2_active).' 
'.__('Enable plugin').'</label></p>
<p><label class="classic">'.__('Maximum width of images (in pixel):').'<br />'.
form::field(array('cinecturlink2_widthmax'),10,4,$cinecturlink2_widthmax).'</label></p>
<p><label class="classic">'.__('Public folder of images (under public folder of blog):').'<br />'.
form::field(array('cinecturlink2_folder'),60,64,$cinecturlink2_folder).'</label></p>
</fieldset>

<fieldset id="setting-widget"><legend>'.__('Widget').'</legend>
<p><label class="classic">'.
form::checkbox(array('cinecturlink2_triggeronrandom'),'1',$cinecturlink2_triggeronrandom).' 
'.__('Update cache when use "Random" or "Number of view" order on widget (Need reload of widgets on change)').'</label></p>
<p class="form-note">'.__('This increases the random effect, but updates the cache of the blog whenever the widget is displayed, which reduces the perfomances of your blog.').'</p>
</fieldset>

<fieldset id="setting-page"><legend>'.__('Public page').'</legend>
<p><label class="classic">'.
form::checkbox(array('cinecturlink2_public_active'),'1',$cinecturlink2_public_active).' 
'.__('Enable public page').'</label></p>
<p class="form-note">'.sprintf(__('Public page has url: %s'),'<a href="'.$core->blog->url.$core->url->getBase('cinecturlink2').'" title="public page">'.$core->blog->url.$core->url->getBase('cinecturlink2').'</a>').'</p>
<p><label class="classic">'.__('Title of the public page:').'<br />'.
form::field(array('cinecturlink2_public_title'),60,255,$cinecturlink2_public_title).'</label></p>
<p><label class="classic">'.__('Description of the public page:').'<br />'.
form::field(array('cinecturlink2_public_description'),60,255,$cinecturlink2_public_description).'</label></p>
<p><label class="classic">'.sprintf(__('Limit to %s entries per page on pulic page.'),form::field(array('cinecturlink2_public_nbrpp'),5,10,$cinecturlink2_public_nbrpp)).'</label></p>
</fieldset>

<fieldset id="setting-info"><legend>'.__('Informations').'</legend>
<ul>
<li>'.__('Once the extension has been configured and your links have been created, you can place one of the cinecturlink widgets in the sidebar.').'</li>
<li>'.sprintf(__('In order to open links in new window you can use plugin %s.'),'<a href="http://lab.dotclear.org/wiki/plugin/externalLinks">External Links</a>').'</li>
<li>'.sprintf(__('In order to change URL of public page you can use plugin %s.'),'<a href="http://lab.dotclear.org/wiki/plugin/myUrlHandlers">My URL handlers</a>').'</li>
<li>'.sprintf(__('You can add public pages of cinecturlink to the plugin %s.'),'<a href="http://lab.dotclear.org/wiki/plugin/sitemaps">sitemaps</a>').'</li>
<li>'.sprintf(__('The plugin Cinecturlink2 is compatible with plugin %s.'),'<a href="http://lab.dotclear.org/wiki/plugin/rateIt">Rate it</a>').'</li>
<li>'.sprintf(__('The plugin Cinecturlink2 is compatible with plugin %s.'),'<a href="http://lab.dotclear.org/wiki/plugin/activityReport">Activity report</a>').'</li>
</ul>
</fieldset>

<p>'.
form::hidden(array('p'),'cinecturlink2').
form::hidden(array('part'),'setting').
form::hidden(array('section'),$section).
form::hidden(array('action'),'save_setting').
$core->formNonce().'
<input type="submit" name="settings" value="'.__('save').'" />
</p>
</form>
</div>';

dcPage::helpBlock('cinecturlink2');
echo $footer.'</body></html>';
?>