//[Master Javascript]

//Project:	Travel club - Onepage Html Responsive Template
//Version:	1.1
//Last change:	28/04/2017 [fixed bug]
//Primary use:	Travel club - Onepage Html Responsive Template 


//Template script here

$(document).ready(function(){
    "use strict"; // Start of use strict

    // jQuery for page scrolling feature - requires jQuery Easing plugin
    $('a.page-scroll').on('click', function(event) {
        var $anchor = $(this);
        $('html, body').stop().animate({
            scrollTop: ($($anchor.attr('href')).offset().top - 50)
        }, 1250, 'easeInOutExpo');
        event.preventDefault();
    });

    // Highlight the top nav as scrolling occurs
    $('body').scrollspy({
        target: '.navbar-fixed-top',
        offset: 51
    });

	// Closes the Responsive Menu on Menu Item Click
	$('.navbar-collapse ul li a').on('click', function(event) {
		$(this).closest('.collapse').collapse('toggle');
	});

    // Offset for Main Navigation
    $('#mainNav').affix({
        offset: {
            top: 100
        }
    })


    // Initialize and Configure Scroll Reveal Animation
	
    window.sr = ScrollReveal();
    sr.reveal('.sr-icons', {
        duration: 600,
        scale: 0.3,
        distance: '0px'
    }, 200);
    sr.reveal('.sr-button', {
        duration: 1000,
        delay: 200
    });
    sr.reveal('.sr-contact', {
        duration: 600,
        scale: 0.3,
        distance: '0px'
    }, 300);

   
	//Script to Activate the Carousel

	$('.carousel').carousel({
		interval: 5000 //changes the speed
	})
	
	// prettyPhoto

	$("a[rel^='alternate']").prettyPhoto();
	
   
}); // End of use strict
