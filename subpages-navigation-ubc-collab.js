jQuery(document).ready(function($) {
       
    // Support for CLF Collab
    if ($('.opened').parents().hasClass('accordion-body'))
    	$('.opened').parents().addClass('in');
});
