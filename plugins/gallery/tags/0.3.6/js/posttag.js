$(function() {
		var meta_edit_tags = $('#meta-edit-tags');
		var post_id = $('#id');
		var meta_field = null;
		
		if (meta_edit_tags.length > 0) {
			post_id = (post_id.length > 0) ? post_id.get(0).value : false;
			if (post_id == false) {
				meta_field = $('<input type="hidden" name="post_tags" />');
				meta_field.val($('#post_tags').val());
			}
			var mEdit = new metaEditor(meta_edit_tags,meta_field);
			mEdit.displayMeta('tag',post_id);
			
			// mEdit object reference for toolBar
			window.dc_tag_editor = mEdit;
		}
});

function metaEditor(target,meta_field) {
	this.target = target;
	this.meta_field = meta_field;
};

metaEditor.prototype = {
	meta_ur: '',
	text_confirm_remove: 'Are you sure you want to remove this %s?',
	text_add_meta: 'Add a %s to this entry',
	text_choose: 'Choose from list',
	text_more: 'more',
	text_all: 'all',
	
	target: null,
	meta_type: null,
	meta_dialog: null,
	meta_field: null,
	post_id: false,
	
	service_uri: 'services.php',
	
	displayMeta: function(type,post_id) {
		this.meta_type = type;
		this.post_id = post_id;
		this.target.empty();
		
		this.meta_dialog = $('<input type="text" />');
		
		var This = this;
		
		var a = $('<a href="#"></a>');
		a.append(this.text_add_meta.replace(/%s/,this.meta_type));
		a.click(function() {
			$(this).parent().remove();
			This.addMetaDialog();
			return false;
		});
		this.target.append($('<p></p>').append(a));
		
		if (this.post_id == false) {
			this.target.append(this.meta_field);
		}
		this.displayMetaList();
	},
	
	displayMetaList: function() {
		var li;
		if (this.meta_list == undefined) {
			this.meta_list = $('<ul class="metaList"></ul>');
			this.target.prepend(this.meta_list);
		}
		
		if (this.post_id == false) {
			var meta = this.splitMetaValues(this.meta_field.val());
			
			this.meta_list.empty();
			for (var i=0; i<meta.length; i++) {
				li = $('<li>'+meta[i]+'</li>');
				a_remove = $('<a class="metaRemove" href="#">[x]</a>');
				a_remove.get(0).caller = this;
				a_remove.get(0).meta_id = meta[i];
				a_remove.click(function() {
					this.caller.removeMeta(this.meta_id);
					return false;
				});
				li.append('&nbsp;').append(a_remove);
				this.meta_list.append(li);
			}
		} else {
			var This = this;
			var params = {
				f: 'getMeta',
				metaType: this.meta_type,
				sortBy: 'metaId asc',
				postId: this.post_id
			};
			
			$.get(this.service_uri,params,function(data) {
				data = $(data);
				
				if (data.find('rsp').attr('status') != 'ok') { return; }
				
				This.meta_list.empty();
				data.find('meta').each(function() {
					var meta_id = $(this).text();
					li = $('<li><a href="' + This.meta_url + $(this).attr('uri') + '">'+meta_id+'</a></li>');
					a_remove = $('<a class="metaRemove" href="#">[x]</a>');
					a_remove.get(0).caller = This;
					a_remove.get(0).meta_id = meta_id;
					a_remove.click(function() {
						this.caller.removeMeta(this.meta_id);
						return false;
					});
					li.append('&nbsp;').append(a_remove);
					This.meta_list.append(li);
				});
			});
		}
	},
	
	addMetaDialog: function() {
		var This = this;
		
		// Meta dialog input
		this.meta_dialog.keypress(function(evt) { // We don't want to submit form!
			if (evt.keyCode == 13) {
				This.addMeta(this.value);
				return false;
			}
			return true;
		});
		
		var S = $('<input type="button" value="ok" />');
		S.click(function() {
			var v = This.meta_dialog.val();
			This.addMeta(v);
			return false;
		});
		
		this.target.append($('<p></p>').append(this.meta_dialog).append(' ').append(S));
		
		// View meta list
		var a = $('<a href="#">' + this.text_choose + '</a>');
		a.click(function() {
			This.showMetaList('small',$(this).parent());
			return false;
		});
		this.target.append($('<p></p>').append(a));
	},
	
	showMetaList: function(type,target) {
		target.empty();
		target.append('...');
		target.addClass('addMeta');
		
		var params = {
			f: 'getMeta',
			metaType: this.meta_type,
			sortBy: 'metaId,asc'
		};
		
		if (type == 'small') {
			params.limit = '15';
		} else if (type == 'more') {
			params.limit = '30';
		}
		
		var This = this;
		
		$.get(this.service_uri,params,function(data) {
			if ($(data).find('meta').length > 0) {
				target.empty();
				var meta_link;
				
				$(data).find('meta').each(function() {
					meta_link = $('<a href="#">' + $(this).text() + '</a>');
					meta_link.get(0).meta_id = $(this).text();
					meta_link.click(function() {
						var v = This.splitMetaValues(This.meta_dialog.val() + ',' + this.meta_id);
						This.meta_dialog.val(v.join(','));
						return false;
					});
					
					target.append(meta_link).append(', ');
				});
				
				if (type == 'small' || type == 'more') {
					var new_text = (type == 'more') ? This.text_all : This.text_more;
					var new_type = (type == 'more') ? 'all' : 'more';
					
					var a_more = $('<a href="#" class="metaGetMore"></a>');
					a_more.append(new_text + String.fromCharCode(160)+String.fromCharCode(187));
					a_more.click(function() {
						This.showMetaList(new_type,target);
						return false;
					});
					target.append(a_more);
				}
			} else {
				target.empty();
			}
		});
	},
	
	addMeta: function(str) {
		str = this.splitMetaValues(str).join(',');
		if (this.post_id == false) {
			str = this.splitMetaValues(this.meta_field.val() + ',' + str);
			this.meta_field.val(str);
			
			this.meta_dialog.val('');
			this.displayMetaList();
		} else {
			var params = {
				xd_check: dotclear.nonce,
				f: 'setPostMeta',
				postId: this.post_id,
				metaType: this.meta_type,
				meta: str
			};
			
			var This = this;
			$.post(this.service_uri,params,function(data) {
				if ($(data).find('rsp').attr('status') == 'ok') {
					This.meta_dialog.val('');
					This.displayMetaList();
				} else {
					alert($(data).find('message').text());
				}
			});
		}
	},
	
	removeMeta: function(meta_id) {
		if (this.post_id == false) {
			var meta = this.splitMetaValues(this.meta_field.val());
			for (var i=0; i<meta.length; i++) {
				if (meta[i] == meta_id) {
					meta.splice(i,1);
					break;
				}
			}
			this.meta_field.val(meta.join(','));
			this.displayMetaList();
		} else {
			var text_confirm_msg = this.text_confirm_remove.replace(/%s/,this.meta_type);
			
			if (window.confirm(text_confirm_msg)) {
				var This = this;
				var params = {
					xd_check: dotclear.nonce,
					f: 'delMeta',
					postId: this.post_id,
					metaId: meta_id,
					metaType: this.meta_type
				};
				
				$.post(this.service_uri,params,function(data) {
					if ($(data).find('rsp').attr('status') == 'ok') {
						This.displayMetaList();
					} else {
						alert($(data).find('message').text());
					}
				});
			}
		}
	},
	
	splitMetaValues: function(str) {
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
jsToolBar.prototype.elements.tag.fn.wiki = function() {
	this.encloseSelection('','',function(str) {
		if (str == '') { window.alert(dotclear.msg.no_selection); return ''; }
		if (str.indexOf(',') != -1) {
			return str;
		} else {
			window.dc_tag_editor.addMeta(str);
			return '['+str+'|tag:'+str+']';
		}
	});
};
jsToolBar.prototype.elements.tag.fn.xhtml = function() {
	var url = this.elements.tag.url;
	this.encloseSelection('','',function(str) {
		if (str == '') { window.alert(dotclear.msg.no_selection); return ''; }
		if (str.indexOf(',') != -1) {
			return str;
		} else {
			window.dc_tag_editor.addMeta(str);
			return '<a href="'+this.stripBaseURL(url+'/'+str)+'">'+str+'</a>';
		}
	});
};
jsToolBar.prototype.elements.tag.fn.wysiwyg = function() {
	var t = this.getSelectedText();
	
	if (t == '') { window.alert(dotclear.msg.no_selection); return; }
	if (t.indexOf(',') != -1) { return; }
	
	var n = this.getSelectedNode();
	var a = document.createElement('a');
	a.href = this.stripBaseURL(this.elements.tag.url+'/'+t);
	a.appendChild(n);
	this.insertNode(a);
	window.dc_tag_editor.addMeta(t);
};
