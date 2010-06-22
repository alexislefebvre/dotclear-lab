/* -- BEGIN LICENSE BLOCK ----------------------------------
 * This file is part of noodles, a plugin for Dotclear 2.
 *
 * Copyright (c) 2009 JC Denis and contributors
 * jcdenis@gdwd.com
 *
 * Licensed under the GPL version 2.0 license.
*  A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -- END LICENSE BLOCK ------------------------------------*/

;if(window.jQuery) (function($) {

	$.fn.noodles = function(options) {

		var opts = $.extend({}, $.fn.noodles.defaults, options);

		return this.each(function() {
			parseNoodle(this,opts.service_url,opts.service_func,opts.imgPlace,opts.imgId);
		});
	};

	function parseNoodle(target,service_url,service_func,imgPlace,imgId) {

		var cur_line = $(target);
		var content = encodeURIComponent($(target).get());

		$.post(service_url,{noodleContent:content,noodleId:imgId},
			function(data){
				data=$(data);
				if(data.find('rsp').attr('status')=='ok' && $(data).find('noodle').attr('src'))
				{
					var size = $(data).find('noodle').attr('size')+'px';
					var res = $('<img src="'+$(data).find('noodle').attr('src')+'" alt="" />');
					$(res).addClass('noodles-'+imgId).height(size).width(size);

					if (imgPlace=='append')
						$(cur_line).append($(res));
					if (imgPlace=='prepend')
						$(cur_line).prepend($(res));
					if (imgPlace=='before')
						$(cur_line).before($(res));
					if (imgPlace=='after')
						$(cur_line).after($(res));
				}
			}
		);
		return target;
	}

	$.fn.noodles.defaults = {
		service_url: '',
		service_func: 'getNoodle',
		imgPlace: 'prepend',
		imgId: ''
	};

})(jQuery);
