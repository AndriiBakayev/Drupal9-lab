<?php

namespace Drupal\ajax_form_submit\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Implementing a ajax form.
 */
class AjaxMultiStepDemo extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'example_multistep_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $step = $form_state->get('step') ?? 1;

    $step_titles = [
      1 => $this->t('Personal data'),
      2 => $this->t('Parameters'),
      3 => $this->t('Questions'),
    ];
    $steps = count($step_titles);

    $form['title'] = [
      '#type' => 'item',
      '#title' =>
      $this->t('Step :step of :steps: :title_form - :title_step', [
        ':step' => $step,
        ':steps' => $steps,
        ':title_form' => $this->t('MultistepForm'),
        ':title_step' => $step_titles[$step],
      ]),
    ];
    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div class="result_message"></div>',
    ];

    // Step 1 fields.
    $form['step1']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => $form_state->getValue('name'),
      '#access' => $step === 1,
    ];
    $form['step1']['surname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Surname'),
      '#default_value' => $form_state->getValue('surname'),
      '#access' => $step === 1,
    ];

    // Step 2 fields.
    $form['step2']['age'] = [
      '#type' => 'number',
      '#min' => 18,
      '#max' => 120,
      '#title' => $this->t('Age'),
      '#default_value' =>
      $form_state->getValue('age') ?? $form_state->get(['data', 'age']) ?? 18,
      '#access' => $step === 2,
    ];
    $form['step2']['gender'] = [
      '#type' => 'select',
      '#options' => [
        'male' => $this->t('Male'),
        'female' => $this->t('Female'),
        'other' => $this->t('Other'),
      ],
      '#title' => $this->t('Gender'),
      '#default_value' =>
      $form_state->getValue('gender') ?? $form_state->get(['data', 'gender']),
      '#access' => $step === 2,
    ];

    // Step 3 fields.
    $form['step3']['bio'] = [
      '#type' => 'textarea',
      '#rows' => 6,
      '#title' => $this->t('Bio'),
      '#default_value' => $form_state->getValue('bio')
      ?? $form_state->get(['data', 'bio']),
      '#access' => $step === 3,
    ];
    $form['step3']['hobby'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Hobby'),
      '#options' => [
        'chess' => $this->t('Chess'),
        'football' => $this->t('Football'),
        'politics' => $this->t('Politics'),
        'gardening' => $this->t('Gardening'),
      ],
      '#default_value' => $form_state->getValue('hobby')
      ?? $form_state->get(['data', 'hobby'])
      ?? [],
      '#access' => $step === 3,
    ];

    // Form wrapper for ajax callback.
    $form['#prefix'] = '<div id="myform-ajax-wrapper">';
    $form['#suffix'] = '</div>';

    // Submit buttons.
    $form['actions']['#type'] = 'actions';
    $form['actions']['prev'] = [
      '#type' => 'submit',
      '#value' => $this->t('Prev'),
      '#submit' => ['::prevSubmit'],
      '#access' => $step > 1,
      '#ajax' => [
        'callback' => '::myAjaxCallback',
        'wrapper' => 'myform-ajax-wrapper',
      ],
    ];
    $form['actions']['next'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#access' => $step < 3,
      '#submit' => ['::nextSubmit'],
      '#ajax' => [
        'callback' => '::myAjaxCallback',
        'wrapper' => 'myform-ajax-wrapper',
      ],
    ];
    $form['actions']['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#access' => $step === 3,
      '#ajax' => [
    // don't forget :: when calling a class method.
        'callback' => '::myAjaxCallback',
    // This element is updated with this AJAX callback.
        'wrapper' => 'myform-ajax-wrapper',
      ],
    ];

    $form['number_1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Number 1'),
    ];

    $form['number_2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Number 2'),
    ];

    $form['actions']['numbers'] = [
      '#type' => 'button',
      '#value' => $this->t('Submit_numbers'),
      '#ajax' => [
        'callback' => '::setMessage',
      ],
    ];
    $form['#tree'] = TRUE;
    return $form;
  }

  /**
   * Main AJAX callback return form.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form State.
   *
   * @return array
   *   Form as Render Array.
   */
  public function myAjaxCallback(array &$form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * Setting the message with sum of numbers  form.
   */
  public function setMessage(array $form, FormStateInterface $form_state) {

    $response = new AjaxResponse();
    $response->addCommand(
      new HtmlCommand(
        '.result_message',
        '<div class="my_top_message">'
          . t(
            'The results is @result',
            [
              '@result' => (
              $form_state->getValue('number_1')
              + $form_state->getValue('number_2')),
            ]
          )
          . '</div>'
      )
    );
    return $response;
  }

  /**
   * Validates form data.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   State of Form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Additional form validation put here.
  }

  /**
   * Submits form data.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $data = $form_state->get('data') ?? [];
    $data = array_merge($data, $values['step3']);

    foreach ($data as $key => $value) {
      $this->messenger()->addStatus($this->t(':key => :value', [
        ':key' => $key,
        ':value' => is_array($value) ? implode(', ', array_filter($value)) : $value,
      ]));
    }
  }

  /**
   * Extra callback on Next Submit button.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public function nextSubmit(array &$form, FormStateInterface $form_state) {

    $step = $form_state->get('step') ?? 1;

    $values = $form_state->getValues();
    $data = $form_state->get('data') ?? [];
    $form_state->set('data', array_merge($data, $values['step' . $step]));

    $form_state->set('step', ++$step);
    $form_state->setRebuild(TRUE);
  }

  /**
   * Extra callback for Prev Submit button.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form State.
   */
  public function prevSubmit(array &$form, FormStateInterface $form_state) {

    $step = $form_state->get('step') ?? 1;

    $values = $form_state->getUserInput();
    $data = $form_state->get('data') ?? [];
    $form_state->set('data', array_merge($data, $values['step' . $step]));

    $form_state->setValues($data);

    $form_state->set('step', --$step);
    $form_state->setRebuild(TRUE);
  }

}
