<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# Copyright (c) 2008 Steven Tlucek
#
# This work is licensed under the Creative Commons
# Attribution-Share Alike 3.0 Unported License.
# To view a copy of this license, visit
# http://creativecommons.org/licenses/by-sa/3.0/ or send a
# letter to Creative Commons, 171 Second Street, Suite 300,
# San Francisco, California, 94105, USA.
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

global $core;

//PARAMS

# Translations
l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/main');

# Default values
$default_color = 'orange';

# Settings
$my_color = $core->blog->settings->themes->warped_color;

# Color scheme
$warped_color_combo = array(
	__('blue') => 'blue',
	__('green') => 'green',
	__('orange') => 'orange'
);

// POST ACTIONS

if (!empty($_POST))
{
	try
	{
		$core->blog->settings->addNamespace('themes');

		# Color scheme
		if (!empty($_POST['warped_color']) && in_array($_POST['warped_color'],$warped_color_combo))
		{
			$my_color = $_POST['warped_color'];


		} elseif (empty($_POST['warped_color']))
		{
			$my_color = $default_color;

		}
		$core->blog->settings->themes->put('warped_color',$my_color,'string','Color display',true);

		// Blog refresh
		$core->blog->triggerBlog();

		// Template cache reset
		$core->emptyTemplatesCache();

		dcPage::success(__('Theme configuration has been successfully updated.'),true,true);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

// DISPLAY

# Color scheme
echo
'<div class="fieldset"><h4>'.__('Colors').'</h4>'.
'<p class="field"><label>'.__('Color display:').'</label>'.
form::combo('warped_color',$warped_color_combo,$my_color).
'</p>'.
'</div>';