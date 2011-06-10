<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of shareOn, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

dcPage::check('admin');

# Vars
$s = $core->blog->settings->shareOn;

# Settings
$_active = (boolean) $s->shareOn_active;
$_style = (string) $s->shareOn_style;
$_title = (string) $s->shareOn_title;
$_home_place = (string) $s->shareOn_home_place;
$_cat_place = (string) $s->shareOn_cat_place;
$_tag_place = (string) $s->shareOn_tag_place;
$_post_place = (string) $s->shareOn_post_place;

# Combos
$combo_place = array(
	__('hide') => '',
	__('before content') => 'before',
	__('after content') => 'after'
);

# Default values
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$section = isset($_REQUEST['section']) ? $_REQUEST['section'] : '';

# Messages
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
$msg_list = array(
	'savesetting' => __('Configuration successfully saved'),
	'savebuttons' => __('Buttons successfully updated')
);
if (isset($msg_list[$msg]))
{
	$msg = sprintf('<p class="message">%s</p>',$msg_list[$msg]);
}

# Pages
$start_part = $s->shareOn_active ? 'buttons' : 'setting';
$default_part = isset($_REQUEST['part']) && in_array($_REQUEST['part'],array('setting','buttons')) ? $_REQUEST['part'] : $start_part;

# Save settings
if ($action == 'savesetting')
{
	try
	{
		$_active = isset($_POST['_active']);
		$_style = isset($_POST['_style']) ? $_POST['_style'] : '';
		$_title = isset($_POST['_title']) ? $_POST['_title'] : '';
		$_home_place = isset($_POST['_home_place']) ? $_POST['_home_place'] : '';
		$_cat_place = isset($_POST['_cat_place']) ? $_POST['_cat_place'] : '';
		$_tag_place = isset($_POST['_tag_place']) ? $_POST['_tag_place'] : '';
		$_post_place = isset($_POST['_post_place']) ? $_POST['_post_place'] : '';
		
		$s->put('shareOn_active',$_active,'boolean');
		$s->put('shareOn_style',$_style,'string');
		$s->put('shareOn_title',$_title,'string');
		$s->put('shareOn_home_place',$_home_place,'string');
		$s->put('shareOn_cat_place',$_cat_place,'string');
		$s->put('shareOn_tag_place',$_tag_place,'string');
		$s->put('shareOn_post_place',$_post_place,'string');
		
		$core->blog->triggerBlog();
		http::redirect($p_url.'&part=setting&msg='.$action.'&section='.$section);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
# Save buttons settings
elseif ($action == 'savebuttons')
{
	try
	{
		foreach($core->shareOnButtons as $button_id => $button)
		{
			$o = new $button($core);
			$b_active = isset($_POST[$button_id.'_active']);
			$b_small = isset($_POST[$button_id.'_small']);
			
			$o->saveSettings($b_active,$b_small);
			$o->moreSettingsSave();
		}
		$core->blog->triggerBlog();
		http::redirect($p_url.'&part=buttons&msg='.$action.'&section='.$section);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

# Display
echo '
<html><head><title>'.__('Share on').'</title>'.
dcPage::jsToolBar().
dcPage::jsLoad('index.php?pf=shareOn/js/shareon.js').
'<script type="text/javascript">'."\n//<![CDATA[\n".
dcPage::jsVar('jcToolsBox.prototype.text_wait',__('Please wait')).
dcPage::jsVar('jcToolsBox.prototype.section',$section).
"\n//]]>\n</script>\n".'
</head>
<body>';

if ($default_part == 'buttons')
{
	echo '
	<h2>'.
	html::escapeHTML($core->blog->name).
	' &rsaquo; '.__('Share on').
	'</h2>'.$msg.'
	<form method="post" action="'.$p_url.'" id="buttons-form">';
	
	foreach($core->shareOnButtons as $button_id => $button)
	{
		$o = new $button($core);
		echo 
		'<fieldset id="'.$o->id.'"><legend>'.$o->name.'</legend>';
		
		if (!$o->preload())
		{
			echo '<p class="form-note">'.__('Please note that this button is loaded at same time of the page and can slow down your blog. It depends on the response time of service.').'</p>';
		}
		else
		{
			echo '<p class="form-note">'.__('This button uses javascript to call service and does not slow down your blog. You must have jQuery loaded in your theme.').'</p>';
		}
		if (!empty($o->home))
		{ 
			echo '<p><a title="'.__('homepage').'" href="'.$o->home.'">'.sprintf(__('Learn more about %s.'),$o->name).'</a></p>';
		}
		echo
		'<p><label class="classic">'.
		form::checkbox(array($button_id.'_active'),'1',$o->_active).
		__('Enable this button on post content').
		'</label></p>'.
		'<p><label class="classic">'.
		form::checkbox(array($button_id.'_small'),'1',$o->_small).
		__('Use small button on post content').
		'</label></p>'.
		$o->moreSettingsForm().
		'</fieldset>';
	
	}
	echo '
	<p>
	<input type="submit" name="buttons" value="'.__('save').'" />'.
	form::hidden(array('p'),'shareOn').
	form::hidden(array('part'),'buttons').
	form::hidden(array('action'),'savebuttons').
	form::hidden(array('section'),$section).
	$core->formNonce().'
	</p>
	</form>';
}
else
{
	echo '
	<h2>'.
	html::escapeHTML($core->blog->name).
	' &rsaquo; <a href="'.$p_url.'&amp;part=buttons">'.__('Share on').'</a>'.
	' &rsaquo; '.__('Settings').
	'</h2>'.$msg.'
	<form method="post" action="'.$p_url.'" id="setting-form">
	
	<fieldset id="plugin"><legend>'. __('Plugin activation').'</legend>
	<p><label class="classic">'.
	form::checkbox(array('_active'),'1',$_active).' '.
	__('Enable plugin').'
	</label></p>
	</fieldset>
	
	<fieldset id="sstyle"><legend>'.__('Style').'</legend>
	<p>'.__('You can add here special cascading style sheet. Share on bar has class "shareonentry" and widget has class "shareonwidget".').'</p>
	<p class="area" id="style-area"><label for="_style">'.__('CSS:').'</label>
	'.form::textarea('_style',50,3,html::escapeHTML($_style),'',2).'
	</p>
	</fieldset>
	
	<fieldset id="scontent"><legend>'.__('Content').'</legend>
	<p>'.
	__('To use this option you must have behavior "publicEntryBeforeContent", "publicEntryAfterContent" and "publicHeadContent" in your theme.').'<br />'.
	__('A widget is also available to add buttons to your blog.').'</p>
	<p><label class="classic">'.
	__('Title:').'<br />'.
	form::field(array('_title'),50,255,$_title).'
	</label></p>
	<p class="form-note">'.__("Title of group of buttons could be empty.").'</p>
	<p><label class="classic">'.
	__('Show buttons on each posts on home page:').'<br />'.
	form::combo(array('_home_place'),$combo_place,$_home_place).'
	</label></p>
	<p><label class="classic">'.
	__('Show buttons on each posts on categorie page:').'<br />'.
	form::combo(array('_cat_place'),$combo_place,$_cat_place).'
	</label></p>
	<p><label class="classic">'.
	__('Show buttons on each posts on tag page:').'<br />'.
	form::combo(array('_tag_place'),$combo_place,$_tag_place).'
	</label></p>
	<p><label class="classic">'.
	__('Show buttons on a post page:').'<br />'.
	form::combo(array('_post_place'),$combo_place,$_post_place).'
	</label></p>
	</fieldset>
	
	<p>
	<input type="submit" name="settings" value="'.__('save').'" />'.
	form::hidden(array('p'),'shareOn').
	form::hidden(array('part'),'setting').
	form::hidden(array('action'),'savesetting').
	form::hidden(array('section'),$section).
	$core->formNonce().'
	</p>
	</form>';
}

dcPage::helpBlock('shareOn');
echo '
<hr class="clear"/><p class="right">
<a class="button" href="'.$p_url.'&amp;part=setting">'.__('Settings').'</a> - 
shareOn - '.$core->plugins->moduleInfo('shareOn','version').'&nbsp;
<img alt="'.__('shareOn').'" src="index.php?pf=shareOn/icon.png" />
</p>
</body>
</html>';
?>