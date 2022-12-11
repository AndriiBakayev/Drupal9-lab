<?php

/**
 * @file
 * Display student info
 * Functionality of this Controller is wired to Drupal in
 *   student_registration.routing.yml
 */

namespace Drupal\student_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ViewController extends ControllerBase {

  /**
   * Database connection
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  public function display($s_id) {
    $query = $this->database->select('student_registration', 'sr')
      ->fields('sr')->condition('s_id', $s_id, '=');
    $result = $query->execute()->fetch();
    if (!$result) {
      throw new NotFoundHttpException();
    }
    $rows = [
      ['label' => t('Name: '), 'student_name' => $result->student_name],
      [
        'label' => t('Enrollment Number: '),
        'student_rollno' => $result->student_rollno,
      ],
      ['label' => t('Email: '), 'student_mail' => $result->student_mail],
      [
        'label' => t('Contact Number: '),
        'student_phone' => $result->student_phone,
      ],
      [
        'label' => t('Date of Birth: '),
        'student_dob' => date('Y-m-d', strtotime($result->student_dob)),
      ],
      ['label' => t('Gender: '), 'student_gender' => $result->student_gender],
    ];
    if (isset($result->average_mark)) {
      $rows[] =
        [
          'label' => t('Average Mark: '),
          'student_average_mark' => $result->average_mark,
        ];
    }


    $form['table'] = [
      '#type' => 'table',
      '#rows' => $rows,
      '#empty' => t('No students found'),
    ];

    $form['#cache'] = [
      'max-age' => 0,
    ];

    return $form;
  }

}
