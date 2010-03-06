<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of pollsFactory, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$_active = (boolean) $s->pollsFactory_active;
$_user_ident = (integer) $s->pollsFactory_user_ident;
$_public_show = (boolean) $s->pollsFactory_public_show;
$_public_graph = (boolean) $s->pollsFactory_public_graph;
$_public_tpltypes = @unserialize($s->pollsFactory_public_tpltypes);
if (!is_array($_public_tpltypes)) $_public_tpltypes = array();

$_graph_path = (string) $s->pollsFactory_graph_path;
$_graph_width = (integer) $s->pollsFactory_graph_width;
$_graph_ttcolor = (string) $s->pollsFactory_graph_ttcolor;
$_graph_txcolor = (string) $s->pollsFactory_graph_txcolor;
$_graph_bgcolor = (string) $s->pollsFactory_graph_bgcolor;
$_graph_chcolor = (string) $s->pollsFactory_graph_chcolor;
$_graph_barcolor = (string) $s->pollsFactory_graph_barcolor;

if ($default_tab == 'setting' && $action == 'savesetting')
{
	try {
		$s->setNameSpace('pollsFactory');
		$s->put('pollsFactory_active',!empty($_POST['_active']));
		$s->put('pollsFactory_user_ident', (integer) $_POST['_user_ident']);
		$s->put('pollsFactory_public_show',!empty($_POST['_public_show']));
		$s->put('pollsFactory_public_graph',!empty($_POST['_public_graph']));
		$s->put('pollsFactory_public_tpltypes',serialize($_POST['_public_tpltypes']));
		$s->put('pollsFactory_graph_path',$_POST['_graph_path']);
		
		$s->put('pollsFactory_graph_width',$_POST['_graph_width']);
		$s->put('pollsFactory_graph_ttcolor',$_POST['_graph_ttcolor']);
		$s->put('pollsFactory_graph_txcolor',$_POST['_graph_txcolor']);
		$s->put('pollsFactory_graph_bgcolor',$_POST['_graph_bgcolor']);
		$s->put('pollsFactory_graph_chcolor',$_POST['_graph_chcolor']);
		$s->put('pollsFactory_graph_barcolor',$_POST['_graph_barcolor']);

		if ($_POST['_graph_width'] != $_graph_width 
		 || $_POST['_graph_ttcolor'] != $_graph_ttcolor 
		 || $_POST['_graph_txcolor'] != $_graph_txcolor 
		 || $_POST['_graph_bgcolor'] != $_graph_bgcolor 
		 || $_POST['_graph_chcolor'] != $_graph_chcolor 
		 || $_POST['_graph_barcolor'] != $_graph_barcolor)
		{
			$s->put('pollsFactory_graph_trigger',time());
		}

		$s->setNameSpace('system');
		$core->blog->triggerBlog();

		http::redirect('plugin.php?p=pollsFactory&tab=setting&msg='.$action);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

$combo_user_ident = array(
	__('cookie') => 0,
	__('cookie and IP') => 1,
	__('IP') => 2
);
$combo_public_show = array(
	__('when poll is finished') => 0,
	__('when user has voted') => 1
);
$echo .= '
<form method="post" action="plugin.php">
<div class="two-cols">
<div class="col">
<h2>'.__('General').'</h2>
<p><label class="classic">'.
form::checkbox(array('_active'),'1',$_active).' '.
__('Enable extension').'</label></p>
<p class="field">'.__('User identification:').' '.
form::combo(array('_user_ident'),$combo_user_ident,$_user_ident).'</p>
<p class="field">'.__('Show reponse:').' '.
form::combo(array('_public_show'),$combo_public_show,$_public_show).'</p>
<h2>'.__('Graphic').'</h2>
<p><label class="classic">'.
form::checkbox(array('_public_graph'),'1',$_public_graph).' '.
__('Use graphic results').'</label></p>
<p><label for="_graph_width">'.__('Cache path:').'</label> '.
form::field('_graph_path',65,255,$_graph_path).'</p>';
if (!is_dir($_graph_path))
{
	$echo .= '<p class="form-note">'.__('Cache path is not well configured, polls images are not store in cache.').'</p>';

	if (is_dir(DC_TPL_CACHE)) {
		$echo .= '<p class="form-note">'.sprintf(__('You can use tpl cache path to store polls images at %s'),path::real(DC_TPL_CACHE)).'</p>';
	}
	if (is_dir($core->blog->public_path)) {
		$echo .= '<p class="form-note">'.sprintf(__('You can use tpl cache path to store polls images at %s'),path::real($core->blog->public_path)).'</p>';
	}
}
$echo .= '
<p class="field">'.__('Width:').' '.
form::field('_graph_width',7,4,$_graph_width).'</p>
<p class="field">'.__('Title color:').' '.
form::field('_graph_ttcolor',7,7,$_graph_ttcolor,'colorpicker').'</p>
<p class="field">'.__('Text color:').' '.
form::field('_graph_txcolor',7,7,$_graph_txcolor,'colorpicker').'</p>
<p class="field">'.__('Background color:').' '.
form::field('_graph_bgcolor',7,7,$_graph_bgcolor,'colorpicker').'</p>
<p class="field">'.__('Chart color:').' '.
form::field('_graph_chcolor',7,7,$_graph_chcolor,'colorpicker').'</p>
<p class="field">'.__('Bar color:').' '.
form::field('_graph_barcolor',7,7,$_graph_barcolor,'colorpicker').'</p>
</div><div class="col">
<h2>'.__('Templates').'</h2>
';
foreach(libPollsFactory::getPublicUrlTypes($core) as $k => $v)
{
	$echo .= '
	<p><label class="classic">'.
	form::checkbox(array('_public_tpltypes[]'),$v,in_array($v,$_public_tpltypes)).' '.
	sprintf(__('Show full content on %s'),__($k)).'</label></p>';
}
$echo .= '
</div></div>
<div class="clear">
<p><input type="submit" name="save" value="'.__('save').'" />'.
$core->formNonce().
form::hidden(array('p'),'pollsFactory').
form::hidden(array('tab'),'setting').
form::hidden(array('action'),'savesetting').'
</p></div>
</form>';
?>