<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of enhancePostContent, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# l10n
__('Tag'); __('Search'); __('Acronym'); __('Link'); __('Word');

dcPage::check('content');

$default_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'setting';

$s =& $core->blog->settings;
$active = (boolean) $s->enhancePostContent_active;

$tabs = array(
	'Tag' => array(
		'has_list' => false,
		'is_link' => true,
		'exemple' => '<a class="epc-tag" href="..." title="'.__('Tag').'">...</a>'
	),
	'Search' => array(
		'has_list' => false,
		'is_link' => false,
		'exemple' => '<span class="epc-search" title="'.__('Search').'">...</span>'
	),
	'Acronym' => array(
		'has_list' => true,
		'is_link' => false,
		'exemple' => '<acronym class="epc-acronym" title="...">...</acronym>'
	),
	'Link' => array(
		'has_list' => true,
		'is_link' => true,
		'exemple' => '<a class="epc-link" title="..." href="...">...</a>'
	),
	'Word' => array(
		'has_list' => true,
		'is_link' => false,
		'exemple' => '<span class="epc-word">...</span>'
	)
);

foreach($tabs as $name => $opt)
{
	$ns = 'enhancePostContent_'.$name;
	$epc[$name] = @unserialize($core->blog->settings->$ns);
	if (!is_array($epc[$name])) $epc[$name] = array();
	$epc[$name] = array(
		'onEntryExcerpt' => isset($epc[$name]['onEntryExcerpt']) ? (boolean) $epc[$name]['onEntryExcerpt'] : false,
		'onEntryContent' => isset($epc[$name]['onEntryContent']) ? (boolean) $epc[$name]['onEntryContent'] : false,
		'onCommentContent' => isset($epc[$name]['onCommentContent']) ? (boolean) $epc[$name]['onCommentContent'] : false,
		'nocase' => isset($epc[$name]['nocase']) ? (boolean) $epc[$name]['nocase'] : false,
		'plural' => isset($epc[$name]['plural']) ? (boolean) $epc[$name]['plural'] : false,
		'style' => isset($epc[$name]['style']) ? (string) $epc[$name]['style'] : '',
		'notag' => isset($epc[$name]['notag']) ? (string) $epc[$name]['notag'] : ''
	);

	if ($opt['has_list'])
	{
		$nsList = $ns.'List';
		$list = @unserialize($core->blog->settings->$nsList);
		$epc[$name]['list'] = !is_array($list) ? array() : $list;
	}
}

if (isset($_POST['save']))
{
	# --BEHAVIOR-- enhancePostContentAdminSave
	$core->callBehavior('enhancePostContentAdminSave',$core);
}

