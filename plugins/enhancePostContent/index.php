<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of enhancePostContent, a plugin for Dotclear 2.
# 
# Copyright (c) 2008-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

dcPage::check('content');

$core->blog->settings->addNamespace('enhancePostContent');
$s = $core->blog->settings->enhancePostContent;

$active = (boolean) $s->enhancePostContent_active;
$list_sortby = (string) $s->enhancePostContent_list_sortby;
$list_order = (string) $s->enhancePostContent_list_order;
$list_nb = (integer) $s->enhancePostContent_list_nb;
$_filters = libEPC::blogFilters();
$allowedtplvalues = libEPC::blogAllowedTplValues();
$allowedpubpages = libEPC::blogAllowedPubPages();

$filters_id = array();
foreach($_filters as $name => $filter)
{
	$filters_id[$filter['id']] = $name;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$default_part = isset($_REQUEST['part']) ? $_REQUEST['part'] : 'settings';
$default_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'record';
if (!isset($filters_id[$default_part]))
{
	$default_part = 'settings';
}
if ($default_part == 'settings' || !$_filters[$filters_id[$default_part]]['has_list'])
{
	$default_tab = 'setting';
}

$sortby_combo = array(
	__('Date') => 'epc_upddt',
	__('Key') => 'epc_key',
	__('Value') => 'epc_value',
	__('ID') => 'epc_id'
);

$order_combo = array(
	__('Descending') => 'desc',
	__('Ascending') => 'asc'
);

$records = new epcRecords($core);

if (!empty($action))
{
	# --BEHAVIOR-- enhancePostContentAdminSave
	$core->callBehavior('enhancePostContentAdminSave',$core);
}

# Update plugin settings
if ($action == 'savesettings')
{
	try
	{
		$active = !empty($_POST['active']);
		$list_sortby = in_array($_POST['list_sortby'],$sortby_combo) ? $_POST['list_sortby'] : 'epc_id';
		$list_order = in_array($_POST['list_order'],$order_combo) ? $_POST['list_order'] : 'desc';
		$list_nb = isset($_POST['list_nb']) && $_POST['list_nb'] > 0 ? $_POST['list_nb'] : 20;
		
		$s->put('enhancePostContent_active',$active);
		$s->put('enhancePostContent_list_sortby',$list_sortby);
		$s->put('enhancePostContent_list_order',$list_order);
		$s->put('enhancePostContent_list_nb',$list_nb);
		
		if ($core->auth->check('admin',$core->blog->id))
		{
			$allowedtplvalues = libEPC::explode($_POST['allowedtplvalues']);
			$allowedpubpages = libEPC::explode($_POST['allowedpubpages']);
			
			$s->put('enhancePostContent_allowedtplvalues',serialize($allowedtplvalues));
			$s->put('enhancePostContent_allowedpubpages',serialize($allowedpubpages));
		}
		
		$core->blog->triggerBlog();
		
		http::redirect('plugin.php?p=enhancePostContent&part=settings&done=1');
	}
	catch(Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

# Update filter settings
if ($action == 'savefiltersetting' && isset($filters_id[$default_part]))
{
	try
	{
		# Parse filters options
		$name = $filters_id[$default_part];
		$f = array();
		$f['nocase'] = !empty($_POST['filter_nocase']);
		$f['plural'] = !empty($_POST['filter_plural']);
		$f['limit'] = abs((integer) $_POST['filter_limit']);
		$f['style'] = (array) $_POST['filter_style'];
		$f['notag'] = (string) $_POST['filter_notag'];
		$f['tplValues'] = (array) $_POST['filter_tplValues'];
		$f['pubPages'] = (array) $_POST['filter_pubPages'];
		
		$s->put('enhancePostContent_'.$name,serialize($f));
		
		$core->blog->triggerBlog();
		
		http::redirect('plugin.php?p=enhancePostContent&part='.$default_part.'&tab=setting&done=1');
	}
	catch(Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

# Add new filter record
if ($action == 'savenewrecord' && isset($filters_id[$default_part]) 
 && !empty($_POST['new_key']) && !empty($_POST['new_value']))
{
	try
	{
		$cur = $records->openCursor();
		$cur->epc_filter = $filters_id[$default_part];
		$cur->epc_key = html::escapeHTML($_POST['new_key']);
		$cur->epc_value = html::escapeHTML($_POST['new_value']);
		$records->addRecord($cur);
		
		$core->blog->triggerBlog();
		
		http::redirect('plugin.php?p=enhancePostContent&part='.$default_part.'&tab=record&add=1');
	}
	catch(Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

# Update filter records
if ($action == 'saveupdaterecords' && isset($filters_id[$default_part])
 && $_filters[$filters_id[$default_part]]['has_list'])
{
	try
	{
		foreach($_POST['epc_id'] as $k => $id)
		{
			$k = abs((integer) $k);
			$id = abs((integer) $id);
			
			if (empty($_POST['epc_key'][$k]) || empty($_POST['epc_value'][$k]))
			{
				$records->delRecord($id);
			}
			elseif ($_POST['epc_key'][$k] != $_POST['epc_old_key'][$k] 
			 || $_POST['epc_value'][$k] != $_POST['epc_old_value'][$k])
			{
				$cur = $records->openCursor();
				$cur->epc_filter = $filters_id[$default_part];
				$cur->epc_key = html::escapeHTML($_POST['epc_key'][$k]);
				$cur->epc_value = html::escapeHTML($_POST['epc_value'][$k]);
				$records->updRecord($id,$cur);
			}
		}
		
		$core->blog->triggerBlog();
		
		$redir = !empty($_REQUEST['redir']) ? $_REQUEST['redir'] :
			'plugin.php?p=enhancePostContent&part='.$default_part.'&tab=record';
		
		http::redirect($redir.'&upd=1');
	}
	catch(Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

echo '
<html><head><title>'.__('Enhance post content').'</title>'.
dcPage::jsLoad('js/_posts_list.js').
dcPage::jsToolbar().
dcPage::jsPageTabs($default_tab);

# --BEHAVIOR-- enhancePostContentAdminHeader
$core->callBehavior('enhancePostContentAdminHeader',$core);

echo '
</head><body>
<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.
__('Enhance post content').'</h2>';

# Filters menu
echo '<p>';
foreach($filters_id as $id => $name)
{
	echo '<a class="button" href="'.$p_url.'&amp;part='.$id.'">'.__($name).'</a> ';
}
echo '</p><hr />';

# Filter content
if (isset($filters_id[$default_part]))
{
	$name = $filters_id[$default_part];
	$filter = $_filters[$name];
	
	# Filter title and description
	echo '
	<h2 style="text-align:center;">'.__($filters_id[$default_part]).'</h2>
	<p style="text-align:center;">'.$filter['help'].'</p>';
	
	# Filter settings
	echo '
	<div class="multi-part" id="setting" title="'.__('Settings').'">
	<form method="post" action="'.$p_url.'&amp;part='.$default_part.'&amp;tab=setting">';
	
	if (isset($_GET['done']))
	{
		echo '<p class="message">'.__('Configuration successfully updated').'</p>';
	}
	
	echo '
	<div class="two-cols">
	<div class="col">
	<h2>'.__('Pages to be filtered').'</h2>';
	
	foreach($allowedpubpages as $k => $v)
	{
		echo '
		<p class="field"><label class="classic">'.
		form::checkbox(array('filter_pubPages[]'),$v,in_array($v,$filter['pubPages'])).' '.
		__($k).'</label></p>';
	}
	
	echo '
	<h2>'.__('Contents to be filtered').'</h2>';

	foreach($allowedtplvalues as $k => $v)
	{
		echo '
		<p class="field"><label class="classic">'.
		form::checkbox(array('filter_tplValues[]'),$v,
		in_array($v,$filter['tplValues'])).' '.__($k).'</label></p>';
	}
	
	echo '
	</div>
	
	<div class="col">
	<h2>'.__('Filtering').'</h2>
	<p><label class="classic">'.
	form::checkbox(array('filter_nocase'),'1',$filter['nocase']).' '.
	__('Case insensitive').'</label></p>
	<p><label class="classic">'.
	form::checkbox(array('filter_plural'),'1',$filter['plural']).' '.
	__('Also use the plural').'</label></p>
	<p><label>'.__('Limit the number of replacement to:').
	form::field(array('filter_limit'),4,10,html::escapeHTML($filter['limit'])).'
	</label></p>
	<p class="form-note">'.__('Leave it blank or set it to 0 for no limit').'</p>
	
	<h2>'.__('Style').'</h2>';
	
	foreach($filter['class'] as $k => $v)
	{
		echo '
		<p><label>'.sprintf(__('Class "%s":'),$v).
		form::field(array('filter_style[]'),60,255,html::escapeHTML($filter['style'][$k])).'
		</label></p>';
	}
	echo '
	<p class="form-note">'.sprintf(__('The inserted HTML tag looks like: %s'),html::escapeHTML(str_replace('%s','...',$filter['replace']))).'</p>
	<p><label>'.__('Ignore HTML tags:').
	form::field(array('filter_notag'),60,255,html::escapeHTML($filter['notag'])).'
	</label></p>
	<p class="form-note">'.__('This is the list of HTML tags where content will be ignored.').' '.
	(empty($filter['htmltag']) ? '' : sprintf(__('Tag "%s" always be ignored.'),$filter['htmltag'])).'</p>
	</div>
	</div>
	
	<div class="clear">
	<p>'.
	$core->formNonce().
	form::hidden(array('action'),'savefiltersetting').'
	<input type="submit" name="save" value="'.__('save').'" />
	</p>
	</div>
	</form>
	</div>';
	
	# Filter records list
	if ($filter['has_list'])
	{
		$params = array();
		$params['epc_filter'] = $name;
		
		$sortby = !empty($_GET['sortby']) ? $_GET['sortby'] : $list_sortby;
		$order = !empty($_GET['order']) ? $_GET['order'] : $list_order;
		$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
		
		if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0)
		{
			$list_nb = (integer) $_GET['nb'];
		}
		
		$params['limit'] = array((($page-1)*$list_nb),$list_nb);
		
		if ($sortby !== '' && in_array($sortby,$sortby_combo))
		{
			if ($order !== '' && in_array($order,$order_combo))
			{
				$params['order'] = $sortby.' '.$order;
			}
		}

		try
		{
			$list = $records->getRecords($params);
			$counter = $records->getRecords($params,true);
			//$post_list = new adminPostList($core,$posts,$counter->f(0));
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
		
		$pager = new pager($page,$counter->f(0),$list_nb,10);
		$pager->html_prev = __('&#171;prev.');
		$pager->html_next = __('next&#187;');
		$pager->var_page = 'page';
		
		$pager_url = $p_url.
		'&amp;part='.$default_part.
		'&amp;tab=record'.
		'&amp;nb='.$list_nb.
		'&amp;sortby=%s'.
		'&amp;order='.($order == 'desc' ? 'asc' : 'desc').
		'&amp;page='.$page;
		
		echo '
		<div class="multi-part" id="record" title="'.__('Records').'">';
		
		if (!empty($_GET['add']))
		{
			echo '<p class="message">'.__('Record successfully added').'</p>';
		}
		if (!empty($_GET['upd']))
		{
			echo '<p class="message">'.__('Records successfully updated').'</p>';
		}
		if ($list->isEmpty())
		{
			echo '<p>'.__('No record').'</p>';
		}
		else
		{
			echo '
			<form action="'.$pager_url.'" method="post">
			<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>
			<table>
			<thead><tr>
			<th><a href="'.sprintf($pager_url,'epc_key').'">'.__('Key').'</a></th>
			<th><a href="'.sprintf($pager_url,'epc_value').'">'.__('Value').'</a></th>
			<th><a href="'.sprintf($pager_url,'epc_upddt').'">'.__('Date').'</a></th>
			</tr></thead>
			<tbody>';
			
			while($list->fetch())
			{
				echo '
				<tr class="line">
				<td class="nowrap">'.
				form::hidden(array('epc_id[]'),$list->epc_id).
				form::hidden(array('epc_old_key[]'),html::escapeHTML($list->epc_key)).
				form::hidden(array('epc_old_value[]'),html::escapeHTML($list->epc_value)).
				form::field(array('epc_key[]'),30,225,html::escapeHTML($list->epc_key),'').'</td>
				<td class="maximal">'.
				form::field(array('epc_value[]'),90,225,html::escapeHTML($list->epc_value),'maximal').'</td>
				<td class="nowrap">'.
				dt::dt2str(__('%Y-%m-%d %H:%M'),$list->epc_upddt,$core->auth->getInfo('user_tz')).'</td>
				</tr>';
			}
			echo '
			</tbody>
			</table>
			<p class="form-note">'.__('In order to remove a record, leave empty its key or value.').'</p>
			<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>
			
			<div class="clear">
			<p>'.
			$core->formNonce().
			form::hidden(array('redir'),$pager_url).
			form::hidden(array('action'),'saveupdaterecords').'
			<input type="submit" name="save" value="'.__('save').'" />
			</p>
			</div>
			</form>';
		}
		
		echo '</div>';
		
		# New record
		echo '
		<div class="multi-part" id="newrecord" title="'.__('New record').'">
		<form action="'.$p_url.'&amp;part='.$default_part.'&amp;tab=setting" method="post">'.
		'<p><label>'.__('Key:').
		form::field('new_key',60,255,'','',3).
		'</label></p>'.
		'<p><label>'.__('Value:').
		form::field('new_value',60,255,'','',3).
		'</label></p>
		
		<div class="clear">
		<p>'.
		form::hidden(array('action'),'savenewrecord').
		$core->formNonce().'
		<input type="submit" name="save" value="'.__('save').'" />
		</p>
		</div>
		</form>
		</div>';
	}
}
# Setting
else 
{
	echo '
	<h2 style="text-align:center;">'.__('Settings of enhancePostContent').'</h2>
	<form method="post" action="'.$p_url.'">';
	
	if (isset($_GET['done']))
	{
		echo '<p class="message">'.__('Configuration successfully saved').'</p>';
	}
	
	echo '
	<p><label class="classic">'.
	form::checkbox(array('active'),'1',$active).' '.
	__('Enable extension').'</label></p>
	<p class="form-note">'.__('This also actives widget').'</p>
	<h2>'.__('Records lists').'</h2>
	<p class="form-note">'.__('This is the default order of records lists.').'</p>
	<p class="field"><label>'.__('Order by:').
	form::combo('list_sortby',$sortby_combo,$list_sortby).'</label> </p>
	<p class="field"><label>'.__('Sort:').
	form::combo('list_order',$order_combo,$list_order).'</label></p>
	<p class="field"><label>'.__('Records per page:').
	form::field('list_nb',3,3,$list_nb).'</label></p>';
	
	if ($core->auth->check('admin',$core->blog->id))
	{
		echo '
		<h2>'.__('Extra').'</h2>
		<p>'.__('This is a special feature to edit list of allowed template values and public pages where this plugin works.').'</p>
		<p><label>'.__('Allowed DC template values:').
		form::field(array('allowedtplvalues'),100,0,libEPC::implode($allowedtplvalues)).'
		</label></p>
		<p class="form-note">'.__('Use "readable_name1:template_value1;readable_name2:template_value2;" like "entry content:EntryContent;entry excerpt:EntryExcerpt;".').'</p>
		<p><label>'.__('Allowed public pages:').
		form::field(array('allowedpubpages'),100,0,libEPC::implode($allowedpubpages)).'
		</label></p>
		<p class="form-note">'.__('Use "readable_name1:template_page1;readable_name2:template_page2;" like "post page:post.html;home page:home.html;".').'</p>';
	}
	
	echo '
	<p>'.
	form::hidden(array('action'),'savesettings').
	$core->formNonce().'
	<input type="submit" name="save" value="'.__('save').'" />
	</p>
	</form>';
}

# --BEHAVIOR-- enhancePostContentAdminPage
$core->callBehavior('enhancePostContentAdminPage',$core);

echo '
'.dcPage::helpBlock('enhancePostContent').'
<hr class="clear"/>
<p class="right">
<a class="button" href="'.$p_url.'&amp;part=settings">'.__('Settings').'</a> - 
enhancePostContent - '.$core->plugins->moduleInfo('enhancePostContent','version').'&nbsp;
<img alt="'.__('enhancePostContent').'" src="index.php?pf=enhancePostContent/icon.png" />
</p></body></html>';

?>