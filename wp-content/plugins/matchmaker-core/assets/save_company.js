(function() {
    "use strict";
  jQuery(document).ready(function($) { 

            // tinyMCE.init({
            //     mode : "none",
            //     statusbar: true,
            //     placeholder: "Please add proper description about your company, make sure that you have added all the necessary keywords about your products and services",
            //     content_style:
            //     "body { background: #fff; color: #356ab3; font-size: 13pt; font-family:Raleway }",
            //     setup: function (editor) {
            //         editor.on('change', function () {
            //             editor.save();
            //             $("#company_description").parsley().reset();
            //         });
            //     },
            //     mobile: {
            //         theme: 'mobile',
            //       },
            //     branding: false,
            //     //plugins: "paste lists",              
            //     //toolbar: 'numlist bullist'
            //     menubar: false,
            //     plugins: [
            //       'advlist autolink lists link image charmap print preview anchor',
            //       'searchreplace visualblocks  fullscreen',
            //       'insertdatetime media table paste wordcount paste'
            //     ],
            //     toolbar: 'undo redo | formatselect | ' +
            //     'bold italic backcolor | alignleft aligncenter ' +
            //     'alignright alignjustify | bullist numlist outdent indent | ' +
            //     'removeformat',
            //     paste_as_text: true,
                
            // });
            // tinyMCE.execCommand('mceAddEditor', false, 'company_description');

            tinymce.init({
                selector: '#company_description',
                menubar: 'file edit view',
                plugins: 'anchor autolink charmap  emoticons  link lists  searchreplace table visualblocks wordcount',
                placeholder: "Please add proper description about your company, make sure that you have added all the necessary keywords about your products and services",
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
                setup: function (editor) {
                    editor.on('change', function () {
                        editor.save();
                        $("#company_description").parsley().reset();
                    });
                },
            });


            //Validation Condition if Manufacturing locations are already added
            var manuf_location = ($(".remove-manufacturing-location").length);
            if(manuf_location > 0 ){
               //Disable submission, add validation
               $('#country_itr').prop('required',false);
               $('#state_itr').prop('required',false);
               $('#city_itr').prop('required',false);
               
            }
/*-------------------------------------------------------------------------------- */
            //Hide Validation Messages for Select2
/*-------------------------------------------------------------------------------- */
            $("#services").change(function(){
                if($(this).val() == "")
                     $('.servError').css({"display": "block"});
                else
                     $('.servError').css({"display": "none"});
            });
            $("input[name=service_type]").change(function(){
                if($(this).val() == "")
                     $('.stypError').css({"display": "block"});
                else
                     $('.stypError').css({"display": "none"});
            });
            $("#industry").change(function(){
                if($(this).val() == "")
                     $('.industryError').css({"display": "block"});
                else
                     $('.industryError').css({"display": "none"});
            });
            $(".country").change(function(){
                if($(this).val() == "")
                     $('.countryError').css({"display": "block"});
                else
                     $('.countryError').css({"display": "none"});
            });
           
            
            $(".state").change(function(){
                if($(this).val() == "")
                     $('.stateError').css({"display": "block"});
                else
                     $('.stateError').css({"display": "none"});
            });
            $(".city").change(function(){
                if($(this).val() == "")
                     $('.cityError').css({"display": "block"});
                else
                     $('.cityError').css({"display": "none"});
            });
            $("#minority_category").change(function(){
                if($(this).val() == "")
                     $('.minority_categoryError').css({"display": "block"});
                else
                     $('.minority_categoryError').css({"display": "none"});
            });
            

            $(".serviceable-countries").change(function(){
                if($(this).val() == "")
                     $('.srv-countryError').css({"display": "block"});
                else
                     $('.srv-countryError').css({"display": "none"});
            });

            $(".serviceable-states").change(function(){
                if($(this).val() == "")
                     $('.srv-stateError').css({"display": "block"});
                else
                     $('.srv-stateError').css({"display": "none"});
            });


/**
 * 
 * Conditionally show form blocks based on company service type
 * 
 */      
        $(document).ready(function(){
            if($('input[name="service_type"]:checked').val() == 'seller'){
                setTimeout(() => {
                    $('#minority_category_block').show();
                    $('#minority_category_block_uploads').show();
                    $('#serviceable-countries').attr('required','true');
                    $('#serviceable-states').attr('required','true');
                  
                }, 100);
                
              }else{
                  $('#minority_category_block').hide();
                  $('#minority_category_block_uploads').hide(); //show
                  $('#minority_category').removeAttr('required','true');
                  $('#serviceable-countries').removeAttr('required');
                  $('#serviceable-states').removeAttr('required');

                //Introjs conditionally hide
                var getChild = $("#minority_category_block, #minority_category_block_uploads").find('*');
                getChild.each(function(i,v){
                    var existing = $(v).attr("data-intro");
                    $(v).attr("data-introhide",existing);
                    $(v).removeAttr("data-intro");
                });
                
             
            }
  
            $('input[name=service_type]').on("change",function(e) {

              if($(this).val() == 'seller'){
                $('#minority_category_block').show();
                $('#minority_category_block_uploads').show();
                $('#minority_category').attr('required','true');
                $('#company_desc_title').html('Description of services or products offered<span>*</span>');
                $('#serviceable-countries').attr('required','true');
                $('#serviceable-states').attr('required','true');
                
                if($('#active_update_company').len > 0){
                  $('#active_update_company').parsley().reset();
                }
                if($('#update_company').len > 0){
                    $('#update_company').parsley().reset();
                }

                //Introjs conditionally hide reverse
                var getChild = $("#minority_category_block, #minority_category_block_uploads").find('*');
                getChild.each(function(i,v){
                    var existing = $(v).attr("data-introhide");
                    $(v).attr("data-intro",existing);
                    $(v).removeAttr("data-introhide");
                });

              }else{
                $('#minority_category_block').hide();
                $('#minority_category_block_uploads').hide();
                $('#minority_category').removeAttr('required');
                $('#serviceable-countries').removeAttr('required');
                $('#serviceable-states').removeAttr('required');
                $('#company_desc_title').html('Company Information<span>*</span>');

                //Introjs conditionally hide
                var getChild = $("#minority_category_block, #minority_category_block_uploads").find('*');
                getChild.each(function(i,v){
                    var existing = $(v).attr("data-intro");
                    $(v).attr("data-introhide",existing);
                    $(v).removeAttr("data-intro");
                });
                
                
              }

              
                //Reinit select2
                $(".mm365-single").select2();
                $('.mm365-multicheck').select2({
                    theme: "classic",
                    placeholder: "Select all that applies",
                    allowClear: true
                  });


            });    
            

        }); 




            $('#other_industry_input').hide();
            //$('#other_certification_input').hide();
            $('#other_manufacturing_process_input').hide();
            $('#other_services_input').hide();
            $('#other_international_services_input').hide();

            if ($("#certifications option[value=other]:selected").length > 0){  $('#other_certification_input').show(); }else{$('#other_certification_input').hide();}
            
            $('#industry').change(function(){
              if ($("#industry option[value=other]:selected").length > 0){ $('#other_industry_input').show();  $("#other_industry_input").next().show("ul"); $('#other_industry_input').attr('required','true'); }
              else{  $('#other_industry_input').removeAttr('required'); $("#other_industry_input").next().hide("ul"); $('#other_industry_input').hide(); $('#other_industry_input').val('');}
            });
            $('#other_industry').click(function(){
                if ($("#industry option[value=other]:selected").length > 0){ $('#other_industry_input').show(); $("#other_industry_input").next().show("ul"); $('#other_industry_input').attr('required','true');}
                else{  $('#other_industry_input').removeAttr('required'); $('#other_industry_input').hide(); $("#other_industry_input").next().hide("ul"); $('#other_industry_input').val('');}
            });
            /*------------------ */
            $('#certifications').change(function(){
              if ($("#certifications option[value=other]:selected").length > 0){ $('#other_certification_input').show(); $("#other_certification_input").next().show("ul"); $('#other_certification_input').attr('required','true');}
              else{ $('#other_certification_input').hide(); $("#other_certification_input").next().hide("ul"); $('#other_certification_input').removeAttr('required');   $('#other_certification_input').val(''); }
            });  
            $('#other_certification').click(function(){
                if ($("#certifications option[value=other]:selected").length > 0){ $('#other_certification_input').show(); $("#other_certification_input").next().show("ul"); $('#other_certification_input').attr('required','true'); }
                else{ $('#other_certification_input').hide(); $("#other_certification_input").next().hide("ul"); $('#other_certification_input').removeAttr('required'); $('#other_certification_input').val('');}
            });

             /*---------------- */
            $('#services').change(function(){
              if ($("#services option[value=other]:selected").length > 0){ $('#other_services_input').show();  $("#other_services_input").next().show("ul"); $('#other_services_input').attr('required','true');}
              else{ $('#other_services_input').removeAttr('required'); $("#other_services_input").next().hide("ul"); $('#other_services_input').hide(); $('#other_services_input').val(''); }
            }); 
            $('#other_services').click(function(){
                if ($("#services option[value=other]:selected").length > 0){ $('#other_services_input').show();  $("#other_services_input").next().show("ul"); $('#other_services_input').attr('required','true');}
                else{ $('#other_services_input').removeAttr('required'); $("#other_services_input").next().hide("ul"); $('#other_services_input').hide(); $('#other_services_input').val('');}
            });


/*-------------------------------------------------------------------------------- */          
             //Manufacturer block hide or show
/*-------------------------------------------------------------------------------- */             
            $("input[name='is_manufacturer']").click(function() {
                var manufacturer = $(this).val();
                if(manufacturer == 'Yes'){ 
                    $('.manufaturer-only-info').show();
                    $('#manufacturing_process').prop('required',true);
                    if(manuf_location == 0 ){
                        $('#country_itr').prop('required',true);
                        $('#state_itr').prop('required',true);
                        $('#city_itr').prop('required',true);
                    }
                }else{
                    $('.manufaturer-only-info').hide();
                    $('#manufacturing_process').prop('required',false);
                    $('#country_itr').prop('required',false);
                    $('#state_itr').prop('required',false);
                    $('#city_itr').prop('required',false);
                }
                
            });

            $("#edit_socialmedias").on("change",function() {
                var sm = $(this).val();
                if(sm != ''){ 
                    //$('.manufaturer-only-info').show();
                    $('#edit_socialmedia_id').prop('required',true);
                }else{
                    //$('.manufaturer-only-info').hide();
                    $('#edit_socialmedia_id').prop('required',false);
                }
                
            });


            $('#company_description').prop('required',true);
/*-------------------------------------------------------------------------------- */
            // Company Registartion Ajax Form Submission and preview output
/*-------------------------------------------------------------------------------- */
            $('form#reg_company').submit(function(e){
                e.preventDefault(); 
                var form = $(this)[0];
                var formdata = new FormData(form);

                formdata.append('action', 'mm365_company_create');
                var files = $('#my-dropzone').get(0).dropzone.getAcceptedFiles();
                    for (let x = 0; x < files.length; x++){ 
                        formdata.append('files[]', files[x]);
                }
                
                formdata.append('nonce',companyAjax.nonce);
                var company_desc = $('#company_description_ifr').contents().find('body')[0].innerHTML;
                if(company_desc != ''){
                    $("#company_description").parsley().reset();
                }
                var count_naics = $("input[name='naics_codes[]']").length;
                if(count_naics == 0 ){
                    Notiflix.Report.failure(
                        'NAICS code is required',
                        'Please select atleast one NAICS code to continue. You can search select the code from "Find NAICS codes" field',
                        'OK',
                        );
                        
                }

                if (count_naics > 0 && $(this).parsley().isValid() ) { 
                    
                    $.ajax({ 
                        url : companyAjax.ajax_url,
                        data: formdata,
                        type: 'POST',                   
                        contentType: false,
                        processData: false,
                        beforeSend: function() { 
                            $('html, body').animate({ scrollTop: 0 }, 'slow');
     
                            $("#reg_company input").prop("disabled", true);
                            $("#reg_company select").prop("disabled", true); 
                            $("#reg_company textarea").prop("disabled", true); 
                            $("#reg_company .company_preview").css("opacity", "0.4"); 

                            $('.btn').hide();
                            $('#comp_submit').hide();                            
                            $('html, body').animate({ scrollTop: 0 }, 'slow');
                            Notiflix.Loading.hourglass('Initializing company registration preview..',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });
   
                        },
                        success : function( data ){
                            if( data ) { 
                                $('#page_main_heading').html("Preview");
                                $('#reg_company').html(data);
                                $('html, body').animate({ scrollTop: 0 }, 0);
                                $("#reg_company").css("opacity", "1"); 
                                $('#ajax-warnings').html('');

                                $("#reg_company .btn-primary:last").on( "click", function(e) {                                  
                                    $('.btn').hide();
                                    $(this).hide();
                                    $('.company').css('opacity','0.5');
                                    $('html, body').animate({ scrollTop: 0 }, 'slow');                                    
                                });

                                Notiflix.Loading.remove(100);  
                                Notiflix.Notify.success('Company registered successfully');    


                            } else {
                                $('.recent_news_load_more_div').css('display','none');
                            }
                        }
                    }); 
               }
               else{
                  setTimeout(function(){ 
                    if ($('.descError').find('.parsley-errors-list.filled').length) {
                        $([document.documentElement, document.body]).animate({
                            scrollTop: $('html').offset().top - 100
                        }, 500);
                    }
                  }, 50);



               }

            });
/*-------------------------------------------------------------------------------- */
          // Company Registartion Ajax Form Submission and preview output
/*-------------------------------------------------------------------------------- */
            $('form#update_company').submit(function(e){
                e.preventDefault(); 
                 
                //If no manufacturing location not submit
                var manuf_location = ($(".remove-manufacturing-location").length);

                var count_naics = $("input[name='naics_codes[]']").length;
                if(count_naics == 0 ){
                    Notiflix.Report.failure(
                        'NAICS code is required',
                        'Please select atleast one NAICS code to continue. You can search select the code from "Find NAICS codes" field',
                        'OK',
                        );
                        
                }


                if(manuf_location == 0 && $('#country_itr').val() == ''){
                   //Disable submission, add validation
                   $('#country_itr').prop('required',true);
                   
                }else{

                        var form_up = $('form')[0];
                        var formdata_up = new FormData(form_up);
                        formdata_up.append('action', 'mm365_company_update');
                        var files = $('#my-dropzone').get(0).dropzone.getAcceptedFiles();
                        for (let x = 0; x < files.length; x++){ 
                            formdata_up.append('files[]', files[x]);
                        }
                        var existing_attachments = [];
                            $("[name='existing_files[]']:checked").each(function (i) {
                                existing_attachments[i] = $(this).val();
                        });
                        formdata_up.append('existing_files', existing_attachments);

                        formdata_up.append('nonce',companyAjax.nonce);
                        var company_desc = $('#company_description_ifr').contents().find('body')[0].innerHTML;
                        if(company_desc != ''){
                            $("#company_description").parsley().reset();
                        }
                        if (count_naics > 0 && $(this).parsley().isValid() ) { 
                            $.ajax({ 
                                url : companyAjax.ajax_url,
                                data: formdata_up,
                                type: 'POST',                   
                                contentType: false,
                                processData: false,
                                beforeSend: function() { 
                                    //$('html, body').animate({ scrollTop: 0 }, 'slow');
                                    $("#update_company input").prop("disabled", true);  
                                    $("#update_company select").prop("disabled", true); 
                                    $("#update_company textarea").prop("disabled", true); 
                                    $("#update_company .company_preview").css("opacity", "0.4"); 

                                    $('.btn').hide();
                                    $('#comp_submit').hide();                            
                                    $('html, body').animate({ scrollTop: 0 }, 'slow');
                                    Notiflix.Loading.hourglass('Updating company information...',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });
 
                                },  
                                success : function( data ){
                                    if( data ) { 
                                        $('#page_main_heading').html("Preview");
                                        $('#update_company').html(data);
                                        $('html, body').animate({ scrollTop: 0 }, 0);
                                        $("#update_company").css("opacity", "1"); 
                                    
                                        $("#update_company .btn-primary:last").on( "click", function(e) {                                  
                                            $('.btn').hide();
                                            $(this).hide();
                                            $('.company').css('opacity','0.5');
                                            $('html, body').animate({ scrollTop: 0 }, 'slow');                                            
                                        });

                                        Notiflix.Loading.remove(100);
                                        Notiflix.Notify.success('Company details updated');

                                                                                               
                                    } else {
                                        $('.recent_news_load_more_div').css('display','none');
                                    }
                                }
                            }); 
                    }else{
                        setTimeout(function(){ 
                            if ($('.descError').find('.parsley-errors-list.filled').length) {
                                $([document.documentElement, document.body]).animate({
                                    scrollTop: $('html').offset().top - 100
                                }, 500);
                            }
                        }, 50);

                    }
                }
            });


/*-------------------------------------------------------------------------------- */
            // Updating Published Company
/*-------------------------------------------------------------------------------- */

            $('form#active_update_company').submit(function(e){
                e.preventDefault(); 

                var count_naics = $("input[name='naics_codes[]']").length;
                if(count_naics == 0 ){
                    Notiflix.Report.failure(
                        'NAICS code is required',
                        'Please select atleast one NAICS code to continue. You can search select the code from "Find NAICS codes" field',
                        'OK',
                        );
                        
                }

                //If no manufacturing location not submit
                var manuf_location = ($(".remove-manufacturing-location").length);
                if(manuf_location == 0 && $('#country_itr').val() == ''){
                   //Disable submission, add validation
                   $('#country_itr').prop('required',true);
                   
                }else{
                            var form_up = $('form')[0];
                            var formdata_up = new FormData(form_up);
                            formdata_up.append('action', 'mm365_company_update');
                            var files = $('#my-dropzone').get(0).dropzone.getAcceptedFiles();
                            for (let x = 0; x < files.length; x++){ 
                                formdata_up.append('files[]', files[x]);
                            }
                            var existing_attachments = [];
                            $("[name='existing_files[]']:checked").each(function (i) {
                                existing_attachments[i] = $(this).val();
                            });
                            formdata_up.append('existing_files', existing_attachments);

                            formdata_up.append('nonce',companyAjax.nonce);
                            var company_desc = $('#company_description_ifr').contents().find('body')[0].innerHTML;
                            if(company_desc != ''){
                                $("#company_description").parsley().reset();
                            }
                            if (count_naics > 0 &&  $(this).parsley().isValid() ) { 
                                $.ajax({ 
                                    url : companyAjax.ajax_url,
                                    data: formdata_up,
                                    type: 'POST',                   
                                    contentType: false,
                                    processData: false,
                                    beforeSend: function() { 
                                        $("#active_update_company input").prop("disabled", true);    
                                        $("#active_update_company select").prop("disabled", true); 
                                        $("#active_update_company textarea").prop("disabled", true); 
                                        $("#active_update_company .company_preview").css("opacity", "0.4"); 

                                        $('.btn').hide();
                                        $('#comp_submit').hide();                            
                                        $('html, body').animate({ scrollTop: 0 }, 'slow');
                                        Notiflix.Loading.hourglass('Updating company information...',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });
 
                                    },  
                                    success : function( data ){
                                        if( data ) { 
                                            $('html, body').animate({ scrollTop: 0 }, 'slow');
                                            $('#active_update_company').html(data);
                                            $("#active_update_company").css("opacity", "1"); 
                                            $('html, body').animate({ scrollTop: 0 }, 0);
                                            Notiflix.Loading.remove(100);
                                            Notiflix.Notify.success('Company details updated');
                                        } else {
                                            $('.recent_news_load_more_div').css('display','none');
                                        }
                                    }
                                }); 
                        }else { 

                            setTimeout(function(){ 
                                if ($('.comp_desc_error').find('.parsley-errors-list.filled').length) {
                                    $([document.documentElement, document.body]).animate({
                                        scrollTop: $('html').offset().top - 100
                                    }, 500);
                                }
                            }, 50);

                        }
                    }
            });



 //ends           

  });
  
})();