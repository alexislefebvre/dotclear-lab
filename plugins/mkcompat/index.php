<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of mkcompat, a plugin for Dotclear 2.
#
# Copyright (c) 2009 Dotclear Team and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

if (!empty($_POST)) {
	echo '<html><head><title>youpla</title></head><body>'.
		'<p>Uais, &ccedil;a va, une minute !</p>'.
		'<p><a href="'.$p_url.'">hop</a></p>'.
		'</body></html>';
	exit;
}
?>
<html>
  <head>
    <title><?php echo __('Dotclear 2.1.6 compatibility plugin'); ?></title>
  </head>
  <body>
      <h1><?php echo __('Dotclear 2.1.6 compatibility plugin'); ?></h1>
      <h2><?php echo __('Themes needing an upgrade'); ?></h2>
<?php
	$core->themes = new dcThemes($core);
	$core->themes->loadModules($core->blog->themes_path,null);
	$themes = $core->themes->getModules();
	unset($themes['default']);
	
	foreach ($themes as $k => $v)
	{
		if (!mkcompat::themeNeedUpgrade($v['root'])) unset($themes[$k]);
	}
	
	if (count($themes) > 0)
	{
		echo '<table>';
		foreach ($themes as $k => $v)
		{
			echo '<tr><th title="'.$v['desc'].'">'.$v['name'].'</th>'.
				'<td>'.$v['author'].'</td>'.
				'<td>'.$v['version'].'</td>';
			
			if ($v['root_writable'])
			{
				echo "\n".'<td><form action="'.$p_url.'" method="post">'.
				form::hidden('root',$v['root'])."\n".
				form::hidden('type','theme')."\n".
				form::hidden('name',$v['name'])."\n".
				$core->formNonce()."\n".
				'<input type="submit" name="action" value="'.__('Upgrade').'"></input>'."\n".
				'</form></td>'."\n";
			} else {
				echo '<td>'.__('You do not have sufficient rights to upgrade this theme.').'</td>';
			}
				
			echo '</tr>';
		}
		echo '</table>';
	}
	else
	{
		echo '<p>'.__('Upgrade does not seem to be required for any theme.').'</p>';
	}
?>
  </body>
</html>