jQuery(document).ready(function($) {
       
    // Support for CLF Collab
    if ($('.opened').parents().hasClass('accordion-body')) {
    	$('.opened').parentsUntil(".subpages-navi").addClass('in');
    	$(".accordion-heading").removeClass("in");
    }
    
    $('.opened').parents().children(".accordion-heading").addClass("opened").parent().closest(".accordion-heading").addClass("opened");
    
});
