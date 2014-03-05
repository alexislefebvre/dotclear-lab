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
$default_style = '1950';

# Settings
$my_style = $core->blog->settings->themes->papierpeint_style;

# style scheme
$papierpeint_style_combo = array(
	"30's" => '1930',
	"50's" => '1950',
	"70's" => '1970'
);

// POST ACTIONS

if (!empty($_POST))
{
	try
	{
		$core->blog->settings->addNamespace('themes');

		# style scheme
		if (!empty($_POST['papierpeint_style']) && in_array($_POST['papierpeint_style'],$papierpeint_style_combo))
		{
			$my_style = $_POST['papierpeint_style'];


		} elseif (empty($_POST['papierpeint_style']))
		{
			$my_style = $default_style;

		}
		$core->blog->settings->themes->put('papierpeint_style',$my_style,'string','Style',true);

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

# Style scheme
echo
'<div class="fieldset"><h4>'.__('Papier Peint styles').'</h4>'.
'<p class="field"><label>'.__('Style:').'</label>'.
form::combo('papierpeint_style',$papierpeint_style_combo,$my_style).
'</p>'.
'</div>';