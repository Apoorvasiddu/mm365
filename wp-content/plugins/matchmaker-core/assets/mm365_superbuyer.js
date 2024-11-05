(function () {
  "use strict";
  jQuery(document).ready(function ($) {
    //STARTS HERE

    /**
     * 
     * Email availability check
     * 
     */

    $('#mm365_superbuyer_email').bind('change keyup input', function (e) {
      $.ajax({
        url: superBuyerAjax.ajax_url,
        data: {
          action: 'email_availablity_check',
          email: $(this).val(),
          nonce: superBuyerAjax.nonce,
        },
        type: 'POST',
        success: function (data) {
          //console.log(data);
          if (data == 1) {
            $('#email-check-success').css('display', 'block');
            $('#email-check-fail').css('display', 'none');
          } else {
            $('#email-check-success').css('display', 'none');
            $('#email-check-fail').css('display', 'block');
          }

        }
      });
    });

    /**
     * 
     * User name availability check
     * 
     */
    $('#mm365_superbuyer_username').bind('change keyup input', function (e) {
      $.ajax({
        url: superBuyerAjax.ajax_url,
        data: {
          action: 'username_availability_check',
          username: $(this).val(),
          nonce: superBuyerAjax.nonce,
        },
        type: 'POST',
        success: function (data) {
          if (data == 1) {
            $('#username-check-success').css('display', 'block');
            $('#username-check-fail').css('display', 'none');
          } else {
            $('#username-check-success').css('display', 'none');
            $('#username-check-fail').css('display', 'block');
          }
        }
      });
    });



    /**
     * 
     * Search and add buyers
     * 
     */

    if ($('#associated_buyers').length > 0 || $('#edit_associated_buyers').length > 0) {

      $(function () {

        $('#associated_buyers,#edit_associated_buyers').select2({
          ajax: {
            url: superBuyerAjax.ajax_url,
            dataType: 'json',
            delay: 250,
            data: function (params) {
              return {
                q: params.term,
                council: $('#superbuyer_council_id').val(),
                action: 'get_buyer_companies'
              };
            },
            processResults: function (data) {
              var options = [];
              if (data) {
                // console.log(data);
                $.each(data, function (index, text) {
                  options.push({ id: text[0], text: text[1] });
                });
              }
              return {
                results: options
              };
            },
            cache: true
          },
          minimumInputLength: 2,
          width: '100%',
          escapeMarkup: function (text) { return text; }
        });
      });
      
    }


    /**
     * 
     * Clear buyers drop down if council is changed
     * 
     */
    $('#superbuyer_council_id').on("change", function (e) {

      $('#associated_buyers').val(null).trigger('change');

    });



    /**
    * 
    * Preload selected companies for editing
    * 
    */

    if ($('#mm365_update_superbuyer #edit_associated_buyers').length > 0) {
      var superBuyerSelect = $('#edit_associated_buyers');
      var superbuyer_id = superBuyerSelect.data('superbuyer');

      if (superbuyer_id != '') {

        $.ajax({
          type: 'POST',
          dataType: 'json',
          url: superBuyerAjax.ajax_url,
          data: {
            superbuyer_id: superbuyer_id,
            action: 'get_existing_buyer_companies',
            nonce: superBuyerAjax.nonce,
          },
        }).then(function (data) {

          $.each(data, function (index, text) {
            var option = new Option(text.text, text.id, true, true);
            superBuyerSelect.append(option).trigger('change');
          });

          //manually trigger the `select2:select` event
          superBuyerSelect.trigger({
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
     * Insert Super buyer User
     * 
     */
    $('form#mm365_add_superbuyer').submit(function (e) {
      e.preventDefault();
      var redirect_to = $('#after_success_redirect').val();
      var form = $('form')[0];
      var formdata = new FormData(form);
      formdata.append('action', 'create_superbuyer');
      formdata.append('nonce', superBuyerAjax.nonce);
      if ($(this).parsley().isValid()) {

        $.ajax({
          url: superBuyerAjax.ajax_url,
          data: formdata,
          type: 'POST',
          contentType: false,
          processData: false,
          beforeSend: function () {
            $('html, body').animate({ scrollTop: 0 }, 'slow');
            //$('.company_preview').before('<div class="loader-wrapper"><div id="loader" class="loader-matchrequest"><i class="fa fa-spinner" aria-hidden="true"></i></div></div>');                                                               
            Notiflix.Loading.hourglass('Adding super buyer...', { svgColor: '#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor: '#356ab3' });
          },
          success: function (data) {

            if (data == 'success') {

              Notiflix.Loading.remove(1800);
              Notiflix.Notify.success('Super buyer added successfully!', () => { window.location = redirect_to });
              setTimeout(() => {
                window.location = redirect_to;
              }, 1800);

            } else {
              $.confirm({
                title: 'Unable to create account!',
                content: "Please check the input values",
                type: 'red',
                typeAnimated: true,
                icon: 'fas fa-exclamation-circle',
                theme: 'modern',
                buttons: {
                  close: {
                    btnClass: 'btn btn-primary',
                    action: function () {
                      Notiflix.Loading.remove(300);
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
      * Update User
      * 
      */
    $('form#mm365_update_superbuyer').submit(function (e) {
      e.preventDefault();
      var redirect_to = $('#after_success_redirect').val();
      var form = $('form')[0];
      var formdata = new FormData(form);
      formdata.append('action', 'update_superbuyer');
      formdata.append('nonce', superBuyerAjax.nonce);
      if ($(this).parsley().isValid()) {

        $.ajax({
          url: superBuyerAjax.ajax_url,
          data: formdata,
          type: 'POST',
          contentType: false,
          processData: false,
          beforeSend: function () {
            $('html, body').animate({ scrollTop: 0 }, 'slow');
            //$('.company_preview').before('<div class="loader-wrapper"><div id="loader" class="loader-matchrequest"><i class="fa fa-spinner" aria-hidden="true"></i></div></div>');                                                               
            Notiflix.Loading.hourglass('Updating super buyer...', { svgColor: '#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor: '#356ab3' });
          },
          success: function (data) {

            if (data == 'success') {

              Notiflix.Loading.remove(1800);
              Notiflix.Notify.success('Super buyer details updated successfully!', () => { window.location = redirect_to });
              setTimeout(() => {
                window.location = redirect_to;
              }, 1800);


            } else {
              $.confirm({
                title: 'Unable to update details!',
                content: "Please check the input values",
                type: 'red',
                typeAnimated: true,
                icon: 'fas fa-exclamation-circle',
                theme: 'modern',
                buttons: {
                  close: {
                    btnClass: 'btn btn-primary',
                    action: function () {
                      Notiflix.Loading.remove(300);
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
    * List Council Managers
    * 
    */
    if ($('#superadmin_list_superbuyers').length > 0) {

      $('#superadmin_list_superbuyers').DataTable({
        responsive: true,
        "processing": true,
        "serverSide": false,
        //"ajax": {url:certificationAjax.ajax_url, data:{'action':'superadmin_list_superbuyersing', 'status':filter_stat, 'period':period}},
        "pagingType": "first_last_numbers",
        "order": [],
        "columnDefs": [{
          "targets": 'no-sort',
          "orderable": false,
        }],
        "fnDrawCallback": function (oSettings) { },
        "language": {
          "lengthMenu": "Display _MENU_ council managers per page",
          "zeroRecords": "No Council Managers found",
          "info": "Showing page _PAGE_ of _PAGES_",
          "infoEmpty": "There are no Council Managers found",
          "infoFiltered": "(filtered from _MAX_ total records)"
        },
        oLanguage: { sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>" }
      });
      $('#superadmin_list_superbuyers_filter label').after('<br/><small>Search using any of the column values</small>');
    }


    /**
     * 
     * 
     */
    /* Init MCE for meeting agenda */
    if($('#opportunities').length > 0){
   
      tinymce.init({
        selector: '#opportunities',
        menubar: '',
        plugins: 'anchor autolink charmap  emoticons  link lists  searchreplace table visualblocks wordcount',
        placeholder: "Please add description about the opportunities you are bringing",
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        setup: function (editor) {
              editor.on('change', function () {
                  editor.save();
                  $("#opportunities").parsley().reset();
              });
        },
      });
  
  
    }

    /**
     * 
     * Insert Sub buyer User
     * 
     */
    $('form#mm365_add_subbuyer').submit(function (e) {
      e.preventDefault();
      var redirect_to = $('#after_success_redirect').val();
      var form = $('form')[0];
      var formdata = new FormData(form);
      formdata.append('action', 'add_sub_buyer');
      formdata.append('nonce', superBuyerAjax.nonce);
      if ($(this).parsley().isValid()) {

        $.ajax({
          url: superBuyerAjax.ajax_url,
          data: formdata,
          type: 'POST',
          contentType: false,
          processData: false,
          beforeSend: function () {
            $('html, body').animate({ scrollTop: 0 }, 'slow');
            //$('.company_preview').before('<div class="loader-wrapper"><div id="loader" class="loader-matchrequest"><i class="fa fa-spinner" aria-hidden="true"></i></div></div>');                                                               
            Notiflix.Loading.hourglass('Adding associated buyer...', { svgColor: '#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor: '#356ab3' });
          },
          success: function (data) {

            if (data == 'success') {

              Notiflix.Loading.remove(1800);
              Notiflix.Notify.success('Associated buyer added successfully!', () => { window.location = redirect_to });
              setTimeout(() => {
                window.location = redirect_to;
              }, 1800);

            } else {
              $.confirm({
                title: 'Unable to create account!',
                content: "Please check the input values",
                type: 'red',
                typeAnimated: true,
                icon: 'fas fa-exclamation-circle',
                theme: 'modern',
                buttons: {
                  close: {
                    btnClass: 'btn btn-primary',
                    action: function () {
                      Notiflix.Loading.remove(300);
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