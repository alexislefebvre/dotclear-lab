function addListener(b, a, c) {
    if (b.addEventListener) {
        b.addEventListener(a, c, false)
    } else {
        if (b.attachEvent) {
            b.attachEvent("on" + a, c)
        }
    }
}


// -------------------------------------------------------------------
// markItUp!
// --------------------------------------------------------------------
// Copyright (C) 2008 Jay Salvat
// http://markitup.jaysalvat.com/
// -------------------------------------------------------------------
// Dotclear Wiki tags example
// -------------------------------------------------------------------
// Feel free to add more tags
// -------------------------------------------------------------------
myPostSettings = {
	previewParserPath:	'', // path to your DotClear parser
	onShiftEnter:		{keepDefault:false, replaceWith:'%%%\n'},
	onCtrlEnter:		{keepDefault:false, replaceWith:'\n\n'},
	markupSet: [
		{name:'strong', className:'bold', openWith:'__', closeWith:'__'}, 
		{name:'em', className:'italic', openWith:"''", closeWith:"''"}, 
		{name:'ins', className:'underline', openWith:"++", closeWith:"++"}, 
		{name:'del', className:'stroke', openWith:'--', closeWith:'--'},
		{name:'h2,h3,...,h5', className:'title', dropMenu: [
			{name:'h2', className:'h2', openWith:'\n!!!!' },
			{name:'h3', className:'h3', openWith:'\!!!' },
			{name:'h4', className:'h4', openWith:'\n!!'},
			{name:'h5', className:'h5', openWith:'\n!'}
		  ]
		},
		/*{name:'img', className:'img', key:"P", replaceWith:'(([![Url:!:http://]!]|[![Alternative text]!](!(|[![Position:!:L]!])!)))'},*/
		{name:'q', className:'cite', openWith:'{{', closeWith:'}}'}, 
		{name:'code', className:'code', openWith:'@@', closeWith:'@@'},
		{name:'ul,ol,pre', className:'list', dropMenu: [
			{name:'ul', className:'ulist', openWith:'(!(* |!|*)!)'}, 
			{name:'ol', className:'olist', openWith:'(!(# |!|#)!)'},
			{name:'pre', className:'pre', openWith:'\n///\n', closeWith:'\n///'},
		  ]
		},
		{name:'blockquote', className:'quote', openWith:'(!(> |!|>)!)'},
		{name:'URL', className:'link', openWith:"[", closeWith:'|[![URL:!:http://]!]|[![hreflang:!:]!]]'}
	]
}
myMessageSettings = {
	previewParserPath:	'', // path to your DotClear parser
	onShiftEnter:		{keepDefault:false, replaceWith:'%%%\n'},
	onCtrlEnter:		{keepDefault:false, replaceWith:'\n\n'},
	markupSet: [
		{name:'strong', className:'bold', openWith:'__', closeWith:'__'}, 
		{name:'em', className:'italic', openWith:"''", closeWith:"''"}, 
		{name:'ins', className:'underline', openWith:"++", closeWith:"++"}, 
		{name:'del', className:'stroke', openWith:'--', closeWith:'--'},
		/*{name:'img', className:'img', key:"P", replaceWith:'(([![Url:!:http://]!]|[![Alternative text]!](!(|[![Position:!:L]!])!)))'},*/
		{name:'q', className:'cite', openWith:'{{', closeWith:'}}'}, 
		{name:'code', className:'code', openWith:'@@', closeWith:'@@'},
		{name:'ul,ol,pre', className:'list', dropMenu: [
			{name:'ul', className:'ulist', openWith:'(!(* |!|*)!)'}, 
			{name:'ol', className:'olist', openWith:'(!(# |!|#)!)'},
			{name:'pre', className:'pre', openWith:'\n///\n', closeWith:'\n///'},
		  ]
		},
		{name:'blockquote', className:'quote', openWith:'(!(> |!|>)!)'},
		{name:'URL', className:'link', openWith:"[", closeWith:'|[![URL:!:http://]!]|[![hreflang:!:]!]]'}
	]
}
