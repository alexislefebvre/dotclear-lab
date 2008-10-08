/* ********************************************************
* pre2ol - convert all pre tags to ol ones in a page.
* author  : Olivier Meunier
* version : 0.1 - 2005-03-11
*
* license :
* Creative Commons Attribution-NonCommercial-ShareAlike 2.0
* http://creativecommons.org/licenses/by-nc-sa/2.0/
******************************************************** */
function pre2ol()
{
	if (!document.getElementsByTagName) {
		return;
	}
	
	var pre = document.getElementsByTagName('pre');
	
	if (pre.length == 0) {
		return;
	}
	
	var tab = "\u00A0\u00A0\u00A0\u00A0\u00A0";
	
	for (var i=0; i<pre.length; i++)
	{
		var e = pre.item(i);
		
		var c = e.childNodes.item(0);
		var content = c.data;
		
		if (e.childNodes.length == 1 && content)
		{
			i--;
			content = content.replace(/\r\n/g,'\n');
			content = content.replace(/\r/g,'\n');
			content = content.replace(/\t/g,tab);
			content = content.replace(/  /g,'\u00A0 ');
			
			var lines = content.split('\n')
			
			var ol = document.createElement("ol");
			
			if (e.id) {
				ol.id = e.id;
			}
			
			var className = 'pre2ol';
			if (e.className) {
				className += ' '+e.className;
			}
			ol.className = className;
			
			for (var j=0; j<lines.length; j++)
			{
				if (lines[j] == '') { lines[j] = '\u00A0'; }
				
				var li = document.createElement('li');
				var span = document.createElement('span');
				var newText = document.createTextNode(lines[j]);
				span.appendChild(newText);
				li.appendChild(span);
				ol.appendChild(li);
			}
			var p = e.parentNode;
			p.replaceChild(ol,e);
		}
	}
}


/* GETELEMENTSBYTAGNAMES */

function getElementsByTagNames(list,obj) {
	if (!obj) var obj = document;
	var tagNames = list.split(',');
	var resultArray = new Array();
	for (var i=0;i<tagNames.length;i++) {
		var tags = obj.getElementsByTagName(tagNames[i]);
		for (var j=0;j<tags.length;j++) {
			resultArray.push(tags[j]);
		}
	}
	var testNode = resultArray[0];
	if (!testNode) return [];
	if (testNode.sourceIndex) {
		resultArray.sort(function (a,b) {
				return a.sourceIndex - b.sourceIndex;
		});
	}
	else if (testNode.compareDocumentPosition) {
		resultArray.sort(function (a,b) {
				return 3 - (a.compareDocumentPosition(b) & 6);
		});
	}
	return resultArray;
}

function zebra()
{
	/* Zebra lists */
	
	var lists = getElementsByTagNames('ol');
	for (var i=0;i<lists.length;i++) {
		var items = lists[i].childNodes;
		var counter = 1;
		for (var j=0;j<items.length;j++) {
			if (items[j].nodeName == 'LI' && !items[j].getElementsByTagName('li').length) {
				counter++;
				if (counter % 2 == 1)
					items[j].className = 'lizebra';
			}
		}
	}
}

function wrapol()
{
	$("ol.pre2ol").wrap("<div class=\"code-container-area\"></div>")
				.wrap("<div class=\"code-container\"></div>")
				.wrap("<div class=\"code\"></div>");
}

/* This file is taken from "Code Highlighter Drupal Module".
 *    Copyright 2008, karma-lab.net
 *    Author : Yoran Brault
 *    eMail  : software@_bad_karma-lab.net (remove _bad_ before sending an email)
 *    Site   : http://artisan.karma-lab.net
 *
 */

function code_highlighter_remove_tools(){
    var id = window.setTimeout('code_highlighter_really_remove_tools("' + $(this).attr("id") + '")', 200);
    $(this).attr('time_id', id);
}

function code_highlighter_really_remove_tools(id){
    $('#' + id).removeAttr('time_id');
    $('#' + id).find(".code-tools").remove();
}

function code_highlighter_add_tools(){
    if ($(this).attr('time_id')) {
        window.clearTimeout($(this).attr('time_id'));
        $(this).removeAttr('time_id');
    }
    if ($(this).find(".code-tools").length == 0) {
        $(this).append("<div class='code-tools'><a href='#' class='plain'>texte simple</a></div>");
        update_label($(this));
        $(this).find('.plain').mousedown(function(){
            var container = $(this).parent().parent().find(".code-container");
            switch_text(container);
        }).removeAttr('href').css('cursor', 'pointer');
    }
}

var code_area_id = 0;

function code_highlighter_initialize(){
    $(".code-container-area").each(function(){
        $(this).attr('id', "code_" + code_area_id);
        code_area_id++;
    });
    $(".code-container-area").mouseover(code_highlighter_add_tools);
    $(".code-container-area").mouseout(code_highlighter_remove_tools);
}

function back_to_color(container){
    var editor = container.find("textarea.code");
    if (editor.length != 0) {
        editor.fadeOut(1000, function(){
            editor.remove();
            var area=container.find("div.code");
            if (area.length == 0) {
                area=container.find("div.traces");
            }
            area.fadeIn(1000);
            
            update_label(container.parent());
        });
        return true;
    }
    return false;
}

function update_label(container){
    if (container.find('textarea').length > 0) {
        container.find('.plain').html('affichage précédent')
    }
    else {
        container.find('.plain').html('texte simple')
    }
}

function switch_text(container){
    if (back_to_color(container)) 
        return;
    var items = container.find("li");
    var text = '';
    for (var i = 0; i < items.length; i++) {
        if (i > 0) {
            text += "\n";
        }
        var item=$(items[i]);
        if (item.attr('class')!='result') {
            var line=item.text();
            if (item.attr('class') == 'prompt') {
                var ipos=line.indexOf('$');
                var ipos1=line.indexOf('#');
                if (ipos==-1){
                    ipos=ipos1;
                }
                line=line.substring(ipos+1);
            }
            text += line;
        }
    }
    var area=container.find('div.code');
    if (area.length==0) {
        area=container.find('div.traces');
    }
    area.fadeOut('false', function(){
        container.append("<textarea class='code'>" + text + "</textarea>");
        container.find("textarea.code").fadeIn('fast');
        container.find("textarea.code")[0].focus();
        update_label(container.parent());
    });
}

function addLoadListener(func) 
{
	if (window.addEventListener) {
		window.addEventListener("load", func, false);
	}
	 else if (document.addEventListener) {
		document.addEventListener("load", func, false);
	}
	 else if (window.attachEvent) {
		window.attachEvent("onload", func);
	}
}

addLoadListener(pre2ol);
addLoadListener(zebra);
addLoadListener(wrapol);
addLoadListener(code_highlighter_initialize);
