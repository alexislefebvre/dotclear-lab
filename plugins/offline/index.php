<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Offline', a plugin for Dotclear 2                 *
 *                                                             *
 *  Copyright (c) 2008                                         *
 *  Osku and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Offline mode' (see LICENCE);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

// Setting default parameters if missing configuration
if (is_null($core->blog->settings->blog_off_flag)) {
	try {
			$core->blog->settings->setNameSpace('offline');

			// Maintenance  is not active by default
			$core->blog->settings->put('blog_off_flag',false,'boolean');
			$core->blog->triggerBlog();
			http::redirect(http::getSelfURI());
		}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

// Getting current parameters
$blog_off_flag			= (boolean)$core->blog->settings->blog_off_flag;
$blog_off_ip_ok 		= $core->blog->settings->blog_off_ip_ok;
$blog_off_page_title	= $core->blog->settings->blog_off_page_title;
$blog_off_msg			= $core->blog->settings->blog_off_msg;
$myip 				= $_SERVER['REMOTE_ADDR'];

if ($blog_off_page_title === null) {
	$blog_off_page_title = __('Maintenance');
}

if ($blog_off_msg === null) {
	$blog_off_msg = __('<p class="message">D\'oh! The blog is offline.</p>');
}

if (!empty($_POST['saveconfig']))
{
	try
	{
		$blog_off_flag = (empty($_POST['blog_off_flag']))?false:true;
		$blog_off_ip_ok = $_POST['blog_off_ip_ok'];
		$blog_off_page_title = $_POST['blog_off_page_title'];
		$blog_off_msg = $_POST['blog_off_msg'];

		if (empty($_POST['blog_off_page_title'])) {
			throw new Exception(__('No page title.'));
		}

		if (empty($_POST['blog_off_msg'])) {
			throw new Exception(__('No maintenance message.'));
		}

		$core->blog->settings->setNamespace('offline');
 		$core->blog->settings->put('blog_off_flag',$blog_off_flag,'boolean');
		$core->blog->settings->put('blog_off_ip_ok',$blog_off_ip_ok,'string','Authorized IP');
		$core->blog->settings->put('blog_off_page_title',$blog_off_page_title,'string','Maintenance page title');
		$core->blog->settings->put('blog_off_msg',$blog_off_msg,'string','Maintenance message');

		$core->blog->triggerBlog();

		$msg = __('Configuration successfully updated.');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
?>
<html>
<head>
	<title><?php echo __('Offline mode'); ?></title>
</head>
<body>
<h2 style="padding:8px 0 8px 34px;background:url(index.php?pf=offline/icon_32.png) no-repeat;">
<?php echo html::escapeHTML($core->blog->name); ?> &rsaquo; <?php echo __('Offline mode'); ?></h2>

<?php if (!empty($msg)) echo '<p class="message">'.$msg.'</p>'; ?>

<div id="offline_options">
	<form method="post" action="plugin.php">
		<fieldset>
			<legend><?php echo __('Plugin activation'); ?></legend>
				<p class="field">
					<?php echo form::checkbox('blog_off_flag', 1, $blog_off_flag); ?>
						<label class="classic" for="blog_off_flag"> <?php echo __('Enable Offline mode');?></label>
				</p>
				<p class="form-note"><?php echo __('Activating this plugin redirect all urls to one.'); ?></p>
		</fieldset>
		<fieldset>
			<legend><?php echo __('IP restricted access'); ?></legend>
				<p class="classic"><?php echo __('My own IP is'); ?><strong> <?php echo $myip ;?></strong></p>
				<p><label class="classic">
					<?php echo __('Authorized IP:');?>
					<?php echo form::field('blog_off_ip_ok',20,39,html::escapeHTML($blog_off_ip_ok)); ?>
				</label></p>
				<p class="form-note"><?php echo __('With this option, a visitor having this IP can access the website.'); ?></p>
		</fieldset>
		<fieldset>
			<legend><?php echo __('Presentation options'); ?></legend>
				<p><label class="required" title="__('Required field')">
					<?php echo __('Offline title:');?>
					<?php echo form::field('blog_off_page_title',30,256,html::escapeHTML($blog_off_page_title)); ?>
				</label></p>
				<p class="area"><label class="required" title="'.__('Required field').'">
					<?php echo __('Offline message:');?>
					<?php echo form::textarea('blog_off_msg',30,2,html::escapeHTML($blog_off_msg)); ?>
				</label></p>
		</fieldset>

		<p><input type="hidden" name="p" value="offline" />
		<?php echo $core->formNonce(); ?>
		<input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" /></p>
	</form>
</div>
</body>
</html>
