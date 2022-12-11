<?php

/**
 * @file
 * Form for confirmation student delete
 * This form is wired to Drupal in registration_students.routing.yml
 */

namespace Drupal\student_registration\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ConfirmDeleteForm extends ConfirmFormBase {

  /**
   * Student id
   *
   * @var integer
   */
  public $s_id;

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

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'student_registration.delete_form';
  }

  public function getQuestion() {
    return t('Do you want to delete student?');
  }

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
    $this->s_id = $s_id;
    $query = $this->database->select('student_registration', 'sr')
      ->fields('sr', ['s_id', 'student_name', 'student_mail', 'student_rollno'])
      ->condition('s_id', $s_id)
      ->execute();
    $data = $query->fetch();
    if (!$data) {//If not exists go to table
      (new RedirectResponse('/registrations'))->send();
      \Drupal::messenger()
        ->addMessage(t('Student with id = %s_id not found!',
          ['%s_id' => $this->s_id]), 'error');
      exit(1);
    }
    $form['student_name'] = [
      '#type' => 'label',
      '#title' => t('Student Name: ' . $data->student_name),
    ];
    $form['student_email'] = [
      '#type' => 'label',
      '#title' => t('Student Mail: ' . $data->student_mail),
    ];
    $form['student_rollno'] = [
      '#type' => 'label',
      '#title' => t('Student RollNo: ' . $data->student_rollno),
    ];
    return parent::buildForm($form, $form_state);
  }


  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->database->delete('student_registration')
      ->condition('s_id', $this->s_id, '=')
      ->execute();
    \Drupal::messenger()->addMessage(t("succesfully deleted"));
    $form_state->setRedirect('/registrations');
  }

}
