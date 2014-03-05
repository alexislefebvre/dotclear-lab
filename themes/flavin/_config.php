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
$default_color = 'pink';

# Settings
$my_color = $core->blog->settings->themes->flavin_color;

# Color type
$flavin_color_combo = array(
	__('pink') => 'pink',
	__('blue') => 'blue',
	__('green') => 'green'
);

// POST ACTIONS

if (!empty($_POST))
{
	try
	{
		$core->blog->settings->addNamespace('themes');

		# Color type
		if (!empty($_POST['flavin_color']) && in_array($_POST['flavin_color'],$flavin_color_combo))
		{
			$my_color = $_POST['flavin_color'];

		} elseif (empty($_POST['flavin_color']))
		{
			$my_color = $default_color;

		}
		$core->blog->settings->themes->put('flavin_color',$my_color,'string','Color display',true);

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

# Color type
echo
'<div class="fieldset"><h4>'.__('Colors').'</h4>'.
'<p class="field"><label>'.__('Display:').'</label>'.
form::combo('flavin_color',$flavin_color_combo,$my_color).
'</p>'.
'</div>';