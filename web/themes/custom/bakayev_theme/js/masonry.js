(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.bakayevstheme_masonry = {
    attach: function (context, settings) {
      $('.grid', context).masonry({
        // options
        itemSelector: '.grid-item',
        columnWidth: 200
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
