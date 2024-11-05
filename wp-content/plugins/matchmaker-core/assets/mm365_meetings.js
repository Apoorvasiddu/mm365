(function() {
    "use strict";
  jQuery(document).ready(function($) { 


    //var browser_timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    //console.log(browser_timezone);
    //$('#proposer_timezone').val(browser_timezone); 


/** 
 * Conditional validtion if the second date is selected
 * + v2.0 onwards
 * 
*/

$("[name=second_choice]").on('change', function () {
    var val = $(this).val();
    if(val != '') {
        $("[name=second_choice_starttime]").attr("required", "required");
        $("[name=second_choice_endtime]").attr("required", "required");
    }else{
      $("[name=second_choice_starttime]").removeAttr("required");
      $("[name=second_choice_endtime]").removeAttr("required");
    }
    $("form").parsley().reset();
});

$("[name=third_choice]").on('change', function () {
  var val = $(this).val();
  if(val != '') {
      $("[name=third_choice_starttime]").attr("required", "required");
      $("[name=third_choice_endtime]").attr("required", "required");
  }else{
    $("[name=third_choice_starttime]").removeAttr("required");
    $("[name=third_choice_endtime]").removeAttr("required");
  }
  $("form").parsley().reset();
});



/* Time zone experimanets */

var timezone_offset_minutes = new Date().getTimezoneOffset();
timezone_offset_minutes = timezone_offset_minutes == 0 ? 0 : -timezone_offset_minutes;

var today = new Date();
var jan = new Date(today.getFullYear(), 0, 1);
var jul = new Date(today.getFullYear(), 6, 1);
var dst = today.getTimezoneOffset() < Math.max(jan.getTimezoneOffset(), jul.getTimezoneOffset());

$.ajax({ 
  url : meetingAjax.ajax_url,
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
      $('#proposer_timezone').val(data); 
      $('#proposer_timezone').trigger('change');

      $('#rescheduler_timezone').val(data); 
      $('#rescheduler_timezone').trigger('change');

      $('#attendee_timezone').val(data); 
      $('#attendee_timezone').trigger('change');
      
      const convertedDate = convertTZ(new Date(), data) ;
      var myoffset = convertedDate.getTimezoneOffset() / 60; // 17

     $('.show_user_tz').html("Converted to " + data + " time");
      
    }, 100);
  }         
}); 

function convertTZ(date, tzString) {
  return new Date((typeof date === "string" ? new Date(date) : date).toLocaleString("en-US", {timeZone: tzString}));   
}



//remove UTC and manual time offsets from timezone dropdown
$("#proposer_timezone,#attendee_timezone,#rescheduler_timezone").children("optgroup[label='Manual Offsets'],optgroup[label='UTC']").remove();

    /* Init MCE for meeting agenda */
  if($('#meeting_agenda').length > 0){
    // tinyMCE.init({
    //     mode : "none",
    //     statusbar: true,
    //     menubar: false,
    //     toolbar: 'undo redo | styleselect | bold italic | link',
    //     placeholder: "Please enter the meeting agenda and notes if any",
    //     content_style:
    //     "body { background: #fff; color: #333; font-size: 13pt; }",
    //     setup: function (editor) {
    //         editor.on('change', function () {
    //             editor.save();
    //             $("#meeting_agenda").parsley().reset();
    //         });
    //     },
    //     mobile: {
    //         theme: 'mobile',
    //       },
    //     branding:false,
    //     plugins: " paste, link, autolink",
    //     paste_as_text: true
        
    // });
    // tinyMCE.execCommand('mceAddEditor', false, 'meeting_agenda');

    tinymce.init({
      selector: '#meeting_agenda',
      menubar: '',
      plugins: 'anchor autolink charmap  emoticons  link lists  searchreplace table visualblocks wordcount',
      placeholder: "Please add description about the meeting",
      toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
      setup: function (editor) {
            editor.on('change', function () {
                editor.save();
                $("#meeting_agenda").parsley().reset();
            });
      },
    });


  }




/*------------------------------------------------TIME PICKERS---------------------------------------------------------- */

var attendee = $("input[name='meeting_with_company_id']").val(); 
var proposer = $("input[name='proposed_company_id']").val(); 
var exclude  = $("input[name='exclude_post']").val(); 

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
                   
    $( ".to_time_1" ).change(function() {
    
      //The same field is used in attendees meeting response screen
      if($('#proposer_timezone').length > 0){
        var prp_timezone = $('#proposer_timezone').val();
      }
      if($('#attendee_timezone').length > 0){
        var prp_timezone = $('#attendee_timezone').val();
      } 
      if($('#rescheduler_timezone').length > 0){
        var prp_timezone = $('#rescheduler_timezone').val();
      }

      var date     = $('.meeting_date_1').val();
      var td       = date +" "+ $(this).val();
      var fd       = date +" "+ $('.from_time_1').val();
      var diff     = daysdifference(fd,td);
      var trigAjax = timeDiff(fd,td);
      if(trigAjax > 0) { $('#showdiff_1').html("Duration: " + diff);
         findDuplicateslots('one');
         $("#proposer_timezone").select2({disabled:'readonly'});
      }
      //Trasfer the values to ajax to check if slot is already booked
      if(trigAjax > 0){
          $.ajax({ 
            url : meetingAjax.ajax_url,
            data : {
                action:'is_slot_availabale',
                start:fd,
                end:td,
                attendee:attendee,
                proposer:proposer,
                exclude:exclude,
                timezone: prp_timezone
            },
            type: 'POST', 
            beforeSend: function() { 
              $('#mm365_create_meeting .btn-primary').prop('disabled',true);
              $('#mm365_meeting_invite .btn-primary, #mm365_request_reschedule_meeting .btn-primary').prop('disabled',true);
              $('#mm365_edit_meeting .btn-primary, #mm365_reschedule_meeting .btn-primary').prop('disabled',true);
              
            },                 
            success : function( data ){
                  if(data != ''){
                      $.confirm({
                          title:  'The selected slot is not available',
                          content: 'Either of the parties have already involved in a meeting for the selected time slot',
                          type: 'red',
                          typeAnimated: true,
                          icon: 'fas fa-exclamation-circle',
                          theme: 'modern',
                          buttons: {
                            close: {
                              btnClass: 'btn btn-primary',
                              action: function(){
                                $('.meeting_date_1').val('');
                                $('.from_time_1').val('');
                                $('.to_time_1').val('');
                                $('#showdiff_1').html('');
                                $('#mm365_create_meeting .btn-primary').prop('disabled',false);
                                $('#mm365_meeting_invite .btn-primary, #mm365_request_reschedule_meeting .btn-primary').prop('disabled',false);
                                $('#mm365_edit_meeting .btn-primary, #mm365_reschedule_meeting .btn-primary').prop('disabled',false);
                              }
                            }
                          }
                      });
                    }else{  
                      $('#mm365_create_meeting .btn-primary').prop('disabled',false); 
                      $('#mm365_meeting_invite .btn-primary, #mm365_request_reschedule_meeting .btn-primary').prop('disabled',false); 
                      $('#mm365_edit_meeting .btn-primary, #mm365_reschedule_meeting .btn-primary').prop('disabled',false);
                    
                    }
                    //$('#showdiff_1').append(data);
            }
        }); 
      }


    });
    
    //Set_2
    if($(".meeting_date_2").val() == ''){
      $(".from_time_2,.to_time_2").prop('disabled', true);
    }
    var startPicker_2 = flatpickr(".meeting_date_2", {
                enableTime: true,
                disableMobile: true,
                time_24hr: false,
                dateFormat: "m/d/Y",
                defaultHour:'6',
                minDate:new Date().fp_incr(1),
                onChange: function(selectedDates, dateStr, instance) {
                    $(".from_time_2,.to_time_2").prop('disabled', false); 
                    startPicker_2.set('minDate', new Date().fp_incr(1));
                    startTime_2.clear();
                    endPicker_2.clear();
                    startTime_2.set('minDate', selectedDates[0]);
                    endPicker_2.set('minDate', selectedDates[0]);
                    
                   }
    });
    var startTime_2 = flatpickr(".from_time_2", { 
                                      enableTime: true,
                                      disableMobile: true,
                                      time_24hr: false, 
                                      dateFormat: "h:i K",
                                      noCalendar: true,
                                      minuteIncrement:1,
                                      onChange: function(selectedDates, dateStr, instance) {
                                        endPicker_2.clear();
                                        if(dateStr != ''){
                                          var twentyMinutesLater = selectedDates[0];
                                          twentyMinutesLater.setMinutes(twentyMinutesLater.getMinutes() + 10);
                                          endPicker_2.set('minDate', twentyMinutesLater);
                                        }
                                      }
                                    });
    var endPicker_2 = flatpickr(".to_time_2", { 
                                      enableTime: true,
                                      disableMobile: true,
                                      time_24hr: false, 
                                      dateFormat: "h:i K",
                                      noCalendar: true,
                                    });
                   
    $( ".to_time_2" ).change(function() {
      var date = $('.meeting_date_2').val();
      var td = date +" "+ $(this).val();
      var fd = date +" "+ $('.from_time_2').val();
      var prp_timezone = $('#proposer_timezone').val();
      var trigAjax = timeDiff(fd,td);
      if(trigAjax > 0) {
        $('#showdiff_2').html("Duration: " + daysdifference(fd,td));
        //check if selected  date is same as pref_date_1 or pref_date_1
        findDuplicateslots('two');
        $("#proposer_timezone").select2({disabled:'readonly'});
      }

          if(trigAjax > 0){
            $.ajax({ 
              url : meetingAjax.ajax_url,
              data : {
                  action:'is_slot_availabale',
                  start:fd,
                  end:td,
                  attendee:attendee,
                  proposer:proposer,
                  exclude:exclude,
                  timezone: prp_timezone
              },
              type: 'POST',      
              beforeSend: function() { 
                $('#mm365_create_meeting .btn-primary').prop('disabled',true);
                $('#mm365_edit_meeting .btn-primary').prop('disabled',true);
              },              
              success : function( data ){
                    if(data != ''){
                      $.confirm({
                          title:  'The selected slot is not available',
                          content: 'Either of the parties have already been involved in a meeting',
                          type: 'red',
                          typeAnimated: true,
                          icon: 'fas fa-exclamation-circle',
                          theme: 'modern',
                          buttons: {
                            close: {
                              btnClass: 'btn btn-primary',
                              action: function(){
                                $('.meeting_date_2').val('');
                                $('.from_time_2').val('');
                                $('.to_time_2').val('');
                                $('#showdiff_2').html('');
                                $('#mm365_create_meeting .btn-primary').prop('disabled',false);
                                $('#mm365_edit_meeting .btn-primary').prop('disabled',false);
                              }
                            }
                          }
                      });
                      
                    }else{  $('#mm365_create_meeting .btn-primary').prop('disabled',false);
                            $('#mm365_edit_meeting .btn-primary').prop('disabled',false);
                          }
              }
          }); 
        }

    });
    
    //Third set
    if($(".meeting_date_3").val() == ''){
      $(".from_time_3,.to_time_3").prop('disabled', true);
    }
    var startPicker_3 = flatpickr(".meeting_date_3", {
                enableTime: true,
                disableMobile: true,
                time_24hr: false,
                dateFormat: "m/d/Y",
                defaultHour:'6',
                minDate:new Date().fp_incr(1),
                onChange: function(selectedDates, dateStr, instance) {
                    $(".from_time_3,.to_time_3").prop('disabled', false); 
                    startPicker_3.set('minDate', new Date().fp_incr(1));
                    startTime_3.clear();
                    endPicker_3.clear();
                    startTime_3.set('minDate', selectedDates[0]);
                    endPicker_3.set('minDate', selectedDates[0]);
                   
                   }
    });
    var startTime_3 = flatpickr(".from_time_3", { 
                                      enableTime: true,
                                      disableMobile: true,
                                      time_24hr: false, 
                                      dateFormat: "h:i K",
                                      noCalendar: true,
                                      minuteIncrement:1,
                                      onChange: function(selectedDates, dateStr, instance) {
                                        endPicker_3.clear();
                                        if(dateStr != ''){
                                          var twentyMinutesLater = selectedDates[0];
                                          twentyMinutesLater.setMinutes(twentyMinutesLater.getMinutes() + 10);
                                          endPicker_3.set('minDate', twentyMinutesLater);
                                        }
                                      }
                                    });
    var endPicker_3 = flatpickr(".to_time_3", { 
                                      enableTime: true,
                                      disableMobile: true,
                                      time_24hr: false, 
                                      dateFormat: "h:i K",
                                      noCalendar: true,
                                    });
                   
    $( ".to_time_3" ).change(function() {
      var date = $('.meeting_date_3').val();
      var td = date +" "+ $(this).val();
      var fd = date +" "+ $('.from_time_3').val();
      var trigAjax = timeDiff(fd,td);
      var prp_timezone = $('#proposer_timezone').val();
      if(trigAjax > 0) {
        findDuplicateslots('three');
        $('#showdiff_3').html("Duration: " + daysdifference(fd,td));
        $("#proposer_timezone").select2({disabled:'readonly'});
      }
         if(trigAjax > 0){
            $.ajax({ 
              url : meetingAjax.ajax_url,
              data : {
                  action:'is_slot_availabale',
                  start:fd,
                  end:td,
                  attendee:attendee,
                  proposer:proposer,
                  exclude:exclude,
                  timezone: prp_timezone
              },
              type: 'POST',    
              beforeSend: function() { 
                $('#mm365_create_meeting .btn-primary').prop('disabled',true);
                $('#mm365_edit_meeting .btn-primary').prop('disabled',true);
              },                 
              success : function( data ){
                     if(data != ''){
                          $.confirm({
                              title:  'The selected slot is not available',
                              content: 'Either of the parties have already been involved in a meeting for the selected time. Clicking close button will clear the input fields',
                              type: 'red',
                              typeAnimated: true,
                              icon: 'fas fa-exclamation-circle',
                              theme: 'modern',
                              buttons: {
                                close: {
                                  btnClass: 'btn btn-primary',
                                  action: function(){
                                      $('.meeting_date_3').val('');
                                      $('.from_time_3').val('');
                                      $('.to_time_3').val('');
                                      $('#showdiff_3').html('');
                                      $('#mm365_create_meeting .btn-primary').prop('disabled',false);
                                      $('#mm365_edit_meeting .btn-primary').prop('disabled',false);
                                  }
                                }
                              }
                          });
                      
                      }else{  
                        $('#mm365_create_meeting .btn-primary').prop('disabled',false); 
                        $('#mm365_edit_meeting .btn-primary').prop('disabled',false);
                    }
              }
          }); 
        }

      
    });

    function daysdifference(firstDate, secondDate)
        {
                    var startDay      = new Date(firstDate);
                    var endDay        = new Date(secondDate);
                    var millisBetween = endDay.getTime() - startDay.getTime();
                    var days          = Math.floor(millisBetween) / 1000 / 60;
                    var hoursDiff     = Math.floor(days / 60);
                    var minsDiff      = days % 60;
                    if(hoursDiff > '0' && minsDiff > '0'){
                    return hoursDiff + 'h and ' + minsDiff + 'm';
                    }
                    else if(hoursDiff > '0' && minsDiff == '0'){
                    return hoursDiff + 'h';
                    }else {  return minsDiff + 'm'; }
    }
    function timeDiff(firstDate, secondDate){
      var startDay      = new Date(firstDate);
      var endDay        = new Date(secondDate);
      var millisBetween = endDay.getTime() - startDay.getTime();
      return millisBetween;
    }
    function withinRange(int,min,max){
      return (min<=int && int<=max);
   }

    function findDuplicateslots(ref = 'one'){
      var pref_1_date = $('.meeting_date_1').val();
      var p1_start    = pref_1_date +" "+$('.from_time_1').val();
      var p1_end      = pref_1_date +" "+$('.to_time_1').val();

      var pref_2_date = $('.meeting_date_2').val();
      var p2_start    = pref_2_date +" "+ $('.from_time_2').val();
      var p2_end      = pref_2_date +" "+ $('.to_time_2').val();

      var pref_3_date = $('.meeting_date_3').val();
      var p3_start    = pref_3_date +" "+ $('.from_time_3').val();
      var p3_end      = pref_3_date +" "+ $('.to_time_3').val();

      switch (ref) {
        case 'three':
            if(pref_3_date == pref_2_date ||  
               pref_3_date == pref_1_date){
                //alert('duplicate date 3' + p3_start + " " + p3_end);
                var start_p1 = new Date(p1_start);
                var end_p1   = new Date(p1_end);
                var start_p2 = new Date(p2_start);
                var end_p2   = new Date(p2_end);
                //current
                var start    = new Date(p3_start);
                var end      = new Date(p3_end);
                //p1
                var _slot_free = 'y';
                if(withinRange(start.getTime(),start_p1.getTime(),end_p1.getTime() )){ _slot_free = 'n'; }
                if(withinRange(end.getTime(),start_p1.getTime(),end_p1.getTime() )){ _slot_free = 'n'; }
                if(withinRange(start_p1.getTime(),start.getTime(),end.getTime() )){ _slot_free = 'n'; }
                //p2
                if(withinRange(start.getTime(),start_p2.getTime(),end_p2.getTime() )){ _slot_free = 'n'; }
                if(withinRange(end.getTime(),start_p2.getTime(),end_p2.getTime() )){ _slot_free = 'n'; }
                if(withinRange(start_p2.getTime(),start.getTime(),end.getTime() )){ _slot_free = 'n'; }
                if(_slot_free == 'n'){
                  $.confirm({
                    title:  'This slot is already selected',
                    content: 'Clicking close button will clear the selected date input',
                    type: 'red',
                    typeAnimated: true,
                    icon: 'fas fa-exclamation-circle',
                    theme: 'modern',
                    buttons: {
                      close: {
                        btnClass: 'btn btn-primary',
                        action: function(){
                          $('.meeting_date_3').prop("required", false).val('');
                          $('.from_time_3').prop("required", false).val('');
                          $('.to_time_3').prop("required", false).val('');
                          $('#showdiff_3').html('');
                        }
                      }
                    }
                  });
                }
             }
          break;
        case 'two':
              if(pref_2_date == pref_1_date ||  
                 pref_2_date == pref_3_date){
                  var start_p1 = new Date(p1_start);
                  var end_p1   = new Date(p1_end);
                  var start_p3 = new Date(p3_start);
                  var end_p3   = new Date(p3_end);
                  //current
                  var start    = new Date(p2_start);
                  var end      = new Date(p2_end);
                  //P1
                  var _slot_free = 'y';
                  if(withinRange(start.getTime(),start_p1.getTime(),end_p1.getTime() )){ _slot_free = 'n'; }
                  if(withinRange(end.getTime(),start_p1.getTime(),end_p1.getTime() )){ _slot_free = 'n';}
                  if(withinRange(start_p1.getTime(),start.getTime(),end.getTime() )){ _slot_free = 'n'; }
                  //P3
                  if(withinRange(start.getTime(),start_p3.getTime(),end_p3.getTime() )){ _slot_free = 'n'; }
                  if(withinRange(end.getTime(),start_p3.getTime(),end_p3.getTime() )){ _slot_free = 'n'; }
                  if(withinRange(start_p3.getTime(),start.getTime(),end.getTime() )){ _slot_free = 'n'; }
                  if(_slot_free == 'n'){
                    $.confirm({
                      title:  'This slot is already selected',
                      content: 'Clicking close button will clear the selected date input',
                      type: 'red',
                      typeAnimated: true,
                      icon: 'fas fa-exclamation-circle',
                      theme: 'modern',
                      buttons: {
                        close: {
                          btnClass: 'btn btn-primary',
                          action: function(){
                            $('.meeting_date_2').prop("required", false).val('');
                            $('.from_time_2').prop("required", false).val('');
                            $('.to_time_2').prop("required", false).val('');
                            $('#showdiff_2').html('');
                          }
                        }
                      }
                    });
                  }
              }
            break;
        default:
          if(pref_1_date == pref_3_date ||  
             pref_1_date == pref_2_date){
             //alert('duplicate date 1' + p1_start + " " + p1_end);
                var start_p3 = new Date(p3_start);
                var end_p3   = new Date(p3_end);
                var start_p2 = new Date(p2_start);
                var end_p2   = new Date(p2_end);
                //current
                var start    = new Date(p1_start);
                var end      = new Date(p1_end);
                //p3
                var _slot_free = 'y';
                if(withinRange(start.getTime(),start_p3.getTime(),end_p3.getTime() )){ _slot_free = 'n'; }
                if(withinRange(end.getTime(),start_p3.getTime(),end_p3.getTime() )){ _slot_free = 'n'; }
                if(withinRange(start_p3.getTime(),start.getTime(),end.getTime() )){ _slot_free = 'n'; }
                //p2
                if(withinRange(start.getTime(),start_p2.getTime(),end_p2.getTime() )){ _slot_free = 'n'; }
                if(withinRange(end.getTime(),start_p2.getTime(),end_p2.getTime() )){ _slot_free = 'n'; }
                if(withinRange(start_p2.getTime(),start.getTime(),end.getTime() )){ _slot_free = 'n'; }
                if(_slot_free == 'n'){
                  $.confirm({
                    title:  'This slot is already selected',
                    content: 'Clicking close button will clear the selected date input',
                    type: 'red',
                    typeAnimated: true,
                    icon: 'fas fa-exclamation-circle',
                    theme: 'modern',
                    buttons: {
                      close: {
                        btnClass: 'btn btn-primary',
                        action: function(){
                          $('.meeting_date_1').val('');
                          $('.from_time_1').val('');
                          $('.to_time_1').val('');
                          $('#showdiff_1').html('');
                        }
                      }
                    }
                  });
                }
          }
          break;
      }

      //alert(pref_1_date + pref_1_from + pref_1_to);
    }




/*---------------------------------------------------------------------------------------------------------- */

            //cross check slots
            $(document).on('change','.to_time_1', function(){
                 var date_1      = $('.meeting_date_1').val(); 
                 var from_time_1 = $('.from_time_1').val(); 
                 var to_time_1   = $(this).val(); 
                 //alert(date_1 + ' '+ from_time_1 + " - "+to_time_1);
            }); 

            // Create meeting
            $('form#mm365_create_meeting').submit(function(e){
              e.preventDefault(); 

         

              var prp_timezone = $('#proposer_timezone').val();
              var redirect_to = $('#after_schedule_redirect').val();
              var form        = $('form')[0];
              var formdata    = new FormData(form);
              formdata.append('action', 'create_meeting');
              formdata.append('nonce',meetingAjax.nonce);
              formdata.append('proposer_timezone', prp_timezone);
              if ( $(this).parsley().isValid() ) { 
                  $.ajax({ 
                      url : meetingAjax.ajax_url,
                      data: formdata,
                      type: 'POST',                   
                      contentType: false,
                      processData: false,
                      beforeSend: function() { 
                          $('html, body').animate({ scrollTop: 0 }, 'slow');
                          $('.company_preview').before('<div class="loader-wrapper"><div id="loader" class="loader-matchrequest"><i class="fa fa-spinner" aria-hidden="true"></i></div></div>');                                            
                              
                      },
                      success : function( data ){
                          if( data ) { 
                             $('html, body').animate({ scrollTop: 0 }, 0);
                              if(data == 'success'){
                                  $.confirm({
                                    title:  'Meeting invite sent successfully!',
                                    content: "Supplier will be notified through an email",
                                    type: 'green',
                                    typeAnimated: true,
                                    icon: 'far fa-check-circle',
                                    theme: 'modern',
                                    buttons: {
                                      close: {
                                        btnClass: 'btn btn-primary',
                                        action: function(){
                                          window.location = redirect_to;
                                        }
                                      }
                                    }
                                  });
                                }else{
                                  $.confirm({
                                    title:  'Unable to create meeting!',
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
                             
                          } else {
                              //$('.mm365_request_for_match').css('display','none');
                          }
                      }
                  }); 
             }

          });

/*-----------------------------------------------DATA TABLES - MEETING SCHEDULED -------------------------------------------------------- */

    if($('#meeting_scheduled_list').length > 0)
      {

        var table  = $('#meeting_scheduled_list').DataTable({
            responsive:true,
            "processing": true,
            "serverSide": true,
            "ajax": {
              "url":meetingAjax.ajax_url, 
              "data":function(data) {
                data.action = 'meetings_scheduled', 
                data.timezone = timezone_offset_minutes, 
                data.offset = (-today.getTimezoneOffset() / 60), 
                data.dst = (+dst),
                data.council = $('#councilFilter').val()
              }
            },
            "pagingType": "first_last_numbers",
            "order": [],
            "columnDefs": [ {
              "targets"  : 'no-sort',
              "orderable": false,
            }],
            "fnDrawCallback": function(oSettings) {
              if ($('#meeting_scheduled_list tr').length <= 1) {
                  $('.dataTables_paginate').hide();
                  $('.dataTables_info').hide();
                  
              }else{
                $('.dataTables_paginate').show();
                $('.dataTables_info').show();
              }
              if ($('#meeting_scheduled_list .dataTables_empty').length == 1) {
                $('.dataTables_paginate').hide();
                $('.dataTables_info').hide();
              }


              $( ".meeting-slots-list" ).each(function() {
                var lis = $(this).find('li');
                if (lis.length > 1) {
                    $(this).find('li').hide().filter(':lt(1)').show();
                    $(this).append('<li style="list-style:none"><div class="three-dots"><small>More</small></div></li>').find('li:last').click(function() {
                      $(this).find('.three-dots small').toggleClass("active");
                        if ($(this).find('.three-dots small').text() == "More")
                          $(this).find('.three-dots small').text("Less")
                        else $(this).find('.three-dots small').text("More");
      
                      $(this).siblings(':gt(0)').toggle();
                    });
                  }
              });

            },
            "language": {
              "lengthMenu": "Display _MENU_ meetings per page",
              "zeroRecords": "No meetings found",
              "info": "Showing page _PAGE_ of _PAGES_",
              "infoEmpty": "There are no scheduled meetings",
              "infoFiltered": "(filtered from _MAX_ total records)"
            },
            oLanguage: {sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>"}
          });

          // $('#meeting_scheduled_list input').unbind();
          // $('#meeting_scheduled_list input').bind('keyup', function(e) {
          //     if (e.keyCode == 13) {
          //         Table.fnFilter($(this).val());
          //     }
          // });

          $('#meeting_scheduled_list_filter label:last').append('<br/><small>Search using any of the column values</small>');
          $("#meeting_scheduled_list_filter.dataTables_filter").prepend($("#councilFilter_label"));
          // var filterTerm;
          // $('#councilFilter').on('change', function() {
          //   filterTerm = this.value.trim();
          //   //var term = '^((?!' + filterTerm + ').)*$';
          //   var term = filterTerm;
          //   table.search(term, true, false, true).draw();
          //   this.value = filterTerm;
          // });
        
          // table.on( 'draw', function () {
          //   $('#councilFilter').val( filterTerm );
          // });

          $('#councilFilter').on('change', function() {
            //$('#meeting_scheduled_list').data('sacouncilfilter',$(this).val());
            table.draw();
          });



  }



/*-----------------------------------------------DATA TABLES - MEETING INVITES -------------------------------------------------------- */

if($('#meeting_invites_list').length > 0)
{
  var table  = $('#meeting_invites_list').DataTable({
      responsive:true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url:meetingAjax.ajax_url, 
        "data":function(data) {
         data.action = 'meetings_invites',  
         data.timezone = timezone_offset_minutes, 
         data.offset = (-today.getTimezoneOffset() / 60), 
         data.dst = (+dst),
         data.council = $('#councilFilter').val()
        }
      },
      "pagingType": "first_last_numbers",
      "order": [],
      "columnDefs": [ {
        "targets"  : 'no-sort',
        "orderable": false,
      }],
      "fnDrawCallback": function(oSettings) {
        if ($('#meeting_invites_list tr').length <= 1) {
            $('.dataTables_paginate').hide();
            $('.dataTables_info').hide();
            
        }else{
          $('.dataTables_paginate').show();
          $('.dataTables_info').show();
        }
        if ($('#meeting_invites_list .dataTables_empty').length == 1) {
          $('.dataTables_paginate').hide();
          $('.dataTables_info').hide();
        }


        $( ".meeting-slots-list" ).each(function() {
          var lis = $(this).find('li');
          if (lis.length > 1) {
              $(this).find('li').hide().filter(':lt(1)').show();
              $(this).append('<li style="list-style:none"><div class="three-dots"><small>More</small></div></li>').find('li:last').click(function() {
                $(this).find('.three-dots small').toggleClass("active");
                  if ($(this).find('.three-dots small').text() == "More")
                    $(this).find('.three-dots small').text("Less")
                  else $(this).find('.three-dots small').text("More");

                $(this).siblings(':gt(0)').toggle();
              });
            }
        });

      },
      "language": {
        "lengthMenu": "Display _MENU_ meetings per page",
        "zeroRecords": "No meetings found",
        "info": "Showing page _PAGE_ of _PAGES_",
        "infoEmpty": "There are no scheduled meetings found",
        "infoFiltered": "(filtered from _MAX_ total records)"
      },
      oLanguage: {sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>"}
    });

    // $('#meeting_invites_list input').unbind();
    // $('#meeting_invites_list input').bind('keyup', function(e) {
    //     if (e.keyCode == 13) {
    //         Table.fnFilter($(this).val());
    //     }
    // });

    $('#meeting_invites_list_filter label:last').append('<br/><small>Search using any of the column values</small>');
    $("#meeting_invites_list_filter.dataTables_filter").prepend($("#councilFilter_label"));
    
    $('#councilFilter').on('change', function() {
      //$('#meeting_scheduled_list').data('sacouncilfilter',$(this).val());
      table.draw();
    });




}


/*-----------------------------------------------Slot selections on meeting page------------------------------------------------------- */
$('.cnd_1').hide();
$('.cnd_2').hide();

$('.invited_mode .time-slot').click(function(){
  $('.invited_mode').find('.time-slot').removeClass('active');
  var val = $(this).attr('data-value');
  $('.invited_mode').find('#radio-value').val(val);
  if(val != null){
    
    $(this).addClass('active');
    

    //condition enable based on value
    switch (val) {
      case 'decline_invite':
        $('textarea[name="decline_invite"]').prop('required',true);
        $('.meeting_date_1').prop('required',false);
        $('.from_time_1').prop('required',false);
        $('.to_time_1').prop('required',false);
        $('.cnd_2').show();
        $('.cnd_1').hide();
        break;
      case 'requesting_new_slot':
          $('textarea[name="decline_invite"]').prop('required',false);
          $('.meeting_date_1').prop('required',true);
          $('.from_time_1').prop('required',true);
          $('.to_time_1').prop('required',true);
          $('.cnd_2').hide();
          $('.cnd_1').show();
        break;  
      default:
        $('textarea[name="decline_invite"]').prop('required',false);
        $('.meeting_date_1').prop('required',false);
        $('.from_time_1').prop('required',false);
        $('.to_time_1').prop('required',false);
        $('.cnd_2').hide();
        $('.cnd_1').hide();
        break;
    }
  }else{
    //alert('Select a valid option');
    Notiflix.Notify.failure('This option is expired, choose a valid option');

  }

});



/*-----------------------------------------------Submit invitation response------------------------------------------------------ */
$('form#mm365_meeting_invite').submit(function(e){
  e.preventDefault(); 
  var prefference = $('#radio-value').val();
  var mid         = $('#meeting_id').val();
  var decline_msg = $('#meeting_decline_reason').val();

  //New slot
  var new_date      = $('.meeting_date_1').val();
  var new_date_from = $('.from_time_1').val();
  var new_date_to   = $('.to_time_1').val();
  var attendee_tz   = $('#attendee_timezone').val();

    if(prefference != ''){
       $.ajax({ 
          url : meetingAjax.ajax_url,
          data : {
              action:'meeting_invite_response',
              preffered:prefference,
              decline_message:decline_msg,
              reschedule_date:new_date,
              reschedule_time_from:new_date_from,
              reschedule_time_to:new_date_to,
              attendee_timezone: attendee_tz,
              meeting_id:mid
          },
          type: 'POST',                  
          success : function( data ){
           var titl = '';
           var cont = '';
           var typ  = 'green';
           
           switch (data) {
             case '4':
               titl = 'New meeting time proposed!';
               cont = 'Buyer will be notified with the proposed new time slot';
               break;
             case '5':
                typ = 'orange';
                titl = 'Meeting invite declined!';
                cont = 'Buyer will be notified with the reason for declining the meeting invite';
                break;
             default:
               titl = 'Meeting invite accepted Successfully!';
               cont = 'Buyer will be notified through an email';
               break;
           }
           
            $.confirm({
              title:  titl,
              content: cont,
              type: typ,
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
          }
       }); 
    }else{
      //alert('Since the proposed slots are expired, kindly propose a time or decline this invite');

      $.confirm({
        title:  "Choose a valid option",
        content:"Since the proposed slots are expired, kindly propose a time or decline this invite",
        type: 'red',
        typeAnimated: true,
        icon: "far fa-times-circle",
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
    }

});


/*----------------------------------------------- Schedule Meeting - Final Step ------------------------------- */
if($('#meeting_details').length > 0){
  // tinyMCE.init({
  //     //mode : "none",
  //     statusbar: false,
  //     menubar: false,
  //     toolbar: 'undo redo | bold italic | link unlink anchor',
  //     placeholder: "Meeting details",
  //     content_style:
  //     "body { background: #fff; color: #333; font-size: 13pt; }",
  //     setup: function (editor) {
  //         editor.on('change', function () {
  //             editor.save();
  //             $("#meeting_details").parsley().reset();
  //         });
  //     },
  //     mobile: {
  //         theme: 'mobile',
  //       },
  //     branding: false,
  //     plugins: "paste, link, autolink",
  //     //paste_as_text: true
      
  // });
  // tinyMCE.execCommand('mceAddEditor', false, 'meeting_details');

  tinymce.init({
    selector: '#meeting_details',
    menubar: 'file edit view',
    plugins: 'anchor autolink charmap  emoticons  link lists  searchreplace table visualblocks wordcount',
    placeholder: "",
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
    setup: function (editor) {
          editor.on('change', function () {
              editor.save();
              $("#meeting_details").parsley().reset();
          });
      },
  });





  function showicons_indropdown (state) {
    if (!state.id) { return state.text; }
    var icon = $(state.element).attr('data-img_src'); 
    var $state = $(
     '<span ><img width="55px" sytle="display: inline-block;"  src="'+ icon + '" /> &nbsp;' + state.text + '</span>'
    );
    return $state;
  }

  var options = {
  'templateResult': showicons_indropdown,
  }
  $('.mm365-single-image').select2(options);
  $('.select2-container--default .select2-selection--single').css({'height': '50px'});

  //Show icons after drop down

    $('.mm365-single-image').on('change', function () {
        var selected_meeting_ico = $('option:selected').attr('data-img_src');
        $('#meeting_icon').html('');
        $('#meeting_icon').html('<img width="150px" class="pto-20" src="'+ selected_meeting_ico + '"/>');
        
    });


}

$('form#mm365_meeting_scheduling').submit(function(e){
  e.preventDefault(); 
  var type    = $('#meeting_type').val();
  var details = $('#meeting_details').val();
  var mid     = $('#meeting_id').val();

    if(type != ''){
       $.ajax({ 
          url : meetingAjax.ajax_url,
          data : {
              action:'schedule_meeting',
              meeting_type:type,
              meeting_details:details,
              meeting_id:mid
          },
          type: 'POST',                  
          success : function( data ){
            $.confirm({
              title:  'Meeting scheduled!',
              content: 'Supplier will be notified through an email',
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
          }
       }); 
    }

});



/*----------------------------------------------- Schedule Meeting - Final Step ------------------------------- */

$('form#mm365_meeting_terminate').submit(function(e){
  e.preventDefault(); 
  var mode        = $('#meeting_mode').val();
  var mid         = $('#meeting_id').val();
  var message     = $('#terminate_meeting_message').val();
  var redirect    = $('#redirect_url').val();
    if(mode != ''){
       $.ajax({ 
          url : meetingAjax.ajax_url,
          data : {
              action:'terminate_meeting',
              meeting_mode:mode,
              terminate_meeting_message:message,
              meeting_id:mid
          },
          type: 'POST',                  
          success : function( data ){
            var cont = '';
            switch (data) {
              case 'declined':
                cont = 'Buyer will be notified with the reason for declining this meeting';
                break;
            
              default:
                cont = 'Supplier will be notified with the reason for cancelling this meeting';
                break;
            }
            $.confirm({
              title:  'Meeting '+ data + '!',
              content: cont,
              type: 'orange',
              typeAnimated: true,
              icon: 'far fa-check-circle',
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

/*-----------------------------------------------Edit and update meeting ------------------------------- */

$('form#mm365_edit_meeting').submit(function(e){
  e.preventDefault(); 
  var redirect_to = $('#after_schedule_redirect').val();
  var form        = $('form')[0];
  var formdata    = new FormData(form);
  var prp_timezone = $('#proposer_timezone').val();
  formdata.append('proposer_timezone', prp_timezone);
  formdata.append('action', 'edit_meeting');
  formdata.append('nonce',meetingAjax.nonce);
  if ( $(this).parsley().isValid() ) { 
      $.ajax({ 
          url : meetingAjax.ajax_url,
          data: formdata,
          type: 'POST',                   
          contentType: false,
          processData: false,
          beforeSend: function() { 
              $('html, body').animate({ scrollTop: 0 }, 'slow');
              $('.company_preview').before('<div class="loader-wrapper"><div id="loader" class="loader-matchrequest"><i class="fa fa-spinner" aria-hidden="true"></i></div></div>');                                            
                  
          },
          success : function( data ){
              if( data ) { 
                 $('html, body').animate({ scrollTop: 0 }, 0);
                 var typ  = 'green';
                 var ico  = 'far fa-check-circle';
                 var titl = 'Meeting invite updated successfully!';
                 var add_cont = '';
                 switch (data) {
                   case 'edit_lock':
                     typ  = 'red';
                     ico  = 'far fa-times-circle';
                     titl = 'Failed to update the meeting!';
                     add_cont = 'The attendee took further actions on the meeting while you were editing';
                     break;
                    case 'slot_error':
                      typ  = 'red';
                      ico  = 'far fa-times-circle';
                      titl = 'Failed to update the meeting!';
                      add_cont = 'One of the time slots selected is already blocked ';
                      break;
                   case 'failed':
                    typ  = 'red';
                    ico  = 'far fa-times-circle';
                    titl = 'Failed to update the meeting!';
                    add_cont = 'Failed due to unexpected reasons';
                    break;
                    default:
                      typ  = 'green';
                      ico  = 'far fa-check-circle';
                      titl = 'Meeting invite updated successfully!';
                      add_cont = '';
                      break;
                 }
                 
                 $.confirm({
                    title:  titl,
                    content: add_cont + "Click close button to redirect to 'Meetings scheduled' page",
                    type: typ,
                    typeAnimated: true,
                    icon: ico,
                    theme: 'modern',
                    buttons: {
                      close: {
                        btnClass: 'btn btn-primary',
                        action: function(){
                          window.location = redirect_to;
                        }
                      }
                    }
                   });

                 
              } else {
                  //$('.mm365_request_for_match').css('display','none');
              }
          }
      }); 
   }
});

/*-----------------------------------------Reschedule meeting--------------------------------------------- */
$('form#mm365_reschedule_meeting').submit(function(e){
  e.preventDefault(); 
  var redirect_to = $('#after_schedule_redirect').val();
  var rescheduled_by = $('#rescheduled_by').val();
  switch (rescheduled_by) {
    case 'attendee':
      var who = 'Buyer';
      break;
    default:
      var who = 'supplier';
      break;
  }
  var form        = $('form')[0];
  var formdata    = new FormData(form);
  formdata.append('action', 'reschedule_meeting');
  formdata.append('nonce',meetingAjax.nonce);
  if ( $(this).parsley().isValid() ) { 
       $.ajax({ 
          url : meetingAjax.ajax_url,
          data: formdata,
          type: 'POST',                   
          contentType: false,
          processData: false,         
          success : function( data ){
            if(data == 'success'){
                $.confirm({
                  title:  'Meeting rescheduled!',
                  content: who + ' will be notified through an email',
                  type: 'green',
                  typeAnimated: true,
                  icon: 'far fa-check-circle',
                  theme: 'modern',
                  buttons: {
                    close: {
                      btnClass: 'btn btn-primary',
                      action: function(){
                        window.location = redirect_to;
                      }
                    }
                  }
                });
            }else{
                $.confirm({
                  title:  'Unable to reschedule the meeting',
                  content: 'The timeslot you have selected is not available',
                  type: 'red',
                  typeAnimated: true,
                  icon: 'far fa-times-circle',
                  theme: 'modern',
                  buttons: {
                    close: {
                      btnClass: 'btn btn-primary',
                      action: function(){
                        window.location = redirect_to;
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
 * Edit meeting details 
 * v2.0 Onwards
 */

 $('form#mm365_edit_meeting_details').submit(function(e){
  e.preventDefault(); 
  var details = $('#meeting_details').val();
  var mid     = $('#meeting_id').val();
  var redirect_to = $('#redirect_to').val();

    if(mid != ''){
       $.ajax({ 
          url : meetingAjax.ajax_url,
          data : {
              action:'update_meeting_details',
              meeting_details:details,
              meeting_id:mid,
              nonce: meetingAjax.nonce,
          },
          type: 'POST',                  
          success : function( data){
            if(data == 1){

              $.confirm({
                title:  'Meeting details updated!',
                content: 'Supplier will be notified through an email',
                type: 'green',
                typeAnimated: true,
                icon: 'far fa-check-circle',
                theme: 'modern',
                buttons: {
                  close: {
                    btnClass: 'btn btn-primary',
                    action: function(){
                      window.location = redirect_to;
                    }
                  }
                }
              });

            }


          }
       }); 
    }

});




//ENDS HERE
  });
  
})();