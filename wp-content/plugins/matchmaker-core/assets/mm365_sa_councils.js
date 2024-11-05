(function() {
    "use strict";
  jQuery(document).ready(function($) { 
    //STARTS HERE

 /**
  * 
  * Add council dropzone
  * 
  */
/*---------------------------------------------------------------------------------------------- */
// Get file extension
function checkFileExt(filename){
        filename = filename.toLowerCase();
        return filename.split('.').pop();
}

Dropzone.autoDiscover = false;
var totalsize = 0.0;
var totalfiles = 0;
var maxtotalallowed = 1; //in MB

$('#council-dropzone').dropzone({
     url: 'post.php',
     method: 'post',
     autoProcessQueue: false,
     maxFilesize: 1, // MB
     maxFiles:1,
     acceptedFiles:'.png',
     addRemoveLinks: true,
     init:function(){

       var myDropzone = this; 
       var exi_file_count = 0;
       var exi_file_size = 0;

              //Populate exisating files from server
       var files_paths = $('#council-dropzone').data('existing');
       if(files_paths !== undefined){
                  $.each(files_paths,function(i,item){
                    let mockFile = { name: item.name, size: item.size, id: item.id, accepted: true };
                    var ext = checkFileExt(item.name); // Get extension
                    myDropzone.emit("addedfile", mockFile);
                    if(ext == 'png' || ext == 'jpg' || ext == 'jpeg'){
                      myDropzone.emit("thumbnail", mockFile, item.path);
                    }
                    myDropzone.emit("complete", mockFile);
                    exi_file_count++;
                    exi_file_size += parseFloat((item.size / (1024*1024)).toFixed(2));
                  })
                  totalsize += exi_file_size;
                  totalfiles += exi_file_count;
        }

           myDropzone.on("addedfile", function(file) { 
             /*Do not accept same files*/
             totalsize += parseFloat((file.size / (1024*1024)).toFixed(2));
             totalfiles += 1;
             calcLivefilesize();
           });

          //Track files to be removed
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
                 content: 'You can only upload one file (.png) and the file size should not exceed 1MB. ',
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


           var submitButton = document.querySelector("#sa_council_add");
           submitButton.addEventListener("click", function (e) {
              if( calcLivefilesize() != true){
                  e.preventDefault();
              }
           });
        


      function calcLivefilesize(){
          //alert(totalsize +  "<>" + totalfiles );
             if (totalfiles <= 0) {
                 totalsize = 0;
                 totalfiles = 0;
                 //alert("Please upload atleast one capability statement");
                 $('#validate-council-logo').show();
                 $('#validate-council-logo .capability-statemets-error').html("Please upload logo");
                 //e.preventDefault();
                 return false;
             }
             else if (totalsize > maxtotalallowed ) {
               //alert("Total file size exceeded the limit");
               $('#validate-council-logo').show();
               $('#validate-council-logo .capability-statemets-error').html("Total file size exceeded allowed limit of "+ maxtotalallowed +" MB!");
               return false;
             }
             else {
               $('#validate-council-logo').hide();
               return true;
             }
       }



     }
     
 });






 /**
  * 
  * Add council 
  * 
  */
  $('form#mm365_add_council').submit(function(e){
    e.preventDefault(); 
    var redirect_to = $('#after_success_redirect').val();
    var form        = $('form')[0];
    var formdata    = new FormData(form);
    formdata.append('action', 'add_council');
    formdata.append('nonce',councilsAjax.nonce);
    var files = $('#council-dropzone').get(0).dropzone.getAcceptedFiles();
    for (let x = 0; x < files.length; x++){ 
        formdata.append('files[]', files[x]);
    }
    if ( $(this).parsley().isValid() ) { 

        $.ajax({ 
            url : councilsAjax.ajax_url,
            data: formdata,
            type: 'POST',                   
            contentType: false,
            processData: false,
            beforeSend: function() { 
                $('html, body').animate({ scrollTop: 0 }, 'slow');
                //$('.company_preview').before('<div class="loader-wrapper"><div id="loader" class="loader-matchrequest"><i class="fa fa-spinner" aria-hidden="true"></i></div></div>');                                                               
                Notiflix.Loading.hourglass('Adding council..',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });

            },
            success : function( data ){

                if(data == 'success'){

                    Notiflix.Loading.remove(1923);
                    Notiflix.Notify.success('Council added successfully!',() => { window.location = redirect_to } );
                    setTimeout(() => {
                      window.location = redirect_to; 
                    }, 2000);

                  }
                  else if(data == 'duplicate'){
                    Notiflix.Loading.remove(1923);
                    $.confirm({
                      title: 'Council already exists',
                      content:  'You are trying to add duplicate council name or short name',
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
                  else{
                    Notiflix.Loading.remove(1923);
                    $.confirm({
                      title:  'Unable to add council!',
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
  * Update council data
  * 
  */
  $('form#mm365_update_council').submit(function(e){
    e.preventDefault(); 
    var redirect_to = $('#after_success_redirect').val();
    var form        = $('form')[0];
    var formdata    = new FormData(form);
    formdata.append('action', 'update_council');
    formdata.append('nonce',councilsAjax.nonce);

    var files = $('#council-dropzone').get(0).dropzone.getAcceptedFiles();
    for (let x = 0; x < files.length; x++){ 
        formdata.append('files[]', files[x]);
    }
    var existing_attachments = [];
        $("[name='existing_files[]']:checked").each(function (i) {
            existing_attachments[i] = $(this).val();
    });
    formdata.append('existing_files', existing_attachments);


    if ( $(this).parsley().isValid() ) { 

        $.ajax({ 
            url : councilsAjax.ajax_url,
            data: formdata,
            type: 'POST',                   
            contentType: false,
            processData: false,
            beforeSend: function() { 
                $('html, body').animate({ scrollTop: 0 }, 'slow');
                //$('.company_preview').before('<div class="loader-wrapper"><div id="loader" class="loader-matchrequest"><i class="fa fa-spinner" aria-hidden="true"></i></div></div>');                                                               
                Notiflix.Loading.hourglass('Updating council details..',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });

            },
            success : function( data ){

                if(data == 'success'){

                    Notiflix.Loading.remove(1923);
                    Notiflix.Notify.success('Council details updated successfully!',() => { window.location = redirect_to } );
                    setTimeout(() => {
                      window.location = redirect_to; 
                    }, 2000);


                  }else if(data == 'duplicate'){
                    Notiflix.Loading.remove(1923);
                    $.confirm({
                      title: 'Council already exists',
                      content:  'You are adding a duplicate council name or short name',
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

                  }else{
                    Notiflix.Loading.remove(1923);
                    $.confirm({
                      title:  'Unable to update details!',
                      content: 'Please check the input values',
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
  * List Councils
  * 
  */


   if($('#superadmin_list_councils').length > 0)
   {

       $('#superadmin_list_councils').DataTable({
         responsive:true,
         "processing": true,
         "serverSide": false,
         "pagingType": "first_last_numbers",
         "order": [],
         "columnDefs": [ {
           "targets"  : 'no-sort',
           "orderable": false,
         }],
         "fnDrawCallback": function(oSettings) {
         
         },
         "language": {
           "lengthMenu": "Display _MENU_ councils per page",
           "zeroRecords": "No Councils",
           "info": "Showing page _PAGE_ of _PAGES_",
           "infoEmpty": "There are no Councils",
           "infoFiltered": "(filtered from _MAX_ total records)"
         },
         oLanguage: {sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>"}
       });
       $('#superadmin_list_councils_filter label').after('<br/><small>Search using any of the column values</small>');
   }








    //ENDS HERE
  });
})();