/**
 * DIAMOND FRAME CMS Admin Login JS file
 * @version 13.07.2012 15:27:36
 */
/*global jQuery */
/*jslint browser: true, devel: true, nomen: true, plusplus: true, regexp: true, sloppy: true, vars: true, white: true */
typeof jQuery === 'function' && jQuery('#main') && jQuery(document).ready(function ($) {


    // login module vertical aligning
    var loginModule = $('#login-module'),
        alignLogin = function () {
            var mainHeight = $('#main').height(),
                loginHeight = loginModule.height();
            loginModule.css({
                margin: (mainHeight - loginHeight) * 0.3 - 18 + 'px 0 0 0',
                visibility: 'visible'
            });
        };
    loginModule.css('visibility', 'hidden').show();
    $(window).bind('resize', function () {
        alignLogin();
    });
    alignLogin();


    // login module initial inputs focusing
    var setInitialFocus = function (isLostPasswordForm) {
        var isLostPasswordForm = (isLostPasswordForm ? true : false),
            n = $('#nickname'),
            p = $('#passwd'),
            e = $('#email');
        if (isLostPasswordForm) {
            if (e.val() === '') {
                e.focus();
            } else if (!$.browser.mozilla && !$.browser.opera) { // v O & FF13 se dela outline & je to hnusne
                e.closest('form').find('input[type="submit"]').focus();
            }
            return;
        }
        if (n.val() === '') {
            n.focus();
        } else if (p.val() === '') {
            p.focus();
        } else if (!$.browser.mozilla && !$.browser.opera) {
            p.closest('form').find('input[type="submit"]').focus();
        }
    };
    setInitialFocus();


    // login module login/lost password sliding
    var forms = $('#login_slider form'),
        firstFormWidth = forms.first().outerWidth(),
        animationLength = 500;
    $("#moveLeft").click(function() {
        if ($.browser.msie && parseInt($.browser.version, 10) === 7) {
            forms.first().hide();
            forms.not(':first').show();
            setInitialFocus();
        } else {
            forms.animate({
                left: '-' + firstFormWidth + 'px'
            }, animationLength, function () {
                setInitialFocus(true);
            });
        }
    });
    $("#moveRight").click(function() {
        if ($.browser.msie && parseInt($.browser.version, 10) === 7) {
            forms.first().show();
            forms.not(':first').hide();
            setInitialFocus();
        } else {
            forms.animate({
                left: '0'
            }, animationLength, function () {
                setInitialFocus();
            });
        }
    });

    // initial position
    if ($('#alert').is('.alert')) {
        if ($.browser.msie && parseInt($.browser.version, 10) === 7) {
            forms.first().hide();
        } else {
            forms.css('left', '-' + firstFormWidth + 'px');
        }
        setInitialFocus(true);
    } else if ($.browser.msie && parseInt($.browser.version, 10) === 7) {
        forms.not(':first').hide();
    }


    // messages hiding
    $("#alert").click(function() {
        $(this).fadeOut();
    });

});

// end of file