(function() {
    "use strict";
  jQuery(document).ready(function($) { 
    //STARTS HERE

/**
 * Date
 * 
 */

  if($(".from_date_tdy").length > 0){
          // $(".from_date_tdy").flatpickr(
          //   {
          //     enableTime: false,
          //     minDate: "today",
          //     disableMobile: "true",
          //     dateFormat: "m/d/Y",
          //     plugins: [new rangePlugin({ input: "#secondRangeInputTDY"})],
          // });

          $(".from_date_tdy").flatpickr(
            {
              enableTime: false,
              minDate: "today",
              disableMobile: "true",
              dateFormat: "m/d/Y",
              plugins: [new rangePlugin({ input: "#secondRangeInputTDY"})],
              onChange: function (selectedDates, dateStr, instance) {
                if (selectedDates.length > 1) {
                    var range = instance.formatDate(selectedDates[1], 'U') - instance.formatDate(selectedDates[0], 'U');
                    range = range / 86400;
                    if(range < 7)
                    {
                        Notiflix.Notify.failure('Minimum subscription duration is 7 days',{textColor:'#000000'});
                        instance.clear()
                    }
                }
            },
          });

  }



/**
  * 
  * Reset Update subscription form on change 
  * of council and company type
  * 
  */
$("input[name='service_type']").on("change", function(){
  $('#suppliers_to_subscribe').val(null).trigger('change');
});

/**
  * 
  * Search and add suppliers for adding subscriptions
  * 
  */

  if( $( '#suppliers_to_subscribe' ).length > 0 ) {
    $( function() {
        $( '#suppliers_to_subscribe' ).select2( {
            ajax: {
                url: subscriptionAjax.ajax_url,
                dataType: 'json',
                delay: 250,
                data: function( params ) {
                    return {
                        q: params.term,
                        council: $('#council').val(),
                        service_type: $("input[name='service_type']:checked").val(),
                        action: 'subscription_get_companies',
                        nonce: subscriptionAjax.nonce
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

/**
  * 
  * Update subscription data
  * 
  * 
  */

 $('form#mm365_update_subscription').submit(function(e){
    e.preventDefault(); 
    var redirect_to = $('#after_success_redirect').val();
    var form        = $('form')[0];
    var formdata    = new FormData(form);
    formdata.append('action', 'update_subscriptions');
    formdata.append('nonce',subscriptionAjax.nonce);
    if ( $(this).parsley().isValid() ) { 

        $.ajax({ 
            url : subscriptionAjax.ajax_url,
            data: formdata,
            type: 'POST',                   
            contentType: false,
            processData: false,
            beforeSend: function() { 
                $('html, body').animate({ scrollTop: 0 }, 'slow');
                Notiflix.Loading.hourglass('Updating subscrpitions...',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });
            },
            success : function( data ){

                if(data == 'success'){

                    Notiflix.Loading.remove(1800);
                    Notiflix.Notify.success('Subscriptions updated successfully!',() => { window.location = redirect_to } );
                    setTimeout(() => {
                      window.location = redirect_to; 
                    }, 1800);

                  }else{
                    $.confirm({
                      title:  'Unable to update subscription',
                      content: "Please check the input values",
                      type: 'red',
                      typeAnimated: true,
                      icon: 'fas fa-exclamation-circle',
                      theme: 'modern',
                      buttons: {
                        close: {
                          btnClass: 'btn btn-primary',
                          action: function(){
                            $('.loader-wrapper').hide();
                          }
                        }
                      }
                    });

                  }

            }
        });
    }
  });

/**
  * 
  * report fields conditionals
  * 
  * 
  */
 $('#subscription_date_between_block').hide();
 $('input[name=subscription_status]').change(function(){
    var value = $( 'input[name=subscription_status]:checked' ).val();
    if(value != 'Active'){
      $('#subscription_date_between_block').hide();
    }else{
      $('#subscription_date_between_block').show();
    }
  });


  /**
   * Generate Report
   * 
   * 
   */
   $('#subscription_report_generate').on("click",function (e){
    if ( $('form#mm365_subscription_report').parsley().isValid() ) { 
        e.preventDefault(); 
        localStorage.clear();
        var redirect_to = $(this).data('redirect');
       
        localStorage.subscription_council_id = $('#council').val();
        localStorage.subscription_service_type = $('input[name=service_type]:checked').val();
        localStorage.subscription_subscription_type = $("#subscription_type").val();
        localStorage.subscription_status = $("input[name='subscription_status']:checked").val();
        localStorage.subscription_start_date = $('input[name=from_date]').val();
        localStorage.subscription_end_date = $('input[name=to_date]').val();

        window.location = redirect_to;

       }
   });

   /**
    * Show report
    * 
    */
    if($('#view_reports_subscription').length > 0){

      var council_id = localStorage.getItem("subscription_council_id");
      var service_type = localStorage.getItem("subscription_service_type");
      var subscription_type = localStorage.getItem("subscription_subscription_type");
      var status = localStorage.getItem("subscription_status");
      var start_date = localStorage.getItem("subscription_start_date");
      var end_date = localStorage.getItem("subscription_end_date");


      var table  = $('#view_reports_subscription').DataTable({
        responsive:true,
        "processing": true,
        "serverSide": true,
        'serverMethod': 'post',
        "searching": false,
        "ajax": {
          url:subscriptionAjax.ajax_url, 
          "data":function(data) {
            data.action = 'subscription_report', 
            data.nonce = subscriptionAjax.nonce, 
            data.council_id = council_id,
            data.service_type = service_type,
            data.subscription_type = subscription_type,
            data.status = status,
            data.start_date = start_date,
            data.end_date = end_date
          }
        },
        "pagingType": "first_last_numbers",
        "order": [],
        "columnDefs": [ {
          "targets"  : 'no-sort',
          "orderable": false,
        }],
        "fnDrawCallback": function(oSettings) {
          if ($('#view_reports_subscription tr').length <= 1) {
              $('.dataTables_paginate').hide();
              $('.dataTables_info').hide();
              
          }else{
            $('.dataTables_paginate').show();
            $('.dataTables_info').show();
          }
          if ($('#view_reports_subscription .dataTables_empty').length == 1) {
            $('.dataTables_paginate').hide();
            $('.dataTables_info').hide();
          }
  
  
        },
        "language": {
          "lengthMenu": "Display _MENU_ details per page",
          "zeroRecords": "No details found",
          "info": "Showing page _PAGE_ of _PAGES_",
          "infoEmpty": "There are no details",
          "infoFiltered": "(filtered from _MAX_ total records)"
        },
        oLanguage: {sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>"}
      });
  
  
      //var filterTerm;
      $('#councilFilter').on('change', function() {
          $('#view_reports_subscription').data('sacouncilfilter',$(this).val());
          table.draw();
      });
  
      $('#view_reports_subscription_filter label:last').append('<br/><small>Search using any of the column values</small>');
      $("#view_reports_subscription_filter.dataTables_filter").prepend($("#councilFilter_label"));


    }
   



    //ENDS HERE
});
})();