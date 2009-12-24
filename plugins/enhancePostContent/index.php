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

dcPage::check('content');

$s =& $core->blog->settings;
$onEntryExcerpt = (boolean) $s->enhancePostContent_onEntryExcerpt;
$onEntryContent = (boolean) $s->enhancePostContent_onEntryContent;

$filterTags = (boolean) $s->enhancePostContent_filterTags;
$styleTags = (string) $s->enhancePostContent_styleTags;
$notagTags = (string) $s->enhancePostContent_notagTags;

$filterSearch = (boolean) $s->enhancePostContent_filterSearch;
$styleSearch = (string) $s->enhancePostContent_styleSearch;
$notagSearch = (string) $s->enhancePostContent_notagSearch;

$filterAcronymes = (boolean) $s->enhancePostContent_filterAcronymes;
$styleAcronymes = (string) $s->enhancePostContent_styleAcronymes;
$listAcronymes = (string) $s->enhancePostContent_listAcronymes;
$listAcronymes = @unserialize($listAcronymes);
if (!is_array($listAcronymes)) $listAcronymes = array();
$notagAcronymes = (string) $s->enhancePostContent_notagAcronymes;

$filterLinks = (boolean) $s->enhancePostContent_filterLinks;
$styleLinks = (string) $s->enhancePostContent_styleLinks;
$listLinks = (string) $s->enhancePostContent_listLinks;
$listLinks = @unserialize($listLinks);
if (!is_array($listLinks)) $listLinks = array();
$notagLinks = (string) $s->enhancePostContent_notagLinks;

