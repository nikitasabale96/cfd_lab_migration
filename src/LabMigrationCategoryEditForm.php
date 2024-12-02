<?php
namespace Drupal\lab_migration;

class LabMigrationCategoryEditForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lab_migration_category_edit_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    /* get current proposal */
    $proposal_id = (int) arg(4);
    //$proposal_q = \Drupal::database()->query("SELECT * FROM {lab_migration_proposal} WHERE id = %d", $proposal_id);
    $query = \Drupal::database()->select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q) {
      if ($proposal_data = $proposal_q->fetchObject()) {
        /* everything ok */
      }
      else {
        \Drupal::messenger()->addmessage(t('Invalid proposal selected. Please try again.'), 'error');
        drupal_goto('lab-migration/manage-proposal');
        return;
      }
    }
    else {
      \Drupal::messenger()->addmessage(t('Invalid proposal selected. Please try again.'), 'error');
      drupal_goto('lab-migration/manage-proposal');
      return;
    }
    $form['name'] = [
      '#type' => 'item',
      '#markup' => l($proposal_data->name_title . ' ' . $proposal_data->name, 'user/' . $proposal_data->uid),
      '#title' => t('Name'),
    ];
    $form['email_id'] = [
      '#type' => 'item',
      '#markup' => user_load($proposal_data->uid)->mail,
      '#title' => t('Email'),
    ];
    $form['contact_ph'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->contact_ph,
      '#title' => t('Contact No.'),
    ];
    $form['department'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->department,
      '#title' => t('Department/Branch'),
    ];
    $form['university'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->university,
      '#title' => t('University/Institute'),
    ];
    $form['lab_title'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->lab_title,
      '#title' => t('Title of the Lab'),
    ];
    $form['category'] = [
      '#type' => 'select',
      '#title' => t('Category'),
      '#options' => _lm_list_of_categories(),
      '#required' => TRUE,
      '#default_value' => $proposal_data->category,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];
    $form['cancel'] = [
      '#type' => 'item',
      '#markup' => l(t('Cancel'), 'lab-migration/manage-proposal/category'),
    ];
    return $form;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    /* get current proposal */
    $proposal_id = (int) arg(4);
    //$proposal_q = \Drupal::database()->query("SELECT * FROM {lab_migration_proposal} WHERE id = %d", $proposal_id);
    $query = \Drupal::database()->select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q) {
      if ($proposal_data = $proposal_q->fetchObject()) {
        /* everything ok */
      }
      else {
        \Drupal::messenger()->addmessage(t('Invalid proposal selected. Please try again.'), 'error');
        drupal_goto('lab-migration/manage-proposal');
        return;
      }
    }
    else {
      \Drupal::messenger()->addmessage(t('Invalid proposal selected. Please try again.'), 'error');
      drupal_goto('lab-migration/manage-proposal');
      return;
    }
    $query = "UPDATE {lab_migration_proposal} SET category = :category WHERE id = :proposal_id";
    $args = [
      ":category" => $form_state->getValue(['category']),
      ":proposal_id" => $proposal_data->id,
    ];
    $result = \Drupal::database()->query($query, $args);
    \Drupal::messenger()->addmessage(t('Proposal Category Updated'), 'status');
    drupal_goto('lab-migration/manage-proposal/category');
  }

}
