(function() {
    "use strict";
  jQuery(document).ready(function($) { 
    //STARTS HERE


/*------------------------------------------Search SUBMIT-------------------------------------- */

$('form#mm365_find_buyer').submit(function(e){
    e.preventDefault(); 
    var form = $(this)[0];
    var formdata = new FormData(form);
    formdata.append('action', 'find_buyer');
    formdata.append('nonce', manageBuyerAjax.nonce);
    //
    if ( $(this).parsley().isValid() ) { 
     
        $.ajax({ 
            url : manageBuyerAjax.ajax_url,
            data: formdata,
            type: 'POST',                   
            contentType: false,
            processData: false,
            beforeSend: function() {                   
                $('html, body').animate({ scrollTop: 0 }, 'slow');
                Notiflix.Loading.hourglass('Searching for buyers...',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });

            },
            success : function( data ){
              
              $('#buyer-data-table').html(data);
              Notiflix.Loading.remove(100);

                 //Initiate DataTables here
                 $('#superadmin_searchresult_buyers').DataTable({
                    responsive:true,
                    "processing": true,
                    "serverSide": false,
                    //"ajax": {url:certificationAjax.ajax_url, data:{'action':'superadmin_list_council_managersing', 'status':filter_stat, 'period':period}},
                    "pagingType": "first_last_numbers",
                    "order": [],
                    "columnDefs": [ {
                      "targets"  : 'no-sort',
                      "orderable": false,
                    }],
                    "fnDrawCallback": function(oSettings) {},
                    "language": {
                      "lengthMenu": "Display _MENU_ companies per page",
                      "zeroRecords": "No buyers found!",
                      "info": "Showing page _PAGE_ of _PAGES_",
                      "infoEmpty": "No buyers found!",
                      "infoFiltered": "(filtered from _MAX_ total records)"
                    },
                    oLanguage: {sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>"}
                  });
                

                //Toggle action goes here
                $('.dataTable').on("click",".user-lock-toggle",function(){
                 var user_id = $(this).data('userid');

                    $.ajax({
                        url : manageBuyerAjax.ajax_url,
                        data: {userid: user_id, nonce: manageBuyerAjax.nonce, action: 'toggle_status'},
                        type: 'POST', 
                        beforeSend: function() {                   
                            $('html, body').animate({ scrollTop: 0 }, 'slow');
                            Notiflix.Loading.hourglass('Changing status..',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });
                        },
                        success: function(resp){

                            Notiflix.Loading.remove(100);

                            if(resp == 'blocked'){
                            $('.'+ user_id +'-lock-status').html('BLOCKED');
                            $('.'+ user_id +'-lock-button').html('UNBLOCK');
                            $('.'+ user_id +'-lock-status').addClass('yes');
                            }else{
                            $('.'+ user_id +'-lock-status').html('ACTIVE'); 
                            $('.'+ user_id +'-lock-button').html('BLOCK');
                            $('.'+ user_id +'-lock-status').removeClass('yes');
                            }
                        }
                    });
                
                });



            }
        }); 


    }
});





    //ENDS HERE
});
  
})();