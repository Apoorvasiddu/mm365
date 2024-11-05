(function () {
  "use strict";
  jQuery(document).ready(function ($) {
    /* Start Editing */


    $(document).ready(function () {


      $.fn.dataTable.ext.errMode = 'throw';
      //Companies
      if ($('#viewreports_filtered_matchrequests_admin').length > 0) {

        var from = localStorage.getItem("mrv_from_date");
        var to = localStorage.getItem("mrv_to_date");
        var match_status = localStorage.getItem("mrv_matchstatus");

        //continue from here


        $('#append_period_text_mr').html(from + " to " + to);
        switch (match_status) {
          case 'auto-approved':
            var kind_label = 'auto approved';
            break;
          case 'nomatch':
            var kind_label = 'without match';
            break;
          case 'all':
            var kind_label = '';
            break;
          default:
            var kind_label = match_status;
            break;
        }
        $('#append_kind_text_mr').html(kind_label);

        var Table = $('#viewreports_filtered_matchrequests_admin').DataTable({
          responsive: true,
          "processing": true,
          "serverSide": true,
          "ajax": {
            "url": adminViewReportMatchFilteredAjax.ajaxurl,
            "data": function (data) {
                data.action = 'mm365_admin_viewreport_match_filtered_listing',
                data.from_date = localStorage.getItem("mrv_from_date"),
                data.to_date = localStorage.getItem("mrv_to_date"),
                data.certifications = localStorage.getItem("mrv_certifications").split(","),
                data.other_certification = localStorage.getItem("mrv_certifications_oth"),
                data.industry = localStorage.getItem("mrv_industries").split(","),
                data.other_industry = localStorage.getItem("mrv_industries_oth"),
                data.services = localStorage.getItem("mrv_services").split(","),
                data.other_services = localStorage.getItem("mrv_services_oth"),
                data.number_of_employees = localStorage.getItem("mrv_numberofemployees"),
                data.match_status = localStorage.getItem("mrv_matchstatus"),
                data.closure_filter = localStorage.getItem("mrv_closure_filter"),
                data.minority_category = localStorage.getItem("mrv_minoritycategory"),
                data.size_of_company = localStorage.getItem("mrv_sizeofcompany"),
                data.service_required_countries = localStorage.getItem("mrv_country"),
                data.service_required_states = localStorage.getItem("mrv_state"),
                data.international_assistance = localStorage.getItem("mrv_intassi").split(","),
                data.naics_codes = localStorage.getItem("mrv_naics").split(","),
                data.council = localStorage.getItem("mrv_council"),
                data.buyer_team = localStorage.getItem("mrv_buyer_team")
            }
          },
          "order": [],
          "pagingType": "first_last_numbers",
          //"pagingType": "input",
          "columnDefs": [
            {
              "targets": 'no-sort',
              "orderable": false,
            }

          ],
          "fnDrawCallback": function (oSettings) {
            if ($('#viewreports_filtered_matchrequests_admin tr').length <= 1) {
              $('.dataTables_paginate').hide();
              $('.dataTables_info').hide();

            } else {
              $('.dataTables_paginate').show();
              $('.dataTables_info').show();
            }
            if ($('#viewreports_filtered_matchrequests_admin .dataTables_empty').length == 1) {
              $('.dataTables_paginate').hide();
              $('.dataTables_info').hide();
            }
            //Hide more results
            $(".matched-companies-list").each(function () {
              var lis = $(this).find('li');
              if (lis.length > 3) {
                $(this).find('li').hide().filter(':lt(3)').show();
                $(this).append('<li style="list-style:none"><div class="three-dots"><small>More</small></div></li>').find('li:last').click(function () {
                  $(this).find('.three-dots small').toggleClass("active");
                  if ($(this).find('.three-dots small').text() == "More")
                    $(this).find('.three-dots small').text("Less")
                  else $(this).find('.three-dots small').text("More");

                  $(this).siblings(':gt(2)').toggle();
                });
              }
            });



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

        $('#viewreports_filtered_matchrequests_admin input').unbind();
        $('#viewreports_filtered_matchrequests_admin input').bind('keyup', function (e) {
          if (e.keyCode == 13) {
            Table.fnFilter($(this).val());
          }
        });

        //hide column
        var match_status = localStorage.getItem("mrv_matchstatus");
        if (match_status == 'auto-approved') {
          Table.column(8).visible(false);
        }
        if (match_status != 'completed' && match_status != 'cancelled') {
          Table.column(5).visible(false);
          Table.column(6).visible(false);
        }


        $('#viewreports_filtered_matchrequests_admin_filter label:last').append('<br/><small>Search using any of the column values</small>');
        $("#viewreports_filtered_matchrequests_admin_filter.dataTables_filter").prepend($("#councilFilter_label"));

        //  var filterTerm;

        // $('#councilFilter').on('change', function() {
        //   filterTerm = this.value.trim();
        //   //var term = '^((?!' + filterTerm + ').)*$';
        //   var term = filterTerm;
        //   Table.search(term, true, false, true).draw();
        //   this.value = filterTerm;
        // });

        // Table.on( 'draw', function () {
        //   $('#councilFilter').val( filterTerm );
        // });

        $('#councilFilter').on('change', function () {
          //$('#viewreports_filtered_companies_admin').data('sacouncilfilter',$(this).val());
          localStorage.mrv_council = $('#councilFilter').val();
          Table.draw();
        });


      }

    });


    //Stop Editing
  });

})();