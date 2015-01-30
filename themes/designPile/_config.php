<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of designPile, a theme for Dotclear.
#
# Original Wordpress Theme from Site5
# http://www.site5.com/wordpress-themes/
#
# Copyright (c) 2010
# annso contact@as-i-am.fr
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) exit;

// Locales
l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/main');

# Default values
$default_color = 'pink';
$separator = ';';
$social_links = array('', '', '/feed/atom');

# Settings
$my_color = $core->blog->settings->themes->designPileColor;

# Color scheme
$designPileColor_combo = array(
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

		# Color scheme
		if (!empty($_POST['designPileColor']) && in_array($_POST['designPileColor'],$designPileColor_combo))
		{
			$my_color = $_POST['designPileColor'];


		} elseif (empty($_POST['designPileColor']))
		{
			$my_color = $default_color;

		}
		$core->blog->settings->themes->put('designPileColor',$my_color,'string','Color display',true);

	// Liens
	$social_links[0] = (!empty($_POST['twitter'])) ? $_POST['twitter'] : '';
	$social_links[1] = (!empty($_POST['facebook'])) ? $_POST['facebook'] : '';
	$social_links[2] = (!empty($_POST['rss'])) ? $_POST['rss'] : '';

	$string = implode($separator, $social_links);
	$core->blog->settings->themes->put('designPileSocialLinks',serialize($string),'string','social_links',true);

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

// Choix de la couleur


# Color scheme
echo
'<div class="fieldset"><h4>'.__('Color').'</h4>'.
'<p class="field"><label>'.__('Color display:').'</label>'.
form::combo('designPileColor',$designPileColor_combo,$my_color).
'</p>';

// Liens sociaux
$url = 'blog_theme.php?shot=designPile&amp;src=img/social/';
echo '<h4>'.__('Social links').'</h4>';
echo '<p><label class="classic"><img style="padding-right: 15px;" src="'.$url.'ico_twitter.png" alt="Twitter" />'.
		form::field('twitter',50,250,html::escapeHTML($social_links[0]),'',1).'</label></p>';
echo '<p><label class="classic"><img style="padding-right: 22px;" src="'.$url.'ico_facebook.png" alt="Facebook" />'.
		form::field('facebook',50,250,html::escapeHTML($social_links[1]),'',1).'</label></p>';
echo '<p><label class="classic"><img style="padding-right: 22px;" src="'.$url.'ico_rss.png" alt="RSS" />'.
		form::field('rss',50,250,html::escapeHTML($social_links[2]),'',1).'</label></p>';
echo '<p class="info">'.__('If you don\'t want to display a link, keep its field empty.').' </p>';
echo '</div>';