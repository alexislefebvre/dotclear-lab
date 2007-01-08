function $()
{
  var elements = new Array();

  for (var i = 0; i < arguments.length; i++) {
    var element = arguments[i];
    if (typeof element == 'string')
      element = document.getElementById(element);

    if (arguments.length == 1)
      return element;

    elements.push(element);
  }

  return elements;
}

Object.extend = function(destination, source) {
  for (property in source) {
    destination[property] = source[property];
  }
  return destination;
}

if (!window.Element) {
  var Element = new Object();
}

Object.extend(Element, {
  
  getHeight: function(element) {
    element = $(element);
    return element.offsetHeight;
  }

});

function getTotalHeight()
{
	if (self.innerHeight) {
		return parseInt(self.innerHeight);
	} else if (document.documentElement && document.documentElement.clientHeight) {
		return document.documentElement.clientHeight;
	} else if (document.body) {
		return document.body.clientHeight;
	}
}

init = function()
{
  
  if( Element.getHeight('content') < 925 ) {
    $('content').style.height = '925px';
  }
  
  var divs = $('blognav').getElementsByTagName('div');
  for(i=0;i<divs.length;i++) {
    if(i == 0) {
      if(i%2 != 1)
        divs[i].className = 'first-odd';
      else
        divs[i].className = 'first';
    } else if(i == divs.length - 1) {
      if(i%2 != 1)
        divs[i].className = 'last-odd';
      else
        divs[i].className = 'last';
    } else {
      if(i%2 != 1)
        divs[i].className = 'odd';
    }
  }

  var divs = $('blogextra').getElementsByTagName('div');
  for(i=0;i<divs.length;i++) {
    if(i == 0) {
      if(i%2 != 0)
        divs[i].className = 'first-odd';
      else
        divs[i].className = 'first';
    } else if(i == divs.length - 1) {
      if(i%2 != 0)
        divs[i].className = 'last-odd';
      else
        divs[i].className = 'last';
    } else {
      if(i%2 != 0)
        divs[i].className = 'odd';
    }
  }

}

window.onload = init
//window.onresize = init