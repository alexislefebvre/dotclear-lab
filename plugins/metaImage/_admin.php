<?php
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(__('Meta Image'),'plugin.php?p=metaImage',
	'index.php?pf=metaImage/icon.png',
	preg_match('/plugin.php\?p=metaImage(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->isSuperAdmin()
);

$core->addBehavior('adminPostFormSidebar',array('metaImageBehaviors','adminPostFormSidebar'));

$core->addBehavior('adminAfterPostCreate',array('metaImageBehaviors','setImage'));
$core->addBehavior('adminAfterPostUpdate',array('metaImageBehaviors','setImage'));
$core->addBehavior('adminBeforePostDelete',array('metaImageBehaviors','removeImage'));

class metaImageBehaviors
{
	public static function adminPostFormSidebar(&$post)
	{
		global $core;
		
		$bMustHaveImage = $core->blog->settings->mi_force;
		$min_width  = $core->blog->settings->mi_min_width;
		$min_height = $core->blog->settings->mi_min_height;
		
		# Tentative de récupération de l'image associée
		$objMeta = new dcMeta($core);
		$imageName = $post
			? $objMeta->getMetaStr($post->post_meta,'image')
			: '';
		
		# Initialise la variable à "true" si une image associée à été trouvée
		$imageAttached = !empty($imageName);
		
		$width  = 0;
		$height = 0;
		if ($imageAttached) {
			# Calcul des dimensions de l'image pour les écrire dans le code HTML
			$imgPath = $GLOBALS['core']->blog->public_path."/illustration-articles/".$imageName;      
			if (file_exists($imgPath)) {
				list($width, $height) = getimagesize($imgPath);
			}
			$imageSrc = $GLOBALS['core']->blog->settings->public_url."/illustration-articles/".$imageName;
		}

		$required = $bMustHaveImage ? 'required' : '';

		# Ajout du formulaire d'upload
		?>
		<h3><label class="<?php echo $required?>" for="upfileimage"><?php echo __('Image')?></label></h3>
		<?php if ($imageAttached) { ?>
		<img src="<?php echo $imageSrc?>" width="<?php echo $width?>" height="<?php echo $height?>" />
		<?php } else { ?>
		<?php } ?>
		<script type="text/javascript">
		formulaire = document.getElementById("entry-form");
		if (formulaire) {
		formulaire.setAttribute("enctype","multipart/form-data");
		formulaire.setAttribute("encoding","multipart/form-data");
		}
		</script>
		<div class="p" id="meta-edit-image">
		<label for="upfileimage"><?php echo __('Choose an image:')?></label>
		<input type="file" id="upfileimage" name="upfileimage" size="22" />
		</div>
		<?php
		if ($min_width > 0 || $min_height > 0) 
		{
			echo '<p class="form-note warn">';
			if ($min_width > 0) {
				echo sprintf (__('min width is %d pixels'), $min_width);
			}          
			if ($min_height > 0) {
				if ($min_width > 0) {
					echo '<br />';
				}
				echo sprintf (__('min height is %d pixels'), $min_height);
			} 
			echo '</p>';
		}
	}

	public static function removeImage ($post_id)
	{
		$core = $GLOBALS['core'];
		$objMeta = new dcMeta($core);    
		$post = $core->blog->getPosts(array('post_id' => $post_id));
		$oldImageName = ($post) ? $objMeta->getMetaStr($post->post_meta,'image') : '';
		$haveImage = !empty($oldImageName);

		# S'il y avait précédement une image, tenter de la supprimer
		if ($haveImage) {
			try {
				$core->media = new dcMedia($GLOBALS['core'], 'image');
				$core->media->chdir("illustration-articles");
				$core->media->removeFile($oldImageName);
			} catch (Exception $e) {;}
		}
	}

	public static function setImage(&$cur,&$post_id)
	{
		$upfileimage = null;
		$core = $GLOBALS['core'];

		$bMustHaveImage = $core->blog->settings->mi_force;

		$objMeta = new dcMeta($core);    
		$post = $core->blog->getPosts(array('post_id' => $post_id));
		$oldImageName = ($post) ? $objMeta->getMetaStr($post->post_meta,'image') : '';
		$haveImage = !empty($oldImageName);

		# S'il y a un fichier à associer au billet
		if (!empty($_FILES['upfileimage']) && (strlen($_FILES['upfileimage']['name']) > 0))
		{
			# Préparation de la structure pour l'upload de l'image
			$imageName = 'tmp-article'.$post_id.'-'.$_FILES['upfileimage']['name'];
			$upfileimage = array(
			'name' => $imageName,
			'type' => $_FILES['upfileimage']['type'],
			'tmp_name' => $_FILES['upfileimage']['tmp_name'],
			'error' => $_FILES['upfileimage']['error'],
			'size' => $_FILES['upfileimage']['size'],

			'title' => __('metaimage-title').$imageName,
			'private' => true
			);

			# Vérif du format de l'image
			if ($upfileimage['type'] != 'image/gif'
			&& $upfileimage['type'] != 'image/jpeg'
			&& $upfileimage['type'] != 'image/png'
			&& $upfileimage['type'] != 'image/pjpeg'
			&& $upfileimage['type'] != 'image/pjpg') {
				throw new Exception ($upfileimage['type'].__('error-bad-file'));
			}

			# Upload de l'image via le gestionnaire de média
			$core->media = new dcMedia($GLOBALS['core'], 'image');
			$core->media->makeDir("illustration-articles");
			$core->media->chdir("illustration-articles");
			files::uploadStatus($upfileimage);
			$file_id = $core->media->uploadFile($upfileimage['tmp_name'],$upfileimage['name'],$upfileimage['title'],$upfileimage['private']);
			$file = $core->media->getFile($file_id);      
			$imgPath = $core->blog->public_path."/illustration-articles/".$imageName;              

			# Chargement en mémoire de l'image
			$imgTool = new imageTools();
			try {
				$imgTool->loadImage($imgPath);
			}
			catch (Exception $e) {
				throw new Exception (__('error-missed-upload'));
			}

			# définintion des largeurs et hauteurs maximales
			# FIXME : à rendre paramétrable
			$min_width  = $core->blog->settings->mi_min_width;
			$min_height = $core->blog->settings->mi_min_height;
			$max_width  = $core->blog->settings->mi_max_width;
			$max_height = $core->blog->settings->mi_max_height;

			# Récupération des dimensions originales
			$width  = $imgTool->getW();
			$height = $imgTool->getH();

			$err_msg = '';
			if ($width < $min_width) {
				throw new Exception(sprintf (__('Warning: Image must have a min width of %d pixels'), $min_width));
			}
			
			if ($width < $min_width || $height < $min_height) {
				if ($width < $min_width) {
					echo sprintf (__('Image must have a min width of %d pixels'), $min_width);
				}

				if ($height < $min_height)
				if ($width < $min_width) {
					echo '<br />';
				}
				echo sprintf (__('Image must have a min height of %d pixels'), $min_height);

				throw new Exception($err_msg);
			}

			# Calcul des ratios
			$x_ratio = $max_width / $width;
			$y_ratio = $max_height / $height;

			# Calcul des nouvelles dimentions
			if (($width <= $max_width) && ($height <= $max_height)) {
				$tn_width = $width;
				$tn_height = $height;
			}
			elseif (($x_ratio * $height) < $max_height) {
				$tn_height = ceil($x_ratio * $height);
				$tn_width = $max_width;
			}
			else {
				$tn_width = ceil($y_ratio * $width);
				$tn_height = $max_height;
			} 

			# Redimentionnement de l'image
			$imgTool->resize($tn_width, $tn_height);

			# S'il y avait précédement une image, tenter de la supprimer
			if ($haveImage) {
				try {
					$core->media->removeFile($oldImageName);
				}
				catch (Exception $e)
				{;}
			}

			# Enregistrement de la nouvelle image dans un format dépendant du format d'origine
			switch($upfileimage['type']) {
				case 'image/gif':
				case 'image/png':
				$ext = 'png'; 
				break;
				default:
				$ext = 'jpg';
			}
			$newImageName = 'billet'.$post_id.'.'.$ext;
			$newImagePathName = $core->blog->public_path."/illustration-articles/".$newImageName;
			$imgTool->output($ext, $newImagePathName);

			# Effacement de l'image temporaire ET ajout de l'image redimentionnée dans le gestionnaire de media
			$core->media->removeFile($imageName);
			$core->media->createFile($newImageName, __('metaimage-title').$post_id, true);

			# Ajout de l'info dans 'post_meta' pour associer l'image au billet
			$post_id = (integer) $post_id;
			$objMeta = new dcMeta($core);
			$objMeta->delPostMeta($post_id,'image');
			$objMeta->setPostMeta($post_id,'image', $newImageName);
		}
		else {      
			# Si aucune image n'est associée à ce billet et qu'une image associée est requise, refuse la validation
			if (!$haveImage && $bMustHaveImage) {
				throw new Exception (sprintf (__('error-missed-image'), __('Image:')));
			}
		}
	}
}
?>