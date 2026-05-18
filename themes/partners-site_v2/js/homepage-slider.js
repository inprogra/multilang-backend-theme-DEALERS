$(document).ready(function () {
  
    var slider = $('body.home .owl-carousel-feuteredCar.owl-carousel');
    var windowWidth = $('body').width();
    var stagePad, newPadding;
    var staticWith = 1980;



    if (windowWidth > 1199){
        stagePad = 380;
    }else if (windowWidth < 1200){
        stagePad = 300;
    }else if (windowWidth < 768 ){
        stagePad = 0
    }
 

    if (stagePad > 0 ){
        newPadding = (windowWidth * stagePad)/staticWith;
    }else{
        newPadding = 0;
    }

    var sliderOptions = {
            items: 1,
            autoplayTimeout: 5000,
            nav: true,
            center: true,
            margin: 0,
            stagePadding:newPadding
        
        };

    if (slider.length && !slider.hasClass('owl-loaded')) {
        slider.owlCarousel(sliderOptions);
    }

     $(window).resize(function(){

        windowWidth = $('body').width();

        if (windowWidth > 1199){
            stagePad = 380;
        }else if (windowWidth > 768){
            stagePad = 150;
        }else if (windowWidth < 768 ){
            stagePad = 0
        }

        if (stagePad > 0 ){
            newPadding = (windowWidth * stagePad)/staticWith;
        }else{
            newPadding = 0;
        }
            
        slider.owlCarousel('destroy');
        slider.owlCarousel({
            items: 1,
            loop: true,
            dots: true,
            autoplay: false,
            autoplayTimeout: 5000,
            nav: true,
            center: true,
            margin: 0,
            stagePadding:newPadding
        
        });

     })
    

       $('.owl-carousel').on('click', '.owl-item', function(e) {
    var carousel = $('.owl-carousel').data('owl.carousel');
    var index = $(this).index(); 
    var realIndex = carousel.relative(index); 
    carousel.to(realIndex);
});



        // $('.custom-prev').on('click', function () {
        //     $slider.trigger('prev.owl.carousel');
        // });

        // $('.custom-next').on('click', function () {
        //     $slider.trigger('next.owl.carousel');
        // });
  
});