<?php

namespace Drupal\latestnode\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\latestnode\Form\LatestNodesService;

/**
 * Controller to output last five nodes via service.
 */
class LatestNodesControllerService extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Stores the service.
   */
  protected LatestNodesService $latestNodesService;

  /**
   * Initializes object with Dependency injection.
   */
  public function __construct(LatestNodesService $latestNodesService) {
    $this->latestNodesService = $latestNodesService;
  }

  /**
   * Dependency injection to connect the service.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('latestnode.latestNodesService')
    );
  }

  /**
   * Connects template and outputs nodes in teaser format.
   */
  public function nodesListService() {
    $latestnodes = $this->latestNodesService->teaserEntityList();
    return [
      '#theme' => 'latestnode_latestnodes',
      '#latestnodes' => $latestnodes,
      '#cache' => [
        'tags' => ['node_list'],
      ],
    ];
  }

}
