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
  
            // $.ajax({
            //     url: helperAjax.ajax_url,
            //     data: {
            //         action: 'validate_naics_code',
            //         nonce:helperAjax.nonce,
            //         naics_to_validate: $(this).val()
            //     },
            //     type: 'POST', 
            //     success: function (data) {
            //         if(data == 'Invalid Code'){
            //           $('input:focus').css('border-color','red')
            //           $('input:focus').next('.naic-info').html('Invalid NAICS code')
            //           $('input:focus').next('.naic-info').addClass('error')
            //           $('input:focus').next('.naic-info').removeClass('valid')
            //         }else{
            //             $('input:focus').css('border-color','green')
            //             //$('input').next('.naic-info').html(data)
            //             $('input:focus').next('.naic-info').html(data)
            //             $('input:focus').next('.naic-info').removeClass('error')
            //             $('input:focus').next('.naic-info').addClass('valid')

            //         }
                   
            //     }
            // });
            var container = $('.naic-suggested');
            $.ajax({
                url: helperAjax.ajax_url,
                data: {
                    action: 'suggest_naics_code',
                    nonce:helperAjax.nonce,
                    naics_to_validate: $(this).val()
                },
                type: 'POST', 
                success: function (data) {

                    container.show();
                    
                    $('input:focus').nextAll('.naic-suggested').html(data)
                    $('.naic-suggested li').on('click',function(){
                        $('.naics-input').val(null);
                        var number = $(this).data('naic');

                        let naics_data = '<section class="naics_remove"><div class="form-row  form-group"><div class="col"><input readonly value="' + number + '" id="mr_naics" class="form-control" type="number" name="naics_codes[]"></div><div class="col-2 d-flex align-items-start naics-codes-btn"><a href="#" class="remove-naics-code plus-btn">-</a></div></div></section>';

                        $('.naics-codes-dynamic').append(naics_data);
                    });
                    
                }
            });

            $(document).mouseup(function(e) 
            {
               // if the target of the click isn't the container nor a descendant of the container
               if (!container.is(e.target) && container.has(e.target).length === 0) 
               {
                   container.hide();
               }
           });


        });

  

        /* End Editing */
    });
})();
