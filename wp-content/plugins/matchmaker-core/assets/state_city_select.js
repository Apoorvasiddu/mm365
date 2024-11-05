(function() {
    "use strict";
  jQuery(document).ready(function($) { 
             // Load States and Cities based on country select - AJAX - For Copany Address Fields
            $('.country').on("change",function(e){
                var cid = $(this).val();
                var listing_mode = $(this).data('listingmode');
                if(listing_mode != ''){ var lm = listing_mode; }else{ lm = 'without_all'}
                $.ajax({ 
                    url : mm365_helper_Ajax.ajax_url,
                    data : {
                        action:'state_city_select',
                        country:cid,
                        identifier:'for_state',
                        mode: lm,
                        nonce: mm365_helper_Ajax.nonce,
                    },
                    type: 'POST', 
                    beforeSend: function() { 
                        $(".state").html('<option> Loading ...</option>');
                        $(".btn-primary").prop('disabled', true); // disable button
                    },                  
                    success : function( data ){
                        $(".btn-primary,.state_multi,.city").prop('disabled', false); // enable button
                        $(".state_multi").find('option').remove();
                        $(".city").empty().trigger('change');
                        if( data ) { 
                            $('.state').html(data);
                            var state = $('.state').val();
                            $.ajax({ 
                                url : mm365_state_city_select.ajax_url,
                                data : {
                                    action:'state_city_select',
                                    state:state,
                                    identifier:'for_cities',
                                    mode:'with_all',
                                    nonce: mm365_helper_Ajax.nonce,
                                },
                                type: 'POST',                   
                                success : function( data ){
                                    if( data ) { 
                                        $('.city').html(data);
                                    } 
                                    $('.stateError').css({"display": "block"});
                                    $('.cityError').css({"display": "block"});
                                }
                            }); 

                            /* Multiple */
                            $('.state_multi').html(data);
                            $.ajax({ 
                                url : statecity.ajaxurl,
                                data : {
                                    action:'state_city_select',
                                    state:state,
                                    identifier:'for_multi_cities',
                                    mode:'with_all',
                                    nonce: mm365_helper_Ajax.nonce,
                                },
                                type: 'POST',                   
                                success : function( data ){
                                    $(".city").prop('disabled', false);   
                                    $(".city").find('option').remove();      
                                    $(".city").empty();                          
                                    if( data ) { 
                                        $('.city').html(data);
                                    } 
                                }
                            }); 
                        }
                    }
                }); 

            });
            // Load Cities
            $('.state').on("change",function(e){
                var sid = $(this).val();
                var listing_mode = $(this).data('listingmode');
                if(listing_mode != ''){ var lm = listing_mode; }else{ lm = 'without_all'}
                $.ajax({ 
                    url : mm365_helper_Ajax.ajax_url,
                    data : {
                        action:'state_city_select',
                        state:sid,
                        identifier:'for_cities',
                        mode:lm,
                        nonce: mm365_helper_Ajax.nonce,
                    },
                    type: 'POST',  
                    beforeSend: function() { 
                        $(".city").html('<option> Loading ...</option>');
                        $(".btn-primary").prop('disabled', true); // disable button
                    },                   
                    success : function( data ){
                        $(".btn-primary").prop('disabled', false); // enable button
                        $('.cityError').css({"display": "block"});
                        if( data ) { 
                            $('.city').html(data);
                        } 
                    }
                }); 
            });


            //Load multiple cities
            $('.state_multi').on("change",function(e){
                
                var states = $(this).val();
                var cities = $('.city').val();

                var selected = $(e.target).val();
                if(selected.includes('all')){
                        $('.state_multi > option').prop("selected",false);
                        $('.state_multi').val();
                        $('.state_multi').val('all');
                };

                var listing_mode = $(this).data('listingmode');
                if(listing_mode != ''){ var lm = listing_mode; }else{ lm = 'without_all'}
                if(states != ''){
                    $.ajax({ 
                        url : mm365_helper_Ajax.ajax_url,
                        data : {
                            action:'state_city_select',
                            state:states,
                            city:cities,
                            identifier:'for_multi_cities',
                            mode:lm,
                            nonce: mm365_helper_Ajax.nonce,
                        },
                        type: 'POST',  
                        beforeSend: function() { 
                            $(".city").prop('disabled', true);
                            $(".btn-primary").prop('disabled', true); // disable button
                        },                   
                        success : function( data ){
                            $(".city").prop('disabled', false);
                            $(".city").find('option').remove();
                            $(".city").empty();
                            $(".btn-primary").prop('disabled', false); // enable button
                            if( data ) { 
                                $('.city').html(data);
                            } 
                        }
                    }); 
                }else{
                    //alert(states);
                    $(".city").empty().trigger('change');
                }

            });
          
          
  });
  
})();






