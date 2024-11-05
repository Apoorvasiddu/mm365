(function () {
  "use strict";
  jQuery(document).ready(function ($) {
    /* Start Editing */
    $(document).ready(function () {

      /**
       * Showing downloading message
       * 
       * 
       */
      $('#download-report').on('click', function(){
        Notiflix.Notify.info('Downloading report...');
      });


      /**
       * View report - Company
       * Get all form items to local storage and redirect to view report
       * Read items from lc in template and show report
       */
      $('#company-view-report-filtered').on("click", function (e) {
        if ($('form#mm365_generate_report_comapny').parsley().isValid()) {
          e.preventDefault();
          localStorage.clear();
          var vals = $('input[name="naics_codes[]"]').map(function () { return this.value }).get();
          var redirect_to = $(this).data('redirect');

          localStorage.crv_from_date = $('input[name=from_date]').val();
          localStorage.crv_to_date = $('input[name=to_date]').val();
          localStorage.crv_services = $('#services').val();
          localStorage.crv_services_oth = $('#other_services_input').val();
          localStorage.crv_industries = $('#industry').val();
          localStorage.crv_industries_oth = $('#other_industry_input').val();
          localStorage.crv_servicetype = $('#service_type').val();
          localStorage.crv_country = $('.country').val();
          localStorage.crv_state = $('.state').val();
          localStorage.crv_city = $('.city').val();
          localStorage.crv_numberofemployees = $('select[name=number_of_employees]').val();
          localStorage.crv_sizeofcompany = $('select[name=size_of_company]').val();
          localStorage.crv_certifications = $('#certifications').val();
          localStorage.crv_certifications_oth = $('#other_certification_input').val();
          localStorage.crv_naics = vals;
          localStorage.crv_intassi = $('#international_assistance').val();

          localStorage.crv_council = $('#councilFilter').val();
          localStorage.crv_mrcode = $('#minority_category').val();

          window.location = redirect_to;


        }
      });





      $.fn.dataTable.ext.errMode = 'throw';
      //Companies
      if ($('#viewreports_filtered_companies_admin').length > 0) {

        var from = localStorage.getItem("crv_from_date");
        var to = localStorage.getItem("crv_to_date");

        //continue from here
        if (from != '' && to != '') {
          $('#append_period_text').html(" between " + from + " to " + to);
        }


        var table =
          $('#viewreports_filtered_companies_admin').DataTable({
            responsive: true,
            "processing": true,
            "serverSide": true,
            "ajax": {
              "url": adminViewReportFilteredAjax.ajaxurl,
              "data": function (data) {
                data.action = 'mm365_admin_viewreport_companies_filtered',
                  data.from = localStorage.getItem("crv_from_date"),
                  data.to = localStorage.getItem("crv_to_date"),
                  data.certifications = localStorage.getItem("crv_certifications").split(","),
                  data.other_certification = localStorage.getItem("crv_certifications_oth"),
                  data.industry = localStorage.getItem("crv_industries").split(","),
                  data.other_industry = localStorage.getItem("crv_industries_oth"),
                  data.services = localStorage.getItem("crv_services").split(","),
                  data.other_services = localStorage.getItem("crv_services_oth"),
                  data.number_of_employees = localStorage.getItem("crv_numberofemployees"),
                  data.service_type = localStorage.getItem("crv_servicetype"),
                  data.company_size = localStorage.getItem("crv_sizeofcompany"),
                  data.company_country = localStorage.getItem("crv_country"),
                  data.company_city = localStorage.getItem("crv_city"),
                  data.company_state = localStorage.getItem("crv_state"),
                  data.international_assistance = localStorage.getItem("crv_intassi").split(","),
                  data.naics_codes = localStorage.getItem("crv_naics").split(","),
                  data.council_id = localStorage.getItem("crv_council"),
                  data.minority_category = localStorage.getItem("crv_mrcode")
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
              if ($('#viewreports_filtered_companies_admin tr').length <= 1) {
                $('.dataTables_paginate').hide();
                $('.dataTables_info').hide();

              } else {
                $('.dataTables_paginate').show();
                $('.dataTables_info').show();
              }
              if ($('#viewreports_filtered_companies_admin .dataTables_empty').length == 1) {
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



        $('#viewreports_filtered_companies_admin_filter label:last').append('<br/><small>Search using any of the column values</small>');

        //$("#viewreports_companies_admin_filter.dataTables_filter").prepend($("#councilFilter"));
        $("#viewreports_filtered_companies_admin_filter.dataTables_filter").prepend($("#councilFilter_label"));

        $('#councilFilter').on('change', function () {
          //$('#viewreports_filtered_companies_admin').data('sacouncilfilter',$(this).val());
          localStorage.crv_council = $('#councilFilter').val();
          table.draw();
        });

      }


    });


    //Stop Editing
  });

})();