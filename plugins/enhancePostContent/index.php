<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of enhancePostContent, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}


dcPage::check('content');

$default_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'setting';

$s =& $core->blog->settings;

$_active = (boolean) $s->enhancePostContent_active;
$_filters = libEPC::blogFilters();
$_allowedtplvalues = libEPC::blogAllowedTplValues();
$_allowedpubpages = libEPC::blogAllowedPubPages();

$records = new epcRecords($core);

if (isset($_POST['save']))
{
	# --BEHAVIOR-- enhancePostContentAdminSave
	$core->callBehavior('enhancePostContentAdminSave',$core);
}

if (isset($_POST['save']) && $_POST['tab'] == 'setting')
{
	try
	{
		$_active = !empty($_POST['_active']);

		$s->setNamespace('enhancePostContent');
		$s->put('enhancePostContent_active',$_active);

		if ($core->auth->check('admin',$core->blog->id))
		{
			$_allowedtplvalues = libEPC::explode($_POST['_allowedtplvalues']);
			$_allowedpubpages = libEPC::explode($_POST['_allowedpubpages']);

			$s->put('enhancePostContent_allowedtplvalues',serialize($_allowedtplvalues));
			$s->put('enhancePostContent_allowedpubpages',serialize($_allowedpubpages));
		}
		$s->setNamespace('system');

		$core->blog->triggerBlog();

		http::redirect('plugin.php?p=enhancePostContent&tab=setting&done=1');
	}
	catch(Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

if (isset($_POST['save']) && isset($_filters[$_POST['tab']]))
{
	try
	{
		$s->setNamespace('enhancePostContent');

		# Parse filters options
		$name = $_POST['tab'];
		$f = array();
		$f['nocase'] = $_filters[$name]['nocase'] = !empty($_POST[$name]['nocase']);
		$f['plural'] = $_filters[$name]['plural'] = !empty($_POST[$name]['plural']);
		$f['limit'] = $_filters[$name]['plural'] = abs((integer) $_POST[$name]['limit']);
		$f['style'] = $_filters[$name]['style'] = (array) $_POST[$name]['style'];
		$f['notag'] = $_filters[$name]['notag'] = (string) $_POST[$name]['notag'];
		$f['tplValues'] = $_filters[$name]['tplValues'] = (array) $_POST[$name]['tplValues'];
		$f['pubPages'] = $_filters[$name]['pubPages'] = (array) $_POST[$name]['pubPages'];

		$s->put('enhancePostContent_'.$name,serialize($f));
		$s->setNamespace('system');

		# Parse filters lists
		if ($_filters[$_POST['tab']]['has_list'])
		{
			foreach($_POST[$name]['listIds'] as $k => $id)
			{
				$id = abs((integer) $id);
				if ($id > 0)
				{
					if (empty($_POST[$name]['listKeys'][$k])
					 || empty($_POST[$name]['listValues'][$k]))
					{
						$records->delRecord($id);
					}
					else
					{
						$cur = $records->openCursor();
						$cur->epc_filter = $name;
						$cur->epc_key = html::escapeHTML($_POST[$name]['listKeys'][$k]);
						$cur->epc_value = html::escapeHTML($_POST[$name]['listValues'][$k]);
						$records->updRecord($id,$cur);
					}
				}
				else
				{
					if (!empty($_POST[$name]['listKeys'][$k])
					 || !empty($_POST[$name]['listValues'][$k]))
					{
						$cur = $records->openCursor();
						$cur->epc_filter = $name;
						$cur->epc_key = html::escapeHTML($_POST[$name]['listKeys'][$k]);
						$cur->epc_value = html::escapeHTML($_POST[$name]['listValues'][$k]);
						$records->addRecord($cur);
					}
				}
			}
		}

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
form::checkbox(array('_active'),'1',$_active).' '.
__('Enable extension').'</label></p>
<p class="form-note">'.__('This also actives widget').'</p>';

if ($core->auth->check('admin',$core->blog->id))
{
	echo '
	<h2>'.__('Extra').'</h2>
	<p>'.__('This is a special feature to edit list of allowed template values and public pages where this plugin works.').'</p>
	<p><label>'.__('Allowed DC template values:').
	form::field(array('_allowedtplvalues'),100,0,libEPC::implode($_allowedtplvalues)).'
	</label></p>
	<p class="form-note">'.__('Use "readable_name1:template_value1;readable_name2:template_value2;" like "entry content:EntryContent;entry excerpt:EntryExcerpt;".').'</p>
	<p><label>'.__('Allowed public pages:').
	form::field(array('_allowedpubpages'),100,0,libEPC::implode($_allowedpubpages)).'
	</label></p>
	<p class="form-note">'.__('Use "readable_name1:template_page1;readable_name2:template_page2;" like "post page:post.html;home page:home.html;".').'</p>';
}

echo '
<p>'.
form::hidden(array('p'),'enhancePostContent').
form::hidden(array('tab'),'setting').
$core->formNonce().'
<input type="submit" name="save" value="'.__('save').'" />
</p>
</form>
</div>';


foreach($_filters as $name => $filter)
{
	echo '
	<div class="multi-part" id="'.$name.'" title="'. __($name).'">
	<p class="message">'.$filter['help'].'</p>
	<form method="post" action="plugin.php">';

	if (isset($_GET['done']) && $default_tab == $name)
	{
		echo '<p class="message">'.__('Configuration successfully saved').'</p>';
	}

	echo '
	<div class="two-cols">
	<div class="col">
	<h2>'.__('Contents').'</h2>';

	foreach($_allowedtplvalues as $k => $v)
	{
		echo '
		<p><label class="classic">'.
		form::checkbox(array($name.'[tplValues][]'),$v,in_array($v,$filter['tplValues'])).' '.
		sprintf(__('Enable filter on %s'),__($k)).'</label></p>';
	}
	
	echo '
	</div>
	<div class="col">
	<h2>'.__('Pages').'</h2>';

	foreach($_allowedpubpages as $k => $v)
	{
		echo '
		<p><label class="classic">'.
		form::checkbox(array($name.'[pubPages][]'),$v,in_array($v,$filter['pubPages'])).' '.
		sprintf(__('Enable filter on %s'),__($k)).'</label></p>';
	}
	
	echo '
	</div>
	</div>
	<div class="clear two-cols">
	<div class="col">
	<h2>'.__('Filtering').'</h2>
	<p><label class="classic">'.
	form::checkbox(array($name.'[nocase]'),'1',$filter['nocase']).' '.
	__('Case insensitive').'</label></p>
	<p><label class="classic">'.
	form::checkbox(array($name.'[plural]'),'1',$filter['plural']).' '.
	__('Also use the plural').'</label></p>
	<p><label>'.__('Limit the number of replacement to:').
	form::field(array($name.'[limit]'),4,10,html::escapeHTML($filter['limit'])).'
	</label></p>
	<p class="form-note">'.__('Leave it blank or set it to 0 for no limit').'</p>
	</div>
	<div class="col">
	<h2>'.__('Style').'</h2>';
	
	foreach($filter['class'] as $k => $v)
	{
		echo '
		<p><label>'.sprintf(__('Class "%s":'),$v).
		form::field(array($name.'[style][]'),60,255,html::escapeHTML($filter['style'][$k])).'
		</label></p>';
	}
	echo '
	<p class="form-note">'.sprintf(__('The inserted HTML tag looks like: %s'),html::escapeHTML(str_replace('%s','...',$filter['replace']))).'</p>
	<p><label>'.__('Ignore HTML tags:').
	form::field(array($name.'[notag]'),60,255,html::escapeHTML($filter['notag'])).'
	</label></p>
	<p class="form-note">'.__('This is the list of HTML tags where content will be ignored.').' '.
	(empty($filter['htmltag']) ? '' : sprintf(__('Tag "%s" always be ignored.'),$filter['htmltag'])).'</p>
	</div>
	</div>
	<div class="clear">';

	if ($filter['has_list'])
	{
		echo '
		<h2>'.__('List').'</h2>
		<table>
		<thead><tr><th>#</th><th>'.__('Key').'</th><th>'.__('Value').'</th></tr>
		<tbody>';
		$i = 1;
		$list = $records->getRecords(array('epc_filter'=>$name));
		while($list->fetch())
		{
			echo '
			<tr>
			<td>'.form::hidden(array($name.'[listIds][]'),$list->epc_id).$i.'</td>
			<td>'.form::field(array($name.'[listKeys][]'),30,225,html::escapeHTML($list->epc_key)).'</td>
			<td>'.form::field(array($name.'[listValues][]'),90,225,html::escapeHTML($list->epc_value)).'</td>
			</tr>';
			$i++;
		}
		echo '
		<tr>
		<td>'.form::hidden(array($name.'[listIds][]'),0).$i.'</td>
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