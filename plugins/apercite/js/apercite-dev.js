(function($) {
	var self = null;
	
	jQuery.fn.apercite = function(o){
		return this.each(function(){
			new jQuery.apercite(this, o);
		});
	};
	
	jQuery.apercite = function(e,o){
		this.options		  	= o || {};
		this.nameDiv			= this.options.nameDiv || "apercite-thumbnail";
		this.baseURL			= this.options.baseURL || "";
		this.javascript			= this.options.javascript || "oui";
		this.java				= this.options.java || "oui";
		this.sizeX				= this.options.sizeX || "120";
		this.sizeY				= this.options.sizeY || "90";
		this.element			= jQuery(e);
		
		this.init();
	};
	
	jQuery.apercite.fn = jQuery.apercite.prototype = {
		apercite: '1.0'
	};
	
	jQuery.apercite.fn.extend = jQuery.apercite.extend = jQuery.extend;
	
	jQuery.apercite.fn.extend({
		init: function(){
			var self = this;
			
			$(this.element).append('<div id="' + self.nameDiv + '">&nbsp;</div>');
			
			this.over();
			this.out();
		},
		
		over: function(){
			var self = this;
			
			$(".post-excerpt a").mouseover(function(e){
				self.display($(this).attr("href"));
				
				self.move(e);
				
				$(this).mousemove(function(e){
					self.move(e);
				});
			});
			
			$(".post-content a").mouseover(function(e){
				self.display($(this).attr("href"));
				
				self.move(e);
				
				$(this).mousemove(function(e){
					self.move(e);
				});
			});
		},
		
		out: function(){
			var self = this;
			
			$(".post-content a").mouseout(function(e){
				$("#" + self.nameDiv).html("&nbsp;");
				$("#" + self.nameDiv).css({
					"display":"none"
				});
			});
		},
		
		display: function(u){
			var self = this;
			
			if(u[0] == '/'){
				u = self.baseURL + u;
			}
			
			$("#" + self.nameDiv).html("<img src='http://www.apercite.fr/api/apercite/" + self.sizeX + "x" + self.sizeY + "/" + self.javascript + "/" + self.java + "/" + u + "' title='Miniatures par Apercite.fr' />");
			
			$("#" + self.nameDiv).css({
				"display":"block",
				"width":self.sizeX + "px",
				"height":self.sizeY + "px"
			});
		},
		
		move: function(p){
			var self = this;
			var x = (window.innerWidth - 35) - (self.sizeX+19);
			var pX = p.pageX+17;
			
			var pY = p.pageY+17;
			
			//alert(x);
			if(x >= 0){
				x = p.pageX + 35 + self.sizeX+19;
				alert(x + ' --- ' +  window.innerWidth);
				if(x >= window.innerWidth){
					pX = p.pageX-17-self.sizeX;
				}
			}
			
			/*if(y >= 0){
				y = p.pageY + self.sizeY+19;
				if(y >= window.innerHeight){
					pY = p.pageY-17-self.sizeY;
				}
			}*/
			
			$("#" + self.nameDiv).css({
				"left":pX,
				"top":pY
			});
		}
	});
})(jQuery);