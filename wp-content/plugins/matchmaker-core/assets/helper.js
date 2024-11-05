(function () {
    "use strict";
    jQuery(document).ready(function ($) {
        /* Start Editing */

        /**
         * 
         * Naics Code Validation
         * 
         */
        // $('input[name^="naics_codes"]').on('keyup',function(e){
        //     //console.log($(this).val())
        
            // $.ajax({
            //     url: helperAjax.ajax_url,
            //     data: {
            //         action: 'validate_naics_code',
            //         nonce:helperAjax.nonce,
            //         naics_to_validate: $(this).val()
            //     },
            //     type: 'POST', 
            //     success: function (data) {
            //         if(data != null){
            //             console.log(data)
            //         }else{
            //             console.log('in valid')
            //         }
            //     }
            // });

        
        // });

        var toValidate = $("#basicSearchFields");
        //$("#clearBasicSearch").removeClass('hidden').removeClass('button').attr('disabled', true);

        toValidate.on('keyup','input', function () {
  
            $.ajax({
                url: helperAjax.ajax_url,
                data: {
                    action: 'validate_naics_code',
                    nonce:helperAjax.nonce,
                    naics_to_validate: $(this).val()
                },
                type: 'POST', 
                success: function (data) {
                    if(data == 'Invalid Code'){
                      $('input:focus').css('border-color','red')
                      $('input:focus').next('.naic-info').html('Invalid NAICS code')
                      $('input:focus').next('.naic-info').addClass('error')
                      $('input:focus').next('.naic-info').removeClass('valid')
                    }else{
                        $('input:focus').css('border-color','green')
                        //$('input').next('.naic-info').html(data)
                        $('input:focus').next('.naic-info').html(data)
                        $('input:focus').next('.naic-info').removeClass('error')
                        $('input:focus').next('.naic-info').addClass('valid')

                    }
                   
                }
            });

            $.ajax({
                url: helperAjax.ajax_url,
                data: {
                    action: 'suggest_naics_code',
                    nonce:helperAjax.nonce,
                    naics_to_validate: $(this).val()
                },
                type: 'POST', 
                success: function (data) {
                    $('input:focus').nextAll('.naic-suggested').html(data)
                    
                }
            });


        });

        //Auto Complete
        // $(".naics-input").autocomplete({
        //       source: function( request, response ) {
        //         $.ajax({
        //           url: helperAjax.ajax_url,
        //           dataType: "jsonp",
        //           data: {
        //             q: request.term,
        //             action: 'suggest_naics_code',
        //           },
        //           success: function( data ) {
        //             response( data );
        //           }
        //         });
        //       },
        //       minLength: 3,
        //       select: function( event, ui ) {
              
        //       },
        //       open: function() {
        //         $( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
        //       },
        //       close: function() {
        //         $( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
        //       }
        //     });
        
  

        /* End Editing */
    });
})();
