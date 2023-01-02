<?php

namespace Drupal\l25_field_api\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'three_color_default' widget.
 *
 * @FieldWidget(
 *   id = "rgb_item_default_widget",
 *   label = @Translation("RGB colour default"),
 *   field_types = {
 *     "rgb_item"
 *   }
 * )
 */
class RGBItemDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['red_component'] = [
      '#title' => $this->t('Red'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->red_component ?? NULL,
      '#element_validate' => [
        [static::class, 'validateColor'],
      ],
    ];
    $element['green_component'] = [
      '#title' => $this->t('Green'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->green_component ?? NULL,
      '#element_validate' => [
        [static::class, 'validateColor'],
      ],
    ];
    $element['blue_component'] = [
      '#title' => $this->t('Blue'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->blue_component ?? NULL,
      '#element_validate' => [
        [static::class, 'validateColor'],
      ],
    ];

    return $element;
  }

  /**
   * Validates if value is integer 0<X<255.
   */
  public static function validateColor($element, FormStateInterface $form_state) {
    if ($element['#value'] == -1) {
      $form_state->setValueForElement($element, '');
      return;
    }
    if (!is_numeric($element['#value'])) {
      $form_state->setError($element, t('Enter valid number'));
      return;
    }
    if (intval($element['#value']) < 0) {
      $form_state->setError($element, t('Enter number > 0'));
    }
    if (intval($element['#value']) > 255) {
      $form_state->setError($element, t('Enter number < 255'));
    }
    $form_state->setValueForElement($element, intval($element['#value']));
  }

}
