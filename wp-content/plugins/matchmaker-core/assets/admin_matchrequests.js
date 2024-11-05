(function () {
  "use strict";
  jQuery(document).ready(function ($) {
    /* Start Editing */

    /*-------------------------------------------------------------------------------- */
    /* Select all */
    /*-------------------------------------------------------------------------------- */
    // $("#checkAll").click(function(){
    //   $('#mr-admin-list tbody input:checkbox:enabled').not(this).prop('checked', this.checked);
    //   showSelectedcount();
    // });


    var url_string = window.location.href;
    var url = new URL(url_string);
    var current_mr_id = url.searchParams.get("mr_id");


    /* Show selection count along with approve button */

    $('#mm365-approve-matches').hide();
    $('.md-checkbox').on("click", function () {
      showSelectedcount();
    });


    function showSelectedcount(boxes_override) {

      if (boxes_override === '') {
        var boxes = $('input[name="matched_comp_id[]"]').filter(function () {
          return this.checked && !this.disabled;
        }).length;
      } else {
        var boxes = boxes_override;
      }

      if (boxes > 0) {
        $('#mm365-approve-matches').show();
        $('#mm365-selected-matches').html(boxes);
        if (boxes == 1) { $('#mm365-selected-matches-message').html(" Company "); }
        else { $('#mm365-selected-matches-message').html(" Companies "); }
      } else {
        $('#mm365-approve-matches').hide();
      }
    }


    /*-------------------------------------------------------------------------------- */
    //For menu effect
    /*-------------------------------------------------------------------------------- */
    $.fn.isInViewport = function () {
      var elementTop = $(this).offset().top;
      var elementBottom = elementTop + $(this).outerHeight();
      var viewportTop = $(window).scrollTop();
      var viewportBottom = viewportTop + $(window).height();
      return elementBottom > viewportTop && elementTop < viewportBottom;
    };


    $(window).on('resize scroll', function () {

      if ($('#mm365-approve-matches').length > 0) {
        var viewPort_width = $(window).width();
        var viewPort = $(window).height();
        var panelHeight = $('.dashboard-content-panel').height();
        var botDiff = (panelHeight - viewPort);
        var valuetoAttach = (botDiff + 210);
        //console.log(panelHeight + "-" + viewPort + "=" + botDiff);
        if (viewPort_width > 850) {
          if ($('.main-footer').isInViewport()) {
            $('#mm365-approve-matches').addClass('shift-position');
            $('#mm365-approve-matches').css('bottom', "-" + valuetoAttach + "px");
          } else {
            $('#mm365-approve-matches').removeClass('shift-position');
            $('#mm365-approve-matches').css('bottom', "0px");
          }
        }
      }

    });



    /*-------------------------------------------------------------------------------- */
    // Manage matched companies
    /*-------------------------------------------------------------------------------- */
    $('form#manage_matched_companies').submit(function (e) {
      e.preventDefault();
      var form = $(this)[0];
      var formdata = new FormData(form);

      var ls_list = JSON.parse(localStorage.getItem(current_mr_id + '_checkboxValues'));
      const to_approve = [];
      $.each(ls_list, function (key1, value1) {
        if (value1 === true) {
          console.log(key1);
          to_approve.push(key1);
        }
      });

      formdata.append('action', 'mm365_admin_matchrequests_approve');
      formdata.append('nonce', adminmatchrequestAjax.nonce);
      formdata.append('to_approve_list', to_approve.toString());

      $.ajax({
        url: adminmatchrequestAjax.ajaxurl,
        data: formdata,
        type: 'POST',
        contentType: false,
        processData: false,
        beforeSend: function () {
          Notiflix.Notify.info('Approving selected companies..');
        },
        success: function (data) {

          if (data == 'success') {

            Notiflix.Notify.success('Selected companies approved', () => { window.location.reload() });
            setTimeout(() => {
              window.location.reload();
            }, 3000);
            localStorage.removeItem(current_mr_id + "_checkboxValues");

          } else {
            $.confirm({
              title: 'Something went wrong!',
              content: "Unable to approve matches!",
              type: 'red',
              typeAnimated: true,
              icon: 'fas fa-exclamation-circle',
              theme: 'modern',
              //autoClose: 'close|3000',
              buttons: {
                close: {
                  btnClass: 'btn btn-primary',
                  action: function () {
                    window.location.reload();
                  }
                }
              }
            });

          }
        }
      });


    });


    /*-------------------------------------------------------------------------------- */
    // Delete match request
    // v1.7 onwards
    // Puts match request to pending status
    /*-------------------------------------------------------------------------------- */
    $('#sa-delete-mr').on("click", function (e) {
      e.preventDefault();

      var mrid = $(this).data('mrid');
      var redirect = $(this).data('redirect_url');

      $.confirm({
        title: 'Do you want to delete this match request?',
        content: 'Click \'Confirm\' or \'Cancel\' to continue',
        icon: "fas fa-passport",
        theme: 'modern',
        type: 'red',
        buttons: {
          confirm: {
            btnClass: 'btn btn-primary',
            action: function () {
              //$.alert('Confirmed!' + cert_id);
              $.ajax({
                url: adminmatchrequestAjax.ajaxurl,
                data: {
                  action: 'mm365_delete_matchrequest',
                  nonce: adminmatchrequestAjax.nonce,
                  mr_id: mrid
                },
                type: 'POST',
                beforeSend: function () {
                  Notiflix.Notify.info('Deleting match request...');
                },
                success: function (data) {
                  if (data == '1') {

                    Notiflix.Notify.success('Match request deleted!', () => { window.location = redirect });
                    setTimeout(() => {
                      window.location = redirect;
                    }, 2000);

                  } else {
                    Notiflix.Notify.failure('Something went wrong!');
                  }
                }
              });
            }

          },
          cancel: {
            btnClass: 'btn btn-primary red',
          }
        }
      });




    });

    /*-------------------------------------------------------------------------------- */
    // Find and add companies to match
    // v1.7 onwards
    /*-------------------------------------------------------------------------------- */


    // $(".js-example-data-array").select2({
    //   data: data
    // });


    if ($('.find-companies-to-add').length > 0) {
      $(function () {
        $('.find-companies-to-add').select2({
          ajax: {
            url: adminmatchrequestAjax.ajaxurl,
            dataType: 'json',
            delay: 250,
            data: function (params) {
              return {
                q: params.term,
                action: 'mm365_search_companynames'
              };
            },
            processResults: function (data) {
              var options = [];
              if (data) {
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
          minimumInputLength: 3,
          width: '100%',
          escapeMarkup: function (text) { return text; }
        });
      });
    }
    //$.parseHTML()
    //Facybox adjustment for select 2 
    $(document).on('onComplete.fb', function (e, instance, current) {

      current.$slide.find('select').select2({
        dropdownParent: current.$content,
      });

    });



    //Process the form - Update selected companie to MR


    $('form#search-companies-mr').submit(function (e) {
      e.preventDefault();
      var form = $(this)[0];
      var formdata = new FormData(form);
      formdata.append('action', 'mm365_manually_add_companies_to_mr');
      formdata.append('nonce', adminmatchrequestAjax.nonce);
      var redirect = $(this).data('redirect_url');
      $.ajax({
        url: adminmatchrequestAjax.ajaxurl,
        data: formdata,
        type: 'POST',
        contentType: false,
        processData: false,
        beforeSend: function () {
          Notiflix.Notify.info('Adding selected companies...');
        },
        success: function (data) {
          if (data == '1') {

            Notiflix.Notify.success('Selected companies added to match results', () => { window.location = redirect });
            Notiflix.Notify.warning('If match is not "auto approved", please do not forget to approve the newly added companies', () => { window.location = redirect });

            setTimeout(() => {
              window.location = redirect;
            }, 3000);

          } else {

            if (data == 3) {
              Notiflix.Notify.failure('Selected company already exists in the match!', { timeout: 3000 });
            } else {
              Notiflix.Notify.failure('Something went wrong!');
            }


          }
        }
      });


    });







    /**------------------------------------------------------------ */

    /*-------------------------------------------------------------------------------- */
    // Force to No match
    // v1.7 onwards
    /*-------------------------------------------------------------------------------- */

    $('#sa-force-nomatch').on("click", function (e) {
      e.preventDefault();

      var mrid = $(this).data('mrid');
      var redirect = $(this).data('redirect_url');

      $.confirm({
        title: 'Are you sure you want to put this match request to \'No Match\' Status?',
        content: 'Click \'Confirm\' or \'Cancel\' to continue',
        icon: "fas fa-exclamation-circle",
        theme: 'modern',
        type: 'red',
        buttons: {
          confirm: {
            btnClass: 'btn btn-primary',
            action: function () {
              //$.alert('Confirmed!' + cert_id);
              $.ajax({
                url: adminmatchrequestAjax.ajaxurl,
                data: {
                  action: 'mm365_force_nomatch_matchrequest',
                  nonce: adminmatchrequestAjax.nonce,
                  mr_id: mrid
                },
                type: 'POST',
                beforeSend: function () {
                  Notiflix.Notify.info('Changing match request status...');

                },
                success: function (data) {
                  if (data == '1') {

                    Notiflix.Notify.success('Match request status changed to \'No Match\'!', () => { window.location = redirect });
                    setTimeout(() => {
                      window.location = redirect;
                    }, 1000);

                  } else {
                    Notiflix.Notify.failure('Something went wrong!');
                  }
                }
              });
            }

          },
          cancel: {
            btnClass: 'btn btn-primary red',
          }
        }
      });




    });


    /*-----------------------------------
    Filter to match results set v1.8 onwards
    ------------------------------------*/
    if ($('#mr-admin-list').length > 0) {

      var councilSelected = $('#councilFilter').val();

      /* Create an array with the values of all the checkboxes in a column */
      //For enabling checkbox sorting
      $.fn.dataTable.ext.order['dom-checkbox'] = function (settings, col) {
        return this.api().column(col, { order: 'index' }).nodes().map(function (td, i) {
          return $('input', td).prop('checked') ? '1' : '0';
        });
      }


      var resultset = $('#mr-admin-list').DataTable({
        "searching": true,
        responsive: true,
        "processing": true,
        "serverSide": false,
        "pagingType": "first_last_numbers",
        "pageLength": 6,
        "lengthMenu": [1,2,3,4,5,6, 7, 8, 9, 10],
        "order": [],
        "columnDefs": [{
          "targets": 'no-sort',
          "orderable": false,
        }],
        columns: [
          { orderDataType: 'dom-checkbox' },
          { "name": "Company" },
          { "name": "Council" },
          { "name": "Location" },
          { "name": "Description" },
          { "name": "match_status" },
          { "name": "meeting_status" },
          { "name": "conf" }
        ],
        columnDefs: [
          {
            target: 7,
            visible: false
          }
        ],
        "fnDrawCallback": function (oSettings) { },
        "language": {
          "lengthMenu": "Display _MENU_ companies per page",
          "zeroRecords": " No Matched Companies",
          "info": "Showing page _PAGE_ of _PAGES_",
          "infoEmpty": "There are no matched companies ",
          "infoFiltered": "(filtered from _MAX_ total records)"
        },
        oLanguage: { sProcessing: "<div id='loader'><i class='fa fa-spinner' aria-hidden='true'></i></div>" },

      });
      $('#mr-admin-list_filter label:last').append('<br/><small>Search using any of the column values</small>');


      //For enabling checkbox sorting
      $(':checkbox').on('change', function (e) {
        var row = $(this).closest('tr');
        var hmc = row.find(':checkbox:enabled').length;
        var kluj = parseInt(hmc);
        row.find('td.counter').text(kluj);
        resultset.row(row).invalidate('dom');
      });




      /* When all items are selected  */
      resultset.on("click", "#checkAll", function () {

        // Get all rows with search applied
        var rows = resultset.rows().nodes();

        // Check/uncheck checkboxes for all rows in the table
        $('input:checkbox:enabled', rows).not(this).prop('checked', this.checked);

        var checkboxValues = JSON.parse(localStorage.getItem(current_mr_id + '_checkboxValues')) || {}

        checkboxValues['checkAll'] = 'checked';

        $("input:checkbox:enabled", rows).each(function () {
          //selectedIds.push($(this).attr('id'));
          checkboxValues[$(this).attr('id')] = $(this).prop('checked');
        });


        localStorage.setItem(current_mr_id + "_checkboxValues", JSON.stringify(checkboxValues));

        //get the count of all items selected
        var rcount = $('input:checkbox:enabled', rows).length;
        //Show count
        showSelectedcount(rcount);

      });

      /* When specific items are selected  */
      var checkboxValues = JSON.parse(localStorage.getItem(current_mr_id + '_checkboxValues')) || {}
      resultset.on("change", "input:checkbox:enabled", function () {

        var rows = resultset.rows().nodes();

        var count = 0;
        $("input:checkbox:enabled", rows).each(function () {
          //selectedIds.push($(this).attr('id'));
          checkboxValues[this.id] = this.checked;
          if (this.checked == true) {
            count++;
          }
        });

        localStorage.setItem(current_mr_id + "_checkboxValues", JSON.stringify(checkboxValues));

        //get the count of all items selected
        //Show count
        showSelectedcount(count);

        var rcount = $('input:checkbox:enabled', rows).length;
        if (rcount === count) {
          $('#checkAll').prop('checked', true);
        } else {
          $('#checkAll').prop('checked', false);
        }

      });


      /* Council filtering */
      //Find the index of the column - council
      var uniquekey_colindex = resultset.columns().count() - 1;
      var councilIndex = 0;
      for (var i = 0; i < uniquekey_colindex; i++) {
        if (councilIndex == 0) {
          var title = $(resultset.column(i).header()).text();
          if (title.match('Council')) {
            councilIndex = i;
          }
        }
      }

      //Take the category filter drop down and append it to the datatables_filter div. 
      //You can use this same idea to move the filter anywhere withing the datatable that you want.
      $("#mr-admin-list_filter.dataTables_filter").prepend($("#councilFilter_label"));
      $("#mr-admin-list_filter.dataTables_filter").prepend($("#conferenceFilter_label"));





      $("#conferenceFilter").change(function (e) {

        let conf_id = $(this).val();
        //let conf_id = 6512;

        //Map through nodes and if company id matches replace it with conefernce id
        if (conf_id) {

          //Clear search before proceeding
          $.fn.dataTable.ext.search.pop()

          $.ajax({
            url: adminmatchrequestAjax.ajaxurl,
            data: {
              action: 'mm365_get_suppliers_in_conference',
              nonce: adminmatchrequestAjax.nonce,
              conf_id: conf_id
            },
            type: 'POST',
            success: function (data) {

              var cmpIds = JSON.parse(data);

              if (cmpIds.length > 0) {

                cmpIds.map(
                  (cmpid) => {
                    resultset.column(7).nodes().each(function (node, index, dt) {
                      if (node.dataset.cmpid == cmpid) {
                        //put conf id to node
                        resultset.cell(node).data(conf_id);
                      }
                    });
                  }

                )

                //Conference filter
                $.fn.dataTable.ext.search.push(
                  function (settings, data, dataIndex) {
                    var selectedItem = conf_id
                    var conference = data[7];
                    if (selectedItem === "" || conference.includes(selectedItem)) {
                      return true;
                    }
                    return false;
                  }
                );

                resultset.draw();
              } else {
                //Notflix
                Notiflix.Notify.failure('None of the suppliers matched are participating in selected conference', { timeout: 3000 });
              }

            }
          });

        } else {

          //Remove search condition
          $.fn.dataTable.ext.search.pop()
          resultset.draw();

        }
        resultset.draw();

      });

      //Set the change event for the Category Filter dropdown to redraw the datatable each time
      //a user selects a new filter.
      $("#councilFilter").change(function (e) {



        //Use the built in datatables API to filter the existing rows by the  column
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


        councilSelected = $(this).val();

        if (councilSelected != '') {
          var empty_message = 'No companies matched from ' + councilSelected;
        } else {
          var empty_message = 'No companies found';
        }

        resultset.draw();


        var $empty = $('#mr-admin-list').find('.dataTables_empty');
        if ($empty) $empty.html(empty_message);

      });

      resultset.draw();

      var $empty = $('#mr-admin-list').find('.dataTables_empty');

      if ($empty) $empty.html("No companies matched from " + councilSelected + ". Please use council filter to see matched companies from other councils");


      /* When page reloaded with preselect  */
      $(document).ready(function () {

        var rows = resultset.rows().nodes();

        const checkboxValues = JSON.parse(localStorage.getItem(current_mr_id + '_checkboxValues')) || {};

        if (rows !== null) {
          var count = 0;
          $("input:checkbox:enabled", rows).each(function () {
            var cur_id = $(this).attr('id');
            if (cur_id in checkboxValues) {
              $(this).prop('checked', checkboxValues[cur_id]);
              if ($(this).prop('checked') == true) {
                count++;
              }

            }
          });

          //Show count
          showSelectedcount(count);

          var rcount = $('input:checkbox:enabled', rows).length;
          if (rcount === count) {
            $('#checkAll').prop('checked', true);
          } else {
            $('#checkAll').prop('checked', false);
          }


        }

      });







    }




    /*--------------------------------------------------------------------------------- */



    /* End Editing */
  });
})();
