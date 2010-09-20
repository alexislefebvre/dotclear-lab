/*
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcCron, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
*/
$(function() {
	$('.checkboxes-helpers').each(function() {
		dotclear.checkboxesHelpers(this);
	});
	$('input[name="save"]').click(function() {
		if ($('#action').val() == 'del') {
			return window.confirm(dotclear.msg.confirm_delete_task);
		};
	});
	$('#interval').getServiceFormInput({
		service: 'getInterval',
		containerType: 'span',
	});
	if (document.getElementById('first_run')) {
		var dccron_dtPick = new datePicker($('#first_run').get(0));
		dccron_dtPick.img_top = '1.5em';
		dccron_dtPick.draw();
	};
});

(function($) {
	$.getServiceFormInput = function(input,params) {
		var defaults = {
			url: 'services.php',
			service: null,
			containerType: null,
			delay: 1000,
		};
		params = $.extend(defaults,params);
		
		if (params.service == null) {
			throw 'No service given';
		}
		
		input = $(input);
		
		if (params.containerType == null) {
			var result = $(document.createElement('div'));
		} else {
			var result = $(document.createElement(params.containerType));
		}
		
		var timeout = false;
		var prevLength = 0;
		
		result.attr('id','result-service').appendTo(input.parent()).hide();
		
		input.blur(function() {
			setTimeout(hideResult,100);
			function hideResult() {
				result.hide();
			};
		});
		
		if ($.browser.mozilla) {
			input.keypress(processKey);
		} else {
			input.keydown(processKey);
		}
		
		function hideResult() {
			result.hide();
		};
		
		function processKey(e) {
			if (input.val().length != prevLength)
			{
				if (timeout)
					clearTimeout(timeout);
				if (input.val().length == 0) 
				{
					hideResult();
				} else {
					timeout = setTimeout(get, params.delay);
					prevLength = input.val().length;
				}
			}
			
		};
		
		function get() {
			$.get(params.url,{f: params.service,i: input.val()},function(data) {
				var rsp = $(data).children('rsp')[0];
				if (rsp.attributes[0].value == 'ok') {
					result.html('&nbsp;<em>= '+$(rsp).find('interval').text()+'</em>');
				} else {
					result.html('&nbsp;<strong>'+$(rsp).find('message').text()+'</strong>');
				}
				result.show();
			});
		};
	};
	
	$.fn.getServiceFormInput = function(params) {
		this.each(function() {
			new $.getServiceFormInput(this,params);
		});
		return this;
	};
})(jQuery);