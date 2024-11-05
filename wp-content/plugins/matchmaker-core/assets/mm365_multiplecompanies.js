(function() {
    "use strict";
  jQuery(document).ready(function($) { 
//-------------------//

//Notofication library
// Notiflix.Notify.init({
//   width: '300px',
//   position: 'right-top',
//   closeButton: false,
//   zindex: 9999999,
//   info: {
//     background: '#356ab3',
//   },
// });


function openFancybox() {
  setTimeout( function() {
    $('[data-src="#companySelectionBox"]').trigger('click'); 
  }, 1000);
}


/**
 * 
 * Check if user has a company selected
 * else show the selection popup
 */

var active_company = Cookies.get('active_company_id');
//Open select box
if(typeof active_company == 'undefined'){
  $.fancybox.open({
    src : '#companySelectionBox',
    type: 'inline',
    clickSlide : 'false',
    clickOutside : 'false',
    touch: false,
    smallBtn : false,
    buttons: [
      "zoom",
      //"share",
      "slideShow",
      //"fullScreen",
      //"download",
      "thumbs",
      //"close"
    ],
  });
}

/**
 * 
 * Switching company action
 * 
 */

$('.switch_company').on('click',function(e){
    e.preventDefault();

    var company_id = $(this).attr('data-companyid');
    var redirect = $(this).attr('data-redirect');
    //AJAX
    $.ajax({ 
      url : multiplecompaniesAjax.ajax_url,
      data : {
          action:'switch_company',
          company_id: company_id,
          nonce: multiplecompaniesAjax.nonce,
      },
      type: 'POST',      
      beforeSend: function() { 
        Notiflix.Notify.info('Changing selected company...');
      },            
      success : function( data ){
         
        if($.trim(data) == '1'){
          window.location = redirect;
        }else{
          Notiflix.Notify.failure('Unable to change company');
        }

      }
    }); 
  
  });





//----ENDS HERE-----//
});
  
})();