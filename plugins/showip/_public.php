<?php $core->tpl->addValue('UserIP',array('publicShowip','tplUserIP')); class publicShowip { public static function tplUserIP() { return '<?php echo $_SERVER[\'REMOTE_ADDR\']; ?>'; } } ?>
