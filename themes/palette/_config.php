<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Palette, a theme for Dotclear.
#
# Copyright (c) 2009 annso
# contact@as-i-am.fr
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) exit;

// Locales
l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/main');

// Valeurs par défaut
$default_colors = array(
	array('#C5D984', '#AFCF48', '#E69B32', '#F75823', '#FF0335'),
	array('#213D44', '#7A2F4E', '#E5224D', '#F48429', '#E5E3B3'),
	array('#0E0A1E', '#396482', '#A2BCBB', '#DF737C', '#E8DDC5'),
	array('#322740', '#5E1E43', '#9E1C48', '#C43D3C', '#E06428'), 
	array('#69D2E7', '#ADD8C7', '#CDD7B6', '#CCFF00', '#666666'),
	array('#EB9D8D', '#93865A', '#A8BB9A', '#C5CBA6', '#EFD8A9'),
	array('#69D2E7', '#A7DBD8', '#E0E4CC', '#F3862F', '#FA6900'),
	array('#D3DFC3', '#D3DC23', '#F50154', '#9F016F', '#160411'),
	array('#67917A', '#170409', '#B8AF03', '#CCBF82', '#E33258'),
	array('#ECD078', '#D95B43', '#C02942', '#542437', '#53777A')
);
$default_names = array('blog', 'archives', 'photos', 'contact', 'rss');
$default_links = array('', '', '', '', '');

// Initialisation
$separator = ',';
$separator_links = ' ';
$tab_colors[]= array();
$tab_names[] = array();
$tab_links[] = array();


// Lecture des settings
if ($core->blog->settings->palette_colors) {
	$palette_colors = @unserialize($core->blog->settings->palette_colors);
	$tab_colors = explode($separator, $palette_colors);
} else {
	$palette_colors = implode($separator, $default_colors[0]);
	$core->blog->settings->setNameSpace('palette');
	$core->blog->settings->put('palette_colors',serialize($palette_colors),'string');
	$core->blog->triggerBlog();
	$tab_colors = $default_colors[0];
}
if ($core->blog->settings->palette_names) {
	$palette_names = @unserialize($core->blog->settings->palette_names);
	$tab_names = explode($separator, $palette_names);
} else {
	$palette_names = implode($separator, $default_names);
	$core->blog->settings->setNameSpace('palette');
	$core->blog->settings->put('palette_names',serialize($palette_names),'string');
	$core->blog->triggerBlog();
	$tab_names = $default_names;
}
if ($core->blog->settings->palette_links) {
	$palette_links = @unserialize($core->blog->settings->palette_links);
	$tab_links = explode($separator_links, $palette_links);
} else {
	$palette_links = implode($separator_links, $default_links);
	$core->blog->settings->setNameSpace('palette');
	$core->blog->settings->put('palette_links',serialize($palette_links),'string');
	$core->blog->triggerBlog();
	$tab_links = $default_links;
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

	// Thème prédéfini
	if($_POST['theme'] != 'p') {
		$palette_colors = implode($separator, $default_colors[$_POST['theme']]);
		$core->blog->settings->setNameSpace('palette');
		$core->blog->settings->put('palette_colors',serialize($palette_colors),'string');
		$core->blog->triggerBlog();
		$tab_colors = $default_colors[$_POST['theme']];
	} else {
		// Couleurs
		for($i = 0; $i<5; $i++) {
			if (!empty($_POST['color'.$i])) $tab_colors[$i] = $_POST['color'.$i];
			else $tab_colors[$i] = $default_colors[$i];
		}
		$palette_colors = implode($separator, $tab_colors);
		$core->blog->settings->setNameSpace('palette');
		$core->blog->settings->put('palette_colors',serialize($palette_colors),'string');
		$core->blog->triggerBlog();
	}

	// Noms
	for($i = 0; $i<5; $i++) {
		$tab_names[$i] = $_POST['name'.$i];
	}
	$palette_names = implode($separator, $tab_names);
	$core->blog->settings->setNameSpace('palette');
	$core->blog->settings->put('palette_names',serialize($palette_names),'string');
	$core->blog->triggerBlog();

	// Liens
	for($i = 0; $i<5; $i++) {
		if (!empty($_POST['link'.$i])) $tab_links[$i] = $_POST['link'.$i];
		else $tab_links[$i] = '';
	}
	$palette_links = implode($separator_links, $tab_links);
	$core->blog->settings->setNameSpace('palette');
	$core->blog->settings->put('palette_links',serialize($palette_links),'string');
	$core->blog->triggerBlog();
	
	echo '<p class="message">'.__('La configuration du thème a été mise à jour avec succès.').'</p>';	
}


// Palette
echo '<fieldset><legend>'.__('Palettes').'</legend>';
echo '<p class="clear">'.__('If you need more inspiration, take a look at').
		' <a href="http://www.colourlovers.com/palettes/top">Colorlovers</a>.</p>';
echo '<p>';
$url = 'blog_theme.php?shot=palette&amp;src=palettes/';	
for($i=0; $i<10; $i++) {
	echo '<label class="classic" style="padding: 0 5px">'.form::radio('theme', $i, $tab_colors==$default_colors[$i]).
			'<img src="'.$url.$i.'.png" alt="Theme #'.$i.'" />'.'</label>';
}
echo '</p>';
// Palette personnalisée
$index = 1;
echo '<p><label class="classic">'.form::radio('theme', 'p').__('Customized theme').'</label></p>';
echo '<p>';
for($i=0; $i<5; $i++) {
	echo '<label style="float: left; padding: 0 10px;">'.__('Color #').($i+1).' : '.
		form::field('color'.$i,7,7,html::escapeHTML($tab_colors[$i]),'colorpicker',$index++).'</label>';
}
echo '</p>';
echo '</fieldset>';


// Onglets
echo '<fieldset><legend>'.__('Tabs').'</legend>';
for($i=0; $i<5; $i++) {
	echo
	'<div class="two-cols clear">'.
	'<div class="col" style="width: 20%">'.
	'<p><label class="classic">'.__('Tab #').($i+1).' :'.' '.form::field('name'.$i,10,50,html::escapeHTML($tab_names[$i]),'',$index++).'</label></p>'.
	'</div>'.
	'<div class="col" style="width: 40%">'.
	'<p><label class="classic">'.__('Link #').($i+1).' :'.' '.form::field('link'.$i,40,255,html::escapeHTML($tab_links[$i]),'',$index++).'</label></p>'.
	'</div>'.
	'</div>';
}
echo '<p class="clear" style="padding-top: 30px;">'.__('Keep the name fields empty to delete a tab.').' </p>';
echo '</fieldset>';

