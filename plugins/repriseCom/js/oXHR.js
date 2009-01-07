function getXMLHttpRequest() {
	var xhr;
	if(window.XMLHttpRequest || window.ActiveXObject) {
		if(window.XMLHttpRequest) {
			xhr = new XMLHttpRequest(); 
		}
		else { // Internet Explorer <7
			try {
				xhr = new ActiveXObject("Msxml2.XMLHTTP");
			} catch(e) {
				xhr = new ActiveXObject("Microsoft.XMLHTTP");
			}
		}
	}
	else {
		alert("Votre navigateur ne supporte pas l'objet XMLHTTPRequest...");
		return;
	}
}
