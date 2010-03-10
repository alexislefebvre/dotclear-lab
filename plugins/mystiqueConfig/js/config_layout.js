
var  scale = {
	'fixed': ['0', '|', '|', '60', '|', '|', '|', '140', '|', '|', '|', '220', '|', '|', '|', '300', '|', '|', '|', '380', '|', '|', '|', '460', '|', '|', '|', '540', '|', '|', '|', '620', '|', '|', '|', '700', '|', '|', '|', '780', '|', '|', '|', '860', '|', '|', '|', '940'],
	'fluid': ['0', '|', '10', '|', '20', '|', '30', '|', '40', '|','50', '|', '60', '|', '70', '|', '80', '|', '90', '|', '100']},
	unit = {'fixed' : 'px', 'fluid' : '%'},
	gs = {'fixed' : 940, 'fluid' : 100},
	jstep = {'fixed' : 10, 'fluid' : 1},
	preview_css_dir,
	
	width_type,color_scheme, layout,bg_color;

function mystique_set_slider() {
	layout = $('[name=layout]:checked').val();
	width_type = $('[name=width-type]:checked').val();
	$("#slider .jslider").remove();
	if (layout == 'col-1') {
		$("#dimension-control").hide();
		return;
	}
	$("#dimension-control").show();
	$("#column_widths").val(layout_info[layout][width_type]);
	$("#slider input").slider({
        from: 0,
		to: gs[width_type],
		step: jstep[width_type],
		dimension: unit[width_type],
		scale: scale[width_type],
		limits: false,
		onstatechange: function(value){ 
			if (!dynamic_preview)
				return;
			var s = value.split(';');

			s[0] = parseInt(s[0]);
			s[1] = parseInt(s[1]);

			switch(layout){
			case 'col-2-left':
				$('#primary-content',previewframe.document).css(
					{'width': gs[width_type]-s[0]+unit[width_type], 'left': s[0]+unit[width_type]});
				$('#sidebar',previewframe.document).css(
					{'width': s[0]+unit[width_type], 'left': -(gs[width_type]-s[0])+unit[width_type], 'right': 'auto'});
				break;
			case 'col-2-right':
				$('#primary-content',previewframe.document).css(
					{'width': gs[width_type]-(gs[width_type]-s[0])+unit[width_type], 'left': '0'});
				$('#sidebar',previewframe.document).css(
					{'width': gs[width_type]-s[0]+unit[width_type], 'right': '0', 'left': 'auto'});
				break;
			case 'col-3':
				$('#primary-content',previewframe.document).css(
					{'width': (gs[width_type]-s[0]-(gs[width_type]-s[1]))+unit[width_type], 
					'left': s[0]+unit[width_type], 'right': 'auto'});
				$('#sidebar',previewframe.document).css(
					{'width': gs[width_type]-s[1]+unit[width_type], 'right': '0', 'left': 'auto'});
				$('#sidebar2',previewframe.document).css(
					{'width': s[0]+unit[width_type], 'left': (-(gs[width_type]-s[0]-(gs[width_type]-s[1])))+unit[width_type], 
					'right': 'auto'});
				break;
			case 'col-3-left':
				$('#primary-content',previewframe.document).css(
					{'width': (gs[width_type]-s[1])+unit[width_type], 'left': (s[1])+unit[width_type], 'right': 'auto'});
				$('#sidebar',previewframe.document).css(
					{'width': s[0]+unit[width_type], 'left': -(gs[width_type]-s[0])+unit[width_type], 'right': 'auto'});
				$('#sidebar2',previewframe.document).css(
					{'width': (s[1]-s[0])+unit[width_type], 'left': -(gs[width_type]-s[1])+s[0]+unit[width_type], 'right': 'auto'});
				break;
			case 'col-3-right':
				$('#primary-content',previewframe.document).css(
					{'width': s[0]+unit[width_type], 'left': '0', 'right': 'auto'});
				$('#sidebar',previewframe.document).css(
					{'width': (gs[width_type]-s[1])+unit[width_type], 'left': '0', 'right': 'auto'});
				$('#sidebar2',previewframe.document).css(
					{'width': (s[1]-s[0])+unit[width_type], 'left': '0', 'right': 'auto'});
				break;
			}

			return value;
			}
	});
}

function mystique_set_color_scheme() {
	color_scheme = $('[name=color-scheme]:checked').val();
	if (dynamic_preview)
		$('#style-color-scheme',previewframe.document).attr('href',preview_css_dir+'/color-'+color_scheme+'.css');
}

function mystique_set_width() {
	mystique_set_slider();
	if (dynamic_preview) {
		$('body',previewframe.document).removeClass('fluid fixed');
		$('body',previewframe.document).addClass(width_type);
	}
}

function mystique_set_layout() {
	mystique_set_slider();
	if (dynamic_preview) {
		$('body',previewframe.document).removeClass('col-1 col-2-left col-2-right col-3 col-3-left col-3-right');
		$('body',previewframe.document).addClass(layout);
	}
}
function mystique_set_bg_color() {
	bg_color = $('[name=bg-color]').val();
	if (dynamic_preview) {
		$('body',previewframe.document).css("background-color",bg_color);
	}
}
$(function() {
	layout = $('[name=layout]:checked').val();
	width_type = $('[name=width-type]:checked').val();
	color_scheme = $('[name=color-scheme]:checked').val();
	bg_color = $('[name=bg-coolor]').val();
	
	//$('.radioimg input[type=radio]').hide();
	$('div.radioimg	input[type=radio]').change(function() {
		$(this).parents('div.radioimg').find('.selected').removeClass('selected');
		$(this).prev().toggleClass('selected');
	});
	$('div.radioimg label').click(function() {
		$(this).siblings('input').attr('checked',true).trigger('change');
	});
	$(".radioimg input[type=radio]").hide();

	
	$("#previewframe").load(function() {
		$("#previewframe").src=document.location.href;
	
		if (dynamic_preview) {
			var preview_css_url= $('#style-color-scheme',previewframe.document).attr('href');
			preview_css_dir = preview_css_url.replace(/(mystique)\/[^\/]*$/,'$1');
		}
		mystique_set_slider();
		$('input[name=width-type]').change(function() {
			mystique_set_width();
		});
		$('input[name=layout]').change(function() {
			mystique_set_layout();
		});
		$('input[name=color-scheme]').change(function() {
			mystique_set_color_scheme();
		});
		$('input[name=bg-color]').change(function() {
			mystique_set_bg_color();
		});

	});
	
});

