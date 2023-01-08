<?php

namespace Drupal\bakayev\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Session\AccountInterface;

/**
 * Supplies form editing name, email and age.
 */
class BakayevForm extends FormBase {

  /**
   * Gets id of form.
   */
  public function getFormId() {
    return 'bakayev_bakayevForm';
  }

  /**
   * Sets form areas.
   */
  public function buildForm(array $form, FormStateInterface $form_state, AccountInterface $user = NULL) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#size' => 30,
      '#maxlength' => 50,
      '#description' => $this->t('Person name.'),
    ];
    $form['email'] = [
      '#type' => 'email',
      '#default_value' => 'vasya@aol.com',
      '#title' => $this->t('Email'),
      '#placeholder' => $this->t('Enter E-mail here'),
      '#size' => 30,
      '#maxlength' => 50,
      '#description' => $this->t('Person email.'),
    ];
    $form['age'] = [
      '#type' => 'number',
      '#title' => $this->t('Age'),
      '#min' => 1,
      '#max' => 100,
      '#default_value' => 30,
      '#description' => $this->t('Person age.'),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit',
    ];
    return $form;
  }

  /**
   * Redirects on submit action.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $path = t(
      '/:person/:name/:email/:age', [
    // Avoid 'person' translation.
        ':person' => 'person',
        ':name' => $form["name"]["#value"],
        ':email' => $form["email"]["#value"],
        ':age' => $form["age"]["#value"],
      ]
    );

    $path_param = [];
    $url = Url::fromUserInput($path, ['query' => $path_param]);
    $form_state->setRedirectUrl($url);
  }

}
