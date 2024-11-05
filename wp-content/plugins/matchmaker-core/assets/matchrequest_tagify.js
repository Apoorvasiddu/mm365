(function() {
    "use strict";
  jQuery(document).ready(function($) { 
/* Start Editing */


/*-------------------------------------------------------------------------------- */
    //tagify search form
/*-------------------------------------------------------------------------------- */ 
if($('[name="services_looking_for"]').length > 0){
    var matchrequestTagsInput = document.querySelector('[name="services_looking_for"]')
    var regex = new RegExp("^.{0,"+ mrTag.keyword_chars +"}$"); 
    var settings = {
        delimiters:",",
        maxTags: mrTag.keyword_count,
        pattern : regex,
        texts: {
            empty      : "Please add keyword(s)",
            exceed     : "Number of keywords exceeded",
            pattern    : "Charater limit exceeded",
            duplicate  : "Duplicate Keyword",
            notAllowed : "Invalid keyword"
        },
        originalInputValueFormat: valuesArr => valuesArr.map(item => item.value).join(', ')
    }
    var matchrequestTagsTagify = new Tagify(matchrequestTagsInput, settings);
    matchrequestTagsTagify.on('invalid', function(e){
        $.confirm({
            title:  e.detail['message'],
            content: 'Maximum of '+ mrTag.keyword_count +' keywords are allowed, each keyword cannot exceed more than '+ mrTag.keyword_chars + ' characters. Duplicate keywords are not allowed',
            type: 'red',
            typeAnimated: true,
            icon: 'far fa-times-circle',
            theme: 'modern',
            buttons: {
              close: {
                btnClass: 'btn btn-primary',
              }
            }
        });
    });
    }
    


/* End Editing */
});
})();
