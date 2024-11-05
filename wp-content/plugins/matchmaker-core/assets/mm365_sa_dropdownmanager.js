(function() {
    "use strict";
  jQuery(document).ready(function($) { 
    //STARTS HERE

/**-------------------------------------------------- */



    //Certificate dropzone
/*---------------------------------------------------------------------------------------------- */
function dropzone_mmsdc(){
  $('#validate-capability-statement').hide();
  Dropzone.autoDiscover = false;
  var totalsize = 0.0;
  var totalfiles = 0;
  var maxtotalallowed = 1; //in MB
 $('#systemsetting-dropzone').dropzone({
     url: 'post.php',
     method: 'post',
     autoProcessQueue: false,
     maxFilesize: 2, // MB
     maxFiles:1,
     acceptedFiles:'.jpg,.jpeg,.png',
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
                 content: 'You can only upload one file (.jpg or .png ) and the file size should not exceed 1MB. ',
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


           var submitButton = document.querySelector("#dropdown_submit");
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

}











    /**---------------------------------- */

    /**
     * 
     * Select drop down to edit
     * 
     */
    
    $('#choose_dropdown_to_edit').on('change',function(e){
      $.ajax({ 
          url : dropdownmanagersAjax.ajax_url,
          data : {
              action:'load_dropdown',
              dropdown:$(this).val(),
              nonce: dropdownmanagersAjax.nonce,
          },
          type: 'POST',    
          beforeSend: function() { 
            $('html, body').animate({ scrollTop: 0 }, 'slow');
            $('.company_preview').before('<div class="loader-wrapper"><div id="loader" class="loader-matchrequest"><i class="fa fa-spinner" aria-hidden="true"></i></div></div>');                                                                          
          },              
          success : function( data ){
             $('.loader-wrapper').remove();
             $('#manage-dropdowns-fields').html(data);
             $('#mm365_dropdown_form').parsley();

            /**
             * 
             * Toggle mode
             * 
             */
            $('.toggler').on('click', function(e){
              e.preventDefault();
              const rec_id = $(this).data('recid');
              $.ajax({ 
                url : dropdownmanagersAjax.ajax_url,
                data : {
                    action:'toggle_mode',
                    dropdown:$(this).data('dropdown'),
                    recid:$(this).data('recid'),
                    nonce: dropdownmanagersAjax.nonce,
                },
                type: 'POST',
                beforeSend: function() { 
                  //$('html, body').animate({ scrollTop: 0 }, 'slow');
                  $('.company_preview').before('<div class="loader-wrapper"><div id="loader" class="loader-matchrequest"><i class="fa fa-spinner" aria-hidden="true"></i></div></div>');                                                                          
                },                  
                success : function( data ){
                  $('.loader-wrapper').remove();
                  if(data !== 'failed'){
                    if(data == 'disable'){
                      $("[data-recid='" + rec_id + "']").prop('checked', true);
                    }else{
                      $("[data-recid='" + rec_id + "']").prop('checked', false);
                    } 
                  }
              
                }
             });

            });

            /*-------------------------------------------- */

             /**
             * 
             * Form Action
             * 
             */
              dropzone_mmsdc();

 
              $('form#mm365_dropdown_form').submit(function(e){
                e.preventDefault(); 
                var form = $(this)[0];
                var formdata = new FormData(form);
                formdata.append('action', 'add_item');
                formdata.append('nonce', dropdownmanagersAjax.nonce);
                //Add dropzone only for meeting type
                if($('#systemsetting-dropzone').length > 0){
                
                  var files = $('#systemsetting-dropzone').get(0).dropzone.getAcceptedFiles();
                  for (let x = 0; x < files.length; x++){ 
                    formdata.append('files[]', files[x]);
                  }
                }
                //
                if ( $(this).parsley().isValid() ) { 
                  $.ajax({ 
                    url : dropdownmanagersAjax.ajax_url,
                    data: formdata,
                    type: 'POST',                   
                    contentType: false,
                    processData: false,
                    beforeSend: function() { 
                        $('html, body').animate({ scrollTop: 0 }, 'slow');
                        $('.company_preview').before('<div class="loader-wrapper"><div id="loader" class="loader-matchrequest"><i class="fa fa-spinner" aria-hidden="true"></i></div></div>');                        
                    },
                    success : function( data ){
                      $('.loader-wrapper').remove();
                      $('#mm365_dropdown_form')[0].reset();
                      $('#mm365_dropdown_form').parsley().reset();
                      //Load the drop down and scroll to the end of list
                      if(data !== 'failed'){
                        $('#choose_dropdown_to_edit').val(data).trigger("change");

                        //Show popup
                        $.confirm({
                          title:  'Success!!',
                          content: "Dropdown item added successfully",
                          type: 'green',
                          typeAnimated: true,
                          icon: 'far fa-check-circle',
                          theme: 'modern',
                          buttons: {
                            close: {
                              btnClass: 'btn btn-primary',
                              action: function(){
                                $('html, body').animate({scrollTop: $("#endoflist").offset().top - 100 }, 300);
                              }
                            }
                          }
                        });

                      
                      }else{

                        $.confirm({
                          title: 'Unable to update dropdown',
                          content:  '---',
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


             /*-------------------------------------- */



          }
       }); 
    });


    //ENDS HERE
  });
})();