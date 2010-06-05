<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of translater, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_TRANSLATER') || DC_CONTEXT_TRANSLATER != 'lang'){return;}

# Update language
if ($action == 'update_lang')
{
	try
	{
		if (empty($_POST['entries']) || empty($lang) || empty($module))
		{
			throw new Exception(__('No language to update'));
		}
		foreach($_POST['entries'] as $i => $entry)
		{
			if (isset($entry['check']) && isset($_POST['multigroup']))
			{
				$_POST['entries'][$i]['group'] = $_POST['multigroup'];
				unset($_POST['entries'][$i]['check']);
			}
		}
		$O->updLang($module,$lang,$_POST['entries']);
		
		http::redirect($p_url.'&part=lang&module='.$module.'&type='.$type.'&lang='.$lang.'&msg='.$action);
	}
	catch (Exception $e)
	{
		$core->error->add(sprintf($errors[$action],$e->getMessage()));
	}
}

# Get infos on module wanted
try
{
	$M = $O->getModule($module,$type);
	
	# Retrieve some infos
	$M->langs = $O->listLangs($module);
	$M->backups = $O->listBackups($module);
	$M->unused_langs = array_flip(array_diff($O->getIsoCodes(),$M->langs));
	$M->used_langs = array_flip(array_diff($M->langs,array_flip($O->getIsoCodes())));
	$allowed_groups = array_combine(
		dcTranslater::$allowed_l10n_groups,
		dcTranslater::$allowed_l10n_groups
	);
}
catch(Exception $e)
{
	$core->error->add(sprintf(__('Failed to launch translater: %s'),$e->getMessage()));
	$action = $module = $type = '';
	$M = false;
}
if (!empty($module) && !empty($type) && !$M)
{
	$action = $module = $type = '';
	$M = false;
}


echo '
<html>
<head><title>'.__('Translater').' - '.__('Lang').'</title>'.$header;

# --BEHAVIOR-- translaterAdminHeaders
$core->callBehavior('translaterAdminHeaders');

echo 
'</head>
<body>'.$menu.
'<h3><a href="'.$p_url.'&amp;part=modules&type='.$type.'">'.($type == 'theme' ? __('Themes') : __('Plugins')).'</a>'.
' &rsaquo; "<a href="'.$p_url.'&amp;part=module&type='.$type.'&module='.$module.'&section=lang">'.$M->name.'</a>"';

if (!empty($M->langs) && isset($M->langs[$lang]))
{
	echo ' &rsaquo; '.$M->langs[$lang];
}
echo '</h3>'.$msg;

