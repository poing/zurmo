$(window).ready(function(){

    //main menu flyouts or mbmenu releacment
    $( '.nav > .parent' ).hover(
        function(){
            if ( $(this).find('ul').length > 0 ){
                $(this).find('ul').stop(true, true).delay(0).fadeIn(100);
            }
        },
        function(){
            if ( $(this).find('ul').length > 0 ){
                $(this).find('ul').stop(true, true).fadeOut(250);
            }
        }
    );

    //Main nav hover
     $('#MenuView a, #RecentlyViewedView a').hover(
        function(){
            $('span:first-child', this).stop(true, true).fadeTo( 500, 1 );
            $('span:last-child', this).stop(true, true).animate({ color : '#555', color: '#fff' }, 250);
        },
        function(){
            if ( $(this).parent().hasClass('active') === false ){
                $('span:first-child',this).stop(true, true).fadeTo( 500, 0 );
                $('span:last-child', this).stop(true, true).animate({ color : '#fff', color: '#555' }, 250);
            }
        }
    );


    function resizeWhiteArea(){

        /*Resizes the app to fill the browser's window case smaller'*/
        var viewportHeight = $(window).height();
        var wrapperDivHeight = $('body > div').outerHeight(true)
        var appChromeHeight = 0;
        var bufferHeight = 0;
        var recentlyViewedHeight = 0;

        //if login
        if ( $('#LoginPageView').length > 0 ) {
            appChromeHeight = 40 + $('#FooterView').outerHeight(true);
            if ( wrapperDivHeight < viewportHeight  ){
                bufferHeight = viewportHeight - appChromeHeight;
                $('#LoginView').height(  bufferHeight   );
            }
           //if admin area
        } else if ( $('.AdministrativeArea').length > 0 ) {
            appChromeHeight = 80 + $('#FooterView').outerHeight(true);
            if ( wrapperDivHeight < viewportHeight  ){
                bufferHeight = viewportHeight - appChromeHeight;
                $('.AppContainer').height(  bufferHeight   );
            }
        //rest of app
        } else {
            recentlyViewedHeight = $('#RecentlyViewedView').outerHeight(true);
            appChromeHeight = recentlyViewedHeight + $('#MenuView').outerHeight(true) + $('#HeaderView').outerHeight(true) + $('#FooterView').outerHeight(true);
            if ( wrapperDivHeight < viewportHeight  ){
                bufferHeight = viewportHeight - appChromeHeight;
                $('#RecentlyViewedView').height( $('#RecentlyViewedView').height() + bufferHeight   );
            }
        }
    }

    resizeWhiteArea();




    /*Label overlays input, address fields*/
    $(".overlay-label-field input").live('focus', function(){
        $(this).prev().fadeOut(100);
    });

    $(".overlay-label-field > input").live('blur', function(){
        if($(this).val() == "") {
            $(this).prev().fadeIn(250);
        }
    });
    $(".overlay-label-field > input").each( function(){
        if($(this).val() == "") {
            $('label', $(this)).fadeIn(250);
        }
    });

    /*Dropdowns - Dropkick - also see dropDownInteractions.js */
    $('html').click(function(e) {
        $.each($('select:not(.ignore-style)'), function(index, value) {
            $(value).dropkick('close');
        });
    });

    /*Checkboxes
     from: http://webdesign.maratz.com/lab/fancy-checkboxes-and-radio-buttons/jquery.html
     * */


    $('input:checkbox').each(function(){
        if ( $( this ).is(':checked') ) {
            $(this).parent().addClass('c_on');
        }
        $(this).bind( 'change', checkMyState );
    });


    function checkMyState(event){
        if (  $(event.target).is('input')  ){
            if (  $(event.target).is(':checked')  ){
                $(this).parent().addClass('c_on');
            } else {
                $(this).parent().removeClass('c_on');
            }
        }
    }


    function setupCheckboxes( $context ) {
        if ( $('input:checkbox', $context ).length ) {
            $('input:checkbox', $context ).each(function(){
                $(this).parent().removeClass('c_on');
            });
            $('label input:checked', $context ).each(function(){
                $(this).parent('label').addClass('c_on');
            });
        }

        $('label', $context[0] ).
            live( 'click', { $inputContext:$(this).content  },
                function( event ){
                    if ( $('input:checkbox', event.data.$inputContext ).length ) {
                        $('input:checkbox', event.data.$inputContext ).each(function(){
                            $(this).parent().removeClass('c_on');
                        });
                        $('label input:checked', event.data.$inputContext ).each(function(){
                            $(this).parent('label').addClass('c_on');
                        });
                    }
            });
    }


    //we're doing that because the multiselect widget isn't generated yet..
    window.setTimeout(
        function setCheckboxes(){
            setupCheckboxes( $('#search-form') );
            setupCheckboxes( $('#app-search') );
        },
    1000 );


});