<?php
// $Id$
function lab_migration_proposal_open()
  {
    global $user;
    /* get open proposal list */
    $proposal_rows = array();
    //$proposal_q = db_query("SELECT * FROM {lab_migration_proposal} WHERE approval_status = 1 AND solution_provider_uid = 0");
    $query = db_select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('approval_status', 1);
    $query->condition('solution_provider_uid', 0);
    $proposal_q = $query->execute();
    $proposal_q_count = $proposal_q->rowCount();
     if ($proposal_q_count != 0)
     {
    while ($proposal_data = $proposal_q->fetchObject())
      {
        if($proposal_data->problem_statement_file == ''){
              $problem_statement_file = "NA";
            }
            else{
              $problem_statement_file = l('View', 'lab-migration/download/problem-statement/' . $proposal_data->id);
            }
        $proposal_rows[] = array(
            l($proposal_data->lab_title, 'lab-migration/show-proposal/' . $proposal_data->id),
            $problem_statement_file,
            l('Apply', 'lab-migration/show-proposal/' . $proposal_data->id)
        );
      }
    $proposal_header = array(
        'Title of the Lab',
        'Problem Statement',
        'Actions'
    );
    $return_html = theme('table', array(
        'header' => $proposal_header,
        'rows' => $proposal_rows
    ));
}
 else
{

$return_html = 'There are no proposals are available to solve';
}
    //$return_html = theme_table($proposal_header, $proposal_rows);
    return $return_html;
  }
function lab_migration_solution_proposal_form($form, $form_state)
  {
    global $user;
    $proposal_id = (int) arg(2);
    //$proposal_q = db_query("SELECT * FROM {lab_migration_proposal} WHERE id = %d", $proposal_id);
    $query = db_select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    $proposal_data = $proposal_q->fetchObject();
    if (!$proposal_data)
      {
        drupal_set_message("Invalid proposal.", 'error');
        drupal_goto('');
      }
    //var_dump($proposal_data->name); die;
    $form['name'] = array(
        '#type' => 'item',
        '#markup' => l($proposal_data->name_title . ' ' . $proposal_data->name, 'user/' . $proposal_data->uid),
        '#title' => t('Proposer Name')
    );
    $form['lab_title'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->lab_title,
        '#title' => t('Title of the Lab')
    );
    $experiment_html = '';
    //$experiment_q = db_query("SELECT * FROM {lab_migration_experiment} WHERE proposal_id = %d", $proposal_id);
    $query = db_select('lab_migration_experiment');
    $query->fields('lab_migration_experiment');
    $query->condition('proposal_id', $proposal_id);
    $experiment_q = $query->execute();
    while ($experiment_data = $experiment_q->fetchObject())
      {
        $experiment_html .= $experiment_data->title . "<br/>";
      }
    $form['experiment'] = array(
        '#type' => 'item',
        '#markup' => $experiment_html,
        '#title' => t('Experiment List')
    );
    $form['solution_provider_name_title'] = array(
        '#type' => 'select',
        '#title' => t('Title'),
        '#options' => array(
            'Mr' => 'Mr',
            'Ms' => 'Ms',
            'Mrs' => 'Mrs',
            'Dr' => 'Dr',
            'Prof' => 'Prof'
        ),
        '#required' => TRUE
    );
    $form['solution_provider_name'] = array(
        '#type' => 'textfield',
        '#title' => t('Name of the Solution Provider'),
        '#size' => 30,
        '#maxlength' => 50,
        '#required' => TRUE
    );
    $form['solution_provider_email_id'] = array(
        '#type' => 'textfield',
        '#title' => t('Email'),
        '#size' => 30,
        '#value' => $user->mail,
        '#disabled' => TRUE
    );
    $form['solution_provider_contact_ph'] = array(
        '#type' => 'textfield',
        '#title' => t('Contact No.'),
        '#size' => 30,
        '#maxlength' => 15,
        '#required' => TRUE
    );
    $form['solution_provider_department'] = array(
        '#type' => 'select',
        '#title' => t('Department/Branch'),
        '#options' => _lm_list_of_departments(),
        '#required' => TRUE
    );
    $form['solution_provider_university'] = array(
        '#type' => 'textfield',
        '#title' => t('University/Institute'),
        '#size' => 30,
        '#maxlength' => 50,
        '#required' => TRUE
    );
    $form['country'] = array(
        '#type' => 'select',
        '#title' => t('Country'),
        '#options' => array(
            'India' => 'India',
            'Others' => 'Others'
        ),
        '#required' => TRUE,
        '#tree' => TRUE,
        '#validated' => TRUE
    );
    $form['other_country'] = array(
        '#type' => 'textfield',
        '#title' => t('Other than India'),
        '#size' => 100,
        '#attributes' => array(
            'placeholder' => t('Enter your country name')
        ),
        '#states' => array(
            'visible' => array(
                ':input[name="country"]' => array(
                    'value' => 'Others'
                )
            )
        )
    );
    $form['other_state'] = array(
        '#type' => 'textfield',
        '#title' => t('State other than India'),
        '#size' => 100,
        '#attributes' => array(
            'placeholder' => t('Enter your state/region name')
        ),
        '#states' => array(
            'visible' => array(
                ':input[name="country"]' => array(
                    'value' => 'Others'
                )
            )
        )
    );
    $form['other_city'] = array(
        '#type' => 'textfield',
        '#title' => t('City other than India'),
        '#size' => 100,
        '#attributes' => array(
            'placeholder' => t('Enter your city name')
        ),
        '#states' => array(
            'visible' => array(
                ':input[name="country"]' => array(
                    'value' => 'Others'
                )
            )
        )
    );
    $form['all_state'] = array(
        '#type' => 'select',
        '#title' => t('State'),
        '#options' => _lm_list_of_states(),
        '#validated' => TRUE,
        '#states' => array(
            'visible' => array(
                ':input[name="country"]' => array(
                    'value' => 'India'
                )
            )
        )
    );
    $form['city'] = array(
        '#type' => 'select',
        '#title' => t('City'),
        '#options' => _lm_list_of_cities(),
        '#validated' => TRUE,
        '#states' => array(
            'visible' => array(
                ':input[name="country"]' => array(
                    'value' => 'India'
                )
            )
        )
    );
    $form['pincode'] = array(
        '#type' => 'textfield',
        '#title' => t('Pincode'),
        '#size' => 30,
        '#maxlength' => 6,
        '#required' => False,
        '#attributes' => array(
            'placeholder' => 'Enter pincode....'
        )
    );
    /*$form['version'] = array(
        '#type' => 'select',
        '#title' => t('Version'),
        '#options' => _lm_list_of_software_version(),
        '#required' => TRUE
    );
    $form['older'] = array(
        '#type' => 'textfield',
        '#size' => 30,
        '#maxlength' => 50,
        //'#required' => TRUE,
        '#description' => t('Specify the Older version used'),
        '#states' => array(
            'visible' => array(
                ':input[name="version"]' => array(
                    'value' => 'olderversion'
                )
            )
        )
    );*/
    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Apply for Solution')
    );
    return $form;
  }
