<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Sup Sub Tags.
# Copyright 2007 Moe (http://gniark.net/)
#
# Sup Sub Tags is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Sup Sub Tags is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Images are from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

	$supsub_tags_sup_open = $core->blog->settings->supsub_tags_sup_open;
	$supsub_tags_sup_close = $core->blog->settings->supsub_tags_sup_close;
	$supsub_tags_sub_open = $core->blog->settings->supsub_tags_sub_open;
	$supsub_tags_sub_close = $core->blog->settings->supsub_tags_sub_close;

	if (!empty($_POST['saveconfig']))
	{
		try
		{
			$core->blog->settings->setNameSpace('supsubtags');
			# text beginning
			$supsub_tags_sup_open = $_POST['supsub_tags_sup_open'];
			$supsub_tags_sup_close = $_POST['supsub_tags_sup_close'];
			$supsub_tags_sub_open = $_POST['supsub_tags_sub_open'];
			$supsub_tags_sub_close = $_POST['supsub_tags_sub_close'];

			$core->blog->settings->put('supsub_tags_sup_open',$supsub_tags_sup_open,'string','Superscript open tag');
			$core->blog->settings->put('supsub_tags_sup_close',$supsub_tags_sup_close,'string','Superscript close tag');
			$core->blog->settings->put('supsub_tags_sub__open',$supsub_tags_sub_open,'string','Subscript open tag');
			$core->blog->settings->put('supsub_tags_sub_close',$supsub_tags_sub_close,'string','Subscript close tag');

			$msg = __('Configuration successfully updated.');
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
?><html>
<head>
	<title><?php echo(__('Sup Sub Tags')); ?></title>
	<style type="text/css">
		#preview {display:none;}
		#preview input, #preview span {
			padding:0.2em;
			font-size:1.5em;
		}
	</style>
	<script type="text/javascript">
	//<![CDATA[
  	$(document).ready(function() {
  		$("#preview").show();
  		
		$('input[@type="text"]').keyup(function() {
			$("#code").val("x"+
			$("#supsub_tags_sup_open").val()+"y"+$("#supsub_tags_sup_close").val()+
			$("#supsub_tags_sub_open").val()+"z"+$("#supsub_tags_sub_close").val());

			var output = $("#code").val();
			output = output.replace($("#supsub_tags_sup_open").val(),'<sup>');
			output = output.replace($("#supsub_tags_sup_close").val(),'</sup>');
			output = output.replace($("#supsub_tags_sub_open").val(),'<sub>');
			output = output.replace($("#supsub_tags_sub_close").val(),'</sub>')
			$("#output").html(output);
		});
	});
  //]]>
	</script>
</head>
<body>

	<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt; <?php echo __('Sup Sub Tags'); ?></h2>

	<?php if (!empty($msg)) {echo '<p class="message">'.$msg.'</p>';} ?>

	<h3><?php echo __('Sup Sub Tags for wiki syntax'); ?></h3>

	<form method="post" action="<?php echo(http::getSelfURI()); ?>">
		<fieldset>
			<legend><?php echo __('Superscript'); ?></legend>
			<p class="field">
				<label for="supsub_tags_sup_open"><?php echo(__('Open tag')); ?>
				<?php echo(form::field('supsub_tags_sup_open',20,20,$supsub_tags_sup_open)); ?></label>
			</p>
			<p class="field">
				<label for="supsub_tags_sup_close"><?php echo(__('Close tag')); ?>
				<?php echo(form::field('supsub_tags_sup_close',20,20,$supsub_tags_sup_close)); ?></label>
			</p>
		</fieldset>
		<fieldset>
			<legend><?php echo __('Subscript'); ?></legend>
			<p class="field">
				<label for="supsub_tags_sub_open"><?php echo(__('Open tag')); ?>
				<?php echo(form::field('supsub_tags_sub_open',20,20,$supsub_tags_sub_open)); ?></label>
			</p>
			<p class="field">
				<label for="supsub_tags_sub_close"><?php echo(__('Close tag')); ?>
				<?php echo(form::field('supsub_tags_sub_close',20,20,$supsub_tags_sub_close)); ?></label>
			</p>
		</fieldset>
		<fieldset id="preview">
			<legend><?php echo __('Preview'); ?></legend>
			<p>
				<?php echo __('Code:'); ?> 
				<?php echo form::field('code',20,20,
				'x'.$supsub_tags_sup_open.'y'.$supsub_tags_sup_close.
				$supsub_tags_sub_open.'z'.$supsub_tags_sub_close); ?>
			</p>
			<p>
				<?php echo __('Output:'); ?> <span id="output">x<sup>y</sup><sub>z</sub></span>
			</p>
		</fieldset>
			<p><?php echo $core->formNonce(); ?></p>
			<p><input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" /></p>
	</form>
</body>