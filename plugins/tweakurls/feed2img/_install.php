<?php
if (!defined('DC_CONTEXT_ADMIN')) { return; }
$public_dir = path::real(path::fullFromRoot($core->blog->settings->public_path,DC_ROOT),false);
if(!is_file($public_dir.'/feed2img_output.png')){
    require_once(dirname(__FILE__).'/inc/feed2img.class.php');
    $entries_selection = new EntriesSelection;
    $entries_selection->ImgOutput('');
}
?>