<?php
if (!defined('DC_CONTEXT_ADMIN')) { return; }
 
$m_version = $core->plugins->moduleInfo('MicroBlog', 'version');
 
$i_version = $core->getVersion('MicroBlog');

if (version_compare($i_version, $m_version, '>=')) {
	return;
}

// Creation de la BDD contenant les paramètres de connexions
$s = new dbStruct($core->con, $core->prefix);
 
$s->MB_services
	->id(     'char',     32, false, "00000000000000000000000000000000")
	->service('varchar', 255, false)
	->user(   'varchar', 255, false)
	->pwd(    'varchar', 255, false)
	->params( 'smallint',  0, false, 0)
	->primary('pk_id','id')
	->unique( 'uk_service','service','user');

$si = new dbStruct($core->con, $core->prefix);
$changes = $si->synchronize($s);

$core->setVersion('MicroBlog', $m_version);
return true;