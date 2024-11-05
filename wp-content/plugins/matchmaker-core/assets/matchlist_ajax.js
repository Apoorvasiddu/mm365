(function () {
  "use strict";
  jQuery(document).ready(function ($) {
    /* Start Editing */


    $(document).ready(function () {
      if ($('#matchlist').length > 0) {


        $('#matchlist').DataTable({
          responsive: true,
          "processing": true,
          "serverSide": true,
          "ajax": matchlistAjax.ajaxurl,
          "ajax": {
            "url":matchlistAjax.ajaxurl, 
            "data":function(data) {
              data.action = 'match_listing'
            }
          },
          "pagingType": "first_last_numbers",
          "order": [[1, "desc"]],
          //"order": [],
          "columnDefs": [{
            "targets": 'no-sort',
            "orderable": false,
          }],
          "fnDrawCallback": function (oSettings) {
            if ($('#matchlist tr').length <= 1) {
              $('.dataTables_paginate').hide();
              $('.dataTables_info').hide();

            } else {
              $('.dataTables_paginate').show();
              $('.dataTables_info').show();
            }
            if ($('#matchlist .dataTables_empty').length == 1) {
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

        $('#matchlist_filter label').after('<br/><small>Search using any of the column values</small>');
      }



    });

    //Stop Editing
  });

})();