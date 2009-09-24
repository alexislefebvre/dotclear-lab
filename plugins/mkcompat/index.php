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

?>
<html>
  <head>
    <title><?php echo __('Dotclear 2.1.6 compatibility plugin'); ?></title>
  </head>
  <body>
      <h1><?php echo __('Dotclear 2.1.6 compatibility plugin'); ?></h1>
      <h2><?php echo __('Themes needing an upgrade'); ?></h2>
<table>
<?php
	$core->themes = new dcThemes($core);
	$core->themes->loadModules($core->blog->themes_path,null);
	$themes = $core->themes->getModules();
	unset($themes['default']);
	
	foreach ($themes as $k => $v)
	{
		if (mkcompat::themeNeedUpgrade($v['root']))
		{
			echo '<tr><th>'.$k.'</th>'.
				'<td>'.$v['root'].'</td>'.
				'<td>'.$v['name'].'</td>'.
				'<td>'.$v['desc'].'</td>'.
				'<td>'.$v['author'].'</td>'.
				'<td>'.$v['version'].'</td>'.
				'<td>'.$v['parent'].'</td>'.
				'<td>'.$v['priority'].'</td>'.
				'<td>'.$v['root_writable'].'</td>'.
				'</tr>';
		}
	}


?>
</table>
  </body>
</html>