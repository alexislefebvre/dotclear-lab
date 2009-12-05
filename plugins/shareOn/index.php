<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of shareOn, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

if (!isset($core->shareOnButtons)) $core->shareOnButtons = array();

require_once dirname(__FILE__).'/inc/class.shareon.php';


# Vars
$s =& $core->blog->settings;
$_active = (boolean) $s->shareOn_active;
$_style = (string) $s->shareOn_style;
$_title = (string) $s->shareOn_title;
$_home_place = (string) $s->shareOn_home_place;
$_cat_place = (string) $s->shareOn_cat_place;
$_tag_place = (string) $s->shareOn_tag_place;
$_post_place = (string) $s->shareOn_post_place;
$default_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'settings';
$msg = '';

$combo_place = array(
	__('hide') => '',
	__('before content') => 'before',
	__('after content') => 'after'
);

# Save settings
if (!empty($_POST['settings']))
{
	try
	{
		$_active = isset($_POST['_active']);
		$_style = isset($_POST['_style']) ? $_POST['_style'] : '';
		$_title = isset($_POST['_title']) ? $_POST['_title'] : '';
		$_home_place = isset($_POST['_home_place']) ? $_POST['_home_place'] : '';
		$_cat_place = isset($_POST['_cat_place']) ? $_POST['_cat_place'] : '';
		$_tag_place = isset($_POST['_tag_place']) ? $_POST['_tag_place'] : '';
		$_post_place = isset($_POST['_post_place']) ? $_POST['_post_place'] : '';

		$s->setNamespace('shareOn');
		$s->put('shareOn_active',$_active);
		$s->put('shareOn_style',$_style);
		$s->put('shareOn_title',$_title);
		$s->put('shareOn_home_place',$_home_place);
		$s->put('shareOn_cat_place',$_cat_place);
		$s->put('shareOn_tag_place',$_tag_place);
		$s->put('shareOn_post_place',$_post_place);
		$s->setNamespace('system');

		$core->blog->triggerBlog();
		http::redirect($p_url.'&tab=settings&updsettings=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

# Save buttons settings
if (!empty($_POST['buttons']))
{
	try
	{
		foreach($core->shareOnButtons as $button_id => $button)
		{
			$o = new $button($core);
			$b_active = isset($_POST[$button_id.'_active']);
			$b_small = isset($_POST[$button_id.'_small']);
			
			$o->saveSettings($b_active,$b_small);
			$o->moreSettingsSave();
		}
		$core->blog->triggerBlog();
		http::redirect($p_url.'&tab=buttons&updbuttons=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

?>
<html>
 <head>
  <title><?php echo __('Share on'); ?></title>
  <?php echo 
	dcPage::jsToolBar().
	dcPage::jsLoad('js/_posts_list.js').
	dcPage::jsPageTabs($default_tab);
  ?>
 </head>
 <body>
  <h2><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.__('Share on'); ?></h2>
  <?php echo $msg; ?>

  <div class="multi-part" id="settings" title="<?php echo __('Settings'); ?>">
   <form method="post" action="plugin.php">

   <?php 
   if (isset($_GET['updsettings']))
	echo '<p class="message">'.__('Configuration successfully updated').'</p>';
   ?>

  <fieldset><legend><?php echo __('Common'); ?></legend>
    <p><label class="classic"><?php echo
	 form::checkbox(array('_active'),'1',$_active).' '.
     __('Enable plugin'); ?>
	</label></p>

	<p class="area" id="style-area"><label for="_style"><?php echo __('CSS:'); ?></label>
	<?php echo form::textarea('_style',50,3,html::escapeHTML($_style),'',2); ?>
	</p>
	<p class="form-note"><?php echo __('You can add here special cascading style sheet. Share on bar has class "shareonentry" and widget has class "shareonwidget".'); ?></p>
  </fieldset>

  <fieldset><legend><?php echo __('Entries'); ?></legend>
	<p class="form-note"><?php echo __('To use this option you must have behavior "publicEntryBeforeContent", "publicEntryAfterContent" and "publicHeadContent" in your theme.'); ?></p>

	<p><label class="classic"><?php echo 
	__('Title:').'<br />'.
	form::field(array('_title'),50,255,$_title); ?>
	</label></p>
	<p class="form-note"><?php echo __("Title of group of buttons could be empty."); ?></p>

	<p><label class="classic"><?php echo
	__('Show buttons on each posts on home page:').'<br />'.
	form::combo(array('_home_place'),$combo_place,$_home_place); ?>
	</label></p>
	<p><label class="classic"><?php echo
	__('Show buttons on each posts on categorie page:').'<br />'.
	form::combo(array('_cat_place'),$combo_place,$_cat_place); ?>
	</label></p>
	<p><label class="classic"><?php echo
	__('Show buttons on each posts on tag page:').'<br />'.
	form::combo(array('_tag_place'),$combo_place,$_tag_place); ?>
	</label></p>
	<p><label class="classic"><?php echo
	__('Show buttons on a post page:').'<br />'.
	form::combo(array('_post_place'),$combo_place,$_post_place); ?>
	</label></p>
  </fieldset>

    <p>
     <input type="submit" name="settings" value="<?php echo __('Save'); ?>" />
     <?php echo 
      form::hidden(array('p'),'shareOn').
      form::hidden(array('tab'),'settings').
      $core->formNonce();
     ?>
	</p>
   </form>
  </div>

  <div class="multi-part" id="buttons" title="<?php echo __('Buttons'); ?>">
   <form method="post" action="plugin.php">

   <?php 
   if (isset($_GET['updbuttons']))
	echo '<p class="message">'.__('Configuration successfully updated').'</p>';
   ?>

	<?php
	foreach($core->shareOnButtons as $button_id => $button)
	{
		$o = new $button($core);
		echo 
		'<fieldset><legend>'.$o->name.'</legend>';
		if (!empty($o->home))
		{ 
			echo '<p><a title="'.__('homepage').'" href="'.$o->home.'">'.sprintf(__('Learn more about %s.'),$o->name).'</a></p>';
		}
		echo
		'<p><label class="classic">'.
		form::checkbox(array($button_id.'_active'),'1',$o->_active).' '.
		__('Enable this button').
		'</label></p>'.

		'<p><label class="classic">'.
		form::checkbox(array($button_id.'_small'),'1',$o->_small).' '.
		__('Use small button').
		'</label></p>'.
		$o->moreSettingsForm().
		'</fieldset>';
	}
	?>

    <p>
     <input type="submit" name="buttons" value="<?php echo __('Save'); ?>" />
     <?php echo 
      form::hidden(array('p'),'shareOn').
      form::hidden(array('tab'),'buttons').
      $core->formNonce();
     ?>
	</p>
   </form>
  </div>


  <hr class="clear"/>
  <p class="right">
   shareOn - <?php echo $core->plugins->moduleInfo('shareOn','version'); ?>&nbsp;
   <img alt="shareOn" src="index.php?pf=shareOn/icon.png" />
  </p>
 </body>
</html>