<?php
/* BEGIN LICENSE BLOCK
This file is part of SendToFriend, a plugin for Dotclear.

Julien Appert
brol contact@brol.info

Licensed under the GPL version 2.0 license.
A copy of this license is available in LICENSE file or at
http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
END LICENSE BLOCK */
if (!defined('DC_CONTEXT_ADMIN')) { return; }

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/public');

$settings = new dcSettings($core,$core->blog->id);
$settings->addNamespace('sendtofriend');
if(isset($_POST['type']))
{
	switch($_POST['type']){
		case 'emailContent':
			$settings->sendtofriend->put('sendtofriend_subject',$_POST['subject']);
			$settings->sendtofriend->set('sendtofriend_subject',$_POST['subject']);
			
			$settings->sendtofriend->put('sendtofriend_content',$_POST['stfcontent']);
			$settings->sendtofriend->set('sendtofriend_content',$_POST['stfcontent']);
			$sMessage = __('Email content updated');
		break;
		case 'abstractContent':
			$settings->sendtofriend->put('sendtofriend_abstractType',$_POST['abstractType']);
			$settings->sendtofriend->set('sendtofriend_abstractType',$_POST['abstractType']);
			
			$settings->sendtofriend->put('sendtofriend_firstWords',$_POST['firstWords']);
			$settings->sendtofriend->set('sendtofriend_firstWords',$_POST['firstWords']);
			$sMessage = __('Abstract content updated');		
		break;
	}
}
?>
<html>
<head>
	<title><?php echo __('Send to friend'); ?></title>
	<?php
	if(isset($_POST['defaultTab']))
	{
		echo dcPage::jsPageTabs($_POST['defaultTab']); 
	}
	else 
	{
		echo dcPage::jsPageTabs(); 
	}
	?>
  <style type="text/css">
  @import "index.php?pf=sendToFriend/style/admin.css";
  </style>	
  <script type="text/javascript">
 function gereFirsWordsContainer(elem){
	if(elem.value=='firstWords'){
		$('#firstWordsContainer').css('display','block');
	}
	else{
		$('#firstWordsContainer').css('display','none');
	}
} 
  </script>
</head>
<body>
<?php echo '<h2>'.__('Send to friend').' - '.$core->plugins->moduleInfo('sendToFriend','version').'</h2>'; ?>

<div class="multi-part" title="<?php echo __('Installation'); ?>">
	<p><?php echo __('Copy/paste this code in'); ?> <strong>home.html</strong> <?php echo __('and/or'); ?> <strong>post.html</strong> <?php echo __('and/or'); ?> <strong>page.html</strong> :</p>
	<pre>
      &lt;a href="{{tpl:BlogURL}}envoyer/{{tpl:EntryID}}" class="sendToFriend"&gt;{{tpl:lang Send to friend}}&lt;/a&gt;
	</pre>
	<p><?php echo __('after this line:'); ?></p>
	<pre>
	&lt;tpl:EntryIf operator="or" show_comments="1" show_pings="1" has_attachment="1"&gt;
	</pre>
</div>

<div class="multi-part" id="configure" title="<?php echo __('Configure'); ?>">
	<?php if(isset($sMessage) ){ ?>
		<p class="message"><?php echo $sMessage; ?></p>
	<?php } ?>
		<form action="plugin.php" method="post">
	<fieldset>
		<legend><?php echo __('Abstract content'); ?></legend>
			<input type="hidden" name="p" value="sendToFriend" />
			<input type="hidden" name="type" value="abstractContent" />
			<input type="hidden" name="defaultTab" value="1" />
			<p>
				<label  for="abstractType"><?php echo __('Abstrat type:'); ?></label>
				<select onchange="gereFirsWordsContainer(this)" class="select" name="abstractType" id="abstractType">
					<option value="abstract"><?php echo __('use defined abstract'); ?></option>
					<option <?php if($settings->sendtofriend->get('sendtofriend_abstractType') == 'firstWords') echo 'selected="selected"'; ?> value="firstWords"><?php echo __('extract first words'); ?></option>
					<option <?php if($settings->sendtofriend->get('sendtofriend_abstractType') == 'none') echo 'selected="selected"'; ?> value="none"><?php echo __('none'); ?></option>
				</select>
			</p>		
			<p id="firstWordsContainer" style="display:<?php echo ($settings->get('sendtofriend_abstractType') == 'firstWords')?'block':'none'?>">
				<label for="firstWords"><?php echo __('First words'); ?></label>
				<input type="text" style="width:50px" name="firstWords" id="firstWords" value="<?php echo $settings->sendtofriend->get('sendtofriend_firstWords'); ?>" />
			</p>
			<p><input type="submit" class="button" value="<?php echo __('Validate'); ?>" /></p>
		<?php echo $core->formNonce(); ?>
	</fieldset>
		</form>
		<form action="plugin.php" method="post">
	<fieldset>
		<legend><?php echo __('Email content'); ?></legend>
			<input type="hidden" name="p" value="sendToFriend" />
			<input type="hidden" name="type" value="emailContent" />
			<input type="hidden" name="defaultTab" value="1" />
			
				<dl>
					<dt>%post-title% :</dt>
					<dd><?php echo __('Title of the shared entry'); ?></dd>		
					<dt>%post-url% :</dt>
					<dd><?php echo __('Url of the shared entry'); ?></dd>		
					<dt>%post-abstract% :</dt>
					<dd><?php echo __('Abstract of the shared entry'); ?></dd>					
					<dt>%sender-name% :</dt>
					<dd><?php echo __('Sender name'); ?></dd>		
					<dt>%sender-email% :</dt>
					<dd><?php echo __('Sender email'); ?></dd>	
					<dt>%receiver-name% :</dt>
					<dd><?php echo __('Receiver name'); ?></dd>		
					<dt>%receiver-email% :</dt>
					<dd><?php echo __('Receiver email'); ?></dd>				
				</dl>
			<p>
				<label for="subject"><?php echo __("Subject:"); ?></label>
				<input type="text" name="subject" id="subject" value="<?php echo $settings->sendtofriend->get('sendtofriend_subject'); ?>" />
			</p>
			<p>
				<label for="stfcontent"><?php echo __("Content:"); ?></label>
				<textarea name="stfcontent" id="stfcontent" rows="4" cols="20"><?php echo $settings->sendtofriend->get('sendtofriend_content'); ?></textarea>
			</p>
			<p><input type="submit" class="button" value="<?php echo __('Validate'); ?>" /></p>
			<?php echo $core->formNonce(); ?>
	</fieldset>
		</form>
</div>

<div class="multi-part" title="<?php echo __('About'); ?>">
	<p><?php echo __("This plugin was developed by"); ?> Julien Appert <?php echo __('and contributors'); ?> (brol).</p>
	<p><?php echo __('For more informations, visit the'); ?> <a href="http://forum.dotclear.net/viewtopic.php?id=43911"><?php echo __("forum dotclear"); ?></a></p>
</div>
</body>
</html>
