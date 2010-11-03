<html>
  <head>
    <title><?php echo __('Add a link');?></title>
    <script type="text/javascript" src="index.php?pf=externalLinks/js/popup.min.js"></script>
  </head>
  <body>
    <h2><?php echo __('Add a link'); ?></h2>
    
    <form id="link-insert-form" action="#" method="get">
      <p>
	<label class="required" title="<?php __('Required field');?>">
	  <?php 
	     echo __('Link URL:');
	     echo form::field('href',35,512,html::escapeHTML($href));
	     ?>
	</label>
      </p>
      <p>
	<label>
	  <?php 
	     echo __('Link language:');
	     echo form::combo('hreflang',$lang_combo,$hreflang);
	     ?>
	</label>
      </p>
      <p>
	<label class="classic">
	  <?php 
	  echo form::checkbox('external',1, $external);
	     echo __('external link?');
	     ?>
	</label>
      </p>
    </form>

    <p>
      <a class="button" href="#" id="link-insert-cancel"><?php echo __('cancel');?></a> - 
      <strong><a class="button" href="#" id="link-insert-ok"><?php echo __('insert');?></a></strong>
    </p>
  </body>
</html>
