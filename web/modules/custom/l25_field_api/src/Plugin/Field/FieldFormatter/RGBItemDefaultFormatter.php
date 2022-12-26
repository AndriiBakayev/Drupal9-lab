<?php

namespace Drupal\l25_field_api\Plugin\Field\FieldFormatter;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'snippets_default' formatter.
 *
 * @FieldFormatter(
 *   id = "rgb_item_default_formatter",
 *   label = @Translation("Snippets default"),
 *   field_types = {
 *     "rgb_item"
 *   }
 * )
 */
class RGBItemDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $rgb = 'rgb('
        . ((int) $item->getValue()['red_component']) . ','
        . ((int) $item->getValue()['green_component']) . ','
        . ((int) $item->getValue()['blue_component']) . ')';

      $elements[$delta] = [
        '#markup' => new FormattableMarkup(
          '<span style="width: 200px; background-color: @rgb;">
            &nbsp;&nbsp;&nbsp;@rgb&nbsp;&nbsp;&nbsp;
          </span>',
          [
            '@rgb' => $rgb,
          ]),
      ];
    }

    return $elements;
  }

}
