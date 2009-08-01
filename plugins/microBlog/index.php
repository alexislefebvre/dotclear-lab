<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Micro-Blogging, a plugin for Dotclear.
# 
# Copyright (c) 2009 Jeremie Patonnier
# jeremie.patonnier@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

# TODO MAKE A FULL REFACTORING, THIS PART IS UGLY

$default_tab = 'tab1';
 
if (isset($_REQUEST['tab'])) {
	$default_tab = $_REQUEST['tab'];
}

$MicroBlog = microBlog::init($core);

$services = array(
	__('Choose a service') => ''
);

$MBl = $MicroBlog->getServicesList();
$MBs = $MicroBlog->getServicesType();
$s   = array_flip($MBs);

$services = array_merge($services, $s);



if (!empty($_POST))
{
	$tab = isset($_POST['MB_send']) ? 1 : 2;
	$out = $tab;
	
	if (isset($_POST['MB_note'])
	&& isset($_POST['MB_services'])
	&& is_array($_POST['MB_services']))
	{
		$l = strlen($_POST['MB_note']);
		
		if ($l == 0)
			$out = 8;
		else if ($l > 140)
			$out = 9;
		else
		{
			foreach ($_POST['MB_services'] as $id)
			{
				$s = $MicroBlog->getServiceAccess($id);
				$r = $s->sendNote($_POST['MB_note']);
				if (!$r) $out = 7;
			}
		}
	}
	
	//echo '<pre>';
	//var_dump($l);
	//var_dump($_POST);
	//echo '</pre>';
	//exit;
	
	if (isset($_POST['MB_service'])
	&& isset($_POST['MB_login'])
	&& isset($_POST['MB_pwd']))
	{
		if (array_key_exists($_POST['MB_service'], $MBs))
		{
			$r = $MicroBlog->addService($_POST['MB_service'], $_POST['MB_login'], $_POST['MB_pwd']);
			if (!$r) $out = 4; 
		}
		else if (!empty($_POST['MB_service']))
		{
			$out = 3;
		}
	}
	
	if (isset($_POST['MB_s_liste']) 
	&& is_array($_POST['MB_s_liste']))
	{
		foreach ($_POST['MB_s_liste'] as $k => $v)
		{
			//echo '<pre>'.$k.' : '.$v.'</pre>';
			if ($v == -1)
			{
				$r = $MicroBlog->deleteService($k);
				if (!$r) $out = 5;
			}
			else
			{
				$a = array(
					'isActive'              => $v == 1,
					'sendNoteOnNewBlogPost' => isset($_POST['MB_auto']) && is_array($_POST['MB_auto']) && in_array($k, $_POST['MB_auto'])
				);
				
				$r = $MicroBlog->updateServiceParams($k, $a);
				if (!$r) $out = 6;
				
				//echo '<pre>';
				//var_dump($a);
				//echo '</pre>';
			}
		}
	}
	
	//echo '<pre>';
	//var_dump($_POST);
	//echo '</pre>';
	//exit;
	http::redirect($p_url.'&tab=tab'. $tab .'&isdone=' . $out);
}

