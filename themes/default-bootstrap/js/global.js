
//global variables
var responsiveflag = false;
	
	
$(document).ready(function(){
var elem = document.getElementById('mySwipe');
window.mySwipe = Swipe(elem, {
  // startSlide: 4,
  // auto: 3000,
  // continuous: true,
  // disableScroll: true,
  // stopPropagation: true,
  // callback: function(index, element) {},
  // transitionEnd: function(index, element) {}
});
		
});

function highdpiInit()
{
	
}


// Used to compensante Chrome/Safari bug (they don't care about scroll bar for width)
function scrollCompensate()
{
	
}

function responsiveResize()
{
	
}

function blockHover(status)
{
	
}

function quick_view()
{
	
}

function bindGrid()
{
	
}

function display(view)
{
	
}

function dropDown()
{
	

	
}

function accordionFooter(status)
{

}

function accordion(status)
{

}

function bindUniform()
{
	if (!!$.prototype.uniform)
		$("select.form-control,input[type='radio'],input[type='checkbox']").not(".not_uniform").uniform();
}

