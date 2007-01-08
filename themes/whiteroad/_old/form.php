<form action="<?php dcPostUrl(); ?>" method="post">

<fieldset>
	<?php dcCommentFormError('<div class="error"><strong>Erreurs :</strong><br /> %s</div>'); ?>
	<p class="field"><label for="c_nom">Nom ou pseudo&nbsp;:</label>
	<input name="c_nom" id="c_nom" type="text" size="30" maxlength="255"
	value="<?php dcCommentFormValue('c_nom'); ?>" />
	</p>

	<p class="field"><label for="c_mail">Email (facultatif)&nbsp;:</label>
	<input name="c_mail" id="c_mail" type="text" size="30" maxlength="255"
	value="<?php dcCommentFormValue('c_mail'); ?>" />
	</p>

	<p class="field"><label for="c_site">Site Web (facultatif)&nbsp;:</label>
	<input name="c_site" id="c_site" type="text" size="30" maxlength="255"
	value="<?php dcCommentFormValue('c_site'); ?>" />
	</p>
	
	<p class="field"><label for="c_content">Commentaire&nbsp;:</label>
	<textarea name="c_content" id="c_content" cols="35" rows="7"><?php
	dcCommentFormValue('c_content');
	?></textarea>
	</p>
</fieldset>


<p class="form-help">Le code HTML dans le commentaire sera affich&eacute; comme du texte,
les adresses internet seront converties automatiquement.</p>

<fieldset>	
	<p><input type="checkbox" id="c_remember" name="c_remember" />
	<label class="inline" for="c_remember">Se souvenir de mes informations</label>
	</p>
	<p><input type="submit" class="preview" name="preview" value="pr&eacute;visualiser" />
	<input type="submit" class="submit" value="envoyer" />
	<input type="hidden" name="redir" value="<?php dcCommentFormRedir(); ?>" /></p>
</fieldset>

</form>