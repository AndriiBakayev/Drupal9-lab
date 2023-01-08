<?php

namespace Drupal\student_registration\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Form for confirm student deletion.
 */
class ConfirmDeleteForm extends ConfirmFormBase {

  /**
   * Student id.
   *
   * @var int
   */
  public $sId;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructor for database connect.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Asks container to supply database via Dependency Injection.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'student_registration.delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Do you want to delete student?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('student_registration.registrations');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText() {
    return t('Cancel');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $s_id = NULL) {
    $this->sId = $s_id;
    $query = $this->database->select('student_registration', 'sr')
      ->fields('sr', ['s_id', 'student_name', 'student_mail', 'student_rollno'])
      ->condition('s_id', $s_id)
      ->execute();
    $data = $query->fetch();
    // If not exists go to table.
    if (!$data) {
      (new RedirectResponse('/registrations'))->send();
      \Drupal::messenger()
        ->addMessage(t('Student with id = %s_id not found!',
          ['%s_id' => $this->sId]), 'error');
      exit(1);
    }
    $form['student_name'] = [
      '#type' => 'label',
      '#title' => t('Student Name: %name', ['%name' => $data->student_name]),
    ];
    $form['student_email'] = [
      '#type' => 'label',
      '#title' => t('Student Mail: %mail', ['%mail' => $data->student_mail]),
    ];
    $form['student_rollno'] = [
      '#type' => 'label',
      '#title' => t('Student RollNo: %rollno', ['%rollno' => $data->student_rollno]),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->database->delete('student_registration')
      ->condition('s_id', $this->sId, '=')
      ->execute();
    \Drupal::messenger()->addMessage(t("succesfully deleted"));
    $form_state->setRedirect('student_registration.registrations');
  }

}
