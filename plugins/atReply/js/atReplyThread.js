/* rearrangement of the comments considered as replies */
var replies_list = new Array();
var commentInfos_list = new Array();
var comments_list = new Array();
var ancestors_count = 0;
var first_margin = 25; // indent of the first level reply (in pixel)

$(document).ready(function () {
	if (!$("#comments > dl > dt").length) {
		return; // no comments, bail out
	}
	if (atreply_show_switch) {setSwitchTag();}
	getCommentsChildren();
	if (atreply_append) {appendReplies(atreply_append);}
});

// Original idea by Aurelien Bompart < http://aurelien.bompard.org/ >
function setSwitchTag()
{ // Init: collect comments, add the switch
    if (!$("#comments").length) {
        return; // no comments, bail out
    }
    var switch_tag = document.createElement("p");
    switch_tag.className = "threading";
    $("#comments h3").before(switch_tag)
    $("#comments > p").html(
        '<label id="atReplySwitch">'+atReply_switch_text+': <input type="checkbox" id="threading-switch" '+
		((atreply_append)? 'checked' : '')+
		'/></label>').find("input").click(toggleAtReplyDisplay);
}

function toggleAtReplyDisplay()
{
	atreply_append = (atreply_append)? 0 : 1;
	appendReplies(atreply_append);
}

/* commentInfos object */
function commentInfos(comment_id, replied)
{
	this.id = comment_id;
	this.replies_to = (replied)? replied : null;//direct parent of the comment (if a reply)
	this.children = new Array();// replies to the comment
	this.children_count = 0;// number of replies to the comment
	this.content = new Array();//content of the comment (dt, dd) and their left-margin
	this.ancestors_count = -1;// number of ancestors (if the comment is a reply, else -1)
	this.indent = 0;//indent of the comment when shown as threaded
	
	this.addChild = function (ci){
		this.children.push(ci);
		this.children_count ++;
	};
	
	this.addContent = function (element){
		var lm = String(element.css('margin-left').replace(/auto$/,'0'));
		lm = parseInt(lm.replace(/(px)|(%)|(pt)$/,''));
		// add element and its left margin
		this.content.push(new Array( $(element), lm ) );
	};
	
	this.isContent = function(element){
		for (var n in this.content)
		{
			if(this.content[n][0].html() === $(element).html()) return this.content[n];
		}
		return false;
	};
	
	this.isReply = function (){
		return (this.replies_to !== null); 
	};
	
	this.appendContent = function(where){
		// set indent on first pass
		if (this.ancestors_count == -1)
		{
			this.ancestors_count = countAncestors(this.id);
			
			if (this.ancestors_count == 1)
			{
				this.indent = first_margin;
			}
			else if(this.ancestors_count > 1)
			{
				var parent_indent = commentInfos_list[this.replies_to].indent;
				this.indent = Math.round(parent_indent+(parent_indent/( Math.pow(ancestors_count-1,2) )));
			}
		}
		
		// append content (dt and dd)
		for (var n in this.content)
		{
			var element = this.content[n][0];
			// set styles
			element.css('margin-left',this.indent+this.content[n][1]);
			
			if(this.isReply()) element.addClass('replyCmt');
			if(this.children_count>0) element.addClass('repliedCmt');
			
			where.append(element);
		}
		
	};
	
	this.appendAll = function(where){
		this.appendContent(where);
		for (var v in this.children){
			this.children[v].appendAll(where);
		}
	};
}


function appendReplies(b)
{
	var dl = $("#comments > dl");
	dl.empty();
	
	for(var v in comments_list)
	{
		var c = $(comments_list[v][0]);
		
		if (b)
		{// threaded comments
			// append element if not already done
			if (dl.children().index(c)  ==  -1 )
			{
				var id = c.attr('id');
				// if the element is a comment (is in commentInfos_list)
				if(id != null && commentInfos_list[id])
					commentInfos_list[id].appendAll(dl);
			}
		}
		else
		{// regular comments display
			// if element c is a comment (dt or dd)
			if(comments_list[v][1])
			{
				// remove styles:
				if(c.hasClass('repliedCmt')) c.removeClass('repliedCmt');
				if(c.hasClass('replyCmt')) c.removeClass('replyCmt');
				// retrieve previous margin
				var ci = commentInfos_list[comments_list[v][1]];
				// a contains the element and its initial margin
				var a = ci.isContent(c); 
				if(a) c.css({'margin-left': a[1]+'px'});
			}
			
			dl.append(c);
		}
	}
	atReply();
}

function countAncestors(id)
{
	ancestors_count = 0;
	hasParent(id);
	return ancestors_count;
}

function hasParent(id)
{
	if (replies_list[id])
	{
		ancestors_count ++;
		hasParent(replies_list[id]);
	}
	return;
}

function setCommentInfos(id)
{
	// if commentInfos not already created
	if (!commentInfos_list['c'+id])
	{
		var dt = $('#c'+id);
		var dd = $('#atReplyComment'+id).parent();
		// is reply
		var str = dd.html();
		var a = dd.html().match(/@<a href="#c(\d+)"/i);
		if (a)
		{
			commentInfos_list['c'+id] = new commentInfos(id, 'c'+a[1]);
			// add to replies_list
			replies_list[id] = a[1];
		}
		else
		{
			// absolute URL
			var a = dd.html().match(/@<a href="http(.+)#c(\d+)"/i);
			if (a)
			{
				commentInfos_list['c'+id] = new commentInfos(id, 'c'+a[1]);
				// add to replies_list
				replies_list[id] = a[2];
			}
			else
			{
				// not a reply
				commentInfos_list['c'+id] = new commentInfos(id, null);
			}
		}
	}
}

function getCommentsChildren()
{
	$("#comments > dl").children().each(function(i)
	{
		// add to comments_list : this element, and the id if it is a comment
		comments_list[i] = new Array(this, null);
		// if dd
		if ($(this).children('span').attr('id') && 
			$(this).children('span').attr('id').match(/^atReplyComment/))
		{
			var id = $(this).children('span').attr('id').replace(/^atReplyComment/,'');
			setCommentInfos(id);
			// add this comment to the replied comment's children
			if(commentInfos_list['c'+id].isReply())
				commentInfos_list[commentInfos_list['c'+id].replies_to].addChild(commentInfos_list['c'+id]);
		}
		// if dt
		else if ($(this).attr('id'))
		{
			var id = $(this).attr('id').replace(/^c/,'');
			setCommentInfos(id);
		}
		else
		{
			// not a comment dt nor dd
			return;
		}
		
		comments_list[i][1] = 'c'+id;
		commentInfos_list['c'+id].addContent($(this));
	});
}