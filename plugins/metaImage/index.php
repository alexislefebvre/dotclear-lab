<?php
$page_name = __('Meta Image');
  
  # Si les propriétés n'ont pas encore été fixées
	if ($core->blog->settings->must_have_image === null)
	{		
    $core->blog->settings->setNameSpace('metaimage');
		$core->blog->settings->put('must_have_image',0,'boolean','Force usage of image',false,true);
    $core->blog->settings->put('min_width',150,'integer','Min width',false,true);
    $core->blog->settings->put('min_height',50,'integer','Min height',false,true);
    $core->blog->settings->put('max_width',150,'integer','Max width',false,true);
    $core->blog->settings->put('max_height',450,'integer','Max height',false,true);
		http::redirect($p_url);
	}
  
  # Si l'on vient de la validation du formulaire
  if (isset($_POST['fromform'])) {
  
    $core->blog->settings->setNameSpace('metaimage');
    $valueChanged = false;
    
    # Si la valeur reçue est différente de la valeur enregistrée en base de données
    if (isset($_POST['must_have_image']) != $core->blog->settings->must_have_image) {
      $core->blog->settings->put('must_have_image',isset($_POST['must_have_image']),'boolean','Force usage of image',true,true);
      $valueChanged = true;
    }
    if ($_POST['max_width'] != $core->blog->settings->max_width) {
      $core->blog->settings->put('min_width',$_POST['min_width'],'integer','Min width',true,true);
      $valueChanged = true;
    }
    if ($_POST['min_height'] != $core->blog->settings->min_height) {
      $core->blog->settings->put('min_height',$_POST['min_height'],'integer','Min height',true,true);
      $valueChanged = true;
    }
    if ($_POST['max_width'] != $core->blog->settings->max_width) {
      $core->blog->settings->put('max_width',$_POST['max_width'],'integer','Max width',true,true);
      $valueChanged = true;
    }
    if ($_POST['max_height'] != $core->blog->settings->max_height) {
      $core->blog->settings->put('max_height',$_POST['max_height'],'integer','Max height',true,true);
      $valueChanged = true;
    }
    
    # Si au moins une valeur a été modifiée, recharger pour mettre à jour cette (ou ces) valeur(s)
    if ($valueChanged)
      http::redirect($p_url.'&up=1');
  }

?>
<html>
<head>
  <title><?php echo $page_name; ?></title>
</head>

<body>
  <h2><?php echo html::escapeHTML($core->blog->name).' &gt; '.$page_name; ?></h2>

  <h3><?php echo __('Usage'); ?></h3>
  <p><?php echo __('In your post.html template, add {{tpl:MetaImage}} where you want to print the meta image.') ?></p>
  
  <h3><?php echo __('Config'); ?></h3>
<form action="<?php echo $p_url; ?>" method="post">
  <input type="hidden" id="fromform" name="fromform" value="1" />
  <?php echo $core->formNonce(); ?> 
  <p>
    <?php echo form::checkbox('must_have_image',1,$core->blog->settings->must_have_image); ?>
    <label for="must_have_image" class="classic"><?php echo __('Posts must have an image'); ?></label>
  </p>

  <fieldset><legend><?php echo __('Images size'); ?></legend>
      <h3><?php echo __('Min size'); ?></h3>
      <p><?php echo __('Message, min size'); ?></p>
      <label for="min_width" class="classic"><?php echo __('Width'); ?></label> x 
      <label for="min_height" class="classic"><?php echo __('height'); ?></label> : 
      <input type="text" id="min_width" name="min_width" size="4" value="<?php echo $core->blog->settings->min_width ?>"/>  x  
      <input type="text" id="min_height" name="min_height" size="4" value="<?php echo $core->blog->settings->min_height ?>" />
      
      <h3 style="padding-top: 1.5em;"><?php echo __('Max size'); ?></h3>
      <p><?php echo __('Message, max size'); ?></p>
      <label for="max_width" class="classic"><?php echo __('Width'); ?></label> x 
      <label for="max_height" class="classic"><?php echo __('height'); ?></label> : 
      <input type="text" id="max_width" name="max_width" size="4" value="<?php echo $core->blog->settings->max_width ?>" />  x  
      <input type="text" id="max_height" name="max_height" size="4" value="<?php echo $core->blog->settings->max_height ?>" />
  </fieldset>
  
  <input type="submit" value="<?php echo __('save')?>" />
</form>

</body>
</html>