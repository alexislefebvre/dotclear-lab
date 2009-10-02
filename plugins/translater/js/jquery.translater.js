/* -- BEGIN LICENSE BLOCK ----------------------------------
 * This file is part of "translater", a plugin for Dotclear 2.
 *
 * Copyright (c) 2009 JC Denis and contributors
 * jcdenis@gdwd.com
 *
 * Licensed under the GPL version 2.0 license.
*  A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -- END LICENSE BLOCK ------------------------------------*/

;if(window.jQuery) (function($){
$.fn.translater = function(options){
	var opts = $.extend({}, $.fn.translater.defaults, options);
	return this.each(function(){
		$(this).click(function(){
			getProposal(this,opts.url,opts.func,opts.from,opts.to,opts.tool,opts.title,opts.failed);});});};
function getProposal(target,surl,func,from,to,tool,title,failed){
	var cur_line = $(target);
	var content = $(target).text();
	$(target).css('cursor','wait');
	$.get(surl,{f:func,langFrom:from,langTo:to,langTool:tool,langStr:content},
		function(data){
			data=$(data);
			if(data.find('rsp').attr('status')=='ok' && $(data).find('proposal').attr('str_to')){
				alert(title+"\n"+$(data).find('proposal').attr('str_to'));$(target).css('cursor','auto');
			}else{
				alert(failed);$(target).css('cursor','auto');
			}
		});
		return target;
	}
$.fn.translater.defaults = {url: '',func: '',from: 'en',to: 'fr',tool: 'google',title: 'Translation: ',failed: 'Failed to translate this'};
})(jQuery);