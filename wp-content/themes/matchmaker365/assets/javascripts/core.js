
// To attach Backstrech as the body's background
(function() {
    "use strict";

    //introJs().addHints();
    

jQuery(document).ready(function($) { 

    $(document).ready(function(){


      /**
       * Floating help button using intro.js
       * 
       */
      $('.floating-help').on("click", function() {
        introJs().start();
      });
      
      // Get file extension for validation while uploading capability statement
      function checkFileExt(filename){
        filename = filename.toLowerCase();
        return filename.split('.').pop();
      }
      $('#validate-capability-statement').hide();
      $('#validate-capability-statement .capability-statemets-error').html("");
      $('#validate-council-logo').hide();
      $('#validate-council-logo .capability-statemets-error').html("");

      //Drop ZONE
      /**
       * 
       * Darg & Drop File Upload  
       * 
       * 
       */
       Dropzone.autoDiscover = false;
       var totalsize = 0.0;
       var totalfiles = 0;
       var maxtotalallowed = 25; //in MB
        $('#my-dropzone').dropzone({
            url: 'post.php',
            method: 'post',
            autoProcessQueue: false,
            //maxFilesize: 25, // MB
            // maxFiles:5,
            acceptedFiles:'.jpg,.jpeg,.png,application/pdf,.docx,.doc,.ppt,.pptx,.pdf',
            addRemoveLinks: true,
            init:function(){
              var myDropzone = this; 
              var exi_file_count = 0;
              var exi_file_size = 0;

              //Populate exisating files from server
              var files_paths = $('#my-dropzone').data('existing');
             
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
                  //alert(totalsize +  "<P>" + totalfiles );
                }
                  myDropzone.on("addedfile", function(file) { 
                    /*Do not accept same files*/
                    if (this.files.length) {
                      var _i, _len;
                      for (_i = 0, _len = this.files.length; _i < _len - 1; _i++) // -1 to exclude current file
                      {
                          if(this.files[_i].name === file.name && this.files[_i].size === file.size && this.files[_i].lastModifiedDate.toString() === file.lastModifiedDate.toString())
                          {
                              this.removeFile(file);
                          }
                      }
                    }

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
                      //alert('"' + file.name + '" is not a supported file format!');
                      //swal( "Unable to upload "+ file.name, "You can only upload .doc, .docx, .pdf, .jpg, .ppt or .pptx formats. Total file size should not exceed 25MB", "error");
                      $.confirm({
                        title:  'The file "' + file.name + '" cannot be uploaded',
                        content: 'You can only upload .doc, .docx, .pdf, .jpg, .ppt or .pptx formats. Total file size should not exceed 25MB',
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
                      // if(totalfiles !== 0) { totalfiles -= 1; }
                      // if(totalsize !== 0)  { totalsize -= parseFloat((file.size / (1024*1024)).toFixed(2)); }
                      // calcLivefilesize();
                  });
               
              
              //Disable submitting if there are no files
              
              var submitButton = document.querySelector("#comp_submit");
              submitButton.addEventListener("click", function(e) {
                if($('input[name=service_type]:checked').val() == 'seller'){
                        if( calcLivefilesize() != true){
                          e.preventDefault();
                          if($('#active_update_company').length > 0){ 
                             $('#active_update_company').parsley().validate(); 

                            if($('#active_update_company').parsley().isValid()){
                              $([document.documentElement, document.body]).animate({
                                scrollTop: $("#minority_category_block_uploads").offset().top - 100
                              }, 500);
                            }else{
                              
                                   $([document.documentElement, document.body]).animate({
                                       scrollTop: $('.parsley-error:first').offset().top - 100
                                   }, 500);
                            }

                          }
                          if($('#update_company').length > 0) { 
                            $('#update_company').parsley().validate(); 
                          
                            if($('#update_company').parsley().isValid()){
                              $([document.documentElement, document.body]).animate({
                                scrollTop: $("#minority_category_block_uploads").offset().top - 100
                              }, 500);
                            }else{
                                   $([document.documentElement, document.body]).animate({
                                       scrollTop: $('.parsley-error:first').prev().offset().top - 100
                                   }, 500);
                            }

                          }                         
                          if($('#reg_company').length > 0) { 
                            $('#reg_company').parsley().validate(); 

                            if($('#reg_company').parsley().isValid()){
                              $([document.documentElement, document.body]).animate({
                                scrollTop: $("#minority_category_block_uploads").offset().top - 100
                              }, 500);
                            }else{
                                   $([document.documentElement, document.body]).animate({
                                       scrollTop: $('.parsley-error:first,.parsley-errors-list:first').offset().top - 100
                                   }, 500);
                            }
                          
                          
                          }


                        }                    
                }
              });

            }
            
        });

        /**
         * 
         * Calculates file size uploaded to dropzone and changes to dropzone
         * 
         */

        function calcLivefilesize(){
           //alert(totalsize +  "<>" + totalfiles );
              if (totalfiles <= 0) {
                  totalsize = 0;
                  totalfiles = 0;
                  //alert("Please upload atleast one capability statement");
                  $('#validate-capability-statement').show();

                  $('#validate-capability-statement .capability-statemets-error').html("Please upload capability statement");
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




    /**
     * 
     * Keep topbar trasparent for home page
     * 
     */

        $(window).on("scroll", function() {
          if($(window).scrollTop() > 50) {
              $(".home_transparent").addClass("active");
          } else {
              //remove the background property so it comes transparent again (defined in your css)
            $(".home_transparent").removeClass("active");
          }
        });

        $(".dash-report-btn").on( "click", function(e) {
          $.fancybox.open(
            ['<div class="popnotice text-center"><div class="popnotice-downloading-box"><svg version="1.1" fill="#356ab3" xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 512 512" xml:space="preserve"><path d="M382.56 233.376A15.96 15.96 0 0 0 368 224h-64V16c0-8.832-7.168-16-16-16h-64c-8.832 0-16 7.168-16 16v208h-64a16.013 16.013 0 0 0-14.56 9.376c-2.624 5.728-1.6 12.416 2.528 17.152l112 128A15.946 15.946 0 0 0 256 384c4.608 0 8.992-2.016 12.032-5.472l112-128c4.16-4.704 5.12-11.424 2.528-17.152z"/><path d="M432 352v96H80v-96H16v128c0 17.696 14.336 32 32 32h416c17.696 0 32-14.304 32-32V352h-64z"/></svg></div><p>Report is being downloaded...</p></div>'],
              {
                  afterShow : function( instance, current ) {
                  setTimeout( function() {
                    $.fancybox.close(); 
                  },1600); // 3000 = 3 secs
                  },
                 
              }
          );

        });


         //Clear local storage of any kind after logout
         if($('.home').length > 0){
          localStorage.clear();
         }

        /**
         * 
         * Select 2 Multiple checkbox common initiation
         * 
         */
        $('.mm365-multicheck').select2({
          theme: "classic",
          placeholder: "Select all that applies",
          allowClear: true
        });
      /**
       * 
       * Select2 Single select box common initiation
       * 
       */
        $(".mm365-single").select2();

        //Other Industry MR
        var thevalue = 'other';
        var exists_othind = 0 != $('#industry option:selected[value='+thevalue+']').length;
          if($('#other_industry_input').val() === '' && exists_othind == false){
                  $('#other_industry_input').hide();
                  $('#other_industry_input').removeAttr('required');
            }else{
                  $('#other_industry_input').show();
                  $('#other_industry_input').attr('required','true');
          }

          //Other Services
          var exists_othser = 0 != $('#services option:selected[value='+thevalue+']').length;
          if($('#other_services_input').val() === '' && exists_othser == false){                  
                  $('#other_services_input').removeAttr('required');
                  $('#other_services_input').hide();
                }else{
                  $('#other_services_input').show();
                  $('#other_services_input').attr('required','true');
          }
          //Other Certification
          var exists_othcert = 0 != $('#certifications option:selected[value='+thevalue+']').length;
          if($('#other_certification_input').val() === '' && exists_othcert == false){
                  $('#other_certification_input').hide();
              }else{
                $('#other_certification_input').show();
          }

/**
 * 
 * Conditionally show form blocks based on company service type
 * 
 */


          $("#mr-advanced-block").hide();

          $("#expand-mr-block").on('click', function(e){
              e.preventDefault();
              var type = $(this).data('expandblock');

              $("#mr-advanced-block").slideToggle();
              if(type === 'mr-report'){
                $(this).text($(this).html() == '+ More Options' ? "- Less Options" : "+ More Options"); // using ternary operator.
              }else{
                $(this).text($(this).html() == '+ More Details' ? "- Less Details" : "+ More Details"); // using ternary operator.
              }
              
          });


        //Calander - restricted to one year gap
        if($(".from_date").length > 0){
          $(".from_date").flatpickr(
            {
              enableTime: false,
              //minDate: new Date().fp_incr(-364),
              maxDate: "today",
              disableMobile: "true",
              dateFormat: "m/d/Y",
              plugins: [new rangePlugin({ input: "#secondRangeInput"})],
              onChange: function (selectedDates, dateStr, instance) {
                if (selectedDates.length > 1) {
                    var range = instance.formatDate(selectedDates[1], 'U') - instance.formatDate(selectedDates[0], 'U');
                    range = range / 86400;
                    if(range > 365)
                    {
                        alert("Maximum duration between selected dates is one year.");
                        instance.clear()
                    }
                }
            },
          });
        }


        //Calander - restricted to one year gap
        if($(".from_date_ur").length > 0){
          $(".from_date_ur").flatpickr(
            {
              enableTime: false,
              //minDate: new Date().fp_incr(-364),
              maxDate: "today",
              disableMobile: "true",
              dateFormat: "m/d/Y",
              plugins: [new rangePlugin({ input: "#secondRangeInputUR"})],
              onChange: function (selectedDates, dateStr, instance) {
                // if (selectedDates.length > 1) {
                //     var range = instance.formatDate(selectedDates[1], 'U') - instance.formatDate(selectedDates[0], 'U');
                //     range = range / 86400;
                //     if(range > 365)
                //     {
                //         alert("Maximum duration between selected dates is one year.");
                //         instance.clear()
                //     }
                // }
            },
          });
        }



        //Enable calneder on icon click
        $('.calendar-icon').on("click", function() {
           $( this ).prev('input').trigger( "click" );
           //alert(1);
        })
        $(".from_date").on("change",function(){
          if($('#secondRangeInput').val() == "")
               $('.todateError').css({"display": "block"});
          else
               $('.todateError').css({"display": "none"});
               $('#secondRangeInput').removeClass('parsley-error');
               $('#secondRangeInput').addClass('parsley-success');
        });


        //Match Request Download Button Change
        $('#mrdownload_month').hide();
        $('#mrdownload_year').hide();
        $('#mrdownload_six_months').hide();
        $('#mm365_mrdownload_filter_select').on('change',function (){
          var filter = $(this).val();
          $('#mrdownload_two_week').hide();
          $('#mrdownload_month').hide();
          $('#mrdownload_six_months').hide();
          $('#mrdownload_year').hide();
          $('#mrdownload_' + filter).show();
        });


    });



/* Social Media Links */
  $('.extra-social-media').click(function(e) {
        e.preventDefault();
        $('.company-socialmedia').clone().find("input:text").val("").end().appendTo('.company-socialmedia-dynamic');
        $('.company-socialmedia-dynamic .company-socialmedia').addClass('single remove');
        $('.single .extra-social-media-btns').remove();
        $('.single .form-row').append('<div class="col-1 d-flex align-items-end justify-content-center extra-social-media-btns"><a href="#" class="remove-field btn-remove-customer plus-btn">-</a></div>');
        $('.company-socialmedia-dynamic > .single').attr("class", "remove");
      
        $('.company-socialmedia-dynamic input').each(function() {
          var count = 0;
          var fieldname = $(this).attr("name");
          $(this).attr('name', fieldname + count);
          count++;
        });
      
      });
      
      $(document).on('click', '.remove-field', function(e) {
        $(this).parentsUntil('.remove').remove();
        e.preventDefault();
 });


 
/* NAICS Code */
 $('.add-naics-code').click(function(e) {
        e.preventDefault();
        $('.naics-codes').clone().find("input").val("").end().appendTo('.naics-codes-dynamic');


        $('.naics-codes-dynamic .naics-codes').addClass('naics_single naics_remove');
        $('.naics_single .naics-codes-btn').remove();
        $('.naics_single .naic-info').html("");
        $('.naics_single .naic-info').removeClass("valid error");
        $('.naics_single .naics-input').css('border-color','#000');

        $('.naics_single .form-row').append('<div class="col-2 d-flex align-items-start naics-codes-btn"><a href="#" class="remove-naics-code plus-btn">-</a></div>');
        $('.naics-codes-dynamic > .naics_single').attr("class", "naics_remove");
      
        $('.naics-codes-dynamic input').each(function() {
          var count = 0;
          var fieldname = $(this).attr("name");
          $(this).attr('name', fieldname + count);
          count++;
        });
      
      });
      
      $(document).on('click', '.remove-naics-code', function(e) {
        $(this).parentsUntil('.naics_remove').remove();
        e.preventDefault();
 });



/* Main Customers */
$('.add-main-customer').click(function(e) {
  e.preventDefault();
  $('.main-customers').clone().find("input:text").val("").end().appendTo('.main-customers-dynamic');
  $('.main-customers-dynamic .main-customers').addClass('maincustomer_single maincustomer_remove');
  $('.maincustomer_single .main-customers-btn').remove();
  $('.maincustomer_single .form-row').append('<div class="col-2 d-flex  main-customers-btn align-items-end"><a href="#" class="remove-main-customer plus-btn">-</a></div>');
  $('.main-customers-dynamic > .maincustomer_single').attr("class", "maincustomer_remove");

  $('.main-customers-dynamic input').each(function() {
    var count = 0;
    var fieldname = $(this).attr("name");
    $(this).attr('name', fieldname + count);
    count++;
  });

});

$(document).on('click', '.remove-main-customer', function(e) {
  $(this).parentsUntil('.maincustomer_remove').remove();
  e.preventDefault();
});





var getUrlParameter = function getUrlParameter(sParam) {
  var sPageURL = window.location.search.substring(1),
      sURLVariables = sPageURL.split('&'),
      sParameterName,
      i;

  for (i = 0; i < sURLVariables.length; i++) {
      sParameterName = sURLVariables[i].split('=');

      if (sParameterName[0] === sParam) {
          return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
      }
  }
};

// Step 1: Create reusable jQuery plugin for popup dialogue
// =====================================

$.fancyConfirm = function( opts ) {
  opts  = $.extend( true, {
    title     : 'Are you sure?',
    message   : '',
    okButton  : 'OK',
    noButton  : 'Cancel',
    callback  : $.noop
  }, opts || {} );

  $.fancybox.open({
    type : 'html',
    src  :
    '<div class="fc-content">' +
    '<h3>' + opts.title   + '</h3>' +
    '<p>'  + opts.message + '</p>' +
    '<div class="row text-right">' +
    '<div class="col-12"><button data-value="0" class="btn" data-fancybox-close>' + opts.noButton + '</button>&nbsp;&nbsp;' +
    '<button data-value="1" data-fancybox-close class="btn btn-primary">' + opts.okButton + '</button></div>' +
    '</div>' +
    '</div>',
    opts : {
      animationDuration : 350,
      animationEffect   : 'material',
      modal : true,
      baseTpl :
      '<div class="fancybox-container fc-container" role="dialog" tabindex="-1">' +
      '<div class="fancybox-bg"></div>' +
      '<div class="fancybox-inner">' +
      '<div class="fancybox-stage"></div>' +
      '</div>' +
      '</div>',
      afterClose : function( instance, current, e ) {
        var button = e ? e.target || e.currentTarget : null;
        var value  = button ? $(button).data('value') : 0;

        opts.callback( value );
      }
    }
  });
}


// Step 2: Start using it!
// =======================
  // Open customized confirmation dialog window
$(document).ready( function(){

  //Company registartion verification
  var cid = getUrlParameter('cid');
  var rdi = getUrlParameter('rdi');

  // var mrid = getUrlParameter('mr_id');
  // var mr_state = getUrlParameter('mr_state');
  // var token = getUrlParameter('_wpnonce');
  // var base_url = window.location.origin; 
  var base_url = getUrlParameter('base'); //Use in localhost

  if(rdi){
    $.fancyConfirm({
      title     : "Continue Registration?",
      message   : "We have found a partially filled company registration in our database. You can continue editing the same by clicking the 'Continue' button below.<br/><br/> If you want to delete the existing data and start new click 'Disard' button",
      okButton  : 'Continue',
      noButton  : 'Discard',
      callback  : function (value) {
        if (value) {

        } else {
          window.location.href = base_url + "/delete-company-draft/?cid="+cid;
        }
      }
    });
  }

  //Matchrequest verification

  // if(mrid && token && mr_state=='draft'){

  //   $.confirm({
  //     title: 'Continue with the partially added Match request?',
  //     content: 'We have found an un submitted match requests in your account, if you want to continue editing the same click \'Continue\' button.<br/><br/> If you want to delete the existing data and start new click \'Disard\' button',
  //     theme: 'modern',
  //     icon: 'fas fa-exclamation-circle',
  //     type: 'red',
  //     buttons: {
  //         continue: {
  //           btnClass: 'btn btn-primary',
  //           action: function () {}
  //         },
  //         disacard: {
  //           btnClass: 'btn btn-primary red',
  //           action: function(){
  //             window.location.href = base_url + "/delete-matchrequest-draft?mr_id="+mrid+"&tokendel="+token;
  //           }
  //         }
  //     }
  //   });


  // }




//Add Icon to submenu
$('.menu-item-has-children').find('a:first').append("<span></span>");

//Additional Validation Rules


$(".uwp-registration-form").attr('autocomplete','off');
$(".uwp-registration-form #password").attr("pattern","^(?=.*[A-Za-z])(?=.*\\d)(?=.*[@$!%*#?&])[A-Za-z\\d@$!%*#?&]{8,}$");
$(".uwp-change-form #password").attr("pattern","^(?=.*[A-Za-z])(?=.*\\d)(?=.*[@$!%*#?&])[A-Za-z\\d@$!%*#?&]{8,}$");
$(".uwp-change-form #confirm_password").attr("pattern","^(?=.*[A-Za-z])(?=.*\\d)(?=.*[@$!%*#?&])[A-Za-z\\d@$!%*#?&]{8,}$");

$(".uwp_widget_reset #password").attr("pattern","^(?=.*[A-Za-z])(?=.*\\d)(?=.*[@$!%*#?&])[A-Za-z\\d@$!%*#?&]{8,}$");
$(".uwp_widget_reset #confirm_password").attr("pattern","^(?=.*[A-Za-z])(?=.*\\d)(?=.*[@$!%*#?&])[A-Za-z\\d@$!%*#?&]{8,}$");


$(".uwp-registration-form #password").attr("title","Minimum eight characters, at least one uppercase letter, one lowercase letter, one number and one special character from  @, ! ,%, *, #, ? and &");
$(".uwp-change-form #password").attr("title","Minimum eight characters, at least one uppercase letter, one lowercase letter, one number and one special character from  @, ! ,%, *, #, ? and &");
$(".uwp-change-form #confirm_password").attr("title","Minimum eight characters, at least one uppercase letter, one lowercase letter, one number and one special character from  @, ! ,%, *, #, ? and &");


$(".uwp-login-form #username").attr("placeholder","Username or Email");
$(".uwp-login-form #password").attr("placeholder","Password");
$(".uwp-login-form #confirm_password").attr("placeholder","Confirm Password");

$(".uwp-registration-form #first_name").attr("placeholder","First Name");
$(".uwp-registration-form #last_name").attr("placeholder","Last Name");
$(".uwp-registration-form #username").attr("placeholder","Username");
$(".uwp-registration-form #password").attr("placeholder","Password");
$(".uwp-registration-form #confirm_password").attr("placeholder","Confirm Password");
$(".uwp-registration-form #email").attr("placeholder","Email");
$(".uwp-registration-form #email").attr("pattern","[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,24}$");
$(".uwp-registration-form #username").attr("minlength","4");


$(".uwp-forgot-form #email").attr("placeholder","Email");
// $(".uwp-forgot-form #email").attr('data-parsley-required', 'true');
$(".uwp-forgot-form #email").attr("pattern","[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,24}$");

$(".uwp-change-form #old_password").attr("placeholder","Old Password");
$(".uwp-change-form #password").attr("placeholder","New Password");
$(".uwp-change-form #confirm_password").attr("placeholder","Confirm Password");
//$(".uwp-registration-form #email").after( "<span class='messageButton'><span class='messageButton-message'>Tool tip test</span></span>" );

$(".uwp-registration-form #email").on("focus",function (){ $(this).before( "<span class='messageButton'><span class='messageButton-message'>Enter a valid email address. Example: <strong>xyz@mmsdc.com</strong></span></span>" ); })
$(".uwp-registration-form #email").on("blur",function (){ $(this).prev('span').remove(); })

$(".uwp-login-form #username").on("focus",function (){ $(this).before( "<span class='messageButton'><span class='messageButton-message'>Enter a valid email address. Example: <strong>xyz@mmsdc.com</strong> OR your username</span></span>" ); })
$(".uwp-login-form #username").on("blur",function (){ $(this).prev('span').remove(); })

$(".uwp-registration-form #password,.uwp-login-form #password").on("focus",function (){ $(this).before( "<span class='messageButton'><span class='messageButton-message'>Password should have minimum 8 characters and it should be alpha numeric with atleast one special character from @, ! ,%, *, #, ? and &</span></span>" ); })
$(".uwp-registration-form #password,.uwp-login-form #password").on("blur",function (){ $(this).prev('span').remove(); })

$(".uwp-change-form #password").on("focus",function (){ $(this).before( "<span class='messageButton'><span class='messageButton-message'>Password should have minimum 8 characters and it should be alpha numeric with atleast one special character from  @, ! ,%, *, #, ? and &</span></span>" ); })
$(".uwp-change-form #password").on("blur",function (){ $(this).prev('span').remove(); })

$(".uwp-registration-form #first_name").attr("pattern","[A-Za-z]+");
$(".uwp-registration-form #last_name").attr("pattern","[A-Za-z]+");
$(".uwp-registration-form #first_name, .uwp-registration-form #last_name").attr("title","Only alphabets are allowed");

//Enable link on Terms  and conditions description on registartion form
var origin   = window.location.origin
$(".btn-register-tos label").html("I agree to the <a href='"+ origin +"/privacy-policy'>Terms & Conditions</a>");

$(".multiselect-icon").on("click", function() {
  
  $(this).prev(".select2-container").siblings('select:enabled').select2('open');
  
    
});

$('#confirm_password').on("cut copy paste",function(e) {
  e.preventDefault();
});



});

//Notofication library
Notiflix.Notify.init({
  width: '300px',
  position: 'right-top',
  closeButton: false,
  zindex: 9999999,
  messageMaxLength:250,
  info: {
    background: '#356ab3',
    notiflixIconColor:'rgba(255,255,255,0.8)'
  },
});


    //v2.9 onwards - currency input

    $("input[data-type='currency']").on({
      keyup: function () {
        formatCurrency($(this));
      },
      blur: function () {
        formatCurrency($(this), "blur");
      }
    });


    function formatNumber(n) {
      // format number 1000000 to 1,234,567
      return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",")
    }


    function formatCurrency(input, blur) {
      // appends $ to value, validates decimal side
      // and puts cursor back in right position.

      // get input value
      var input_val = input.val();

      // don't validate empty input
      if (input_val === "") { return; }

      // original length
      var original_len = input_val.length;

      // initial caret position 
      var caret_pos = input.prop("selectionStart");

      // check for decimal
      if (input_val.indexOf(".") >= 0) {

        // get position of first decimal
        // this prevents multiple decimals from
        // being entered
        var decimal_pos = input_val.indexOf(".");

        // split number by decimal point
        var left_side = input_val.substring(0, decimal_pos);
        var right_side = input_val.substring(decimal_pos);

        // add commas to left side of number
        left_side = formatNumber(left_side);

        // validate right side
        right_side = formatNumber(right_side);

        // On blur make sure 2 numbers after decimal
        if (blur === "blur") {
          right_side += "00";
        }

        // Limit decimal to only 2 digits
        right_side = right_side.substring(0, 2);

        // join number by .
        input_val = "$" + left_side + "." + right_side;

      } else {
        // no decimal entered
        // add commas to number
        // remove all non-digits
        input_val = formatNumber(input_val);
        input_val = "$" + input_val;

        // final formatting
        if (blur === "blur") {
          input_val += ".00";
        }
      }

      // send updated string to input
      input.val(input_val);

      // put caret back in the right position
      var updated_len = input_val.length;
      caret_pos = updated_len - original_len + caret_pos;
      input[0].setSelectionRange(caret_pos, caret_pos);
    }



  //Stop Editing
});

})();