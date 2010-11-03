<html>
  <head>
    <title><?php echo __('External links'); ?></title>
    <?php echo dcPage::jsPageTabs($default_tab); ?>
  </head>
  <body>
    <h2><?php echo html::escapeHTML($core->blog->name); ?> &gt; <?php echo __('External links'); ?></h2>
    <?php if (!empty($message)):?>
    <p class="message"><?php echo $message;?></p>
    <?php endif;?>

    <div class="multi-part" id="externallinks_settings" title="<?php echo __('Settings'); ?>">
      <form action="<?php echo $p_url;?>" method="post">
	<fieldset>
	  <legend><?php echo __('Plugin activation'); ?></legend>
	  <p>
	    <label class="classic">
	      <?php echo __('Enable External links plugin');?>&nbsp;
	      <?php echo form::checkbox('active', 1, $active); ?>
	      
	    </label>
	  </p>
	</fieldset>
	<?php echo form::hidden('p', 'externalLinks');?>
	<?php echo $core->formNonce();?>
	<input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" />
      </form>
    </div>
    <div class="multi-part" id="externallinks_about" title="<?php echo __('About'); ?>">
      <p>
	<?php echo __('If you want more informations on that plugin or have new ideas to develope it, or want to submit a bug or need help (to install or configure it) or for anything else ...');?></p>
      <ul>
	<li>
	  <?php printf(__('Go to %sthe dedicated page%s in'), 
		'<a hreflang="fr" href="http://forum.dotclear.net/viewtopic.php?id=43711">',
		'</a>');?>
	  <a hreflang="fr" href="http://forum.dotclear.net/">forum de dotclear</a>
	</li>
	<li>
	  <?php printf(__('Go to %sthe dedicated page%s in'), 
		'<a hreflang="fr" href="http://www.nikrou.net/pages/externalLinks">',
		'</a>');?>
	  <a hreflang="fr" href="http://www.nikrou.net/">journal de nikrou</a>
	</li>
      </ul>
      <p>
	<?php echo __('Made by:');?>
	<a href="http://www.morefnu.org/contact">Bruno Hondelatte</a> (Dsls)
      </p>
      <p>
	<?php echo __('Contributor:');?>
	<a href="http://www.nikrou.net/contact">Nicolas Roudaire</a> (nikrou)
      </p>
    </div>
  </body>
</html>
