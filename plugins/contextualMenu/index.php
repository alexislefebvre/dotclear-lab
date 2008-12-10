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

require dirname(__FILE__).'/class.dc.contextual_menu.php';

$menu = new dcBlogMenu($core->blog);

# Page de configuration avancée
if (!empty($_REQUEST['advanced_settings'])) {
	include dirname(__FILE__).'/advanced_settings.php';
	return;
}

# Page d'édition d'un lien
if (!empty($_REQUEST['edit']) && !empty($_REQUEST['id'])) {
	include dirname(__FILE__).'/edit.php';
	return;
}

#Default values
$default_tab = '';
$link_title = $link_href = $link_desc = $link_type = '';
$cat_title = '';

# Add link
if (!empty($_POST['add_link']))
{
	# Récup des valeurs des champs principaux
	# NB : Les champs complémentaires sont initialisés dans la méthode addLink
	$link_title = $_POST['link_title'];
	$link_href = $_POST['link_href'];
	$link_desc = $_POST['link_desc'];
	$link_type = $_POST['link_type'];
	
	try {
		$menu->addLink($link_title,$link_href,$link_type,$link_desc);
		http::redirect($p_url.'&addlink=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
		$default_tab = 'add-link';
	}
}

# Delete link
if (!empty($_POST['removeaction']) && !empty($_POST['remove'])) {
	foreach ($_POST['remove'] as $k => $v)
	{
		try {
			$menu->delItem($v);
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
			break;
		}
	}
	
	if (!$core->error->flag()) {
		http::redirect($p_url.'&removed=1');
	}
}

# Order links
$order = array();
if (empty($_POST['links_order']) && !empty($_POST['order'])) {
	$order = $_POST['order'];
	asort($order);
	$order = array_keys($order);
} elseif (!empty($_POST['links_order'])) {
	$order = explode(',',$_POST['links_order']);
}

if (!empty($_POST['saveorder']) && !empty($order))
{
	foreach ($order as $pos => $l) {
		$pos = ((integer) $pos)+1;
		
		try {
			$menu->updateOrder($l,$pos);
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
	
	if (!$core->error->flag()) {
		http::redirect($p_url.'&neworder=1');
	}
}


# Get links
try {
	$rs = $menu->getLinks();
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Combo type
$type_combo = array(
__('menu') => 'menu',
__('context') => 'context'
);

?>
<html>
<head>
  <title>Contextual Menu</title>
  <?php echo dcPage::jsToolMan(); ?>
  <?php echo dcPage::jsConfirmClose('links-form','add-link-form','add-category-form'); ?>
  <script type="text/javascript">
  //<![CDATA[
  
  var dragsort = ToolMan.dragsort();
  $(function() {
  	dragsort.makeTableSortable($("#links-list").get(0),
  	dotclear.sortable.setHandle,dotclear.sortable.saveOrder);
	
	$('.checkboxes-helpers').each(function() {
		dotclear.checkboxesHelpers(this);
	});
  });
  
  dotclear.sortable = {
	  setHandle: function(item) {
		var handle = $(item).find('td.handle').get(0);
		while (handle.firstChild) {
			handle.removeChild(handle.firstChild);
		}
		
		item.toolManDragGroup.setHandle(handle);
		handle.className = handle.className+' handler';
	  },
	  
	  saveOrder: function(item) {
		var group = item.toolManDragGroup;
		var order = document.getElementById('links_order');
		group.register('dragend', function() {
			order.value = '';
			items = item.parentNode.getElementsByTagName('tr');
			
			for (var i=0; i<items.length; i++) {
				order.value += items[i].id.substr(2)+',';
			}
		});
	  }
  };
  //]]>
  </script>
  <?php echo dcPage::jsPageTabs($default_tab); ?>
</head>

<body>
<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt; Menu</h2>

<?php echo '<p><a href="'.$p_url.'&amp;advanced_settings=1">'.__('Advanced Settings').'</a></p>'; ?>

<?php
if (!empty($_GET['neworder'])) {
	echo '<p class="message">'.__('Items order has been successfully updated').'</p>';
}

if (!empty($_GET['removed'])) {
		echo '<p class="message">'.__('Items have been successfully removed.').'</p>';
}

if (!empty($_GET['addlink'])) {
		echo '<p class="message">'.__('Link has been successfully created.').'</p>';
}

if (!empty($_GET['nofile'])) {
		echo '<p class="error">'.__('Template directory is empty!').'</p>';
}

?>

<div class="multi-part" title="<?php echo __('Menu'); ?>">
<form action="plugin.php" method="post" id="links-form">
<table class="maximal dragable">
<thead>
<tr>
  <th colspan="3"><?php echo __('Title'); ?></th>
  <th><?php echo __('Description'); ?></th>
  <th><?php echo __('URL'); ?></th>
  <th><?php echo __('Type'); ?></th>
</tr>
</thead>
<tbody id="links-list">
<?php
while ($rs->fetch())
{
	$position = (string) $rs->index()+1;
	
	echo
	'<tr class="line" id="l_'.$rs->link_id.'">'.
	'<td class="handle minimal">'.form::field(array('order['.$rs->link_id.']'),2,6,$position).'</td>'.
	'<td class="minimal">'.form::checkbox(array('remove[]'),$rs->link_id).'</td>';
	
	
	echo
	'<td><a href="'.$p_url.'&amp;edit=1&amp;id='.$rs->link_id.'&amp;type='.$rs->link_type.'">'.
	html::escapeHTML($rs->link_title).'</a></td>'.
	'<td>'.html::escapeHTML($rs->link_desc).'</td>'.
	'<td>'.html::escapeHTML($rs->link_href).'</td>'.
	'<td>'.html::escapeHTML($rs->link_type).'</td>';

	echo '</tr>';
}
?>
</tbody>
</table>

<div class="two-cols">
<p class="col"><?php echo form::hidden('links_order','');
echo form::hidden(array('p'),'contextualMenu');
echo $core->formNonce(); ?>
<input type="submit" name="saveorder" value="<?php echo __('Save order'); ?>" /></p>

<p class="col right"><input type="submit" name="removeaction"
value="<?php echo __('Delete selected links'); ?>"
onclick="return window.confirm('<?php echo html::escapeJS(
__('Are you sure you want to delete selected links?')); ?>');" /></p>
</div>

</form>
</div>

<?php
echo
'<div class="multi-part" id="add-link" title="'.__('Add a link').'">'.
'<form action="plugin.php" method="post" id="add-link-form">'.
'<fieldset class="two-cols"><legend>'.__('Add a new link').'</legend>'.
'<p class="col"><label class="required" title="'.__('Required field').'">'.__('Title:').' '.
form::field('link_title',30,255,$link_title,'',2).
'</label></p>'.

'<p class="col"><label class="required" title="'.__('Required field').'">'.__('URL:').' '.
form::field('link_href',60,255,$link_href,'',3).
'</label></p>'.

'<p class="col"><label class="required" title="'.__('Required field').'">'.__('Type:').' '.
form::combo('link_type',$type_combo,$link_type, '',4).
'</label></p>'.

'<p class="col"><label>'.__('Description:').' '.
form::field('link_desc',60,255,$link_desc,'',5).
'</label></p>'.

'<p>'.form::hidden(array('p'),'contextualMenu').
$core->formNonce().
'<input type="submit" name="add_link" value="'.__('save').'" tabindex="6" /></p>'.
'</fieldset>'.
'</form>'.
'</div>';

dcPage::helpBlock('contextualMenu');
?>

</body>
</html>