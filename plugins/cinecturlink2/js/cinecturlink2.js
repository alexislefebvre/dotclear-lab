/* -- BEGIN LICENSE BLOCK ----------------------------------
 * This file is part of cinecturlink2, a plugin for Dotclear 2.
 * 
 * Copyright (c) 2009-2010 JC Denis and contributors
 * jcdenis@gdwd.com
 * 
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -- END LICENSE BLOCK ------------------------------------*/

;if(window.jQuery) (function($){
	$.fn.openGoogle = function(lang,target){
		return this.each(function(){
			$(this).click(function(){
				var val = $(target).attr('value');
				if (val!=''){
					searchwindow=window.open('http://www.google.com/search?hl='+lang+'&q='+val,'search','scrollbars=yes,width=800,height=600,resize=yes,toolbar=yes,menubar=yes');
					searchwindow.focus();
				}
				return false;
			});
		});
	}
	$.fn.openAmazon = function(lang,target){
		return this.each(function(){
			$(this).click(function(){
				var val = $(target).attr('value');
				if (val!=''){
					searchwindow=window.open('http://www.amazon.fr/exec/obidos/external-search?keyword='+val+'&mode=blended','search','scrollbars=yes,width=800,height=600,resize=yes,toolbar=yes,menubar=yes');
					searchwindow.focus();
				}
				return false;
			});
		});
	}
	$.fn.fillLink = function(target){
		return this.each(function(){
			$(this).change(function(){
				$(target).attr('value',$(this).attr('value'));
				return false;
			});
		});
	}
})(jQuery);