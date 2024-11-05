(function() {
    "use strict";
  jQuery(document).ready(function($) { 
    //STARTS HERE

 

/**
 * Initiate Editor for company details
 * 
 */

function mce_initiation(id){
    // tinyMCE.init({
    //     mode : "none",
    //     statusbar: true,
    //     placeholder: "",
    //     content_style:
    //     "body { background: #fff; color: #333; font-size: 13pt; }",
    //     setup: function (editor) {
    //         editor.on('change', function () {
    //             editor.save();
    //             $("#".id).parsley().reset();
    //         });
    //     },
    //     mobile: {
    //         theme: 'mobile',
    //     },
    //     branding: false,
    //     //plugins: "paste lists",              
    //     //toolbar: 'numlist bullist'
    //     menubar: false,
    //     browser_spellcheck: true,
    //     plugins: [
    //     'advlist autolink lists link charmap print preview anchor',
    //     'searchreplace visualblocks  fullscreen',
    //     'insertdatetime media table paste wordcount paste anchor'
    //     ],
    //     toolbar: 'link undo redo | formatselect | ' +
    //     'bold italic backcolor | alignleft aligncenter ' +
    //     'alignright alignjustify | bullist numlist outdent indent | ' +
    //     'removeformat',
    //     paste_as_text: true,
        
    // });
    // tinyMCE.execCommand('mceAddEditor', false, id);

    tinymce.init({
      selector: id,
      menubar: 'file edit view',
      plugins: 'anchor autolink charmap  emoticons  link lists  searchreplace table visualblocks wordcount',
      placeholder: "",
      toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
      setup: function (editor) {
            editor.on('change', function () {
                editor.save();
                $("#".id).parsley().reset();
            });
      },
    });
  
}

if($("#add_user_tip").length > 0){
    mce_initiation('add_user_tip');
}

// if($("#add_user_update").length > 0){
//     mce_initiation('add_user_update');
// }

/**
 * Save Tip/Update
 * 
 * 
 */
 $('form#mm365_add_dashboard_tip').submit(function(e){
    e.preventDefault(); 
    var redirect_to = $('#after_success_redirect').val();
    var form        = $('form')[0];
    var formdata    = new FormData(form);
    formdata.append('action', 'add_dashboard_content');
    formdata.append('nonce',sadashboardAjax.nonce);
    if ( $(this).parsley().isValid() ) { 

        $.ajax({ 
            url : sadashboardAjax.ajax_url,
            data: formdata,
            type: 'POST',                   
            contentType: false,
            processData: false,
            beforeSend: function() { 
                $('html, body').animate({ scrollTop: 0 }, 'slow');
                Notiflix.Loading.hourglass('Adding dasshboard content..',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });
            },
            success : function( data ){

                if(data == 'success'){
                    Notiflix.Loading.remove(1923);
                    Notiflix.Notify.success('Content added successfully!',() => { window.location = redirect_to } );
                    setTimeout(() => {
                      window.location = redirect_to; 
                    }, 2000);
                  }
                else{
                    Notiflix.Loading.remove(1923);
                    $.confirm({
                      title:  'Unable to add content!',
                      content: "Please check the input values",
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

            }
        });
    }
  });   

//toggle visibility
$('.tip-visibility-toggle').on('click',function(e){
  e.preventDefault();
  var tip_post_id = $(this).attr('data-tippostid');
  //AJAX
  $.ajax({ 
    url : sadashboardAjax.ajax_url,
    data : {
        action:'toggle_post_visibility',
        post_id: tip_post_id,
        nonce: sadashboardAjax.nonce,
    },
    type: 'POST',      
    beforeSend: function() { 
      Notiflix.Notify.info('Changing visibility...');
    },            
    success : function( data ){
       
      if($.trim(data) == 'success'){
        window.location.reload();
      }else{
        Notiflix.Notify.failure('Unable to perform change');
      }

    }
  }); 


});

  //Delete post

  $('.delete-tip-post').on('click',function(e){
    e.preventDefault();
    var tip_post_id = $(this).attr('data-tippostid');
    //AJAX
    $.ajax({ 
      url : sadashboardAjax.ajax_url,
      data : {
          action:'delete_post',
          post_id: tip_post_id,
          nonce: sadashboardAjax.nonce,
      },
      type: 'POST',      
      beforeSend: function() { 
        Notiflix.Notify.info('Deleting post...');
      },            
      success : function( data ){
         
        if($.trim(data) == 'success'){
          Notiflix.Notify.success('Post deleted!');
          window.location.reload();
        }else{
          Notiflix.Notify.failure('Unable to perform change');
        }
  
      }
    }); 
  
  
  });


    //ENDS HERE
  });
})();