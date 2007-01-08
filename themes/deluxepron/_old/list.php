<!-- Boucle sur la liste de billets -->

<?php $i=0; while ($news->fetch()) : ?>
    
    <?php
    
    $i++;

    if($i==1) { $class = 'post postA first-post'; } elseif($i%2==1) { $class = 'post postA'; } else { $class = 'post postB'; }

    ?>

	<div class="<?php echo $class ?>">
		
        <h2 id="p<?php dcPostID(); ?>" class="post-title"><a href="<?php dcPostURL(); ?>"><?php dcPostTitle(); ?></a></h2>
		
        <p class="post-info">par <span><?php dcPostAuthor(); ?></span> le <?php dcPostDate(); ?> dans <a href="<?php dcPostCatURL(); ?>"><?php dcPostCatTitle(); ?></a></p>

		<div class="post-content" <?php dcPostLang(); ?>>
			<?php dcPostAbstract('%s'); ?>
		</div>

        <p class="post-comment">
		
            <a href="<?php dcPostURL(); ?>#co"><?php dcPostNbComments('<span>0</span> commentaire','<span>1</span> commentaire','<span>%s</span> commentaires'); ?></a>
		    <a href="<?php dcPostURL(); ?>#tb"><?php dcPostNbTrackbacks('<span>0</span> trackback','<span>1</span> trackback','<span>%s</span> trackbacks'); ?></a>

		</p>

	</div>

<?php endwhile; ?>