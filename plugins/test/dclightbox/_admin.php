<?php
// Fichier du plguin modifié le 28 janvier 2008 par Oleksandr Syenchuk
// (ce n'est pas une copie originale de dcLightbox)

# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

$_menu['Plugins']->addItem('dclightbox','plugin.php?p=dclightbox','index.php?pf=dclightbox/icon.png',preg_match('/plugin.php\?p=dclightbox(&.*)?$/',$_SERVER['REQUEST_URI']),$core->auth->check('usage,contentadmin',$core->blog->id));

$GLOBALS['core']->addBehavior('coreInitWikiPost',array('dcLightbox','initWiki2Lightbox'));
$GLOBALS['core']->addBehavior('adminPostHeaders',array('dcLightbox','patchButtons'));

class dcLightbox 
{
	public static function initWiki2Lightbox(&$wiki2xhtml) 
	{
		$wiki2xhtml->registerFunction('url:lbox',array('dcLightbox','wiki2Lightbox'));
	}


	public static function wiki2Lightbox($url, $content)
	{
	
		#parsing de $url qui sera de la forme :
		#lbox:url ou lbox:groupe:url
		$url = explode(':', $url, 3);

		if( isset($url[2]) )
		{
			#si de la forme lbox:group:url
			$res['url'] = $url[2];
			$rel='lightbox['.$url[1].']';
		}
		else
		{
			#si de la forme lbox:url
			$res['url'] = $url[1];
			$rel='lightbox';
		}

		if(!$GLOBALS['core']->blog->settings->enable_html_filter || (extension_loaded('tidy') && class_exists('tidy')) )
			$res['lang'] = '" rel="'.$rel; #passer par l'attribut hreflang - inconvénient empêche de paramétrer cet attribut
		else
			$res['url'] .= '" rel="'.$rel; #passer par l'attribut href - pas d'inconvénient connu

		return $res;
	}


	public static function patchButtons()
	{
		$admin_path = dirname($_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']);
		$res = '<script type="text/javascript">'."\n".
		"//<![CDATA[\n";


		# Bouton "lien" 
		# Vérifier si le fichier de remplacement existe avant sinon plus rien ne fonctionne et on me tape dessus
		if (file_exists($admin_path.'/dclb.popup_link.php'))
		{
			$res .= "jsToolBar.prototype.elements.link.open_url='dclb.popup_link.php';\n";

			$res .= "jsToolBar.prototype.elements.link.fncall.wiki = function() {
						var data = this.elements.link.data;

						if (data.href ==  '') return;

						if (data.lbox) {
							if (data.gname !== '') data.gname += ':';
							data.href = 'lbox:'+data.gname+data.href ;
						}

						var etag = '|'+data.href;
						if (data.hreflang)  etag += '|'+data.hreflang;

						if (data.title) {
							if (!data.hreflang)  etag += '|';
							etag += '|'+data.title;
						}

						this.encloseSelection('[',etag+']');
					};\n";

			$res .= "jsToolBar.prototype.elements.link.fncall.xhtml = function() {
						var data = this.elements.link.data;

						if (data.href == '') return;

						var stag = '<a href=\"'+data.href+'\"';

						if (data.hreflang) stag += ' hreflang=\"'+data.hreflang+'\"';
						if (data.title) stag += ' title=\"'+data.title+'\"';
						if (data.gname !== '') data.gname = '['+data.gname+']';
						if (data.lbox) stag += ' rel=\"ligthbox'+data.gname+'\"';
						stag += '>';

						this.encloseSelection(stag,'</a>');
					};\n";
			
			$res .= "jsToolBar.prototype.elements.link.fn.wysiwyg = function() {
						var href, title, hreflang, rel;
						href = title = hreflang = rel = '';
						hreflang = this.elements.link.default_hreflang;

						var a = this.getAncestor();

						if (a.tagName == 'a') {
							href= a.tag.href || '';
							title = a.tag.title || '';
							hreflang = a.tag.hreflang || '';
							rel = a.tag.rel || '';
						}

					this.elements.link.popup.call(this,'?href='+href+'&hreflang='+hreflang+'&title='+title+'&rel='+rel);
					};\n";

			$res .= "jsToolBar.prototype.elements.link.fncall.wysiwyg = function() {
						var data = this.elements.link.data;

						var a = this.getAncestor();

						if (a.tagName == 'a') {
							if (data.href == '') {
								// Remove link
								this.replaceNodeByContent(a.tag);
								this.iwin.focus();
								return;
							} else {
								// Update link
								a.tag.href = data.href;

								if (data.hreflang) a.tag.setAttribute('hreflang',data.hreflang);
								else a.tag.removeAttribute('hreflang');

								if (data.title) a.tag.setAttribute('title',data.title);
								else a.tag.removeAttribute('title');

								if (data.lbox) {
									if (data.gname !== '') data.gname = '['+data.gname+']';
									a.tag.setAttribute('rel','lightbox'+data.gname);
								} else {
									a.tag.removeAttribute('rel');
								}
								return;
							}
						}

