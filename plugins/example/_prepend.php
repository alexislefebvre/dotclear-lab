<?php
/***** BEGIN LICENSE BLOCK *****
This program is free software. It comes without any warranty, to
the extent permitted by applicable law. You can redistribute it
and/or modify it under the terms of the Do What The Fuck You Want
To Public License, Version 2, as published by Sam Hocevar. See
http://sam.zoy.org/wtfpl/COPYING for more details.
	
Icon (icon.png) is from Silk Icons :
	http://www.famfamfam.com/lab/icons/silk/

***** END LICENSE BLOCK *****/

if (!defined('DC_RC_PATH')) {return;}

# load the example class
$__autoload['example'] = dirname(__FILE__).'/lib.example.php';

# register the "example" URL
$core->url->register('example','example',
	'^example(?:/(.+))?$',array('exampleDocument','page'));

# load the widget, in administration and on the blog
require_once(dirname(__FILE__).'/_widget.php');

?>