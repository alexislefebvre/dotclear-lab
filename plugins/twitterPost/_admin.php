<?php
include_once(dirname(__FILE__) . '/inc/TwitterPost.class.php');
$core->addBehavior(
	'adminPostFormSidebar',
	array(
		'TwitterPost',
		'initPostFormSidebar'
	)
);

$core->addBehavior(
	'adminAfterPostCreate',
	array(
		'TwitterPost',
		'adminBeforePostUpdate'
	)
);
$core->addBehavior(
	'adminAfterPostUpdate',
	array(
		'TwitterPost',
		'adminBeforePostUpdate'
	)
);

$_menu['Plugins']->addItem(
	'Twitter Post',
	'plugin.php?p=twitterPost',
	'index.php?pf=twitterPost/img/icon_16.png',
	preg_match(
		'/plugin.php\?p=twitterPost(&.*)?$/',
		$_SERVER['REQUEST_URI']
	),
	$core->auth->check(
		'usage,contentadmin',
		$core->blog->id
	)
);
