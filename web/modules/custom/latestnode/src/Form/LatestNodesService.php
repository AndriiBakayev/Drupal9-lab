<?php

namespace Drupal\latestnode\Form;

use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service returns 5 last created nodes.
 */
class LatestNodesService extends ControllerBase {

  /**
   * Entity typeManager interface service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entity;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity, ConfigFactory $configFactory) {
    $this->entity = $entity;
    $this->configFactory = $configFactory;

  }

  /**
   * Dependency injection to connect the parameter service.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * Returns last 5 created nodes with images in a teaser viewmode.   *
   */
  public function teaserEntityList() {
    $query = \Drupal::entityQuery('node');
    $nodeslist = $query->accessCheck(FALSE)
      ->exists('field_blog_image')
      ->sort('created', 'DESC')
      ->range(0, 5)
      ->execute();
    $nodesLoaded = Node::loadMultiple($nodeslist);
    foreach ($nodesLoaded as $node) {
      $node->teaser = $this->entityTypeManager()
        ->getViewBuilder('node')
        ->view($node, 'teaser');
    }
    return $nodesLoaded;
  }

  /**
   * Returns last configed created nodes of configured type.
   *
   * Returns in a teaser viewmode.
   */
  public function teaserConfigEntityList() {
    $query = \Drupal::entityQuery('node');
    $config = $this->configFactory->get('latestnode.settings');
    $node_type = $config->get('typenode');
    $node_count = $config->get('countnode');
    $nodeslist = $query->accessCheck(FALSE)
      ->exists('field_blog_image')
      ->condition('type', $node_type ?? 'Blog')
      ->sort('created', 'DESC')
      ->range(0, $node_count ?? 5)
      ->execute();
    $nodesLoaded = Node::loadMultiple($nodeslist);
    foreach ($nodesLoaded as $node) {
      $node->teaser = $this->entityTypeManager()
        ->getViewBuilder('node')
        ->view($node, 'teaser');
    }
    return $nodesLoaded;
  }

}
