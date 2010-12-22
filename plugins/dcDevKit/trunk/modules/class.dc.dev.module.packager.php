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

class dcDevModulePackager extends dcDevModule
{
	protected $has_gui = true;
	
	protected function setInfo()
	{
		$this->name = __('Packager');
		$this->icon = 'index.php?pf=dcDevKit/img/packager.png';
		$this->description = __('Here, you can create a package of your plugins/themes');
	}
	
	public function gui($url)
	{
		# Create themes list
		if (empty($this->core->themes)) {
			$this->core->themes = new dcModules($this->core);
			$this->core->themes->loadModules($this->core->blog->themes_path,null);
		}
		
		# Build package(s)
		if (isset($_POST['do_package'])) {
			$type = ($_POST['addons_type'] == 'plugins') ? 'plugins' : 'themes';
			$prefix = substr($type,0,-1).'-';
			
			try
			{
				foreach (array_keys($_POST['pack']) as $ext_id) {
					if (!$this->core->{$type}->moduleExists($ext_id)) {
						throw new Exception(__('No such '.substr($type,0,-1).' ('.$ext_id.').'));
					}
					$ext = $this->core->{$type}->getModules($ext_id);
					$ext['id'] = $ext_id;
		
					# --BEHAVIOR-- packagerBeforeCreate
					$this->core->callBehavior('dcDevKitPackagerBeforeCreate', $type, $ext);
					
					zipBuilder::pack($ext,$prefix);
					
					# --BEHAVIOR-- packagerAfterCreate
					$this->core->callBehavior('dcDevKitPackagerAfterCreate', $type, $ext);
				}
				
				http::redirect($url.'&tab='.$type.'&pkg=1');
			}
			catch (Exception $e)
			{
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
			sprintf(__('Pack %s'),$title).'">';
			
			if (!empty($elements) && is_array($elements)) 
			{
				$res[$type] .=
				'<form action="'.$url.'" method="post">'.
				'<table class="clear"><tr>'.
				'<th colspan="2">'.ucfirst($title).'</th>'.
				'<th class="nowrap">'.__('Version').'</th>'.
				'<th class="nowrap">'.__('Description').'</th>'.
				'</tr>';
			
				foreach ($elements as $k => $v)
				{	
					$res[$type] .=
					'<tr class="line">'.
					'<td>'.form::checkbox(array('pack['.html::escapeHTML($k).']'),1).'</td>'.
					'<td class="minimal nowrap">'.html::escapeHTML($v['name']).'</td>'.
					'<td class="minimal">'.html::escapeHTML($v['version']).'</td>'.
					'<td class="maximal">'.html::escapeHTML($v['desc']).'</td>'.
					'</tr>';
				}
				$res[$type] .=
				'</table>'.
				'<p><input type="hidden" name="addons_type" value="'.$type.'" />';
				
				if (!empty($redir)) {
					$res[$type] .=
					'<input type="hidden" name="redir" value="'.html::escapeHTML($redir).'" />';
				}
				
				$res[$type] .=
				'<input type="submit" name="do_package" value="'.sprintf(__('Pack selected %s'),$type).'" />'.
				$this->core->formNonce().'</p>'.
				'</form>';
			}
			else
			{
				$res[$type] .= '<p><strong>'.__('No available '.$type).'</strong></p>';
			}
			
			$res[$type] .= '</div>';
		}
		
		return 
		(isset($_GET['pkg']) ? '<p class="message">'.__('Package(s) successfully created.').'</p>' : '').
		implode("\n",$res);
	}
	
	public function getConfigForm()
	{
		return
		'<fieldset><legend>'.__('Packager').'</legend>'.
		'<p><label class="field">'.__('Repository path:').
		form::field('repository',40,255,$this->core->blog->settings->dcDevKit->packager_repository).
		'</label></p>'.
		'<p><label class="classic">'.
		form::checkbox('minify_js',1,$this->core->blog->settings->dcDevKit->packager_minify_js).
		__('Minify *.js files').
		'</label></p>'.
		'<p><label class="classic">'.
		form::checkbox('minify_css',1,$this->core->blog->settings->dcDevKit->packager_minify_css).
		__('Also minify *.css files').
		'</label></p>'.
		'<p><label class="field">'.__('Files/Folders to exclude:').
		form::field('to_exclude',40,255,$this->core->blog->settings->dcDevKit->packager_to_exclude).
		'</label></p>'.
		'<p class="form-note">'.__('Exclude automatically ., .., .svn, CVS, .DS_Store, Thumbs.db').'</p>'.
		'</fieldset>';
	}
	
	public function saveConfig()
	{
		$this->core->blog->settings->dcDevKit->put('packager_repository',$_POST['repository']);
		$this->core->blog->settings->dcDevKit->put('packager_minify_js',$_POST['minify_js']);
		$this->core->blog->settings->dcDevKit->put('packager_minify_css',$_POST['minify_css']);
		$this->core->blog->settings->dcDevKit->put('packager_to_exclude',$_POST['to_exclude']);
	}
}

?>