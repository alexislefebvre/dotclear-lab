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
		this.localLink			= this.options.localLink || "oui";
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
			
			$("#link-summarizer a").mouseover(function(e){
				self.display($(this).attr("href"));
				
				self.move(e);
				
				$(this).mousemove(function(e){
					self.move(e);
				});
			});
		},
		
		out: function(){
			var self = this;
			
			$(".post-excerpt a").mouseout(function(){
				$("#" + self.nameDiv).css({
					"display":"none"
				});
				$("#" + self.nameDiv).html("&nbsp;");
			});
			
			$(".post-content a").mouseout(function(){
				$("#" + self.nameDiv).css({
					"display":"none"
				});
				$("#" + self.nameDiv).html("&nbsp;");
			});
			
			$("#link-summarizer a").mouseout(function(){
				$("#" + self.nameDiv).css({
					"display":"none"
				});
				$("#" + self.nameDiv).html("&nbsp;");
			});
		},
		
		display: function(u){
			var self = this;
			
			if(u[0] == '/'){
				if(self.localLink == "oui"){
					u = self.baseURL + u;
				}
				else{
					return true;
				}
			}
			else{
				if(self.localLink == "non"){
					var c = u.substr(0,self.baseURL.length);
					
					if(self.baseURL == c){
						return true;
					}
				}
			}
			
			$("#" + self.nameDiv).html("<img src='http://www.apercite.fr/api/apercite/" + self.sizeX + "x" + self.sizeY + "/" + self.javascript + "/" + self.java + "/" + u + "' title='Miniature par Apercite.fr' />");
			
			$("#" + self.nameDiv).css({
				"display":"block",
				"width":self.sizeX + "px",
				"height":self.sizeY + "px"
			});
		},
		
		move: function(p){
			var self = this;
			
			var pX = p.pageX+17;
			var pY = p.pageY+17;
			
			widthDisplay = window.innerWidth - 30;
			
			var x = widthDisplay - (window.innerWidth - p.pageX);
			
			if(x > self.sizeX){
				x = p.pageX + self.sizeX+19;
				if(x >= widthDisplay){
					pX = p.pageX-17-self.sizeX;
				}
			}
			
			$("#" + self.nameDiv).css({
				"left":pX,
				"top":pY
			});
		}
	});
})(jQuery);