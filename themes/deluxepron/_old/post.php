<div class="post post-page">
	
    <h2 id="p<?php dcPostID(); ?>" class="post-title"><a href="<?php dcPostURL(); ?>"><?php dcPostTitle(); ?></a></h2>

    <p class="post-info">par <span><?php dcPostAuthor(); ?></span> le <?php dcPostDate(); ?> dans <a href="<?php dcPostCatURL(); ?>"><?php dcPostCatTitle(); ?></a></p>
	
	<?php dcPostChapo('<div class="post-chapo">%s</div>'); ?>

	<div class="post-content"><?php dcPostContent(); ?></div>
	
</div>

<div id="trackbacks">
	
    <h3 id="tb"><?php dcPostNbTrackbacks('<span>0</span> trackbacks','<span>1</span> trackback','<span>%s</span> trackbacks'); ?></h3>

	<?php if (dcPostOpenTrackbacks() && dc_allow_trackbacks) : ?>
		<p class="tb-info">Pour faire un trackback sur ce billet&nbsp;:
		<?php echo dcPostTrackBackURI(); ?></p>
	<?php else: ?>
		<p class="tb-info">Les trackbacks pour ce billet sont ferm&eacute;s.</p>
	<?php endif; ?>
    
    <!-- <div id="trackback-content"> -->

    <?php if ($trackbacks->isEmpty()) : /* Message si aucune trackback */?>
		<p class="tb-info">Aucun trackback.</p>
    <?php else: ?>

        <?php while ($trackbacks->fetch()) : /* Liste des trackbacks */?>

        <div class="trackback">
            
            <blockquote>
            <?php dcTBContent(); ?>
            </blockquote>

            <p id="c<?php dcTBID(); ?>" class="comment-info"><span>Le <?php dcTBDate(); ?> &agrave; <?php dcTBTime(); ?>,</span> 
            de <?php dcTBAuthor(); ?> <a href="#c<?php dcTBID(); ?>">#</a></p>

         </div>

        <?php endwhile; ?>

    <?php endif; ?>

    <!-- </div> -->

</div>


<?php if ( ( dc_allow_comments ) || ( ! $comments->isEmpty() ) ) : ?>

<div id="comments">

	<h3 id="co"><?php dcPostNbComments('<span>0</span> commentaires','<span>1</span> commentaire','<span>%s</span> commentaires'); ?> </h3>

	<?php if ( $comments->isEmpty() ) : /* Si aucun commentaire */?>
		<p>Aucun commentaire.</p>
	<?php endif; ?>
	
	<?php $i=0; while ($comments->fetch()) : /* Boucle de commentaires */ ?>
        
        <?php

        $i++; if($i%2==1) { $class = 'comment commentA'; } else { $class = 'comment commentB'; }

        ?>

        <div class="<?php echo $class ?>">

            <div class="comment-content">
            <blockquote>
                <?php dcCommentContent(); /* Le corps du commentaire */  ?>
            </blockquote>
            </div>
        
            <p id="c<?php dcCommentID(); ?>" class="comment-info">
                <span class="date">Le <?php dcCommentDate(); ?> &agrave; <?php dcCommentTime(); ?>,</span>
                par <span class="author"><?php dcCommentAuthor(); ?></span>
                <a href="#c<?php dcCommentID(); ?>">#</a>
            </p>

        </div>
	
	<?php endwhile; ?>

</div>

<div id="comment-form">
	<?php if ( dcPostOpenComments() ) : /* Si les commentaires sont (encore) ouverts */ ?>
		<h3><span>Ajouter</span> un commentaire</h3>
		<?php include dirname(__FILE__).'/form.php'; ?>
	<?php else : /* Sinon */ ?>
		<p>Les commentaires pour ce billet sont ferm&eacute;s.</p>
	<?php endif; ?>
</div>

<?php endif; ?>