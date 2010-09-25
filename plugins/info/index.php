<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Informations, a plugin for Dotclear 2
# Copyright 2007,2008,2009 Moe (http://gniark.net/)
#
# Informations is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Informations is distributed in the hope that it will be useful,
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

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/admin');

require_once(dirname(__FILE__).'/php-xhtml-table/class.table.php');
require_once(dirname(__FILE__).'/inc/lib.info.php');

$errors = array();

# get URL of a post
$post_url = null;
$rs = $core->blog->getPosts(array('limit' => 1));

while ($rs->fetch())
{
	$post_url = $rs->getURL();
}
unset($rs);

?>
<html>
<head>
	<title><?php echo(__('Informations')); ?></title>
	<?php echo dcPage::jsPageTabs('blog'); ?>
  <style type="text/css">
  	p img, table img {vertical-align:middle;}
  	.center {text-align:center;}
  </style>
</head>
<body>

	<h2><?php echo(__('Informations')); ?></h2>
	<h3><?php echo(__('Legend:')); ?></h3>
	<p><?php echo(info::yes().__('ok').', '.info::no().__('error')); ?></p>
	
	<div class="multi-part" id="blog" title="<?php echo __('Blog'); ?>">
		<?php 
			info::fp(__('The blog ID is %s'),$core->blog->id);
			info::fp(__('The blog URL is %s'),$core->blog->url);
			info::fp(__('URL scan method is %s'),
				$core->blog->settings->url_scan);
			
			if ($post_url === null)
			{
				echo(__('This blog has no published entry yet. To test the URL of the blog, please publish an entry.'));
			}
			else
			{
				try
				{
					# from dcBlog::__construct()
					$host = preg_replace('|^([a-z]{3,}://)(.*?)$|','$2',
						$core->blog->host);
					
					$netHttp = new netHttp($host);
					$netHttp->setTimeout(5);
					$netHttp->get($post_url);
					$status = $netHttp->getStatus();
				}
				catch (Exception $e) {$status = null;}
				
				if ($status == 200)
				{
					info::fp(info::yes().
						__('The entry\'s URL %s returned a 200 status code, the page was found, it seems that the URL of the blog is correctly configured.'),
						'<a href="'.$post_url.'">'.$post_url.'</a>');
				}
				elseif ($status == 404)
				{
					info::fp(info::no().
						__('The entry\'s URL %s returned a 404 status code, the page was not found, it seems that the URL of the blog is not correctly configured.'),
						'<a href="'.$post_url.'">'.$post_url.'</a>');

					$errors[] = info::f_return(
						__('The entry\'s URL %s returned a 404 status code, the page was not found, it seems that the URL of the blog is not correctly configured.'),
							'<a href="'.$post_url.'">'.$post_url.'</a>');
				}
				elseif (is_int($status))
				{
					info::fp(info::no().
						__('The entry\'s URL %s returned a %s status code.'),
						'<a href="'.$post_url.'">'.$post_url.'</a>',$status);

					$errors[] = info::f_return(
						__('The entry\'s URL %s returned a %s status code.'),
							$status);
				}
			}
		?>
		
		<h3><?php echo(__('Registered URLs')); ?></h3>
		<?php echo(info::urls()); ?>
		
		<h3><?php echo(__('Directory informations')); ?></h3>
		<p><?php echo(__('Public directory is optional.')); ?></p>
		<?php echo(info::directories(false)); ?>
	</div>

	<div class="multi-part" id="system" title="<?php echo __('System'); ?>">
		<?php
			info::fp(__('The Dotclear version is %s'),DC_VERSION);
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
			info::fp(__('The Operating System is %s.'),php_uname());
		?>
		
		<h3>PHP</h3>
		<?php
				info::fp(__('The PHP version is %s'),phpversion());
				info::fp(__('Safe mode is %s'),
					((ini_get('safe_mode') == true) ? __('active') : __('inactive')));
				info::fp(__('Maximum size of a file when uploading a file is %s'),
					files::size(DC_MAX_UPLOAD_SIZE));
				
				$error_reporting = ini_get('error_reporting');
				if ((ini_get('display_errors')) AND ($error_reporting > 0))
				{
					info::fp(__('The error_reporting level is %s'),$error_reporting);
					info::fp(__('The displayed errors are %s'),
						info::error2string($error_reporting));
				}
		?>
		
		<h3><?php echo(__('Database')); ?></h3>
		<?php info::fp(__('The database driver is %1$s and its version is %2$s'),
			$core->con->driver(),$core->con->version());
			info::fp(__('The database name is %1$s and the user is %2$s'),
				$core->con->database(),DC_DBUSER);
			info::fp(__('The tables in your database of which name begin with %s prefix are:'),
				$core->prefix); ?>
		<?php echo(info::tables()); ?>
		
		<h3><?php echo(__('Directory informations')); ?></h3>
		<?php echo(info::directories(true)); ?>
	</div>
	
	<?php
		if (!empty($errors))
		{
			echo('<div class="multi-part" id="errors" title="'.__('Errors').
				'">'.
				'<h3>'.__('Errors').'</h3>'.
				'<ul>'.
					'<li>'.
					implode('</li><li>',$errors).
					'</li>'.
				'</ul>'.
				'</div>');
		}
	?>
	
</body>
</html>