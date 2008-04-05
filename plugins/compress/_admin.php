<?php
$_menu['Plugins']->addItem(__('CompreSS'),'plugin.php?p=compress',
	'index.php?pf=compress/icon.png',preg_match('/plugin.php\?p=compress(&.*)?$/',
		$_SERVER['REQUEST_URI']),$core->auth->check('admin',$core->blog->id));
?>