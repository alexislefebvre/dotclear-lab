$(function() {
	$('#paypal-area h3').toggleWithLegend($('#paypal'), {
		cookie: 'dcx_paypal_detail'
	});
	
	$("input[type=radio][name=PayPalButton_button_type]").click(function() {
		var p = $(this).attr('value');
		
		var btn_type = $("input[name=PayPalButton_button_type]").attr('value');
		var btn_size = $("input[name=PayPalButton_button_size]:checked").attr('value');
		var button_lang = $("input[name=button_lang]").attr('value');
		
		var s_button_names = $('input[name=button_names]').attr('value').split(",");
		var button_sizes_names = $("input[name=button_sizes_names]").attr('value').split(",");
		
		
		var btn_types = {
		'1' : 'buynow',
		'2' : 'donate',
		'3' : 'subscribe',
		'4' : 'gift',
		'5' : 'cart',
		'6' : 'saved'
		};
		
		$button_names = {
		'1' : s_button_names[0],
		'2' : s_button_names[1],
		'3' : s_button_names[2],
		'4' : s_button_names[3],
		'5' : s_button_names[4],
		'6' : s_button_names[5],
		};

		
		var btn_sizes = {
		'1' : 'SM',
		'2' : 'LG',
		'3' : 'CC_LG'
		};
		
		btn_type = btn_types[p];
		btn_name = $button_names[p];
		
		$("#first-image").attr('src','https://www.paypal.com/'+button_lang+'/i/btn/btn_'+btn_type+'_SM.gif');
		$("#first-image").attr('alt',''+btn_name+' - '+button_sizes_names[0]+'');
		
		$("#second-image").attr('src','https://www.paypal.com/'+button_lang+'/i/btn/btn_'+btn_type+'_LG.gif');
		$("#second-image").attr('alt',''+btn_name+' - '+button_sizes_names[1]+'');
		
		$("#third-image").attr('src','https://www.paypal.com/'+button_lang+'/i/btn/btn_'+btn_type+'CC_LG.gif');
		$("#third-image").attr('alt',''+btn_name+' - '+button_sizes_names[2]+'');
		
		
		if (btn_type == "cart") {
			if (btn_size == "CC_LG") {
				$("#PayPalButton_button_size-2").attr('checked', 'checked');
			}
			
			$("#third-image").attr('src','index.php?pf=PayPalButtons/no-preview.png');
			$("#third-image").attr('alt','');
			
			$("#PayPalButton_button_size-1").parents('p').show();
			$("#PayPalButton_button_size-2").parents('p').show();
			$("#PayPalButton_button_size-3").parents('p').hide();
			
		} else if (btn_type == "saved") {
			$("#first-image").attr('src','index.php?pf=PayPalButtons/no-preview.png');
			$("#first-image").attr('alt','');
			$("#second-image").attr('src','index.php?pf=PayPalButtons/no-preview.png');
			$("#second-image").attr('alt','');
			$("#third-image").attr('src','index.php?pf=PayPalButtons/no-preview.png');
			$("#third-image").attr('alt','');
			
			$("#PayPalButton_button_size-1").parents('p').hide();
			$("#PayPalButton_button_size-2").parents('p').hide();
			$("#PayPalButton_button_size-3").parents('p').hide();
			
		} else if ((btn_type == "buynow") || (btn_type == "donate") || (btn_type == "subscribe") || (btn_type == "gift")) {
			$("#PayPalButton_button_size-1").parents('p').show();
			$("#PayPalButton_button_size-2").parents('p').show();
			$("#PayPalButton_button_size-3").parents('p').show();
			
		}
	});
	
	
});