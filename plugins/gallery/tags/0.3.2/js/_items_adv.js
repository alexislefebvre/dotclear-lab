/* Advanced options for js */
var filter= {};
var imgContainer;
var imageList=[];
var nbItemsPerLine=0;
var defaults= {
	icons: {"image": 'images/media/image.png'},
	thumbs : {
		'sq': 'images/media/image.png',
		't': 'images/media/image.png',
		's': 'images/media/image.png',
		'm': 'images/media/image.png'}};
var configuration= {
	maxItemsPerLine: 0,
	maxItemsPerCol: 0,
	maxItems: 0
}
var totalItems;


function selection(list) {
	this.selected = [];
}
function imgItem(id) {
	this.id=id;
	this.component = $('<div class="item invisible" id="'+id+'"><img class="icon" src="'+defaults.thumbs['sq']+'" alt="blank"/></div>');
	this.component.bind('dblclick',this,this.editImage);
	this.component.bind('click',this,this.toggleSelectImage);

}

imgItem.prototype = {
	id:"",
	post: { id:0, url:"", title:"" },
	media: { id: 0, url: "", thumbs: defaults.thumbs},
	selected: false,
	component: $(),
	editImage: function(event) {
		var This=event.data;
		$('#details-thumb').attr('src',This.media.thumbs['s']);
		$('#details-title').empty();
		$('#details-title').text(This.post.title);
	},
	updateFromXml: function(item) {
		var This=this;
		This.post = {id: item.attr('id'),url: item.attr('url'),title: item.attr('title')};
		This.media = {id: item.attr('media_id'),url:item.attr('media_url'),type:item.attr('type')};
		This.media.thumbs = {
			'sq': 'images/media/image.png',
			't': 'images/media/image.png',
			's': 'images/media/image.png',
			'm': 'images/media/image.png'};
		item.find("thumb").each(function() {
			This.media.thumbs[$(this).attr('size')] = $(this).attr('url');
		});
		This.component.find('.icon').attr('src',This.media.thumbs['sq']);
		This.component.find('.icon').attr('alt',This.post.title);
		This.component.removeClass('invisible');
	},
	toggleSelectImage: function(event) {
		var This=event.data;
		This.selected=!This.selected;
		This.component.toggleClass("selected");
	}
}


function editImage(event) {
	var This=event.data;
	$('#details-thumb').attr('src',This.thumbs['s']);
	$('#details-title').empty();
	$('#details-title').text(image.title);
	
	
}
function toggleSelectImage(event) {
	var image=event.data;
	image.selected=!image.selected;
	image.component.toggleClass("selected");
}

function updateImageListFromXml(data) {
	var pos=0;
	if ($(data).find('rsp').attr('status')== 'ok') {
		imageList = [];
		var img = $(data).find('image');
		img.each(function() {
			items[pos++].updateFromXml($(this));
		});
	} else {
		imgContainer.append("<p>Error</p>");
	}

}

function updateCountsAndGetImages(data) {
	if ($(data).find('rsp').attr('status')== 'ok') {
		totalItems = $(data).find('images').attr('count');
		$("#items-footer").append(totalItems);
		}
	var params = filter;
	params['f'] = 'galGetImages';
	params['limit'] = configuration.maxItems;
	delete (params['count']);
	$.get("services.php", params,updateImageListFromXml,"xml");
}
function getImagesCount() {
	var params = filter;
	params['f'] = 'galGetImages';
	params['count']=1;
	$.get("services.php", params,updateCountsAndGetImages,"xml");
}

$(window).load(function() {
	totalItems = 0;
	imgContainer = $('#all-items');
	configuration.maxItemsPerLine = parseInt(imgContainer.innerWidth()/60);
	configuration.maxItemsPerCol = 5;
	configuration.maxItems = configuration.maxItemsPerLine * configuration.maxItemsPerCol;

	items=new Array(configuration.maxItems);
	for (var i=0; i<items.length; i++) {
		items[i] = new imgItem ("item"+i);
		imgContainer.append(items[i].component);
	}
	getImagesCount();
});
