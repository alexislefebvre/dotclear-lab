<?php
/**
 * @author Nicolas Frandeboeuf <nicofrand@gmail.com>
 * @version 1.0.1
 * @package feed2img
 */

if (!defined("DC_CONTEXT_ADMIN"))
    return;

require_once(dirname(__FILE__) . "/inc/class.feed2img.php");
$core->addBehavior("adminAfterPostCreate", array("EntriesSelection", "buildImage"));
$core->addBehavior("adminAfterPostUpdate", array("EntriesSelection", "buildImage"));
$core->addBehavior("adminBeforePostDelete", array("EntriesSelection", "buildImage"));
?>