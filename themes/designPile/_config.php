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

echo '<script type="text/javascript" src="js/_blog_theme.js"></script>';

// Initialisation
$separator = ' ';
$color = "pink";
$social_links = array('', '', '');

// Lecture des settings
$core->blog->settings->setNameSpace('designPile');
if ($core->blog->settings->designPileColor) {
	$color = @unserialize($core->blog->settings->designPileColor);
} else {
	$core->blog->settings->put('designPileColor',serialize($color),'string');
	$core->blog->triggerBlog();
}
if ($core->blog->settings->designPileSocialLinks) {
	$social_links = @unserialize($core->blog->settings->designPileSocialLinks);
} else {
	$social_links[2] = $core->blog->url.$core->url->getBase("feed")."/atom";	
	$string = implode($separator, $social_links);
	$core->blog->settings->put('designPileSocialLinks',serialize($string),'string');
	$core->blog->triggerBlog();
}

// Mise à jour des settings
if (!empty($_POST))
{
	// Vidage du cache
	try {
		$core->emptyTemplatesCache();
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}

	// Couleur
	$color = (!empty($_POST['color'])) ? $_POST['color'] : '';
	$core->blog->settings->put('designPileColor',serialize($color),'string');

	// Liens
	$social_links[0] = (!empty($_POST['twitter'])) ? $_POST['twitter'] : '';
	$social_links[1] = (!empty($_POST['facebook'])) ? $_POST['facebook'] : '';
	$social_links[2] = (!empty($_POST['rss'])) ? $_POST['rss'] : '';
	
	$string = implode($separator, $social_links);
	$core->blog->settings->put('designPileSocialLinks',serialize($social_links),'string');
	$core->blog->triggerBlog();
	
	echo '<p class="message">'.__('La configuration du thème a été mise à jour avec succès.').'</p>';	

}

// Choix de la couleur
$url = 'blog_theme.php?shot=designPile&amp;src=img/config/';
echo '<fieldset><legend>'.__('Color').'</legend>';
echo '<p>';

$pink = '<label class="classic" style="padding: 0 5px">'.form::radio('color', 'pink').
			'<img src="'.$url.'rose.jpg" alt="Rose" />'.'</label>';
$green = '<label class="classic" style="padding: 0 5px">'.form::radio('color', 'green').
			'<img src="'.$url.'vert.jpg" alt="Vert" />'.'</label>';
$blue = '<label class="classic" style="padding: 0 5px">'.form::radio('color', 'blue').
			'<img src="'.$url.'bleu.jpg" alt="Bleu" />'.'</label>';
if($color == 'pink') echo $green.$blue.$pink;
if($color == 'green') echo $blue.$pink.$green;
if($color == 'blue') echo $pink.$green.$blue;
echo '</p>';

// Liens sociaux
$url = 'blog_theme.php?shot=designPile&amp;src=img/social/';
echo '<fieldset><legend>'.__('Social links').'</legend>';
echo '<div><p><label class="classic"><img style="padding-right: 15px;" src="'.$url.'ico_twitter.png" alt="Twitter" />'.
		form::field('twitter',50,250,html::escapeHTML($social_links[0]),'',1).'</label></p></div>';
echo '<div><p><label class="classic"><img style="padding-right: 22px;" src="'.$url.'ico_facebook.png" alt="Facebook" />'.
		form::field('facebook',50,250,html::escapeHTML($social_links[1]),'',1).'</label></p></div>';
echo '<div><p><label class="classic"><img style="padding-right: 22px;" src="'.$url.'ico_rss.png" alt="RSS" />'.
		form::field('rss',50,250,html::escapeHTML($social_links[2]),'',1).'</label></p></div>';
echo '<p class="clear" style="padding-top: 30px;">'.__('If you don\'t want to display a link, keep its field empty.').' </p>';
echo '</fieldset>';
