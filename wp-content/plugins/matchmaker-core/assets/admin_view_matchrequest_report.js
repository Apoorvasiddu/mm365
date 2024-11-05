(function() {
  "use strict";
jQuery(document).ready(function($) { 
/* Start Editing */


$(document).ready(function() {
  //$.fn.DataTable.ext.pager.numbers_length = 3;

  if($('#viewreports_matchlist_admin').length > 0){

        var meta   = $('#viewreports_matchlist_admin').data('matchmeta');

        var table  =  
        $('#viewreports_matchlist_admin').DataTable({
          responsive:true,
          "processing": true,
          "serverSide": true,

          //"ajax": {url:adminViewReportMatchAjax.ajaxurl, data:{'period':period, 'meta':meta, 'sa_council_filter': sa_council_filter}},
          "ajax": {
            url:adminViewReportMatchAjax.ajaxurl, 
            "data":function(data) {
              data.action = 'mm365_admin_viewreport_match_listing',
              data.meta = $('#viewreports_matchlist_admin').data('matchmeta'), 
              data.period = $('#viewreports_matchlist_admin').data('period'),
              data.sa_council_filter = $('#viewreports_matchlist_admin').data('sacouncilfilter')
            }
          },

          "order": [[ 0, "desc" ]],
          "pagingType": "first_last_numbers",
          //"pagingType": "input",
          "columnDefs": [ {
            "targets"  : 'no-sort',
            "orderable": false,
          }],
          "fnDrawCallback": function(oSettings) {
            if ($('#viewreports_matchlist_admin tr').length <= 1) {
                $('.dataTables_paginate').hide();
                $('.dataTables_info').hide();
                
            }else{
              $('.dataTables_paginate').show();
              $('.dataTables_info').show();
            }
            if ($('#viewreports_matchlist_admin .dataTables_empty').length == 1) {
              $('.dataTables_paginate').hide();
              $('.dataTables_info').hide();
            }

            //$('.matched-companies-list li').hide().filter(':lt(2)').show();
            $( ".matched-companies-list" ).each(function() {
              var lis = $(this).find('li');
              if (lis.length > 3) {
                  $(this).find('li').hide().filter(':lt(3)').show();
                  $(this).append('<li style="list-style:none"><div class="three-dots"><small>More...</small></div></li>').find('li:last').click(function() {
                    $(this).find('.three-dots small').toggleClass("active");
                      if ($(this).find('.three-dots small').text() == "More...")
                        $(this).find('.three-dots small').text("Less..")
                      else $(this).find('.three-dots small').text("More...");

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
          oLanguage: {sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>"},
          initComplete: function () {
            var api = this.api();
            if ( meta == 'auto-approved' ) {
              api.column(6).visible( false );
            }else { api.column(6).visible( true ) ;}
          }
      });
      $('#viewreports_matchlist_admin_filter label:last').append('<br/><small>Search using any of the column values</small>');
      $("#viewreports_matchlist_admin_filter.dataTables_filter").prepend($("#councilFilter_label"));

      $('#councilFilter').on('change', function() {
        $('#viewreports_matchlist_admin').data('sacouncilfilter',$(this).val());
        table.draw();
      });
  }



});


//Stop Editing
});

})();