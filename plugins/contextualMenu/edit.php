<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of contextualMenu, a plugin for Dotclear.
# 
# Copyright (c) 2008 Frédéric Leroy
# bestofrisk@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$id = $_REQUEST['id'];

# chargement de l'item de menu courant dans l'objet $rs_current
try {
	$rs_current = $menu->getLink($id);
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

if (!$core->error->flag() && $rs_current->isEmpty()) {
	$core->error->add(__('No such link or title'));
} else {
	$link_title = $rs_current->link_title;
	$link_href = $rs_current->link_href;
	$link_type = trim($rs_current->link_type);
	$link_desc = $rs_current->link_desc;
	
	$link_lang = $rs_current->link_lang;
	
	$link_xfn = $rs_current->link_xfn;
	$link_group = explode(',', $rs_current->link_group);
	
	$link_special_xfn = $rs_current->link_special_xfn;
	$link_special_group = explode(',', $rs_current->link_special_group);
	$link_special_widget = $rs_current->link_special_widget;
	$link_special_content = $rs_current->link_special_content;
	$link_special_link_title = $rs_current->link_special_link_title;
}

# Update a link
if (isset($rs_current) && !$rs_current->is_cat && !empty($_POST['edit_link']))
{
	$link_title = $_POST['link_title'];
	$link_href = $_POST['link_href'];
	$link_type = $_POST['link_type'];
	$link_desc = $_POST['link_desc'];
	
	$link_lang = $_POST['link_lang'];

	$link_xfn = $_POST['link_xfn'];
	$link_group = join(',', $_POST['link_group']);

	$link_special_xfn = $_POST['link_special_xfn'];
	$link_special_group = join(',', $_POST['link_special_group']);
	$link_special_widget = $_POST['link_special_widget'];
	$link_special_content = $_POST['link_special_content'];
	$link_special_link_title = $_POST['link_special_link_title'];
	
	try {
		$menu->updateLink($id,$link_title,$link_href,$link_type,$link_desc,$link_lang,trim($link_xfn),trim($link_group),trim($link_special_xfn),trim($link_special_group), trim($link_special_widget), trim($link_special_content), $link_special_link_title);
		http::redirect($p_url.'&edit=1&id='.$id.'&upd=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}


# Update a category
if (isset($rs_current) && $rs_current->is_cat && !empty($_POST['edit_cat']))
{
	$link_desc = $_POST['link_desc'];
	
	try {
		$munu->updateCategory($id,$link_desc);
		http::redirect($p_url.'&edit=1&id='.$id.'&upd=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Ne concerne que les liens de type 'menu'
if ($link_type == 'menu') {

	#Combo widgets : Création de la liste des widgets valides (sauf le widget contextualMenu)
	if (!isset($__widgets)) include dirname(__FILE__).'/../widgets/_default_widgets.php';
	$widgets_combo = array();
	foreach ($__widgets->elements() as $w) {
		if ($w->id() != 'contextualMenu') {
			$k = __($w->name());
			$v = $w->id();
			$widgets_combo[$k] = $v;
		}
	}	

	# Combo widgets : Ajout du widget 'not_defined'
	$widgets_combo[''] = 'not_defined';
	$widgets_combo = array_reverse($widgets_combo);

	# Test si le widget est toujours valide
	$widgets = array_values($widgets_combo);
	if (!in_array($link_special_widget, $widgets)) {
		//$link_special_widget = 'not_defined';
		try {
			$menu->setLinkSpecialWidgetToNotDefined($id);
			http::redirect($p_url.'&edit=1&id='.$id.'&widget_not_defined=1&widget_id='.$link_special_widget);
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}

} // fin du bloc spécifique au liens de type 'menu'
	
# Get menu links
$params = array();
$params['link_type'] = 'menu';

try {
	$rs_menu = $menu->getLinks($params);
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

#Get context links
$params = array();
$params['link_type'] = 'context';

try {
	$rs_context = $menu->getLinks($params);
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Combo type
$type_combo = array(
__('menu') => 'menu',
__('context') => 'context'
);

# Class du champs lang
$extra_html_lang = '';
if ($link_type == 'context') $extra_html_lang = 'style="visibility: hidden;"';
?>

<html>
<head>
  <title>Contextual Menu</title>
</head>

<body>
<?php echo '<p><a href="'.$p_url.'">'.__('Return to menu').'</a></p>'; ?>

<?php

if (isset($rs_current))
{
	if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('Link has been successfully updated').'</p>';
	}
	
	if (!empty($_GET['widget_not_defined'])) {
		echo '<p class="message">'.sprintf(__('Widget %s not found in the list of valid widgets!<br />Widget field has been set to \'not_defined\''), $_GET['widget_id']).'</p>';
	}
	
	echo
	'<form action="plugin.php" method="post">'.
	'<fieldset class="two-cols"><legend>'.__('Main Fields').'</legend>'.
	
	'<p class="col"><label class="required" title="'.__('Required field').'">'.__('Title:').' '.
	form::field('link_title',30,255,html::escapeHTML($link_title)).'</label></p>'.
	
	'<p class="col"><label class="required" title="'.__('Required field').'">'.__('URL:').' '.
	form::field('link_href',60,255,html::escapeHTML($link_href)).'</label></p>'.
	
	'<p class="col"><label class="required" title="'.__('Required field').'">'.__('Type:').' '.
	form::combo('link_type',$type_combo,$link_type,'',6).
	'</label></p>'.
	
	'<p class="col"><label>'.__('Description:').' '.
	form::field('link_desc',60,255,html::escapeHTML($link_desc)).'</label></p>'.
	
	'<p class="col" ' . $extra_html_lang . '><label>'.__('Language:').' '.
	form::field('link_lang',5,5,html::escapeHTML($link_lang)).'</label>'.
	'</p>'.

	'<p class="col">'.form::hidden('p','contextualMenu').
	form::hidden('edit',1).
	form::hidden('id',$id).
	$core->formNonce().
	'<input type="submit" name="edit_link" class="submit" value="'.__('save').'"/></p>'.
	'</fieldset>';
	
	// Les champs qui suivent ne concernent que les links de type menu
	if ($link_type == 'menu') {
		# Complément 1 : Affichage contextuel des menus (oui/non) 
		echo
		'<fieldset><legend>'.__('Visibility').'</legend>'.
		'<table class="noborder">'.

		'<tr>'.
		'<th>'.__('Menu Link Visibility:').'</th>'.
		'<td>'.
		'<p><label class="classic">'.form::radio(array('link_xfn'),'group_only',$link_xfn == 'group_only').' '.__('Group links only').'</label> '.
		'<label class="classic">'.form::radio(array('link_xfn'),'all_except_group',$link_xfn == 'all_except_group').' '.__('All links except Group ones').'</label> '.
		'<label class="classic">'.form::radio(array('link_xfn'),'all',$link_xfn == 'all').' '.__('All links').'</label> '.
		'</label></p>'.
		'</td>'.
		'</tr>'.

		'<tr>'.
		'<th>'.__('Group:').'</th>'.
		'<td>'.

		'<table class="noborder">'.
		'<tr>'.
		'<th>'.__('Select menu items:').'</th>'.		
		'<th>'.__('Select context items:').'</th>'.
		'</tr>'.
		
		'<tr>'.
		'<td>';

		// Affichage des links de type 'menu'	
		while ($rs_menu->fetch())
		{
		echo
			'<p>'.'<label class="classic">'.
			form::checkbox(array('link_group[]'), $rs_menu->link_id, in_array($rs_menu->link_id,$link_group)).' '.
			$rs_menu->link_title.'</label></p>';
		} // Fin while

		echo
		'</td>'.
		'<td>';

		// Affichage des links de type 'context'
		while ($rs_context->fetch())
		{
		echo
			'<p>'.'<label class="classic">'.
			form::checkbox(array('link_group[]'), $rs_context->link_id, in_array($rs_context->link_id,$link_group)).' '.
			$rs_context->link_title.'</label></p>';
		} // Fin while

		echo
		'</td>'.		
		'</tr>'.
		'</table>'.
		'</td>'.
		'</tr>'.

		'</table>'.
	
		'</fieldset>'.
	
		# Complément 2 : Affichage contextuel des menus (html du menu) 
		'<fieldset><legend>'.__('Widget').'</legend>'.
		'<table class="noborder">'.

		'<tr>'.
		'<th>'.__('Widget Visibility:').'</th>'.
		'<td>'.
		'<p><label class="classic">'.form::radio(array('link_special_xfn'),'group_only',$link_special_xfn == 'group_only').' '.__('Group links only').'</label> '.
		'<label class="classic">'.form::radio(array('link_special_xfn'),'all_except_group',$link_special_xfn == 'all_except_group').' '.__('All links except Group ones').'</label> '.
		'<label class="classic">'.form::radio(array('link_special_xfn'),'none',$link_special_xfn == 'none').' '.__('No link').'</label></p>'.
		'</td>'.
		'</tr>'.

		'<tr>'.
		'<th>'.__('Group:').'</th>'.
		'<td>'.

		'<table class="noborder">'.
		'<tr>'.
		'<th>'.__('Select menu items:').'</th>'.		
		'<th>'.__('Select context items:').'</th>'.
		'</tr>'.
		
		'<tr>'.
		'<td>';

		// Affichage des links de type 'menu'	
		while ($rs_menu->fetch())
		{
		echo
			'<p>'.'<label class="classic">'.
			form::checkbox(array('link_special_group[]'), $rs_menu->link_id, in_array($rs_menu->link_id,$link_special_group)).' '.
			$rs_menu->link_title.'</label></p>';
		} // Fin while

		echo
		'</td>'.
		'<td>';

		// Affichage des links de type 'context'
		while ($rs_context->fetch())
		{
		echo
			'<p>'.'<label class="classic">'.
			form::checkbox(array('link_special_group[]'), $rs_context->link_id, in_array($rs_context->link_id,$link_special_group)).' '.
			$rs_context->link_title.'</label></p>';
		} // Fin while

		echo
		'</td>'.		
		'</tr>'.
		'</table>'.
		'</td>'.
		'</tr>'.

		# Liste des widgets
		'<tr>'.
		'<th>'.__('Widget ID:').'</th>'.
		'<td>'.
		'<p><label class="classic">'.form::combo('link_special_widget',$widgets_combo,$link_special_widget).'</label></p>'.
		'</td>'.
		'</tr>'.

		# Paramètres du widget
		'<tr>'.
		'<th>'.__('Widget Parameters:').'</th>'.
		'<td>'.
		'<p><label class="classic">'.form::textArea('link_special_content', 100, 5, $link_special_content).'</label> '.
		'</td>'.
		'</tr>'.
		
		# Mode d'affichage du titre du widget (texte vs lien)
		'<tr>'.
		'<th>'.__('Widget Link Title:').'</th>'.
		'<td>'.
		'<p><label class="classic">'.form::radio(array('link_special_link_title'),1,$link_special_link_title == 1).' '.__('Yes').'</label> '.
		'<label class="classic">'.form::radio(array('link_special_link_title'),0,$link_special_link_title == 0).' '.__('No').'</label></p>'.
		'</td>'.
		'</tr>'.
		
		'</table>'.
		'</fieldset>';
	} // Fin if
	
	echo
	'</form>';
}
?>
</body>
</html>