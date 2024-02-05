<?php

namespace Drupal\example_two_step_form_dialog\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\UpdateBuildIdCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class ExampleTwoStepForm extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'example_two_step_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $step = (int)$form_state->get('step');
    if (!$step) {
      $step = 1;
      $form_state->set('step', $step);
      $form_state->set('steps_values', []);
    }

    return $this->{'buildStep' . $step}($form, $form_state);
  }

  /**
   * Build step 1.
   */
  public function buildStep1(array $form, FormStateInterface $form_state): array {
    $form['first_name'] = [
      '#type' => 'textfield',
      '#title' => 'First name',
    ];

    $form['next'] = [
      '#type' => 'submit',
      '#value' => 'Next',
      '#name' => 'next',
      '#submit' => ['::nextButtonSubmit'],
      '#ajax' => ['callback' => '::nextButtonAjax'],
    ];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**
   * "Next" button submit callback.
   */
  public function nextButtonSubmit(array $form, FormStateInterface $form_state): void {
    $form_state->set('step', 2);

    // Store current values to all-steps values
    $steps_values = $form_state->get('steps_values') ?? [];
    $steps_values = NestedArray::mergeDeep($steps_values, $form_state->cleanValues()->getValues());
    $form_state->set('steps_values', $steps_values);

    // Copy all-steps values to current values
    $form_state->setValues($steps_values);

    // Disable form reload (redirect)
    $form_state->setRebuild();
  }

  /**
   * "Next" button ajax callback.
   */
  public function nextButtonAjax(array $form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    $response->addCommand(new UpdateBuildIdCommand($form['#build_id'], $form['#build_id_old']));
    $response->addCommand(new OpenModalDialogCommand('Step 2', $form));
    return $response;
  }

  /**
   * Build step 2.
   */
  public function buildStep2(array $form, FormStateInterface $form_state): array {
    $form['last_name'] = [
      '#type' => 'textfield',
      '#title' => 'Last name',
    ];

    $form['finish'] = [
      '#type' => 'submit',
      '#value' => 'Submit',
      '#name' => 'finish',
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $steps_values = $form_state->get('steps_values') ?? [];
    $values = NestedArray::mergeDeep($steps_values, $form_state->cleanValues()->getValues());
    dsm($values);
  }

}
