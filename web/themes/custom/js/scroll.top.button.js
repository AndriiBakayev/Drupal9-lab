// Adds behavior of custom scroll to top button
// Source of behavior button was taken in "drupal/scroll_top_button" module
(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.bakayevstheme_to_the_top = {
    attach: function () {
      var button_text = "Наверх";
      var button_animation_speed = 200;
      var scroll_min_distance = 1000;
      var scroll_speed = 300;

      var $element = $('<a/>', {
        id: 'scrollTopButton-pill',
        href: '#',
        class: 'scroll-top-button'
      });
      $element.html(button_text);
      if ($('#' + 'scrollTopButton-pill').length === 0) {
        $element.appendTo('body');
      }

      var animationIn = 'fadeIn';
      var animationOut = 'fadeOut';
      var animationSpeed = button_animation_speed;
      var buttonVisible = false;

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
      $element.click(function () {
        //event.preventDefault();
        $('html, body').animate({scrollTop: 0}, scroll_speed, 'linear');
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