function lab_migration_solution_proposal_form_validate($form, &$form_state)
  {
    global $user;
    if ($form_state['values']['country'] == 'Others')
      {
        if ($form_state['values']['other_country'] == '')
          {
            form_set_error('other_country', t('Enter country name'));
            // $form_state['values']['country'] = $form_state['values']['other_country'];
          }
        else
          {
            $form_state['values']['country'] = $form_state['values']['other_country'];
          }
        if ($form_state['values']['other_state'] == '')
          {
            form_set_error('other_state', t('Enter state name'));
            // $form_state['values']['country'] = $form_state['values']['other_country'];
          }
        else
          {
            $form_state['values']['all_state'] = $form_state['values']['other_state'];
          }
        if ($form_state['values']['other_city'] == '')
          {
            form_set_error('other_city', t('Enter city name'));
            // $form_state['values']['country'] = $form_state['values']['other_country'];
          }
        else
          {
            $form_state['values']['city'] = $form_state['values']['other_city'];
          }
      }
    else
      {
        if ($form_state['values']['country'] == '')
          {
            form_set_error('country', t('Select country name'));
            // $form_state['values']['country'] = $form_state['values']['other_country'];
          }
        if ($form_state['values']['all_state'] == '')
          {
            form_set_error('all_state', t('Select state name'));
            // $form_state['values']['country'] = $form_state['values']['other_country'];
          }
        if ($form_state['values']['city'] == '')
          {
            form_set_error('city', t('Select city name'));
            // $form_state['values']['country'] = $form_state['values']['other_country'];
          }
      }
    
    if ($form_state['values']['version'] == 'olderversion')
      {
        if ($form_state['values']['older'] == '')
          {
            form_set_error('older', t('Please provide valid version'));
          }
      }
    return;
    //$solution_provider_q = db_query("SELECT * FROM {lab_migration_proposal} WHERE solution_provider_uid = ".$user->uid." AND approval_status IN (0, 1) AND solution_status IN (0, 1, 2)");
    $query = db_select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('solution_provider_uid', $user->uid);
    $query->condition('approval_status', array(
        0,
        1
    ), 'IN');
    $query->condition('solution_status', array(
        0,
        1,
        2
    ), 'IN');
    $solution_provider_q = $query->execute();
    if ($solution_provider_q->fetchObject())
      {
        form_set_error('', t("You have already applied for a solution. Please compelete that before applying for another solution."));
        drupal_goto('lab-migration/open-proposal');
      }
  }
