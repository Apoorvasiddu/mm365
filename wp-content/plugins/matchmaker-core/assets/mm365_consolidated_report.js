(function() {
    "use strict";
  jQuery(document).ready(function($) { 
  /* Start Editing */




$("#download-report-consolidated").on( "click", function(e) {
    if ( $('form#mm365_generate_report_consolidated').parsley().isValid() ) { 
        $.fancybox.open(
            ['<div class="popnotice text-center"><div class="popnotice-downloading-box"><svg version="1.1" fill="#356ab3" xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 512 512" xml:space="preserve"><path d="M382.56 233.376A15.96 15.96 0 0 0 368 224h-64V16c0-8.832-7.168-16-16-16h-64c-8.832 0-16 7.168-16 16v208h-64a16.013 16.013 0 0 0-14.56 9.376c-2.624 5.728-1.6 12.416 2.528 17.152l112 128A15.946 15.946 0 0 0 256 384c4.608 0 8.992-2.016 12.032-5.472l112-128c4.16-4.704 5.12-11.424 2.528-17.152z"/><path d="M432 352v96H80v-96H16v128c0 17.696 14.336 32 32 32h416c17.696 0 32-14.304 32-32V352h-64z"/></svg></div><p>Report is being downloaded...</p></div>'],
            {
                afterShow : function( instance, current ) {
                //$('form#mm365_generate_report_comapny').submit();
                setTimeout( function() {$.fancybox.close(); },1600); // 3000 = 3 secs
                }
            }
        );
    }
});







  /* View report - Consolidated
  * Get all form items to local storage and redirect to view report
  * Read items from localstorage in template and show report
  */
  $('#company-view-report-consolidated').on("click",function (e){
     if ( $('form#mm365_generate_report_consolidated').parsley().isValid() ) { 
       e.preventDefault(); 
         localStorage.clear();
         var redirect_to = $(this).data('redirect');
        
         localStorage.consolidated_from_date = $('input[name=from_date]').val();
         localStorage.consolidated_to_date = $('input[name=to_date]').val();

         window.location = redirect_to;

        }
  });

/**
 * Get values and ajax request
 * 
 */

  if($('#viewreports_filtered_consolidated').length > 0){
    var from = localStorage.getItem("consolidated_from_date");
    var to   = localStorage.getItem("consolidated_to_date");

    $('#conrep_append_period_text').html(from + " to " + to);

    $.ajax({ 
        url : consolidatedrepAjax.ajax_url,
        data: {action: 'view_consolidated_report', from_date: from, to_date: to, nonce: consolidatedrepAjax.nonce},
        type: 'POST',                   
        success : function( data ){
           //console.log(data);
           $('#viewreports_filtered_consolidated > tbody:last-child').append(data);
        }
    }); 


  }




  //Stop Editing
  });
  
})();