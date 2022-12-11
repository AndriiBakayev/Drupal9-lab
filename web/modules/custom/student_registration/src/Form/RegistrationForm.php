<?php

/**
 * @file
 * Contains \Drupal\student_registration\Form\RegistrationForm.
 */

namespace Drupal\student_registration\Form;

use Drupal\Component\Utility\EmailValidator;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RegistrationForm extends FormBase {

  /**
   * Database connection
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Email validator
   *
   * @var \Drupal\Component\Utility\EmailValidator
   */
  protected $emailValidator;

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('email.validator')
    );
  }


  public function __construct(Connection $database, EmailValidator $emailValidator) {
    $this->database = $database;
    $this->emailValidator = $emailValidator;
  }

  /**
   * Gets formId.
   */
  public function getFormId() {
    return 'student_registration_form';
  }

  /**
   * Supply form fields.
   */
  //  public function buildForm(array $form, FormStateInterface $form_state) {
  public function buildForm(array $form, FormStateInterface $form_state, $s_id = NULL) {

    if ($s_id) {
      $query = $this->database->select('student_registration', 'sr')
        ->fields('sr')
        ->condition('s_id', $s_id)
        ->execute();
      $data = $query->fetch();
    }
    $form['s_id'] = [
      '#type' => 'hidden',
      '#value' => ($s_id) ? $s_id : '',
    ];
    $form['student_name'] = [
      '#type' => 'textfield',
      '#title' => t('Enter Name:'),
      '#required' => TRUE,
      '#default_value' => ($s_id && isset($data->student_name)) ? $data->student_name : '',
    ];
    $form['student_rollno'] = [
      '#type' => 'textfield',
      '#title' => t('Enter Enrollment Number:'),
      '#required' => TRUE,
      '#default_value' => ($s_id && isset($data->student_rollno)) ? $data->student_rollno : '',
    ];
    $form['student_mail'] = [
      '#type' => 'email',
      '#title' => t('Enter Email ID:'),
      '#required' => TRUE,
      '#default_value' => ($s_id && isset($data->student_mail)) ? $data->student_mail : '',
    ];
    $form['student_phone'] = [
      '#type' => 'tel',
      '#title' => t('Enter Contact Number'),
      '#default_value' => ($s_id && isset($data->student_phone)) ? $data->student_phone : '',
    ];
    $form['student_dob'] = [
      '#type' => 'date',
      '#title' => t('Enter DOB:'),
      '#required' => TRUE,
      '#default_value' => ($s_id && isset($data->student_dob)) ? date('Y-m-d', strtotime($data->student_dob)) : '2000-01-01',
    ];
    $form['student_gender'] = [
      '#type' => 'select',
      '#title' => ('Select Gender:'),
      '#options' => [
        'Male' => t('Male'),
        'Female' => t('Female'),
        'Other' => t('Other'),
        '#default_value' => ($s_id && isset($data->student_gender)) ? $data->student_gender : '',
      ],
    ];
    $form['average_mark'] = [
      '#type' => 'number',
      'settings' => ['precision' => 5, 'scale' => 2],
      '#max' => 100,
      '#min' => 1,
      '#step' => 0.01,
      '#title' => t('Average mark:'),
      '#default_value' => ($s_id && isset($data->student_average_mark)) ? $data->student_average_mark : '',
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $s_id ? $this->t('Update') : $this->t('Register'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * Validates form.
   *
   * @param array $form
   *   Form to validate.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Formstate.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    if (strlen($form_state->getValue('student_name')) < 10) {
      $form_state->setErrorByName('student_name', $this->t('Student name too short (less 10 chars)'));
    }
    if (strlen($form_state->getValue('student_name')) > 100) {
      $form_state->setErrorByName('student_name', $this->t('Student name cannot exceed 100 chars'));
    }
    if (strlen($form_state->getValue('student_rollno')) < 8) {
      $form_state->setErrorByName('student_rollno', $this->t('Please enter a valid Enrollment Number (8 chars minimum)'));
    }
    if (strlen($form_state->getValue('student_rollno')) > 10) {
      $form_state->setErrorByName('student_rollno', $this->t('Please enter a valid Enrollment Number (10 chars maximum)'));
    }
    if (strlen($form_state->getValue('student_mail')) < 8) {
      $form_state->setErrorByName('student_mail', $this->t('Please enter a valid Email (8 chars minimum)'));
    }
    if (strlen($form_state->getValue('student_mail')) > 50) {
      $form_state->setErrorByName('student_mail', $this->t('Please enter a valid Email (50 chars maximum)'));
    }

    $studentMail = $form_state->getValue('student_mail');
    if ($studentMail == !$this->emailValidator->isValid($studentMail)) {
      $form_state->setErrorByName(
        'student_mail',
        t('The email address %mail is not valid.', ['%mail' => $studentMail]));
    }

    if (strlen($form_state->getValue('student_phone')) < 10) {
      $form_state->setErrorByName('student_phone', $this->t('Please enter a valid Contact Number (too_short)'));
    }
    if (strlen($form_state->getValue('student_phone')) > 13) {
      $form_state->setErrorByName('student_phone', $this->t('Please enter a valid Contact Number (too long)'));
    }
    if ($form_state->getValue('average_mark') !== NULL) {
      if (strlen($form_state->getValue('average_mark')) < 1) {
        $form_state->setErrorByName('average_mark', $this->t('Вы его не дооценили'));
      }
      if (strlen($form_state->getValue('average_mark')) > 100) {
        $form_state->setErrorByName('average_mark', $this->t('Не льстите ему!'));
      }
    }

  }

  /**
   * Submits form into messenger.
   */
  public
  function submitForm(array &$form, FormStateInterface $form_state) {
    $formData = $form_state->getValues();
    $s_id = $formData['s_id'];
    unset($formData['s_id']);
    unset($formData['submit']);
    unset($formData['op']);
    unset($formData['form_build_id']);
    unset($formData['form_token']);
    unset($formData['form_id']);
    if ($s_id) {
      $query = $this->database->update('student_registration')
        ->fields($formData)
        ->condition('s_id', $s_id)
        ->execute();
    }
    else {
      $query = $this->database->insert('student_registration')
        ->fields($formData);
      $s_id = $query->execute();
    }

    (new RedirectResponse('/registration/' . $s_id))->send();

    \Drupal::messenger()
      ->addMessage(t("Student Registration Done!! Registered Values are:"));
    foreach ($formData as $key => $value) {
      \Drupal::messenger()->addMessage($key . ': ' . $value);
    }
  }

}
