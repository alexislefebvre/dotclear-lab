$(document).ready(ofcInit);

function ofcInit() {
	$('#c_name').change(function(){});
}

function ofcSetState(k,v) {

	if (k=='name'){
		if (v) {
			$('#c_name').parent().show();
		}
		else {
			$('#c_name').parent().hide();
		}
	}
}