<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Contribute.
# Copyright 2008,2009 Moe (http://gniark.net/)
#
# Contribute is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Contribute is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$settings =& $core->blog->settings;

try
{
	if (!empty($_POST['saveconfig']))
	{
		$settings->setNameSpace('contribute');
		$settings->put('contribute_active',!empty($_POST['contribute_active']),
			'boolean','Enable Contribute');
		$settings->put('contribute_user',$_POST['contribute_user'],
			'string', 'user');
		$settings->put('contribute_email_notification',
			$_POST['contribute_email_notification'],
			'string', 'email notification');
		$settings->put('contribute_enable_antispam',
			!empty($_POST['contribute_enable_antispam']),
			'boolean', 'Enable antispam');
			
		
		$settings->put('contribute_allow_excerpt',
			!empty($_POST['contribute_allow_excerpt']),
			'boolean','Allow contributors to write an excerpt');
		$settings->put('contribute_allow_category',
			!empty($_POST['contribute_allow_category']),
			'boolean','Allow contributors to choose the category');
		$settings->put('contribute_allow_tags',
			!empty($_POST['contribute_allow_tags']),
			'boolean','Allow contributors to choose the tags');
		$settings->put('contribute_allow_new_tags',
			!empty($_POST['contribute_allow_new_tags']),
			'boolean','Allow contributors to add new tags');
		
		$settings->put('contribute_allow_notes',
			!empty($_POST['contribute_allow_notes']),
			'boolean','Allow contributors to write notes');
		$settings->put('contribute_allow_author',
			!empty($_POST['contribute_allow_author']),
			'boolean','Allow contributors to enter their name, email address and website URL');
		$settings->put('contribute_require_name_email',
			!empty($_POST['contribute_require_name_email']),
			'boolean','require name and email');
		
		$settings->put('contribute_author_format',
			(!empty($_POST['contribute_author_format'])
				? $_POST['contribute_author_format'] : '%s'),
			'string','Author format');
		
		$settings->put('contribute_default_post',
			$_POST['contribute_default_post'],'integer','Default post');
		$settings->put('contribute_format',$_POST['contribute_format'],
			'string','Post format');
			
		$settings->put('contribute_allow_mymeta',
			!empty($_POST['contribute_allow_mymeta']),
			'boolean','Allow contributors to choose My Meta values');
		
		$mymeta_values = array();
		if (!empty($_POST['mymeta_values']))
		{
			$mymeta_values = $_POST['mymeta_values'];
		}
		$mymeta_values = base64_encode(serialize($mymeta_values));
		$settings->put('contribute_mymeta_values',$mymeta_values,'string',
			'Active My Meta values');
		# inspirated by lightbox/admin.php
		$settings->setNameSpace('system');
		
		http::redirect($p_url.'&saveconfig=1');
	}
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

if (isset($_GET['saveconfig']))
{
	$msg = __('Configuration successfully updated.');
}

$formaters_combo = array();
$formaters_combo[''] = '';

# Formaters combo
foreach ($core->getFormaters() as $v) {
	$formaters_combo[$v] = $v;
}

# get the users list
$users= array();

# get users with usage permission
foreach ($core->getBlogPermissions($core->blog->id,true)
	as $user_id => $infos)
{
	$name = $user_id.' '.((strlen($infos['displayname']) > 1) ?
		'('.$infos['displayname'].') ' : '').
		$infos['firstname'].' '.$infos['name'];
	
	$users[$name.(($user_id == $core->auth->userID())
		? ' ('.__('me').')' : '')] = $user_id;
}

# get the posts list
$posts = array();
$rs = $core->blog->getPosts();

$posts[''] = '';
while ($rs->fetch())
{
	$posts[html::escapeHTML($rs->post_title)] = $rs->post_id;
}
unset($rs);

$default_post_url = 'post.php?id='.$settings->contribute_default_post;

$author_format = $settings->contribute_author_format;

if (empty($author_format)) {$author_format = __('%s (contributor)');}

?>
<html>
<head>
	<title><?php echo(__('Contribute')); ?></title>
	<script type="text/javascript">
	//<![CDATA[
	  $(document).ready(function () {
	  	$('#contribute_default_post').change( function() {
	  		$('#edit-post').attr({href:'post.php?id='+$(this).val()});
	  	});
  		
	  	$('#contribute_default_post').change( function() {
	  		if ($(this).val() == '') {
	  			$('#contribute_format').attr('disabled','');
	  		} else {
	  			$('#contribute_format').attr('disabled','disabled');
	  		}
	  	});
		});
	//]]>
  </script>
</head>
<body>

	<h2><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.__('Contribute'); ?></h2>
	
	<?php 
		if (!empty($msg)) {echo '<p class="message">'.$msg.'</p>';}
	?>
	
	<form method="post" action="<?php echo http::getSelfURI(); ?>">
		<fieldset>
			<legend><?php echo(__('General settings')); ?></legend>
			<p>
				<?php echo(
				form::checkbox('contribute_active',1,
					$settings->contribute_active)); ?>
				<label class="classic" for="contribute_active">
				<?php echo(__('Allow visitors to contribute to your blog')); ?>
				</label>
			</p>
			<p class="form-note">
				<?php echo(__('This will disable smilies on entries and comments.')); ?>
			</p>
			
			<p>
				<label for="contribute_user">
				<?php echo(__('Owner of the posts:').
				form::combo('contribute_user',$users,$settings->contribute_user)); ?>
				</label>
			</p>
			<p class="form-note">
				<?php echo(__('Only the users with the following permissions on this blog are shown:')); ?>
			</p>
			<ul class="form-note">
				<li><!-- usage --><?php echo(__('manage their own entries and comments')); ?></li>
			</ul>
			
			<p>
				<label for="contribute_email_notification">
				<?php echo(__('Send emails to these email adresses when a new post is submitted:').
				form::field('contribute_email_notification',80,255,
					$settings->contribute_email_notification)); ?>
				</label>
			</p>
			<p class="form-note">
				<?php echo(__('You can enter several email adresses by separating these by a comma (<code>,</code>).').' '.
				__('Leave empty to cancel this feature.')); ?>
			</p>
			
			<p>
				<?php echo(
				form::checkbox('contribute_enable_antispam',1,
					$settings->contribute_enable_antispam)); ?>
				<label class="classic" for="contribute_enable_antispam">
				<?php echo(__('Enable antispam.').
					' '.
					sprintf(__('It requires the %s plugin.'),
						__('Antispam'))); ?>
				</label>
			</p>
		</fieldset>
		
		<fieldset>
			<legend><?php echo(__('Allow contributors to')); ?></legend>
			<p>
				<?php echo(form::checkbox('contribute_allow_excerpt',1,
					$settings->contribute_allow_excerpt)); ?>
				<label class="classic" for="contribute_allow_excerpt">
				<?php echo(__('write an excerpt')); ?>
				</label>
			</p>
			
			<p>
				<?php echo(form::checkbox('contribute_allow_category',1,
					$settings->contribute_allow_category)); ?>
				<label class="classic" for="contribute_allow_category">
				<?php echo(__('choose the category')); ?>
				</label>
			</p>
			
			<p>
				<?php echo(form::checkbox('contribute_allow_tags',1,
					$settings->contribute_allow_tags)); ?>
				<label class="classic" for="contribute_allow_tags">
				<?php echo(__('choose the tags')); ?>
				</label>
			</p>
			
			<p>
				<?php echo(form::checkbox('contribute_allow_new_tags',1,
					$settings->contribute_allow_new_tags)); ?>
				<label class="classic" for="contribute_allow_new_tags">
				<?php echo(__('add new tags (only if tags are allowed)')); ?>
				</label>
			</p>
					
			<p>
				<?php echo(form::checkbox('contribute_allow_notes',1,
					$settings->contribute_allow_notes)); ?>
				<label class="classic" for="contribute_allow_notes">
				<?php echo(__('write notes')); ?>
				</label>
			</p>
			
			<p>
				<?php echo(form::checkbox('contribute_allow_author',1,
					$settings->contribute_allow_author)); ?>
				<label class="classic" for="contribute_allow_author">
				<?php echo(__('enter their name, email address and website URL')); ?>
				</label>
			</p>
			
			<p>
				<?php echo(form::checkbox('contribute_require_name_email',1,
					$settings->contribute_require_name_email)); ?>
				<label class="classic" for="contribute_require_name_email">
				<?php echo(__('require name and email (only if name, email address and website URL are allowed)')); ?>
				</label>
			</p>
		</fieldset>
		
		<fieldset>
			<legend><?php echo(__('Display')); ?></legend>
			<p>
				<label for="contribute_author_format">
				<?php echo(__('Display of the author name on the blog:').' '.__('(%s is the author name or nickname)').
				form::field('contribute_author_format',80,80,$author_format)); ?>
				</label> 
			</p>
			<p class="form-note">
				<?php echo(__('Leave empty to cancel this feature.')); ?>
			</p>
		</fieldset>	
		
		<fieldset>
			<legend><?php echo(__('Default post and format')); ?></legend>
			<p>
				<label for="contribute_default_post">
				<?php echo(__('Default post:').
				form::combo('contribute_default_post',$posts,
					$settings->contribute_default_post)); ?>
				</label>
				<?php if ($settings->contribute_default_post)
				{
					printf('<a href="%1$s" id="edit-post">%2$s</a>',$default_post_url,
						__('edit this post'));
				}
				?>
			</p>
			<p class="form-note">
				<?php echo(__('Select an existing post or create a new post, then select it.')).' ';
				printf(__('The post can be %s or %s.'),__('pending'),
				__('unpublished')).' ';
				echo(__('The form will be filled with the values of this post.').' '.
				__('Leave empty to cancel this feature.')); ?>
			</p>
			
			<p>
				<label for="contribute_format">
				<?php echo(__('Text formating (only if no default post is selected):').
				form::combo('contribute_format',$formaters_combo,
					$settings->contribute_format)); ?>
				</label>
			</p>
			<p class="form-note">
				<?php echo(__('Contributors will be able to choose the format.').' '.
					__('Some formats may be unavailable on the blog.').' '.
					__('Leave empty to cancel this feature.')); ?>
			</p>
		</fieldset>
		
		<fieldset>
			<legend><?php echo(__('My Meta')); ?></legend>
			<p>
				<?php echo(form::checkbox('contribute_allow_mymeta',1,
					$settings->contribute_allow_mymeta)); ?>
				<label class="classic" for="contribute_allow_mymeta">
				<?php printf(__('Allow contributors to choose %s values.'),
					__('My Meta'));
					echo(' ');
					printf(__('It requires the %s plugin.'),
						__('My Meta')); ?>
				</label>
			</p>
			
			<?php
				if ($core->plugins->moduleExists('mymeta'))
				{
					$mymeta = new myMeta($core);
					$rs_values = contribute::getMyMeta($mymeta,true);
					
					if (!$rs_values->isEmpty())
					{
						while ($rs_values->fetch())
						{
							if ($rs_values->isStart())
							{
								echo('<hr />');
								printf(__('Enable these %s values:'),__('My Meta'));
							}
							echo('<p>'.form::checkbox(
								array('mymeta_values[]','mymeta_'.$rs_values->id),
								$rs_values->id,$rs_values->active).
							'<label class="classic" for="mymeta_'.$rs_values->id.'">'.
							$rs_values->prompt.
							'</label></p>');
							if ($rs_values->isEnd())
							{
								//echo('<hr />');
							}
						}
					}
					unset($rs_values);
				}
			?>
		</fieldset>
				
		<p><?php echo $core->formNonce(); ?></p>
		<p><input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" /></p>
	</form>
	
	<hr />
	
	<p>
		<?php printf(__('URL of the %s page:'),__('Contribute')); ?>
		<br />
		<code><?php echo($core->blog->url.$core->url->getBase('contribute')); ?></code>
		<br />
		<a href="<?php echo($core->blog->url.$core->url->getBase('contribute')); ?>">
		<?php printf(__('View the %s page'),__('Contribute')); ?></a>	
	</p>

</body>
</html>