jQuery(document).ready(function($) {
       
    // Support for CLF Collab
    if ($('.opened').parents().hasClass('accordion-body')) {
    	$('.opened').parentsUntil(".subpages-navi").addClass('in');
    	$(".accordion-heading").removeClass("in");
    }
    
    $('.opened').parents().children(".accordion-heading").addClass("opened").parent().closest(".accordion-heading").addClass("opened");
    
});

(function($) {

    $( document ).ready(function() {

        // Toggle aria-expanded attribute on droptown menu caret when button state changed. including click and enter pressed when the button has focus.
        var dropdownToggles = $('.subpages-navi-widget .accordion-toggle');

        dropdownToggles.on( 'click', function( event ) {
            var toggle = event.target;
            $( toggle ).attr( 'aria-expanded', toggle.getAttribute( 'aria-expanded' ) === 'true' ? 'false' : 'true' );
        } );
    });
    
}(jQuery));