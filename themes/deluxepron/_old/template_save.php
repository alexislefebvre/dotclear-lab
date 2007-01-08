<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php dcInfo('lang'); ?>" lang="<?php dcInfo('lang'); ?>">
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="text/html; charset=<?php dcInfo('encoding'); ?>" />
<link rel="alternate" type="application/rss+xml" title="RSS" href="<?php dcInfo('rss'); ?>" />
<link rel="alternate" type="application/xml" title="Atom" href="<?php dcInfo('atom'); ?>" />
<title><?php dcSinglePostTitle('%s - '); dcSingleCatTitle('%s - '); dcSingleMonthTitle('%s - '); dcInfo(); ?></title>
<link rel="shortcut icon" href="<?php dcInfo('theme'); ?>/img/favicon.ico" />
<link rel="stylesheet" type="text/css" href="<?php dcInfo('theme'); ?>/style.css" media="screen" />

<?php dcPostTrackbackAutoDiscovery(); ?>

<script src="<?php dcInfo('theme'); ?>/sifr.js" type="text/javascript"></script>
<script src="<?php dcInfo('theme'); ?>/sifr-addons.js" type="text/javascript"></script>

</head>

<body>


<div id="page">

	<div id="top">
		<h1><a href="<?php dcInfo('url'); ?>"><?php dcInfo(); ?></a></h1>
    <div id="subtitle">“The Flower mafia série”</div>
	</div>

	<p id="prelude">
    	<a href="#main">Aller au contenu</a>
    -
    <a href="#sidebar">Aller au menu</a>
    -
    <a href="#search">Aller &agrave; la recherche</a>
	</p>

	<div id="main">
<div id="fsidebar">

    <div id="content">
				
	<?php if ($err_msg != '') : ?>

		<div class="error"><p><strong>Erreur : </strong>
		<?php echo $err_msg; ?></p></div>
	
	<?php elseif ($preview) : ?>
		
        <div id="comments" class="first-post">
			
        <h3>Commentaire pour <?php dcPostTitle(); ?></h3>

        <div id="comment-preview">
			<blockquote>
			<?php dcCommentPreview(); ?>
			</blockquote>
		</div>

        </div>
		
        <div id="comment-form">
			
		<h3>Changer le commentaire</h3>
		<?php include dirname(__FILE__).'/form.php'; ?>

        </div>
		
	<?php elseif ($mode != 'post') : ?>
		
        <?php
		dcSearchString('<div id="search-result"><p>R&eacute;sultats de votre recherche de <strong>%s</strong>.</p></div>');
		?>
		
		<?php include dirname(__FILE__).'/list.php'; ?>
		
	<?php else : ?>

		<?php include dirname(__FILE__).'/post.php'; ?>

	<?php endif; ?>
	
	</div> <!--/#content  -->

    <div id="sidebar">
    	<img src="<?php dcInfo('theme'); ?>/img/topside.gif" />
        <div id="calendar">
            <!--<h2>Calendrier</h2>-->
			<div id="decale_cal">
            <?php dcCalendar('<table summary="Calendrier">%s</table>'); ?>
			</div>
        </div>
		
        <div id="search">
            <form action="<?php dcInfo('search'); ?>" method="get">
                <h2><label for="q">Rechercher</label></h2>
                <p class="field"><input name="q" id="q" type="text" size="10"
                value="<?php dcSearchString(); ?>" accesskey="4" />
                <input type="submit" id="submit" class="submit" value="ok &#9658;" /></p>
            </form>
        </div>
		
		<div class="separe">&nbsp;</div>
		
        <?php if (function_exists('dcCatListWithNumbers')) : ?>

        <div id="categories">
            <h2>Catégories</h2>
            <?php dcCatListWithNumbers('<ul>%s</ul>', '<li>%s</li>', '%s '); ?>
        </div>
		
		

        <?php endif; ?>
        

        
        <?php if ($mode != 'post') : ?>
            
            <?php if (function_exists('dcCommentsList')) : ?>
            <div class="separe">&nbsp;</div>
            <div id="lastcomments">
                <h2>Commentaires</h2>
                <?php dcCommentsList(6,70,'<ul>%s</ul>','<li class="%5$s"><p class="comment-infos"><span class="comment-author"><a href="%3$s">%2$s</a> sur</span><br />%1$s</p></li>'); ?>
            </div>
			<div class="separe">&nbsp;</div>
            <?php endif; ?>
            
			
			
            <div id="links">
                <h2>Blogoliste</h2>
                <?php dcBlogroll::linkList('<h4>&gt;&gt;&nbsp;%s</h4>'); ?>
            </div>
		
        <?php endif; ?>
			<div class="separe">&nbsp;</div>
			
			
        <div id="archives">
            <h2>Archives</h2>
            <?php dcMonthsList(); ?>
        </div>
        
			<div class="separe">&nbsp;</div>
		<img src="<?php dcInfo('theme'); ?>/img/bottomside.gif" />
    </div> <!--/#sidebar  -->
	
	<p style="clear:both;">&nbsp;</p>


