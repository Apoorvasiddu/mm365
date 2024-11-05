(function() {
    "use strict";
  jQuery(document).ready(function($) { 
    //STARTS HERE

    if($("#help_desc_blocks").length > 0){
        // tinyMCE.init({
        //     mode : "none",
        //     statusbar: true,
        //     file_picker_callback_types: 'image',
        //     file_picker_callback: wpmediabrowser,
        //     placeholder: "Please add proper description about your company, make sure that you have added all the necessary keywords about your products and services",
        //     content_style:
        //     "body { background: #fff; color: #333; font-size: 13pt; }",
        //     setup: function (editor) {
        //         editor.on('change', function () {
        //             editor.save();
        //             $("#help_desc_blocks").parsley().reset();
        //         });
        //     },
        //     mobile: {
        //         theme: 'mobile',
        //     },
        //     branding: false,
        //     //plugins: "paste lists",              
        //     //toolbar: 'numlist bullist'
        //     menubar: false,
        //     plugins: [
        //     'advlist autolink lists link image charmap print preview anchor',
        //     'searchreplace visualblocks  fullscreen',
        //     'insertdatetime media table paste wordcount paste anchor textcolor mediaembed code'
        //     ],
        //     toolbar: 'image media code link anchor undo redo | formatselect | ' +
        //     'bold italic forecolor backcolor | alignleft aligncenter ' +
        //     'alignright alignjustify | bullist numlist outdent indent | ' +
        //     'removeformat',
        //     paste_as_text: true,
            
        // });
        // tinyMCE.execCommand('mceAddEditor', false, 'help_desc_blocks');
        tinymce.init({
            selector: '#help_desc_blocks',
            menubar: 'file edit view',
            plugins: 'anchor autolink charmap  emoticons  link lists  searchreplace table visualblocks wordcount',
            placeholder: "",
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            setup: function (editor) {
                editor.on('change', function () {
                    editor.save();
                    $("#help_desc_blocks").parsley().reset();
                });
            },
        });
    }

    // Get image from wp-uploader
    function wpmediabrowser(callback, value, meta)
    {
        var image = wp.media({ 
            title: 'Upload Image',
            multiple: false
        }).open().on('select', function(e){
            
            var uploaded_image = image.state().get('selection').first();
            // console.log(field_name);
            // jQuery("#" + field_name).val(uploaded_image["attributes"]["url"]);
            // var arr = field_name.split('-');
            // var field = arr[0];
            // var number = parseInt(field.split('_')[1]) + 1;
            // var descriptionid = field.split('_')[0] + "_" + number;
            // jQuery("#" + descriptionid).val(uploaded_image["attributes"]["description"]);
            callback(uploaded_image["attributes"]["url"], { title: uploaded_image.name });
    
        });
    }


/**
  * 
  * Update User
  * 
  */
 $('form#update_help_docs').submit(function(e){
    e.preventDefault(); 
    var redirect_to = $('#after_success_redirect').val();
    var form        = $('form')[0];
    var formdata    = new FormData(form);
    formdata.append('action', 'update_help_page');
    formdata.append('nonce',manageHelpPageAjax.nonce);
    if ( $(this).parsley().isValid() ) { 
  
        $.ajax({ 
            url : manageHelpPageAjax.ajax_url,
            data: formdata,
            type: 'POST',                   
            contentType: false,
            processData: false,
            beforeSend: function() { 
                $('html, body').animate({ scrollTop: 0 }, 'slow');
                Notiflix.Loading.hourglass('Updating help page contents...',{svgColor:'#356ab3', backgroundColor: 'rgba(255,255,255,0.8)', messageColor:'#356ab3' });
            },
            success : function( data ){
                Notiflix.Loading.remove(100);
                if($.trim(data) == 'success'){
                    Notiflix.Notify.success('Content updated');
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