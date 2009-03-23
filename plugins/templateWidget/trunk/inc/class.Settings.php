<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of templateWidget
# Copyright (c) 2009 Olivier Azeau and contributors. All rights reserved.
#
# This is free software; you can redistribute it and/or modify
# it under the terms of the Affero GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
# 
# This is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the Affero GNU General Public License
# along with templateWidget; If not, see <http://www.gnu.org/licenses/>.
#
# templateWidget is a plugin for Dotclear software.
# templateWidget is not part of Dotclear.
# templateWidget can be used inside Dotclear but this license only applies to templateWidget
# ***** END LICENSE BLOCK *****

require_once(dirname(__FILE__).'/class.SettingsList.php');

class templateWidgetActive
{
  public $isActive = false, $id;
  private $name, $description;
  
  public function Display($settings) {
    print
      form::hidden($settings->GetHttpDefinition('id'), html::escapeHTML($this->id)).
      '<p><label class="classic">'.
      ' '.form::checkbox($settings->GetHttpDefinition('isActive'), html::escapeHTML($this->id), $this->isActive).
      ' '.html::escapeHTML($this->name).
      ' ('.html::escapeHTML($this->description).')'.
      '</label></p>'."\n";
  }
  
  public static function FromWidgetDefinition($widgetDefinition) {
    $widgetActivity = new self();
    $widgetActivity->id = $widgetDefinition['id'];
    $widgetActivity->name = @$widgetDefinition['name'];
    $widgetActivity->description = @$widgetDefinition['description'];
    return $widgetActivity;
  }
}

class templateWidgetSettings extends SettingsList
{
	function __construct()
	{
		parent::__construct('templateWidget','active','templateWidgetActive','id', array('isActive'=>'checkbox'));
  }
}

?>