<?php

namespace Drupal\latestnode\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class for implementing config form for LatestNodeBlock.
 */
class LatestNodeConfigForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'latestnode.settings';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'latestnode.settings_form';
  }

  /**
   * {@inheritDoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManager $entityTypeManager) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $contentTypes = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
    $contentTypesList = [];
    foreach ($contentTypes as $contentType) {
      $contentTypesList[$contentType->id()] = $contentType->label();
    }
    $config = $this->config(static::SETTINGS);

    $form['typenode'] = [
      '#type' => 'select',
      '#options' => $contentTypesList,
      '#title' => $this->t('Type of nodes'),
      '#default_value' => $config->get('typenode'),
    ];

    $form['countnode'] = [
      '#type' => 'number',
      '#title' => $this->t('Count of nodes'),
      '#default_value' => $config->get('countnode'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->config(static::SETTINGS)
      ->set('typenode', $form_state->getValue('typenode'))
      ->set('countnode', $form_state->getValue('countnode'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
