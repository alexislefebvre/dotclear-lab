<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of wikioWorld, a plugin for Dotclear 2.
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

# Settings
$s = wikioWorldSettings($core);
$active = (boolean) $s->wikioWorld_active;
$entryvote_active = (boolean) $s->wikioWorld_entryvote_active;
$entryvote_style = (string) $s->wikioWorld_entryvote_style;
$entryvote_place = (string) $s->wikioWorld_entryvote_place;
$blogrss_active = (boolean) $s->wikioWorld_blogrss_active;
$blogrss_style = (string) $s->wikioWorld_blogrss_style;
$addwikio_active = (boolean) $s->wikioWorld_addwikio_active;
$toprank_active = (boolean) $s->wikioWorld_toprank_active;
$toprank_cat = (string) $s->wikioWorld_toprank_cat;

# Default values
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$section = isset($_REQUEST['section']) ? $_REQUEST['section'] : '';

# Messages
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
$msg_list = array(
	'savesetting' => __('Configuration successfully saved')
);
if (isset($msg_list[$msg])) {
	$msg = sprintf('<p class="message">%s</p>',$msg_list[$msg]);
}

# Pages
$start_part = $active ? 'backlinks' : 'setting';
$default_part = isset($_REQUEST['part']) && in_array($_REQUEST['part'],array('setting','backlinks')) ? $_REQUEST['part'] : $start_part;

# Combos
$combo_style = array(
	__('compact')=>'compact',
	__('normal')=>'normal'
);
$combo_place = array(
	__('before content') => 'before',
	__('after content') => 'after'
);
$combo_button_style = array(
	__('interactive')=>'',
	__('plain')=>'plain',
	__('rounded')=>'rounded',
	__('rounded open')=>'rounded-open',
	__('plain blue') => 'plain-blue',
	__('rounded blue') => 'rounded-blue',
	__('rounded open blue') => 'rounded-open-blue'
);

