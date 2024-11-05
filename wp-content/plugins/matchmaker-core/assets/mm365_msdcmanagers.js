(function() {
    "use strict";
  jQuery(document).ready(function($) { 
    //STARTS HERE

 /**
  * 
  * Email availability check
  * 
  */

  $('#mm365_dcm_email').bind('change keyup input',function(e){
    $.ajax({ 
        url : msdcmanagerAjax.ajax_url,
        data : {
            action:'is_email_available',
            email:$(this).val(),
            nonce: msdcmanagerAjax.nonce,
        },
        type: 'POST',                  
        success : function( data ){
           //console.log(data);
           if(data == 1){
               $('#email-check-success').css('display','block');
               $('#email-check-fail').css('display','none');
           }else{
               $('#email-check-success').css('display','none');
               $('#email-check-fail').css('display','block');
           }
           
        }
     }); 
  });

 /**
  * 
  * User name availability check
  * 
  */
  $('#mm365_dcm_username').bind('change keyup input',function(e){
    $.ajax({ 
        url : msdcmanagerAjax.ajax_url,
        data : {
            action:'is_username_available',
            username:$(this).val(),
            nonce: msdcmanagerAjax.nonce,
        },
        type: 'POST',                  
        success : function( data ){
            if(data == 1){
                $('#username-check-success').css('display','block');
                $('#username-check-fail').css('display','none');
            }else{
                $('#username-check-success').css('display','none');
                $('#username-check-fail').css('display','block');
            }
        }
     }); 
  });

 /**
  * 
  * Inser User
  * 
  */
  $('form#mm365_add_dc_manager').submit(function(e){
    e.preventDefault(); 
    var redirect_to = $('#after_success_redirect').val();
    var form        = $('form')[0];
    var formdata    = new FormData(form);
    formdata.append('action', 'create_user');
    formdata.append('nonce',msdcmanagerAjax.nonce);
    if ( $(this).parsley().isValid() ) { 

        $.ajax({ 
            url : msdcmanagerAjax.ajax_url,
            data: formdata,
            type: 'POST',                   
            contentType: false,
            processData: false,
            beforeSend: function() { 
                $('html, body').animate({ scrollTop: 0 }, 'slow');
                //$('.company_preview').before('<div class="loader-wrapper"><div id="loader" class="loader-matchrequest"><i class="fa fa-spinner" aria-hidden="true"></i></div></div>');                                                               
                Notiflix.Loading.hourglass('Adding council manager...',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });
            },
            success : function( data ){

                if(data == 'success'){

                    Notiflix.Loading.remove(1800);
                    Notiflix.Notify.success('Council Manager added successfully!',() => { window.location = redirect_to } );
                    setTimeout(() => {
                      window.location = redirect_to; 
                    }, 1800);

                  }else{
                    $.confirm({
                      title:  'Unable to create account!',
                      content: "Please check the input values",
                      type: 'red',
                      typeAnimated: true,
                      icon: 'fas fa-exclamation-circle',
                      theme: 'modern',
                      buttons: {
                        close: {
                          btnClass: 'btn btn-primary',
                          action: function(){
                            $('.loader-wrapper').hide();
                          }
                        }
                      }
                    });

                  }

            }
        });
    }
  });



/**
  * 
  * Update User
  * 
  */
 $('form#mm365_update_dc_manager').submit(function(e){
  e.preventDefault(); 
  var redirect_to = $('#after_success_redirect').val();
  var form        = $('form')[0];
  var formdata    = new FormData(form);
  formdata.append('action', 'update_user');
  formdata.append('nonce',msdcmanagerAjax.nonce);
  if ( $(this).parsley().isValid() ) { 

      $.ajax({ 
          url : msdcmanagerAjax.ajax_url,
          data: formdata,
          type: 'POST',                   
          contentType: false,
          processData: false,
          beforeSend: function() { 
              $('html, body').animate({ scrollTop: 0 }, 'slow');
              //$('.company_preview').before('<div class="loader-wrapper"><div id="loader" class="loader-matchrequest"><i class="fa fa-spinner" aria-hidden="true"></i></div></div>');                                                               
              Notiflix.Loading.hourglass('Updating council manager...',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });
          },
          success : function( data ){

              if(data == 'success'){

                  Notiflix.Loading.remove(1800);
                  Notiflix.Notify.success('Council Manager details updated successfully!',() => { window.location = redirect_to } );
                  setTimeout(() => {
                    window.location = redirect_to; 
                  }, 1800);


                }else{
                  $.confirm({
                    title:  'Unable to update details!',
                    content: "Please check the input values",
                    type: 'red',
                    typeAnimated: true,
                    icon: 'fas fa-exclamation-circle',
                    theme: 'modern',
                    buttons: {
                      close: {
                        btnClass: 'btn btn-primary',
                        action: function(){
                          $('.loader-wrapper').hide();
                        }
                      }
                    }
                  });

                }

          }
      });
  }
});



  /**
  * 
  * List Council Managers
  * 
  */
   if($('#superadmin_list_council_managers').length > 0)
   {

       $('#superadmin_list_council_managers').DataTable({
         responsive:true,
         "processing": true,
         "serverSide": false,
         //"ajax": {url:certificationAjax.ajax_url, data:{'action':'superadmin_list_council_managersing', 'status':filter_stat, 'period':period}},
         "pagingType": "first_last_numbers",
         "order": [],
         "columnDefs": [ {
           "targets"  : 'no-sort',
           "orderable": false,
         }],
         "fnDrawCallback": function(oSettings) {},
         "language": {
           "lengthMenu": "Display _MENU_ council managers per page",
           "zeroRecords": "No Council Managers found",
           "info": "Showing page _PAGE_ of _PAGES_",
           "infoEmpty": "There are no Council Managers found",
           "infoFiltered": "(filtered from _MAX_ total records)"
         },
         oLanguage: {sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>"}
       });
       $('#superadmin_list_council_managers_filter label').after('<br/><small>Search using any of the column values</small>');
   }

  /**
   * 
   * Toggle User Lock
   * 
   */
  $('.user_lock').on('click',function(e){
    e.preventDefault();
    var user_id       = $(this).attr('data-userid');
    var current_state = $(this).attr('data-lockstate');
    
    //AJAX
    $.ajax({ 
      url : msdcmanagerAjax.ajax_url,
      data : {
          action:'toggle_user_lock',
          user_id: user_id,
          current_state: current_state,
          nonce: msdcmanagerAjax.nonce,
      },
      type: 'POST',                  
      success : function( data ){
         
        if(data == 'success'){
          $.confirm({
            title:  'Account login status changed!',
            content: "",
            type: 'green',
            typeAnimated: true,
            icon: 'far fa-check-circle',
            theme: 'modern',
            buttons: {
              close: {
                btnClass: 'btn btn-primary',
                action: function(){
                  window.location.reload();
                }
              }
            }
          });
        }else{
          $.confirm({
            title:  'Unable to change status!',
            content: "",
            type: 'red',
            typeAnimated: true,
            icon: 'fas fa-exclamation-circle',
            theme: 'modern',
            buttons: {
              close: {
                btnClass: 'btn btn-primary',
                action: function(){
                  $('.loader-wrapper').hide();
                }
              }
            }
          });

        }

      }
    }); 
  
  });

/**
  * 
  * Match request - Council wise
  * 
  */

 if($('#matchlist_council_manager').length > 0){
  var table = $('#matchlist_council_manager').DataTable({
        responsive:true,
        "processing": true,
        "serverSide": true,
        "ajax":  {url: msdcmanagerAjax.ajax_url,  data:{'action':'council_match_listing'}},
        "order": [[ 1, "desc" ]],
        "pagingType": "first_last_numbers",
        //"pagingType": "input",
        "pageLength": 25,
        "columnDefs": [ {
          "targets"  : 'no-sort',
          "orderable": false,
        }],
        "fnDrawCallback": function(oSettings) {
          if ($('#matchlist_council_manager tr').length <= 1) {
              $('.dataTables_paginate').hide();
              $('.dataTables_info').hide();
              
          }else{
            $('.dataTables_paginate').show();
            $('.dataTables_info').show();
          }
          if ($('#matchlist_council_manager .dataTables_empty').length == 1) {
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
        oLanguage: {sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>"}
    });

    // $('#matchlist_council_manager_filter input').unbind();
    // $('#matchlist_council_manager_filter input').bind('keyup', function(e) {
    //     if (e.keyCode == 13) {
    //         Table.fnFilter($(this).val());
    //     }
    // });

    $('#matchlist_council_manager_filter label:last').append('<br/><small>Search using any of the column values</small>');
    // $("#matchlist_council_manager_filter.dataTables_filter").prepend($("#councilFilter_label"));

}







    //ENDS HERE
  });
})();