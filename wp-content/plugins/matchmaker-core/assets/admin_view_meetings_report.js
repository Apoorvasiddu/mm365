(function () {
  "use strict";
  jQuery(document).ready(function ($) {
    /* Start Editing */


    $(document).ready(function () {
      $.fn.dataTable.ext.errMode = 'throw';

      var today = new Date();
      var timezone_offset_minutes = new Date().getTimezoneOffset();
      timezone_offset_minutes = timezone_offset_minutes == 0 ? 0 : -timezone_offset_minutes;
      var jan = new Date(today.getFullYear(), 0, 1);
      var jul = new Date(today.getFullYear(), 6, 1);
      var dst = today.getTimezoneOffset() < Math.max(jan.getTimezoneOffset(), jul.getTimezoneOffset());

      //meetings
      if ($('#viewreports_meetings_admin').length > 0) {
        var period = $('#viewreports_meetings_admin').data('period');

        

        //SA filtering with council - get the id of counil
        var sa_council_filter = $('#viewreports_meetings_admin').data('sacouncilfilter');

        var table =
          $('#viewreports_meetings_admin').DataTable({
            responsive: true,
            "processing": true,
            "serverSide": true,
            "ajax": {
              url: adminViewReportMeetingsAjax.ajax_url,
              data: {
                'action': 'mm365_meetings_reports_view',
                'period': period,
                'sa_council_filter': sa_council_filter,
                'timezone': timezone_offset_minutes,
                'offset': (-today.getTimezoneOffset() / 60),
                'dst': (+dst),
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
              if ($('#viewreports_meetings_admin tr').length <= 1) {
                $('.dataTables_paginate').hide();
                $('.dataTables_info').hide();

              } else {
                $('.dataTables_paginate').show();
                $('.dataTables_info').show();
              }
              if ($('#viewreports_meetings_admin .dataTables_empty').length == 1) {
                $('.dataTables_paginate').hide();
                $('.dataTables_info').hide();
              }
            },
            "language": {
              "lengthMenu": "Display _MENU_ meetings per page",
              "zeroRecords": "No meetings found",
              "info": "Showing page _PAGE_ of _PAGES_",
              "infoEmpty": "No meetings available",
              "infoFiltered": "(filtered from _MAX_ total records)"
            },
            oLanguage: { sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>" }
          });

        $('#viewreports_meetings_admin input').unbind();
        $('#viewreports_meetings_admin input').bind('keyup', function (e) {
          if (e.keyCode == 13) {
            Table.fnFilter($(this).val());
          }
        });



        //$("#viewreports_companies_admin_filter.dataTables_filter").prepend($("#councilFilter"));
        $('#viewreports_meetings_admin_filter label:last').append('<br/><small>Search using any of the column values except date values</small>');
        $("#viewreports_meetings_admin_filter.dataTables_filter").prepend($("#councilFilter_label"));

        var filterTerm;

        $('#councilFilter').on('change', function () {
          filterTerm = this.value.trim();
          //var term = '^((?!' + filterTerm + ').)*$';
          var term = filterTerm;
          table.search(term, true, false, true).draw();
          this.value = filterTerm;
        });

        table.on('draw', function () {
          $('#councilFilter').val(filterTerm);
        });
      }


    });


    //Stop Editing
  });

})();