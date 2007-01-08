<?php

/**
@function dcCatListWithNumbers
 
Cette fonction affiche une liste des categories avec les liens pour y
acceder, ainsi que le nombre de billets par categorie. La liste generee 
est une liste non ordonnee (<ul>), il est donc tres simple de la mettre 
en forme par la suite.
 
@param string  block Chaine de substitution pour pour la liste ('<ul>%s</ul>')
@param string  item  Chaine de substitution pour un element ('<li>%s</li>')
@param string  number  Chaine de substitution pour le nombre de billets ('&nbsp[%s]')
*/
 
function dcCatListWithNumbers($block='<ul>%s</ul>',$item='<li>%s</li>',$number='&nbsp;[%s]')
{
	global $rs_cat, $cat_id, $lang, $blog;
	
	$comp_url = '';
	
	if ($lang) {
		$comp_url = $lang.'/';
	}
	
	if (!$rs_cat->isEmpty())
	{
		$res = '';
		
		while (!$rs_cat->EOF())
		{
			if ($rs_cat->f('nb_post') > 0)
			{
               
				$id = $rs_cat->f('cat_libelle_url');
				$libelle = $rs_cat->f('cat_libelle');
				$nPost = $rs_cat->f('nb_post');

                if( $nPost >= 50 ) { $class = "weight-10"; }
                elseif( $nPost >= 20 ) { $class = "weight-8"; }
                elseif( $nPost >= 10 ) { $class = "weight-5"; }
                elseif( $nPost >= 5 ) { $class = "weight-3"; }
                elseif( $nPost >= 3 ) { $class = "weight-2"; }
                elseif( $nPost >= 1 ) { $class = "weight-1"; }
				
                // sprintf($number,$nPost) . 
				$lien = '<a rel="tag" class="'.$class.'" href="'.
				sprintf($blog->front_url['cat'],$comp_url.$id).
				'">'. $libelle.'</a>';
				
				if ($cat_id == $id) {
					$lien = '<strong>'.$lien.'</strong>';
				}
				
				$res .= sprintf($item,$lien);
			}
			$rs_cat->moveNext();
		}
		$rs_cat->moveStart();
		
		printf($block,$res);
	}
}



/*

Cette fonction affiche la liste des derniers commentaires sur le blog

code original :
- fonction dcLastComments       : Mega (http://www.mega-box.net/)
- fonction dcMore::lastComments : Olivier Meunier (http://www.dotclear.net/)

@proto function dcCommentsList
@param int limit Nombre de commentaires à afficher
@param int maxLength Nombre de caractères maximum pour l'extrait
@param string block Chaine de substitution pour pour la liste ('<ul>%s</ul>')
@param string item Chaine de substitution pour un élément ('<li><a href="%3$s">%1$s - %2$s : %4$s</a></li>')

Dans $item :
- %1$s sera remplacé par le titre du billet
- %2$s        ->         l'auteur
- %3$s        ->         le permalien vers le commentaire 
- %4$s        ->         extrait du commentaire coupé à $maxLength caractères

*/

function dcCommentsList($limit=5,$maxlength=50,$block='<ul>%s</ul>',$item='<li><a href="%3$s">%1$s - %2$s : %4$s</a></li>')
{
    global $blog;
    
    $strReq = 'SELECT comment_id, comment_dt, comment_auteur, comment_content, '.
            'P.post_id, P.post_titre, '.
            'DATE_FORMAT(P.post_dt,\'%d\') AS postday, '.
            'DATE_FORMAT(P.post_dt,\'%m\') AS postmonth, '.
            'DATE_FORMAT(P.post_dt,\'%Y\') AS postyear, '.
            'DATE_FORMAT(comment_dt,\'%Y%m%d\') AS comment_date '.
            'FROM '.$blog->t_comment.' C, '.$blog->t_post.' P '.
            'WHERE C.post_id = P.post_id '.
            'AND comment_pub = 1 '.
            'AND comment_trackback <> 1 '.
            'ORDER BY comment_dt DESC '.
            'LIMIT 0,'.$limit.' ';
    
    $rs = $blog->con->select($strReq,$blog->rs_blogcomment);
    $rs->setBlog($blog);
    
    $res = '';
    
    $i=0;
    while ($rs->fetch())
    {
        $i++;
        if($i%2==1) { $class = 'commentA'; } else { $class = 'commentB'; }
        $extract	= strip_tags( $rs->f('comment_content') );

        if (strlen($extract) > $maxlength) {
            $extract = substr($extract,0,$maxlength) . "&nbsp;...";
        }

        $titre = $rs->f('post_titre');
        $auteur = $rs->f('comment_auteur');
        $url = $rs->getPermURL();
        
        $res .= sprintf($item,$titre,$auteur,$url,$extract,$class);
    }
    
    printf($block,$res);
}

# Ajout des fichiers du template pour le cache
$mod_files[] = dirname(__FILE__).'/list.php';
$mod_files[] = dirname(__FILE__).'/post.php';
$mod_files[] = dirname(__FILE__).'/form.php';

?>