if (isset($_POST['save']) && $_POST['tab'] == 'setting')
{
	try
	{
		$active = !empty($_POST['active']);

		$s->setNamespace('enhancePostContent');
		$s->put('enhancePostContent_active',$active);
		$s->setNamespace('system');

		$core->blog->triggerBlog();

		http::redirect('plugin.php?p=enhancePostContent&tab=setting&done=1');
	}
	catch(Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

if (isset($_POST['save']) && isset($tabs[$_POST['tab']]))
{
	try
	{
		$s->setNamespace('enhancePostContent');

		$name = $_POST['tab'];
		$epcOpt = array(
			'onEntryExcerpt' => !empty($_POST[$name]['onEntryExcerpt']),
			'onEntryContent' => !empty($_POST[$name]['onEntryContent']),
			'onCommentContent' => !empty($_POST[$name]['onCommentContent']),
			'nocase' => !empty($_POST[$name]['nocase']),
			'plural' => !empty($_POST[$name]['plural']),
			'style' => (string) $_POST[$name]['style'],
			'notag' => (string) $_POST[$name]['notag']
		);
		$s->put('enhancePostContent_'.$name,serialize($epcOpt));


		if ($tabs[$_POST['tab']]['has_list'])
		{
			$epcList = array();
			foreach($_POST[$name]['listKeys'] as $k => $v)
			{
				if (empty($v)
				 || !isset($_POST[$name]['listValues'][$k])
				 || empty($_POST[$name]['listValues'][$k])) continue;

				$epcList[$v] = $_POST[$name]['listValues'][$k];
			}
			$s->put('enhancePostContent_'.$name.'List',serialize($epcList));
		}

		$s->setNamespace('system');

		$core->blog->triggerBlog();

		http::redirect('plugin.php?p=enhancePostContent&tab='.$name.'&done=1');
	}
	catch(Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

echo '
<html><head><title>'.__('Enhance post content').'</title>
'.dcPage::jsLoad('js/_posts_list.js').dcPage::jsPageTabs($default_tab);


# --BEHAVIOR-- enhancePostContentAdminHeader
$core->callBehavior('enhancePostContentAdminHeader',$core);


echo '
</head><body>
<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.
__('Enhance post content').'</h2>';

# Setting
echo '
<div class="multi-part" id="setting" title="'. __('Settings').'">
<form method="post" action="plugin.php">';

if (isset($_GET['done']) && $default_tab == 'setting')
{
	echo '<p class="message">'.__('Configuration successfully saved').'</p>';
}

echo '
<p><label class="classic">'.
form::checkbox(array('active'),'1',$active).' '.
__('Enable extension').'</label></p>
<p>'.
form::hidden(array('p'),'enhancePostContent').
form::hidden(array('tab'),'setting').
$core->formNonce().'
<input type="submit" name="save" value="'.__('save').'" />
</p>
</form>
</div>';


foreach($tabs as $name => $with_list)
{
	echo '
	<div class="multi-part" id="'.$name.'" title="'. __($name).'">
	<form method="post" action="plugin.php">';

	if (isset($_GET['done']) && $default_tab == $name)
	{
		echo '<p class="message">'.__('Configuration successfully saved').'</p>';
	}

	echo '
	<div class="two-cols">
	<div class="col">
	<p><label class="classic">'.
	form::checkbox(array($name.'[onEntryExcerpt]'),'1',$epc[$name]['onEntryExcerpt']).' '.
	__('Enable filter on entry excerpt').'</label></p>
	<p><label class="classic">'.
	form::checkbox(array($name.'[onEntryContent]'),'1',$epc[$name]['onEntryContent']).' '.
	__('Enable filter on entry content').'</label></p>
	<p>
	<p><label class="classic">'.
	form::checkbox(array($name.'[onCommentContent]'),'1',$epc[$name]['onCommentContent']).' '.
	__('Enable filter on comment content').'</label></p>
	</div>
	<div class="col">
	<p><label class="classic">'.
	form::checkbox(array($name.'[nocase]'),'1',$epc[$name]['nocase']).' '.
	__('Case insensitive').'</label></p>
	<p><label class="classic">'.
	form::checkbox(array($name.'[plural]'),'1',$epc[$name]['plural']).' '.
	__('Also use the plural').'</label></p>
	</div>
	</div>
	<div class="clear">
	<p><label>'.__('Style:').
	form::field(array($name.'[style]'),60,255,html::escapeHTML($epc[$name]['style'])).'
	</label></p>
	<p class="form-note">'.sprintf(__('The inserted HTML tag looks like: %s'),html::escapeHTML($tabs[$name]['exemple'])).'</p>
	<p><label>'.__('Ignore HTML tags:').
	form::field(array($name.'[notag]'),60,255,html::escapeHTML($epc[$name]['notag'])).'
	</label></p>
	<p class="form-note">'.__('This is the list of HTML tags that will be ignored.').($tabs[$name]['is_link'] ? ' '.__('As this is a link you must ignore tag "a".') : '').'</p>';
	
	if ($tabs[$name]['has_list'])
	{
		echo '
		<table>
		<thead><tr><th>'.__('Key').'</th><th>'.__('Value').'</th></tr>
		<tbody>';
		foreach($epc[$name]['list'] as $key => $val)
		{
			echo '
			<tr>
			<td>'.form::field(array($name.'[listKeys][]'),30,225,html::escapeHTML($key)).'</td>
			<td>'.form::field(array($name.'[listValues][]'),90,225,html::escapeHTML($val)).'</td>
			</tr>';
		}
		echo '
		<tr>
		<td>'.form::field(array($name.'[listKeys][]'),30,225,'').'</td>
		<td>'.form::field(array($name.'[listValues][]'),90,225,'').'</td>
		</tr>
		</tbody>
		</table>';
	}
	echo '
	<p>'.
	form::hidden(array('p'),'enhancePostContent').
	form::hidden(array('tab'),$name).
	$core->formNonce().'
	<input type="submit" name="save" value="'.__('save').'" />
	</p>
	</div>
	</form>
	</div>';
}


# --BEHAVIOR-- enhancePostContentAdminPage
$core->callBehavior('enhancePostContentAdminPage',$core);


echo '
'.dcPage::helpBlock('enhancePostContent').'
<hr class="clear"/>
<p class="right">
enhancePostContent - '.$core->plugins->moduleInfo('enhancePostContent','version').'&nbsp;
<img alt="'.__('enhancePostContent').'" src="index.php?pf=enhancePostContent/icon.png" />
</p></body></html>';
?>