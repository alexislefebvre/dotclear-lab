<html>
  <head>
    <title><?php echo __('Tag Flash'); ?></title>
    <link rel="stylesheet" type="text/css" href="index.php?pf=tagflash/colorpicker.css"/>
    <?php echo dcPage::jsLoad('index.php?pf=tagflash/js/jquery.colorpicker.js');?>
    <style type="text/css">
      form p{clear: both;}
      label{float:left;width:180px;margin-right:10px;text-align:right;}
    </style>
    <script type="text/javascript">
      $(function() {
      $('input.color-picker')
      .each(function() {
      if ($(this).val() !== undefined) {
      if ($(this).val().indexOf('#',0)==-1) {
      $(this).css('background-color', '#'+$(this).val());
      } else {
      $(this).css('background-color', $(this).val());
      }
      }
      })
      .ColorPicker({
      onSubmit: function(hsb, hex, rgb, el) {
      $(el).val(hex);
      $(el).ColorPickerHide();
      $(el).css('background-color', '#'+hex);
      },
      onBeforeShow: function () {
      $(this).ColorPickerSetColor(this.value);
      }
      })
      .bind('keyup', function(){
      $(this).ColorPickerSetColor(this.value);
      });
      });
    </script>
    <?php echo dcPage::jsPageTabs($default_tab); ?>
  </head>
  <body>
    <h2><?php echo html::escapeHTML($core->blog->name); ?> &gt; <?php echo __('Tag Flash'); ?></h2>
    <?php if (!empty($message)):?>
    <p class="message"><?php echo $message;?></p>
    <?php endif;?>

    <div class="multi-part" id="tagflash_settings" title="<?php echo __('Settings'); ?>">
      <form action="<?php echo $p_url;?>" method="post">
	<fieldset>
	  <legend><?php echo __('Plugin activation'); ?></legend>
	  <p>
	    <label for="tagflash_active"><?php echo __('Enable Tag Flash plugin');?></label>
	    <?php echo form::checkbox('tagflash_active', 1, $tagflash_active); ?>
	  </p>
	</fieldset>
	<?php if ($tagflash_active):?>
	<fieldset>
	  <legend><?php echo __('General configuration'); ?></legend>
	  <p>
	    <label for="tagflash_width"><?php echo __('Animation width');?></label>
	    <?php echo form::field('tagflash_width', 10, 10, $tagflash_width);?>
	  </p>
	  <p>
	    <label for="tagflash_height"><?php echo __('Animation height');?></label>
	    <?php echo form::field('tagflash_height', 10, 10, $tagflash_height);?>
	  </p>
	  <p>
	    <label for="tagflash_speed"><?php echo __('Animation rotation speed');?></label>
	    <?php echo form::field('tagflash_speed', 10, 10, $tagflash_speed);?>
	  </p>
	</fieldset>
	<fieldset>
	  <legend><?php echo __('Color configuration'); ?></legend>
	  <p>
	    <label for="tagflash_bgcolor"><?php echo __('Animation background');?></label>
	    <?php echo form::field('tagflash_bgcolor', 10, 10, $tagflash_bgcolor, 'color-picker');?>
	  </p>
	  <p>
	    <label for="tagflash_color1"><?php echo __('First color');?></label>
	    <?php echo form::field('tagflash_color1', 10, 10, $tagflash_color1, 'color-picker');?>
	  </p>
	  <p>
	    <label for="tagflash_color2"><?php echo __('Second color');?></label>
	    <?php echo form::field('tagflash_color2', 10, 10, $tagflash_color2, 'color-picker');?>
	  </p>
	  <p>
	    <label for="tagflash_hicolor"><?php echo __('MouseOver color');?></label>
	    <?php echo form::field('tagflash_hicolor', 10, 10, $tagflash_hicolor, 'color-picker');?>
	  </p>
	</fieldset
	<?php endif;?>
	<?php echo form::hidden('p', 'tagflash');?>
	<?php echo $core->formNonce();?>
	<input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" />
      </form>
    </div>
    <div class="multi-part" id="tagflash_about" title="<?php echo __('About'); ?>">
      <p>
	<?php echo __('If you want more informations on that plugin or have new ideas to develope it, or want to submit a bug or need help (to install or configure it) or for anything else ...');?></p>
      <ul>
	<li>
	  <?php printf(__('Go to %sthe dedicated page%s in'), 
		'<a hreflang="fr" href="http://forum.dotclear.net/viewtopic.php?id=34559">',
		'</a>');?>
	  <a hreflang="fr" href="http://forum.dotclear.net/">forum de dotclear</a>
	</li>
	<li>
	  <?php printf(__('Go to %sthe dedicated page%s in'), 
		'<a hreflang="fr" href="http://www.nikrou.net/pages/tagFlash">',
		'</a>');?>
	  <a hreflang="fr" href="http://www.nikrou.net/">journal de nikrou</a>
	</li>
      </ul>
      <p>
	<?php echo __('Made by:');?>
	Gwénaël Després
      </p>
      <p>
	<?php echo __('Contributor:');?>
	<a href="http://www.nikrou.net/contact">Nicolas Roudaire</a> (nikrou)
      </p>
    </div>
    <?php dcPage::helpBlock('tagflash');?>
  </body>
</html>
