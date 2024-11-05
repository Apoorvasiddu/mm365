(function () {
  "use strict";
  jQuery(document).ready(function ($) {
    /* Start Editing */


    $(document).ready(function () {
      $.fn.dataTable.ext.errMode = 'throw';
      //Companies
      if ($('#viewreports_companies_admin').length > 0) {

        var table = $('#viewreports_companies_admin').DataTable({
          responsive: true,
          "processing": true,
          "serverSide": true,
          "ajax": {
            url: adminViewReportAjax.ajaxurl,
            "data": function (data) {
              data.action = 'mm365_admin_viewreport_companies',
                data.companymeta = $('#viewreports_companies_admin').data('matchmeta'),
                data.period = $('#viewreports_companies_admin').data('period'),
                data.sa_council_filter = $('#viewreports_companies_admin').data('sacouncilfilter')
            }
          },


          "order": [],
          "pagingType": "first_last_numbers",
          //"pagingType": "input",
          "columnDefs": [{
            "targets": 'no-sort',
            "orderable": false,
          }],
          "fnDrawCallback": function (oSettings) {
            if ($('#viewreports_companies_admin tr').length <= 1) {
              $('.dataTables_paginate').hide();
              $('.dataTables_info').hide();

            } else {
              $('.dataTables_paginate').show();
              $('.dataTables_info').show();
            }
            if ($('#viewreports_companies_admin .dataTables_empty').length == 1) {
              $('.dataTables_paginate').hide();
              $('.dataTables_info').hide();
            }



          },
          "language": {
            "lengthMenu": "Display _MENU_ companies per page",
            "zeroRecords": "No companies found",
            "info": "Showing page _PAGE_ of _PAGES_",
            "infoEmpty": "No companies available",
            "infoFiltered": "(filtered from _MAX_ total records)"
          },
          oLanguage: { sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>" }
        });



        $('#councilFilter').on('change', function () {
          $('#viewreports_companies_admin').data('sacouncilfilter', $(this).val());
          table.draw();
        });


        $('#viewreports_companies_admin_filter label:last').append('<br/><small>Search using any of the column values</small>');

        //$("#viewreports_companies_admin_filter.dataTables_filter").prepend($("#councilFilter"));
        $("#viewreports_companies_admin_filter.dataTables_filter").prepend($("#councilFilter_label"));


      }

    });

    //Stop Editing
  });

})();