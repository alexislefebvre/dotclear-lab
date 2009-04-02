<?php

$_menu['Plugins']->addItem(__('Specifics Templates'),'plugin.php?p=specifics_templates','index.php?pf=specifics_templates/bricks.png',
		preg_match('/plugin.php\?p=specifics_templates(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('usage',$core->blog->id));
		
?>