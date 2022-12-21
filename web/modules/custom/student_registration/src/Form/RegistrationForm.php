<?php

/**
 * @file
 * Contains \Drupal\student_registration\Form\RegistrationForm.
 */

namespace Drupal\student_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class RegistrationForm extends FormBase {

  /**
   * Gets formId.
   */
  public function getFormId() {
    return 'student_registration_form';
  }

  /**
   * Supply form fields.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['student_name'] = [
      '#type' => 'textfield',
      '#title' => t('Enter Name:'),
      '#required' => TRUE,
    ];
    $form['student_rollno'] = [
      '#type' => 'textfield',
      '#title' => t('Enter Enrollment Number:'),
      '#required' => TRUE,
    ];
    $form['student_mail'] = [
      '#type' => 'email',
      '#title' => t('Enter Email ID:'),
      '#required' => TRUE,
    ];
    $form['student_phone'] = [
      '#type' => 'tel',
      '#title' => t('Enter Contact Number'),
    ];
    $form['student_dob'] = [
      '#type' => 'date',
      '#title' => t('Enter DOB:'),
      '#required' => TRUE,
    ];
    $form['student_gender'] = [
      '#type' => 'select',
      '#title' => ('Select Gender:'),
      '#options' => [
        'Male' => t('Male'),
        'Female' => t('Female'),
        'Other' => t('Other'),
      ],
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Register'),
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
    if (strlen($form_state->getValue('student_rollno')) < 8) {
      $form_state->setErrorByName('student_rollno', $this->t('Please enter a valid Enrollment Number'));
    }
    if (strlen($form_state->getValue('student_phone')) < 10) {
      $form_state->setErrorByName('student_phone', $this->t('Please enter a valid Contact Number'));
    }
  }

  /**
   * Submits form into messenger.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::messenger()
      ->addMessage(t("Student Registration Done!! Registered Values are:"));
    foreach ($form_state->getValues() as $key => $value) {
      \Drupal::messenger()->addMessage($key . ': ' . $value);
    }
  }

}
