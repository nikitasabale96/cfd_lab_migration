<?php

/**
 * @file
 * Contains \Drupal\lab_migration\Form\LabMigrationSettingsForm.
 */

namespace Drupal\lab_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class LabMigrationSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lab_migration_settings_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $form['emails'] = [
      '#type' => 'textfield',
      '#title' => t('(Bcc) Notification emails'),
      '#description' => t('Specify emails id for Bcc option of mail system with comma separated'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => variable_get('lab_migration_emails', ''),
    ];
    $form['cc_emails'] = [
      '#type' => 'textfield',
      '#title' => t('(Cc) Notification emails'),
      '#description' => t('Specify emails id for Cc option of mail system with comma separated'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => variable_get('lab_migration_cc_emails', ''),
    ];
    $form['from_email'] = [
      '#type' => 'textfield',
      '#title' => t('Outgoing from email address'),
      '#description' => t('Email address to be display in the from field of all outgoing messages'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => variable_get('lab_migration_from_email', ''),
    ];
    $form['extensions']['proposal_problem_statement'] = [
      '#type' => 'textfield',
      '#title' => t('Allowed extensions for the problem statement in the Proposal form'),
      '#description' => t('A comma separated list WITHOUT SPACE of file extensions for the problem statement that are permitted to be uploaded on the server'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => variable_get('lab_migration_problem_statement_extensions', ''),
    ];
    $form['extensions']['source'] = [
      '#type' => 'textfield',
      '#title' => t('Allowed source file extensions'),
      '#description' => t('A comma separated list WITHOUT SPACE of source file extensions that are permitted to be uploaded on the server'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => variable_get('lab_migration_source_extensions', ''),
    ];
    $form['extensions']['code_submission_result_pdf'] = [
      '#type' => 'textfield',
      '#title' => t('Allowed file extensions for results in the form of PDF'),
      '#description' => t('A comma separated list WITHOUT SPACE of report file extensions that are permitted to be uploaded on the server'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => variable_get('lab_migration_result_pdf_extensions', ''),
    ];
    $form['extensions']['result_zip'] = [
      '#type' => 'textfield',
      '#title' => t('Allowed result file extensions in the zip format'),
      '#description' => t('A comma separated list WITHOUT SPACE of result file extensions that are permitted to be uploaded on the server'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => variable_get('lab_migration_result_extensions', ''),
    ];
    $form['extensions']['pdf'] = [
      '#type' => 'textfield',
      '#title' => t('Allowed pdf file extensions'),
      '#description' => t('A comma separated list WITHOUT SPACE of pdf file extensions that are permitted to be uploaded on the server'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => variable_get('lab_migration_pdf_extensions', ''),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];
    return $form;
  }

  public function validateForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    return;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    variable_set('lab_migration_emails', $form_state->getValue(['emails']));
    variable_set('lab_migration_cc_emails', $form_state->getValue(['cc_emails']));
    variable_set('lab_migration_from_email', $form_state->getValue(['from_email']));
    variable_set('lab_migration_problem_statement_extensions', $form_state->getValue(['proposal_problem_statement']));
    variable_set('lab_migration_source_extensions', $form_state->getValue(['source']));
    variable_set('lab_migration_result_pdf_extensions', $form_state->getValue(['code_submission_result_pdf']));
    variable_set('lab_migration_result_extensions', $form_state->getValue(['result_zip']));
    variable_set('lab_migration_pdf_extensions', $form_state->getValue(['pdf']));
    \Drupal::messenger()->addmessage(t('Settings updated'), 'status');
  }

}
?>
