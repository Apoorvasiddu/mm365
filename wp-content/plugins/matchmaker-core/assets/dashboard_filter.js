(function() {
    "use strict";
  jQuery(document).ready(function($) { 
/* Start Editing */

//Initiate on period change
            $("#mm365_card_filter_select").on( "change", function() {
                var periodValue = $(this).val();

                //For council managers
                var cm_council_id = $(this).data('council_id');

                //For super admin
                if(cm_council_id == ''){
                    if($('#dash-councilFilter').length > 0){
                        var sa_council_id_filtering = $('#dash-councilFilter').val();
                    }
                    var council_id = sa_council_id_filtering;
                }else{
                    var council_id = cm_council_id;
                }
                dashboard_status_cards(periodValue, council_id);
              
            });

//Initiate on council change
      if($('#dash-councilFilter').length > 0){
            $("#dash-councilFilter").on( "change", function() {
                var periodValue = $("#mm365_card_filter_select").val();

                //For super admin
                var sa_council_id_filtering = $(this).val();

                dashboard_status_cards(periodValue, sa_council_id_filtering);
            
            });
        }


//
function dashboard_status_cards(periodValue, council_id){


    $.ajax({ 
        url : dashboardFilter.ajaxurl,
        data: {action: 'mm365_dashboard_status_cards', period: periodValue, council_id: council_id},
        type: 'POST',        
        beforeSend: function() { 
            Notiflix.Notify.info('Updating status cards');
        },             
        success : function( data ){
            if( data ) { 
                $('.report-cards').html(data);
                $(".dash-report-btn").on( "click", function(e) {
                    $.fancybox.open(
                        ['<div class="popnotice text-center"><div class="popnotice-downloading-box"><svg version="1.1" fill="#356ab3" xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 512 512" xml:space="preserve"><path d="M382.56 233.376A15.96 15.96 0 0 0 368 224h-64V16c0-8.832-7.168-16-16-16h-64c-8.832 0-16 7.168-16 16v208h-64a16.013 16.013 0 0 0-14.56 9.376c-2.624 5.728-1.6 12.416 2.528 17.152l112 128A15.946 15.946 0 0 0 256 384c4.608 0 8.992-2.016 12.032-5.472l112-128c4.16-4.704 5.12-11.424 2.528-17.152z"/><path d="M432 352v96H80v-96H16v128c0 17.696 14.336 32 32 32h416c17.696 0 32-14.304 32-32V352h-64z"/></svg></div><p>Report is being downloaded...</p></div>'],
                        {
                            afterShow : function( instance, current ) {
                            //$('form#mm365_generate_report_comapny').submit();
                            setTimeout( function() {$.fancybox.close(); },1600); // 3000 = 3 secs
                            }
                        }
                    );
                  });
            }
        }
    }); 

}



/* End Editing */
  });
})();