if (isset($_GET['isdone'])){
	$k = (int)$_GET['isdone'];
	$aMsg = array(
		1 => __('Note successfuly send'),
		2 => __('Setting successfuly saved'),
		3 => __('Unknown service'),
		4 => __('Service cannot be added'),
		5 => __('Service cannot be deleted'),
		6 => __('Unable to update services'),
		7 => __('Unable to send note'),
		8 => __('Your note is empty'),
		9 => __('Your note is to long'),
	);
	
	$msg = $aMsg[$k];
}
?>
<html>
<head>
	<title><?php echo(__('MicroBlog')); ?></title>
	
	<?php echo dcPage::jsPageTabs($default_tab); ?>
	
	<script type="text/javascript">
	//<![CDATA[
	function MB_length(){
		var c = 140 - $("#MB_note").val().length;
		var node = $("#MB_note_length span").text(c).parent();

		if (c < 0 && !node.hasClass("fail"))
			node.addClass("fail");
		else if (c >= 0 && node.hasClass("fail"))
			node.removeClass("fail");
	}
	
	$(function(){
		$("#MB_note")
		.keyup(MB_length);
	});
	//]]>		
	</script>
	
	<style type="text/css">
	.fail{color : #900;}
	</style>
</head>
<body>
 
	<h2><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.
		__('MicroBlog'); ?></h2>
	
	<?php if (!empty($msg)) {echo '<p class="message">'.$msg.'</p>';} ?>
 
	<div class="multi-part" id="tab1" title="<?php echo __('Add a micro note'); ?>">
<?php 
if ($MBl->count() < 1){
?>
		<p class="message"><?php echo __('You must define at least 1 service.'); ?></p>
<?php 
}
else
{
?>
		<form method="post" action="<?php echo($p_url); ?>">
			<div id="entry-sidebar">
				<h3><?php echo __('Send to'); ?></h3>
				<ul>
<?php 
	$i = 0;
	while ($MBl->fetch())
	{
		if (!array_key_exists($MBl->service, $MBs))
			continue;
		
		$params   = $MicroBlog->getServiceParams($MBl->id);
		$isActive = $params['isActive'];
?>
					<li><label class="classic"><?php echo(form::checkbox(array('MB_services[]','MB_service_'.$i++),$MBl->id, $isActive).' '.__($MBs[$MBl->service]).' ('.$MBl->user.')'); ?></label></li>
<?php 
	}
?>
				</ul>
			</div>
			
			<div id="entry-content">
				<h3><label for="note"><?php echo(__('Content')); ?></label></h3>
				<p class="area">
					<?php echo form::textarea('MB_note', 20, 5, ""); ?>
					<strong id="MB_note_length"><span>140</span> <?php echo __('characters left'); ?></strong>
				</p>

				<p>
					<?php echo $core->formNonce(); ?>
					<input type="submit" name="MB_send" value="<?php echo __('Send'); ?>" />
				</p>
		
			</div>
		</form>
<?php 
}
?>
	</div>
 
	<div class="multi-part" id="tab2" title="<?php echo __('Params'); ?>">
		<form method="post" action="<?php echo($p_url); ?>">
			<h3><?php echo(__('MicroBlogging settings')); ?></h3>
<?php 
if ($MBl->count() > 0)
{
?>
			<fieldset>
				<legend><?php echo __('Accounts settings'); ?></legend>
				
				<table>
					<thead>
						<tr>
							<th scope="col"><?php echo __('Service'); ?></th>
							<th scope="col"><?php echo __('User'); ?></th>
							<th scope="col"><img src="<?php echo str_replace($_SERVER['DOCUMENT_ROOT'], "", dirname(__FILE__)."/img/note_go.png"); ?>" alt="<?php echo __('Send a note for each new blog post') ?>" title="<?php echo __('Send a note for each new blog post') ?>" /></th>
							<th scope="col"><img src="<?php echo str_replace($_SERVER['DOCUMENT_ROOT'], "", dirname(__FILE__)."/img/note_add.png"); ?>" alt="<?php echo __('Activate') ?>" title="<?php echo __('Activate') ?>" /></th>
							<th scope="col"><img src="<?php echo str_replace($_SERVER['DOCUMENT_ROOT'], "", dirname(__FILE__)."/img/note_delete.png"); ?>" alt="<?php echo __('Suspend') ?>" title="<?php echo __('Suspend') ?>" /></th>
							<th scope="col"><img src="<?php echo str_replace($_SERVER['DOCUMENT_ROOT'], "", dirname(__FILE__)."/img/exclamation.png"); ?>" alt="<?php echo __('Delete') ?>" title="<?php echo __('Delete') ?>" /></th>
						</tr>
					</thead>
					<tbody>
<?php 
	$i = 0;
	$MBl->moveStart();
	while ($MBl->fetch())
	{
		if (!array_key_exists($MBl->service, $MBs))
			continue;
		
		$i++;
		$params = $MicroBlog->getServiceParams($MBl->id);
?>
						<tr>
							<th scope="row"><label for="MB_s_liste_<?php echo $i; ?>_a"><?php echo __($MBs[$MBl->service]); ?></label></th>
							<td><?php echo $MBl->user; ?></td>
							<td><?php echo form::checkbox(array('MB_auto[]','MB_auto_'.$i), $MBl->id, $params['sendNoteOnNewBlogPost']); ?></td>
							<td><?php echo form::radio(array('MB_s_liste['.$MBl->id.']','MB_s_liste_'.$i.'_a'), '1', $params['isActive']); ?></td>
							<td><?php echo form::radio(array('MB_s_liste['.$MBl->id.']','MB_s_liste_'.$i.'_b'), '0',!$params['isActive']); ?></td>
							<td><?php echo form::radio(array('MB_s_liste['.$MBl->id.']','MB_s_liste_'.$i.'_c'),'-1', false); ?></td>
						</tr>
<?php 
	}
?>
					</tbody>
				</table>
			</fieldset>
<?php 
}
?>
			<fieldset class="clear">
				<legend><?php echo __('Add an account'); ?></legend>
				
				<p style="float:left;margin-right:1em;">
					<label for="service"><?php echo __('Service'); ?></label>
					<?php echo form::combo('MB_service',$services); ?>
				</p>
				<p style="float:left;margin-right:1em;">
					<label for="login"><?php echo __('Login'); ?></label>
					<?php echo form::field('MB_login',16, 240); ?>
				</p>
				<p style="float:left;">
					<label for="pwd"><?php echo __('Password'); ?></label>
					<?php echo form::password('MB_pwd',16, 240); ?>
				</p>
			</fieldset>
			
			<p>
				<?php echo $core->formNonce(); ?>
				<input type="submit" name="MB_save" value="<?php echo __('Save'); ?>" />
			</p>
		</form>
	</div>
 
</body>
</html>