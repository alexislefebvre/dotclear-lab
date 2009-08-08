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

# TODO NEED TO CHECK HOW TO REDUCE CODE AND MAKE IT REUSABLE

$default_tab = 'tab1';
 
if (isset($_REQUEST['tab'])) {
	$default_tab = $_REQUEST['tab'];
}

$MicroBlog = microBlog::init($core);
$MBl = $MicroBlog->getServicesList();
$MBs = $MicroBlog->getServicesType();

$services = array(
	__('Choose a service') => ''
);
$s   = array_flip($MBs);
$services = array_merge($services, $s);
unset($s);


# -- BEGIN POST DATAS BLOCK -------------------------------
# Perform action if POST datas are send
if (!empty($_POST))
{
	//echo '<pre>';
	//var_dump($_POST);
	//echo '</pre>';
	
	$sList = array();
	while($MBl->fetch())
	{
		$sList[$MBl->id] = $MBs[$MBl->service].' ('.$MBl->user.')';
	}
	
	$tab      = isset($_POST['MB_send']) ? 1 : 2;
	$_SESSION['mb_post_msg']    = array();
	$_SESSION['mb_post_values'] = array();
	
	# ----------------------------------------------------
	# Envoie d'une nouvelle note
	if (isset($_POST['MB_note'])
	 && isset($_POST['MB_services']))
	{
		$error = array();
		
		$l = strlen($_POST['MB_note']);
		
		if ($l == 0)
			$error[] = __('A note can not be empty.');
			
		// TODO VERIFIER QUE CE CONTROLE EST PERTINANT ICI
		else if ($l > 140)
			$error[] = __('A note can not be longer than 140 caracters.');
		
		else if(!is_array($_POST['MB_services'])
		|| empty($_POST['MB_services']))
			$error[] = __('You must choose a service to send a note.');
		
		else
		{
			foreach ($_POST['MB_services'] as $id)
			{
				try
				{
					$s = $MicroBlog->getServiceAccess($id);
					$r = $s->sendNote($_POST['MB_note']);
					
					if (!$r) 
						$error[] = $sList[$id]. ' : '. __('This note can not be send.');
				}
				catch (microBlogException $e)
				{
					$error[] = $sList[$id]. ' : '.__('This note can not be send.');
					$error[] = $sList[$id]. ' : '.__($e->getMessage());
				}
			}
		}
		
		if(!empty($error)) {
			$_SESSION['mb_post_msg'] = array_merge($_SESSION['mb_post_msg'], $error);
			$_SESSION['mb_post_values']['MB_note'] = $_POST['MB_note'];
		}
		else {
			$_SESSION['mb_post_msg'][] = __('Note successfully send.');
		}
	}
	
	# ----------------------------------------------------
	# Ajout d'un nouveau service
	if (!empty($_POST['MB_service'])
	 && isset($_POST['MB_login'])
	 && isset($_POST['MB_pwd']))
	{
		$error = array();
		
		if (array_key_exists($_POST['MB_service'], $MBs))
		{
			try
			{
				$r = $MicroBlog->addService($_POST['MB_service'], 
				                            $_POST['MB_login'], 
				                            $_POST['MB_pwd']);
				if (!$r) 
					$error[] = __('Impossible to add this new service.');
			}
			catch (microBlogException $e)
			{
				$error[] = __('Impossible to add this new service.');
				$error[] = __($e->getMessage());
			}
		}
		else if (!empty($_POST['MB_service']))
		{
			$error[] = __('This service is not supported.');
		}
		
		if(!empty($error)) {
			$_SESSION['mb_post_msg'] = array_merge($_SESSION['mb_post_msg'], $error);
		}
		else {
			$_SESSION['mb_post_msg'][] = __('Service successfully added.');
		}
	}
	
	# ----------------------------------------------------
	# Paramétrage des services
	if (isset($_POST['MB_s']) 
	 && is_array($_POST['MB_s']))
	{
		$error = array();

		foreach ($_POST['MB_s'] as $id => $param)
		{
			if (!is_array($param)) continue;
			
			if (isset($param['delete'])
			 && $param['delete'] == 1)
			{
				// TODO PENSER A SUPPRIMER LE WIDGET CORRESPONDANT
				try
				{
					$r = $MicroBlog->deleteService($id);
				
					if (!$r)
						$error[] = $sList[$id]. ' : '.__('Impossible to delete this service.');
					else
						$error[] = __('Service successfully deleted.').' : '.$sList[$id];
				}
				catch (microBlogException $e)
				{
					$error[] = $sList[$id]. ' : '.__('Impossible to delete this service.');
					$error[] = $sList[$id]. ' : '.__($e->getMessage());
				}
			}
			else
			{
				$a = array(
					'isActive'              => isset($param['active']) && $param['active'] == 1,
					'sendNoteOnNewBlogPost' => isset($param['auto'])   && $param['auto']   == 1
				);
			
				try
				{
					$r = $MicroBlog->updateServiceParams($id, $a);
				
					if (!$r)
						$error[] = $sList[$id]. ' : '.__('Impossible to update this service.');
					else
						$error[] = __('Service successfully updated.').' : '.$sList[$id];
				}
				catch (microBlogException $e)
				{
					$error[] = $sList[$id]. ' : '.__('Impossible to update this service.');
					$error[] = $sList[$id]. ' : '.__($e->getMessage());
				}
			}
		}
		
		if(!empty($error)) {
			$_SESSION['mb_post_msg'] = array_merge($_SESSION['mb_post_msg'], $error);
		}
		else {
			$_SESSION['mb_post_msg'][] = __('Services successfully updated');
		}
	}
	
	http::redirect($p_url.'&tab=tab'. $tab .'&isdone=1');
}
# -- END POST DATAS BLOCK ---------------------------------

