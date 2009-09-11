<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of My Dashboard, a plugin for Dotclear 2
# Copyright (C) 2009 Moe (http://gniark.net/)
#
# My Dashboard is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# My Dashboard is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

dcPage::checkSuper();

$tab = 'links';

$settings =& $core->blog->settings;

$msg = (string)'';

$title = $url = $icon = (string)'';

$title_edit = $url_edit = $icon_edit = (string)'';

if (isset($_GET['edit_link']))
{
	$link_id = $_GET['edit_link'];
	
	$links = myDashboard::loadLinks();
	
	$link = $links[$link_id];
	
	$title_edit = $link['title'];
	$url_edit = $link['url'];
	$icon_edit = $link['icon'];
	
	$tab = 'edit-link';
}

# save order
if (!empty($_POST['saveorder']))
{
	try
	{
		$links = array();
		
		foreach ($_POST['title'] as $k => $v)
		{
			$title = $v;
			$url = $_POST['url'][$k];
			$icon = $_POST['icon'][$k];
			
			myDashboard::addLink($links,$title,$url,$icon);
		}
		
		myDashboard::saveLinks($links);
		
		http::redirect($p_url.'&saveorder=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
elseif ((!empty($_POST['removeaction'])) && (!empty($_POST['remove'])))
{
	try
	{
		$links = myDashboard::loadLinks();
		
		foreach ($_POST['remove'] as $k => $v)
		{
			unset($links[$v]);
		}
		
		myDashboard::saveLinks($links);
		
		http::redirect($p_url.'&removed=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
elseif (!empty($_POST['add_link']))
{
	$tab = 'add-link';
	
	try
	{
		$links = myDashboard::loadLinks();
		
		$title = $_POST['title'];
		$url = $_POST['url'];
		$icon = $_POST['icon'];
		
		if (empty($title)) {throw new Exception(__('Empty title'));}
		
		if (empty($url)) {throw new Exception(__('Empty URL'));}
		
		myDashboard::addLink($links,$title,$url,$icon);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
	
	if (!$core->error->flag()) {
		http::redirect($p_url.'&addlink=1');
	}
}
elseif (!empty($_POST['edit_link']))
{
	$tab = 'add-link';
	
	try
	{
		$links = myDashboard::loadLinks();
		
		$link_id = $_GET['edit_link'];
		
		$title = $_POST['title'];
		$url = $_POST['url'];
		$icon = $_POST['icon'];
		
		if (empty($title)) {throw new Exception(__('Empty title'));}
		
		if (empty($url)) {throw new Exception(__('Empty URL'));}
		
		$links[$link_id] = array(
			'title' => $title,
			'url' => $url,
			'icon' => $icon,
		);
		
		myDashboard::saveLinks($links);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
	
	if (!$core->error->flag()) {
		http::redirect($p_url.'&edit_link='.$link_id.'&link_saved=1');
	}
}

if (isset($_GET['saveorder']))
{
	$msg = __('Items order has been successfully updated');
}
elseif (isset($_GET['removed']))
{
	$msg = __('Items have been successfully removed.');
}
elseif (isset($_GET['addlink']))
{
	$msg = __('Link has been successfully created.');
}
elseif (isset($_GET['link_saved']))
{
	$msg = __('Link has been successfully updated');
}

$links = myDashboard::loadLinks();

?><html>
<head>
	<title><?php echo __('My Dashboard'); ?></title>
	<?php echo dcPage::jsPageTabs($tab).
		dcPage::jsToolMan().
  	dcPage::jsConfirmClose('links-form','add-link-form',
		'add-category-form'); ?>
  <script type="text/javascript">
  //<![CDATA[
  
	<?php echo dcPage::jsVar('dotclear.msg.confirm_cleanconfig_delete',
		__('Are you sure you want to delete settings?')).
		dcPage::jsVar('dotclear.msg.confirm_remove',
		__('Are you sure you you want to delete selected links?')); ?>
	
  var dragsort = ToolMan.dragsort();
  $(function() {
  	dragsort.makeTableSortable($("#links-list").get(0),
  	dotclear.sortable.setHandle,dotclear.sortable.saveOrder);
	
		$('.checkboxes-helpers').each(function() {
			dotclear.checkboxesHelpers(this);
		});
		
		$('input[@name="removeaction"]').click(function() {
				return window.confirm(dotclear.msg.confirm_remove);
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
	<?php echo(dcPage::jsConfirmClose('links-form','add-link-form',
		'edit-link-form')); ?>
</head>
<body>

	<h2><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.
		__('My Dashboard'); ?></h2>
	
	<?php 
		if (!empty($msg)) {echo '<p class="message">'.$msg.'</p>';}
	?>
	
	<!--<?php echo('<pre>'.print_r($links,true).'</pre>'); ?>-->
	
	<div class="multi-part" id="links" title="<?php echo __('Links'); ?>">
		<form method="post" id="links-form" action="<?php echo http::getSelfURI(); ?>">
			<table class="clear maximal dragable">
				<thead>
					<tr>
						<th colspan="3"><?php echo __('Title'); ?></th>
						<th><?php echo __('URL'); ?></th>
						<th><?php echo __('Image URL'); ?></th>
					</tr>
				</thead>
				<tbody id="links-list">
				<?php
					$i = 0;
					
					foreach ($links as $id => $v)
					{
						echo('<tr class="line" id="l_'.$i.'">'.
						
						'<td class="handle minimal">&nbsp;</td>'.
						
						'<td class="minimal">'.
							form::checkbox(array('remove[]'),$i).'</td>'.
						'<td class="minimal">'.
							'<a href="'.$p_url.'&edit_link='.$i.'">'.$v['title'].'</a>'.
							form::hidden(array('title[]'),$v['title']).
							form::hidden(array('url[]'),$v['url']).
							form::hidden(array('icon[]'),$v['icon']).
						'</td>'.
						'<td class="minimal">'.$v['url'].'</td>'.
						'<td class="minimal">'.$v['icon'].'</td>'.
						'</tr>');
						$i++;
					}
				?>
				</tbody>
			</table>
			
			<p><?php echo form::hidden('links_order',''); ?></p>
			<p><?php echo $core->formNonce(); ?></p>
			<p>
				<input type="submit" name="saveorder"
					value="<?php echo __('Save order'); ?>" />
				<input type="submit" name="removeaction"
					value="<?php echo __('Delete selected links'); ?>" />
			</p>
		</form>
	</div>
	
	<div class="multi-part" id="add-link" title="<?php echo __('Add a link'); ?>">
		<form method="post" id="add-link-form" action="<?php echo http::getSelfURI(); ?>">
			<?php
				echo('<p>'.
					'<label class="required" title="'.__('Required field').'">'.
					__('Title:').
					form::field('title',40,255,$title).
					'</label></p> '.
					'<p><label class="required"  title="'.__('Required field').'">'.
					__('URL:').
					form::field('url',40,255,$url).
					'</label></p> '.
					'<p><label>'.__('Icon:').
					form::field('icon',40,255,$icon).
					/*'<a href="'.$p_url.'&amp;file=media&amp;link_id='.
						$i.'">&nbsp;'.
						'<img src="images/plus.png" alt="'.
						__('Use a image from the media manager').'" />'.
						'</a>'.*/
					'</label>'.
					'</p>');
				?>
				</tbody>
			</table>
			<p><?php echo $core->formNonce(); ?></p>
			<p><input type="submit" name="add_link"
				value="<?php echo __('save'); ?>" /></p>
		</form>
	</div>
	
	<?php if (isset($_GET['edit_link'])) { ?>
	
	<div class="multi-part" id="edit-link" title="<?php echo __('Edit link'); ?>">
		<form method="post" id="edit-link-form" action="<?php echo http::getSelfURI(); ?>">
			<?php
				echo('<p>'.
					'<label class="required" title="'.__('Required field').'">'.
					__('Title:').
					form::field('title',40,255,$title_edit).
					'</label></p> '.
					'<p><label class="required"  title="'.__('Required field').'">'.
					__('URL:').
					form::field('url',40,255,$url_edit).
					'</label></p> '.
					'<p><label>'.__('Icon:').
					form::field('icon',40,255,$icon_edit).
					/*'<a href="'.$p_url.'&amp;file=media&amp;link_id='.
						$link_id.'">&nbsp;'.
						'<img src="images/plus.png" alt="'.
						__('Use a image from the media manager').'" />'.
						'</a>'.*/
					'</label>'.
					'</p>');
				?>
				</tbody>
			</table>
			<p><?php echo form::hidden('edit_link',$link_id); ?></p>
			<p><?php echo $core->formNonce(); ?></p>
			<p><input type="submit" name="edit_link"
				value="<?php echo __('save'); ?>" /></p>
		</form>
	</div>
	
	<?php } ?>
	
</body>
</html>