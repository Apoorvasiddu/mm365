(function() {
    "use strict";
  jQuery(document).ready(function($) { 
    //STARTS HERE



  /**
  * 
  * MBE Occurance List
  * 
  */


//    if($('#mbeoccurance_list_councils').length > 0)
//    {
//        $('#mbeoccurance_list_councils').DataTable({
//          responsive:true,
//          "processing": true,
//          "serverSide": false,
//          "pagingType": "first_last_numbers",
//          "iDisplayLength": 50,
//          "order": [[ 1, "desc" ]],
//          "columnDefs": [ {
//            "targets"  : 'no-sort',
//            "orderable": false,
//          }],
//          "fnDrawCallback": function(oSettings) {},
//          "language": {
//            "lengthMenu": "Display _MENU_ companies per page",
//            "zeroRecords": "No Companies",
//            "info": "Showing page _PAGE_ of _PAGES_",
//            "infoEmpty": "There are no companies",
//            "infoFiltered": "(filtered from _MAX_ total records)"
//          },
//          oLanguage: {sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>"}
//        });
//        $('#mbeoccurance_list_councils_filter label').after('<br/><small>Search using any of the column values</small>');
//    }


    if($('#mbeoccurance_list_councils').length > 0)
    {
        var filter_stat   = $('#mbeoccurance_list_councils').data('statfilter');
        var period        = $('#mbeoccurance_list_councils').data('period');
    
    
        //SA filtering with council - get the id of counil
        var sa_council_filter   = $('#mbeoccurance_list_councils').data('sacouncilfilter');
    
        var table  = $('#mbeoccurance_list_councils').DataTable({
          responsive:true,
          "processing": true,
          "serverSide": true,
          'serverMethod': 'post',
          "ajax": {
            url:mbeOccurrenceAjax.ajax_url, 
            "data":function(data) {
              data.action = 'mbe_occurrence_listing', 
              data.sa_council_filter = $('#mbeoccurance_list_councils').data('sacouncilfilter')
            }
          },
          "pagingType": "first_last_numbers",
          "iDisplayLength": 25,
          "order": [],
          "columnDefs": [ {
            "targets"  : 'no-sort',
            "orderable": false,
          }],
          "fnDrawCallback": function(oSettings) {
            if ($('#mbeoccurance_list_councils tr').length <= 1) {
                $('.dataTables_paginate').hide();
                $('.dataTables_info').hide();
                
            }else{
              $('.dataTables_paginate').show();
              $('.dataTables_info').show();
            }
            if ($('#mbeoccurance_list_councils .dataTables_empty').length == 1) {
              $('.dataTables_paginate').hide();
              $('.dataTables_info').hide();
            }
    
    
          },
          "language": {
            "lengthMenu": "Display _MENU_ companies per page",
            "zeroRecords": "No companies found",
            "info": "Showing page _PAGE_ of _PAGES_",
            "infoEmpty": "There are no companies",
            "infoFiltered": "(filtered from _MAX_ total records)"
          },
          oLanguage: {sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>"}
        });

        //var filterTerm;
        $('#councilFilter').on('change', function() {
            $('#mbeoccurance_list_councils').data('sacouncilfilter',$(this).val());
            table.draw();
        });
    
        $('#mbeoccurance_list_councils_filter label:last').append('<br/><small>Search using any of the column values</small>');
        $("#mbeoccurance_list_councils_filter.dataTables_filter").prepend($("#councilFilter_label"));
    
    }


    if($('#mbeoccurance_details').length > 0)
    {
      //List the match requests with which the company is appeared as match

      var table  = $('#mbeoccurance_details').DataTable({
        responsive:true,
        "processing": true,
        "serverSide": true,
        'serverMethod': 'post',
        "searching": false,
        "ajax": {
          url:mbeOccurrenceAjax.ajax_url, 
          "data":function(data) {
            data.action = 'mbe_occurrence_details', 
            data.cmp_id = $('#mbeoccurance_details').data('company_id'),
            data.nonce  = mbeOccurrenceAjax.nonce
          }
        },
        "pagingType": "first_last_numbers",
        "iDisplayLength": 25,
        "order": [],
        "columnDefs": [ {
          "targets"  : 'no-sort',
          "orderable": false,
        }],
        "fnDrawCallback": function(oSettings) {
          if ($('#mbeoccurance_details tr').length <= 1) {
              $('.dataTables_paginate').hide();
              $('.dataTables_info').hide();
              
          }else{
            $('.dataTables_paginate').show();
            $('.dataTables_info').show();
          }
          if ($('#mbeoccurance_details .dataTables_empty').length == 1) {
            $('.dataTables_paginate').hide();
            $('.dataTables_info').hide();
          }
        },
        "language": {
          "lengthMenu": "Display _MENU_ match requests per page",
          "zeroRecords": "No match requests found",
          "info": "Showing page _PAGE_ of _PAGES_",
          "infoEmpty": "There are no match requests",
          "infoFiltered": "(filtered from _MAX_ total records)"
        },
        oLanguage: {sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>"}
      });

      //var filterTerm;
      $('#councilFilter').on('change', function() {
          $('#mbeoccurance_details_councils').data('sacouncilfilter',$(this).val());
          table.draw();
      });
  
      $('#mbeoccurance_details_councils_filter label:last').append('<br/><small>Search using any of the column values</small>');
      $("#mbeoccurance_details_councils_filter.dataTables_filter").prepend($("#councilFilter_label"));

    }




    //ENDS HERE
});
  
})();