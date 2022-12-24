<?php

namespace Drupal\latestnode\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

/**
 * Prints 5 latest nodes.
 */
class LatestNodesController extends ControllerBase {

  /**
   * Gets and outputs 5 latest nodes.
   */
  public function nodesList() {
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
    return [
      '#theme' => 'latestnode_latestnodes',
      '#latestnodes' => $nodesLoaded,
      '#cache' => [
        'tags' => ['node_list'],
      ],
    ];
  }

}
