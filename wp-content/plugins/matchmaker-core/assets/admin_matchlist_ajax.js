(function () {
  "use strict";
  jQuery(document).ready(function ($) {
    /* Start Editing */


    $(document).ready(function () {
      //$.fn.DataTable.ext.pager.numbers_length = 3;

      if ($('#matchlist_admin').length > 0) {

        var table = $('#matchlist_admin').DataTable({
          responsive: true,
          "processing": true,
          "serverSide": true,
          'serverMethod': 'post',
          //"ajax": adminmatchlistAjax.ajaxurl,
          "ajax": {
            "url": adminmatchlistAjax.ajaxurl,
            "data": function (data) {
              data.council_filter = $('#councilFilter').val();
              data.action = 'mm365_matchrequests_admin_listing';
            }
          },
          "order": [[3, "desc"]],
          //"order":[],
          "pagingType": "first_last_numbers",
          //"pagingType": "input",
          "pageLength": 25,
          "columnDefs": [{
            "targets": 'no-sort',
            "orderable": false,
          }],
          "fnDrawCallback": function (oSettings) {
            if ($('#matchlist_admin tr').length <= 1) {
              $('.dataTables_paginate').hide();
              $('.dataTables_info').hide();

            } else {
              $('.dataTables_paginate').show();
              $('.dataTables_info').show();
            }
            if ($('#matchlist_admin .dataTables_empty').length == 1) {
              $('.dataTables_paginate').hide();
              $('.dataTables_info').hide();
            }

          },
          "language": {
            "lengthMenu": "Display _MENU_ match requests per page",
            "zeroRecords": "No match requests found",
            "info": "Showing page _PAGE_ of _PAGES_",
            "infoEmpty": "No Match Requests available",
            "infoFiltered": "(filtered from _MAX_ total records)"
          },
          oLanguage: { sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>" }
        });


        $('#councilFilter').change(function () {
          table.draw();
        });

        $('#matchlist_admin_filter label:last').append('<br/><small>Search using any of the column values</small>');
        $("#matchlist_admin_filter.dataTables_filter").prepend($("#councilFilter_label"));

      }

    });


    //Stop Editing
  });

})();