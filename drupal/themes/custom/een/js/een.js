jQuery(function () {
    var $ = jQuery;

    var buttons = $("label input[type='radio'], label input[type='checkbox']");
    new GOVUK.SelectionButtons(buttons);

    /* Basic toggle for form helper text */
    $(document).on('click', '.field-prefix', function () {
        var desc = $(this).next();
        $(desc).toggle();
    });
     
    $('textarea').focus(function() {
        $(this).closest('.js-form-type-textarea').find('div[id$="--description"]').toggleClass('show');
    });

    $('textarea').focusout(function() {
        $(this).closest('.js-form-type-textarea').find('div[id$="--description"]').toggleClass('show');
    });    
    
    $( ".what-value" ).click(function() {
        $( ".een-value" ).slideToggle("slow");
    });
  
    
    
    
    /*
     * 
     * accessibility
     */
    
    
    /*
     * In some instances get a link to behave like button on spacebar press
     */
    $("a.js-spb-click").on("keydown", function(e) {
        
        if (e.which == 32) {            
            $(this)[0].click();
            e.preventDefault();
        }
    });
    
    setTimeout(function() {
        
        if($('.error-summary-list').length){
            $('.error-summary-list').find('li').each(function(){
                var text = $(this).html();
                $(this).html(text+'&nbsp;');
            });
        }
        
        if($('.continue-with-application-status').length){
            var content = $('.continue-with-application-status').html();
            $('.continue-with-application-status').html(content+'&nbsp;');
            $('.js-continue-focus').focus();
        }
    }, 1000);
    
    
    
    

});

