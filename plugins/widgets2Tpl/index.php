<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Widgets to Template, a plugin for Dotclear 2
# Copyright 2010 Moe (http://gniark.net/)
#
# Widgets to Template is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Widgets to Template is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with this program. If not, see
# <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) and images are from Silk Icons :
# <http://www.famfamfam.com/lab/icons/silk/>
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

$page_title = __('Widgets to Template');

if ($core->plugins->moduleExists('widgets'))
{
	# load widgets
	require($core->plugins->moduleRoot('widgets').'/_default_widgets.php');
}
else
{
	throw new Exception(__('The widgets plugins is missing.'));
}

?><html>
<head>
  <title><?php echo $page_title; ?></title>
	<?php echo dcPage::jsPageTabs('template'); ?>
	<!-- force tab display -->
	<style type="text/css">
		pre {white-space:pre-wrap;}
	</style>
</head>
<body>
<?php

	echo dcPage::breadcrumb(
		array(
			html::escapeHTML($core->blog->name) => '',
			'<span class="page-title">'.$page_title.'</span>' => ''
		));
?>
	
	<div class="multi-part" id="template" title="<?php echo __('Template'); ?>">
		<?php
			foreach ($__widgets->elements() as $w)
			{
				echo('<h3>'.$w->name().'</h3>'."\n\n".
					'<pre>'."\n".
					html::escapeHTML('<!-- # '.$w->name().' -->'."\n".
					'<tpl:Widget id="'.$w->id().'">'."\n"));
				
				$w_settings = $w->settings();
				if (empty($w_settings))
				{
					echo "\t".html::escapeHTML('<!-- # '.
						__('No setting for this widget').' -->'."\n");
				}
				else
				{
					foreach ($w->settings() as $n => $s)
					{
						$s_values = '';
						switch ($s['type'])
						{
							case 'check':
								$s_values = ', '.__('Allowed values:').'(0|1)';
								$type = __('boolean');
								break;
							case 'combo':
								$s_values = ', '.__('Allowed values:').'('.
									html::escapeHTML(implode('|',$s['options']).')');
								$type = __('list');
								break;
							case 'text':
							case 'textarea':
							default:
								$type = __('text');
								break;
						}
						
						echo "\t".
							html::escapeHTML('<setting name="'.$n.'">'.$s['value'].
								'</setting> <!-- # '.$s['title'].
								' ('.$type.')').
								$s_values.
								html::escapeHTML(' -->'."\n");
					}
				}
				
				echo(html::escapeHTML('</tpl:Widget>').'</pre>'."\n");
				}
		?>
	</div>
	
	<div class="multi-part" id="doc" title="<?php echo __('Dotclear documentation'); ?>">
		<pre><?php
			foreach ($__widgets->elements() as $w)
			{
				echo('==== '.$w->name().' ===='."\n\n".
					html::escapeHTML('<code tpl>'."\n".
					'<!-- # '.$w->name().' -->'."\n".
					'<tpl:Widget id="'.$w->id().'">'."\n"));
				
				$w_settings = $w->settings();
				if (empty($w_settings))
				{
					echo "\t".html::escapeHTML('<!-- # '.
						__('No setting for this widget').' -->'."\n");
				}
				else
				{
					foreach ($w->settings() as $n => $s)
					{
						$s_values = '';
						switch ($s['type'])
						{
							case 'check':
								$s_values = ', '.__('Allowed values:').'(0|1)';
								$type = __('boolean');
								break;
							case 'combo':
								$s_values = ', '.__('Allowed values:').'('.implode('|',$s['options']).')';
								$type = __('list');
								break;
							case 'text':
							case 'textarea':
							default:
								$type = __('text');
								break;
						}
						
						echo "\t".
							html::escapeHTML('<setting name="'.$n.'">'.$s['value'].
								'</setting> <!-- # '.$s['title'].
								' ('.$type.')'.$s_values.' -->'."\n");
					}
				}
				
				echo(html::escapeHTML('</tpl:Widget>'."\n".
					'</code>'."\n\n"));
				}
		?></pre>
	</div>

</body>
</html>