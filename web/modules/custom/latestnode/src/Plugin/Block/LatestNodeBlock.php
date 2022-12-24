<?php

namespace Drupal\latestnode\Plugin\Block;

use Drupal\Core\Block\BlockBase;

use Drupal\latestnode\Form\LatestNodesService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides a 'Hello' Block Plugin.
 *
 * @Block(
 *   id = "hello_block",
 *   admin_label = @Translation("Latestnode block"),
 *   category = @Translation("Latestnode"),
 * )
 */
class LatestNodeBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Stores the service.
   */
  protected LatestNodesService $latestNodesService;


  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Initializes plugin class with dependency injection.
   *
   * @param \Drupal\latestnode\Form\LatestNodesService $latestNodesService
   *   Service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param array $configuration
   *   Plugin's cofiguration.
   * @param int $plugin_id
   *   Plugin's ID.
   * @param string $plugin_definition
   *   Definition of plugin.
   */
  public function __construct(
    LatestNodesService $latestNodesService,
    ConfigFactoryInterface $configFactory,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->latestNodesService = $latestNodesService;
    $this->configFactory = $configFactory;
  }

  /**
   * Dependency injection to connect the service.
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $container->get('latestnode.latestNodesService'),
      $container->get('config.factory'),
      $configuration,
      $plugin_id,
      $plugin_definition

    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $latestnodes = $this->latestNodesService->teaserConfigEntityList();
    $nodesLinks = [];
    foreach ($latestnodes as $node) {
      $nodesLinks[] = [
        'url' => $node->toLink()->toString(),
      ];
    }
    return [
      '#theme' => 'latestnode_latestnodes_block',
      '#latestnodes' => $nodesLinks,
      '#cache' => [
        'tags' => ['node_list'],
      ],
    ];
  }

}
