<?php

namespace Drupal\l24_event_dispatcher;

use Drupal\Core\Cache\CacheableRedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Redirects paths containing successive slashes to those with single slashes.
 */
class Param1TestEventSubscriber implements EventSubscriberInterface {

  /**
   * Redirects to page with set option "?param1=test".
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The RequestEvent to process.
   */
  public function addParam1Test(RequestEvent $event) {
    $request = $event->getRequest();
    // Get the requested path minus the base path.
    $path = $request->getPathInfo();
    if (str_contains($request->getRequestUri(), 'param1=test')) {
      return;
    }
    $url = Url::fromUri($request->getUriForPath($path));
    $url->setOption('query', ['param1' => 'test']);
    $event->setResponse(new CacheableRedirectResponse($url->toUriString()));
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['addParam1Test', 10000];
    return $events;
  }

}
