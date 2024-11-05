(function() {
  "use strict";
jQuery(document).ready(function($) { 
/* Start Editing */

$(document).ready(function() {
  //$.fn.DataTable.ext.pager.numbers_length = 3;

  if($('#matchpreference_admin').length > 0){
        $.fn.dataTable.ext.errMode = 'throw';
      //Use the built in datatables API to filter the existing rows by the Category column
        var table =  $('#matchpreference_admin').DataTable({
          responsive:true,
          "processing": true,
          "serverSide": true,
          "iDisplayLength": 50,
          'serverMethod': 'post',
          //"ajax": adminmatchpreferenceAjax.ajaxurl,
          "ajax": {
						"url": adminmatchpreferenceAjax.ajaxurl,
						"data":function(data) {
              data.action = 'mm365_matchpreference_admin_listing';
							data.council_filter = $('#councilFilter').val();
						}
          },
          "order": [[ 0, "ASC" ]],
          "pagingType": "first_last_numbers",
          //"pagingType": "input",
          "columnDefs": [ {
            "targets"  : 'no-sort',
            "orderable": false,
          }],
          "fnDrawCallback": function(oSettings) {
            if ($('#matchpreference_admin tr').length <= 1) {
                $('.dataTables_paginate').hide();
                $('.dataTables_info').hide();
                
            }else{
              $('.dataTables_paginate').show();
              $('.dataTables_info').show();
            }
            if ($('#matchpreference_admin .dataTables_empty').length == 1) {
              $('.dataTables_paginate').hide();
              $('.dataTables_info').hide();
            }

            //Click checkbox 
            $('.matchpreference_toggle').on( "click", function() {
              var comp_id = $(this).val();
              if(comp_id != ''){
                $.ajax({ 
                  url : adminmatchpreferenceAjax.ajaxurl,
                  data : {
                      action:'mm365_matchpreference_toggle',
                      company_id:comp_id
                  },
                  type: 'POST',    
                  beforeSend: function() { 
                    Notiflix.Notify.info('Changing approval preference..');
                  },   
                  success : function( data ){
                    $('#stat-' + comp_id).html(data);
                    $('#stat-' + comp_id).removeClass();
                    $('#stat-' + comp_id).addClass('approval-required '+ data);
                    Notiflix.Notify.success('Approval preference changed');
                  }
              });
            } 



            });



          },
          "language": {
            "lengthMenu": "Display _MENU_  companies per page",
            "zeroRecords": "No companies found",
            "info": "Showing page _PAGE_ of _PAGES_",
            "infoEmpty": "No companies available",
            "infoFiltered": "(filtered from _MAX_ total records)"
          },
          oLanguage: {sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>"}
          
      });



      $('#matchpreference_admin_filter label:last').append('<br/><small>Search using any of the column values</small>');
    
      $("#matchpreference_admin_filter.dataTables_filter").prepend($("#councilFilter_label"));
      
      $('#councilFilter').change(function(){
        table.draw();
      });



  }

} );


//Stop Editing
});

})();