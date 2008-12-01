<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Informations.
# Copyright 2007,2008 Moe (http://gniark.net/)
#
# Informations is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Informations is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) and images are from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_CONTEXT_ADMIN')) { return; }

require_once(dirname(__FILE__).'/php-xhtml-table/class.table.php');
require_once(dirname(__FILE__).'/class.info.php');

$url_scan = $core->blog->settings->url_scan;

$errors = array();

?>

<html>
<head>
	<title><?php echo(__('Informations')); ?></title>
  	<style type="text/css">
  		p img, table img {vertical-align:middle;}
  		.center {text-align:center;}
  </style>
</head>
<body>

	<h2><?php echo(__('Informations')); ?></h2>
	<h3><?php echo(__('Legend :')); ?></h3>
	<p><?php echo(info::yes().__('ok').', '.info::no().__('error')); ?></p>
	<h3><?php echo(__('General informations')); ?></h3>
	<?php info::fp(__('The Dotclear version is %s'),DC_VERSION); ?>
	<?php 
		info::fp(__('The blog ID is %s'),$core->blog->id);
		info::fp(__('The blog URL is %s'),$core->blog->url);
	?>
	<p><?php 
		$char = mb_substr($core->blog->url,-1);
		if ((($url_scan == 'path_info') AND ($char == '/'))
			 OR (($url_scan == 'query_string') AND ($char == '?')))
		{
			echo(info::yes());
			info::f(__('URL scan method is %1$s and the last character of URL is %2$s'),
			$url_scan,$char);
		}
		elseif (in_array($url_scan,array('path_info','query_string')))
		{
			if ($url_scan == 'path_info')
			{
				$errors[] = info::f_return(
				__('URL scan method is %1$s and the last character of URL isn\'t %2$s'),
				'path_info','/');
			}
			elseif ($url_scan == 'query_string')
			{	
				$errors[] = info::f_return(
				__('URL scan method is %1$s and the last character of URL isn\'t %2$s'),
				'query_string','?');
			}
		}
		else
		{
			$errors[] = info::f_return(
				__('URL scan method is not %1$s or %2$s'),'path_info',
				'query_string');
		}
	?></p>

	<hr />

	<h3><?php echo(__('Database')); ?></h3>
	<p><?php echo(__('Dotclear tables in your database are').'&nbsp;:'); ?></p>
	<?php echo(info::tables()); ?>

	<hr />

	<h3><?php echo(__('Directory informations')); ?></h3>
	<?php echo(info::directories()); ?>
	<p><?php echo(__('Public directory is optional.')); ?></p>

	<hr />

	<h3><?php echo(__('Server informations')); ?></h3>
	<?php
		info::fp(__('Dotclear is installed in the directory %s'),path::real(DC_ROOT));
		info::fp(__('The PHP version is %s'),phpversion());
		info::fp(__('The database driver is %1$s and its version is %2$s'),
			$core->con->driver(),$core->con->version());
		info::fp(__('Safe mode is %s'),
			((ini_get('safe_mode') == true) ? __('active') : __('inactive')));
		info::fp(__('Maximum size of a file when uploading a file is %s'),
			files::size(DC_MAX_UPLOAD_SIZE));
		if (!empty($_SERVER["SERVER_SOFTWARE"])) {
			info::fp(__('The web server is %s'),$_SERVER["SERVER_SOFTWARE"]);
		}
		if (function_exists('exec'))
		{
			$user = exec('whoami');
			if (!empty($user))
			{
				info::fp(__('The user is %s'),$user);
			}
		}
		$error_reporting = ini_get('error_reporting');
		if ((ini_get('display_errors')) AND ($error_reporting > 0))
		{
			info::fp(__('The error_reporting level is %s'),$error_reporting);
			info::fp(__('The displayed errors are %s'),
				info::error2string($error_reporting));
		}

		echo '<hr />';

		if (!empty($errors))
		{
			echo '<h3>'.__('Errors').'</h3>';
			echo '<ul>';
			echo '<li>';
			echo(implode('</li><li>',$errors));
			echo '</li>';
			echo '</ul>';
		}
	?>
	
</body>
</html>