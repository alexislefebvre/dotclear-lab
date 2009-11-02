$(document).ready(function(){
	for (var i=0; i< subscribers.length; i++) {
		var action_id = addLine(processid,subscribers[i], dotclear.msg.send_letters+" : "+subscribers[i], dotclear.msg.please_wait);
		actions.push ({line_id: action_id, params: {f: "sendSubscriberLetter", userId: subscribers[i]}});
	}
	
	doProcess();
});