function lab_migration_solution_proposal_form_submit($form, &$form_state)
  {
    global $user;
    $proposal_id = (int) arg(2);
    if ($form_state['values']['version'] == 'olderversion')
      {
        $form_state['values']['version'] = $form_state['values']['older'];
      }
    //$proposal_q = db_query("SELECT * FROM {lab_migration_proposal} WHERE id = %d", $proposal_id);
    $query = db_select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    $proposal_data = $proposal_q->fetchObject();
    if (!$proposal_data)
      {
        drupal_set_message("Invalid proposal.", 'error');
        drupal_goto('lab-migration/open-proposal');
      }
    if ($proposal_data->solution_provider_uid != 0)
      {
        drupal_set_message("Someone has already applied for solving this Lab.", 'error');
        drupal_goto('lab-migration/open-proposal');
      }
    $query = "UPDATE {lab_migration_proposal} set solution_provider_uid = :uid, solution_status = 1, solution_provider_name_title = :solution_provider_name_title, solution_provider_name = :solution_provider_contact_name, solution_provider_contact_ph = :solution_provider_contact_ph, solution_provider_department = :solution_provider_department, solution_provider_university = :solution_provider_university , solution_provider_city = :solution_provider_city, solution_provider_pincode = :solution_provider_pincode, solution_provider_state = :solution_provider_state,solution_provider_country = :solution_provider_country WHERE id = :proposal_id";
    $args = array(
        ":uid" => $user->uid,
        ":solution_provider_name_title" => $form_state['values']['solution_provider_name_title'],
        ":solution_provider_contact_name" => $form_state['values']['solution_provider_name'],
        ":solution_provider_contact_ph" => $form_state['values']['solution_provider_contact_ph'],
        ":solution_provider_department" => $form_state['values']['solution_provider_department'],
        ":solution_provider_university" => $form_state['values']['solution_provider_university'],
        ":solution_provider_city" => $form_state['values']['city'],
        ":solution_provider_pincode" => $form_state['values']['pincode'],
        ":solution_provider_state" => $form_state['values']['all_state'],
        ":solution_provider_country" => $form_state['values']['country'],
        
        ":proposal_id" => $proposal_id
    );
    
    $result = db_query($query, $args);
    drupal_set_message("We have received your application. We will get back to you soon.", 'status');
    /* sending email */
    $email_to = $user->mail;
    $from = variable_get('lab_migration_from_email', '');
    $bcc = variable_get('lab_migration_emails', '');
    $cc = variable_get('lab_migration_cc_emails', '');
    $param['solution_proposal_received']['proposal_id'] = $proposal_id;
    $param['solution_proposal_received']['user_id'] = $user->uid;
    $param['solution_proposal_received']['headers'] = array(
        'From' => $from,
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
        'Content-Transfer-Encoding' => '8Bit',
        'X-Mailer' => 'Drupal',
        'Cc' => $cc,
        'Bcc' => $bcc
    );
    if (!drupal_mail('lab_migration', 'solution_proposal_received', $email_to, language_default(), $param, $from, TRUE))
        drupal_set_message('Error sending email message.', 'error');
    /* sending email */
    /* $email_to = variable_get('lab_migration_emails', '');
    if (!drupal_mail('lab_migration', 'solution_proposal_received', $email_to , language_default(), $param, variable_get('lab_migration_from_email', NULL), TRUE))
    drupal_set_message('Error sending email message.', 'error');*/
    drupal_goto('lab-migration/open-proposal');
  }