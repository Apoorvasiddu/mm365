(function() {
    "use strict";
    jQuery(document).ready(function($) { 
        //STARTS HERE
        $('.burger').on('click', function(){

            $(".founders-icons").addClass("hide_show_founders");

            $(".burger-btn").toggleClass("active");
            $(".homepop").toggleClass("showpop");

            $(".home-council-pop").removeClass("showcouncilpop");
            $(".hero").show();
            $(".council_icons").removeClass('nobg');
            $(".founders-icons").toggleClass("hide_show_founders");
        });

        $('.p-col a').on('click', function(){
            $(".homepop").removeClass("showpop");
            $(".home-council-pop").toggleClass("showcouncilpop");
            $(".hero").hide();
            $(".council_icons").toggleClass('nobg');
            $(".burger-btn").removeClass("active");
            $(".founders-icons").addClass('hide_show_founders');
            //Ajax here

            $.ajax({ 
                url : homeAjax.ajax_url,
                data : {
                    action:'show_councildata_in_home',
                    council_id:$(this).attr('data-council-id'),
                    nonce: homeAjax.nonce,
                },
                type: 'POST',       
                beforeSend: function() { 
                    $('#council-info-ajax-response').html("");
                    Notiflix.Loading.hourglass('Loading council deatails...',{svgColor:'#fff', backgroundColor: 'rgba(0,0,0,0.3)', messageColor:'#fff' });
                },         
                success : function( data ){
                    Notiflix.Loading.remove(10);
                  $('#council-info-ajax-response').html(data);
                }
             }); 
        

        });

       
        $('.close-content').on('click', function(){
            $(".burger-btn").removeClass("active");
            $(".homepop").removeClass("showpop");
            $(".home-council-pop").removeClass("showcouncilpop");
            $(".council_icons").removeClass('nobg');
            $(".hero").show();
            $('#council-info-ajax-response').html('');
            $(".founders-icons").removeClass('hide_show_founders');
        });


        $('.burger-btn .active').on('click', function(){
            $(".close-content").trigger("click");
        });



        //ENDS HERE
    });
  
})();