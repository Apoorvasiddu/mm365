(function() {
    "use strict";
  jQuery(document).ready(function($) { 
    //STARTS HERE

  $('#mc-block').hide();
  $('#service_type').on("change",function(e){
    if($(this).val() == 'seller'){
      $('#intassi-block, #mc-block').show();  $(".mm365-single").select2();
    }else{  $('#intassi-block, #mc-block').hide(); }
});

/*------------------------------------------Search and find-------------------------------------- */
$('#download_search_company').hide();
$('form#mm365_find_companies #search_company').on("click",function(e){
    e.preventDefault(); 
    
   $('form#mm365_find_companies').parsley().validate();

   $('#download_search_company').hide();

    var form = $('form#mm365_find_companies')[0];
    var formdata = new FormData(form);
    formdata.append('action', 'find_company');
    formdata.append('nonce', searchCompaniesAjax.nonce);

    if ( $('form#mm365_find_companies').parsley().isValid() ) { 
     
        $.ajax({ 
            url : searchCompaniesAjax.ajax_url,
            data: formdata,
            type: 'POST',                   
            contentType: false,
            processData: false,
            beforeSend: function() {                   
                $('html, body').animate({ scrollTop: 0 }, 'slow');
                Notiflix.Loading.hourglass('Searching for companies...',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });
            },
            success : function( data ){

              if(data != 'no-match'){

                 //Show download button only when there are results
                 $('#download_search_company').show();
                 $('#companies-data-table').html(data);
                 Notiflix.Loading.remove(100);

                 //Initiate DataTables here
                 $('#superadmin_searchresult_companies').DataTable({
                    responsive:true,
                    "processing": true,
                    "serverSide": false,
                    //"ajax": {url:certificationAjax.ajax_url, data:{'action':'superadmin_list_council_managersing', 'status':filter_stat, 'period':period}},
                    "pagingType": "first_last_numbers",
                    "order": [],
                    "columnDefs": [{
                      "targets"  : 'no-sort',
                      "orderable": false,
                    }],
                    "fnDrawCallback": function(oSettings) {},
                    "language": {
                      "lengthMenu": "Display _MENU_ companies per page",
                      "zeroRecords": "No companies found!",
                      "info": "Showing page _PAGE_ of _PAGES_",
                      "infoEmpty": "No companies found!",
                      "infoFiltered": "(filtered from _MAX_ total records)"
                    },
                    oLanguage: {sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>"}
                 });

                 //Removes  download button if user has changed the form input after generating result once
                 $("input, select").change(function(){
                  $('#download_search_company').hide();
                });

                 

              }else{
                Notiflix.Loading.remove(100);
                $('#companies-data-table').html('<h4 class="text-center">No companies found!</h4>');
              }
                

            }
        }); 


    }
});






/*---------------------------- */


    //ENDS HERE
});
  
})();