if (isset($_POST['save']))
{
	try
	{


		# --BEHAVIOR-- enhancePostContentAdminSave
		$core->callBehavior('enhancePostContentAdminSave',$core);


		$onEntryExcerpt = !empty($_POST['onEntryExcerpt']);
		$onEntryContent = !empty($_POST['onEntryContent']);

		$filterTags = !empty($_POST['filterTags']);
		$styleTags = $_POST['styleTags'];
		$notagTags = $_POST['notagTags'];

		$filterSearch = !empty($_POST['filterSearch']);
		$styleSearch = $_POST['styleSearch'];
		$notagSearch = $_POST['notagSearch'];

		$filterAcronymes = !empty($_POST['filterAcronymes']);
		$styleAcronymes = $_POST['styleAcronymes'];
		$listAcronymes = array();
		foreach($_POST['listAcronymesKeys'] as $k => $v)
		{
			if (empty($v)
			 || !isset($_POST['listAcronymesValues'][$k])
			 || empty($_POST['listAcronymesValues'][$k])) continue;

			$listAcronymes[$v] = $_POST['listAcronymesValues'][$k];
		}
		$notagAcronymes = $_POST['notagAcronymes'];

		$filterLinks = !empty($_POST['filterLinks']);
		$styleLinks = $_POST['styleLinks'];
		$listLinks = array();
		foreach($_POST['listLinksKeys'] as $k => $v)
		{
			if (empty($v)
			 || !isset($_POST['listLinksValues'][$k])
			 || empty($_POST['listLinksValues'][$k])) continue;

			$listLinks[$v] = $_POST['listLinksValues'][$k];
		}
		$notagLinks = $_POST['notagLinks'];

		$s->setNamespace('enhancePostContent');
		
		$s->put('enhancePostContent_onEntryExcerpt',$onEntryExcerpt);
		$s->put('enhancePostContent_onEntryContent',$onEntryContent);

		$s->put('enhancePostContent_filterTags',$filterTags);
		$s->put('enhancePostContent_styleTags',$styleTags);
		$s->put('enhancePostContent_notagTags',$notagTags);

		$s->put('enhancePostContent_filterSearch',$filterSearch);
		$s->put('enhancePostContent_styleSearch',$styleSearch);
		$s->put('enhancePostContent_notagSearch',$notagSearch);

		$s->put('enhancePostContent_filterAcronymes',$filterAcronymes);
		$s->put('enhancePostContent_styleAcronymes',$styleAcronymes);
		$s->put('enhancePostContent_listAcronymes',serialize($listAcronymes));
		$s->put('enhancePostContent_notagAcronymes',$notagAcronymes);

		$s->put('enhancePostContent_filterLinks',$filterLinks);
		$s->put('enhancePostContent_styleLinks',$styleLinks);
		$s->put('enhancePostContent_listLinks',serialize($listLinks));
		$s->put('enhancePostContent_notagLinks',$notagLinks);

		$s->setNamespace('system');
		$core->blog->triggerBlog();

		http::redirect('plugin.php?p=enhancePostContent&done=1');
	}
	catch(Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

echo '
<html><head><title>'.__('Enhance post content').'</title>';


# --BEHAVIOR-- enhancePostContentAdminHeader
$core->callBehavior('enhancePostContentAdminHeader',$core);


echo '
</head><body>
<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.
__('Enhance post content').'</h2>
<form method="post" action="plugin.php">';

if (isset($_GET['done']))
{
	echo '<p class="message">'.__('Configuration successfully saved').'</p>';
}

# Options
echo '
<fieldset><legend>'.__('Options').'</legend>
<p><label class="classic">'.
form::checkbox(array('onEntryExcerpt'),'1',$onEntryExcerpt).' '.
__('Enable filter on entry excerpt').'</label></p>
<p><label class="classic">'.
form::checkbox(array('onEntryContent'),'1',$onEntryContent).' '.
__('Enable filter on entry content').'</label></p>
</fieldset>';

# Tags
echo '
<fieldset><legend>'.__('Tags').'</legend>
<p><label class="classic">'.
form::checkbox(array('filterTags'),'1',$filterTags).' '.
__('Enable tags replacement in post content').'</label></p>
<p><label>'.__('Style:').
form::field('styleTags',60,255,html::escapeHTML($styleTags)).'
</label></p>
<p class="form-note">'.sprintf(__('The inserted HTML tag looks like: %s'),html::escapeHTML('<a class="post-tag" href="..." title="'.__('Tag').'">...</a>')).'</p>
<p><label>'.__('Ignore HTML tags:').
form::field('notagTags',60,255,html::escapeHTML($notagTags)).'
</label></p>
<p class="form-note">'.__('This is the list of HTML tags that will be ignored.').' '.__('As this is a link you must ignore tag "a".').'</p>
</fieldset>';

# Search
echo '
<fieldset><legend>'.__('Search').'</legend>
<p><label class="classic">'.
form::checkbox(array('filterSearch'),'1',$filterSearch).' '.
__('Enable search replacement in post content').'</label></p>
<p><label>'.__('Style:').
form::field('styleSearch',60,255,html::escapeHTML($styleSearch)).'
</label></p>
<p class="form-note">'.sprintf(__('The inserted HTML tag looks like: %s'),html::escapeHTML('<span class="post-search" title="'.__('Search').'">...</span>')).'</p>
<p><label>'.__('Ignore HTML tags:').
form::field('notagSearch',60,255,html::escapeHTML($notagSearch)).'
</label></p>
<p class="form-note">'.__('This is the list of HTML tags that will be ignored.').'</p>
</fieldset>';

# Acronymes
echo '
<fieldset><legend>'.__('Acronyms').'</legend>
<p><label class="classic">'.
form::checkbox(array('filterAcronymes'),'1',$filterAcronymes).' '.
__('Enable acronymes replacement in post content').'</label></p>
<p><label>'.__('Style:').
form::field('styleAcronymes',60,255,html::escapeHTML($styleAcronymes)).'
</label></p>
<p class="form-note">'.sprintf(__('The inserted HTML tag looks like: %s'),html::escapeHTML('<span class="post-acronyme" title="...">...</span>')).'</p>
<p><label>'.__('Ignore HTML tags:').
form::field('notagAcronymes',60,255,html::escapeHTML($notagAcronymes)).'
</label></p>
<p class="form-note">'.__('This is the list of HTML tags that will be ignored.').'</p>
<table>
<thead><tr><th>'.__('Acronym').'</th><th>'.__('Meaning').'</th></tr>
<tbody>';
foreach($listAcronymes as $acro_key => $acro_val)
{
	echo '
	<tr>
	<td>'.form::field('listAcronymesKeys[]',30,225,html::escapeHTML($acro_key)).'</td>
	<td>'.form::field('listAcronymesValues[]',90,225,html::escapeHTML($acro_val)).'</td>
	</tr>';
}
echo '
<tr>
<td>'.form::field('listAcronymesKeys[]',30,225,'').'</td>
<td>'.form::field('listAcronymesValues[]',90,225,'').'</td>
</tr>
</tbody>
</table>
</fieldset>';

# Links
echo '
<fieldset><legend>'.__('Links').'</legend>
<p><label class="classic">'.
form::checkbox(array('filterLinks'),'1',$filterLinks).' '.
__('Enable word to link replacement in post content').'</label></p>
<p><label>'.__('Style:').
form::field('styleLinks',60,255,html::escapeHTML($styleLinks)).'
</label></p>
<p class="form-note">'.sprintf(__('The inserted HTML tag looks like: %s'),html::escapeHTML('<a class="post-link" title="..." href="...">...</a>')).'</p>
<p><label>'.__('Ignore HTML tags:').
form::field('notagLinks',60,255,html::escapeHTML($notagLinks)).'
<p class="form-note">'.__('This is the list of HTML tags that will be ignored.').' '.__('As this is a link you must ignore tag "a".').'</p>
</label></p>
<table>
<thead><tr><th>'.__('Word').'</th><th>'.__('Link').'</th></tr>
<tbody>';
foreach($listLinks as $link_key => $link_val)
{
	echo '
	<tr>
	<td>'.form::field('listLinksKeys[]',30,225,html::escapeHTML($link_key)).'</td>
	<td>'.form::field('listLinksValues[]',90,225,html::escapeHTML($link_val)).'</td>
	</tr>';
}
echo '
<tr>
<td>'.form::field('listLinksKeys[]',30,225,'').'</td>
<td>'.form::field('listLinksValues[]',90,225,'').'</td>
</tr>
</tbody>
</table>
</fieldset>';


# --BEHAVIOR-- enhancePostContentAdminPage
$core->callBehavior('enhancePostContentAdminPage',$core);


echo '
<p>'.
form::hidden(array('p'),'enhancePostContent').
$core->formNonce().'
<input type="submit" name="save" value="'.__('save').'" />
</p>
</form>
'.dcPage::helpBlock('enhancePostContent').'
<hr class="clear"/>
<p class="right">
enhancePostContent - '.$core->plugins->moduleInfo('enhancePostContent','version').'&nbsp;
<img alt="'.__('enhancePostContent').'" src="index.php?pf=enhancePostContent/icon.png" />
</p></body></html>';
?>