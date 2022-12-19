<?php

namespace Drupal\latestnode\Form;

use Drupal\node\Entity\Node;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Controller\ControllerBase;

/**
 * Service returns 5 last created nodes.
 */
class LatestNodesService extends ControllerBase {

  /**
   * QueryInterface for query.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected QueryInterface $query;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->query = \Drupal::entityQuery('node');
  }

  /**
   * Returns last 5 created nodes in a teaser format.
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

}
