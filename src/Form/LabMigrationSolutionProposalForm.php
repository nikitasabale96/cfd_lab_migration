<?php

/**
 * @file
 * Contains \Drupal\lab_migration\Form\LabMigrationSolutionProposalForm.
 */

namespace Drupal\lab_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LabMigrationSolutionProposalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lab_migration_solution_proposal_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    $route_match = \Drupal::routeMatch();
    
    // Unified parameter key
    $proposal_id = (int) $route_match->getParameter('proposal_id');

    if (empty($proposal_id)) {
      \Drupal::messenger()->addError($this->t('Proposal ID not found.'));
      return $form;
    }

    $proposal_data = \Drupal::database()
      ->select('lab_migration_proposal', 'lmp')
      ->fields('lmp')
      ->condition('id', $proposal_id)
      ->execute()
      ->fetchObject();

    if (!$proposal_data) {
      \Drupal::messenger()->addError($this->t('Proposal data not found.'));
      return $form;
    }

    $form['name'] = [
      '#type' => 'item',
      '#title' => $this->t('Proposer Name'),
      '#markup' => $proposal_data->name_title . ' ' . $proposal_data->name,
    ];

    $form['lab_title'] = [
      '#type' => 'item',
      '#title' => $this->t('Title of the Lab'),
      '#markup' => $proposal_data->lab_title,
    ];

    $experiment_html = '';
    $query = \Drupal::database()->select('lab_migration_experiment');
    $query->fields('lab_migration_experiment');
    $query->condition('proposal_id', $proposal_id);
    $experiment_q = $query->execute();
    
    while ($experiment_data = $experiment_q->fetchObject()) {
      $experiment_html .= $experiment_data->title . '<br>';
    }
    
    $form['experiment'] = [
      '#type' => 'item',
      '#title' => $this->t('Experiment List'),
      '#markup' => $experiment_html,
    ];
    
    $form['solution_provider_name_title'] = [
      '#type' => 'select',
      '#title' => $this->t('Title'),
      '#options' => [
        'Mr' => 'Mr',
        'Ms' => 'Ms',
        'Mrs' => 'Mrs',
        'Dr' => 'Dr',
        'Prof' => 'Prof',
      ],
      '#required' => TRUE,
    ];

    $form['solution_provider_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name of the Solution Provider'),
      '#maxlength' => 50,
      '#required' => TRUE,
    ];

    $form['solution_provider_email_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email'),
      '#default_value' => $user->getEmail(),
      '#disabled' => TRUE,
    ];

    $form['solution_provider_contact_ph'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Contact No.'),
      '#maxlength' => 15,
      '#required' => TRUE,
    ];

    $form['solution_provider_department'] = [
      '#type' => 'select',
      '#title' => $this->t('Department/Branch'),
      '#options' => \Drupal::service("lab_migration_global")->_lm_list_of_departments(),
      '#required' => TRUE,
    ];

    $form['solution_provider_university'] = [
      '#type' => 'textfield',
      '#title' => $this->t('University/Institute'),
      '#maxlength' => 50,
      '#required' => TRUE,
    ];

    $form['country'] = [
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#options' => [
        'India' => 'India',
        'Others' => 'Others',
      ],
      '#required' => TRUE,
      '#tree' => TRUE,
      '#validated' => TRUE,
    ];

    $form['other_country'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Other than India'),
      '#attributes' => [
        'placeholder' => $this->t('Enter your country name')
      ],
      '#states' => [
        'visible' => [
          ':input[name="country"]' => ['value' => 'Others']
        ]
      ],
    ];

    $form['other_state'] = [
      '#type' => 'textfield',
      '#title' => $this->t('State other than India'),
      '#attributes' => [
        'placeholder' => $this->t('Enter your state/region name')
      ],
      '#states' => [
        'visible' => [
          ':input[name="country"]' => ['value' => 'Others']
        ]
      ],
    ];

    $form['other_city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City other than India'),
      '#attributes' => [
        'placeholder' => $this->t('Enter your city name')
      ],
      '#states' => [
        'visible' => [
          ':input[name="country"]' => ['value' => 'Others']
        ]
      ],
    ];

    $form['all_state'] = [
      '#type' => 'select',
      '#title' => $this->t('State'),
      '#options' => \Drupal::service("lab_migration_global")->_lm_list_of_states(),
      '#validated' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="country"]' => ['value' => 'India']
        ]
      ],
    ];

    $form['city'] = [
      '#type' => 'select',
      '#title' => $this->t('City'),
      '#options' => \Drupal::service("lab_migration_global")->_lm_list_of_cities(),
      '#states' => [
        'visible' => [
          ':input[name="country"]' => ['value' => 'India']
        ]
      ],
    ];

    $form['pincode'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Pincode'),
      '#maxlength' => 6,
      '#required' => FALSE,
      '#attributes' => [
        'placeholder' => 'Enter pincode....'
      ],
    ];
    
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply for Solution'),
    ];
    
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    if ($form_state->getValue(['country']) == 'Others') {
      if ($form_state->getValue(['other_country']) == '') {
        $form_state->setErrorByName('other_country', $this->t('Enter country name'));
      }
      else {
        $form_state->setValue(['country'], $form_state->getValue(['other_country']));
      }
      if ($form_state->getValue(['other_state']) == '') {
        $form_state->setErrorByName('other_state', $this->t('Enter state name'));
      }
      else {
        $form_state->setValue(['all_state'], $form_state->getValue(['other_state']));
      }
      if ($form_state->getValue(['other_city']) == '') {
        $form_state->setErrorByName('other_city', $this->t('Enter city name'));
      }
      else {
        $form_state->setValue(['city'], $form_state->getValue(['other_city']));
      }
    }
    else {
      if ($form_state->getValue(['country']) == '') {
        $form_state->setErrorByName('country', $this->t('Select country name'));
      }
      if ($form_state->getValue(['all_state']) == '') {
        $form_state->setErrorByName('all_state', $this->t('Select state name'));
      }
      if ($form_state->getValue(['city']) == '') {
        $form_state->setErrorByName('city', $this->t('Select city name'));
      }
    }

    // Skip verification logic during dev validation bypasses if necessary, 
    // but the query below remains updated to use standard API calls.
    $query = \Drupal::database()->select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('solution_provider_uid', $user->id());
    $query->condition('approval_status', [0, 1], 'IN');
    $query->condition('solution_status', [0, 1, 2], 'IN');
    $solution_provider_q = $query->execute();
    
    if ($solution_provider_q->fetchObject()) {
      $form_state->setErrorByName('', $this->t("You have already applied for a solution. Please complete that before applying for another solution."));
      $form_state->setRedirectUrl(\Drupal\Core\Url::fromUserInput('/lab-migration/open-proposal'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    $route_match = \Drupal::routeMatch();
    
    // FIX: Match parameter from buildForm() exactly
    $proposal_id = (int) $route_match->getParameter('proposal_id');

    $query = \Drupal::database()->select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    $proposal_data = $proposal_q->fetchObject();

    if (!$proposal_data) {
      \Drupal::messenger()->addMessage($this->t("Invalid proposal."), 'error');
      $form_state->setRedirectUrl(\Drupal\Core\Url::fromUserInput('/lab-migration/open-proposal'));
      return;
    }

    if ($proposal_data->solution_provider_uid != 0) {
      \Drupal::messenger()->addMessage($this->t("Someone has already applied for solving this Lab."), 'error');
      $form_state->setRedirectUrl(\Drupal\Core\Url::fromUserInput('/lab-migration/open-proposal'));
      return;
    }

    // Modern Drupal Dynamic Update Query Execution
    \Drupal::database()->update('lab_migration_proposal')
      ->fields([
        'solution_provider_uid' => $user->id(),
        'solution_status' => 1,
        'solution_provider_name_title' => $form_state->getValue(['solution_provider_name_title']),
        'solution_provider_name' => $form_state->getValue(['solution_provider_name']),
        'solution_provider_contact_ph' => $form_state->getValue(['solution_provider_contact_ph']),
        'solution_provider_department' => $form_state->getValue(['solution_provider_department']),
        'solution_provider_university' => $form_state->getValue(['solution_provider_university']),
        'solution_provider_city' => $form_state->getValue(['city']),
        'solution_provider_pincode' => $form_state->getValue(['pincode']),
        'solution_provider_state' => $form_state->getValue(['all_state']),
        'solution_provider_country' => $form_state->getValue(['country']),
      ])
      ->condition('id', $proposal_id)
      ->execute();

    \Drupal::messenger()->addMessage($this->t("We have received your application. We will get back to you soon."), 'status');
    
    /* Email Notification Engine */
    $config = \Drupal::config('lab_migration.settings');
    $from = $config->get('lab_migration_from_email') ?: \Drupal::config('system.site')->get('mail');
    $bcc  = $config->get('lab_migration_emails');
    $cc   = $config->get('lab_migration_cc_emails');
    $langcode = \Drupal::languageManager()->getDefaultLanguage()->getId();
    $mailManager = \Drupal::service('plugin.manager.mail');

    $params['solution_proposal_received'] = [
      'proposal_id' => $proposal_id,
      'user_id' => $user->id(),
      'headers' => [
        'From' => $from,
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
        'Content-Transfer-Encoding' => '8Bit',
        'X-Mailer' => 'Drupal',
        'Cc' => $cc,
        'Bcc' => $bcc,
      ],
    ];

    // Mail block 1
    $mailManager->mail('lab_migration', 'solution_proposal_received', $user->getEmail(), $langcode, $params, $from, TRUE);

    // Mail block 2
    if (!empty($bcc)) {
      $mailManager->mail('lab_migration', 'solution_proposal_received', $bcc, $langcode, $params, $from, TRUE);
    }

    // Dynamic clean redirection layout
    $form_state->setRedirect('<front>');
  }

}