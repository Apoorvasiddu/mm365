(function() {
    "use strict";
  jQuery(document).ready(function($) { 
    //STARTS HERE

    /**
     * This is an exception case
     * Slection values are part of theme option but
     * 'Established business with a supplier' is hardcoded here
     * as other dropdown values are not providing comfirmation
     * to show contract details block
     */
    $('#contract-details-block').hide();
    $('#match_closure_filter').on('change',function(){
      var getSelection = $(this).val();
      if(getSelection === 'Established business with a supplier'){
        $('#contract-details-block').show();
        $('#contract_value').attr( "required",true);
        $('#contract_termsandconditions').attr( "required",true);
      }else{
        $('#contract-details-block').hide();
        //Remove required attribute - contract_value, contract_termsandconditions
        $('#contract_value').removeAttr( "required");
        $('#contract_termsandconditions').removeAttr( "required");
      }
    });

    
/*----------------------------------------------- Schedule Meeting - Final Step ------------------------------- */

$('form#mm365_matchrequest_close').submit(function(e){
  e.preventDefault(); 

  var form = $(this)[0];
  var formdata = new FormData(form);

  for (const value of formdata.values()) {
    console.log(value);
  }

  formdata.append('action', 'close_match');
  formdata.append('nonce', mrclosureAjax.nonce);
  
  var mr_id       = $('#mr_id').val();
  var message     = $('#reason_for_mrclosure').val();
  var reason      = $('#match_closure_filter').val(); 

  var redirect    = $('#redirect_url').val();
  var act         = $('#act').val();

  switch (act) {
    case 'cancel':
      var reason_label  = 'cancelled';
      break;
  
    case 'complete':
      var reason_label  = 'completed'; 
      break;
  }

  if ( $(this).parsley().isValid() ) { 

       $.ajax({ 
          url : mrclosureAjax.ajax_url,
          data: formdata,
          contentType: false,
          processData: false,
          // data : {
          //     action:'close_match',
          //     mr_id:mr_id,
          //     filter_reason:reason,
          //     message:message,
          //     nonce:mrclosureAjax.nonce,
          //     act:act
          // },
          type: 'POST',                  
          success : function( data ){
            if(data == '1'){
              var typ   = 'green';
              var ico   = 'far fa-check-circle';
              var title = 'Match request ' + reason_label + '!';
              var cont  = 'You can\'t take any further actions on this match request. All the meetings set for future dates associated with this match request will be cancelled';
            }else{
              var typ   = 'red';
              var ico   = 'far fa-times-circle';
              var title = 'Unable to perform this action!';
              var cont  = 'Unauthorised security token!';
            }
            $.confirm({
              title:  title,
              content: cont,
              type: typ,
              typeAnimated: true,
              icon: ico,
              theme: 'modern',
              buttons: {
                close: {
                  btnClass: 'btn btn-primary',
                  action: function(){
                    window.location = redirect;
                  }
                }
              }
             });

          }
       }); 
    }

});
    

    //ENDS HERE
  });
  
})();