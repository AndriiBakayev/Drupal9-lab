// Adds behavior of custom scroll to top button
(function ($, Drupal, drupalSettings) {

    'use strict';

    Drupal.behaviors.bakayevstheme_to_the_top = {
        attach: function (context, settings) {
            var button_style = 'pill'
            var button_animation = 'fade';

            var button_text = "Наверх";
            var button_animation_speed = 200;
            var scroll_min_distance = 1000;
            var scroll_speed = 300;

            var element_name = 'scrollTopButton-pill';

            var $element = $('<a/>', {id: 'scrollTopButton-pill', href: '#', class:'scroll-top-button'});
            $element.html(button_text);
            if ($('#' + 'scrollTopButton-pill').length === 0) {
              $element.appendTo('body');
            }

            console.log("to_the_top " + $element + " ready");
            var animationIn = 'fadeIn';
            var animationOut = 'fadeOut';
            var animationSpeed = button_animation_speed;
            var buttonVisible = FALSE;

            $(window).scroll(function () {
                if ($(window).scrollTop() > scroll_min_distance) {
                    if (!buttonVisible) {
                        buttonVisible = TRUE;
                        $element[animationIn](animationSpeed);
                    }
                } else {
                    if (buttonVisible) {
                        $element[animationOut](animationSpeed);
                        buttonVisible = FALSE;
                    }
                }
            });

            // Scroll to top after click on button
            $element.click(function (event) {
              //event.preventDefault();

                $('html, body').animate({ scrollTop: 0}, scroll_speed, 'linear');
            });
        }
    };

  })(jQuery, Drupal, drupalSettings);
