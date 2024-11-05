
(function() {
    "use strict";
  jQuery(document).ready(function($) { 


    $('#conference_participating').hide();

  
    $(document).ready(function(){
    if($("input[name='edit_sb_for_event']:checked").val() == 'yes'){
       $('#edit_conference_participating').show();
       $('#edit_superbuyer_upcoming_conference').attr('required',true);
    }else{
     
      $('#edit_conference_participating').hide();
      $('#edit_superbuyer_upcoming_conference').attr('required',false);
    }
  });



    $("input[name='sb_for_event']").on("change", function(){
      if($(this).val() == 'yes'){
        $('#conference_participating').show();
        $('#associatedbuyer_upcoming_conference').attr('required',true);
      }else{
        $('#conference_participating').hide();
        $('#associatedbuyer_upcoming_conference').removeAttr('required');
      }
    });

    $("input[name='edit_sb_for_event']").on("change", function(){
      if($(this).val() == 'yes'){
        $('#edit_conference_participating').show();
        $('#associatedbuyer_upcoming_conference').attr('required',true);
        $('#edit_superbuyer_upcoming_conference').attr('required',true);
      }else{
        $('#edit_conference_participating').hide();
        $('#associatedbuyer_upcoming_conference').removeAttr('required');
        $('#edit_superbuyer_upcoming_conference').removeAttr('required');
      }
    });


if($('#buyer_teams_meetinglist').length > 0)
{
    

  $('#buyer_teams_meetinglist').DataTable({
      responsive:true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        "url":superBuyerPubAjax.ajax_url, 
        "data":function(data) {
          data.action = 'buyer_team_created_meetings'
          // data.timezone = timezone_offset_minutes, 
          // data.offset = (-today.getTimezoneOffset() / 60), 
          // data.dst = (+dst),
          // data.council = $('#councilFilter').val()
        }
      },
      "pagingType": "first_last_numbers",
      "order": [],
      "columnDefs": [ {
        "targets"  : 'no-sort',
        "orderable": false,
      }],
      "fnDrawCallback": function(oSettings) {
        if ($('#buyer_teams_meetinglist tr').length <= 1) {
            $('.dataTables_paginate').hide();
            $('.dataTables_info').hide();
            
        }else{
          $('.dataTables_paginate').show();
          $('.dataTables_info').show();
        }
        if ($('#buyer_teams_meetinglist .dataTables_empty').length == 1) {
          $('.dataTables_paginate').hide();
          $('.dataTables_info').hide();
        }



      },
      "language": {
        "lengthMenu": "Display _MENU_ meetings per page",
        "zeroRecords": "No meetings found",
        "info": "Showing page _PAGE_ of _PAGES_",
        "infoEmpty": "No meetings found",
        "infoFiltered": "(filtered from _MAX_ total records)"
      },
      oLanguage: {sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>"}
    });



}



/*-------------------------------------------------------------------------------- */
    //tagify search form
/*-------------------------------------------------------------------------------- */ 
if($("#sb_naics_code").length > 0){
  var subBuyerTagsInput = document.querySelector('[name="sb_naics_codes"]')
  var regex_p1 = new RegExp('^[0-9]{2,6}$')
  var settings = {
      delimiters:",",
      maxTags: 200,
      pattern : regex_p1,
      texts: {
          empty      : "Please add NAICS Code(s)",
          exceed     : "Number of NAICS codes exceeded",
          pattern    : "NAICS code length exceeded",
          duplicate  : "Duplicate NAICS Code",
          notAllowed : "Invalid NAICS code"
      },
      originalInputValueFormat: valuesArr => valuesArr.map(item => item.value).join(', ')
  }
  var subBuyerNaics = new Tagify(subBuyerTagsInput, settings);
  subBuyerNaics.on('invalid', function(e){
      $.confirm({
          title:  e.detail['message'],
          content: 'Maximum of 20 NAICS codes are allowed, each code cannot exceed more than 6 digits. Duplicate codes are not allowed',
          type: 'red',
          typeAnimated: true,
          icon: 'far fa-times-circle',
          theme: 'modern',
          buttons: {
            close: {
              btnClass: 'btn btn-primary',
            }
          }
      });
  });
  }

  /**
   * Show conference selection only if selected
   * 
   */
  $('#asb_for_conference_block').hide();
  $("input[name='asb_for_conference']").on("change", function(){
    if($(this).val() == 'yes'){
      $('#asb_for_conference_block').show();
      $('#associatedbuyer_upcoming_conference').attr('required',true);
    }else{
      $('#asb_for_conference_block').hide();
      $('#associatedbuyer_upcoming_conference').removeAttr('required');
    }
  });

  //Only show selected council conferences
  $('#superbuyer_council_id').on('change', function(){


    $.ajax({ 
      url : superBuyerAjax.ajax_url,
      data : {
        council: $('#superbuyer_council_id').val(),
        action: 'offline_conferences_fordropdown'
      },
      type: 'POST',  
      beforeSend: function() { 
          $("#associatedbuyer_upcoming_conference").html('<option> Loading ...</option>');
      },                   
      success : function( data ){
          if( data ) { 
              $('#associatedbuyer_upcoming_conference').html(data);
          } 
          $("#associatedbuyer_upcoming_conference'").empty().trigger('change');
      }
     }); 


  });



//ENDS HERE
});
  
})();