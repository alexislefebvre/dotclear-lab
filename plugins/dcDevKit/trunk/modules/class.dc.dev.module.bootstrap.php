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

class dcDevModuleBootstrap extends dcDevModule
{
	protected $has_gui = true;
	
	protected function setInfo()
	{
		$this->name = __('Module bootstrap');
		$this->icon = 'index.php?pf=dcDevKit/img/pluginBootstrap.png';
		$this->description = __('Here, you can create a new plugin/theme');
	}
	
	public function gui($url)
	{
		# Create themes list
		if (empty($this->core->themes)) {
			$this->core->themes = new dcModules($this->core);
			$this->core->themes->loadModules($this->core->blog->themes_path,null);
		}
		
		# Create module
		if (isset($_POST['do_module'])) {
			$type = ($_POST['module_type'] == 'themes') ? 'themes' : 'plugins';
			try {
				$bootstrap = new moduleBootstrap($this->core);
				$bootstrap->createModule($type);
				http::redirect($url.'&tab='.$type.'&new=1');
			}
			catch (Exception $e) {
				$this->core->error->add($e->getMessage());
			}
		}
		
		# ------------
		# Display
		$res = array('plugins' => '','themes' => '');
		
		foreach ($res as $type => $content)
		{
			$redir = '';
			$title = __(substr($type,0,-1));
			$elements = $this->core->{$type}->getModules();
		
			$res[$type] .= '<div class="multi-part" id="'.$type.'" title="'.
			sprintf(__('Create %s'),$title).'">'.
			'<form action="'.$url.'" method="post">';
			
			# Form
			$res[$type] .=
			'<fieldset><legend>'.__('General').'</legend>'.
			'<div class="two-cols"><div class="col">'.
				'<p><label class="field required">'.__('ID:').
				form::field('module_id',60,255,'').
				'</label></p>'.
				'<p><label class="field required">'.__('Name:').
				form::field('module_name',60,255,'').
				'</label></p>'.
				'<p><label class="field required">'.__('Description:').
				form::field('module_description',60,255,'').
				'</label></p>'.
				'<p><label class="field required">'.__('Author:').
				form::field('module_author',60,255,$this->core->blog->settings->dcDevKit->author).
				'</label></p>';
			
			if ($type === 'plugins') {
				$res[$type] .=
				'<p><label class="field">'.__('Permissions:').
				form::field('module_permissions',60,255,'').
				'</label></p>'.
				'<p class="form-note">'.__('Leave blank for default (usage,contentadmin)').'</p>'.
				'<p><label class="field">'.__('Priority:').
				form::field('module_priority',60,255,'').
				'</label></p>'.
				'<p class="form-note">'.__('Leave blank for default (100)').'</p>';
			}
			
			$res[$type] .=
			'</div><div class="col">'.
				'<p><label class="field">'.
				__('Implement behaviors:').
				'</label>'.
				form::textarea('module_behaviors',60,10).
				'</p>'.
				'<p class="form-note">'.__('Enter one behavior name by line. Leave blank to any implementation').'</p>'.
			'</div></div>'.
			'</fieldset>';
			
			if ($type === 'plugins') {
				$res[$type] .=
				'</fieldset>'.
				'<fieldset><legend>'.__('Administration part').'</legend>'.
				'<div class="two-cols"><div class="col">'.
					'<p><label class="classic">'.
					form::checkbox('module_admin_page',1,false).
					__('Add an administration page').
					'</label></p>'.
					'<p><label class="field">'.__('Precise for which permissions:').
					form::field('module_admin_page_permissions',60,255,'').
					'</label></p>'.
					'<p class="form-note">'.__('Leave blank for default (usage,contentadmin)').'</p>'.
					'<p><label class="classic">'.
					form::checkbox('module_admin_widget',1,false).
					__('Add a widget').
					'</label></p>'.
				'</div><div class="col">'.
					'<p><label class="field">'.__('Implement REST/ajax service(s):').
					'</label>'.
					form::textarea('module_admin_services',60,4).
					'</p>'.
					'<p class="form-note">'.__('Enter one service name by line. Leave blank to any implementation').'</p>'.
					'</label></p>'.
				'</div></div>'.
				'</fieldset>'.
				'<fieldset><legend>'.__('Public part').'</legend>'.
				'<div class="two-cols"><div class="col">'.
					'<p><label class="field">'.__('Add handlers:').
					form::field('module_public_page_handlers',60,255,'').
					'</label></p>'.
					'<p class="form-note">'.__('Enter names separated by coma').'</p>'.
				'</div><div class="col">'.
					'<p><label class="field">'.__('Implement template tag(s):').
					'</label>'.
					form::textarea('module_public_page_tags',60,3).
					'</p>'.
					'<p class="form-note">'.__('Enter one template name by line (ex: myValue:value or myBlock:block). Leave blank to any implementation').'</p>'.
					'</label></p>'.
				'</div></div>'.
				'</fieldset>';
			}
			
			$res[$type] .=
			'<p><input type="hidden" name="tab" value="'.$type.'" />'.
			'<input type="hidden" name="module_type" value="'.$type.'" />'.
			'<input type="submit" name="do_module" value="'.sprintf(__('Create new %s'),$title).'" />'.
			$this->core->formNonce().'</p>'.
			'</form>'.
			'</div>';
			
		}
		
		return 
		(isset($_GET['new']) ? '<p class="message">'.__('Module successfully created.').'</p>' : '').
		implode("\n",$res);
	}
	
	public function getConfigForm()
	{
		return;
	}
	
	public function saveConfig()
	{
		return;
	}
}

?>