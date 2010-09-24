<?php if (!defined('DC_CONTEXT_ADMIN')) {return;}
/**
 * @author kévin lepeltier [lipki] (kevin@lepeltier.info)
 * @license http://creativecommons.org/licenses/by-sa/3.0/deed.fr
 */
 
$media = (integer) !empty($_GET['media']);

if( $media )
	require dirname(__FILE__).'/media.php';
else  require dirname(__FILE__).'/form.php';