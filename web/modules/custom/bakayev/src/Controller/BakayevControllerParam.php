<?php

namespace Drupal\bakayev\Controller;

/**
 * BakayevTestController Controller.
 *
 * @file BakayevTestController Controller
 *
 * class generates response page with record given in URL
 * /people/{name}/{email}/{age}
 */
use Drupal\Core\Controller\ControllerBase;

/**
 * Class controller used to supply response with params on particular URL.
 */
class BakayevControllerParam extends ControllerBase {

  /**
   * Default function formats and generates response.
   */
  public function content(string $name, string $email, string $age) {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('
        <ul>
          <li>Name: :name</li>
          <li>Email: :email</li>
          <li>Age: :age</li>
        </ul>
        Added to database, but not sure :)
        <input type="button" onclick="location.href=\'/person/edit\';" value="Edit" />
        ',
        [':name' => $name, ':email' => $email, ':age' => $age]
      ),
    ];
  }

}
