<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2008 Xavier Plantefeve and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

$core->addBehavior('adminBlogPreferencesForm',array('dcCviBehaviors','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('dcCviBehaviors','adminBeforeBlogSettingsUpdate'));

class dcCviBehaviors
{
	public static function adminBlogPreferencesForm(&$core,&$settings)
	{
		# CVI modes
		$cvi_effect_combo = array(
			'Bevel' => 'bevel',
			'Corner' => 'corner',
			'Curl' => 'curl',
			'Edge' => 'edges',
			'Filmed' => 'filmed',
			'Glossy' => 'glossy',
			'Instant' => 'instant',
			'Reflex' => 'reflex',
			'Slided' => 'slided'
		);
		
		echo
		'<fieldset><legend>CVI</legend>'.
		'<p><label class="classic">'.
		form::checkbox('cvi_enabled','1',$settings->cvi_enabled).
		__('Enable CVI').'</label></p>'.
		'<p><label>'.__('CVI effect:').
		form::combo('cvi_effect',$cvi_effect_combo,$settings->cvi_effect).'</label></p>'.
		'</fieldset>';
	}
	
	public static function adminBeforeBlogSettingsUpdate(&$settings)
	{
		$settings->setNameSpace('cvi');
		$settings->put('cvi_enabled',!empty($_POST['cvi_enabled']));
		$settings->put('cvi_effect',$_POST['cvi_effect']);
		$settings->setNameSpace('system');
	}
}
?>
