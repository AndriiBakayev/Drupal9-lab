<?php

namespace Drupal\l27_rest_api\Plugin\rest\resource;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;

// Use Drupal\Core\Entity\EntityInterface;
// use Drupal\Core\Url;.
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

// Use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
// use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;.
use Drupal\node\Entity\Node;

// Use Drupal\paragraphs\Entity\Paragraph;.
use Drupal\rest\ModifiedResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Provides REST API for Content Based on URL.
 *
 * @RestResource(
 *   id = "data_rest_resource",
 *   label = @Translation("Data Rest API"),
 *   serialization_class = "",
 *   uri_paths = {
 *     "canonical" = "/api/get",
 *     "create" = "/api/post"
 *   }
 * )
 */
class DataRestApi extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $currentUser;

  /**
   * Mail manager service.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * Site configs.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Request stack of /Drupal::request.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array which contains the information about the plugin
   *   instance  for inherit constructor.
   * @param string $plugin_id
   *   The module_id for the plugin instance for inherit constructor.
   * @param mixed $plugin_definition
   *   The plugin implementation definition for inherit constructor.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   A currently logged user instance.
   * @param \Drupal\Core\Mail\MailManagerInterface $mailManager
   *   A mail manager to compose email.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   A Config Factory for get a config.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   An Entity_type_manager for manipulate entities.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   a Request stack for get a request url.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    mixed $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $currentUser,
    MailManagerInterface $mailManager,
    ConfigFactoryInterface $configFactory,
    EntityTypeManagerInterface $entity_type_manager,
    RequestStack $request_stack
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $currentUser;
    $this->mailManager = $mailManager;
    $this->config = $configFactory->get('system.site');
    $this->entityTypeManager = $entity_type_manager;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
                       $plugin_id,
    mixed $plugin_definition
  ): ResourceBase|DataRestApi|ContainerFactoryPluginInterface|static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('sample_rest_resource'),
      $container->get('current_user'),
      $container->get('plugin.manager.mail'),
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('request_stack')
    );
  }

  /**
   * Responds to entity GET requests.
   *
   * Returns a node where the field "field_unique_url" set to "/testing"
   * (tutorial 1). if parameter ?url=/testing set. If parameter not set returns
   * a list of taxonomy terms "tags" (tutorial 2).
   *
   * Link to tutorial 1:
   * http://www.learnwebtech.in/create-custom-rest-api-in-drupal-9/
   * For test purposes I added a field unique_url (machine_name
   * field_unique_url) to content type Article and made test article with it's
   * value "/testing" to verify this get go
   * GET: https://drupal9.ddev.site/api/getrest1?url=/testing.
   *
   * Link to tutorial 2:
   * https://www.valuebound.com/resources/blog/creating-custom-restful-web-service-drupal-9
   *
   * @return \Drupal\rest\ResourceResponse
   *   Returning rest resource.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get() {
    // Resource 1: if URL not found.
    if ($this->requestStack->getCurrentRequest()->query->has('url')) {
      $url = $this->requestStack->getCurrentRequest()->query->get('url');
      if (!empty($url)) {
        $query = $this->entityTypeManager->getStorage('node')->getQuery()
          ->condition('field_unique_url', $url);
        $nodes = $query->execute();
        $node_id = array_values($nodes);

        if (!empty($node_id)) {
          $data = Node::load($node_id[0]);
          return new ModifiedResourceResponse($data);
        }
      }
    }
    // Resource 2: if URL not found
    // Use currently logged user after passing authentication and validating
    // the access of term list.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $vid = 'tags';
    $terms = $this->entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree($vid);
    foreach ($terms as $term) {
      $term_result[] = [
        'id' => $term->tid,
        'name' => $term->name,
      ];
    }

    $response = new ResourceResponse($term_result);
    $response->addCacheableDependency($term_result);
    return $response;
  }

  /**
   * Emails if tag Email set or makes new article if title and body set.
   *
   * @param string $data
   *   Post data in JSON format.
   */
  public function post($data) {

    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    // {
    // "email": {
    // "value": "abc@drupaljournal.com"
    // },
    // "message": {
    // "value": "Test email custom body"
    // },
    // "subject": {
    // "value": "Test Email"
    // },
    // "lang": {
    // "value": "en"
    // }
    // }
    if (!empty($data['email'])) {
      $response_status['status'] = FALSE;
      $site_email = $this->config->get('mail');
      $module = 'custom_rest';
      $key = 'notice';
      $to = $site_email;
      $params['message'] = $data['message']['value'];
      $params['title'] = $data['subject']['value'];
      $params['from'] = $data['email']['value'];
      $langcode = $data['lang']['value'];
      $send = TRUE;
      $result = $this->mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
      $response_status['status'] = 'Sending message '
        . ($result['result'] ? " Sent" : "Not sent ")
        . ' from ' . $params['from']
        . ' to ' . $to
        . ' title ' . $params['title']
        . ' body ' . $params['message'];
      $response = new ResourceResponse($response_status);
      return $response;
    }

    // {
    // "title": {
    // "value": "REST_generated_Article2"
    // },
    // "body": {
    // "value": "REST_generated_Article body"
    // }
    // }
    if (!empty($data['title']) && !empty($data['body'])) {
      try {
        $data['type'] = 'article';
        $article = $this->entityTypeManager
          ->getStorage('node')
          ->create($data);
        $article->save();
        return new ResourceResponse($article);
      }
      catch (\Exception $e) {
        return new ResourceResponse('Something went wrong during entity creation. Check your data.', 400);
      }
    }
    else {
      return new ResourceResponse('Title not set', 400);
    }
  }

  /**
   * Supports PATCH method.
   */
  public function patch($data) {

    return new ResourceResponse('Patch not fully realised. $data=' . print_r($data,true), 400);

  }

  /**
   * Supports DELETE of entity method.
   */
  public function delete(EntityInterface $entity) {
    try {
      $entity->delete();
      $this->logger->notice('Deleted entity %type with ID %id.', [
        '%type' => $entity->getEntityTypeId(),
        '%id' => $entity->id(),
      ]);

      // DELETE responses have an empty body.
      return new ModifiedResourceResponse(NULL, 204);
    }
    catch (EntityStorageException $e) {
      throw new HttpException(500, 'Internal Server Error', $e);
    }
  }

}
