<?php

$_menu['Blog']->addItem(__('blogUsers'),'plugin.php?p=gestionUtilisateurs','index.php?pf=gestionUtilisateurs/icon.png',
		preg_match('/plugin.php\?p=gestionUtilisateurs(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('admin',$core->blog->id));
?>