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

  $('wrapper').style.height = 'auto';
  var occupedHeight = Element.getHeight('top') + Element.getHeight('footer');
  var newMainHeight = getTotalHeight() - occupedHeight;
  if(newMainHeight < Element.getHeight('wrapper')) {
		newMainHeight = Element.getHeight('wrapper');
	}
	$('wrapper').style.height = newMainHeight + 'px';

}

window.onload = init
window.onresize = init