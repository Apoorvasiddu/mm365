(function() {
    "use strict";
  jQuery(document).ready(function($) { 
    //STARTS HERE

    if($("#conference_description").length > 0){
        // tinyMCE.init({
        //     mode : "none",
        //     statusbar: true,
        //     file_picker_callback_types: 'image',
        //     file_picker_callback: wpmediabrowser,
        //     placeholder: "Please add proper description about your requirement, make sure that you have added all the necessary keywords about your products and services",
        //     content_style:
        //     "body { background: #fff; color: #333; font-size: 13pt; }",
        //     setup: function (editor) {
        //         editor.on('change', function () {
        //             editor.save();
        //             $("#help_desc_blocks").parsley().reset();
        //         });
        //     },
        //     mobile: {
        //         theme: 'mobile',
        //     },
        //     branding: false,
        //     //plugins: "paste lists",              
        //     //toolbar: 'numlist bullist'
        //     menubar: false,
        //     plugins: [
        //     'advlist autolink lists link image charmap print preview anchor',
        //     'searchreplace visualblocks  fullscreen',
        //     'insertdatetime media table paste wordcount paste anchor textcolor mediaembed code'
        //     ],
        //     toolbar: ' link anchor undo redo | formatselect | ' +
        //     'bold italic forecolor backcolor | alignleft aligncenter ' +
        //     'alignright alignjustify | bullist numlist outdent indent | ' +
        //     'removeformat',
        //     paste_as_text: true,
            
        // });
        // tinyMCE.execCommand('mceAddEditor', false, 'conference_description');
        tinymce.init({
          selector: '#conference_description',
          menubar: 'file edit view',
          plugins: 'anchor autolink charmap  emoticons  link lists  searchreplace table visualblocks wordcount',
          placeholder: "",
          toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
          setup: function (editor) {
                editor.on('change', function () {
                    editor.save();
                    $("#help_desc_blocks").parsley().reset();
                });
          },
        });
      
    }

    // Get image from wp-uploader
    function wpmediabrowser(callback, value, meta)
    {
        var image = wp.media({ 
            title: 'Upload Image',
            multiple: false
        }).open().on('select', function(e){
            
            var uploaded_image = image.state().get('selection').first();
            callback(uploaded_image["attributes"]["url"], { title: uploaded_image.name });
    
        });
    }



/**
 * Date and time Icker
 * 
 */
 var d = new Date();
 var month    = d.getMonth()+1;
 var day      = d.getDate();
 var min_date = ((''+month).length<2 ? '0' : '') + month + '/' + ((''+day).length<2 ? '0' : '') + day + '/' + d.getFullYear()  + ' 09:00 AM';
 
     //Time and date picker
     if($(".meeting_date_1").val() == ''){
       $(".from_time_1,.to_time_1").prop('disabled', true);
     }
     var startPicker = flatpickr(".meeting_date_1", {
                 disableMobile: true,
                 enableTime: true,
                 time_24hr: false,
                 dateFormat: "m/d/Y",
                 defaultHour:'6',
                 minDate:new Date().fp_incr(1),
                 onValueUpdate:  function(selectedDates, dateStr, instance) {
                   //startTime.clear();
                   if(dateStr != ''){
                     $('.first_choice_error').css({"display": "none"});
                     $('.meeting_date_1').removeClass('parsley-error');
                     $('.meeting_date_1').addClass('parsley-success');
                   }else{
                     $('.first_choice_error').css({"display": "block"});
                     $('.meeting_date_1').addClass('parsley-error');
                     $('.meeting_date_1').removeClass('parsley-success');
                     
                   }
                 },
                 onChange: function(selectedDates, dateStr, instance) {
                   //alert(dateStr);
                     $(".from_time_1,.to_time_1").prop('disabled', false); 
                     startPicker.set('minDate', new Date().fp_incr(1));
                     startTime.clear();
                     endPicker.clear();
                     startTime.set('minDate', selectedDates[0]);
                     endPicker.set('minDate', selectedDates[0]);    
                     registrationClosingDate.set('maxDate', selectedDates[0]);
                     registrationClosingDate.set('minDate', new Date().fp_incr(1));
                    }
     });
     var startTime = flatpickr(".from_time_1", { 
                                       enableTime: true,
                                       disableMobile: true,
                                       time_24hr: false, 
                                       dateFormat: "h:i K",
                                       noCalendar: true,
                                       minuteIncrement:1,
                                       //minDate: new Date().fp_incr(1),
                                       onValueUpdate:  function(selectedDates, dateStr, instance) {
                                         if(dateStr != ''){
                                           $('.from_time_1_error').css({"display": "none"});
                                           $('.from_time_1').removeClass('parsley-error');
                                           $('.from_time_1').addClass('parsley-success');
                                         }else{
                                           $('.from_time_1_error').css({"display": "block"});
                                           $('.from_time_1').addClass('parsley-error');
                                           $('.from_time_1').removeClass('parsley-success');
                       
                                         }
                                       },
                                       onChange: function(selectedDates, dateStr, instance) {
                                         endPicker.clear();
                                         if(dateStr != ''){
                                           var twentyMinutesLater = selectedDates[0];
                                           twentyMinutesLater.setMinutes(twentyMinutesLater.getMinutes() + 10);
                                           endPicker.set('minDate', twentyMinutesLater);
                                         }
                                       }
                                });
                                
     var endPicker = flatpickr(".to_time_1", { 
                                       enableTime: true,
                                       disableMobile: true,
                                       time_24hr: false, 
                                       dateFormat: "h:i K",
                                       noCalendar: true,
                                     });

     var registrationClosingDate = flatpickr(".registration_closing_date", { 
                                      enableTime: false,
                                      disableMobile: true,
                                      time_24hr: false, 
                                      dateFormat: "m/d/Y",
              });                                

/**
 * Pre load time
 * 
 */

 var timezone_offset_minutes = new Date().getTimezoneOffset();
 timezone_offset_minutes = timezone_offset_minutes == 0 ? 0 : -timezone_offset_minutes;
 
 var today = new Date();
 var jan = new Date(today.getFullYear(), 0, 1);
 var jul = new Date(today.getFullYear(), 6, 1);
 var dst = today.getTimezoneOffset() < Math.max(jan.getTimezoneOffset(), jul.getTimezoneOffset());
 
 $.ajax({ 
   url : confSharedAjax.ajax_url,
   data : {
       action:'read_timezone',
       timezone:timezone_offset_minutes,
       offset:-today.getTimezoneOffset() / 60,
       dst: +dst
   },
   type: 'POST',      
   success : function (data){
     $('#local-timezone').html(data);
     setTimeout(() => {
       //console.log(data);
       $('#timezone').val(data); 
       $('#timezone').trigger('change');
 
       const convertedDate = convertTZ(new Date(), data) ;
       var myoffset = convertedDate.getTimezoneOffset() / 60; // 17
 
      $('.show_user_tz').html("Converted to " + data + " time");
       
     }, 100);
   }         
 }); 
 
 function convertTZ(date, tzString) {
   return new Date((typeof date === "string" ? new Date(date) : date).toLocaleString("en-US", {timeZone: tzString}));   
 }
 
 

 /**
  * Update conference screen
  * Preloading buyers
  * 
  */

  if( $( '#mm365_council_update_conference #participating_buyers' ).length > 0 ) {
    var buyerSelect = $('#participating_buyers');
    var conf_id = buyerSelect.data('conf_id');

     if(conf_id != ''){
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: confSharedAjax.ajax_url,
            data : {
              conf_id: conf_id,
              action: 'get_existing_buyers_in_conference',
              nonce: confSharedAjax.nonce,
            },
        }).then(function (data) {

            $.each( data, function( index, text ) {
              var option = new Option(text.text, text.id, true, true);
              buyerSelect.append(option).trigger('change');
            });

            //manually trigger the `select2:select` event
            buyerSelect.trigger({
                type: 'select2:select',
                params: {
                    data: data
                }
            });
        });
    }

}

/**
 * 
 * 
 * 
 */
 
 if( $( '#mm365_council_update_conference #fellow_council_managers' ).length > 0 ) {
  var councilManagersSelect = $('#fellow_council_managers');
  var conf_id = councilManagersSelect.data('conf_id');

   if(conf_id != ''){
      $.ajax({
          type: 'POST',
          dataType: 'json',
          url: confSharedAjax.ajax_url,
          data : {
            conf_id: conf_id,
            action: 'get_existing_councilmanagers_in_conference',
            nonce: confSharedAjax.nonce,
          },
      }).then(function (data) {

          $.each( data, function( index, text ) {
            var option = new Option(text.text, text.id, true, true);
            councilManagersSelect.append(option).trigger('change');
          });

          //manually trigger the `select2:select` event
          councilManagersSelect.trigger({
              type: 'select2:select',
              params: {
                  data: data
              }
          });
      });
  }

}

/**
  * 
  * Update User
  * 
  */
//  $('form#update_help_docs').submit(function(e){
//     e.preventDefault(); 
//     var redirect_to = $('#after_success_redirect').val();
//     var form        = $('form')[0];
//     var formdata    = new FormData(form);
//     formdata.append('action', 'update_help_page');
//     formdata.append('nonce',manageHelpPageAjax.nonce);
//     if ( $(this).parsley().isValid() ) { 
  
//         $.ajax({ 
//             url : manageHelpPageAjax.ajax_url,
//             data: formdata,
//             type: 'POST',                   
//             contentType: false,
//             processData: false,
//             beforeSend: function() { 
//                 $('html, body').animate({ scrollTop: 0 }, 'slow');
//                 Notiflix.Loading.hourglass('Updating help page contents...',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });
//             },
//             success : function( data ){
//                 Notiflix.Loading.remove(100);
//                 if($.trim(data) == 'success'){
//                     Notiflix.Notify.success('Content updated');
//                 }else{
//                     Notiflix.Notify.failure('Update failed');
//                 }
                
  
//             }
//         });
//     }
//   });



/**
 * Publish a newly created confernce
 * Button action
 * 
 */

      $(document).on("click", '#conf_publish', function(){

         var conf_id = $(this).data('conf_id');
         var redirect_to = $(this).data('redirect_to');

         $.ajax({ 
          url : confSharedAjax.ajax_url,
          data: {
            action: 'publish_conference',
            conf_id: conf_id,
            nonce: confSharedAjax.nonce
          },               
          beforeSend: function() { 
              $('html, body').animate({ scrollTop: 0 }, 'slow');
              Notiflix.Loading.hourglass('Publishing conference...',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });

          },
          success : function( data ){
              if( data) { 
                 $('html, body').animate({ scrollTop: 0 }, 0);
                  if(data){
                      Notiflix.Loading.remove(1500);
                      Notiflix.Notify.success('Conference published');
                      setTimeout(() => {
                        window.location = redirect_to; 
                      }, 1500);
                    }else{
                      Notiflix.Loading.remove(1500);
                      Notiflix.Notify.failure('Unable to publish conference!');
                    }
                 
              } else {
                  Notiflix.Notify.failure('Unknown error!');
              }
          }
         });

     });


/**
 * Supplier applying for participating the conference
 * Popup open action
 * 
 */

    $('#applyConferenceParticipation').on("click", function(){
      $.fancybox.open({
        src : '#applyConferenceParticiaptionForm',
        type: 'inline',
        touch: false,
        smallBtn : true,
      });
    });



    /**
     * 
     * Apply for conference particiaption
     * 
     */
     $('form#mm365_apply_offline_conf_particiaption').submit(function(e){
      e.preventDefault(); 
      //var prp_timezone = $('#proposer_timezone').val();
      //var redirect_to = $('#after_schedule_redirect').val();
      var form        = $('form')[0];
      var formdata    = new FormData(form);
      formdata.append('action', 'apply_offline_conference_particiaption');
      formdata.append('nonce',confSharedAjax.nonce);
      //formdata.append('proposer_timezone', prp_timezone);
      if ( $(this).parsley().isValid() ) { 
          $.ajax({ 
              url : confSharedAjax.ajax_url,
              data: formdata,
              type: 'POST',                   
              contentType: false,
              processData: false,
              beforeSend: function() { 
                  $('html, body').animate({ scrollTop: 0 }, 'slow');
                  Notiflix.Loading.hourglass('Applying conference participation...',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });
                  $.fancybox.close();
              },
              success : function( data ){
                  if( data ) { 
                     $('html, body').animate({ scrollTop: 0 }, 0);
                      if(data){
                          Notiflix.Loading.remove(1500);
                          Notiflix.Notify.success('Application submitted.');
                          setTimeout(() => {
                            window.location.reload();
                          }, 1500);
                          
                        }else{
                          Notiflix.Loading.remove(1500);
                          Notiflix.Notify.failure('Unable to create conference!');
                        }
                     
                  } else {
                      Notiflix.Notify.failure('Unknown error!');
                  }
              }
          }); 
     }

  });


    //ENDS HERE
  });
  
})();