$(document).ready(function(){
	//fontResizer('80%','100%','120%'); 
	// $("a").niceTitle();
	$("p.field:last").addClass("content");
	$("p.form-help").text("Les commentaires comprennent la syntaxe Wiki. Utilisez-la à bon escient.");
	
	/*var link = $('<a href="#">' + $('.litribune  h2').text() + '</a>').click(function() {
		$('.litribune  fieldset').show(200);
		$('#tribnick').focus();
		$(this).parent().html($(this).text());
		return false;
	});
	$('.litribune h2').empty().append(link);
	$('.litribune fieldset').hide();*/

});


$(function() {
	var link = $('<a href="#" class="add" title="Ajouter un message"><span class="button">' + 'Plop ?' + '</span></a>').click(function() {
		$(this).hide();
		$('#sidebar .litribune fieldset').fadeIn("slow");
		$('#tribnick').focus();
		return false;
	});
	$('#sidebar .litribune ').append(link);
	$('#sidebar .litribune fieldset').hide();
});


// Developed by fluidByte (http://www.fluidbyte.net)
function fontResizer(smallFont,medFont,largeFont)
{
function clearSelected() { $(".smallFont").removeClass("curFont"); $(".medFont").removeClass("curFont"); $(".largeFont").removeClass("curFont"); }
function saveState(curSize) {	var date = new Date(); date.setTime(date.getTime()+(7*24*60*60*1000)); var expires = "; expires="+date.toGMTString(); document.cookie = "fontSizer"+"="+curSize+expires+"; path=/"; }

$(".smallFont").click(function(){ $('html').css('font-size', smallFont); clearSelected(); $(".smallFont").addClass("curFont"); saveState(smallFont); });

$(".medFont").click(function(){ $('html').css('font-size', medFont); clearSelected(); $(".medFont").addClass("curFont"); saveState(medFont); });

$(".largeFont").click(function(){ $('html').css('font-size', largeFont); clearSelected(); $(".largeFont").addClass("curFont"); saveState(largeFont); });

function getCookie(c_name) { if (document.cookie.length>0) { c_start=document.cookie.indexOf(c_name + "="); if (c_start!=-1) { c_start=c_start + c_name.length+1; c_end=document.cookie.indexOf(";",c_start); if (c_end==-1) c_end=document.cookie.length; return unescape(document.cookie.substring(c_start,c_end)); } } return ""; }

var savedSize = getCookie('fontSizer');

if (savedSize!="") { $('html').css('font-size', savedSize); switch (savedSize) { case smallFont: $(".smallFont").addClass("curFont"); break; case medFont: $(".medFont").addClass("curFont"); break; case largeFont: $(".largeFont").addClass("curFont"); break; default: $(".medFont").addClass("curFont"); } }
else { $('html').css('font-size', medFont); $(".medFont").addClass("curFont"); }
}


$.fn.colorHover = function (animtime,fromColor,toColor) { //link hovers color
	$(this).hover(function () {
		return $(this).css('color',fromColor).stop().animate({'color': toColor},animtime);
		}, function () {
		return $(this).stop().animate({'color': fromColor},animtime);
	});
}


$.fn.backgroundColorHover = function (animtime,fromColor,toColor) { //link hovers color
	$(this).hover(function () {
		return $(this).css('backgroundColor',fromColor).stop().animate({'backgroundColor': toColor},animtime);
		}, function () {
		return $(this).stop().animate({'backgroundColor': fromColor},animtime);
	});
}


/*
 * jQuery niceTitle plugin
 * Version 1.00 (1-SEP-2009)
 * @author leeo(IT北瓜)
 * @requires jQuery v1.2.6 or later
 *
 * Examples at: http://imleeo.com/jquery-example/jQuery.niceTitle.html
 * Copyright (c) 2009-2010 IT北瓜www.imleeo.com
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 
 *History:
 *Version 1.00 (1-SEP-2009) The first release
 *Version 1.10 (7-SEP-2009) Fixed the bug in IE when change parameter "bgColor"(add code: line: 68,69)
 *Version 1.20 (14-SEP-2009) Hide the <img />'s alt and title attributes if <a> includes an image.(code: line: 21,46-53,84-87)
 */
