<?php
// $Id$
function lab_migration_settings_form($form, $form_state)
  {
    $form['emails'] = array(
        '#type' => 'textfield',
        '#title' => t('(Bcc) Notification emails'),
        '#description' => t('Specify emails id for Bcc option of mail system with comma separated'),
        '#size' => 50,
        '#maxlength' => 255,
        '#required' => TRUE,
        '#default_value' => variable_get('lab_migration_emails', '')
    );
    $form['cc_emails'] = array(
        '#type' => 'textfield',
        '#title' => t('(Cc) Notification emails'),
        '#description' => t('Specify emails id for Cc option of mail system with comma separated'),
        '#size' => 50,
        '#maxlength' => 255,
        '#required' => TRUE,
        '#default_value' => variable_get('lab_migration_cc_emails', '')
    );
    $form['from_email'] = array(
        '#type' => 'textfield',
        '#title' => t('Outgoing from email address'),
        '#description' => t('Email address to be display in the from field of all outgoing messages'),
        '#size' => 50,
        '#maxlength' => 255,
        '#required' => TRUE,
        '#default_value' => variable_get('lab_migration_from_email', '')
    );
    $form['extensions']['proposal_problem_statement'] = array(
        '#type' => 'textfield',
        '#title' => t('Allowed extensions for the problem statement in the Proposal form'),
        '#description' => t('A comma separated list WITHOUT SPACE of file extensions for the problem statement that are permitted to be uploaded on the server'),
        '#size' => 50,
        '#maxlength' => 255,
        '#required' => TRUE,
        '#default_value' => variable_get('lab_migration_problem_statement_extensions', '')
    );
    $form['extensions']['source'] = array(
        '#type' => 'textfield',
        '#title' => t('Allowed source file extensions'),
        '#description' => t('A comma separated list WITHOUT SPACE of source file extensions that are permitted to be uploaded on the server'),
        '#size' => 50,
        '#maxlength' => 255,
        '#required' => TRUE,
        '#default_value' => variable_get('lab_migration_source_extensions', '')
    );
    $form['extensions']['code_submission_result_pdf'] = array(
        '#type' => 'textfield',
        '#title' => t('Allowed file extensions for results in the form of PDF'),
        '#description' => t('A comma separated list WITHOUT SPACE of report file extensions that are permitted to be uploaded on the server'),
        '#size' => 50,
        '#maxlength' => 255,
        '#required' => TRUE,
        '#default_value' => variable_get('lab_migration_result_pdf_extensions', '')
    );
    $form['extensions']['result_zip'] = array(
        '#type' => 'textfield',
        '#title' => t('Allowed result file extensions in the zip format'),
        '#description' => t('A comma separated list WITHOUT SPACE of result file extensions that are permitted to be uploaded on the server'),
        '#size' => 50,
        '#maxlength' => 255,
        '#required' => TRUE,
        '#default_value' => variable_get('lab_migration_result_extensions', '')
    );
    $form['extensions']['pdf'] = array(
        '#type' => 'textfield',
        '#title' => t('Allowed pdf file extensions'),
        '#description' => t('A comma separated list WITHOUT SPACE of pdf file extensions that are permitted to be uploaded on the server'),
        '#size' => 50,
        '#maxlength' => 255,
        '#required' => TRUE,
        '#default_value' => variable_get('lab_migration_pdf_extensions', '')
    );
    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Submit')
    );
    return $form;
  }
function lab_migration_settings_form_validate($form, &$form_state)
  {
    return;
  }
function lab_migration_settings_form_submit($form, &$form_state)
  {
    variable_set('lab_migration_emails', $form_state['values']['emails']);
    variable_set('lab_migration_cc_emails', $form_state['values']['cc_emails']);
    variable_set('lab_migration_from_email', $form_state['values']['from_email']);
    variable_set('lab_migration_problem_statement_extensions', $form_state['values']['proposal_problem_statement']);
    variable_set('lab_migration_source_extensions', $form_state['values']['source']);
    variable_set('lab_migration_result_pdf_extensions', $form_state['values']['code_submission_result_pdf']);
    variable_set('lab_migration_result_extensions', $form_state['values']['result_zip']);
    variable_set('lab_migration_pdf_extensions', $form_state['values']['pdf']);
    drupal_set_message(t('Settings updated'), 'status');
  }
