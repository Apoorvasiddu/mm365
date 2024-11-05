(function() {
    "use strict";
  jQuery(document).ready(function($) { 
    //STARTS HERE

    /**--------------------------------------------------
     * Participating buyers
     * 
     * 
     ----------------------------------------------------*/
    if( $( '#participating_buyers' ).length > 0 ) {
    $( function() {
        $( '#participating_buyers' ).select2( {
            ajax: {
                url: confCouncilAjax.ajax_url,
                dataType: 'json',
                delay: 250,
                data: function( params ) {
                    return {
                        q: params.term,
                        action: 'get_council_buyer_companies'
                    };
                },
                processResults: function( data ) {
                    var options = [];
                    if( data ) {
                     // console.log(data);
                        $.each( data, function( index, text ) {
                            options.push( { id: text[0], text: text[1] } );
                        });
                    }
                    return {
                        results: options
                    };
                },
                cache: true
            },
            minimumInputLength: 2,
            width: '100%',
            escapeMarkup: function (text) { return text; }
        } );
    });
  }


  /**--------------------------------------------------------
   * Council Managers
   * Search and add
   * 
   ----------------------------------------------------------*/
      if( $( '#fellow_council_managers' ).length > 0 ) {
        $( function() {
            $( '#fellow_council_managers' ).select2( {
                ajax: {
                    url: confCouncilAjax.ajax_url,
                    dataType: 'json',
                    delay: 250,
                    data: function( params ) {
                        return {
                            q: params.term,
                            action: 'get_fellow_council_manager'
                        };
                    },
                    processResults: function( data ) {
                        var options = [];
                        if( data ) {
                         // console.log(data);
                            $.each( data, function( index, text ) {
                                options.push( { id: text[0], text: text[1] } );
                            });
                        }
                        return {
                            results: options
                        };
                    },
                    cache: true
                },
                minimumInputLength: 2,
                width: '100%',
                escapeMarkup: function (text) { return text; }
            } );
        });
      }


/**---------------------------------------------------------------
 * Create conference
 * 
 * 
 -----------------------------------------------------------------*/
            $('form#mm365_council_add_conference').submit(function(e){
                e.preventDefault(); 
                //var prp_timezone = $('#proposer_timezone').val();
                var redirect_to = $('#after_schedule_redirect').val();
                var form        = $('form')[0];
                var formdata    = new FormData(form);
                formdata.append('action', 'council_create_offline_conference');
                formdata.append('nonce',confCouncilAjax.nonce);
                //formdata.append('proposer_timezone', prp_timezone);
                var count_naics = $("input[name='naics_codes[]']").length;
                if(count_naics == 0 ){
                    Notiflix.Report.failure(
                        'NAICS code is required',
                        'Please select atleast one NAICS code to continue. You can search select the code from "Find NAICS codes" field',
                        'OK',
                        );
                        
                }

                if ( $(this).parsley().isValid() && count_naics > 0 && $(this).parsley().isValid()) { 
                    $.ajax({ 
                        url : confCouncilAjax.ajax_url,
                        data: formdata,
                        type: 'POST',                   
                        contentType: false,
                        processData: false,
                        beforeSend: function() { 
                            $('html, body').animate({ scrollTop: 0 }, 'slow');
                            Notiflix.Loading.hourglass('Adding conference...',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });

                        },
                        success : function( data ){
                            if( data ) { 
                               $('html, body').animate({ scrollTop: 0 }, 0);
                                if(data){
                                    Notiflix.Loading.remove(1500);
                                    Notiflix.Notify.success('Conference created.');
                                    $('#mm365_council_add_conference').hide();
                                    //Show preview
                                    $('#preview_conference').html(data);
                                  }else{
                                    Notiflix.Loading.remove(1500);
                                    Notiflix.Notify.failure('Unable to create conference!');
                                  }
                               
                            } else {
                                Notiflix.Notify.failure('Unknown error!');
                            }
                        }
                    }); 
               }
  
            });
  
/**--------------------------------------------------------------
 * Update conference
 * 
 * 
 ----------------------------------------------------------------*/
 $('form#mm365_council_update_conference').submit(function(e){
    e.preventDefault(); 
    //var prp_timezone = $('#proposer_timezone').val();
    //var redirect_to = $('#after_schedule_redirect').val();
    var form        = $('form')[0];
    var formdata    = new FormData(form);
    formdata.append('action', 'council_update_offline_conference');
    formdata.append('nonce',confCouncilAjax.nonce);

    var count_naics = $("input[name='naics_codes[]']").length;
    if(count_naics == 0 ){
        Notiflix.Report.failure(
            'NAICS code is required',
            'Please select atleast one NAICS code to continue. You can search select the code from "Find NAICS codes" field',
            'OK',
            );
            
    }

    //formdata.append('proposer_timezone', prp_timezone);
    if ( $(this).parsley().isValid() ) { 
        $.ajax({ 
            url : confCouncilAjax.ajax_url,
            data: formdata,
            type: 'POST',                   
            contentType: false,
            processData: false,
            beforeSend: function() { 
                $('html, body').animate({ scrollTop: 0 }, 'slow');
                Notiflix.Loading.hourglass('Updating conference...',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });

            },
            success : function( data ){
                if( data ) { 
                   $('html, body').animate({ scrollTop: 0 }, 0);
                    if(data){
                        Notiflix.Loading.remove(1500);
                        Notiflix.Notify.success('Conference updated.');
                        $('#mm365_council_update_conference').hide();
                        //Show preview
                        $('#preview_conference').html(data);
                      }else{
                        Notiflix.Loading.remove(1500);
                        Notiflix.Notify.failure('Unable to update conference!');
                      }
                   
                } else {
                    Notiflix.Notify.failure('Unknown error!');
                }
            }
        }); 
   }

});


/**-----------------------------
 * List conferences
 -------------------------------*/

if($('#cm_list_conferences').length > 0)
{

    $('#cm_list_conferences').DataTable({
      responsive:true,
      "processing": true,
      "serverSide": false,
      "pagingType": "first_last_numbers",
      "order": [],
      "columnDefs": [ {
        "targets"  : 'no-sort',
        "orderable": false,
      }],
      "fnDrawCallback": function(oSettings) {
      
      },
      "language": {
        "lengthMenu": "Display _MENU_ conferences per page",
        "zeroRecords": "No Conferences",
        "info": "Showing page _PAGE_ of _PAGES_",
        "infoEmpty": "There are no conferences",
        "infoFiltered": "(filtered from _MAX_ total records)"
      },
      oLanguage: {sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>"}
    });
    $('#cm_list_conferences_filter label').after('<br/><small>Search using any of the column values</small>');
}

    /**-----------------------------
     * Act on paricipation request
     * Accpet or reject particiaption
     -------------------------------*/

    $('.councilAcceptParticipation').on("click", function(){
        var application_id = $(this).data('application_id');
        var redirect_to = $(this).data('redirect_to');

        Notiflix.Confirm.init({
            titleColor: '#356ab3',
            okButtonBackground: '#00cc00',
            cancelButtonBackground: '#ff0000',
        });


        Notiflix.Confirm.show(

            'Conference Participation Request',
            'Accept the application?',
            'Yes',
            'No',
            () => {
                applicationAction(application_id, redirect_to, 'accepted' ,'')
            },
            () => {
                //alert('If you say so...');
            },
                
            );

        //;
    });

    $('.councilRejectParticipation').on("click", function(){
        var application_id = $(this).data('application_id');
        var redirect_to = $(this).data('redirect_to');
        //

            $('#pop_application_id').val(application_id);

            $.fancybox.open({
              src : '#rejectConfParticipationApplicationForm',
              type: 'inline',
              touch: false,
              smallBtn : true,
            });


            $('form#mm365_reject_offline_conf_particiaption').submit(function(e){
                e.preventDefault(); 
                var form        = $(this)[0];
                var formData    = new FormData(form);
                var reject_message = formData.get("cause_of_rejection");
                //console.log(reject_message);
                applicationAction(application_id, redirect_to, 'rejected', reject_message );
            });
 
      


    });


    function applicationAction(application_id, redirect_to, act, message){

        if(act == 'accepted'){
            var msg = "accepted";
        }else {
            var msg = "rejected";
        }

        $.ajax({ 
         url : confCouncilAjax.ajax_url,
         data: {
           action: 'process_particiaption_request',
           application_id: application_id,
           nonce: confCouncilAjax.nonce,
           message: message,
           act: act
         },   
         type: 'POST',                
         beforeSend: function() { 
             $('html, body').animate({ scrollTop: 0 }, 'slow');
             Notiflix.Loading.hourglass('Processing participation request...',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });
         },
         success : function( data ){
             if( data) { 
                 $('html, body').animate({ scrollTop: 0 }, 0);
                 if(data){
                     Notiflix.Loading.remove(1500);
                     Notiflix.Notify.success('Supplier participation ' + msg + '.');
                     setTimeout(() => {
                         window.location.reload();
                     }, 1600);
                   }
             } else {
                Notiflix.Loading.remove(1500);
                Notiflix.Notify.failure('Unknown error!');
             }
         }
        });

    }


      //ENDS HERE
    });
})();