;(function($) {
	$.fn.niceTitle = function(options){
		var opts = $.extend({}, $.fn.niceTitle.defaults, options);
		var _self = this, _imgAlt = "", _imgTitle = "", _hasImg = false, _imgObj;
		this.initialize = function(_opts){
			var htmlStr = "";
			if(jQuery.browser.msie){//如果是IE浏览器，则通过css来产生圆角效果
				htmlStr = '<div id="niceTitle">' +
							   '<span>' +
								   '<span class="r1"></span>' +
								   '<span class="r2"></span>' +
								   '<span class="r3"></span>' +
								   '<span class="r4"></span>' +
							   '</span>' +
							   '<div id="niceTitle-ie"><p><em></em></p></div>' +
							   '<span>' +
								   '<span class="r4"></span>' +
								   '<span class="r3"></span>' +
								   '<span class="r2"></span>' +
								   '<span class="r1"></span>' +
							   '</span>' +
						    '</div>';
			}else{
				htmlStr = '<div id="niceTitle"><p><em></em></p></div>';
			}
			$(_self).mouseover(function(e){
			    this.tmpTitle = this.title;//等价于$(this).attr("title");
			    this.tmpHref = this.href;//等价于$(this).attr("href");
			    _imgObj = $(this).find("img");
			    if(_imgObj.length > 0){
			    	_imgAlt = _imgObj.attr("alt");
			    	_imgObj.attr("alt", "");
			    	_imgTitle = _imgObj.attr("title");
			    	_imgObj.attr("title", "");
			    	_hasImg = true;
			    }
				var _length = _opts.urlSize;
			    this.tmpHref = (this.tmpHref.length > _length ? this.tmpHref.toString().substring(0,_length) + "..." : this.tmpHref);
			    this.title = "";//等价于$(this).attr("title", "");
				$(htmlStr).appendTo("body").find("p").prepend(this.tmpTitle + "<br />").css({"color": _opts.titleColor}).find("em").text(this.tmpHref).css({"color": _opts.urlColor});
				var obj = $('#niceTitle');
			    obj.css({
					"position":"absolute",
	                "text-align":"left",
	                "padding":"5px",
					"opacity": _opts.opacity,
					"top": (e.pageY + _opts.y) + "px",
					"left": (e.pageX + _opts.x) + "px",
					"z-index": _opts.zIndex,
					"max-width": _opts.maxWidth + "px",
					"width": "auto !important",
					"width": _opts.maxWidth + "px",
					"min-height": _opts.minHeight + "px",
					"-moz-border-radius": _opts.radius + "px",
					"-webkit-border-radius": _opts.radius + "px"
				});
				if(!jQuery.browser.msie){//如果不是IE浏览器
				    obj.css({"background": _opts.bgColor});
				}else{//Version 1.10修正IE下改变背景颜色
				    $('#niceTitle span').css({"background-color": _opts.bgColor, "border-color": _opts.bgColor});
					$('#niceTitle-ie').css({"background": _opts.bgColor, "border-color": _opts.bgColor});
				}
				obj.show('fast');
		    }).mouseout(function(){
			    this.title = this.tmpTitle;
			    $('#niceTitle').remove();
			    if(_hasImg){
			    	_imgObj.attr("alt", _imgAlt);
			    	_imgObj.attr("title", _imgTitle);
			    }
		    }).mousemove(function(e){
			    $('#niceTitle').css({
			   	    "top": (e.pageY + _opts.y) + "px",
					"left": (e.pageX + _opts.x) + "px"
			    });
		    });
			return _self;
		};
		this.initialize(opts);
	};
    $.fn.niceTitle.defaults = {
		x: 10,
		y: 20,
		urlSize: 30,
		bgColor: "#000",
		titleColor: "#FFF",
		urlColor: "#F60",
		zIndex: 999,
		maxWidth: 250,
		minHeight: 30,
		opacity: 0.8,
		radius: 8
	};
})(jQuery);