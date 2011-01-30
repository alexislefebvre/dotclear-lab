<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of soCialMe, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Services features
foreach($page['class']->things() as $thing => $title)
{
	$things[$thing] = __($title);
	$available[$thing] = $page['class']->can($thing.'Content',$thing.'Script');
}

# Read settings
$s_order = $page['class']->fillOrder($available,true);

# Save settings
if ($request_act == 'save')
{
	try
	{
		foreach($things as $thing => $plop)
		{
			$order = $new_order = array();
			
			if (empty($_POST['js_orders_'.$thing]) && !empty($_POST['s_orders_'.$thing]))
			{
				$order = $_POST['s_orders_'.$thing];
				asort($order);
				$order = array_keys($order);
			}
			elseif (!empty($_POST['js_orders_'.$thing]))
			{
				$order = explode(',',$_POST['js_orders_'.$thing]);
			}
			if (!empty($order))
			{
				foreach ($order as $pos => $id)
				{
					$pos = ((integer) $pos)+1;
					if (!empty($pos) && !empty($id))
					{
						$new_order[$pos] = $id;
					}
				}
				$s_order[$thing] = $new_order;
			}
		}
		
		$page['setting']->put('order',base64_encode(serialize($s_order)));
		
		$core->blog->triggerBlog();
		
		http::redirect(soCialMeUtils::link(0,$request_page,$request_part));
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

# Settings form
echo soCialMeUtils::top($page,dcPage::jsToolMan().soCialMeUtils::multiDragsortScript($things)).
'<p>'.__('Configure the order of the services of this part.').'</p>'.
'<form id="setting-form" method="post" action="'.soCialMeUtils::link(1,$request_page).'">'.

'<fieldset id="setting-priority"><legend>'. __('Priority').'</legend>'.
'<p>'.__('Select display order of services for each location.').'</p>';

$i = $j = 0;
foreach($s_order as $thing => $orders)
{
	
	if ($i == 0) {
		echo '<div class="clear three-cols">';
	}
	$i++; $j++;
	echo 
	'<div class="col"><div class="socialbox">'.
	'<h4>'.__($thing).'</h4>'.
	'<table class="dragable maximal" summary="'.sprintf(__('List of services for %s'),$things[$thing]).'">'.
	'<tbody id="priority-list-'.$thing.'">';
	
	foreach($orders as $order => $service_id)
	{
		$service = $page['class']->services($service_id);
		$position = $order + 1;
		
		echo
		'<tr class="line" id="l'.$j.'_'.$service_id.'">'.
		'<td class="handle minimal">'.
		form::field(array('s_orders_'.$thing.'['.$service_id.']'),2,5,$position).
		'</td><td class="minimal">'.
		($service->icon ? '<img src="'.$service->icon.'" alt="'.$service->name.'" />' : '&nbsp;').
		'</td><td class="maximal">'.
		$service->name.
		'</td><td class="minimal">'.$position.'</td>'.
		'</tr>';
	}
	
	echo 
	'</tbody>'.
	'</table>'.
	'</div></div>';
	
	if ($i == 3) {
		echo '</div>';
		$i = 0;
	}
}

echo 
'</fieldset>'. 
'<div class="clear">'.
'<p><input type="submit" name="save" value="'.__('save').'" />';

# Add hidden fields for dragsort javascript
foreach($things as $thing => $plop) { echo form::hidden('js_orders_'.$thing,''); }

echo 
$core->formNonce().
form::hidden(array('p'),'soCialMe').
form::hidden(array('page'),$request_page).
form::hidden(array('part'),$request_part).
form::hidden(array('act'),'save').
'</p></div>'.
'</form>';

?>