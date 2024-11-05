(function() {
    "use strict";
  jQuery(document).ready(function($) { 
    //STARTS HERE

  function numbersOnly(value) {
      if (typeof (value) === 'number') {
          return value;
      }
  }
             // Load States and Cities based on country select - AJAX - For Copany Address Fields
             $('.serviceable-countries').on("change",function(e){
              var country_ids = $(this).val();
              var state_ids   = $('.serviceable-states').val();

              $.ajax({ 
                  url : companyaddonsAjax.ajax_url,
                  data : {
                      action:'serviceable_states',
                      countries:country_ids,
                      states:state_ids,
                  },
                  type: 'POST', 
                  beforeSend: function() { 
                      $(".serviceable-states").html('<option> Loading ...</option>');
                      $(".serviceable-states").prop('disabled', true);
                      $(".btn-primary").prop('disabled', true); // disable button
                  },                  
                  success : function( data ){

                      $(".btn-primary").prop('disabled', false); // enable button
                      $(".serviceable-states").find('option').remove();
                      if( data ) {   
                        $(".serviceable-states").prop('disabled', false);                     
                        $('.serviceable-states').html(data);
                      }

                  }
              }); 

          });


          $('.serviceable-states').on("change",function(e){

            $(".serviceable-countries").trigger('change');

          });


/**
 * Initiate Editor for company details
 * 
 */

 if($("#edit_company_description").length > 0){


tinymce.init({
    selector: '#edit_company_description',
    menubar: 'file edit view',
    plugins: 'anchor autolink charmap  emoticons  link lists  searchreplace table visualblocks wordcount',
    placeholder: "Please add proper description about your company, make sure that you have added all the necessary keywords about your products and services",
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
    setup: function (editor) {
          editor.on('change', function () {
              editor.save();
              $("#edit_company_description").parsley().reset();
          });
    },
});
}


/**
 * 
 * Ajax init
 * 
 */
 $('form#mm365_update_company_description').submit(function(e){
    e.preventDefault(); 
    var redirect_to = $('#after_success_redirect').val();
    var form        = $('form')[0];
    var formdata    = new FormData(form);
    formdata.append('action', 'mm365_update_company_description');
    formdata.append('nonce',companyaddonsAjax.nonce);
    if ( $(this).parsley().isValid() ) { 
  
        $.ajax({ 
            url : companyaddonsAjax.ajax_url,
            data: formdata,
            type: 'POST',                   
            contentType: false,
            processData: false,
            beforeSend: function() { 
                $('html, body').animate({ scrollTop: 0 }, 'slow');
                Notiflix.Loading.hourglass('Updating company description...',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });
            },
            success : function( data ){
                Notiflix.Loading.remove(100);
                if($.trim(data) == 'success'){
                    Notiflix.Notify.success('Company description updated');
                    setTimeout(() => {
                        window.location = redirect_to; 
                    }, 130);
                }else{
                    Notiflix.Notify.failure('Update failed');
                }

            }
        });
    }
  });



    //ENDS HERE
  });
})();