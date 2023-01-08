<?php

namespace Drupal\latestnode\Plugin\Block;

// Use Drupal\Core\Block\Annotation\Block;.
use Drupal\user\Entity\User;
use Drupal\Core\Block\BlockBase;

use Drupal\Core\Entity\EntityMalformedException;
use Drupal\latestnode\Form\LatestNodesService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Render\Renderer;

/**
 * Provides a 'Latestnode' Block Plugin.
 *
 * @Block(
 *   id = "latestnode_block_cache",
 *   admin_label = @Translation("Latestnode block cache"),
 *   category = @Translation("Latestnode cache"),
 * )
 */
class LatestNodeBlockCacheAPI extends BlockBase implements ContainerFactoryPluginInterface {

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
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Initializes plugin class with dependency injection.
   *
   * @param \Drupal\latestnode\Form\LatestNodesService $latestNodesService
   *   Service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param Drupal\Core\Render\Renderer $renderer
   *   Cache Renderer.
   * @param array $configuration
   *   Plugin's cofiguration.
   * @param string $plugin_id
   *   Plugin's ID.
   * @param mixed $plugin_definition
   *   Definition of plugin.
   */
  public function __construct(
    LatestNodesService $latestNodesService,
    ConfigFactoryInterface $configFactory,
    Renderer $renderer,
    array $configuration,
    string $plugin_id,
    mixed $plugin_definition
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->latestNodesService = $latestNodesService;
    $this->configFactory = $configFactory;
    $this->renderer = $renderer;
  }

  /**
   * Dependency injection to connect the service.
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
                       $plugin_id,
                       $plugin_definition
  ): LatestNodeBlockCacheAPI|static {
    return new static(
      $container->get('latestnode.latestNodesService'),
      $container->get('config.factory'),
      $container->get('renderer'),
      $configuration,
      $plugin_id,
      $plugin_definition

    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $latestnodes = $this->latestNodesService->teaserConfigEntityList();
    $latestnodesList = [];
    foreach ($latestnodes as $node) {
      try {
        $latestnodesList[] = [
          'url' => $node->toLink()->toString(),
        ];
      }
      catch (EntityMalformedException $e) {
        return ['#markup' => $this->t('Error getting nodes links')];
      }
    }
    $build = [
      '#cache' => [
        'tags' => ['node_list'],
        'max-age' => 60,
        'contexts' => ['user'],
      ],
    ];
    $latestnodesList[] = ['info' => $this->t("Cache info:")];
    $latestnodesList[] = ['info' => $this->t("Block rendered %date", ['%date' => date('H:i:s')])];
    // Write cache params without deps.
    $latestnodesList[] = ['info' => $this->t("Build's cache: %info", ['%info' => json_encode($build['#cache'])])];
    // Add dependencies.
    $config = \Drupal::config('latestnode.settings');
    $current_user = \Drupal::currentUser();
    $this->renderer->addCacheableDependency($build, $config);
    $this->renderer->addCacheableDependency($build, User::load($current_user->id()));
    // Write cache params with deps.
    $latestnodesList[] = ['info' => $this->t("Build's cache with deps: %info", ['%info' => json_encode($build['#cache'])])];

    $build['#theme'] = 'latestnode_latestnodes_block';
    $build['#latestnodes'] = $latestnodesList;

    return $build;
  }

}
