<?php

namespace Drupal\l27_rest_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "site_bundle_post",
 *   label = @Translation("Custom rest resource"),
 *   serialization_class = "",
 *   uri_paths = {
 *     "canonical" = "/api/custom/get",
 *     "create" = "/api/custom"
 *   }
 * )
 */
class DateRestApiPost2 extends ResourceBase {


}
