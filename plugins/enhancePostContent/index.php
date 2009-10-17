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
$filterTags = (boolean) $s->enhancePostContent_filterTags;
$styleTags = (string) $s->enhancePostContent_styleTags;
$filterSearch = (boolean) $s->enhancePostContent_filterSearch;
$styleSearch = (string) $s->enhancePostContent_styleSearch;
$filterAcronymes = (boolean) $s->enhancePostContent_filterAcronymes;
$styleAcronymes = (string) $s->enhancePostContent_styleAcronymes;
$listAcronymes = (string) $s->enhancePostContent_listAcronymes;
$listAcronymes = @unserialize($listAcronymes);
if (!is_array($listAcronymes)) $listAcronymes = array();

if (isset($_POST['save']))
{
	try
	{


		# --BEHAVIOR-- enhancePostContentAdminSave
		$core->callBehavior('enhancePostContentAdminSave',$core);



		$filterTags = !empty($_POST['filterTags']);
		$styleTags = $_POST['styleTags'];

		$filterSearch = !empty($_POST['filterSearch']);
		$styleSearch = $_POST['styleSearch'];

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

		$s->setNamespace('enhancePostContent');
		$s->put('enhancePostContent_filterTags',$filterTags);
		$s->put('enhancePostContent_styleTags',$styleTags);
		$s->put('enhancePostContent_filterSearch',$filterSearch);
		$s->put('enhancePostContent_styleSearch',$styleSearch);
		$s->put('enhancePostContent_filterAcronymes',$filterAcronymes);
		$s->put('enhancePostContent_styleAcronymes',$styleAcronymes);
		$s->put('enhancePostContent_listAcronymes',serialize($listAcronymes));
		$s->setNamespace('system');

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

# Tags
echo '
<fieldset><legend>'.__('Tags').'</legend>
<p><label class="classic">'.
form::checkbox(array('filterTags'),'1',$filterTags).' '.
__('Enable tags replacement in post content').'</label></p>
<p><label>'.__('Style:').
form::field('styleTags',60,255,html::escapeHTML($styleTags)).'
</label></p>
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
</fieldset>';

# Acronymes
echo '
<fieldset><legend>'.__('Acronymes').'</legend>
<p><label class="classic">'.
form::checkbox(array('filterAcronymes'),'1',$filterAcronymes).' '.
__('Enable acronymes replacement in post content').'</label></p>
<p><label>'.__('Style:').
form::field('styleAcronymes',60,255,html::escapeHTML($styleAcronymes)).'
</label></p>
<table>
<thead><tr><th>'.__('Acronyme').'</th><th>'.__('Meaning').'</th></tr>
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
