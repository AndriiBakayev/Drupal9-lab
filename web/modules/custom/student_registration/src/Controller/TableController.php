<?php

/**
 * @file
 * Display students info in table format
 * Functionality of this Controller is wired to Drupal in
 *   student_registration.routing.yml
 */

namespace Drupal\student_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TableController extends ControllerBase {

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

  public function display() {
    //create table header
    $header_table = [
      'rollno' => t('Rollno'),
      'name' => t('Name'),
      'viewlink' => t('View'),
      'editlink' => t('Edit'),
      'deletelink' => t('Delete'),
    ];

    //select data from db
    $query = $this->database->select('student_registration', 'sr')
      ->fields('sr', ['s_id', 'student_name', 'student_rollno']);
    $rows = [];
    $results = $query->execute()->fetchAll();
    foreach ($results as $row) {
      $view = Url::fromUserInput('/registration/' . $row->s_id);
      $delete = Url::fromUserInput('/registration/' . $row->s_id . '/delete');
      $edit = Url::fromUserInput('/registration/' . $row->s_id . '/edit');

      //print the data from table
      $rows[] = [
        'rollno' => $row->student_rollno,
        'name' => $row->student_name,
        Link::fromTextAndUrl(t('View'), $view),
        Link::fromTextAndUrl(t('Edit'), $edit),
        Link::fromTextAndUrl(t('Delete'), $delete),
      ];
    }

    //display data
    $form['table'] = [
      '#type' => 'table',
      '#header' => $header_table,
      '#rows' => $rows,
      '#empty' => t('No students found'),
    ];
    $form['#cache'] = [
      'max-age' => 0,
    ];

    return $form;
  }

}
