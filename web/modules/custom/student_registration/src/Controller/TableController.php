<?php

namespace Drupal\student_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Shows all records in table form with Viev/Edit/Delete links.
 */
class TableController extends ControllerBase {

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructor with database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Dependency Injection to get database.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Shows records.
   */
  public function display() {
    // Create table header.
    $header_table = [
      'rollno' => t('Rollno'),
      'name' => t('Name'),
      'viewlink' => t('View'),
      'editlink' => t('Edit'),
      'deletelink' => t('Delete'),
    ];

    // Select data from db.
    $query = $this->database->select('student_registration', 'sr')
      ->fields('sr', ['s_id', 'student_name', 'student_rollno']);
    $rows = [];
    $results = $query->execute()->fetchAll();
    foreach ($results as $row) {
      $view = Url::fromUserInput('/registration/' . $row->s_id);
      $delete = Url::fromUserInput('/registration/' . $row->s_id . '/delete');
      $edit = Url::fromUserInput('/registration/' . $row->s_id . '/edit');

      // Print the data from table.
      $rows[] = [
        'rollno' => $row->student_rollno,
        'name' => $row->student_name,
        'view' => Link::fromTextAndUrl(t('View'), $view),
        'edit' => Link::fromTextAndUrl(t('Edit'), $edit),
        'delete' => Link::fromTextAndUrl(t('Delete'), $delete),
      ];
    }

    // Display data.
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
