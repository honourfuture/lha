(function(){

})();

$(function(){
	
	$(document.body).css("min-height",$(window).height());
	$(window).resize(function(){		
		$(document.body).css("min-height",$(window).height());
	});

});	