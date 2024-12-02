<?php
namespace Drupal\lab_migration;

class LabMigrationBulkApprovalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lab_migration_bulk_approval_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $options_first = _bulk_list_of_labs();
    $options_two = _ajax_bulk_get_experiment_list();
    $selected = !$form_state->getValue(['lab']) ? $form_state->getValue(['lab']) : key($options_first);
    $select_two = !$form_state->getValue(['lab_experiment_list']) ? $form_state->getValue([
      'lab_experiment_list'
      ]) : key($options_two);
    $form = [];
    $form['lab'] = [
      '#type' => 'select',
      '#title' => t('Title of the lab'),
      '#options' => _bulk_list_of_labs(),
      '#default_value' => $selected,
      '#ajax' => [
        'callback' => 'ajax_bulk_experiment_list_callback'
        ],
      '#suffix' => '<div id="ajax_selected_lab"></div><div id="ajax_selected_lab_pdf"></div>',
    ];
    $form['lab_actions'] = [
      '#type' => 'select',
      '#title' => t('Please select action for Entire Lab'),
      '#options' => _bulk_list_lab_actions(),
      '#default_value' => 0,
      '#prefix' => '<div id="ajax_selected_lab_action" style="color:red;">',
      '#suffix' => '</div>',
      '#states' => [
        'invisible' => [
          ':input[name="lab"]' => [
            'value' => 0
            ]
          ]
        ],
    ];
    $form['lab_experiment_list'] = [
      '#type' => 'select',
      '#title' => t('Titile of the experiment'),
      '#options' => _ajax_bulk_get_experiment_list($selected),
      '#default_value' => !$form_state->getValue([
        'lab_experiment_list'
        ]) ? $form_state->getValue(['lab_experiment_list']) : '',
      '#ajax' => [
        'callback' => 'ajax_bulk_solution_list_callback'
        ],
      '#prefix' => '<div id="ajax_selected_experiment">',
      '#suffix' => '</div>',
      '#states' => [
        'invisible' => [
          ':input[name="lab"]' => [
            'value' => 0
            ]
          ]
        ],
    ];
    $form['download_experiment'] = [
      '#type' => 'item',
      '#markup' => '<div id="ajax_download_experiment"></div>',
    ];
    $form['lab_experiment_actions'] = [
      '#type' => 'select',
      '#title' => t('Please select action for Entire Experiment'),
      '#options' => _bulk_list_experiment_actions(),
      '#default_value' => 0,
      '#prefix' => '<div id="ajax_selected_lab_experiment_action" style="color:red;">',
      '#suffix' => '</div>',
      //'#states' => array('invisible' => array(':input[name="lab"]' => array('value' => 0),),),  
        '#states' => [
        'invisible' => [
          [
            [
              ':input[name="lab"]' => [
                'value' => 0
                ]
              ],
            'or',
            [':input[name="lab_actions"]' => ['value' => 0]],
          ]
          ]
        ],
    ];
    $form['lab_solution_list'] = [
      '#type' => 'select',
      '#title' => t('Solution'),
      '#options' => _ajax_bulk_get_solution_list($select_two),
      '#default_value' => !$form_state->getValue([
        'lab_solution_list'
        ]) ? $form_state->getValue(['lab_solution_list']) : '',
      '#ajax' => [
        'callback' => 'ajax_bulk_solution_files_callback'
        ],
      '#prefix' => '<div id="ajax_selected_solution">',
      '#suffix' => '</div>',
      '#states' => [
        'invisible' => [
          ':input[name="lab"]' => [
            'value' => 0
            ]
          ]
        ],
    ];
    $form['lab_experiment_solution_actions'] = [
      '#type' => 'select',
      '#title' => t('Please select action for solution'),
      '#options' => _bulk_list_solution_actions(),
      '#default_value' => 0,
      '#prefix' => '<div id="ajax_selected_lab_experiment_solution_action" style="color:red;">',
      '#suffix' => '</div>',
      '#states' => [
        'invisible' => [
          ':input[name="lab"]' => [
            'value' => 0
            ]
          ]
        ],
    ];
    $form['download_solution'] = [
      '#type' => 'item',
      '#markup' => '<div id="ajax_download_experiment_solution"></div>',
    ];
    $form['edit_solution'] = [
      '#type' => 'item',
      '#markup' => '<div id="ajax_edit_experiment_solution"></div>',
    ];
    $form['solution_files'] = [
      '#type' => 'item',
      //'#title' => t('List of solution_files'),
        '#markup' => '<div id="ajax_solution_files"></div>',
      '#states' => [
        'invisible' => [
          ':input[name="lab"]' => [
            'value' => 0
            ]
          ]
        ],
    ];
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => t('If Dis-Approved please specify reason for Dis-Approval'),
      '#prefix' => '<div id= "message_submit">',
      '#states' => [
        'visible' => [
          [
            [
              ':input[name="lab_actions"]' => [
                'value' => 3
                ]
              ],
            'or',
            [
              ':input[name="lab_experiment_actions"]' => [
                'value' => 3
                ]
              ],
            'or',
            [
              ':input[name="lab_experiment_solution_actions"]' => [
                'value' => 3
                ]
              ],
            'or',
            [':input[name="lab_actions"]' => ['value' => 4]],
          ]
          ],
        'required' => [
          [
            [':input[name="lab_actions"]' => ['value' => 3]],
            'or',
            [
              ':input[name="lab_experiment_actions"]' => [
                'value' => 3
                ]
              ],
            'or',
            [
              ':input[name="lab_experiment_solution_actions"]' => [
                'value' => 3
                ]
              ],
            'or',
            [':input[name="lab_actions"]' => ['value' => 4]],
          ]
          ],
      ],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
      '#suffix' => '</div>',
      '#states' => [
        'invisible' => [
          ':input[name="lab"]' => [
            'value' => 0
            ]
          ]
        ],
    ];
    return $form;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    $root_path = lab_migration_path();
    if ($form_state->get(['clicked_button', '#value']) == 'Submit') {
      /*if ($form_state['values']['lab'])
            lab_migration_del_lab_pdf($form_state['values']['lab']);
        */
      if (user_access('lab migration bulk manage code')) {
        $query = \Drupal::database()->select('lab_migration_proposal');
        $query->fields('lab_migration_proposal');
        $query->condition('id', $form_state->getValue(['lab']));
        $user_query = $query->execute();
        $user_info = $user_query->fetchObject();
        $user_data = user_load($user_info->uid);
        if (($form_state->getValue(['lab_actions']) == 1) && ($form_state->getValue(['lab_experiment_actions']) == 0) && ($form_state->getValue(['lab_experiment_solution_actions']) == 0)) {
          /* approving entire lab */
          //   $experiment_q = \Drupal::database()->query("SELECT * FROM {lab_migration_experiment} WHERE proposal_id = %d", $form_state['values']['lab']);
          $query = \Drupal::database()->select('lab_migration_experiment');
          $query->fields('lab_migration_experiment');
          $query->condition('proposal_id', $form_state->getValue(['lab']));
          $query->orderBy('number', 'ASC');
          $experiment_q = $query->execute();
          $experiment_list = '';
          while ($experiment_data = $experiment_q->fetchObject()) {
            //  \Drupal::database()->query("UPDATE {lab_migration_solution} SET approval_status = 1, approver_uid = %d WHERE experiment_id = %d AND approval_status = 0", $user->uid, $experiment_data->id);
            \Drupal::database()->query("UPDATE {lab_migration_solution} SET approval_status = 1, approver_uid = :approver_uid WHERE experiment_id = :experiment_id AND approval_status = 0", [
              ':approver_uid' => $user->uid,
              ':experiment_id' => $experiment_data->id,
            ]);
            $experiment_list .= '<p>' . $experiment_data->number . ') ' . $experiment_data->title . '<br> Description :  ' . $experiment_data->description . '<br>';
            $experiment_list .= ' ';
            $experiment_list .= '</p>';
          }
          \Drupal::messenger()->addmessage(t('Approved Entire Lab.'), 'status');
          /* email */
          $email_subject = t('[!site_name] Your uploaded Lab Migration solutions have been approved', [
            '!site_name' => variable_get('site_name', '')
            ]);
          $email_body = [
            0 => t('

Dear !user_name,

Your all the uploaded solutions for the Lab with the below detail has been approved:

Title of Lab :' . $user_info->lab_title . '

List of experiments : ' . $experiment_list . '

Best Wishes,

!site_name Team,
FOSSEE,IIT Bombay', [
              '!site_name' => variable_get('site_name', ''),
              '!user_name' => $user_data->name,
            ])
            ];
        }
        elseif (($form_state->getValue(['lab_actions']) == 2) && ($form_state->getValue(['lab_experiment_actions']) == 0) && ($form_state->getValue(['lab_experiment_solution_actions']) == 0)) {
          /* pending review entire lab */
          //$experiment_q = \Drupal::database()->query("SELECT * FROM {lab_migration_experiment} WHERE proposal_id = %d", $form_state['values']['lab']);
          $experiment_q = \Drupal::database()->query("SELECT * FROM {lab_migration_experiment} WHERE proposal_id = :proposal_id", [
            ':proposal_id' => $form_state->getValue(['lab'])
            ]);
          while ($experiment_data = $experiment_q->fetchObject()) {
            //\Drupal::database()->query("UPDATE {lab_migration_solution} SET approval_status = 0 WHERE experiment_id = %d", $experiment_data->id);
            \Drupal::database()->query("UPDATE {lab_migration_solution} SET approval_status = 0 WHERE experiment_id = :experiment_id", [
              ":experiment_id" => $experiment_data->id
              ]);
          }
          \Drupal::messenger()->addmessage(t('Pending Review Entire Lab.'), 'status');
          /* email */
          $email_subject = t('[!site_name] Your uploaded Lab Migration solutions have been marked as pending', [
            '!site_name' => variable_get('site_name', '')
            ]);
          $email_body = [
            0 => t('

Dear !user_name,

Your all the uploaded solutions for the Lab with Title : ' . $user_info->lab_title . ' have been marked as pending to be reviewed.
 
You will be able to see the solutions after they have been approved by one of our reviewers.

Best Wishes,

!site_name Team,
FOSSEE,IIT Bombay', [
              '!site_name' => variable_get('site_name', ''),
              '!user_name' => $user_data->name,
            ])
            ];
          /* email */
          /* $email_subject = t('Your uploaded Lab Migration solutions have been marked as pending');
                $email_body = array(0 => t('Your all the uploaded solutions for the Lab have been marked as pending to be review. You will be able to see the solutions after they have been approved by one of our reviewers.'));*/
        }
        elseif (($form_state->getValue(['lab_actions']) == 3) && ($form_state->getValue(['lab_experiment_actions']) == 0) && ($form_state->getValue(['lab_experiment_solution_actions']) == 0)) {
          if (strlen(trim($form_state->getValue(['message']))) <= 30) {
            $form_state->setErrorByName('message', t(''));
            \Drupal::messenger()->addmessage("Please mention the reason for disapproval. Minimum 30 character required", 'error');
            return;
          }
          if (!user_access('lab migration bulk delete code')) {
            \Drupal::messenger()->addmessage(t('You do not have permission to Bulk Dis-Approved and Deleted Entire Lab.'), 'error');
            return;
          }
          if (lab_migration_delete_lab($form_state->getValue(['lab']))) {
            \Drupal::messenger()->addmessage(t('Dis-Approved and Deleted Entire Lab.'), 'status');
          }
          else {
            \Drupal::messenger()->addmessage(t('Error Dis-Approving and Deleting Entire Lab.'), 'error');
          }
          /* email */
          $email_subject = t('[!site_name] Your uploaded Lab Migration solutions have been marked as dis-approved', [
            '!site_name' => variable_get('site_name', '')
            ]);
          $email_body = [
            0 => t('

Dear !user_name,

Your all the uploaded solutions for the whole Lab with Title : ' . $user_info->lab_title . ' have been marked as dis-approved.

Reason for dis-approval: ' . $form_state->getValue(['message']) . '

Best Wishes,

!site_name Team,
FOSSEE,IIT Bombay', [
              '!site_name' => variable_get('site_name', ''),
              '!user_name' => $user_data->name,
            ])
            ];
          /* email */
          /* $email_subject = t('Your uploaded Lab Migration solutions have been marked as dis-approved');
                $email_body = array(0 =>t('Your all the uploaded solutions for the whole Lab have been marked as dis-approved.
                
                Reason for dis-approval:
                
                ' . $form_state['values']['message']));*/
        }
        elseif (($form_state->getValue(['lab_actions']) == 4) && ($form_state->getValue(['lab_experiment_actions']) == 0) && ($form_state->getValue(['lab_experiment_solution_actions']) == 0)) {
          if (strlen(trim($form_state->getValue(['message']))) <= 30) {
            $form_state->setErrorByName('message', t(''));
            \Drupal::messenger()->addmessage("Please mention the reason for disapproval/deletion. Minimum 30 character required", 'error');
            return;
          }
          $query = \Drupal::database()->select('lab_migration_experiment');
          $query->fields('lab_migration_experiment');
          $query->condition('proposal_id', $form_state->getValue(['lab']));
          $query->orderBy('number', 'ASC');
          $experiment_q = $query->execute();
          $experiment_list = '';
          while ($experiment_data = $experiment_q->fetchObject()) {
            $experiment_list .= '<p>' . $experiment_data->number . ') ' . $experiment_data->title . '<br> Description :  ' . $experiment_data->description . '<br>';
            $experiment_list .= ' ';
            $experiment_list .= '</p>';
          }
          if (!user_access('lab migration bulk delete code')) {
            \Drupal::messenger()->addmessage(t('You do not have permission to Bulk Delete Entire Lab Including Proposal.'), 'error');
            return;
          }
          /* check if dependency files are present */
          $dep_q = \Drupal::database()->query("SELECT * FROM {lab_migration_dependency_files} WHERE proposal_id = :proposal_id", [
            ":proposal_id" => $form_state->getValue(['lab'])
            ]);
          if ($dep_data = $dep_q->fetchObject()) {
            \Drupal::messenger()->addmessage(t("Cannot delete lab since it has dependency files that can be used by others. First delete the dependency files before deleting the lab."), 'error');
            return;
          }
          if (lab_migration_delete_lab($form_state->getValue(['lab']))) {
            \Drupal::messenger()->addmessage(t('Dis-Approved and Deleted Entire Lab solutions.'), 'status');
            $query = \Drupal::database()->select('lab_migration_proposal');
            $query->fields('lab_migration_proposal');
            $query->condition('id', $form_state->getValue(['lab']));
            $experiment_q = $query->execute()->fetchObject();
            $dir_path = $root_path . $experiment_q->directory_name;
            //var_dump($dir_path);die;
            if (is_dir($dir_path)) {
              $res = rmdir($dir_path);
              if (!$res) {
                \Drupal::messenger()->addmessage(t("Cannot delete Lab directory : " . $dir_path . ". Please contact administrator."), 'error');
                return;
              }
            }
            else {
              \Drupal::messenger()->addmessage(t("Lab directory not present : " . $dir_path . ". Skipping deleting lab directory."), 'status');
            }
            /* deleting full proposal */
            //$proposal_q = \Drupal::database()->query("SELECT * FROM {lab_migration_proposal} WHERE id = %d", $form_state['values']['lab']);
            $proposal_q = \Drupal::database()->query("SELECT * FROM {lab_migration_proposal} WHERE id = :id", [
              ":id" => $form_state->getValue(['lab'])
              ]);
            $proposal_data = $proposal_q->fetchObject();
            $proposal_id = $proposal_data->id;
            \Drupal::database()->query("DELETE FROM {lab_migration_experiment} WHERE proposal_id = :proposal_id", [
              ":proposal_id" => $proposal_id
              ]);
            \Drupal::database()->query("DELETE FROM {lab_migration_proposal} WHERE id = :id", [
              ":id" => $proposal_id
              ]);
            \Drupal::messenger()->addmessage(t('Deleted Lab Proposal.'), 'status');
            /* email */
            $email_subject = t('[!site_name] Your uploaded Lab Migration solutions including the Lab proposal have been deleted', [
              '!site_name' => variable_get('site_name', '')
              ]);
            $email_body = [
              0 => t('

Dear !user_name,

We regret to inform you that all the uploaded Experiments of your Lab with following details have been deleted permanently.

Title of Lab :' . $user_info->lab_title . '

List of experiments : ' . $experiment_list . '

Reason for dis-approval: ' . $form_state->getValue(['message']) . '

Best Wishes,

!site_name Team,
FOSSEE,IIT Bombay', [
                '!site_name' => variable_get('site_name', ''),
                '!user_name' => $user_data->name,
              ])
              ];
            /* email */
            /*  $email_subject = t('Your uploaded Lab Migration solutions including the Lab proposal have been deleted');
                    $email_body = array(0 =>t('Your all the uploaded solutions including the Lab proposal have been deleted permanently.'));*/
          }
          else {
            \Drupal::messenger()->addmessage(t('Error Dis-Approving and Deleting Entire Lab.'), 'error');
          }
        }
        elseif (($form_state->getValue(['lab_actions']) == 0) && ($form_state->getValue(['lab_experiment_actions']) == 1) && ($form_state->getValue(['lab_experiment_solution_actions']) == 0)) {
          \Drupal::database()->query("UPDATE {lab_migration_solution} SET approval_status = 1, approver_uid = :approver_uid WHERE experiment_id = :experiment_id AND approval_status = 0", [
            ":approver_uid" => $user->uid,
            ":experiment_id" => $form_state->getValue(['lab_experiment_list']),
          ]);
          $query = \Drupal::database()->select('lab_migration_experiment');
          $query->fields('lab_migration_experiment');
          $query->condition('id', $form_state->getValue(['lab_experiment_list']));
          $query->orderBy('number', 'ASC');
          $experiment_q = $query->execute();
          $experiment_value = $experiment_q->fetchObject();
          $query = \Drupal::database()->select('lab_migration_solution');
          $query->fields('lab_migration_solution');
          $query->condition('experiment_id', $form_state->getValue(['lab_experiment_list']));
          $query->orderBy('code_number', 'ASC');
          $solution_q = $query->execute();
          $solution_value = $solution_q->fetchObject();
          \Drupal::messenger()->addmessage(t('Approved Entire Experiment.'), 'status');
          /* email */
          $email_subject = t('[!site_name] Your uploaded Lab Migration solution have been approved', [
            '!site_name' => variable_get('site_name', '')
            ]);
          $email_body = [
            0 => t('

Dear !user_name,

Your Experiment for DWSIM Lab Migration with the following details is approved.

Experiment name : ' . $experiment_value->title . '
Caption : ' . $solution_value->caption . '

Best Wishes,

!site_name Team,
FOSSEE,IIT Bombay', [
              '!site_name' => variable_get('site_name', ''),
              '!user_name' => $user_data->name,
            ])
            ];
          /* email */
          /* $email_subject = t('Your uploaded Lab Migration solutions have been approved');
                $email_body = array(0 =>t('Your all the uploaded solutions for the experiment have been approved.'));*/
        }
        elseif (($form_state->getValue(['lab_actions']) == 0) && ($form_state->getValue(['lab_experiment_actions']) == 2) && ($form_state->getValue(['lab_experiment_solution_actions']) == 0)) {
          \Drupal::database()->query("UPDATE {lab_migration_solution} SET approval_status = 0 WHERE experiment_id = :experiment_id", [
            ":experiment_id" => $form_state->getValue(['lab_experiment_list'])
            ]);
          \Drupal::messenger()->addmessage(t('Entire Experiment marked as Pending Review.'), 'status');
          $query = \Drupal::database()->select('lab_migration_experiment');
          $query->fields('lab_migration_experiment');
          $query->condition('id', $form_state->getValue(['lab_experiment_list']));
          $query->orderBy('number', 'ASC');
          $experiment_q = $query->execute();
          $experiment_value = $experiment_q->fetchObject();
          $query = \Drupal::database()->select('lab_migration_solution');
          $query->fields('lab_migration_solution');
          $query->condition('experiment_id', $form_state->getValue(['lab_experiment_list']));
          $query->orderBy('code_number', 'ASC');
          $solution_q = $query->execute();
          $solution_value = $solution_q->fetchObject();
          /* email */
          $email_subject = t('[!site_name] Your uploaded Lab Migration solution have been marked as pending', [
            '!site_name' => variable_get('site_name', '')
            ]);
          $email_body = [
            0 => t('

Dear !user_name,

Your all the uploaded solutions for the experiment have been marked as pending to be reviewed.

Experiment name : ' . $experiment_value->title . '
Caption : ' . $solution_value->caption . '

Best Wishes,

!site_name Team,
FOSSEE,IIT Bombay', [
              '!site_name' => variable_get('site_name', ''),
              '!user_name' => $user_data->name,
            ])
            ];
          /* email */
          /*$email_subject = t('Your uploaded Lab Migration solutions have been marked as pending');
                $email_body = array(0 =>t('Your all the uploaded solutions for the experiment have been marked as pending to be review.'));*/
        }
        elseif (($form_state->getValue(['lab_actions']) == 0) && ($form_state->getValue(['lab_experiment_actions']) == 3) && ($form_state->getValue(['lab_experiment_solution_actions']) == 0)) {
          if (strlen(trim($form_state->getValue(['message']))) <= 30) {
            $form_state->setErrorByName('message', t(''));
            \Drupal::messenger()->addmessage("Please mention the reason for disapproval. Minimum 30 character required", 'error');
            return;
          }
          if (!user_access('lab migration bulk delete code')) {
            \Drupal::messenger()->addmessage(t('You do not have permission to Bulk Dis-Approved and Deleted Entire Experiment.'), 'error');
            return;
          }
          $query = \Drupal::database()->select('lab_migration_experiment');
          $query->fields('lab_migration_experiment');
          $query->condition('id', $form_state->getValue(['lab_experiment_list']));
          $query->orderBy('number', 'ASC');
          $experiment_q = $query->execute();
          $experiment_value = $experiment_q->fetchObject();
          $query = \Drupal::database()->select('lab_migration_solution');
          $query->fields('lab_migration_solution');
          $query->condition('experiment_id', $form_state->getValue(['lab_experiment_list']));
          $query->orderBy('code_number', 'ASC');
          $solution_q = $query->execute();
          $solution_value = $solution_q->fetchObject();
          if (lab_migration_delete_experiment($form_state->getValue(['lab_experiment_list']))) {
            \Drupal::messenger()->addmessage(t('Dis-Approved and Deleted Entire Experiment.'), 'status');
          }
          else {
            \Drupal::messenger()->addmessage(t('Error Dis-Approving and Deleting Entire Experiment.'), 'error');
          }
          /* email */
          $email_subject = t('[!site_name] Your uploaded Lab Migration solutions have been marked as dis-approved', [
            '!site_name' => variable_get('site_name', '')
            ]);
          $email_body = [
            0 => t('

Dear !user_name,

We regret to inform you that your experiment with the following details under DWSIM Lab Migration is disapproved and has been deleted.

Experiment name : ' . $experiment_value->title . '
Caption : ' . $solution_value->caption . '

Reason for dis-approval: ' . $form_state->getValue(['message']) . '

Please resubmit the modified solution.

Best Wishes,

!site_name Team,
FOSSEE,IIT Bombay', [
              '!site_name' => variable_get('site_name', ''),
              '!user_name' => $user_data->name,
            ])
            ];
          /* email */
          /*$email_subject = t('Your uploaded Lab Migration solutions have been marked as dis-approved');
                $email_body = array(0 => t('Your uploaded solutions for the entire experiment have been marked as dis-approved.
                
                Reason for dis-approval:
                
                ' . $form_state['values']['message']));*/
        }
        elseif (($form_state->getValue(['lab_actions']) == 0) && ($form_state->getValue(['lab_experiment_actions']) == 0) && ($form_state->getValue(['lab_experiment_solution_actions']) == 1)) {
          $query = \Drupal::database()->select('lab_migration_solution');
          $query->fields('lab_migration_solution');
          $query->condition('id', $form_state->getValue(['lab_solution_list']));
          $query->orderBy('code_number', 'ASC');
          $solution_q = $query->execute();
          $solution_value = $solution_q->fetchObject();
          $query = \Drupal::database()->select('lab_migration_experiment');
          $query->fields('lab_migration_experiment');
          $query->condition('id', $solution_value->experiment_id);
          $query->orderBy('number', 'ASC');
          $experiment_q = $query->execute();
          $experiment_value = $experiment_q->fetchObject();
          \Drupal::database()->query("UPDATE {lab_migration_solution} SET approval_status = 1, approver_uid = :approver_uid WHERE id = :id", [
            ":approver_uid" => $user->uid,
            ":id" => $form_state->getValue(['lab_solution_list']),
          ]);
          \Drupal::messenger()->addmessage(t('Solution approved.'), 'status');
          /* email */
          $email_subject = t('[!site_name] Your uploaded Lab Migration solution has been approved', [
            '!site_name' => variable_get('site_name', '')
            ]);
          $email_body = [
            0 => t('

Dear !user_name,

Your Experiment for DWSIM Lab Migration with the following details is approved.

Experiment name : ' . $experiment_value->title . '
Caption : ' . $solution_value->caption . '

Best Wishes,

!site_name Team,
FOSSEE,IIT Bombay', [
              '!site_name' => variable_get('site_name', ''),
              '!user_name' => $user_data->name,
            ])
            ];
          /* email */
          /* $email_subject = t('Your uploaded Lab Migration solution has been approved');
                $email_body = array(0 =>t('Your uploaded solution has been approved.'));*/
        }
        elseif (($form_state->getValue(['lab_actions']) == 0) && ($form_state->getValue(['lab_experiment_actions']) == 0) && ($form_state->getValue(['lab_experiment_solution_actions']) == 2)) {
          $query = \Drupal::database()->select('lab_migration_solution');
          $query->fields('lab_migration_solution');
          $query->condition('id', $form_state->getValue(['lab_solution_list']));
          $query->orderBy('code_number', 'ASC');
          $solution_q = $query->execute();
          $solution_value = $solution_q->fetchObject();
          $query = \Drupal::database()->select('lab_migration_experiment');
          $query->fields('lab_migration_experiment');
          $query->condition('id', $solution_value->experiment_id);
          $query->orderBy('number', 'ASC');
          $experiment_q = $query->execute();
          $experiment_value = $experiment_q->fetchObject();
          \Drupal::database()->query("UPDATE {lab_migration_solution} SET approval_status = 0 WHERE id = :id", [
            ":id" => $form_state->getValue(['lab_solution_list'])
            ]);
          \Drupal::messenger()->addmessage(t('Solution marked as Pending Review.'), 'status');
          /* email */
          $email_subject = t('[!site_name] Your uploaded Lab Migration solution has been marked as pending', [
            '!site_name' => variable_get('site_name', '')
            ]);
          $email_body = [
            0 => t('

Dear !user_name,

Your all the uploaded solutions for the experiment have been marked as pending to be reviewed.

Experiment name : ' . $experiment_value->title . '
Caption : ' . $solution_value->caption . '

Best Wishes,

!site_name Team,
FOSSEE,IIT Bombay', [
              '!site_name' => variable_get('site_name', ''),
              '!user_name' => $user_data->name,
            ])
            ];
          /* email */
          /*$email_subject = t('Your uploaded Lab Migration solution has been marked as pending');
                $email_body = array(0 =>t('Your uploaded solution has been marked as pending to be review.'));*/
        }
        elseif (($form_state->getValue(['lab_actions']) == 0) && ($form_state->getValue(['lab_experiment_actions']) == 0) && ($form_state->getValue(['lab_experiment_solution_actions']) == 3)) {
          $query = \Drupal::database()->select('lab_migration_solution');
          $query->fields('lab_migration_solution');
          $query->condition('id', $form_state->getValue(['lab_solution_list']));
          $query->orderBy('code_number', 'ASC');
          $solution_q = $query->execute();
          $solution_value = $solution_q->fetchObject();
          $query = \Drupal::database()->select('lab_migration_experiment');
          $query->fields('lab_migration_experiment');
          $query->condition('id', $solution_value->experiment_id);
          $query->orderBy('number', 'ASC');
          $experiment_q = $query->execute();
          $experiment_value = $experiment_q->fetchObject();
          if (strlen(trim($form_state->getValue(['message']))) <= 30) {
            $form_state->setErrorByName('message', t(''));
            \Drupal::messenger()->addmessage("Please mention the reason for disapproval. Minimum 30 character required", 'error');
            return;
          }
          if (lab_migration_delete_solution($form_state->getValue(['lab_solution_list']))) {
            \Drupal::messenger()->addmessage(t('Solution Dis-Approved and Deleted.'), 'status');
          }
          else {
            \Drupal::messenger()->addmessage(t('Error Dis-Approving and Deleting Solution.'), 'error');
          }
          /* email */
          $email_subject = t('[!site_name] Your uploaded Lab Migration solution has been marked as dis-approved', [
            '!site_name' => variable_get('site_name', '')
            ]);
          $email_body = [
            0 => t('

Dear !user_name,

We regret to inform you that your experiment with the following details under DWSIM Lab Migration is disapproved and has been deleted.

Experiment name : ' . $experiment_value->title . '
Caption : ' . $solution_value->caption . '

Reason for dis-approval: ' . $form_state->getValue(['message']) . '

Please resubmit the modified solution.

Best Wishes,

!site_name Team,
FOSSEE,IIT Bombay', [
              '!site_name' => variable_get('site_name', ''),
              '!user_name' => $user_data->name,
            ])
            ];
          /* email */
          /* $email_subject = t('Your uploaded Lab Migration solution has been marked as dis-approved');
                $email_body = array(0 =>t('Your uploaded solution has been marked as dis-approved.
                
                Reason for dis-approval:
                
                ' . $form_state['values']['message']));*/
        }
        else {
          \Drupal::messenger()->addmessage(t('Please select only one action at a time'), 'error');
          return;
        }
        /** sending email when everything done **/
      if ($email_subject) {
          $email_to = $user_data->mail;
          $from = variable_get('lab_migration_from_email', '');
          $bcc = variable_get('lab_migration_emails', '');
          $cc = variable_get('lab_migration_cc_emails', '');
          $param['standard']['subject'] = $email_subject;
          $param['standard']['body'] = $email_body;
          $param['standard']['headers'] = [
            'From' => $from,
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
            'Content-Transfer-Encoding' => '8Bit',
            'X-Mailer' => 'Drupal',
            'Cc' => $cc,
            'Bcc' => $bcc,
          ];
          if (!drupal_mail('lab_migration', 'standard', $email_to, language_default(), $param, $from, TRUE)) {
            \Drupal::messenger()->addmessage('Error sending email message.', 'error');
          }
        }
      }
      else {
        \Drupal::messenger()->addmessage(t('You do not have permission to bulk manage code.'), 'error');
      }
    }
    return;
  }

}
