<?php

namespace Drupal\l30_queue_api\Form;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure l30_queue_api settings for this site.
 */
class BatchForm extends FormBase {

  /**
   * An array with available node types.
   *
   * @var array|mixed
   */
  protected array $nodeBundles;

  /**
   * Batch Builder.
   *
   * @var \Drupal\Core\Batch\BatchBuilder
   */
  protected BatchBuilder $batchBuilder;

  /**
   * Node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected NodeStorageInterface $nodeStorage;

  /**
   * BatchForm constructor.
   */
  public function __construct(EntityTypeBundleInfo $entity_type_bundle_info, NodeStorageInterface $node_storage) {
    $this->nodeBundles = $entity_type_bundle_info->getBundleInfo('node');
    // Move bundle name from 'label' key to value.
    array_walk($this->nodeBundles, function (&$a) {
      $a = $a['label'];
    });

    $this->nodeStorage = $node_storage;
    $this->batchBuilder = new BatchBuilder();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.bundle.info'),
      $container->get('entity_type.manager')->getStorage('node')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'l30_queue_api_batch';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['help'] = [
      '#markup' => $this->t('This form set entered publication date to all content of selected type.'),
    ];

    $form['node_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content type'),
      '#options' => $this->nodeBundles,
      '#required' => TRUE,
      '#default_value' => 'article',
    ];

    $form['date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('New date in titles'),
      '#required' => TRUE,
      '#default_value' => new DrupalDateTime('2023-01-01 00:00:00', 'Europe/Kiev'),
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['run'] = [
      '#type' => 'submit',
      '#value' => $this->t('Run batch'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $nodes = $this->getNodes($form_state->getValue(['node_type']));
    $date = $form_state->getValue('date');

    $this->batchBuilder
      ->setTitle($this->t('Processing'))
      ->setInitMessage($this->t('Initializing.'))
      ->setProgressMessage($this->t('Completed @current of @total.'))
      ->setErrorMessage($this->t('An error has occurred.'));
    $this->batchBuilder->setFile(\Drupal::service('extension.path.resolver')
      ->getPath('module', 'l30_queue_api') . '/src/Form/BatchForm.php');
    // $this->batchBuilder->setFile('l30_queue_api/src/Form/BatchForm.php');
    $this->batchBuilder->addOperation([$this, 'processItems'], [$nodes, $date]);
    $this->batchBuilder->setFinishCallback([$this, 'finished']);

    batch_set($this->batchBuilder->toArray());
  }

  /**
   * Processor for batch operations.
   */
  public function processItems($items, DrupalDateTime $date, array &$context) {
    // Elements per operation.
    $limit = 2;

    // Set default progress values.
    if (empty($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = count($items);
    }

    // Save items to array which will be changed during processing.
    if (empty($context['sandbox']['items'])) {
      $context['sandbox']['items'] = $items;
    }

    $counter = 0;
    if (!empty($context['sandbox']['items'])) {
      // Remove already processed items.
      if ($context['sandbox']['progress'] != 0) {
        array_splice($context['sandbox']['items'], 0, $limit);
      }

      foreach ($context['sandbox']['items'] as $item) {
        if ($counter != $limit) {
          $this->processItem($item, $date);

          $counter++;
          $context['sandbox']['progress']++;

          $context['message'] = $this->t('Processing node :progress of :count', [
            ':progress' => $context['sandbox']['progress'],
            ':count' => $context['sandbox']['max'],
          ]);

          // Increment total processed item values. Will be used in finished
          // callback.
          $context['results']['processed'] = $context['sandbox']['progress'];
        }
      }
    }

    // If not finished all tasks, we count percentage of process. 1 = 100%.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * Process single item.
   *
   * @param int|string $nid
   *   An id of Node.
   * @param \Drupal\Core\Datetime\DrupalDateTime $date
   *   An object with new published date.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function processItem($nid, DrupalDateTime $date) {
    /** @var \Drupal\node\NodeInterface $node */
    try {
      $node = $this->nodeStorage->load($nid);
      $node->setTitle(
        preg_replace(
          '(\d{2}\.\d{2}\.\d{4} \d{2}:\d{2})',
          $date->format('d.m.Y H:i'),
          // For init uncomment:
          // $node->getTitle() . ' ' . $date->format('d.m.Y H:m'),.
          $node->getTitle(),
          1
        )
      );
      $node->save();
    }
    catch (\Exception $e) {
      $this->batchBuilder
        ->setErrorMessage($this->t('Something went wrong during entity update.'));
    }

  }

  /**
   * Finished callback for batch.
   */
  public function finished($success, $results, $operations) {
    $message = $this->t('Number of nodes affected by batch: @count', [
      '@count' => $results['processed'],
    ]);

    $this->messenger()
      ->addStatus($message);
  }

  /**
   * Load all nids for specific type.
   *
   * @return array
   *   An array with nids.
   */
  public function getNodes($type) {
    return $this->nodeStorage->getQuery()
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('type', $type)
      ->execute();
  }

}