if (isset($_GET['isdone']))
{
	if(!empty($_SESSION['mb_post_msg']))
	{
		$msg = implode("<br />\n", (array)$_SESSION['mb_post_msg']);
		unset($_SESSION['mb_post_msg']);
	}	
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

	function MB_chk_delete(){
		if($(this).attr('checked') == true){
			if(!confirm('Etes-vous sur de vouloir supprimer ce service ?')){
				$(this).attr('checked', false);
			}
		}
	}
	
	$(function(){
		$("#MB_note")
		.keyup(MB_length);

		$(".delete")
		.change(MB_chk_delete);
	});
	//]]>		
	</script>
	
<style type="text/css">
.fail{color : #900;}
.danger{background : #FCC;}
</style>
</head>
<body>

	<h2><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.
		__('MicroBlog'); ?></h2>
	
<?php 
if (!empty($msg))
{
	echo '	<p class="message">'.$msg.'</p>';
}
?>

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
				<h3><?php echo __('Send to:'); ?></h3>
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
				<h3><label for="note"><?php echo __('Content'); ?></label></h3>
				<p class="area">
					<?php 
			$value = isset($_SESSION['mb_post_values']['MB_note'])
			       ? $_SESSION['mb_post_values']['MB_note']
			       : "";
					
			echo form::textarea('MB_note', 20, 5, $value); 
					?>
					<strong id="MB_note_length"><?php echo sprintf(__('<span>%d</span> characters left'), 140); ?></strong>
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
			<h3><?php echo(__('Micro-Blogging settings')); ?></h3>
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
							<th scope="col" class="danger"><img src="<?php echo str_replace($_SERVER['DOCUMENT_ROOT'], "", dirname(__FILE__)."/img/exclamation.png"); ?>" alt="<?php echo __('Delete') ?>" title="<?php echo __('Delete') ?>" /></th>
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
							<td><?php echo form::checkbox(array('MB_s['.$MBl->id.'][auto]','MB_s_'.$i.'_a'), '1', $params['sendNoteOnNewBlogPost']); ?></td>
							<td><?php echo form::radio(array('MB_s['.$MBl->id.'][active]', 'MB_s_'.$i.'_b'), '1', $params['isActive']); ?></td>
							<td><?php echo form::radio(array('MB_s['.$MBl->id.'][active]', 'MB_s_'.$i.'_c'), '0',!$params['isActive']); ?></td>
							<td class="danger"><?php echo form::checkbox(array('MB_s['.$MBl->id.'][delete]','MB_s_'.$i.'_d'), '1', false, 'delete'); ?></td>
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