jQuery(document).ready(function ($) {

    var clear_timer;
  
    var successful_import_list = [];
    var imported_companies = [];
    var failed_companies = [];
    var company_country_id = '';





    $('#sample_form').on('submit', function (event) {
        $('#message').html('');
        event.preventDefault();
        var form_data = new FormData(this);
        //Confirm

        $.confirm({
            title: 'Do you want to continue?',
            content: 'Please continue if you have confirmed all the parameters',
            icon: "fas fa-question-circle",
            theme: 'modern',
            type: 'red',
            buttons: {
                confirm:{
                    action: function () {

                    /*------------------------------------- */

                    if ($('#importer-file-upload-input').get(0).files.length === 0) {
                        $('#message').html('<div class="alert alert-danger">No file is selected</div>');
                        $('#import').attr('disabled', false);
                        $('#import').val('Import');
                        $("#sample_form")[0].reset();
                        return false;
                    }
            
                    
                    form_data.append('action', 'upload_csv_file');
                    //$('.company_preview').append("<div class='processing-message'><img style='width: 30px; margin - bottom: 5px;' class='small - loader' src='" + ajax_object.plugin_url + "/loader.gif' >Importing csv file, please wait</div>");
                    Notiflix.Loading.hourglass('Uploading CSV file.',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });
                    
                    $('#output-container').show();
                    $(".content").html('');
                    $("#import").attr('disabled', true);
            
                    $.ajax({
                            url: ajax_object.ajax_url,
                            method: "POST",
                            data: form_data,
                            dataType: "json",
                            contentType: false,
                            cache: false,
                            processData: false,
                            success: import_start,
                    });


                    /*---------------------------------------- */

                    }
                },
                cancel: {

                }
            }
          });



        ///


        

        function import_start(uploaded_data) {

            Notiflix.Loading.remove(10);

            if (uploaded_data.error) {
                $('#message').html('<div class="alert alert-danger">' + uploaded_data.error + '</div>');

                Notiflix.Notify.failure(uploaded_data.error);

                $('#import').attr('disabled', false);
                $('#import').val('Import');
                $('.processing-message').remove();
                $("#sample_form")[0].reset();
                return false;
            }
            if (uploaded_data.success) {
                items = uploaded_data.csv_content;
                //Get these values from upload form (Read through ajax)
                council_id = uploaded_data.council_id;
                get_company_country_id = uploaded_data.company_country_id;
                company_country_id = get_company_country_id;
                service_type = uploaded_data.service_type;

                $('#total_data').html(items.length)
                var error_data = [];
                total = items.length
                counter = 0;
                loop_records();
                function loop_records() {
                    setTimeout(function () {
                        process_records(counter, items[counter], council_id, get_company_country_id, service_type);
                        counter++;
                        if (counter < total) {
                            loop_records();
                        }
                    }, 1000)
                }
                function process_records(counter, $items, $council_id, $company_country, $service_type) {

                    $import_data = { 'action': 'import_csv_data', 'item': $items , 'council':$council_id, 'company_country': $company_country, 'service_type':$service_type};
                    if (counter == (total - 1)) {
                        $import_data = { 'action': 'import_csv_data', 'item': $items, 'last_item': 'true', 'council':$council_id , 'company_country':$company_country, 'service_type':$service_type};
                    }

                    counter_print = counter + 1;

                    //$('.processing-message').html("<div><img style='width: 30px;margin - bottom: 5px;' class='small - loader' src='" + ajax_object.plugin_url + "/loader.gif' >Processing row " + counter_print + " of " + total + " rows</div>");

                    //Notiflix.Loading.hourglass("Importing record " + counter_print + " of " + total ,{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });
                    
                   
                    $.ajax({
                        url: ajax_object.ajax_url,
                        method: "POST",
                        data: $import_data,
                        success: user_import_success
                    })
                }

                function user_import_success(data) {

                    //Notiflix.Loading.remove(10);

                    var result_div = $('<div>').html(data);
                    var response_status = result_div.find('.status_code').html();
                    csv_row_id = result_div.find('.csv_row_id').html();

                    

                    print_progress_bar();

                    $('#status-messages-import .content').append('<p>Processing - #' + csv_row_id + ' completed</p >');

                    if (response_status == 2) {
                        $('#error-messages-import .content').append('<p>#' + csv_row_id + ' - Error: ' + result_div.find('.message').html() + '</p>');
                        failed_companies.push(csv_row_id);
                    } else {
                        csv_email = result_div.find('.csv_email').html();
                        imported_company_id = result_div.find('.csv_company_id').html();

                        successful_import_list.push(csv_email);
                        imported_companies.push(imported_company_id);
                        $('#success-messages-import .content').append('<p>Imported #' + csv_row_id + ' </p>');                       
                        
                    }

                    last_item = result_div.find('.last_item').html();
                    if (last_item) {
                        $('.processing-message').remove();
                        $("#import").removeAttr('disabled');
                        $("#sample_form")[0].reset();                        
                    }

                }

                function print_progress_bar() {
                    $('#process').css('display', 'block');
                    var width = (counter / total) * 100;
                    $('.import-progress-bar').css('width', width + '%');
                    if (width >= 100) {
                        clearInterval(clear_timer);
                        $('#process').css('display', 'none');
                        $('.processing-message').css('display', 'none');
                        
                        //$('#importer-file-upload-input').val('');
                        $('#message').html(
                            '<div class="alert alert-success">Data Import Completed for ' + total + ' records</div>');

                        Notiflix.Notify.info('Data Import Completed for ' + total + ' records', {timeout: 3500});

                        $('#import').attr('disabled', false);
                        $('#import').val('Import Data');
                     
                        removeUpload();
                        //Collect emails and post it to sucessful imports
                        setTimeout(() => {
                            //console.log(successful_import_list);
                             //AJAX
                                $log_data = { 
                                     'action': 'update_importlog', 
                                     'failed_companies':JSON.stringify(failed_companies),
                                     'success_ids':JSON.stringify(imported_companies),
                                     'emails': JSON.stringify(successful_import_list),
                                     'council_id':council_id,
                                     'country':company_country_id
                                    };

                                    if(counter == total){
                                        $.ajax({
                                            url: ajax_object.ajax_url,
                                            method: "POST",
                                            data: $log_data
                                        });
                                    }       
                               
                        }, 100);

                       

                    }
                }

            }
        }

        return false;
    });

    // Download reports
    $(".status-download, .error-download, .success-download").click(function (e) {
        e.preventDefault();
        saveFile($(this).next('.content').html());
    })

    function saveFile(content) {

        // Convert the text to BLOB.
        const textToBLOB = new Blob([content], { type: 'text/plain' });
        const sFileName = 'reports.txt';	   // The file to save the data.

        let newLink = document.createElement("a");
        newLink.download = sFileName;

        if (window.webkitURL != null) {
            newLink.href = window.webkitURL.createObjectURL(textToBLOB);
        }
        else {
            newLink.href = window.URL.createObjectURL(textToBLOB);
            newLink.style.display = "none";
            document.body.appendChild(newLink);
        }

        newLink.click();
    }


/*****************************************************************
 * 
 * File upload scripts
 * 
 *****************************************************************/

 $(document).on('change','#importer-file-upload-input',function(){
    readURL(this);
});

$('#importer-file-upload-remove').on('click',function(){
    removeUpload();
});

 function readURL(input) {
    if (input.files && input.files[0]) {
  
      var reader = new FileReader();
  
      reader.onload = function(e) {
        $('.image-upload-wrap').hide();
  
       // $('.importer-file-upload-image').attr('src', e.target.result);
        $('.importer-file-upload-content').show();
  
        $('.image-title').html(input.files[0].name);
      };

      reader.readAsDataURL(input.files[0]);
  
    } else {
      removeUpload();
    }
  }
  
  function removeUpload() {
    $('.importer-file-upload-input').replaceWith($('.importer-file-upload-input').clone());
    $('.importer-file-upload-content').hide();
    $('.image-upload-wrap').show();
  }
  $('.image-upload-wrap').bind('dragover', function () {
          $('.image-upload-wrap').addClass('image-dropping');
      });
      $('.image-upload-wrap').bind('dragleave', function () {
          $('.image-upload-wrap').removeClass('image-dropping');
  });

/********************************************************************** */


$("#importing_council_id").change(function(){
    if($(this).val() == "")
         $('.council-parError').css({"display": "block"});
    else
         $('.council-parError').css({"display": "none"});
});

$("#importing_to_country").change(function(){
    if($(this).val() == "")
         $('.country-parError').css({"display": "block"});
    else
         $('.country-parError').css({"display": "none"});
});




});