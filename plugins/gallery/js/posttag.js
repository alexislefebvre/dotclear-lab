$(function()
{
		document.post_tags = document.getElementById('post_tags');
		
		var meta_edit_tags = document.getElementById('meta-edit-tags');
		var post_id = document.getElementById('id');
		if (meta_edit_tags != undefined && post_id != undefined)
		{
			post_id = post_id.value;
			// Remove everything in meta_edit_tags
			while (meta_edit_tags.hasChildNodes()) {
				meta_edit_tags.removeChild(meta_edit_tags.firstChild)
			}
			
			// Add tags
			var mEdit = new metaEditor(meta_edit_tags);
			mEdit.displayMeta('tag',post_id);
		}
});

function metaEditor(target_element) {
	this.target_element = target_element;
};

metaEditor.prototype = {
	text_confirm_remove: 'Are you sure you want to remove this %s?',
	text_add_meta: 'Add a %s to this entry',
	text_choose: 'Choose from list',
	text_more: 'more',
	text_all: 'all',
	
	service_uri: 'services.php',
	
	displayMeta: function(meta_type,post_id)
	{
		var params = {
			f: 'getMeta',
			metaType: meta_type,
			sortBy: 'metaId asc',
			postId: post_id
		}
		
		var This = this;
		$.get(this.service_uri,params,function(data) {
			var tags = $(data).children('meta')[0];
			
			if ($(data).find('rsp').attr('status') != 'ok') { return; }
			
			while (This.target_element.hasChildNodes()) {
				This.target_element.removeChild(This.target_element.firstChild)
			}
			
			if ($(data).find('meta').length > 0) {
				var ul = document.createElement('ul');
				ul.className = 'metaList';
				var meta_id, li, a_tag, a_remove;
				$(data).find('meta').each(function() {
					meta_id = $(this).text();
					li = document.createElement('li');
					a_tag = document.createElement('a');
					a_tag.href='plugin.php?p=metadata&m=tag_posts&tag='+$(this).attr('uri');
					a_tag.appendChild(document.createTextNode(meta_id));
					
					a_remove = document.createElement('a');
					a_remove.className = 'metaRemove';
					a_remove.href='#';
					a_remove.appendChild(document.createTextNode('[x]'));
					
					a_remove.meta_type = meta_type;
					a_remove.meta_id = meta_id;
					
					a_remove.onclick = function() {
						This.removePostMeta(post_id,this.meta_type,this.meta_id);
						return false;
					};
					
					li.appendChild(a_tag);
					li.appendChild(document.createTextNode(String.fromCharCode(160)));
					li.appendChild(a_remove);
					ul.appendChild(li);
					This.target_element.appendChild(ul);
				});
			}
			
			var p_add = document.createElement('p');
			var a_add = document.createElement('a');
			p_add.appendChild(a_add);
			a_add.href='#';
			
			a_add.meta_type = meta_type;
			a_add.post_id = post_id;
			
			a_add.appendChild(document.createTextNode(This.text_add_meta.replace(/%s/,meta_type)));
			
			a_add.onclick = function() {
				This.addMetaDialog(this,this.meta_type,this.post_id);
				return false;
			};
			
			
			This.target_element.appendChild(p_add);
		});
	},
	
	addMetaDialog: function(a,meta_type,post_id)
	{
		var This = this;
		
		var I = document.createElement('input'); // Input element to add meta
		I.id = 'meta-text-field';
		I.setAttribute('type','text');
		I.className = '';
		$(I).keypress(function(evt) { // We don't want to submit form!
			if (evt.keyCode == 13) {
				var v = This.splitMetaValues(this.value).join(',');
				This.addPostMeta(post_id,meta_type,v);
				return false;
			}
			return true;
		});
		
		var S = document.createElement('input'); // Button to add meta
		S.setAttribute('type','button');
		S.value = 'ok';
		S.onclick = function() {
			var target = document.getElementById('meta-text-field').value; // Opera sucks!
			var v = This.splitMetaValues(target).join(',');
			This.addPostMeta(post_id,meta_type,v);
			return false;
		};
		
		// View meta list
		var P = a.parentNode;
		P.removeChild(a);
		
		P.appendChild(I);
		P.appendChild(document.createTextNode(' '));
		P.appendChild(S);
		
		var p = document.createElement('p');
		var A = document.createElement('a');
		A.href = '#';
		A.appendChild(document.createTextNode(this.text_choose));
		A.onclick = function() {
			This.showMetaList('small',meta_type,p,I);
			return false;
		};
		
		p.appendChild(A);
		P.parentNode.appendChild(p);
	},
	
	showMetaList: function(type,meta_type,target,input_target)
	{
		while (target.hasChildNodes()) {
			target.removeChild(target.firstChild)
		}
		var w = document.createTextNode('...');
		target.appendChild(w);
		
		target.className = 'addMeta';
		
		var params = {
			f: 'getMeta',
			metaType: meta_type,
			sortby: 'metaId,asc'
		};
		
		if (type == 'small') {
			params.limit = '15';
		} else if (type == 'more') {
			params.limit = '30';
		}
		
		var This = this;
		
		$.get(this.service_uri,params,function(data) {
			if ($(data).find('meta').length > 0) {
				target.removeChild(w);
				var meta_id, A;
				
				$(data).find('meta').each(function() {
					meta_id = $(this).text();
					A = document.createElement('a');
					A.href = '#';
					A.meta_id = meta_id;
					
					A.onclick = function() {
						var v = This.splitMetaValues(input_target.value+','+this.meta_id);
						input_target.value = v.join(', ');
						return false;
					};
					
					A.appendChild(document.createTextNode(meta_id));
					target.appendChild(A);
					target.appendChild(document.createTextNode(' '));
				});
				
				
				if (type == 'small') {
					var new_type = 'more';
					var new_text = This.text_more;
				} else if (type == 'more') {
					var new_type = 'all';
					var new_text = This.text_all;
				}
				
				if (type == 'small' || type == 'more') {
					var a_more = document.createElement('a');
					a_more.className = 'metaGetMore';
					a_more.href = '#';
					a_more.onclick = function() {
						This.showMetaList(new_type,meta_type,target,input_target);
						return false;
					};
					a_more.appendChild(document.createTextNode(new_text +
						String.fromCharCode(160)+String.fromCharCode(187)));
					
					target.appendChild(a_more);
				}
			}
		});
	},
	
	addPostMeta: function(post_id,meta_type,meta)
	{
		var params = {
			f: 'setPostMeta',
			postId: post_id,
			metaType: meta_type,
			meta: meta
		};
		
		var This = this;
		$.post(this.service_uri,params,function(data) {
			if ($(data).find('rsp').attr('status') == 'ok') {
				This.displayMeta(meta_type,post_id);
			} else {
				alert($(data).find('message').text());
			}
		});
	},
	
	removePostMeta: function(post_id,meta_type,meta_id)
	{
		var text_confirm_msg = this.text_confirm_remove.replace(/%s/,meta_type);
		var This = this;
		
		if (window.confirm(text_confirm_msg)) {
			var params = {
				f: 'delMeta',
				postId: post_id,
				metaId: meta_id,
				metaType: meta_type
			};
			
			$.post(this.service_uri,params,function(data) {
				if ($(data).find('rsp').attr('status') == 'ok') {
					This.displayMeta(meta_type,post_id);
				} else {
					alert($(data).find('message').text());
				}
			});
		}
	},
	
	splitMetaValues: function(str)
	{
		function inArray(needle,stack) {
			for (var i=0; i<stack.length; i++) {
				if (stack[i] == needle) {
					return true;
				}
			}
			return false;
		}
		
		var res = new Array();
		var v = str.split(',');
		v.sort();
		for (var i=0; i<v.length; i++) {
			v[i] = v[i].replace(/^\s*/,'').replace(/\s*$/,'');
			if (v[i] != '' && !inArray(v[i],res)) {
				res.push(v[i]);
			}
		}
		res.sort();
		return res;
	}
};

