(function () {
	'use strict';

    jQuery(document).ready(function($) { 

	var $swipeTabsContainer = $('.swipe-tabs'),
		$swipeTabs = $('.swipe-tab'),
		$swipeTabsContentContainer = $('.swipe-tabs-container'),
		currentIndex = 0,
		activeTabClassName = 'active-tab';

	$swipeTabsContainer.on('init', function(event, slick) {
		$swipeTabsContentContainer.removeClass('invisible');
		$swipeTabsContainer.removeClass('invisible');

		currentIndex = slick.getCurrent();
		$swipeTabs.removeClass(activeTabClassName);
       	$('.swipe-tab[data-slick-index=' + currentIndex + ']').addClass(activeTabClassName);
	});

	$swipeTabsContainer.slick({
		slidesToShow: 5,
		slidesToScroll: 1,
		infinite: false,
		swipeToSlide: true,
		touchThreshold: 10,
		arrows: true,
		responsive: [
			{
			  breakpoint: 480,
			  settings: {
				slidesToShow: 1,
				slidesToScroll: 1,
			  }
			},
		]
	});

	$swipeTabsContentContainer.slick({
		asNavFor: $swipeTabsContainer,
		slidesToShow: 1,
		slidesToScroll: 1,
		adaptiveHeight: true,
		arrows: false,
		infinite: false,
		swipeToSlide: true,
        draggable: true,
		touchThreshold: 10
	});


	$swipeTabs.on('click', function(event) {
        // gets index of clicked tab
        currentIndex = $(this).data('slick-index');
        $swipeTabs.removeClass(activeTabClassName);
        $('.swipe-tab[data-slick-index=' + currentIndex +']').addClass(activeTabClassName);
        $swipeTabsContainer.slick('slickGoTo', currentIndex);
        $swipeTabsContentContainer.slick('slickGoTo', currentIndex);
    });

    //initializes slick navigation tabs swipe handler
    $swipeTabsContentContainer.on('swipe', function(event, slick, direction) {
    	currentIndex = $(this).slick('slickCurrentSlide');
		$swipeTabs.removeClass(activeTabClassName);
		$('.swipe-tab[data-slick-index=' + currentIndex + ']').addClass(activeTabClassName);
	});

	$(".rarr").on("click",function(e) { // Added a '.'
	        e.preventDefault();
			$swipeTabsContentContainer.slick('slickNext');	
			
			currentIndex = $swipeTabsContentContainer.slick('slickCurrentSlide');
		    $swipeTabs.removeClass(activeTabClassName);
		    $('.swipe-tab[data-slick-index=' + currentIndex + ']').addClass(activeTabClassName);

	});
	$(".larr").on("click",function(e) { // Added a '.'
	        e.preventDefault();
			$swipeTabsContentContainer.slick('slickPrev');		
			
			currentIndex = $swipeTabsContentContainer.slick('slickCurrentSlide');
		    $swipeTabs.removeClass(activeTabClassName);
		    $('.swipe-tab[data-slick-index=' + currentIndex + ']').addClass(activeTabClassName);
	});


});

})();