(function () {
    "use strict";
    jQuery(document).ready(function ($) {
        /* Start Editing */

        //$('#other_certification_input').hide();

        if ($('#mm365_generate_report_comapny').length > 0 || $('#mm365_generate_report_matchrequests').length > 0) {
            
            if ($("#certifications option[value=other]:selected").length > 0) { $('#other_certification_input').show(); } else { $('#other_certification_input').hide(); }
            $('#certifications').change(function () {
                if ($("#certifications option[value=other]:selected").length > 0) { $('#other_certification_input').show(); $("#other_certification_input").next().show("ul"); $('#other_certification_input').attr('required', 'true'); }
                else { $('#other_certification_input').attr('value', ''); $('#other_certification_input').hide(); $("#other_certification_input").next().hide("ul"); $('#other_certification_input').removeAttr('required'); $('#other_certification_input').val(''); }
            });
            $('#other_certification').click(function () {
                if ($("#certifications option[value=other]:selected").length > 0) { $('#other_certification_input').show(); $("#other_certification_input").next().show("ul"); $('#other_certification_input').attr('required', 'true'); }
                else { $('#other_certification_input').hide(); $("#other_certification_input").next().hide("ul"); $('#other_certification_input').removeAttr('required'); $('#other_certification_input').val(''); }
            });

            $('#other_industry_input').hide();
            $('#industry').change(function () {
                if ($("#industry option[value=other]:selected").length > 0) { $('#other_industry_input').show(); }
                else { $('#other_industry_input').hide(); $('#other_industry_input').removeAttr('required'); $('#other_industry_input').val(''); }
            });
            $('#other_industry').click(function () {
                if ($("#industry option[value=other]:selected").length > 0) { $('#other_industry_input').show(); }
                else { $('#other_industry_input').hide(); $('#other_industry_input').removeAttr('required'); $('#other_industry_input').val(''); }
            });

            $('#other_services_input').hide();
            $('#services').change(function () {
                if ($("#services option[value=other]:selected").length > 0) { $('#other_services_input').show(); }
                else { $('#other_services_input').hide(); $('#other_services_input').removeAttr('required'); $('#other_services_input').val(''); }
            });
            $('#other_services').click(function () {
                if ($("#services option[value=other]:selected").length > 0) { $('#other_services_input').show(); }
                else { $('#other_services_input').hide(); $('#other_services_input').removeAttr('required'); $('#other_services_input').val(''); }
            });





            // $("#download-report").on("click", function (e) {
            //     if ($('form#mm365_generate_report_comapny').parsley().isValid()) {
            //         $.fancybox.open(
            //             ['<div class="popnotice text-center"><div class="popnotice-downloading-box"><svg version="1.1" fill="#356ab3" xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 512 512" xml:space="preserve"><path d="M382.56 233.376A15.96 15.96 0 0 0 368 224h-64V16c0-8.832-7.168-16-16-16h-64c-8.832 0-16 7.168-16 16v208h-64a16.013 16.013 0 0 0-14.56 9.376c-2.624 5.728-1.6 12.416 2.528 17.152l112 128A15.946 15.946 0 0 0 256 384c4.608 0 8.992-2.016 12.032-5.472l112-128c4.16-4.704 5.12-11.424 2.528-17.152z"/><path d="M432 352v96H80v-96H16v128c0 17.696 14.336 32 32 32h416c17.696 0 32-14.304 32-32V352h-64z"/></svg></div><p>Report is being downloaded...</p></div>'],
            //             {
            //                 afterShow: function (instance, current) {
            //                     //$('form#mm365_generate_report_comapny').submit();
            //                     setTimeout(function () { $.fancybox.close(); }, 1600); // 3000 = 3 secs
            //                 },
            //                 afterClose: function (instance, current) {
            //                     //window.location.reload();
            //                     //$('form#mm365_generate_report_comapny').submit();
            //                     // var url = window.location.href;    
            //                     // if (url.indexOf('?') > -1){
            //                     // url += '&stat=succ'
            //                     // }else{
            //                     // url += '?stat=succ'
            //                     // }
            //                     // window.location.href = url;
            //                 }

            //             }
            //         );
            //     }
            // });


            // $("#download-report-mr").on("click", function (e) {
            //     if ($('form#mm365_generate_report_matchrequests').parsley().isValid()) {
            //         $.fancybox.open(
            //             ['<div class="popnotice text-center"><div class="popnotice-downloading-box"><svg version="1.1" fill="#356ab3" xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 512 512" xml:space="preserve"><path d="M382.56 233.376A15.96 15.96 0 0 0 368 224h-64V16c0-8.832-7.168-16-16-16h-64c-8.832 0-16 7.168-16 16v208h-64a16.013 16.013 0 0 0-14.56 9.376c-2.624 5.728-1.6 12.416 2.528 17.152l112 128A15.946 15.946 0 0 0 256 384c4.608 0 8.992-2.016 12.032-5.472l112-128c4.16-4.704 5.12-11.424 2.528-17.152z"/><path d="M432 352v96H80v-96H16v128c0 17.696 14.336 32 32 32h416c17.696 0 32-14.304 32-32V352h-64z"/></svg></div><p>Report is being downloaded...</p></div>'],
            //             {
            //                 afterShow: function (instance, current) {
            //                     //$('form#mm365_generate_report_comapny').submit();
            //                     setTimeout(function () { $.fancybox.close(); }, 1600); // 3000 = 3 secs
            //                 },
            //                 afterClose: function (instance, current) {
            //                     $("#mm365_generate_report_matchrequests")[0].reset()
            //                 }

            //             }
            //         );
            //     }
            // });




            $('#service_type').on("change", function (e) {
                if ($(this).val() == 'buyer') {
                    $('#intassi-block, #mc-block').hide();
                } else { $('#intassi-block, #mc-block').show(); }
            });

            $('#closurefilter-block').hide();
            $("#match_closure_filter_cancelled").select2();
            $("#match_closure_filter_completed").select2();

            $('#match_status').on("change", function (e) {
                var cur_selection = $(this).val();
                //alert(cur_selection);
                if (cur_selection == 'completed' || cur_selection == 'cancelled') {
                    $('#closurefilter-block').show();
                    if (cur_selection == 'completed') {
                        $('#reason-label').html('Reason for completion');
                        //$("#match_closure_filter_cancelled").select2("destroy");
                        if ($('#match_closure_filter_cancelled').data('select2')) {
                            $("#match_closure_filter_cancelled").select2("destroy");
                        }
                        $("#match_closure_filter_cancelled").hide();
                        $("#match_closure_filter_completed").show();
                        $("#match_closure_filter_completed").select2();

                    } else {
                        $('#reason-label').html('Reason for cancellation');

                        //$("#match_closure_filter_completed").select2("destroy");
                        if ($('#match_closure_filter_completed').data('select2')) {
                            $("#match_closure_filter_completed").select2("destroy");
                        }

                        $("#match_closure_filter_completed").hide();
                        $("#match_closure_filter_cancelled").show();
                        $("#match_closure_filter_cancelled").select2();

                    }

                } else {
                    $('#closurefilter-block').hide();
                    if ($('#match_closure_filter_cancelled').data('select2')) {
                        $("#match_closure_filter_cancelled").select2("destroy");
                    }
                    if ($('#match_closure_filter_completed').data('select2')) {
                        $("#match_closure_filter_completed").select2("destroy");
                    }

                }
            });


            /**
             * View report - Matchrequests
             * Get all form items to local storage and redirect to view report
             * Read items from localstorage in template and show report
             * 
             * mrv = match request view
             */
            $('#matchrequests-view-report-filtered').on("click", function (e) {
                if ($('form#mm365_generate_report_matchrequests').parsley().isValid()) {
                    e.preventDefault();
                    localStorage.clear();
                    var redirect_to = $(this).data('redirect');


                    localStorage.mrv_from_date = $('input[name=from_date]').val();
                    localStorage.mrv_to_date = $('input[name=to_date]').val();
                    localStorage.mrv_services = $('#services').val();
                    localStorage.mrv_services_oth = $('#other_services_input').val();
                    localStorage.mrv_industries = $('#industry').val();
                    localStorage.mrv_industries_oth = $('#other_industry_input').val();
                    localStorage.mrv_matchstatus = $('#match_status').val();

                    //conditionally add filter
                    if ($('#match_status').val() == 'completed') {
                        localStorage.mrv_closure_filter = $('#match_closure_filter_completed').val();
                    } else {
                        localStorage.mrv_closure_filter = $('#match_closure_filter_cancelled').val();
                    }

                    localStorage.mrv_minoritycategory = $('#minority_category').val();
                    localStorage.mrv_country = $('.serviceable-countries').val();
                    localStorage.mrv_state = $('.serviceable-states').val();
                    //localStorage.mrv_city = $('.city').val();
                    localStorage.mrv_numberofemployees = $('select[name=number_of_employees]').val();
                    localStorage.mrv_sizeofcompany = $('select[name=size_of_company]').val();
                    localStorage.mrv_certifications = $('#certifications').val();
                    localStorage.mrv_certifications_oth = $('#other_certification_input').val();
                    localStorage.mrv_naics = $('input[name="naics_codes[]"]').map(function () { return this.value }).get();
                    localStorage.mrv_intassi = $('#international_assistance').val();

                    localStorage.mrv_council = $('#councilFilter').val();

                    //For super buyer match request report - get value of my_team_member
                    if ($('#my_team_member').length > 0) {
                        localStorage.mrv_buyer_team = $('#my_team_member').val();
                    } else {
                        //For fail safe 
                        localStorage.mrv_buyer_team = '';
                    }

                    window.location = redirect_to;


                }
            });


        }






        /* End Editing */
    });
})();
