(function() {
    "use strict";
  jQuery(document).ready(function($) { 
    //STARTS HERE


    //Certificate dropzone
/*---------------------------------------------------------------------------------------------- */
      Dropzone.autoDiscover = false;
      var totalsize = 0.0;
      var totalfiles = 0;
      var maxtotalallowed = 2; //in MB
       $('#certificate-dropzone').dropzone({
           url: 'post.php',
           method: 'post',
           autoProcessQueue: false,
           maxFilesize: 2, // MB
           maxFiles:1,
           acceptedFiles:'.jpg,.jpeg,.png,.pdf',
           addRemoveLinks: true,
           init:function(){
             var myDropzone = this; 
             var exi_file_count = 0;
             var exi_file_size = 0;
                 myDropzone.on("addedfile", function(file) { 
                   /*Do not accept same files*/
                   totalsize += parseFloat((file.size / (1024*1024)).toFixed(2));
                   totalfiles += 1;
                   calcLivefilesize();
                 });
                //  //Track files to be removed
                 myDropzone.on("removedfile", function(file) { 
                   $('#file_to_delete_' + file.id).prop('checked',false);
                   //Reduce count
                   if(totalfiles !== 0) { totalfiles -= 1; }
                   if(totalsize !== 0)  { totalsize -= parseFloat((file.size / (1024*1024)).toFixed(2)); }
                   calcLivefilesize();
                 });
             
                 myDropzone.on("error", function(file) {
                      $.confirm({
                       title:  'The file "' + file.name + '" cannot be uploaded',
                       content: 'You can only upload one file (.jpg,.png or .pdf ) and the file size should not exceed 2MB. ',
                       type: 'red',
                       typeAnimated: true,
                       icon: 'fas fa-exclamation-circle',
                       theme: 'modern',
                       buttons: {
                         close: {
                           btnClass: 'btn btn-primary',
                           action: function(){}
                         }
                       }
                   });
                     myDropzone.removeFile(file);
                 });


                 var submitButton = document.querySelector("#certificate_upload");
                 submitButton.addEventListener("click", function (e) {
                    if( calcLivefilesize() != true){
                        e.preventDefault();
                        //alert('cant');
                    }
                 });
              


            function calcLivefilesize(){
                //alert(totalsize +  "<>" + totalfiles );
                   if (totalfiles <= 0) {
                       totalsize = 0;
                       totalfiles = 0;
                       //alert("Please upload atleast one capability statement");
                       $('#validate-capability-statement').show();
                       $('#validate-capability-statement .capability-statemets-error').html("Please upload certificate");
                       //e.preventDefault();
                       return false;
                   }
                   else if (totalsize > maxtotalallowed ) {
                     //alert("Total file size exceeded the limit");
                     $('#validate-capability-statement').show();
                     $('#validate-capability-statement .capability-statemets-error').html("Total file size exceeded allowed limit of "+ maxtotalallowed +" MB!");
                     return false;
                   }
                   else {
                     $('#validate-capability-statement').hide();
                     return true;
                   }
             }

     

           }
           
       });
/*------------------------------------date picker----------------------------------------- */


if($(".certificate_expiry_date").length > 0){
    $(".certificate_expiry_date").flatpickr(
      {
        enableTime: false,
        minDate: new Date().fp_incr(8),
        disableMobile: "true",
        dateFormat: "m/d/Y",
      
    });
  }

/*------------------------------------------upload SUBMIT-------------------------------------- */

$('form#mm365_upload_certificate').submit(function(e){
    e.preventDefault(); 
    var form = $(this)[0];
    var formdata = new FormData(form);
    formdata.append('action', 'save_certificate');
    formdata.append('cert_nonce', certificationAjax.nonce);
    var files = $('#certificate-dropzone').get(0).dropzone.getAcceptedFiles();
    for (let x = 0; x < files.length; x++){ 
        formdata.append('files[]', files[x]);
    }
    //
    if ( $(this).parsley().isValid() ) { 
     
        $.ajax({ 
            url : certificationAjax.ajax_url,
            data: formdata,
            type: 'POST',                   
            contentType: false,
            processData: false,
            beforeSend: function() { 
                $('html, body').animate({ scrollTop: 0 }, 'slow');
                $('.btn').hide();
                $('#certificate_upload').hide();                            
                $('html, body').animate({ scrollTop: 0 }, 'slow');
                //$('#certificate_upload').after('<div class="loader-wrapper"><div id="loader" class="loader-matchrequest"><i class="fa fa-spinner" aria-hidden="true"></i></div></div>');  
                //Notiflix.Notify.info('Uploading certificate...'); 
                Notiflix.Loading.hourglass('Uploading certificate...',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });

            },
            success : function( data ){
                if( data == '1') { 
                      //$('.loader-wrapper').hide();

                      Notiflix.Notify.success('Your certificate has been submitted for verification. Updates will be notified through email.',() => { location.reload() } );
                      setTimeout(() => {
                          location.reload();
                      }, 2000);

                      Notiflix.Loading.remove(1500);
                    
                } else {

                  switch (data) {
                    case '3':
                      var cont = 'Certificates expiring under seven days should be renewed before submission.';
                      break;
                  
                    case '0':
                      var cont = 'Files failed to upload. Please try again later';
                      break;
                  }
                    
                  $.confirm({
                    title:  'Unable to upload the certificate!',
                    content: cont,
                    type: 'red',
                    typeAnimated: true,
                    icon: 'far fa-times-circle',
                    theme: 'modern',
                    buttons: {
                      close: {
                        btnClass: 'btn btn-primary',
                        action: function(){
                            location.reload();
                        }
                      }
                    }
                  });


                }
            }
        }); 


    }
});

/*------------------------------------Listing users submitted certificates--------------------------- */


if($('#certificates_list').length > 0)
{
    $('#certificates_list').DataTable({
      responsive:true,
      "processing": true,
      "serverSide": true,
      "ajax": {url:certificationAjax.ajax_url, data:{'action':'certificates_submitted'}},
      "pagingType": "first_last_numbers",
      "order": [],
      "columnDefs": [ {
        "targets"  : 'no-sort',
        "orderable": false,
      }],
      "fnDrawCallback": function(oSettings) {
        if ($('#certificates_list tr').length <= 1) {
            $('.dataTables_paginate').hide();
            $('.dataTables_info').hide();
            
        }else{
          $('.dataTables_paginate').show();
          $('.dataTables_info').show();
        }
        if ($('#certificates_list .dataTables_empty').length == 1) {
          $('.dataTables_paginate').hide();
          $('.dataTables_info').hide();
        }

        //Delete action
        $('.delete-certificate').on("click", function(e){
          e.preventDefault(); 
          var cert_id  = $(this).data('certificate');
          var redirect = $(this).data('redirect');
          del_certificate(cert_id, redirect);
        });
        

      },
      "language": {
        "lengthMenu": "Display _MENU_ certificates per page",
        "zeroRecords": "No certifcates submitted",
        "info": "Showing page _PAGE_ of _PAGES_",
        "infoEmpty": "There are no certificates",
        "infoFiltered": "(filtered from _MAX_ total records)"
      },
      oLanguage: {sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>"}
    });


    $('#certificates_list_filter label:last').append('<br/><small>Search using any of the column values</small>');
}

/*--------------------------------------------------------------------------- */


$('.delete-certificate').on("click", function(e){
  e.preventDefault(); 
  var cert_id  = $(this).data('certificate');
  var redirect = $(this).data('redirect');
  del_certificate(cert_id, redirect);
});


function del_certificate(cert_id, redirect){

  $.confirm({
    title: 'Do you want to delete the certificate?',
    content: 'Click \'Confirm\' or \'Cancel\' to continue',
    theme: 'modern',
    icon: 'fas fa-trash-alt',
    type: 'red',
    buttons: {
        confirm: {
          btnClass: 'btn btn-primary',
          action: function () {
            $.ajax({ 
              url : certificationAjax.ajax_url,
              data : {
                  action:'delete_certificate',
                  nonce: certificationAjax.nonce,
                  cert_id: cert_id
              },
              type: 'POST',   
              beforeSend: function(){
                //Notiflix.Notify.info('Deleting certificate...');
                Notiflix.Loading.hourglass('Deleting certificate...',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });
                
              },                    
              success : function( data ){
                     if(data == '1'){
                        Notiflix.Loading.remove(1923);

                        Notiflix.Notify.success('Certificate deleted!',() => { window.location = redirect } );
                        setTimeout(() => {
                          window.location = redirect; 
                        }, 2000);

                      }else{  
                        Notiflix.Loading.remove(1923);
                        //window.location = redirect; 
                        Notiflix.Notify.failure('Something went wrong!');
                    }
              }
           }); 
          }
        },
        cancel: {
          btnClass: 'btn btn-primary red',
        }
    }
  });
}

/*-------------------------------------------------------------------------------- */

if($('#admin_certificates_list').length > 0)
{
    var filter_stat   = $('#admin_certificates_list').data('statfilter');
    var period        = $('#admin_certificates_list').data('period');


    //SA filtering with council - get the id of counil
    var sa_council_filter   = $('#admin_certificates_list').data('sacouncilfilter');

    var table  = $('#admin_certificates_list').DataTable({
      responsive:true,
      "processing": true,
      "serverSide": true,
      'serverMethod': 'post',
      "ajax": {
        url:certificationAjax.ajax_url, 
        "data":function(data) {
          data.action = 'admin_certificates_listing', 
          data.status = filter_stat, 
          data.period = period,
          data.sa_council_filter = $('#admin_certificates_list').data('sacouncilfilter')
        }
      },
      "pagingType": "first_last_numbers",
      "order": [],
      "columnDefs": [ {
        "targets"  : 'no-sort',
        "orderable": false,
      }],
      "fnDrawCallback": function(oSettings) {
        if ($('#admin_certificates_list tr').length <= 1) {
            $('.dataTables_paginate').hide();
            $('.dataTables_info').hide();
            
        }else{
          $('.dataTables_paginate').show();
          $('.dataTables_info').show();
        }
        if ($('#admin_certificates_list .dataTables_empty').length == 1) {
          $('.dataTables_paginate').hide();
          $('.dataTables_info').hide();
        }


      },
      "language": {
        "lengthMenu": "Display _MENU_ certificates per page",
        "zeroRecords": "No certifcates submitted",
        "info": "Showing page _PAGE_ of _PAGES_",
        "infoEmpty": "There are no certificates",
        "infoFiltered": "(filtered from _MAX_ total records)"
      },
      oLanguage: {sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>"}
    });

    // $('#admin_certificates_list input').unbind();
    // $('#admin_certificates_list input').bind('keyup', function(e) {
    //     if (e.keyCode == 13) {
    //         Table.fnFilter($(this).val());
    //     }
    // });


    //var filterTerm;
    $('#councilFilter').on('change', function() {
        $('#admin_certificates_list').data('sacouncilfilter',$(this).val());
        table.draw();
    });

    $('#admin_certificates_list_filter label:last').append('<br/><small>Search using any of the column values</small>');
    $("#admin_certificates_list_filter.dataTables_filter").prepend($("#councilFilter_label"));

}

/*------------------------------------------Admin action-------------------------------------- */


$('form#mm365_admin_certificate_action').submit(function(e){
  e.preventDefault(); 
  //APPROVE ACTION
  if ( $(this).parsley().isValid() ) { 
    var note = $('#certificate_note').val();
    var cert = $('#certificate_id').val();
    var redirect = $('#redirect_url').val();
    admin_certificate_processing(cert,'verified',note,redirect);
  }

});

$('#reject-certificate').on("click",function(e){
  e.preventDefault(); 
  $('form#mm365_admin_certificate_action').parsley().validate();
  //APPROVE ACTION
  if ( $('form#mm365_admin_certificate_action').parsley().isValid() ) { 
    var note     = $('#certificate_note').val();
    var cert     = $('#certificate_id').val();
    var redirect = $('#redirect_url').val();
    admin_certificate_processing(cert,'rejected',note,redirect);
  }

});


function admin_certificate_processing(cert_id, status, note, redirect){

  switch (status) {
    case 'verified':
      var status_label = 'approve';
      var successs_label = 'verified';
      var typ = 'green';
      break;
  
    default:
      var status_label = 'unapprove';
      var typ = 'red';
      var successs_label = 'unapproved';
      break;
  }
  $.confirm({
    title: 'Do you want to '+ status_label +' the certificate?',
    content: 'Click \'Confirm\' or \'Cancel\' to continue',
    icon: "fas fa-passport",
    theme: 'modern',
    type: typ,
    buttons: {
        confirm:{
          btnClass: 'btn btn-primary',
          action: function () {
            //$.alert('Confirmed!' + cert_id);
            $.ajax({ 
              url : certificationAjax.ajax_url,
              data : {
                  action:'admin_verification',
                  nonce: certificationAjax.nonce,
                  status: status,
                  cert_id: cert_id,
                  note: note
              },
              type: 'POST',   
              beforeSend: function() { 
                Notiflix.Notify.info('Performing action...');
              },                      
              success : function( data ){
                     if(data == '1' || data == '2' ){

                        Notiflix.Notify.success('Certificate \''+ successs_label +'\' !',() => {  window.location = redirect; } );
                        setTimeout(() => {
                          window.location = redirect;
                        }, 3000);
                        
                      }else{  
                        Notiflix.Notify.failure('Something went wrong!');
                    }
              }
            }); 
          }

        },
        cancel: {
          btnClass: 'btn btn-primary red',
        }
    }
  });

}

    //ENDS HERE
});
  
})();