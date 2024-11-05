(function () {
    "use strict";
    jQuery(document).ready(function ($) {
        /* Start Editing */


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


        $(document).ready( function(){
        /**
         * Moved from chrome
         */
        var mrid = getUrlParameter('mr_id');
        var mr_state = getUrlParameter('mr_state');
        var token = getUrlParameter('_wpnonce');
        var base_url = window.location.origin; 

        if(mrid && token && mr_state=='draft'){

            $.confirm({
              title: 'Continue with the partially added Match request?',
              content: 'We have found an un submitted match requests in your account, if you want to continue editing the same click \'Continue\' button.<br/><br/> If you want to delete the existing data and start new click \'Disard\' button',
              theme: 'modern',
              icon: 'fas fa-exclamation-circle',
              type: 'red',
              buttons: {
                  continue: {
                    btnClass: 'btn btn-primary',
                    action: function () {}
                  },
                  disacard: {
                    btnClass: 'btn btn-primary red',
                    action: function(){
                      //window.location.href = base_url + "/delete-matchrequest-draft?mr_id="+mrid+"&tokendel="+token;

                      //Ajax acction here

                      $.ajax({ 
                        url : matchmakingAjax.ajaxurl,
                        data : {
                            action:'match_delete',
                            mr_id:mrid,
                            nonce: matchmakingAjax.nonce,
                        },
                        type: 'POST', 
                        beforeSend: function () {
                            Notiflix.Loading.hourglass('Deleting match request...', { svgColor: '#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor: '#356ab3' });
                        },               
                        success : function( data ){
                           
                            Notiflix.Notify.success('Match request discarded');
                            Notiflix.Loading.remove(100);
                            window.location.href = matchmakingAjax.site_url + "/new-request-for-match";
                           
                        }
                     }); 



                    }
                  }
              }
            });
        
         }

        });


        /*-------------------------------------------------------------------------------- */
        //MISC
        /*-------------------------------------------------------------------------------- */

        //Hide Validation Messages for Select2
        $("#services").change(function () {
            if ($(this).val() == "")
                $('.servError').css({ "display": "block" });
            else
                $('.servError').css({ "display": "none" });
        });
        $("#service_type").change(function () {
            if ($(this).val() == "")
                $('.stypError').css({ "display": "block" });
            else
                $('.stypError').css({ "display": "none" });
        });
        $("#industry").change(function () {
            if ($(this).val() == "")
                $('.industryError').css({ "display": "block" });
            else
                $('.industryError').css({ "display": "none" });
        });
        /*---------------- */
        $('#other_industry_input').hide();
        $('#other_services_input').hide();
        /*---------------- */
        $('#industry').change(function () {
            if ($("#industry option[value=other]:selected").length > 0) { $('#other_industry_input').show(); $("#other_industry_input").next().show("ul"); $('#other_industry_input').attr('required', 'true'); }
            else { $('#other_industry_input').removeAttr('required'); $("#other_industry_input").next().hide("ul"); $('#other_industry_input').hide(); $('#other_industry_input').val(''); }
        });
        $('#other_industry').click(function () {
            if ($("#industry option[value=other]:selected").length > 0) { $('#other_industry_input').show(); $("#other_industry_input").next().show("ul"); $('#other_industry_input').attr('required', 'true'); }
            else { $('#other_industry_input').removeAttr('required'); $('#other_industry_input').hide(); $("#other_industry_input").next().hide("ul"); $('#other_industry_input').val(''); }
        });
        /*---------------- */
        $('#services').change(function () {
            if ($("#services option[value=other]:selected").length > 0) { $('#other_services_input').show(); $("#other_services_input").next().show("ul"); $('#other_services_input').attr('required', 'true'); }
            else { $('#other_services_input').removeAttr('required'); $("#other_services_input").next().hide("ul"); $('#other_services_input').hide(); $('#other_services_input').val(''); }
        });
        $('#other_services').click(function () {
            if ($("#services option[value=other]:selected").length > 0) { $('#other_services_input').show(); $("#other_services_input").next().show("ul"); $('#other_services_input').attr('required', 'true'); }
            else { $('#other_services_input').removeAttr('required'); $("#other_services_input").next().hide("ul"); $('#other_services_input').hide(); $('#other_services_input').val(''); }
        });

        $('#certifications').change(function () {
            if ($("#certifications option[value=other]:selected").length > 0) { $('#other_certification_input').show(); $("#other_certification_input").next().show("ul"); $('#other_certification_input').attr('required', 'true'); }
            else { $('#other_certification_input').attr('value', '');; $('#other_certification_input').hide(); $("#other_certification_input").next().hide("ul"); $('#other_certification_input').removeAttr('required'); $('#other_certification_input').val(''); }
        });
        $('#other_certification').click(function () {
            if ($("#certifications option[value=other]:selected").length > 0) { $('#other_certification_input').show(); $("#other_certification_input").next().show("ul"); $('#other_certification_input').attr('required', 'true'); }
            else { $('#other_certification_input').hide(); $("#other_certification_input").next().hide("ul"); $('#other_certification_input').removeAttr('required'); $('#other_certification_input').val(''); }
        });


        // $(".expand").on("click", function () {
        //     $(this).next().slideToggle(0);
        //     var expand = $(this).find(".right-arrow");
        //     if (expand.text() == "-") {
        //         expand.text("+");
        //         $("#advanced_search").val('false');
        //         var label = $("#mr_submit_btn").data('btnmode');
        //         $("#mr_submit_btn").text(label);
        //     } else {
        //         expand.text("-");
        //         $("#advanced_search").val('true');
        //         $("#mr_submit_btn").text('Preview');
        //         $('.mm365-multicheck').select2({
        //             theme: "classic",
        //             placeholder: "Select all that applies",
        //             allowClear: true
        //         });
        //     }
        // });

        $("#advanced_search").val('true');
        $("#mr_submit_btn").text('Preview');

        // Open for advanced search

        if ($('#services').val() != ''
            || $('#industry').val() != ''
            || $('#mr_mbe_category').val() != ''
            || $('#certifications').val() != ''
            || $("input[name='naics_codes[]']").map(function () { return this.value ? this.value : null; }).get() != ''
            || $("#mr_size_of_company").val() != ''
            || $('#mr_number_of_employees').val() != ''
            || $('#looking_for').val() != ''
            || $('input[name=approval_required]:checked').val() == 'yes'
        ) {
            $(".expand").next().slideToggle(1);
            $(".expand").find(".right-arrow").text("-");
            $("#advanced_search").val('true');
            $("#mr_submit_btn").text('Preview');
        }
        /*-------------------------------------------------------------------------------- */
        //URL Checker
        /*-------------------------------------------------------------------------------- */
        function isUrlValid(url) {
            var RegExp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
            if (RegExp.test(url)) {
                return true;
            } else {
                return false;
            }
        }

        /*-------------------------------------------------------------------------------- */
        // Request for match create
        /*-------------------------------------------------------------------------------- */
        $('form#mm365_request_for_match').submit(function (e) {
            e.preventDefault();


            // if () {
            //     alert("exists");
            //   }else{
            //     alert('NAICS code is mandatory. Use the search field to find and add NAICS code');
            //     $(this).parsley().isValid(false);
            //   }
            var count_naics = $("input[name='naics_codes[]']").length;


            var form = $(this)[0];
            var formdata = new FormData(form);
            formdata.append('action', 'match_create');
            formdata.append('nonce', matchmakingAjax.nonce);
            if (count_naics > 0 && $(this).parsley().isValid()) {
                var mode = $('#advanced_search').val();
                $.ajax({
                    url: matchmakingAjax.ajaxurl,
                    data: formdata,
                    type: 'POST',
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $('html, body').animate({ scrollTop: 0 }, 'slow');
                        Notiflix.Loading.hourglass('Creating match request...', { svgColor: '#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor: '#356ab3' });
                    },
                    success: function (data) {
                        if (data != '') {

                            Notiflix.Notify.success('Match request created!');
                            Notiflix.Loading.remove(100);

                            if (mode == 'true') {
                                $('.loader-wrapper').remove();
                                $('.expand h2').html("Preview Match Request");
                                $('.heading-large').html("Preview Match Request");
                                $('form#mm365_request_for_match').html('<section class="company_preview">' + data + '</section>');
                                $('html, body').animate({ scrollTop: 0 }, 0);

                                $(".start-matching").on("click", function (e) {
                                    $('.btn').hide();
                                    $(this).hide();
                                    $('html, body').animate({ scrollTop: 0 }, 'slow');
                                    Notiflix.Loading.hourglass('Finding match...', { svgColor: '#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor: '#356ab3' });
                                });
                            }
                            else if (isUrlValid(data)) {
                                window.location = data;
                            }

                        }
                    }
                });
            }else{
                //alert('NAICS code is mandatory. Please select at least one code to continue')
                Notiflix.Report.failure(
                    'NAICS code is required',
                    'Please select atleast one NAICS code to continue. You can search select the code from "Find NAICS codes" field',
                    'OK',
                    );
                    
            }

        });
        /*-------------------------------------------------------------------------------- */
        //Update
        /*-------------------------------------------------------------------------------- */
        $('form#mm365_request_for_match_update').submit(function (e) {
            e.preventDefault();

            var count_naics = $("input[name='naics_codes[]']").length;
            var form = $(this)[0];
            var formdata = new FormData(form);
            formdata.append('action', 'match_update');
            formdata.append('nonce', matchmakingAjax.nonce);
            if (count_naics > 0 && $(this).parsley().isValid()) {
                var mode = $('#advanced_search').val();
                $.ajax({
                    url: matchmakingAjax.ajaxurl,
                    data: formdata,
                    type: 'POST',
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $('html, body').animate({ scrollTop: 0 }, 'slow');
                        Notiflix.Loading.hourglass('Updating match request parameters...', { svgColor: '#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor: '#356ab3' });
                    },
                    success: function (data) {
                        if (data) {

                            Notiflix.Notify.success('Match request details updated');
                            Notiflix.Loading.remove(100);

                            if (mode == 'true') {
                                $('.loader-wrapper').remove();
                                $('.expand h2').html("Preview Match Request");
                                $('.heading-large').html("Preview Match Request");
                                $('#mm365_request_for_match_update').html(data);
                                $('html, body').animate({ scrollTop: 0 }, 0);
                                $(".start-matching").on("click", function (e) {
                                    $('.btn').hide();
                                    $(this).hide();
                                    $('html, body').animate({ scrollTop: 0 }, 'slow');
                                    Notiflix.Loading.hourglass('Finding match...', { svgColor: '#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor: '#356ab3' });
                                });
                            }
                            else if (isUrlValid(data)) {
                                window.location = data;
                            }

                        }
                    }
                });
            }else{
                //alert('NAICS code is mandatory. Please select at least one code to continue')
                Notiflix.Report.failure(
                    'NAICS code is required',
                    'Please select atleast one NAICS code to continue. You can search select the code from "Find NAICS codes" field',
                    'OK',
                    );
                    
            }


        });



        /*-------------------------------------------------------------------------------- */
        //active Update preview
        /*-------------------------------------------------------------------------------- */
        $('form#mm365_request_for_match_update_active').submit(function (e) {
            e.preventDefault();
            var form = $(this)[0];
            var formdata = new FormData(form);
            formdata.append('action', 'match_unsaved_preview');
            formdata.append('nonce', matchmakingAjax.nonce);
            var count_naics = $("input[name='naics_codes[]']").length;
            if (count_naics > 0 && $(this).parsley().isValid()) {
                var mode = $('#advanced_search').val();
                localStorage.clear();

                localStorage.s_mode = mode;
                localStorage.mr_id = $('#mr_id').val();
                localStorage.service_looking_for = $('#mr_services_looking_for').val();
                //localStorage.service_looking_for       = $("#mr_services_looking_for").tagify('serialize');
                localStorage.services = $('#services').val();
                localStorage.other_services = $('#other_services_input').val();
                localStorage.industry = $('#industry').val();
                localStorage.other_industry = $('#other_industry_input').val();
                localStorage.mr_mbe_category = $('#mr_mbe_category').val();

                localStorage.mr_country = $('#serviceable-countries').val();
                localStorage.mr_state = $('#serviceable-states').val();
                //localStorage.mr_city                   = $('#mr_city').val();

                localStorage.certifications = $('#certifications').val();
                localStorage.other_certification_input = $('#other_certification_input').val();
                localStorage.mr_naics = $("input[name='naics_codes[]']").map(function () { return this.value ? this.value : null; }).get();
                localStorage.mr_size_of_company = $('#mr_size_of_company').val();
                localStorage.mr_number_of_employees = $('#mr_number_of_employees').val();
                localStorage.mr_intassi = $('#looking_for').val();


                if ($('#approval_required_hidden').length > 0) {
                    var approval_mode = $('#approval_required_hidden').val();
                } else {
                    var approval_mode = $('input[type=radio][name=approval_required]:checked').val();
                }

                localStorage.approval_required = approval_mode;

                //console.log(localStorage);

                $.ajax({
                    url: matchmakingAjax.ajaxurl,
                    data: formdata,
                    type: 'POST',
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $('html, body').animate({ scrollTop: 0 }, 'slow');
                        Notiflix.Loading.hourglass('Updating match request parameters...', { svgColor: '#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor: '#356ab3' });
                    },
                    success: function (data) {
                        if (data) {

                            Notiflix.Notify.success('Match request details updated');
                            Notiflix.Loading.remove(100);

                            $('.loader-wrapper').remove();
                            $('.expand h2').html("Preview Match Request");
                            $('.heading-large').html("Preview Match Request");
                            $('#mm365_request_for_match_update_active').html(data);
                            $('html, body').animate({ scrollTop: 0 }, 0);
                            if (mode == 'false') {
                                $(document).ready(function () {
                                    $(".start-matching-again").trigger("click");
                                });
                            }

                            $(".start-matching-again").on("click", function (e) {
                                var redirect_to = $(this).attr('data-publishurl');
                                $(this).hide();
                                //Update match request ajax
                                $.ajax({
                                    url: matchmakingAjax.ajaxurl,
                                    data: {
                                        action: 'match_update',
                                        nonce:matchmakingAjax.nonce,
                                        services_looking_for: localStorage.getItem("service_looking_for"),
                                        mr_id: localStorage.getItem("mr_id"),
                                        services: localStorage.getItem("services").split(","),
                                        other_services: localStorage.getItem("other_services"),
                                        industry: localStorage.getItem("industry").split(","),
                                        other_industry: localStorage.getItem("other_industry"),
                                        mr_mbe_category: localStorage.getItem("mr_mbe_category").split(","),
                                        service_required_countries: localStorage.getItem("mr_country").split(","),
                                        service_required_states: localStorage.getItem("mr_state").split(","),
                                        //service_city:localStorage.getItem("mr_city").split(","),
                                        certifications: localStorage.getItem("certifications").split(","),
                                        other_certification: localStorage.getItem("other_certification_input"),
                                        naics_codes: localStorage.getItem("mr_naics").split(","),
                                        size_of_company: localStorage.getItem("mr_size_of_company"),
                                        number_of_employees: localStorage.getItem("mr_number_of_employees"),
                                        approval_required: localStorage.getItem("approval_required"),
                                        looking_for: localStorage.getItem("mr_intassi").split(",")
                                    },
                                    type: 'POST',
                                    beforeSend: function () {
                                        $('html, body').animate({ scrollTop: 0 }, 'slow');
                                        Notiflix.Loading.hourglass('Updating match request parameters...', { svgColor: '#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor: '#356ab3' });
                                    },
                                    success: function (data) {
                                        if (data) {
                                            //Clear local storage
                                            localStorage.clear();

                                            Notiflix.Notify.success('Match request details updated');
                                            Notiflix.Loading.remove(100);

                                            //Redirect to matching page
                                            window.location = redirect_to;
                                        }
                                    }
                                });

                            });
                        }
                    }
                });
            }else{
                //alert('NAICS code is mandatory. Please select at least one code to continue')
                Notiflix.Report.failure(
                    'NAICS code is required',
                    'Please select atleast one NAICS code to continue. You can search select the code from "Find NAICS codes" field',
                    'OK',
                    );
                    
            }

        });



        $(document).ready(function () {


        //Load localstorage Items if present 
        if (localStorage.getItem("mr_id") === $('#mr_id').val()) {

            setTimeout(() => {
              $('#mr_services_looking_for').val(localStorage.getItem("service_looking_for"));
            
            //$('#mr_services_looking_for').val();
            $('#services').val(localStorage.getItem("services").split(","));
            $('#other_services_input').val(localStorage.getItem("other_services"));
            $('#industry').val(localStorage.getItem("industry").split(","));
            $('#other_industry_input').val(localStorage.getItem("other_industry"));
            $('#mr_mbe_category').val(localStorage.getItem("mr_mbe_category").split(","));
            $('#serviceable-countries').val(localStorage.getItem("mr_country").split(","));
            $('#serviceable-states').val(localStorage.getItem("mr_state").split(","));
            $('#looking_for').val(localStorage.getItem("mr_intassi").split(","));
            $("input[name=approval_required][value=" + localStorage.getItem("approval_required") + "]").prop('checked', true);
            $('#certifications').val(localStorage.getItem("certifications").split(","));
            $('#other_certification_input').val(localStorage.getItem("other_certification_input"));

            $('#mr_size_of_company').val(localStorage.getItem("mr_size_of_company"));
            $('#mr_number_of_employees').val(localStorage.getItem("mr_number_of_employees"));

            //NAICS ARRAY
            $('.naics-codes-dynamic').html('');
            var naics_data = '';
            $.each(localStorage.getItem("mr_naics").split(","), function (u, i) {

                naics_data += '<section class="naics_remove"><div class="form-row  form-group"><div class="col"><input value="' + i + '" id="mr_naics" class="form-control" type="number" name="naics_codes[]"></div><div class="col-2 d-flex align-items-start naics-codes-btn"><a href="#" class="remove-naics-code plus-btn">-</a></div></div></section>';

            });

            $('.naics-codes-dynamic').append(naics_data);
            $("#mr_size_of_company").trigger('change');
            $('#mr_number_of_employees').trigger('change');
            $('#certifications').trigger('change');
            $('#services').trigger('change');
            $('#industry').trigger('change');
            $('#mr_mbe_category').trigger('change');
            $('#looking_for').trigger('change');
            $('#serviceable-countries').trigger('change');
            $('#serviceable-states').trigger('change');

        }, 500);

        


        }


            if ($('#mm365_request_for_match_update_active').length > 0 || $('#mm365_request_for_match_update').length > 0) {
                $(".serviceable-countries").trigger('change');
                setTimeout(() => {

                    if(localStorage.getItem("mr_state") !== null){
                    $('.serviceable-states').val(localStorage.getItem("mr_state").split(","));
                    $(".serviceable-states").trigger('change');
                    }
                    //Toggle open 
                   

                }, 1000);
            }

            if (localStorage.getItem("s_mode") === 'true') {

                $(".accordion-fold").css("display", "block");
                $(".expand .right-arrow").html("-");
               
            }

        });


        /*-----------------------------------
        Filter to match results for user 
         v1.8 onwards
        ------------------------------------*/
        if ($('#matchresults-list').length > 0) {
            var users_resultset = $('#matchresults-list').DataTable({
                responsive: true,
                "processing": true,
                "serverSide": false,
                "pagingType": "first_last_numbers",
                "pageLength": 6,
                //"lengthMenu": [1,2,3,4,5,6, 7, 8, 9, 10],
                "bLengthChange" : false,
                "order": [],
                "columnDefs": [{
                    "targets": 'no-sort',
                    "orderable": false,
                }],
                columns: [
                    { "name": "Company" },
                    { "name": "Council" },
                    { "name": "Location" },
                    { "name": "Company Description" },
                    { "name": "Meeting Status" },
                    { "name": "View Company Details" }
                  ],
                "fnDrawCallback": function (oSettings) {

                },
                "language": {
                    "lengthMenu": "Display _MENU_ companies per page",
                    "zeroRecords": "No Matches Found",
                    "info": "Showing page _PAGE_ of _PAGES_",
                    "infoEmpty": "There are no matched companies",
                    "infoFiltered": "(filtered from _MAX_ total records)"
                },
                oLanguage: { sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>" },

            });
            $('#matchresults-list_filter label:last').append('<br/><small>Search using any of the column values</small>');
            
            // Customize search to search both visible and hidden descriptions
            $('#matchresults-list_filter input').on('keyup change', function () {
                var searchTerm = $(this).val();

                users_resultset.rows().every(function () {
                    var rowData = this.node();
                    var visibleDescription = $(rowData).find('td:nth-child(4)').text();displayed
                    var fullDescription = $(rowData).find('.hidden-full-description').text(); 
                    var combinedDescription = visibleDescription + " " + fullDescription;
                    if (combinedDescription.toLowerCase().includes(searchTerm.toLowerCase())) {
                        $(rowData).show();
                    } else {
                        $(rowData).hide();
                    }
                });
                users_resultset.draw();
            });

            /* Council filtering */

            //Take the category filter drop down and append it to the datatables_filter div. 
            //You can use this same idea to move the filter anywhere withing the datatable that you want.
            $("#matchresults-list_filter.dataTables_filter").prepend($("#councilFilter_label"));

            //Get the column index for the Category column to be used in the method below ($.fn.dataTable.ext.search.push)
            //This tells datatables what column to filter on when a user selects a value from the dropdown.
            //It's important that the text used here (Category) is the same for used in the header of the column to filter
            var uniquekey_colindex = users_resultset.columns().count() - 1;
            var councilIndex = 0;
            for (var i = 0; i < uniquekey_colindex; i++) {
              if (councilIndex == 0) {
                var title = $(users_resultset.column(i).header()).text();
                if (title.match('Council')) {
                  councilIndex = i;
                }
              }
            }


            //Use the built in datatables API to filter the existing rows by the Category column
            $.fn.dataTable.ext.search.push(
                function (settings, data, dataIndex) {
                    var selectedItem = $('#councilFilter').val();
                    var council = data[councilIndex];
                    if (selectedItem === "" || council.includes(selectedItem)) {
                        return true;
                    }
                    return false;
                }
            );

            //Set the change event for the Category Filter dropdown to redraw the datatable each time
            //a user selects a new filter.
            $("#councilFilter").change(function (e) {
                users_resultset.draw();
            });
            users_resultset.draw();


        }



  

        /* End Editing */
    });
})();
