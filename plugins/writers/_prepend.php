<?php
# Super admins don't need this extension
if ($GLOBALS['core']->auth->isSuperAdmin()) {
	return;
}
?>