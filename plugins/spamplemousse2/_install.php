<?php

$label = 'spamplemousse2';

# On lit la version du plugin
$m_version = $core->plugins->moduleInfo($label,'version');
 
# On lit la version du plugin dans la table des versions
$i_version = $core->getVersion($label);
 
# La version dans la table est supérieure ou égale à
# celle du module, on ne fait rien puisque celui-ci
# est installé
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

$s = new dbStruct($core->con,$core->prefix);

# création de la table spam_token
$s->spam_token
	->token_id('varchar',255,false,0)
	->token_nham('integer',0,false,0)
	->token_nspam('integer',0,false,0)
	->token_mdate('timestamp',0,false,'now()')
	->token_p('float', 0, false,0)
	->token_mature('smallint', 0, false,0)
	->primary('pk_spam_token','token_id')
	;
	
# ajout d'une colonne sur la table comment
$s->comment
	->comment_bayes('smallint',0,false,0)
	;

$si = new dbStruct($core->con,$core->prefix);
$si->synchronize($s); 

$core->setVersion($label,$m_version);	
return true;		
?>
