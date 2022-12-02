// Adds behavior of custom scroll to top button
// Source of behavior button was taken in "drupal/scroll_top_button" module
(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.bakayevstheme_to_the_top = {
    attach: function (context, settings) {
      const button_text = "Наверх";
      const button_animation_speed = 200;
      const scroll_min_distance = 1000;
      const scroll_speed = 300;
      const $element = $('<a/>', {
        id: 'scrollTopButton-pill',
        href: '#',
        class: 'scroll-top-button'
      });
      $element.html(button_text);
      if ($('#' + 'scrollTopButton-pill').length === 0) {
        $element.appendTo('body');
      }

      const animationIn = 'fadeIn';
      const animationOut = 'fadeOut';
      const animationSpeed = button_animation_speed;
      let buttonVisible = false;

      $(window).scroll(function () {
        if ($(window).scrollTop() > scroll_min_distance) {
          if (!buttonVisible) {
            buttonVisible = true;
            $element[animationIn](animationSpeed);
          }
        }
        else {
          if (buttonVisible) {
            $element[animationOut](animationSpeed);
            buttonVisible = false;
          }
        }
      });

      // Scroll to top after click on button
      $element.click(function (event) {
        //event.preventDefault();
        $('html, body').animate({scrollTop: 0}, scroll_speed, 'linear');
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