// Toolbar button for tags
jsToolBar.prototype.elements.tagSpace = {type: 'space'};

jsToolBar.prototype.elements.tag = {type: 'button', title: 'Keyword', fn:{} };
jsToolBar.prototype.elements.tag.context = 'post';
jsToolBar.prototype.elements.tag.icon = 'index.php?pf=metadata/tag-add.png';
jsToolBar.prototype.elements.tag.addTag = function(content) {
	if (!content) { return false; }
	
	var meta_edit_tags = document.getElementById('meta-edit-tags');
	var post_tags = document.getElementById('post_tags');
	var post_id = document.getElementById('id');
	
	if (meta_edit_tags && post_id) {
		var mEdit = new metaEditor(meta_edit_tags);
		mEdit.addPostMeta(post_id.value,'tag',content);
		return true;
	} else if (post_tags != undefined) {
		post_tags.value += content+', ';
	}
	return false;
};
jsToolBar.prototype.elements.tag.fn.wiki = function() {
	this.encloseSelection('','',function(str) {
		if (str == '') { return ''; }
		if (str.indexOf(',') != -1) {
			return str;
		} else {
			this.elements.tag.addTag(str);
			return '['+str+'|tag:'+str+']';
		}
	});
};
jsToolBar.prototype.elements.tag.fn.xhtml = function() {
	var url = this.elements.tag.url;
	this.encloseSelection('','',function(str) {
		if (str == '') { return ''; }
		if (str.indexOf(',') != -1) {
			return str;
		} else {
			this.elements.tag.addTag(str);
			return '<a href="'+this.stripBaseURL(url+'/'+str)+'">'+str+'</a>';
		}
	});
};
jsToolBar.prototype.elements.tag.fn.wysiwyg = function() {
	var t = this.getSelectedText();
	
	if (t == '') { return; }
	if (t.indexOf(',') != -1) { return; }
	
	var n = this.getSelectedNode();
	var a = document.createElement('a');
	a.href = this.stripBaseURL(this.elements.tag.url+'/'+t);
	a.appendChild(n);
	this.insertNode(a);
	this.elements.tag.addTag(t);
};