# Existing langs
if (!empty($M->langs) && isset($M->langs[$lang]))
{
	$iso = $M->langs[$lang];
	
	$i = 0;
	$sort_order = 'asc';
	$lines = $O->getMsgs($module,$lang);
	
	# Sort array
	if (isset($_GET['sort']) && !empty($lines))
	{
		$sort = explode(',',$_GET['sort']);
		$sort_by = $sort[0];
		$sort_order = isset($sort[1]) && $sort[1] == 'desc' ? 'asc' : 'desc';
		
		switch($sort_by)
		{
			case 'group':
			foreach($lines AS $k => $v)
			{
				$sort_list[] = $v['group'];
			}
			break;
			
			case 'msgid':
			foreach($lines AS $k => $v)
			{
				$sort_list[] = strtolower($k);
			}
			break;
			
			case 'file':
			foreach($lines AS $k => $v)
			{
				$file = array();
				foreach($v['files'] as $fv)
				{
					$file[] = empty($fv[0]) || empty($fv[1]) ? '' : $fv[0].($fv[1] /1000);
				}
				sort($file);
				$sort_list[] = $file[0];
			}
			break;
			
			case 'msgstr':
			foreach($lines AS $k => $v)
			{
				$sort_list[] = strtolower($v['msgstr']);
			}
			break;
			
			default:
			$sort_list = false;
			break;
		}
		if ($sort_list)
		{
			array_multisort(
				$sort_list,
				($sort_order == 'asc' ? SORT_DESC : SORT_ASC),
				SORT_STRING,
				$lines
			);
		}
	}
	
	echo 
	'<div id="lang-form" title="'.$iso.'">'.
	'<form method="post" action="plugin.php">'.
	'<table>'.
	'<tr>'.
	'<th><a href="'.$p_url.'&amp;part=lang&amp;module='.$module.'&amp;type='.$type.'&amp;lang='.$lang.
	'&amp;sort=group,'.$sort_order.'">'.__('Group').'</a></th>'.
	'<th><a href="'.$p_url.'&amp;part=lang&amp;module='.$module.'&amp;type='.$type.'&amp;lang='.$lang.
	'&amp;sort=msgid,'.$sort_order.'">'.__('String').'</a></th>'.
	'<th><a href="'.$p_url.'&amp;part=lang&amp;module='.$module.'&amp;type='.$type.'&amp;lang='.$lang.
	'&amp;sort=msgstr,'.$sort_order.'">'.__('Translation').'</a></th>'.
	'<th>'.__('Existing').'</th>'.
	'<th><a href="'.$p_url.'&amp;part=lang&amp;module='.$module.'&amp;type='.$type.'&amp;lang='.$lang.
	'&amp;sort=file,'.$sort_order.'">'.__('File').'</a></th>'.
	'</tr>';
	
	foreach ($lines AS $msgid => $rs)
	{
		$i++;
		$in_dc = ($rs['in_dc'] && $O->parse_nodc);
		echo 
		'<tr class="line'.($in_dc ? ' offline' : ' translaterline').'">'.
		
		'<td class="nowrap">'.
		form::checkbox(array('entries['.$i.'][check]'),1).' '.
		form::combo(array('entries['.$i.'][group]'),
			$allowed_groups,$rs['group'],'','',$in_dc
		).
		'</td>'.
		
		'<td'.('' != $O->proposal_tool ? ' class="translatermsgid"' : '' ).'>'.
		html::escapeHTML($msgid).'</td>'.
		
		'<td class="nowrap translatertarget">'.
		form::hidden(array('entries['.$i.'][msgid]'),html::escapeHTML($msgid)).
		form::field(array('entries['.$i.'][msgstr]'),
			75,255,html::escapeHTML($rs['msgstr']),'','',$in_dc).
		'</td>'.
		
		'<td class="translatermsgstr">';
		$strin = array();
		foreach($rs['o_msgstrs'] AS $o_msgstr)
		{
			if (!isset($strin[$o_msgstr['msgstr']]))
			{
				$strin[$o_msgstr['msgstr']] = '';
			}
			$strin[$o_msgstr['msgstr']][] = array('module'=>$o_msgstr['module'],'file'=>$o_msgstr['file']);
		}
		foreach($strin as $k => $v)
		{
			echo '<strong>'.html::escapeHTML($k).'</strong><div class="strlist">';
			foreach($v as $str)
			{
				echo '<i>'.html::escapeHTML($str['module'].' => '.$str['file']).'</i><br />';
			}
			echo '</div><br />';
		}
		echo 
		'</td>'.
		
		'<td class="nowrap translatermsgfile">';
		if (empty($rs['files'][0]))
		{
			echo '&nbsp;';
		}
		elseif (count($rs['files']) == 1)
		{
			echo $rs['files'][0][0].' : '.$rs['files'][0][1];
		}
		else
		{
			echo
			'<strong>'.sprintf(__('%s files'),count($rs['files'])).'</strong>'.
			'<div class="strlist">';
			foreach($rs['files'] as $location)
			{
				echo '<i>'.implode(' : ',$location).'</i><br />';
			}
			echo '</div>';
		}
		echo
		'</td>'.
		
		'</tr>';
	}
	
	$i++;
	echo 
	'<tr>'.
	'<td class="nowrap">'.
	form::checkbox(array('entries['.$i.'][check]'),1).' '.
	form::combo(array('entries['.$i.'][group]'),$allowed_groups,'main').
	'</td>'.
	'<td class="">'.form::field(array('entries['.$i.'][msgid]'),75,255,'').'</td>'.
	'<td class="nowrap">'.form::field(array('entries['.$i.'][msgstr]'),75,255,'').'</td>'.
	'<td class="">&nbsp;</td>'.
	'<td class="">&nbsp;</td>'.
	'</tr>'.
	'</table>'.
	'<p>'.sprintf(__('Total of %s strings.'),$i-1).'</p>'.
	'<p class="col checkboxes-helpers"></p>'.
	'<p class="col right">'.__('Change the group of the selected entries to:').' '.
	form::combo(array('multigroup'),$allowed_groups).
	'</p>'.
	'<p>'.
	'<input type="submit" name="save" value="'.__('save').'" />'.
	$core->formNonce().
	form::hidden(array('lang'),$lang).
	form::hidden(array('type'),$type).
	form::hidden(array('module'),$module).
	form::hidden(array('action'),'update_lang').
	form::hidden(array('part'),'lang').
	form::hidden(array('p'),'translater').
	'</p>'.
	'</form>'.
	'<p>&nbsp;</p>'.
	'</div>';
}

dcPage::helpBlock('translater');
echo $footer.'</body></html>';
?>