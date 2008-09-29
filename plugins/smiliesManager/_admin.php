<?php
# ***** BEGIN LICENSE BLOCK *****
# This is Smilies Manager, a plugin for DotClear. 
# Copyright (c) 2005 k-net. All rights reserved.
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

if ($core->auth->isSuperAdmin()) {
	$_menu['Plugins']->addItem(__('Smilies Manager'),'plugin.php?p=smiliesManager','index.php?pf=smiliesManager/icon.png',
			preg_match('/plugin.php\?p=smiliesManager(&.*)?$/',$_SERVER['REQUEST_URI']),
			$core->auth->check('usage,contentadmin',$core->blog->id));
}
if ($core->blog->settings->smiliesmanager_admintoolbar) {
	$core->addBehavior('adminPostHeaders',array('smiliesManagerAdmin','adminPostHeaders'));
}

class smiliesManagerAdmin
{
	public static function setSmilies($theme, $smilies) {
		global $core;
		
		if (!empty($theme) && is_array($smilies)) {
			
			$definition = $core->blog->themes_path.'/'.$theme.'/smilies/smilies.txt';
			
			if (is_writable($definition) || (!file_exists($definition) && is_writable(dirname($definition)))) {
				$fcontent = '';
				
				foreach ($smilies as $smiley) {
					$fcontent .= $smiley['code']."\t\t".basename($smiley['url'])."\r\n";
				}
				
				file_put_contents($definition, $fcontent);
				
				return true;
			}
		}
		
		return false;
	}
	
	public static function setConfig($smilies) {
		global $core;
		
		if (is_array($smilies)) {
			
			$config = array();
			
			foreach ($smilies as $smiley) {
				if ($smiley['onToolbar']) {
					$config[] = $smiley['code'];
				}
			}
			$core->blog->settings->setNamespace('smiliesmanager');
			$core->blog->settings->put('smiliesmanager_smiliesontoolbar',serialize($config),'string');
			$core->blog->triggerBlog();
			
			return true;
		}
		
		return false;
	}
	
	public static function autoEditTpl_isInstalled($theme = '') {
		global $core;
		
		if (!empty($theme)) {
			$tpl_file = $core->blog->themes_path.'/'.$theme.'/post.html';
			
			if (file_exists($tpl_file) && is_writable($tpl_file)) {
				$fcontent = file_get_contents($tpl_file);
				
				if (FALSE !== strpos($fcontent, '{{tpl:SmiliesManagerToolbar textarea="c_content"}}')) {
					return true;
				}
				return false;
			}
		}
		return null;
	}
	
	public static function autoEditTpl_install($theme = '') {
		global $core;
		
		if (!empty($theme)) {
			$tpl_file = $core->blog->themes_path.'/'.$theme.'/post.html';
			
			if (file_exists($tpl_file) && is_writable($tpl_file)) {
				$fcontent = file_get_contents($tpl_file);
				
				if (FALSE !== strpos($fcontent, '<p class="field"><label for="c_content">')) {
					$fcontent = str_replace(
						'<p class="field"><label for="c_content">',
						'{{tpl:SmiliesManagerToolbar textarea="c_content"}}<p class="field"><label for="c_content">',
						$fcontent);
					file_put_contents($tpl_file, $fcontent);
					return true;
				}
			}
		}
		return false;
	}
	
	public static function autoEditTpl_uninstall($theme = '') {
		global $core;
		
		if (!empty($theme)) {
			$tpl_file = $core->blog->themes_path.'/'.$theme.'/post.html';
			
			if (file_exists($tpl_file) && is_writable($tpl_file)) {
				$fcontent = file_get_contents($tpl_file);
				
				if (FALSE !== strpos($fcontent, '{{tpl:SmiliesManagerToolbar textarea="c_content"}}')) {
					$fcontent = str_replace(
						'{{tpl:SmiliesManagerToolbar textarea="c_content"}}',
						'',
						$fcontent);
					file_put_contents($tpl_file, $fcontent);
					return true;
				}
			}
		}
		return false;
	}
	
	public static function adminPostHeaders() {
		$res = '<script type="text/javascript">'."\n".
		"//<![CDATA[\n";
		$smilies = smiliesManager::getSmilies();
		foreach ($smilies as $id => $smiley) {
			if ($smiley['onToolbar']) {
				$res .= "jsToolBar.prototype.elements.smiliesmanager_s".$id." = {type: 'button', title: '".html::escapeJS($smiley['code'])."', fn:{} }; ".
					"jsToolBar.prototype.elements.smiliesmanager_s".$id.".context = 'post'; ".
					"jsToolBar.prototype.elements.smiliesmanager_s".$id.".icon = '".html::escapeJS($smiley['url'])."'; ".
					"jsToolBar.prototype.elements.smiliesmanager_s".$id.".fn.wiki = function() { this.encloseSelection('','',function(str) { return '".html::escapeJS($smiley['code'])."'; } ); }; ".
					"jsToolBar.prototype.elements.smiliesmanager_s".$id.".fn.xhtml = function() { this.encloseSelection('','',function(str) { return '".html::escapeJS($smiley['code'])."'; } ); }; ".
					#"jsToolBar.prototype.elements.smiliesmanager_s".$id.".fn.wysiwyg = function() { var img = document.createElement('img'); img.src = '".html::escapeJS($smiley['url'])."'; img.alt = '".html::escapeJS($smiley['code'])."'; img.title = '".html::escapeJS($smiley['code'])."'; this.insertNode(img); };\n".
					"jsToolBar.prototype.elements.smiliesmanager_s".$id.".fn.wysiwyg = function() {
						smiley = document.createTextNode('".html::escapeJS($smiley['code'])."');
						this.insertNode(smiley);
					};\n";
			}
		}
		$res .= "</script>\n";
		return $res;
	}
}

?>