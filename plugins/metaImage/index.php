<?php
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$label = 'metaImage';
$p_name = __('Meta Image');

if ($core->blog->settings->mi_force === null) {		
	$res = require dirname(__FILE__).'/_install.php';
	
	# If installation failed, redirect to index.php
	if ($res !== true) {
		http::redirect('index.php');
	}
}

$settings = &$core->blog->settings;

$force = $settings->mi_force;
$min_width = $settings->mi_min_width;
$min_height = $settings->mi_min_height;
$max_width = $settings->mi_max_width;
$max_height = $settings->mi_max_height;

if (isset($_POST['act_save'])) {
	$force = !empty($_POST['force']);
	$min_width = (integer) $_POST['min_width'];
	$min_height = (integer) $_POST['min_height'];
	$max_width = (integer) $_POST['max_width'];
	$max_height = (integer) $_POST['max_height'];
}

if (isset($_POST['act_save'])) {
	try {
		if ($min_width > $max_width) {
			throw new Exception(__('Minimal width greater than maximal width.'));
		} elseif ($min_height > $max_height) {
			throw new Exception(__('Minimal height greater than maximal height.'));
		}
		
		$settings->setNameSpace(strtolower($label));
		$settings->put('mi_force',$force);
		$settings->put('mi_min_width',$min_width);
		$settings->put('mi_min_height',$min_height);
		$settings->put('mi_max_width',$max_width);
		$settings->put('mi_max_height',$max_height);
		
		http::redirect($p_url.'&up=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

$msg = '';

if (isset($_REQUEST['up'])) {
	$msg = __('Configuration successfully updated');
}

if (!empty($msg)) {
	$msg = '<p class="message">'.$msg.'</p>';
}

echo
'<html><head>
  <title>'.$p_name.'</title>
</head><body>
<h2>'.html::escapeHTML($core->blog->name).' &gt; '.html::escapeHTML($p_name).'</h2>
'.$msg.'
<h3>'.__('Usage').'</h3>
<p>'.
  __('In your post.html template, add {{tpl:MetaImage}} where you want to print the meta image.').
'</p>

<h3>'.__('Config').'</h3>
<form action="'.$p_url.'" method="post">
 
<p><label class="classic">'.form::checkbox('force',1,$force).' '.
  __('Posts must have an image').'</label></p>

<fieldset><legend>'.__('Images size').'</legend>
  <h3>'.__('Min size').'</h3>
  <p>'.__('Message, min size').'</p>
  <p>'.sprintf(__('%sWidth%s x %sheight%s:'),
    '<label for="min_width" class="classic">','</label>',
    '<label for="min_height" class="classic">','</label>').' '.
    form::field('min_width',4,4,$min_width).' x '.
    form::field('min_height',4,4,$min_height).'
  </p>
  
  <h3 style="padding-top: 1.5em;">'.__('Max size').'</h3>
  <p>'.__('Message, max size').'</p>
  <p>'.sprintf(__('%sWidth%s x %sheight%s:'),
    '<label for="max_width" class="classic">','</label>',
    '<label for="max_height" class="classic">','</label>').' '.
    form::field('max_width',4,4,$max_width).' x '.
    form::field('max_height',4,4,$max_height).'
  </p>
</fieldset>

<p><input type="submit" name="act_save" value="'.__('save').'" />'.
	$core->formNonce().'</p>
</form>
</body></html>
';
?>