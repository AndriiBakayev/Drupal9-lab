<?php

namespace Drupal\l25_field_api\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'snippets' field type.
 *
 * @FieldType(
 *   id = "rgb_item",
 *   label = @Translation("RGB color"),
 *   description = @Translation("Field stores RGB components in decimal."),
 *   default_widget = "rgb_item_default_widget",
 *   default_formatter = "rgb_item_default_formatter"
 * )
 */
class RGBItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field) {
    return [
      'columns' => [
        'red_component' => [
          'type' => 'int',
          'max' => 255,
          'min' => 0,
          'not null' => TRUE,
        ],
        'green_component' => [
          'type' => 'int',
          'max' => 255,
          'min' => 0,
          'not null' => TRUE,
        ],

        'blue_component' => [
          'type' => 'int',
          'max' => 255,
          'min' => 0,
          'not null' => TRUE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return !(
      $this->getValue()['red_component'] ||
      $this->getValue()['green_component'] ||
      $this->getValue()['blue_component']

    );
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['red_component'] = DataDefinition::create('integer')
      ->setLabel(t('Red (0-255)'))
      ->setRequired(TRUE);

    $properties['green_component'] = DataDefinition::create('integer')
      ->setLabel(t('Green (0-255)'))
      ->setRequired(TRUE);

    $properties['blue_component'] = DataDefinition::create('integer')
      ->setLabel(t('Blue (0-255)'))
      ->setRequired(TRUE);

    return $properties;
  }

}
