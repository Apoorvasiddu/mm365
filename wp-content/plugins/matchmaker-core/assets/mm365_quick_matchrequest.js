(function() {
    "use strict";
  jQuery(document).ready(function($) { 
    //STARTS HERE


/*------------------------------------------Search and find-------------------------------------- */
$('#mm365-select-matches-quicksearch').hide();
$('form#mm365_quick_match #quick_match').on("click",function(e){
    e.preventDefault(); 
    
    var form = $('form#mm365_quick_match')[0];
    var formdata = new FormData(form);
    formdata.append('action', 'mm365_quick_match');
    formdata.append('nonce', quickMatchAjax.nonce);

    if ( $('form#mm365_quick_match').parsley().isValid() ) { 
     
        $.ajax({ 
            url : quickMatchAjax.ajax_url,
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
          
                 $('#quickmatch-data-table').html(data);
                 Notiflix.Loading.remove(100);

                 $.fn.dataTable.ext.order['dom-checkbox'] = function (settings, col) {
                  return this.api().column(col, { order: 'index' }).nodes().map(function (td, i) {
                    return $('input', td).prop('checked') ? '1' : '0';
                  });
                }

                 //Initiate DataTables here
                 var resultset = $('#quick_matchsearchresult_companies').DataTable({
                    responsive:true,
                    "processing": true,
                    "serverSide": false,
                    searching: false, 
                    info: false,
                    //"ajax": {url:certificationAjax.ajax_url, data:{'action':'superadmin_list_council_managersing', 'status':filter_stat, 'period':period}},
                    "pagingType": "first_last_numbers",
                    "order": [],
                    columns: [
                      { orderDataType: 'dom-checkbox' },
                      { "name": "Company" },
                      { "name": "Description" },
                      { "name": "NAICS" },
                      { "name": "Products" },
                      { "name": "Council" },
                      { "name": "Location" }
                    ],
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


                  //For enabling checkbox sorting
                  $(':checkbox').on('change', function (e) {
                    var row = $(this).closest('tr');
                    var hmc = row.find(':checkbox:enabled').length;
                    var kluj = parseInt(hmc);
                    row.find('td.counter').text(kluj);
                    resultset.row(row).invalidate('dom');
                  });


                  var checkboxValues = {}
                  resultset.on("change", "input:checkbox:enabled", function () {

                    var rows = resultset.rows().nodes();

                    var count = 0;
                    $("input:checkbox:enabled", rows).each(function () {
                      //selectedIds.push($(this).attr('id'));
                      checkboxValues[this.id] = this.checked;
                      if (this.checked == true) {
                        count++;
                      }
                    });

                    
                  $('#mm365-select-matches-quicksearch').hide();
                  showSelectedcount('#mm365-select-matches-quicksearch',count);

                  
                 });

                 

                 //Realigns the sticky position of the button bar
                 adjustBarPosition('#mm365-select-matches-quicksearch');


                 //Get the IDs and create a match request on clicking 
                 $('#qs-convert-to-match').on('click', function(){

                    //AJAX process the quick search result
                    $.ajax({ 
                      url : quickMatchAjax.ajax_url,
                      data : {
                        action:'mm365_qs_to_matchrequest',
                        selected_companies:checkboxValues,
                        keyword:$('#company_name').val(),
                        redirect:$(this).data('redirect'),
                        requester:$(this).data('requester_company_id'),
                        council:$(this).data('requester_council_id'),
                        user_id:$(this).data('userid'),
                      },
                      type: 'POST',  
                      beforeSend: function() {                   
                          $('html, body').animate({ scrollTop: 0 }, 'slow');
                          Notiflix.Loading.hourglass('Converting to match request...',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });
                      },
                      success : function( data ){
                        //Redirect to match page on return success
                        if(data != 'FAIL'){
                          window.location.replace(data)
                        }


                      }
                    })
          

                 })


              }else{
                Notiflix.Loading.remove(100);
                $('#quickmatch-data-table').html('<h4 class="text-center">Sorry!! No companies found!</h4>');
              }
                

            }
        }); 


    }
});





//
function showSelectedcount(id, boxes_override) {

  if (boxes_override === '') {
    var boxes = $('input[name="matched_comp_id[]"]').filter(function () {
      return this.checked && !this.disabled;
    }).length;
  } else {
    var boxes = boxes_override;
  }
 
  if (boxes > 0) {
    $(id).show();
    $('#mm365-selected-matches').html(boxes);
    if (boxes == 1) { $('#mm365-selected-matches-message').html(" Company "); }
    else { $('#mm365-selected-matches-message').html(" Companies "); }
  } else {
    $(id).hide();
  }
}

    /*-------------------------------------------------------------------------------- */
    //For menu effect
    /*-------------------------------------------------------------------------------- */

function adjustBarPosition(id){

    $.fn.isInViewport = function () {
      var elementTop = $(this).offset().top;
      var elementBottom = elementTop + $(this).outerHeight();
      var viewportTop = $(window).scrollTop();
      var viewportBottom = viewportTop + $(window).height();
      return elementBottom > viewportTop && elementTop < viewportBottom;
    };


    $(window).on('resize scroll', function () {

      if ($(id).length > 0) {
        var viewPort_width = $(window).width();
        var viewPort = $(window).height();
        var panelHeight = $('.dashboard-content-panel').height();
        var botDiff = (panelHeight - viewPort);
        var valuetoAttach = (botDiff + 210);
        //console.log(panelHeight + "-" + viewPort + "=" + botDiff);
        if (viewPort_width > 850) {
          if ($('.main-footer').isInViewport()) {
            $(id).addClass('shift-position');
            $(id).css('bottom', "-" + valuetoAttach + "px");
          } else {
            $(id).removeClass('shift-position');
            $(id).css('bottom', "0px");
          }
        }
      }

    });


}




/*---------------------------- */


    //ENDS HERE
});
  
})();