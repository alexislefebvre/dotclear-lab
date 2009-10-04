/*
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dctribune, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku  and contributors
# Many thanks to Pep, Tomtom and JcDenis
# Originally from Antoine Libert
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
*/

;if(window.jQuery) (function($) {

	$.fn.tribune = function(options) {

		var opts = $.extend({}, $.fn.tribune.defaults, options);

		return this.each(function() {
			tribunise(this,opts.service_url,opts.refresh_delay,opts.verbose);
		});
	};
	
	function tribunise(target,service_url,refresh_delay,verbose) {
	
		getFirstTribune(target,service_url);
		var fsubmit = $(target).parent().find('.tribunesubmit');
		updateTribune(target,service_url,refresh_delay);
		$(fsubmit).click(function(){
		
			var tribnick = $('#tribnick').val();
			var tribmsg = $('#tribmsg').val();

			$(target).fadeTo("slow", 0.33);
			
			$.ajax({
				url: service_url,
				type: "POST",
				data: ({tribnick : tribnick, tribmsg : tribmsg}),
				error:function(){if(verbose==1){alert("Something wrong with AJAX/Server...");}},
				
				success: function(html){
					$('#tribmsg').attr('value','');
					
					$(target).fadeTo("slow", 1).html(html);
				}
			});
		return false;
		});
	}
	
	function getFirstTribune(target,service_url) {

		$.post(service_url,{foo: "bar"}, function(html) {
			$(target).hide().html(html).fadeIn();
		});		
	}
	
	function updateTribune(target,service_url,refresh_delay) {

		setTimeout(function(){
			$(target).fadeTo("slow", 0.33);
			$.post(service_url,{foo: "bar"}, function(html) {
				$(target).html(html).fadeTo("slow", 1);
		});
		updateTribune(target,service_url,refresh_delay);
		},refresh_delay);
		
	}

	$.fn.tribune.defaults = {
		service_url: '',
		blog_uid: '',
		refresh_delay: 5000,
		verbose: 0
	}
})(jQuery);