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
	$usable[$thing] = $page['class']->can($thing.'Before',$thing.'Content');
}

# Profil features and settings
$markers = $page['class']->getMarkers();
$s_action = $page['class']->getMarker('action',array());
$s_title = $page['class']->getMarker('title','');
$s_homeonly = $page['class']->getMarker('homeonly',false);

# Save settings
if ($request_act == 'save')
{
	try
	{
		foreach($markers as $place => $marker)
		{
			foreach($marker['action'] as $action)
			{
				if (empty($usable[$action])) continue;
				
				$s_action[$place][$action] = !empty($_POST['s_action_'.$place.'_'.$action]) && is_array($_POST['s_action_'.$place.'_'.$action]) ?
					array_keys($_POST['s_action_'.$place.'_'.$action]) : array();
			}
			
			if (!empty($marker['title']))
			{
				$s_title[$place] = !empty($_POST['s_title_'.$place]) ? $_POST['s_title_'.$place] : '';
			}
			if (!empty($marker['homeonly']))
			{
				$s_homeonly[$place] = !empty($_POST['s_homeonly_'.$place]);
			}
		}
		
		# save
		$page['setting']->put('action',soCialMeUtils::encode($s_action));
		$page['setting']->put('title',soCialMeUtils::encode($s_title));
		$page['setting']->put('homeonly',soCialMeUtils::encode($s_homeonly));
		
		$core->blog->triggerBlog();
		
		http::redirect(soCialMeAdmin::link(0,$request_page,$request_part,'','&section='.$request_section));
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

# Settings form
echo soCialMeAdmin::top($page,dcPage::jsLoad('index.php?pf=soCialMe/js/action.js')).
'<p>'.__('Configure stream you want to use.').'</p>'.
'<form id="action-form" method="post" action="'.soCialMeAdmin::link(1,$request_part).'">';

foreach($markers as $place => $marker)
{
	$display = false;
	foreach($marker['action'] as $action)
	{
		if (!empty($usable[$action])) { $display = true; break; }
	}
	if (!$display) continue;
	
	echo 
	'<fieldset id="action-'.$place.'"><legend>'.$marker['name'].'</legend>'.
	'<p>'.$marker['description'].'</p>';
	
	if ($marker['title'])
	{
		echo 
		'<div class="two-cols"><div class="col">'.
		'<p><label class="classic">'.__('Title of the stream:').'<br />'.
		form::field(array('s_title_'.$place),50,255,$s_title[$place]).
		'</label></p>'.
		'</div><div class="col">';
	}
	
	if (!empty($marker['homeonly']))
	{
		echo 
		'<p><label class="classic">'.
		form::checkbox(array('s_homeonly_'.$place),'1',!empty($s_homeonly[$place])).' '.
		__('On home page only').'</label></p>';
	}
	
	if (!empty($marker['title']))
	{
		echo '</div></div>';
	}
	
	echo 
	'<p class="clear">'.__('Select services to use:').'</p>'.
	'<div class="three-cols">';
	
	$i = 0;
	foreach($marker['action'] as $action)
	{
		if (empty($usable[$action])) continue;

		$i++;
		if ($i == 4) {
			$i = 0;
			echo '</div><div class="clear three-cols">';
		}
		
		echo '<div class="col"><div class="socialbox"><h4>'.$things[$action].'</h4><ul>';
		
		foreach($usable[$action] as $service_id)
		{
			$service = $page['class']->services($service_id);
			$check = isset($s_action[$place][$action]) && in_array($service_id,$s_action[$place][$action]);
			
			echo 
			'<li><label class="classic">'.
			form::checkbox(array('s_action_'.$place.'_'.$action.'['.$service_id.']'),'1',$check).
			($service->icon ? '<img src="'.$service->icon.'" alt="'.$service->name.'" /> ' : '').
			$service->name.'</label></li>';
		}
		echo '</ul></div></div>';
	}
	echo '</div></fieldset>';
}

echo 
'<div class="clear">'.
'<p><input type="submit" name="save" value="'.__('save').'" />'.
$core->formNonce().
form::hidden(array('p'),'soCialMe').
form::hidden(array('page'),$request_page).
form::hidden(array('part'),$request_part).
form::hidden(array('section'),$request_section).
form::hidden(array('act'),'save').
'</p></div>'.
'</form>';

?>