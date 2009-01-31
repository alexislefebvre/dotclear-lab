<?php

$_menu['Blog']->addItem(__('blogUsers'),'plugin.php?p=usersManagement','index.php?pf=usersManagement/icon.png',
		preg_match('/plugin.php\?p=usersManagement(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('admin',$core->blog->id));
?>