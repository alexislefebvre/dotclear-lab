<?php
# This file is in public domain.
if (!defined('DC_RC_PATH')) { return; }

$core->tpl->addValue('UserIP',array('publicShowip','tplUserIP'));

class publicShowip
{
	public static function tplUserIP()
	{
		return '<?php echo $_SERVER[\'REMOTE_ADDR\']; ?>';
	}
}
?>