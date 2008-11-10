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

$m_version = $core->plugins->moduleInfo('dcCVI','version');
$i_version = $core->getVersion('dcCVI');

if (version_compare($i_version,$m_version,'>=')) {
	return;
}



# Install procedure

function getCviLib($script_name,$new_name = '')
{
	$zip_url = "http://www.netzgesta.de/".$script_name."/".$script_name.".zip";
	$zip_file = dirname(__FILE__)."/scriptinstallation.zip";
	$destinationname = ($new_name == '' ? $script_name : $new_name);

	netHttp::quickGet($zip_url, $zip_file);
	$zip = new fileUnzip($zip_file);
	$zip->unzip($script_name."/".$script_name.".js",dirname(__FILE__)."/js/".$destinationname.".js");
	unlink($zip_file);
}

function getCviLibs($libs_list)
{
	foreach ($libs_list as $lib_name => $new_name) {
		if (is_numeric($lib_name)) {
			$lib_name = $new_name;
			$new_name = '';
		}
		getCviLib($lib_name, $new_name);
	}
}

$cvi_libs = array (
	"bevel", 
	"corner", 
	"curl", 
	"edge" => "edges", 
	"filmed", 
	"glossy", 
	"instant", 
	"reflex", 
	"slided"
);

$cvi_parameters = array (
	'bevel'	=> "",
	"corner" 	=> " iradius5",
	'curl'	=> "",
	"edges" 	=> " inbuilt isize5",
	'filmed'	=> "",
	'glossy'	=> "",
	"instant" => "",
	'reflex'	=> "",
	'slided'	=> ""
);

$settings = new dcSettings($core,null);
$settings->setNameSpace('cvi');
$settings->put('cvi_enabled','','boolean','is CVI enabled ?',true);
$settings->put('cvi_effect','','string','chosen CVI effect',true);
$settings->put('cvi_effect_parameters',serialize($cvi_parameters),'string','CVI effect tweaking',true);

if (!is_writable(dirname(__FILE__)."/js")) {
	if (!chmod(dirname(__FILE__)."/js", 0755)) {
		$core->setVersion('dcCVI',$m_version);
		throw new Exception(__('js subdirectory is not writable. Please download the files manually.'));
	}
}

getCviLibs($cvi_libs);

$core->setVersion('dcCVI',$m_version);

return true;
?>