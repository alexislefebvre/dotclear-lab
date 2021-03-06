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

if (!defined('DC_CONTEXT_ADMIN')){return;}# Save services settings// allways call as it could be used as "top of page behavior"try{	foreach($page['class']->services() as $service_id => $service)	{		$service->adminSave($request_lib,soCialMeAdmin::link(0,$request_page,$request_part,$service_id));	}}catch (Exception $e){	$core->error->add($e->getMessage());}# Displayecho soCialMeAdmin::top($page).'<p>'.__('Configure options for each service.').'</p>'.'<div class="two-cols">';# Services settings form$i = 0;$no_config = '';foreach($page['class']->services() as $service_id => $service){		if (null === ($rsp = $service->adminForm($request_lib,soCialMeAdmin::link(0,$request_page,$request_part,$service_id))))	{		$no_config .= 		'<li>'.		($service->icon ? '<img src="'.$service->icon.'" alt="'.$service_id.'" /> ' : '').		($service->home ? '<a title="'.__('homepage').'" href="'.$service->home.'">'.$service->name.'</a>' : $service->name).		'</li>';	}	else	{		$i++;				echo '<div class="col">'.		'<div class="socialbox" id="service-'.$service_id.'">'.		'<h4>'.($service->icon ? '<img src="'.$service->icon.'" alt="'.$service_id.'" /> ' : '').$service->name.'</h4>';		if ($service->home)		{			echo 			'<p>'.			'<a title="'.sprintf(__('homepage of '),$service->name).'" href="'.$service->home.'">'.			sprintf(__('Learn more about %s.'),$service->name).'</a>			</p>';		}		echo 		$rsp.'</div></div>';	}		if ($i == 2) {		echo '</div><div class="clear two-cols">';		$i = 0;	}}if (!empty($no_config)){		echo 		'<div class="col">'.		'<div class="socialbox" id="service-noconfig">'.		'<h4>'.__('The following services have no configuration.').'</h4>'.		'<ul>'.$no_config.'</ul>'.		'</div></div>';}echo'</div><p class="clear">&nbsp;</p>';?>