</div> <!-- /#fsidebar  -->



	</div> <!-- /#main  -->



</div> <!-- /#page -->

	<div id="footer">
		<!--<img src="<?php dcInfo('theme'); ?>/photo.jpg" id="moi" />-->
		<div id="footer_contenu">
			<!--<span id="letitre"><?php dcInfo(); ?></span>-->
	
	Copyright &copy; 1995-2005 <?php dcPostAuthor(); ?>
	<a href="<?php dcInfo('rss'); ?>">RSS</a> |
	<a href="<?php dcInfo('rss'); ?>?type=co">RSS Commentaires</a> |
	<a href="<?php dcInfo('atom'); ?>">ATOM</a> |
	<a href="<?php dcInfo('atom'); ?>?type=co">ATOM Commentaires</a> 
	<!--<br  />-->
	Propulsé par <a href="http://www.dotclear.net/">Dotclear</a>,
	habillé par <a href="http://www.upian.com/">Upian</a>
		</div>
	</div>

<script type="text/javascript">
	//<![CDATA[
/* Replacement calls. Please see documentation for more information. */

if(typeof sIFR == "function"){
	
	sIFR.replaceElement(named({sSelector:"#search h2", sFlashSrc:"<?php dcInfo('theme'); ?>/bikram.swf", sColor:"#211B1B", nPaddingTop:0, nPaddingBottom:0, sWmode:"transparent"}));
	
	sIFR.replaceElement(named({sSelector:"#categories h2", sFlashSrc:"<?php dcInfo('theme'); ?>/bikram.swf", sColor:"#211B1B", nPaddingTop:0, nPaddingBottom:0, sWmode:"transparent"}));
	
	sIFR.replaceElement(named({sSelector:"#lastcomments h2", sFlashSrc:"<?php dcInfo('theme'); ?>/bikram.swf", sColor:"#211B1B", nPaddingTop:0, nPaddingBottom:0, sWmode:"transparent"}));
	
	sIFR.replaceElement(named({sSelector:"#links h2", sFlashSrc:"<?php dcInfo('theme'); ?>/bikram.swf", sColor:"#211B1B", nPaddingTop:0, nPaddingBottom:0, sWmode:"transparent"}));
	
	sIFR.replaceElement(named({sSelector:"#archives h2", sFlashSrc:"<?php dcInfo('theme'); ?>/bikram.swf", sColor:"#211B1B", nPaddingTop:0, nPaddingBottom:0, sWmode:"transparent"}));
	

};

//]]>
</script>
</body>
</html>

<?php
if (function_exists('bbclone::counter')) {
    require( bbclone::counter() );
}
?>

<?php
if (function_exists('dcThemesForm')) {
    dcThemesForm();
}
?>