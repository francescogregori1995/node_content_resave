<?php

namespace Drupal\node_content_resave\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node_content_resave\NodeContentResaveBatch;

class NodeContentResaveForm extends FormBase {

  public function getFormId() {
    return 'node_content_resave_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $content_types = \Drupal::service('entity_type.bundle.info')->getBundleInfo('node');
    $options = [];
    foreach ($content_types as $machine_name => $content_type) {
      $options[$machine_name] = $content_type['label'];
    }

    $form['content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content type'),
      '#options' => $options,
      '#required' => TRUE,
    ];

    $form['update_changed'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Update last changed date'),
      '#default_value' => 0,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Resave nodes'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $batch = NodeContentResaveBatch::getBatch($form_state->getValue('content_type'), $form_state->getValue('update_changed'));
    batch_set($batch);
  }

}