						// Create link
						var n = this.getSelectedNode();
						var a = this.iwin.document.createElement('a');
						a.href = data.href;
						if (data.hreflang) a.setAttribute('hreflang',data.hreflang);
						if (data.title) a.setAttribute('title',data.title);
						if (data.gname !== '') data.gname = '['+data.gname+']';
						if (data.lbox) a.setAttribute('rel','lightbox'+data.gname);
						a.appendChild(n);
						this.insertNode(a);
					};\n";
		}


		# Bouton "image interne"
		# Vérifier si le fichier de remplacement existe avant sinon plus rien ne fonctionne et on me tape dessus
		if (file_exists($admin_path.'/dclb.media.php') && file_exists($admin_path.'/dclb.media_item.php'))
		{
			$res .= "jsToolBar.prototype.elements.img_select.open_url='dclb.media.php?type=image&popup=1';\n";
			$res .= "jsToolBar.prototype.elements.img_select.fncall.wiki = function() {
						var d = this.elements.img_select.data;
						if (d.src == undefined) return;

						this.encloseSelection('','',function(str) {
							var alt = (str) ? str : d.title;
							var res = '(('+d.src+'|'+alt;

							if (d.alignment == 'left') res += '|L';
							else if (d.alignment == 'right') res += '|R';
							else if (d.alignment == 'center') res += '|C';

							res += '))';

							if (d.link) {
								if (d.lbox)
								{
									if (d.gname !== '') d.gname += ':';
									d.url = 'lbox:'+d.gname+d.url ;
								}
								res = '['+res+'|'+d.url+'||'+d.title+']';
							}

							return res;
						});
					};\n";

			$res .= "jsToolBar.prototype.elements.img_select.fncall.xhtml = function() {
						var d = this.elements.img_select.data;
						if (d.src == undefined) return;

						this.encloseSelection('','',function(str) {
							var alt = (str) ? str : d.title;
							var res = '<img src=\"'+d.src+'\" alt=\"'+alt+'\"';

							if (d.alignment == 'left') res += ' style=\"float: left; margin: 0 1em 1em 0;\"';
							else if (d.alignment == 'right') res += ' style=\"float: right; margin: 0 0 1em 1em;\"';
							else if (d.alignment == 'center') res += ' style=\"margin: 0 auto; display: block;\"';

							res += ' />';

							if (d.link) {
								if (d.title) d.title=' title=\"'+d.title+'\"' ;
								if (d.gname !== '') d.gname = '['+d.gname+']' ;
								if (d.lbox) d.lbox=' rel=\"lightbox'+d.gname+'\"' ;
								res = '<a href=\"'+d.url+'\"'+d.title+d.lbox+'>'+res+'</a>';
							}

							return res;
						});
					};\n";

			$res .= "jsToolBar.prototype.elements.img_select.fncall.wysiwyg = function() {
						var d = this.elements.img_select.data;
						if (d.src == undefined) return;

						var img = this.iwin.document.createElement('img');
						img.src = d.src;
						img.setAttribute('alt',this.getSelectedText());

						if (d.alignment == 'left') {

							if (img.style.styleFloat != undefined) img.style.styleFloat = 'left';
							else img.style.cssFloat = 'left';

							img.style.marginTop = 0;
							img.style.marginRight = '1em';
							img.style.marginBottom = '1em';
							img.style.marginLeft = 0;

						} else if (d.alignment == 'right') {

							if (img.style.styleFloat != undefined) img.style.styleFloat = 'right';
							else img.style.cssFloat = 'right';

							img.style.marginTop = 0;
							img.style.marginRight = 0;
							img.style.marginBottom = '1em';
							img.style.marginLeft = '1em';

						} else if (d.alignment == 'center') {

							img.style.marginTop = 0;
							img.style.marginRight = 'auto';
							img.style.marginBottom = 0;
							img.style.marginLeft = 'auto';
							img.style.display = 'block';

						}
		
						if (d.link) {
							var a = this.iwin.document.createElement('a');
							a.href = d.url;
							if (d.title) a.setAttribute('title',d.title);
							if (d.gname !== '') d.gname = '['+d.gname+']';
							if (d.lbox) a.setAttribute('rel','lightbox'+d.gname);
							a.appendChild(img);
							this.insertNode(a);
						} else {
							this.insertNode(img);
						}
					};\n";
		}


		$res .= "</script>\n";

		return $res;
	}
}
?>
