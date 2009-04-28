YAHOO.util.Event.onDOMReady(
	function()
	{
		iPhoneView.updateLayout();
		
		setInterval(
			function()
			{
				iPhoneView.updateLayout();
			},
			400
		);
	}
);

var iPhoneView = {
	updateLayout: function()
	{
		if (window.innerWidth != currentWidth)
		{
			currentWidth = window.innerWidth;
			var orient = currentWidth == 320 ? "profile": "portrait";
			document.body.setAttribute("orient", orient);
			setTimeout(function()
			{
				window.scrollTo(0, 1);
			},
			100
			);
		}
	},
	initMenu: function()
	{
		var els = ['dropmenu', 'search'];
		for (i in els)
		{
			var el = YAHOO.util.Dom.get(els[i]);
			
			el.fullHeight = YAHOO.util.Dom.getRegion(
				el
			).height;
			
			YAHOO.util.Dom.setStyle(
				el,
				'height', 0
			);
			
			el.toggle = function()
			{
				if (parseInt(
						YAHOO.util.Dom.getStyle(
							this, 'height'
						)
					) == 0)
				{
					(new YAHOO.util.Anim(
						this,
						{
							height: {
								to: this.fullHeight
							}
						},
						0.4,
						YAHOO.util.Easing.easeOutStrong
					)).animate();
				}
				else
				{
					(new YAHOO.util.Anim(
						this,
						{
							height: {
								to: 0
							}
						},
						0.4,
						YAHOO.util.Easing.easeInStrong
					)).animate();
				}
			};
		}
	},
	initPagination: function(el)
	{
		YAHOO.util.Event.on(
			YAHOO.util.Dom.getElementsByClassName(
				'ajax',
				'a',
				YAHOO.util.Dom.get(el)
			),
			'click',
			function(e)
			{
				YAHOO.util.Event.preventDefault(e);
				
				this.parentNode.getElementsByTagName('img')[0].style.visibility = 'visible';
				
				YAHOO.util.Connect.asyncRequest(
					'get',
					this.href,
					{
						success: function(o)
						{
							var p = this.parentNode.parentNode;
							p.removeChild(
								this.parentNode
							);
							
							var div = document.createElement('div');
							div.innerHTML = o.responseText;
							
							p.appendChild(div);
							
							iPhoneView.initPostList(div);
							iPhoneView.initPagination(div);
							
							while (div.firstChild)
							{
								p.appendChild(
									div.firstChild
								);
							}
						},
						scope: this
					}
				)
			}
		);
	},
	initPostList: function(el)
	{
		var els = YAHOO.util.Dom.getElementsByClassName(
			'post-arrow',
			'span',
			YAHOO.util.Dom.get(el)
		);
		for (var i = 0; els[i]; i++)
		{
			els[i].ctn = YAHOO.util.Dom.getElementsByClassName(
				'mainentry',
				'div',
				els[i].parentNode
			)[0];
			els[i].ctn.fullHeight = YAHOO.util.Dom.getRegion(
				els[i].ctn
			).height;
			if (els[i].ctn.fullHeight == 0)
			{
				els[i].ctn.fullHeight = 200;
			}
			
			YAHOO.util.Dom.setStyle(
				els[i].ctn,
				'height', 0
			);
			
			YAHOO.util.Event.on(
				els[i],
				'click',
				function(e)
				{
					if (!this.ctn)
					{
						return;
					}
					
					if (this.className == 'post-arrow')
					{
						(new YAHOO.util.Anim(
							this.ctn,
							{
								height: {
									to: this.ctn.fullHeight
								}
							},
							0.4,
							YAHOO.util.Easing.easeOutStrong
						)).animate();
						
						this.className = 'post-arrow-down';
					}
					else
					{
						(new YAHOO.util.Anim(
							this.ctn,
							{
								height: {
									to: 0
								}
							},
							0.4,
							YAHOO.util.Easing.easeInStrong
						)).animate();
						
						this.className = 'post-arrow';
					}
				}
			);
		}
	},
	initConfigLink: function()
	{
		YAHOO.util.Event.on(
			YAHOO.util.Dom.get(
				'wptouch-switch-link'
			).getElementsByTagName('a')[0],
			'click',
			function(e)
			{
				document.cookie = 'iphoneview=no;expires=0;path=/';
				this.getElementsByTagName('img')[0].style.display = 'none';
				this.getElementsByTagName('img')[1].style.display = '';
			}
		);
	}
};