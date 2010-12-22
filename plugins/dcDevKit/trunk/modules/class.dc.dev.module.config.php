<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcDevKit, a plugin for Dotclear.
# 
# Copyright (c) 2010 Tomtom, Dsls and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class dcDevModuleConfig extends dcDevModule
{
	protected $has_gui = true;
	
	protected function setInfo()
	{
		$this->name = __('Developers kit configuration');
		$this->icon = 'index.php?pf=dcDevKit/img/config.png';
		$this->description = __('Here, you can set all parameters related of each module');
	}
	
	public function gui($url)
	{
		$devkit = new dcDevKit($this->core);
		
		if (isset($_POST['save'])) {
			$has_error = false;
			foreach ($devkit->getModules() as $m) {
				try {
					$m->saveConfig();
				} catch(Exception $e) {
					$this->core->error->add($e->getMessage());
					$has_error = true;
				}
			}
			
			if (!$has_error) {
				http::redirect($url.'&upd=1');
			}
		}
		
		$res = '';
		
		if (isset($_GET['upd']) && $_GET['upd'] === '1') {
			$res .= '<p class="message">'.__('Configuration has been saved successfully').'</p>';
		}
		
		$res .= '<form action="'.$url.'" method="post" id="devkit-config">';
		
		
		foreach ($devkit->getModules() as $m) {
			$res .= $m->getConfigForm();
		}
		
		$res .=
		$this->core->formNonce().
		'<p><input class="save" name="save" value="'.__('Save configuration').'" type="submit" />'.
		'</p></form>';
		
		return $res;
	}
	
	public function getConfigForm()
	{
		return
		'<fieldset><legend>'.__('General').'</legend>'.
			'<p><label class="field">'.__('Author:').
			form::field('author',40,255,$this->core->blog->settings->dcDevKit->author).
			'</label></p>'.
		'</fieldset>';
	}
	
	public function saveConfig()
	{
		$this->core->blog->settings->dcDevKit->put('author',$_POST['author']);
	}
}

?>