<?php

$core->url->register('category','category','^category/(.+)$',array('specificsTemplatesURLHandlers','category')); // inc/prepend.php
$core->url->register('pages','pages','^pages/(.+)$',array('specificsTemplatesURLHandlers','pages')); // /plugins/pages/_prepend.php

?>