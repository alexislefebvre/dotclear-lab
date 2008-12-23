<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Contribute.
# Copyright 2008 Moe (http://gniark.net/)
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
		
		$settings->put('contribute_allow_notes',
			!empty($_POST['contribute_allow_notes']),
			'boolean','Allow contributors to write notes');
		$settings->put('contribute_allow_author',
			!empty($_POST['contribute_allow_author']),
			'boolean','Allow contributors to choose the name of the author');
		
		$settings->put('contribute_author_format',
			(!empty($_POST['contribute_author_format'])
				? $_POST['contribute_author_format'] : '%s'),
			'string','Author format');
		
		$settings->put('contribute_default_post',
			$_POST['contribute_default_post'],'integer','Default post');
		
		$settings->put('contribute_format',$_POST['contribute_format'],
			'string','Post format');
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

foreach ($core->getBlogPermissions($core->blog->id,true)
	as $user_id => $infos)
{
	if (($infos['super'] == 1)
		||(isset($infos['p']['admin'])
			&& ($infos['p']['admin'] == 1))
		||(isset($infos['p']['contentadmin'])
			&& ($infos['p']['contentadmin'] == 1)))
	{
		$name = $user_id.' '.((strlen($infos['displayname']) > 1) ?
			'('.$infos['displayname'].') ' : '').
			$infos['firstname'].' '.$infos['name'];
		
		$users[$name.(($user_id == $core->auth->userID())
			? ' ('.__('me').')' : '')] = $user_id;
	}
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
	<title><?php echo(('Contribute')); ?></title>
	<script type="text/javascript">
	//<![CDATA[
	  $(document).ready(function () {
	  	$('#contribute_default_post').change( function() {
	  		$('#edit-post').attr({href:'post.php?id='+$(this).val()});
	  	});
		});
	//]]>
  </script>
</head>
<body>

	<h2><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.('Contribute'); ?></h2>
	
	<?php 
		if (!empty($msg)) {echo '<div class="message"><p>'.$msg.'</p></div>';}
	?>
	
	<form method="post" action="<?php echo http::getSelfURI(); ?>">
		<fieldset>
			<legend><?php echo(('Contribute')); ?></legend>
			<p>
				<?php echo(
				form::checkbox('contribute_active',1,
					$settings->contribute_active)); ?>
				<label class="classic" for="contribute_active">
				<?php printf(__('Enable %s'),('Contribute')); ?>
				</label>
			</p>
			<p class="form-note">
				<?php printf(__('%s allow visitors to contribute to your blog.'),
					('Contribute')); ?>
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
				<li><!-- contentadmin --><?php echo(__('manage all entries and comments')); ?></li>
			</ul>
			
			<p>
				<label for="contribute_email_notification">
				<?php echo(__('Send emails to these email adresses when a new post is submitted:').
				form::field('contribute_email_notification',80,80,
					$settings->contribute_email_notification)); ?>
				</label>
				</p>
				<p class="form-note">
				<?php echo(__('You can enter several email adresses by separating these by a comma (<code>,</code>).').' '.
				__('Leave empty to cancel this feature.')); ?>
			</p>
			
			<p>
				<?php echo(form::checkbox('contribute_allow_excerpt',1,
					$settings->contribute_allow_excerpt)); ?>
				<label class="classic" for="contribute_allow_excerpt">
				<?php echo(__('Allow contributors to write an excerpt')); ?>
				</label>
			</p>
			
			<p>
				<?php echo(form::checkbox('contribute_allow_category',1,
					$settings->contribute_allow_category)); ?>
				<label class="classic" for="contribute_allow_category">
				<?php echo(__('Allow contributors to choose the category')); ?>
				</label>
			</p>
			
			<p>
				<?php echo(form::checkbox('contribute_allow_tags',1,
					$settings->contribute_allow_tags)); ?>
				<label class="classic" for="contribute_allow_tags">
				<?php echo(__('Allow contributors to choose the tags')); ?>
				</label>
			</p>
			
			<p>
				<?php echo(form::checkbox('contribute_allow_new_tags',1,
					$settings->contribute_allow_new_tags)); ?>
				<label class="classic" for="contribute_allow_new_tags">
				<?php echo(__('Allow contributors to add new tags (only if tags are allowed)')); ?>
				</label>
			</p>
			
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
								echo('<hr />');
							}
						}
					}
					unset($rs_values);
				}
			?>
						
			<p>
				<?php echo(form::checkbox('contribute_allow_notes',1,
					$settings->contribute_allow_notes)); ?>
				<label class="classic" for="contribute_allow_notes">
				<?php echo(__('Allow contributors to write notes')); ?>
				</label>
			</p>
			
			<p>
				<?php echo(form::checkbox('contribute_allow_author',1,
					$settings->contribute_allow_author)); ?>
				<label class="classic" for="contribute_allow_author">
				<?php echo(__('Allow contributors to choose the name of the author')); ?>
				</label>
			</p>
			
			<p>
				<label for="contribute_author_format">
				<?php echo(__('Display of the author name:').' '.__('(%s is the author name or nickname)').
				form::field('contribute_author_format',80,80,$author_format)); ?>
				</label> 
			</p>
			<p class="form-note">
				<?php echo(__('Leave empty to cancel this feature.')); ?>
			</p>
			
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
				<?php echo(__('Create a new post and select it here.').' '.
				sprintf(__('The post can be %s or %s.'),__('pending'),
				__('unpublished')).' '.
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
			
			<p>
				<?php printf(__('URL of the %s page:'),('Contribute')); ?>
				<br />
				<code><?php echo($core->blog->url.$core->url->getBase('contribute')); ?></code>
				<br />
				<a href="<?php echo($core->blog->url.$core->url->getBase('contribute')); ?>">
				<?php printf(__('View the %s page'),('Contribute')); ?></a>	
			</p>
		</fieldset>
		
		<p><?php echo $core->formNonce(); ?></p>
		<p><input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" /></p>
	</form>

</body>
</html>