# Save settings
if ($action == 'savesetting')
{
	try
	{
		$active = !empty($_POST['active']);
		$entryvote_active = !empty($_POST['entryvote_active']);
		$entryvote_style = $_POST['entryvote_style'];
		$entryvote_place = $_POST['entryvote_place'];
		$blogrss_active = !empty($_POST['blogrss_active']);
		$blogrss_style = $_POST['blogrss_style'];
		$addwikio_active = !empty($_POST['addwikio_active']);
		$toprank_active = !empty($_POST['toprank_active']);
		$toprank_cat = $_POST['toprank_cat'];
		
		$s->put('wikioWorld_active',$active,'boolean');
		$s->put('wikioWorld_entryvote_active',$entryvote_active,'boolean');
		$s->put('wikioWorld_entryvote_style',$entryvote_style,'string');
		$s->put('wikioWorld_entryvote_place',$entryvote_place,'string');
		$s->put('wikioWorld_blogrss_active',$blogrss_active,'boolean');
		$s->put('wikioWorld_blogrss_style',$blogrss_style,'string');
		$s->put('wikioWorld_addwikio_active',$addwikio_active,'boolean');
		$s->put('wikioWorld_toprank_active',$toprank_active,'boolean');
		$s->put('wikioWorld_toprank_cat',$toprank_cat,'string');
		
		$core->blog->triggerBlog();
		http::redirect($p_url.'&part=setting&msg='.$action.'&section='.$section);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

# Display
if ($default_part == 'backlinks')
{
	echo '
	<html><head><title>'.__('Wikio world').'</title></head>
	<body>
	<h2>'.
	html::escapeHTML($core->blog->name).
	' &rsaquo; '.__('Wikio world').
	' - <a class="button" href="'.$p_url.'&part=setting&section=wwplugin">'.__('Settings').'</a>'.
	'</h2>'.
	'<fieldset><legend>'.__('Backlinks').'</legend>'.
	'<a href="http://www.wikio.fr" class="wikio-bl-source">Widget Backlinks par Wikio!</a>'.
	'<script type="text/javascript" src="http://widgets.wikio.fr/js/source/backlinks?'.
	'style=raw&country='.wikioWorldSettings($core,'system')->lang.
	'&width=400&content=1&url='.wikioWorld::cleanURL($core->blog->url).'" charset="utf-8"></script>'.
	'</fieldset>'.
	'<fieldset><legend>'.__('Neighbours').'</legend>'.
	'<a href="http://www.wikio.fr" class="wikio-neighbours">Widget blogroll par Wikio!</a>'.
	'<script type="text/javascript" src="http://widgets.wikio.fr/js/source/neighbours?'.
	'style=raw&country='.wikioWorldSettings($core,'system')->lang.
	'&width=400&dir=in&url='.wikioWorld::cleanURL($core->blog->url).'" charset="utf-8"></script>'.
	'</fieldset>';
}
elseif ($default_part == 'setting')
{
	echo '
	<html><head><title>'.__('Wikio world').'</title>'.
	dcPage::jsToolBar().
	dcPage::jsLoad('index.php?pf=wikioWorld/js/wikioworld.js').
	'<script type="text/javascript">'."\n//<![CDATA[\n".
	dcPage::jsVar('jcToolsBox.prototype.text_wait',__('Please wait')).
	dcPage::jsVar('jcToolsBox.prototype.section',$section).
	"\n//]]>\n</script>\n".'
	</head>
	<body>
	<h2>'.
	html::escapeHTML($core->blog->name).
	' &rsaquo; <a href="'.$p_url.'&part=backlinks&section=wwplugin">'.__('Wikio world').'</a>'.
	' - '.__('Settings').
	'</h2>'.$msg.'
	<form method="post" action="'.$p_url.'" id="setting-form">

	<fieldset id="wwplugin"><legend>'. __('Plugin activation').'</legend>
	<p class="field"><label>'.__('Enable plugin').
	form::checkbox(array('active'),'1',$active).'</label></p>
	</fieldset>

	<fieldset id="wwentryvote"><legend>'. __('Entry').'</legend>
	<p>'.__('You can add a button to vote on wikio for your entries.').'</p>
	<p class="field"><label>'.__('Enable:').
	form::checkbox(array('entryvote_active'),'1',$entryvote_active).'</label></p>
	<p class="field"><label>'.__('Style:').
	form::combo(array('entryvote_style'),$combo_style,$entryvote_style).'</label></p>
	<p class="field"><label>'.__('Place:').
	form::combo(array('entryvote_place'),$combo_place,$entryvote_place).'</label></p>
	</fieldset>

	<fieldset id="wwfooter"><legend>'. __('Footer').'</legend>
	<p>'.__('You can add some wikio buttons to the footer of your theme.').'</p>
	
	<h3>'.__('Universal subscription').'</h3>
	<p class="field"><label>'.__('Enable:').
	form::checkbox(array('blogrss_active'),'1',$blogrss_active).'</label></p>
	<p class="field"><label>'.__('Style:').
	form::combo(array('blogrss_style'),$combo_button_style,$blogrss_style).'</label></p>
	
	<h3>'.__('Top rank').'</h3>
	<p class="field"><label>'.__('Enable:').
	form::checkbox(array('toprank_active'),'1',$toprank_active).'</label></p>
	<p class="field"><label>'.__('Category:').
	form::combo(array('toprank_cat'),wikioWorld::topCatCombo(),$toprank_cat).'</label></p>
	
	<h3>'.__('Wikio subscription').'</h3>
	<p class="field"><label>'.__('Enable:').
	form::checkbox(array('addwikio_active'),'1',$addwikio_active).'</label></p>
	</fieldset>

	<fieldset id="wwtheme"><legend>'. __('Theme').'</legend>
	<p>'.__('In order to use all features of this extension, your theme must have behaviors:').'</p>
	<ul>
	<li>publicHeadContent</li>
	<li>publicEntryBeforeContent</li>
	<li>publicEntryAfterContent</li>
	<li>publicFooterContent</li>
	</ul>
	<p>'.__('Widgets are availables:').'</p>
	<ul>
	<li>'.__('Wikio : Wikio news').'</li>
	<li>'.__('Wikio : Blog subscription').'</li>
	<li>'.__('Wikio : Blog add to wikio').'</li>
	<li>'.__('Wikio : Blog backlinks').'</li>
	<li>'.__('Wikio : Blog neighbours').'</li>
	<li>'.__('Wikio : Blog top rank').'</li>
	<li>'.__('Wikio : Blog entry share').'</li>
	<li>'.__('Wikio : Blog entry vote').'</li>
	</ul>
	<p>'.__('In order to use "Top rank" button, your blog must be know in the Wikio top rank.').'</p>
	<p>'.__('In order to use "Vote" button, your entry must be know of Wikio.').'</p>
	<p><a href="http://wikio.fr">www.wikio.fr</a> - <a href="http://www.wikio.fr/tools">www.wikio.fr/tools</a></p>
	</fieldset>

	<p>
	<input type="submit" name="settings" value="'.__('save').'" />'.
	form::hidden(array('p'),'wikioWorld').
	form::hidden(array('action'),'savesetting').
	form::hidden(array('part'),'setting').
	form::hidden(array('section'),$section).
	$core->formNonce().'
	</p>
	</form>';
}

dcPage::helpBlock('wikioWorld');
echo '
<hr class="clear"/><p class="right">
wikioWorld - '.$core->plugins->moduleInfo('wikioWorld','version').'&nbsp;
<img alt="'.__('Wikio world').'" src="index.php?pf=wikioWorld/icon.png" />
</p>
</body>
</html>';
?>